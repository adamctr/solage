# Guide — Protéger les POST contre le CSRF

Guide pas-à-pas pour implémenter soi-même la protection CSRF (Synchronizer Token) dans
Solage. Ordonné pour **poser le rejet avant d'émettre les tokens** — l'app est donc
volontairement « cassée » entre l'étape 3 et l'étape 6, ce qui prouve que la protection
mord. Chaque étape : où écrire, quelle API, le piège, comment vérifier, et la « réponse
jury » d'une phrase.

> Rien n'est codé à ta place ici : signatures, flux et markup-cible seulement. Les corps
> de méthodes, le branchement et le wrapper JS sont à écrire toi-même (c'est court).

## Le point de départ (la question jury n°1)

Le cookie de session est déjà en `SameSite=Lax` + `HttpOnly` (`public/index.php:17-21`).
**Lax bloque déjà l'essentiel des CSRF cross-site sur les POST** : un `<form>`
auto-soumis depuis `attacker.com` n'enverra pas le cookie. Le jury demandera donc :

> « Si tu as déjà SameSite=Lax, pourquoi ajouter un token ? »

**Réponse : défense en profondeur.** `SameSite` est une protection *navigateur* (vieux
navigateurs, certaines configs, et les attaques *same-site* via un sous-domaine compromis
la contournent). Le token est une protection *applicative* qui ne dépend pas du
navigateur. On **garde** `SameSite=Lax`, on ne le remplace pas.

## Décision d'architecture (le point que le jury sondera)

Trois façons de déclencher la vérification :

| Option | Où | Coût / risque |
|---|---|---|
| **A — check global sur tout POST, dans le `Router`** | `Router::match()` | ✅ Couvre les 9 POST **et tous les futurs**, zéro oubli possible |
| B — un `CsrfMiddleware` attaché route par route | `routes/*.php` | ❌ `addRoute()` ne gère **qu'un seul** middleware (`Router.php:12`) et les routes POST ont déjà `AuthMiddleware` → il faut refondre le router **et** penser à l'ajouter partout (oubli = trou) |
| C — check explicite en tête de chaque controller | 9 controllers | ❌ 9 duplications, un oubli = un trou |

**Choix retenu :** un `CsrfMiddleware` (cohérent avec le pattern `AuthMiddleware` /
`AdminMiddleware` existant) **mais déclenché par le `Router` sur toute requête POST**, pas
attaché route par route. `Auth` / `Admin` restent dans le slot par-route ; le CSRF est
orthogonal, branché sur le verbe HTTP.

> **Réponse jury :** « Toute requête mutante est vérifiée par construction — je ne peux pas
> oublier un endpoint. La sécurité par défaut bat la sécurité opt-in. Je réutilise mon
> pattern middleware, mais c'est le router qui l'arme sur tous les POST. »

**Tradeoff assumé :** c'est moins granulaire qu'un middleware par route — si un jour il
faut un POST exempté (webhook tiers signé autrement), il faudra une exception explicite.
Aujourd'hui aucun POST n'est dans ce cas, donc accepté.

**Respect du layering :** la lecture de `$_POST` est réservée aux controllers et à
`SessionController` (règle du projet). Le middleware ne touche donc **aucune
superglobale** : il délègue la lecture + comparaison à `SessionController::verifyCsrf()`
et se contente de décider `403`-ou-passe.

## Le pattern : Synchronizer Token

| Option | Pour qui | Pour Solage |
|---|---|---|
| **Synchronizer Token (STP)** | apps avec session serveur | ✅ **Retenu.** Session PHP déjà présente. Token en session, comparé à la réception. Reco OWASP n°1 quand il y a une session. |
| Double-Submit Cookie | apps *stateless* | ❌ Inférieur ici : l'état serveur existe déjà, et c'est cassable via un sous-domaine. |
| SameSite seul | tout le monde, en base | ⚠️ Déjà en place, insuffisant seul. |
| Vérif Origin/Referer | complément serveur | ⚠️ Bonus, mais `Referer` peut être absent → pas fiable seul. |

> **Réponse jury :** « J'ai une session serveur, donc Synchronizer Token : un secret stocké
> côté serveur, jamais devinable par un site tiers. Le double-submit n'aurait servi que
> sans session. »

## Les 9 POST vus de près (ça pilote les transports)

| Route | Transport client | Lecture serveur | Canal token |
|---|---|---|---|
| `/api/post` | `fetch` **FormData** (multipart) | `$_POST['data']` + fichier | header |
| `/api/like` | `fetch` **JSON** (`php://input`) | `json_decode` | header |
| `/api/users/delete` | `fetch` **JSON** | `json_decode` | header |
| `/api/posts/delete` | `fetch` **JSON** | `json_decode` | header |
| `/login` | `fetch` **urlencoded** | `$_POST` | header |
| `/register` | `fetch` **urlencoded** | `$_POST` | header |
| `/logout` | **form natif** | — | hidden input |
| `/edituser/{id}` | **form natif** multipart | `$_POST` | hidden input |
| `/api/users` → `HomepageController#test` | ? (voir plus bas) | ? | couvert d'office par l'option A |

**Le constat qui pilote tout :** 6 POST sur 8 passent par `fetch`, et la moitié envoie du
**JSON brut** (`php://input`), pas du `$_POST`. Donc pour ces JSON le token **ne peut pas**
être lu dans `$_POST` → il voyage dans un **header HTTP** (`X-CSRF-Token`). Seuls les 2
vrais `<form>` natifs (logout, edituser) ont besoin d'un `<input type="hidden">`.

→ **Header partout, hidden input pour les 2 forms. Le serveur accepte les deux.**

---

# Étape 1 — Générer et stocker le token (`SessionController`)

C'est ici car la génération touche `$_SESSION` (règle : `$_SESSION` seulement dans les
controllers et `SessionController`).

