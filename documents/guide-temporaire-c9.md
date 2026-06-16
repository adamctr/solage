# Guide temporaire — C9 · Tests (PHPUnit) — checklist d'exécution

> **Ce que c'est :** le document de travail que tu gardes ouvert pendant que tu codes la
> suite de tests. Ordre d'exécution, commandes exactes, squelettes à remplir, vérif à chaque
> pas, cases à cocher. Le **« pourquoi » et les réponses jury** détaillées sont dans
> `plan-de-tests-guide.md` — ici on ne répète pas, on **fait**.
>
> **Tu codes toi-même les corps de test.** Je te donne le squelette, les assertions à
> utiliser, le piège et la vérif. La plomberie (commande Composer, `phpunit.xml`,
> `bootstrap.php`, `DatabaseTestCase`) est dans le guide de référence — recopie-la telle
> quelle.
>
> **État de départ :** aucun dossier `tests/`, PHPUnit pas installé. Seule compétence « à
> développer ».

> **Avancement — 2026-06-16 : périmètre C9 terminé ✅** (Étapes 0→4).
> - **Unitaires + intégration** : `40 tests, 62 assertions` verts (Utils, CsrfHelper,
>   SessionManager au mock, UserModel/UserValidator, **PostModel**, injection SQL sur SearchModel).
> - **Fonctionnel** : T13 CSRF → 403 + `csrf.token.rejected` ; T14 IDOR (Bob→Alice) → 403 +
>   `post.delete.forbidden` ; jeu d'essai « Publier un message » OK (positif + négatif).
> Toutes les captures sont dans l'**Annexe** en fin de document. **Dossier LaTeX à jour** :
> `04e-tests.tex` et l'annexe « jeux de tests » remplis (3 encadrés « À DÉVELOPPER » résorbés),
> `main.pdf` recompilé (45 p.).

---

## ⚠️ Faits vérifiés contre le code réel (à connaître avant de taper)

Ces points te font gagner le premier essai vert — ils ne sont pas évidents.

1. **PHPUnit 11** — image/prod en PHP 8.3 (`Dockerfile:2`), ton hôte en PHP 8.2 ; PHPUnit 11
   est compatible des deux (requiert PHP ≥ 8.2).
2. **`Database` n'est PAS dans l'autoloader.** `includes/autoload.php` balaie `routes/`,
   `modules/{views,models,controllers,validators}/`, `src/` — **pas `includes/`**. Le
   bootstrap de test doit faire `require .../includes/database.php` (sinon
   `Class "Database" not found` en intégration).
3. **`Utils::e` utilise `ENT_HTML5`** (`Utils.php:45`) → l'apostrophe devient **`&apos;`**,
   pas `&#039;`. Asserter `&#039;` fait rougir un code correct.
4. **`UserValidator` instancie `new UserModel()` dans la méthode** (`UserValidator.php:24` et
   `:50`) → **non mockable** → testable seulement en intégration.
5. **`UserModel::__construct` appelle `Database::getConnection()` sans condition**
   (`UserModel.php:26`) → en test au mock, `createMock(UserModel::class)` **saute** ce
   constructeur (c'est tout l'intérêt de la DI). Ne retourne jamais un vrai `new UserModel`
   depuis un stub.
6. **Colonnes NOT NULL pour les INSERT d'intégration :**
   - `users` : `name`, `email` (UNIQUE), `password` obligatoires ; `firstname`, `role`,
     `image` nullables.
   - `posts` : `user_id`, **`date` (NOT NULL)**, `content`. **Oublier `date` casse l'INSERT** —
     c'est le piège n°1.
7. **Comptes du seed** (`seed.sql`, mdp commun **`demo1234`**, bcrypt coût 10) :
   - **Alice = id 1**, Admin (`admin@solage.demo`), auteure des posts **1, 10, 20**.
   - **Bob = id 2**, **Modérateur** (`mod@solage.demo`) — *pas* Admin → idéal pour l'IDOR.
   - `getName()` renvoie la colonne `name` = le **nom de famille** (`Garnier` pour Bob) ; le
     prénom est dans `firstname`. Sans importance pour les tests, mais à savoir.
8. **Ordre des middlewares dans le routeur** (`Router.php:47` puis `:54`) :
   `middleware de route` **PUIS** `CsrfMiddleware` (sur tout POST). Conséquence directe sur
   les démos (voir Étape 4).