### Signature à écrire
```
SessionController::getCsrfToken(): string
```
**Comportement attendu :** si `$_SESSION['csrf_token']` est absent, le créer avec
`bin2hex(random_bytes(32))`, sinon renvoyer l'existant. Un seul token **par session**.

⚠️ **Génération** : utiliser `random_bytes` (CSPRNG). **Jamais** `uniqid()`, `rand()`,
`mt_rand()` ni `md5(time())` — ce ne sont pas cryptographiques, c'est *la* faute classique.

⚠️ **Durée de vie** : un token **par session**, pas par requête. Le par-requête casserait
la navigation multi-onglets et le bouton retour pour un gain marginal.

> **Jury :** « `random_bytes` = imprévisible cryptographiquement. Un token par session :
> standard suffisant, et compatible multi-onglets. »

**Vérif étape 1 :** appeler la méthode depuis une page, `var_dump` le token, recharger →
il doit être **stable** entre deux reloads de la même session.

---

# Étape 2 — Vérifier le token (`SessionController`)

### Signature à écrire
```
SessionController::verifyCsrf(): bool
```
**Comportement attendu :** lire le token entrant depuis le header `X-CSRF-Token`
(`$_SERVER['HTTP_X_CSRF_TOKEN']`) **ou**, à défaut, `$_POST['csrf_token']` ; le comparer à
`$_SESSION['csrf_token']` et renvoyer le résultat.

⚠️ **Comparaison** : `hash_equals($attendu, $reçu)`, **jamais `===`**. Protège contre les
*timing attacks*. Gérer le cas « pas de token en session » → `false`.

> **Jury :** « `hash_equals` compare en temps constant : on ne fuite pas l'information par
> le temps de réponse. La lecture de la requête vit dans `SessionController`, pas dans le
> middleware, pour respecter mon layering. »

**Vérif étape 2 :** depuis un controller de test, vérifier que la méthode renvoie `true`
avec le bon token (header ou champ) et `false` avec un mauvais / aucun.

---

# Étape 3 — Brancher le rejet (`CsrfMiddleware` + `Router`)

### Le middleware
Une classe `CsrfMiddleware` avec `handle()`, calquée sur `AuthMiddleware` /
`AdminMiddleware`. **Comportement :** appeler `(new SessionController())->verifyCsrf()` ;
si `false` → `http_response_code(403)`, `Logger::get()->warning('csrf.denied', …)` (comme
`AdminMiddleware.php:13` le fait pour les accès refusés), puis `exit`.

### Le déclenchement (le cœur de l'option A)
Dans `Router::match()` (`Router.php:26`), quand `$_SERVER['REQUEST_METHOD'] === 'POST'`,
instancier et exécuter `CsrfMiddleware` **avant** de dispatcher le controller — donc avant
ou au moment où la route est résolue. Aucun changement à `addRoute()` ni aux routes.

⚠️ Garder l'exécution des middlewares de route (`Auth`/`Admin`) telle quelle : le CSRF
s'ajoute, il ne les remplace pas.

> **Jury :** « Le router arme le CSRF sur tout POST. Auth/Admin restent par-route ; le CSRF
> est transversal à toutes les mutations. »

**Vérif étape 3 :** un POST **sans** token doit renvoyer `403` ; un GET passe normalement.
À partir d'ici, **toute l'app est volontairement cassée en POST** jusqu'à l'étape 6 — c'est
le signe que le rejet fonctionne.

---

# Étape 4 — Exposer le token côté client (les 2 layouts)

Une balise meta dans le `<head>`, alimentée par `SessionController::getCsrfToken()` :
```html
<meta name="csrf-token" content="<?= … getCsrfToken() échappé … ?>">
```
À placer dans **les deux** layouts : `LayoutView` (app) **et** `LoginRegisterLayoutView`
(sinon login/register enverront un token vide → 403, voir « À signaler »).

> **Jury :** « Le token est exposé une fois dans le `<head>` ; tout le JS le lit de là. »

**Vérif étape 4 :** View-source des pages app **et** login → la meta est présente et
remplie.

---

# Étape 5 — Canal `fetch` (header `X-CSRF-Token`)

**Un seul** point d'injection JS couvre les 6 endpoints `fetch` (post, like, les 2 delete,
login, register). Lire la meta une fois :
`document.querySelector('meta[name="csrf-token"]').content`, et l'ajouter comme header
`X-CSRF-Token` à **chaque** appel `fetch`.

Deux manières (au choix, à écrire toi-même) :
- une fonction maison `postWithCsrf(url, options)` appelée partout, **ou**
- envelopper le `fetch` natif pour injecter le header par défaut.

⚠️ Ne **pas** toucher à la structure des bodies : ni au `FormData` de `/api/post`
(`index.js:94`), ni aux JSON de like/delete, ni à l'urlencoded de login/register
(`dynamicMessages.js`). Tout le secret passe par le header — c'est l'intérêt de ce canal.

**Vérif étape 5 :** like, création de post, suppression, login : tout doit remarcher.
Network → la requête porte bien `X-CSRF-Token`.

---

# Étape 6 — Canal form natif (hidden input)

Pour les 2 `<form>` réellement soumis par le navigateur — logout (`UserView.php:29`) et
edituser (`EditUserView.php:17`) — un champ caché :
```html
<input type="hidden" name="csrf_token" value="<?= … échappé … ?>">
```
Pour rester DRY, un helper `Utils::csrfField()` qui rend la ligne complète (même esprit que
`Utils::e()` déjà présent).

**Vérif étape 6 :** déconnexion et édition de profil fonctionnent à nouveau. L'app est de
nouveau pleinement opérationnelle, désormais protégée.

---

# Étape 7 — Tester l'attaque (la démo jury)

Rejouer un POST avec un **mauvais** token (DevTools → modifier le header, ou `curl` avec un
`X-CSRF-Token` bidon) sur `/api/like` ou `/api/posts/delete`. Résultat attendu : **403**,
ligne `csrf.denied` dans les logs. C'est la preuve à montrer au jury.

> **Jury :** « Voici une requête légitime acceptée, et la même avec un token falsifié :
> rejetée en 403. La mutation est impossible sans le secret de session. »

---

## Les détails qui font gagner des points

- **CSPRNG** : `random_bytes(32)` + `bin2hex`, pas `uniqid`/`rand`/`md5(time())`.
- **Timing-safe** : `hash_equals()`, pas `===`.
- **Par session**, pas par requête → multi-onglets OK.
- **Échec = `403` + log** `csrf.denied`, cohérent avec `AdminMiddleware`.
- **GET sans effet de bord** : toutes les mutations sont déjà en POST (like, delete, edit).
  Le token ne s'applique qu'aux POST — correct *parce que* les GET ne mutent rien.

## À signaler avant de coder

- **Login CSRF** : le token doit exister **avant** la connexion. C'est OK — `session_start()`
  tourne pour tout le monde (`public/index.php:59`), donc une session anonyme a déjà un
  token, **à condition** que la meta soit bien dans `LoginRegisterLayoutView` (étape 4).
  Sinon login/register enverront un token vide → 403.
- **Connexe, hors périmètre** : `login()` ne fait pas de `session_regenerate_id(true)`
  (`SessionController.php:21`) → c'est de la *session fixation*, pas du CSRF. Le token
  survit au regenerate (il vit dans `$_SESSION`). À traiter séparément, mais savoir
  l'expliquer si on demande.
- **`POST /api/users → HomepageController#test`** (`routes/api.php:3`) : le nom `#test` sent
  le code mort / debug. Avec l'option A il est protégé d'office (pas de trou), mais vérifier
  s'il a encore une raison d'exister. Ne pas le supprimer sans décision.

## Ordre de bataille résumé

| # | Fichier(s) | Effet | Vérif |
|---|---|---|---|
| 1 | `SessionController` (`getCsrfToken`) | génère le secret | token stable entre 2 reloads |
| 2 | `SessionController` (`verifyCsrf`) | compare le secret | true/false selon le token |
| 3 | `CsrfMiddleware` + `Router::match()` | **arme le rejet** | POST sans token = 403, GET passe |
| 4 | `LayoutView` + `LoginRegisterLayoutView` | expose le token | meta présente (app + login) |
| 5 | wrapper JS (`X-CSRF-Token`) | couvre les 6 `fetch` | like/post/delete/login remarchent |
| 6 | `Utils::csrfField()` + logout & edituser | couvre les 2 forms | logout + edit profil remarchent |
| 7 | — | démo d'attaque | POST falsifié = 403 |

Avancer étape par étape. Entre 3 et 6 l'app est cassée en POST **par conception** :
le rejet est posé avant l'émission des tokens.

## Récap des artefacts

- `SessionController::getCsrfToken(): string` + `verifyCsrf(): bool` (PHP, session)
- `CsrfMiddleware`, déclenché par `Router::match()` sur tout POST (PHP)
- `<meta name="csrf-token">` dans les 2 layouts (PHP/HTML)
- Injection du header `X-CSRF-Token` dans tous les `fetch` (JS)
- `Utils::csrfField()` + `<input type="hidden" name="csrf_token">` dans les 2 forms natifs
- On conserve `SameSite=Lax` (défense en profondeur, pas remplacé)