9. **Correction du guide de référence :** sa démo CSRF cible `/api/like`, qui a
   `AuthMiddleware`. Non connecté → **redirection 302 vers /login**, *pas* un 403. La démo
   CSRF propre est **`POST /login`** (aucun middleware). Détaillé Étape 4.

---

## Étape 0 — Environnement (la plomberie)

> Fichiers complets dans `plan-de-tests-guide.md` §0.3 (`tests/bootstrap.php`) et §0.4
> (`phpunit.xml`). Recopie-les tels quels — il n'y a qu'une bonne façon de les écrire.

- [x] Installer la dépendance dev (comme phpcs) :
  ```powershell
  composer require --dev "phpunit/phpunit:^11"
  ```
- [x] (optionnel, pratique) ajouter dans `composer.json` :
  ```json
  "scripts": { "test": "phpunit" }
  ```
- [x] Créer `tests/bootstrap.php` — **3 require dans l'ordre** : `vendor/autoload.php` (Composer)
  → `includes/autoload.php` + `Autoloader::register()` → `includes/database.php`
  (cf. §0.3). Le point qui compte : tes tests chargent les classes de l'app avec **le même
  autoloader que la prod**, zéro divergence.
- [x] Créer `phpunit.xml` à la racine (`bootstrap="tests/bootstrap.php"`,
  `cacheDirectory=".phpunit.cache"`, suite `tests`) — cf. §0.4.
- [x] Créer le dossier `tests/`.
- [x] Ajouter au **`.gitignore`** :
  ```
  .phpunit.cache/
  ```
- [x] **Smoke test** : `tests/SmokeTest.php` avec une seule assertion `assertTrue(true)`.
  ```powershell
  php vendor/bin/phpunit
  ```
  → **1 test, 1 assertion, OK (vert)**. Si vert : l'environnement est bon → **supprime** le
  smoke test.

### Décision : où tournent les tests ? (tu es sous Windows + Docker)

**Tout tourne sur l'hôte.** Le conteneur `app` remonte `vendor/` en volume anonyme issu du
build `--no-dev` (`docker-compose.yml:53-57`) → PHPUnit, dépendance *dev*, n'y est pas. Inutile
de se battre avec ça pour le diplôme.

| Famille | Commande | Pré-requis |
|---|---|---|
| **Purs + mock** (Étapes 1-2) | `php vendor/bin/phpunit` | `mbstring` ✓ activé |
| **Intégration** (Étape 3) | `$env:DB_HOST='localhost'; $env:DB_PORT='5434'; $env:DB_NAME='solage'; $env:DB_USER='solage'; $env:DB_PASSWORD='solage'; php vendor/bin/phpunit` | `pdo_pgsql` ✓ activé + Postgres up |

**Réglages déjà faits** (dans `C:\tools\php82\php.ini`) : `extension=mbstring` et
`extension=pdo_pgsql` décommentés (les DLL existaient déjà). Vérif : `php -m` liste les deux.

**Accès BDD — ce qui a été démêlé (à savoir) :**
- Le port hôte **5432 est pris par un PostgreSQL natif Windows** (`postgresql-x64-16`, messages
  en français), et **5433** par un autre projet. Le Postgres **Docker** du projet a donc été
  republié sur **`localhost:5434`** (`docker-compose.yml`, service `postgres`).
- **Pas de `.env`** sur l'hôte → on passe les `DB_*` au shell (cf. commande). `Database` charge
  `.env` en mode *immutable* : une variable déjà posée dans le shell gagne sur `.env`.
- Le volume Docker avait un **mot de passe inconnu** (≠ `solage`) ; réaligné sur la valeur
  documentée via le socket `trust` du conteneur : `ALTER ROLE solage PASSWORD 'solage'`.
  Identifiants désormais : `solage`/`solage`/`solage`.

> **Note runtime :** sous FrankenPHP `pdo_pgsql` renvoie les entiers en chaînes ; en CLI (ton
> hôte) ils peuvent revenir en `int`. Nos assertions passent par les getters qui castent
> (`getId()`→int, `getRole()`→?string) → stables quel que soit le runtime. La fidélité au
> runtime web reste assurée par le smoke test bout-en-bout déjà au dossier.
>
> **Réponse jury :** « La suite tourne sur l'hôte en une commande ; l'intégration vise la
> Postgres dev exposée en local. Les assertions passent par des getters typés, donc stables
> malgré la nuance de typage pdo_pgsql entre CLI et FrankenPHP. »

---

## Étape 1 — Unitaires purs (sans BDD) · `tests/UtilsTest.php`, `tests/CsrfHelperTest.php`

> Détail des cas + réponses jury : `plan-de-tests-guide.md` §1-3. Le **squelette PHPUnit
> complet** (la forme `#[Test]`, `TestCase`) est en §1 — recopie-le une fois, réutilise
> partout.

### 1a — `UtilsTest` : `Utils::e()` (anti-XSS), `isAjax()`, `sendResponse()`

- [x] **`Utils::e`** — cas à écrire (mêmes outils, entrées différentes) :

  | Cas | Entrée | Assert |
  |---|---|---|
  | `<script>` | `<script>alert(1)</script>` | `assertStringNotContainsString('<script>', $out)` + contient `&lt;script&gt;` |
  | Chevrons | `<b>x</b>` | plus de `<b>`, contient `&lt;b&gt;` |
  | Guillemets | `il a dit "oui"` | contient `&quot;` |
  | Apostrophe | `O'Brien` | contient **`&apos;`** ⚠️ (ENT_HTML5, pas `&#039;`) |
  | Esperluette | `a & b` | contient `&amp;` |
  | `null` | `null` | `assertSame('', Utils::e(null))` |

- [x] **`Utils::isAjax`** (lit `$_SERVER`) :

  | Cas | Arrange | Assert | Piège |
  |---|---|---|---|
  | vrai | `$_SERVER['HTTP_X_REQUESTED_WITH']='XMLHttpRequest'` | `assertTrue(...)` | nettoie `$_SERVER` en `tearDown` |
  | faux | en-tête absent | `assertFalse(...)` | — |

- [x] **`Utils::sendResponse`** (fait un `echo` → capture la sortie) :

  | Cas | Act | Assert |
  |---|---|---|
  | forme JSON | `ob_start(); Utils::sendResponse(true,'ok'); $j=ob_get_clean();` | `json_decode($j,true) === ['success'=>true,'message'=>'ok']` |
  | data falsy omis | `sendResponse(true,'ok',[])` | la clé `data` est **absente** (`if ($data)` faux pour `[]`, `Utils.php:22`) — à documenter, pas à « corriger » |

- [x] Vérif : `php vendor/bin/phpunit --filter UtilsTest` → vert.

### 1b — `CsrfHelperTest` : token + vérification

> `CsrfHelper` ne touche que `$_SESSION` (un simple tableau qu'on pilote). Aucune base, aucun
> `session_start`. **Isolation obligatoire** : `protected function setUp(): void { $_SESSION = []; }`.

- [x] Cas à écrire (`src/CsrfHelper.php`) :

  | # | Méthode | Arrange | Act | Assert |
  |---|---|---|---|---|
  | 1 | `getToken` | session vide | `getToken()` | longueur **64** + `ctype_xdigit()` vrai |
  | 2 | `getToken` stable | session vide | appeler **2×** | `assertSame($a, $b)` |
  | 3 | `verifyToken` bon | `$_SESSION['csrf_token']='abc'` | `verifyToken('abc')` | `assertTrue` |
  | 4 | mauvais | idem | `verifyToken('xyz')` | `assertFalse` |
  | 5 | vide | idem | `verifyToken('')` | `assertFalse` |
  | 6 | null | idem | `verifyToken(null)` | `assertFalse` |
  | 7 | sans session | `$_SESSION=[]` | `verifyToken('abc')` | `assertFalse` (le `!empty(...)` de `verifyToken`) |
  | 8 | `field` | session vide | `field()` | contient `name="csrf_token"` + le token |

  > ⚠️ Longueur **64** car `bin2hex(random_bytes(32))` = 32 octets → 64 hex. Le timing-safe de
  > `hash_equals` ne se teste pas en unitaire : asserte la correction (bon/mauvais), **dis** que
  > c'est `hash_equals` (temps constant).

- [x] Vérif : `php vendor/bin/phpunit --filter CsrfHelperTest` → vert.

---

## Étape 2 — Unitaire au mock (le payoff de la DI) · `tests/SessionManagerTest.php`

> `SessionManager` reçoit `UserModel` **par le constructeur** (`SessionManager.php:13`) → on
> passe un **mock** → **zéro BDD**. Détail + réponses jury : `plan-de-tests-guide.md` §4.
> `setUp` : `$_SESSION = [];`.

- [x] **Cas 1 — `login()` peuple la session** (login lit `getName/getImage/getRole`,
  `SessionManager.php:35-37`) :
  - *Arrange :* `$user = $this->createMock(UserModel::class);` puis stub `getName()→'Garnier'`,
    `getImage()→'🛡️'`, `getRole()→'3'`. `$model = $this->createMock(UserModel::class);` stub
    `getUserById(2)→$user`.
  - *Act :* `$sm = new SessionManager($model); $sm->login(2);`
  - *Assert :* `$_SESSION['user_id']===2`, `$_SESSION['name']==='Garnier'`,
    `assertTrue($sm->isLoggedIn())`, `assertSame($user, $sm->getUser())`.

- [x] **Cas 2 — `isAdmin()` vrai/faux** (isAdmin lit `getRoleName()`, `SessionManager.php:76`) :
  - *Arrange :* `$_SESSION['user_id']=1;` `$admin = createMock(UserModel)` stub
    `getRoleName()→'Admin'` ; `$model` stub `getUserById(1)→$admin`.
  - *Act :* `$sm = new SessionManager($model);` (le constructeur charge l'user depuis la session).
  - *Assert :* `assertTrue($sm->isAdmin())`. Refaire avec `getRoleName()→'Modérateur'` →
    `assertFalse` (reflète le seed : Bob est Modérateur, pas Admin).

  > ⚠️ **Pièges :**
  > - Le stub `getUserById` retourne un **mock** de `UserModel`, jamais `new UserModel($row)`
  >   (qui rappellerait `Database::getConnection()`).
  > - Cas 1 : `$_SESSION=[]` → le constructeur n'appelle **pas** `getUserById` (pas d'attente
  >   surprise). Cas 2 : on pose `user_id` **avant** car on **veut** qu'il l'appelle.
  > - `session_start()` tourne dans le constructeur. Parade retenue dans le code livré : le
  >   `setUp` **active la session d'abord** (`session.use_cookies=0`) puis pose les valeurs →
  >   le constructeur ne relance pas `session_start()` et ne réinitialise donc pas `$_SESSION`.

- [x] Vérif : `php vendor/bin/phpunit --filter SessionManagerTest` → vert, **sans BDD**.

> **Réponse jury (le contraste à placer) :** « `SessionManager` reçoit son modèle injecté → je
> le teste au mock, sans base. `UserValidator` instancie le sien en dur → je ne peux le tester
> qu'en intégration. La testabilité est une conséquence du design. »

---

## Étape 3 — Intégration (vraie Postgres, transaction + rollback)

> Tourne sur l'hôte avec la base ciblée (commande complète dans la table de l'Étape 0, port
> **5434**). La classe de base `tests/Integration/DatabaseTestCase.php` (begin/rollback autour
> de chaque test) est **livrée**. Principe : `Database` est un singleton → modèles et test
> partagent la même transaction → rollback = base intacte (vérifié : la base reste à
> 11 users / 48 posts après la suite).

### Arrange réutilisable (la seule plomberie que j'ajoute, à cause des NOT NULL)

Insère tes données **dans la transaction** via PDO direct (pas via `createUser`, voir piège) :

```php
// dans un test extends DatabaseTestCase ($this->db = la connexion en transaction)

// --- un utilisateur ---
$stmt = $this->db->prepare(
    'INSERT INTO users (name, email, password, role)
     VALUES (:n, :e, :p, :r) RETURNING id'
);
$stmt->execute([
    ':n' => 'Test',
    ':e' => 't@test.io',
    ':p' => password_hash('secret', PASSWORD_BCRYPT),
    ':r' => 2,
]);
$userId = (int) $stmt->fetchColumn();   // RETURNING id : plus fiable que lastInsertId() en pgsql

// --- un post (date est NOT NULL : ne l'oublie pas) ---
$this->db->prepare(
    'INSERT INTO posts (user_id, date, content) VALUES (:u, :d, :c)'
)->execute([
    ':u' => $userId,
    ':d' => '2026-01-01 12:00:00',
    ':c' => 'Bonjour le monde',
]);
```

### 3a — `tests/Integration/UserModelTest.php extends DatabaseTestCase`

- [x] Cas à écrire (arrange = INSERT ci-dessus dans la transaction) :

  | # | Cas | Act | Assert |
  |---|---|---|---|
  | 1 | lecture par email | `(new UserModel())->getUserByEmail('t@test.io')` | non null, `getEmail()==='t@test.io'` |
  | 2 | email inconnu | `getUserByEmail('nope@x.io')` | `assertNull(...)` |
  | 3 | **mdp hashé (sécu)** | lire `getPassword()` du user inséré | **≠ `'secret'`** ET `password_verify('secret', $hash)===true` |
  | 4 | validateur login OK | `UserValidator::login('t@test.io','secret')` | `['ok'=>true, ...]` |
  | 5 | login mauvais mdp | `UserValidator::login('t@test.io','faux')` | `ok=false`, message « ne correspond pas » |
  | 6 | login email absent | `UserValidator::login('nope@x.io','x')` | `ok=false`, « n'existe pas » |
  | 7 | register email pris | `UserValidator::register('t@test.io','x')` | `ok=false`, « déjà utilisé » |
  | 8 | register nouveau | `UserValidator::register('libre@x.io','x')` | `ok=true` |
  | 9 | champs vides | `login('','')` / `register('','')` | `ok=false`, « renseigner vos informations » |

  > Messages **exacts** à matcher (`UserValidator.php`) : `"Merci de renseigner vos
  > informations"`, `"L'email n'existe pas"`, `"Le mot de passe ne correspond pas"`,
  > `"L'email est déjà utilisé"`. Préfère `assertStringContainsString` sur un fragment stable.

  > ⚠️ **Piège majeur — `createUser()` n'est pas testable tel quel** : il fait
  > `include 'assets/emojiList.php'` (`UserModel.php:182`), **chemin relatif au CWD**. En prod
  > le docroot est `public/` ; en PHPUnit le CWD est la racine → fichier introuvable →
  > l'insertion casse. **D'où l'INSERT PDO direct ci-dessus.** C'est un vrai couplage au
  > docroot que le test révèle → à signaler (voir « Findings »).

- [x] Vérif : `php vendor/bin/phpunit --filter UserModelTest` (préfixe `DB_*` de la table Étape 0) → vert.

### 3b — `tests/Integration/SqlInjectionTest.php extends DatabaseTestCase` (sécu)

> Prouve que les requêtes préparées rendent une charge d'injection **inerte**. Cible :
> `SearchModel::searchPosts()` qui entoure le terme de `%...%` puis `WHERE p.content LIKE ?`
> (`SearchModel.php:72-85`).

- [x] Arrange : INSERT 1 user + 1 post au contenu **unique** (un marqueur, pour rester
  déterministe face au seed) dans la transaction.
- [x] **Cas A (charge inerte)** : `(new SearchModel())->searchPosts("' OR '1'='1")`
  → le `LIKE` cherche littéralement `%' OR '1'='1%` → **0 résultat**, **aucune exception**.
  `assertCount(0, $resultats)`.
- [x] **Cas B (la recherche marche)** : `searchPosts(<marqueur unique>)` → **1 résultat**
  (`assertCount(1, ...)`).

  > A+B ensemble = l'entrée est traitée comme **donnée**, jamais comme **SQL**. Si l'injection
  > marchait, A renverrait *toutes* les lignes.

- [x] Vérif : `php vendor/bin/phpunit --filter SqlInjectionTest` (préfixe `DB_*` de la table Étape 0) → vert.

---

## Étape 4 — Fonctionnels (curl, app lancée) — les 2 démos sécu

> Ce qui dépend de `$_SERVER` / `php://input` / statics / `exit` ne s'automatise pas
> proprement → on le valide **fonctionnellement** et on **capture la preuve** (statut + ligne
> de log). Adapte `http://localhost` à ton URL dev (Traefik).

### T13 — CSRF : un POST sans token → 403 (route **sans** middleware)

> **Pourquoi `/login` et pas `/api/like` :** sur `/api/*`, `AuthMiddleware` s'exécute **avant**
> le CSRF (`Router.php:47` puis `:54`). Non connecté, tu reçois une **302 → /login**, pas le
> 403. `POST /login` n'a aucun middleware → le CsrfMiddleware est la première barrière.

- [x] Démo :
  ```bash
  curl -i -X POST http://localhost/login
  ```
- [x] Attendu : **`HTTP/1.1 403`**, corps `403 — Token CSRF refusé.` (`CsrfMiddleware.php:19`),
  ligne **`csrf.token.rejected`** dans les logs (`CsrfMiddleware.php:14`).
- [x] Logs : `docker compose logs app` (le Logger écrit les `warning` sur **stderr**,
  `Logger.php:53`).

**✅ Obtenu (2026-06-16) :** `HTTP/1.1 403 Forbidden` · corps `403 — Token CSRF refusé.` · log
`{"level":"warning","msg":"csrf.token.rejected","context":{"uri":"/login"}}`. **Conforme.**

### T14 — IDOR : Bob supprime un post d'Alice → 403

> `PostController::delete` vérifie *propriétaire-ou-admin* (`PostController.php:153`). La route
> `/api/posts/delete` a `AuthMiddleware` **puis** CSRF **puis** le contrôleur → pour atteindre
> le 403 IDOR, Bob doit être **connecté** ET fournir un **token CSRF valide**. (Bob est
> Modérateur, pas Admin → la condition `!isAdmin()` est vraie → 403.)

- [x] Se connecter en **Bob** (`mod@solage.demo` / `demo1234`).
- [x] Récupérer ses identifiants de session (DevTools, ou — comme ici — via un cookie jar curl :
  `GET /login` pose le `PHPSESSID` **et** expose le token, qu'on rejoue ensuite) :
  - cookie **`PHPSESSID`** (onglet Application → Cookies),
  - **token CSRF** : en console, `document.querySelector('input[name=csrf_token]').value`.
- [x] Choisir un **postId d'Alice** (seed : `1`, `10` ou `20`, tous `user_id = 1`).
- [x] Démo (remplace `<SID>` et `<TOKEN>`) :
  ```bash
  curl -i -X POST http://localhost/api/posts/delete \
    -b "PHPSESSID=<SID>" \
    -H "X-CSRF-Token: <TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"postId": 1}'
  ```
- [x] Attendu : **`HTTP/1.1 403`**, JSON `Vous n'avez pas la permission de supprimer ce post.`
  (`PostController.php:160`), ligne **`post.delete.forbidden`** dans les logs
  (`PostController.php:154`).

**✅ Obtenu (2026-06-16) :** `HTTP/1.1 403 Forbidden` · JSON `{"success":false,"message":"Vous
n'avez pas la permission de supprimer ce post."}` · log `{"msg":"post.delete.forbidden",
"context":{"current_user_id":2,"post_owner_id":1,"post_id":1}}` (Bob=2 vise le post d'Alice=1).
**Conforme.**

> ⚠️ Si tu obtiens `403 — Token CSRF refusé.` au lieu du JSON IDOR : ton token CSRF est faux
> → tu t'es arrêté à la barrière CSRF avant d'atteindre le contrôleur. Re-copie le token.
> Alternative à curl : rejoue en console avec `fetch('/api/posts/delete', {method:'POST',
> headers:{'Content-Type':'application/json','X-CSRF-Token': <token>}, body:
> JSON.stringify({postId:1})}).then(r=>r.status)` → `403`.

- [x] **Captures archivées** : les 2 sorties `-i` (statut 403) + les 2 lignes de log — voir
  l'**Annexe — captures d'exécution** en fin de document.

---

## Le cahier de tests T01-T14 (artefact « préparé » → à reprendre dans le dossier)

Colonnes **Obtenu** / **Écart** remplies **à l'exécution**. À recopier dans `04e-tests.tex`
+ annexes. **Tout exécuté le 2026-06-16 → conforme.** T01-T12 + T15 : 40 tests / 62 assertions verts
(PHPUnit). T13-T14 : démos fonctionnelles 403 + lignes de log (cf. **Annexe — captures**).

| ID | Fonctionnalité | Cas | Entrée | Attendu | Type | Obtenu | Écart |
|---|---|---|---|---|---|---|---|
| T01 | Anti-XSS | échappe `<script>` | `<script>…` | sortie encodée `&lt;…` | unit | ✅ vert | — |
| T02 | Anti-XSS | apostrophe/guillemets/`&`/`null` | divers | entités correctes, `null`→`''` | unit | ✅ vert | — |
| T03 | CSRF token | génération | session vide | 64 hex, stable/session | unit | ✅ vert | — |
| T04 | CSRF token | vérification | bon/mauvais/vide/nul/sans-session | true / false×4 | unit/sécu | ✅ vert | — |
| T05 | Transport JSON | `sendResponse` | `(true,'ok')` | `{success,message}` | unit | ✅ vert | — |
| T06 | Session | `login()` peuple la session | userId mocké | `$_SESSION` rempli, `isLoggedIn` | unit (mock) | ✅ vert | — |
| T07 | Autorisation | `isAdmin()` | rôle Admin / non-Admin | true / false | unit (mock) | ✅ vert | — |
| T08 | Comptes | lecture par email | email connu / inconnu | user / null | intég. | ✅ vert | — |
| T09 | Mot de passe | stockage hashé | mdp `secret` | hash ≠ clair, `verify` ok | sécu (intég.) | ✅ vert | — |
| T10 | Connexion | validateur login | bon / mauvais mdp / email absent / vide | ok / 3× erreurs | intég. | ✅ vert | — |
| T11 | Inscription | validateur register | email pris / libre / vide | erreur / ok / erreur | intég. | ✅ vert | — |
| T12 | Recherche | injection SQL inerte | `' OR '1'='1` + terme normal | 0 résultat / 1 résultat | sécu (intég.) | ✅ vert | — |
| T13 | CSRF (bout-en-bout) | POST sans token | curl `/login` | 403 + log `csrf.token.rejected` | fonctionnel | ✅ vert | — |
| T14 | IDOR | suppression non-propriétaire | Bob → post d'Alice | 403 + log `post.delete.forbidden` | fonctionnel | ✅ vert | — |
| T15 | Accès données | PostModel CRUD | créer / lire / supprimer un post | id retourné / post lu / supprimé | intég. | ✅ vert | — |

### Jeu d'essai détaillé — « Publier un message »

| Champ | Valeur (vérifiée sur `PostController::create`) |
|---|---|
| **Fonctionnalité** | Création d'un post — `POST /api/post` (auth + CSRF requis) |
| **Pré-condition** | utilisateur connecté (`AuthMiddleware`), token CSRF présent |
| **Entrée** | champ form **`data`** = JSON `{"content":"Bonjour le monde","replyTo":0,"replyToParent":0}`, sans image (le contrôleur lit `$_POST['data']`, `PostController.php:22`) |
| **Étapes** | 1. se connecter · 2. soumettre le formulaire de post · 3. observer la réponse JSON |
| **Attendu** | JSON `success=true`, un `id` numérique, le post en tête de feed |
| **Obtenu** | `success=true`, `id=53`, `username=Garnier` (2026-06-16) — JSON complet en Annexe |
| **Écart** | aucun — conforme. Cas négatif `content=""` → `success=false`, « Données invalides ou contenu vide ». Post de test supprimé après coup (base propre). |
| **Cas négatif** | `content=""` → `success=false`, message **`Données invalides ou contenu vide`** (`PostController.php:69`) |

---

## Traces d'exécution à capturer (la preuve « exécuter »)

- [x] Suite complète verte, format cahier :
  ```powershell
  php vendor/bin/phpunit --testdox
  ```
  (préfixe `$env:DB_*` de l'Étape 0 pour inclure l'intégration) → **40 tests, 62 assertions,
  OK** (cf. Annexe).
- [x] Les **2 démos 403** (T13, T14) : sortie `curl -i` + ligne de log (cf. Annexe).
- [x] Ranger ces captures dans les annexes du dossier (`annexes.tex`) + résumé dans
  `04e-tests.tex` → fait ; `main.pdf` recompilé (45 p.).

---

## Findings à signaler au jury (un plan de tests SERT à les trouver)

L'honnêteté sur ces points rapporte des points.

- **Correction du guide de référence** : la démo CSRF doit viser `POST /login`, pas
  `/api/like` (sinon `AuthMiddleware` renvoie 302 avant le CSRF). Documenté ici, Étape 4.
- **`createUser()` couplé au docroot** (`UserModel.php:182`) : `include 'assets/emojiList.php'`
  relatif au CWD → casse hors contexte web. Révélé par l'Étape 3a. Correctif (chemin absolu)
  hors périmètre.
- **`getNameFromId()` plante sur id inconnu** (`UserModel.php:128`) : `return $row->name;`
  sans vérifier `$row`. Correctif trivial (`$row ? $row->name : null`) hors périmètre.
- **`sendResponse` omet `data` falsy** (`Utils.php:22`) : `if ($data)` faux pour `[]`/`0`/`''`.
  Comportement connu (test T05), pas forcément un bug.
- **`Router::match()` non unitaire** : la regex `{param}` est inline (`Router.php:40`).
  L'extraire (`resolve()`) la rendrait testable — refactor noté, non requis pour C9.

---

## Checklist finale (mappée sur la roadmap)

- [x] PHPUnit ^11 installé + `phpunit.xml` + `tests/bootstrap.php` + `.phpunit.cache/` ignoré +
  smoke test vert
- [x] Unitaires purs : `UtilsTest` (`e`, `isAjax`, `sendResponse`) + `CsrfHelperTest`
- [x] Unitaire au mock : `SessionManagerTest` (DI, zéro BDD)
- [x] Intégration : `UserModelTest` (+ `UserValidator`, mdp hashé) + `SqlInjectionTest`
  (`SearchModel`)
- [x] Fonctionnel : CSRF `POST /login` → 403 + log ; IDOR Bob→Alice → 403 + log
- [x] Cahier T01-T14 rempli (tous ✅) + jeu d'essai « Publier un message » fait
- [x] Captures : suite verte `--testdox` + les 2 démos 403 → **Annexe** en fin de document
- [x] Transcription dans le dossier LaTeX (`04e-tests.tex` + annexe « jeux de tests ») + `main.pdf` recompilé (45 p.)

> **Ordre conseillé :** Étapes 0→1→2 d'abord (sans base, fiables partout) — elles constituent
> déjà un livrable C9 valable. Puis 3→4 (intégration + sécu réelle) qui le renforcent.

---

## Annexe — captures d'exécution (2026-06-16)

À transcrire / screenshoter dans le dossier (`04e-tests.tex` + annexes LaTeX). Hôte PHP 8.2,
base Docker sur `localhost:5434`.

### A1 — Suite PHPUnit (`--testdox`)

```
PHPUnit 11.0.0 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.2.0
Configuration: C:\Users\Adam\Desktop\_Projets\solage\phpunit.xml

.................................................................. 40 / 40 (100%)

Csrf Helper  ✔ ×8    Post Model  ✔ ×6    Session Manager  ✔ ×3
Sql Injection ✔ ×2   User Model  ✔ ×10   Utils            ✔ ×11

OK (40 tests, 62 assertions)
```

### A2 — T13 · CSRF : `POST /login` sans token → 403

```
$ curl -i -X POST http://localhost/login
HTTP/1.1 403 Forbidden
Content-Type: text/plain; charset=utf-8
Server: Caddy
X-Powered-By: PHP/8.3.19

403 — Token CSRF refusé.
```
Log (stderr) :
```json
{"ts":"2026-06-16T12:12:46+00:00","level":"warning","msg":"csrf.token.rejected","context":{"uri":"/login"}}
```

### A3 — T14 · IDOR : Bob (id 2) supprime le post d'Alice (id 1) → 403

```
HTTP/1.1 403 Forbidden
Content-Type: application/json

{"success":false,"message":"Vous n'avez pas la permission de supprimer ce post."}
```
Log (stderr) :
```json
{"ts":"2026-06-16T12:14:23+00:00","level":"warning","msg":"post.delete.forbidden","context":{"current_user_id":2,"post_owner_id":1,"post_id":1}}
```

### A4 — Jeu d'essai « Publier un message » (`POST /api/post`)

Cas valide :
```json
{"success":true,"message":"Succès lors de la création du post","data":{"id":"53","user":2,"username":"Garnier","content":"Bonjour le monde (jeu d'essai C9)","date":"2026-06-16 12:14:24","reply_to":null,"image":null,"reply_to_parent":null}}
```
Cas négatif (`content=""`) :
```json
{"success":false,"message":"Données invalides ou contenu vide"}
```
Nettoyage (Bob supprime son propre post 53 → delete *happy-path*) :
```
HTTP/1.1 200 OK
{"success":true,"message":"Post supprimé avec succès"}
```

> **Méthode :** T13/T14 et le jeu d'essai sont rejoués en `curl` avec un *cookie jar*
> (`GET /login` → `PHPSESSID` + token CSRF, rejoués sur les POST). La base reste propre : le
> seul post créé (id 53) a été supprimé juste après. Suite PHPUnit relançable :
> `$env:DB_HOST='localhost'; $env:DB_PORT='5434'; $env:DB_NAME='solage'; $env:DB_USER='solage'; $env:DB_PASSWORD='solage'; php vendor/bin/phpunit --testdox`.
