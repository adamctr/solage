# Problèmes / Solutions

Journal des décisions techniques prises pendant le développement. Chaque entrée suit le format **contexte → problème → options envisagées → solution retenue → justification jury**.

## Migrations 

Migrations.php appelait à chaque fois les migrations -> Mauvais cycle de requetes.

Passage à un script bin migrate.

---

## Logger applicatif (PSR-3)

### Contexte

Les erreurs PHP sont aujourd'hui remontées de manière incohérente :
- `var_dump()` dans les blocs `catch` (`LikeModel`, `PostModel`, `PostController`…)
- `error_log()` dans `Migrations.php`
- Pas de niveau, pas de contexte, pas de format unifié.

Le `var_dump` en particulier **fuite vers la réponse HTTP** en cas d'erreur, ce qui est à la fois un anti-pattern (UX) et une fuite d'information (sécurité).

### Problème

Comment unifier la journalisation applicative pour qu'elle soit :
1. **Lisible** par un humain en dev (logs Docker / dozzle / `docker compose logs`).
2. **Exploitable** par une chaîne d'agrégation en prod (Loki, ELK…).
3. **Défendable** devant un jury — pas une réinvention de roue.

### Options envisagées

| Option | Pour | Contre |
|---|---|---|
| **A. Logger custom signature maison** (`Logger::info`, `Logger::error`) | Zéro dépendance, court à écrire | "Pourquoi pas PSR-3 ?" → faiblesse en soutenance |
| **B. PSR-3 (`Psr\Log\LoggerInterface`)** + implémentation maison | Standard PHP-FIG, interopérable, dépendance triviale (`psr/log` ≈ 3 fichiers d'interface) | Une dépendance Composer en plus |
| **C. Monolog** | Bibliothèque mature, handlers tout faits | Surdimensionné pour la taille du projet, masque la mécanique pédagogique |

### Solution retenue : Option B (PSR-3 + implémentation maison)

- Implémentation d'une classe `Logger` dans `src/Logger.php` qui implémente `Psr\Log\LoggerInterface`.
- Sortie **JSON-line** (clé/valeur) sur **stdout** pour les niveaux `info`/`notice`/`debug`, **stderr** pour `warning`/`error`/`critical`/`alert`/`emergency`.
- Format compatible avec ce que FrankenPHP émet déjà → cohérence dans `docker compose logs`.
- Interpolation PSR-3 du tableau `$context` dans le message (`{key}` → valeur).

### Justification jury

> *"J'ai suivi PSR-3, le standard d'interface de la PHP-FIG. Cela me permettrait de remplacer mon implémentation par Monolog sans toucher au code appelant. J'écris en JSON sur stdout/stderr car c'est la convention Docker — les logs sont consommés par l'orchestrateur, pas écrits dans des fichiers que je devrais ensuite faire tourner."*

### Plan d'implémentation

```
1. composer require psr/log              → vérif : vendor/psr/log présent
2. src/Logger.php (PSR-3, JSON stdout)   → vérif : tests unitaires (info/error/interpolation)
3. Remplacer var_dump + error_log        → vérif : grep var_dump|error_log → 0 résultat dans modules/, src/
4. Smoke test                             → vérif : provoquer une erreur, voir le JSON propre dans docker compose logs app
```

### Statut

✅ Implémenté.

**Avant** :
```
string(249) "Erreur lors de l'insertion du like la base de données : SQLSTATE[23503]..."
```
(sortie d'un `var_dump`, fuitait dans la réponse HTTP)

**Après** :
```json
{"ts":"2026-05-06T19:50:00+00:00","level":"error","msg":"like.create.failed",
 "context":{"user_id":1,"post_id":999999,
  "exception":{"class":"PDOException",
               "message":"SQLSTATE[23503]: ...",
               "file":"/app/modules/models/LikeModel.php:30"}}}
```

### Piège rencontré : constantes `STDOUT`/`STDERR` CLI-only

Première version du Logger : `fwrite(STDOUT, ...)`. Les tests CLI (`docker compose exec app php -r ...`) passaient — mais toute requête HTTP retournait **500** : *"Undefined constant STDOUT"*.

Cause : `STDOUT` / `STDERR` / `STDIN` sont définies **uniquement par le SAPI CLI de PHP**. En SAPI HTTP (FrankenPHP, FPM, etc.), elles n'existent pas.

Fix : remplacement par `file_put_contents('php://stdout' / 'php://stderr', ..., FILE_APPEND)`. Les wrappers `php://*` fonctionnent dans tous les SAPI.

Leçon pour la défense : *"Tester en CLI ne garantit pas que ça marche en HTTP. Les deux SAPI partagent du code mais pas tous les symboles globaux."*

---

## Authentification vs autorisation : 2 failles

### `/admin` accessible à n'importe quel user loggé

**Problème** : la route `/admin` n'avait aucun check de rôle. `isAdmin()` existait mais n'était appelé nulle part.

**Solution** : `AdminMiddleware` séparé d'`AuthMiddleware` — distinction *authentication* (loggé) vs *authorization* (admin). Refus loggé en `warning`.

### IDOR sur `/edituser/{id}`

**Problème** : un user authentifié pouvait éditer le profil d'un autre en changeant `{id}` dans l'URL. Le contrôleur ne vérifiait que la session, pas la propriété.

**Solution** : check `current_user_id === target_user_id || isAdmin()` au début de `UserController::update`. Avant : `Bobby (id=8)` pouvait POST `/edituser/1` → modifie le compte admin. Après : 403 + log.

**Réponse jury** : *"Authentification ≠ autorisation. Les deux failles venaient du même réflexe — supposer que 'loggé' = 'autorisé'. Le fix sépare explicitement les deux notions."*

✅ Implémenté.

---

## XSS stocké sur le contenu des posts

**Problème** : `PostView` (et autres) affichaient `$post->getContent()`, `$user->getName()`, `$user->getImage()` **sans `htmlspecialchars`**. Un user pouvait poster `<script>alert(1)</script>` et le code s'exécutait dans le navigateur de **tous les autres users** consultant le feed (XSS stocké, OWASP A03).

**Solution** : escaping à l'**output**, pas à l'input.
- Helper `Utils::e()` (`htmlspecialchars` + `ENT_QUOTES | ENT_HTML5` + UTF-8).
- Sweep des 6 vues qui affichent du contenu user-controlled (`PostView`, `MainPostView`, `PostResponsesView`, `UserView`, `AdminView`, `CreatePostView`, `DynamicMessageView`).
- **Côté JS** : `index.js` injecte du contenu de post via `innerHTML` (bypass de l'escape serveur). Ajout d'un `escapeHtml()` JS miroir, appliqué à chaque interpolation `${post.*}`.

**Vérification** : payload `<script>alert(1)</script><img src=x onerror=alert(2)>` posté → rendu en texte (`&lt;script&gt;...`), pas exécuté.

**Réponse jury** : *"L'escaping se fait à la sortie, pas à l'entrée — sinon on perd l'information originale (l'utilisateur peut légitimement écrire `<` dans un message). Le pattern est centralisé dans `Utils::e()` côté serveur et `escapeHtml()` côté client. La double protection est nécessaire car le JS reconstruit du HTML à la volée pour l'affichage optimiste après création de post."*

---

## IDOR sur suppression (post / user)

**Problème** :
- `PostController::delete` : un check d'ownership existait mais était **commenté en dur** (debug oublié). N'importe quel user authentifié pouvait supprimer le post d'un autre.
- `UserController::delete` : aucun check du tout — un user authentifié pouvait supprimer le compte de n'importe qui.
- En plus, les routes `/api/posts/delete` et `/api/users/delete` n'avaient **pas de middleware d'auth** (un non-loggé pouvait théoriquement les invoquer).

**Solution** :
- Ajout d'`AuthMiddleware` sur les deux routes (assure le `session_start`).
- Vérification dans chaque contrôleur : `current_user_id === target_owner_id || isAdmin()`. Refus → 403, log `warning` avec contexte (current/target ids).

**Vérification** : 5 scénarios E2E (user supprime post d'autre, user supprime son post, admin supprime, etc.) → tous conformes.

**Réponse jury** : *"Même classe de faille IDOR que sur `/edituser/{id}`. Le pattern est identique : ownership ou rôle admin, vérifié au plus tôt dans le contrôleur, log `warning` pour audit. Le code commenté dans le contrôleur prouvait que l'équipe avait identifié le besoin mais l'avait désactivé pendant un debug — c'est exactement le genre de dette qu'un audit doit rattraper."*

✅ Implémenté.

---

## Helpers et outils mal rangés dans `controllers/`

### Contexte

Trois fichiers vivaient dans `modules/controllers/` sans être des handlers de requête HTTP :

| Fichier | Rôle réel |
|---|---|
| `Utils.php` | Helpers (escape `Utils::e`, `sendResponse` JSON, `isAjax`) |
| `MinificationController.php` | Outil de build d'assets, appelé une fois au boot dans `public/index.php` |

### Problème

Le suffixe `Controller` suggère « invoqué par le routeur sur une route ». Aucun de ces deux fichiers ne l'est. Un jury qui ouvre `controllers/` y voit une nomenclature trompeuse — et perd confiance dans le reste du layering.

### Solution retenue

- Déplacement vers `src/` (framework + infra) :
  - `modules/controllers/Utils.php` → `src/Utils.php`
  - `modules/controllers/MinificationController.php` → `src/MinificationController.php`
- L'autoloader scanne déjà `src/` (`includes/autoload.php`), donc **aucun changement de code appelant** n'est requis.
- `MinificationController` garde son nom pour ne pas casser `public/index.php`. Un renommage en `AssetCompiler` serait plus propre mais sort du scope.

### Justification jury

> *"`src/` contient le code framework et infrastructure (Router, Migrations, Middleware, Logger, helpers). `modules/controllers/` contient strictement les handlers HTTP appelés par une route. La nomenclature reflète maintenant le rôle réel."*

✅ Implémenté (déplacement via `git mv`).

---

## ValidatorController couplé à la sortie HTTP

### Contexte

`ValidatorController::login` et `register` faisaient deux choses en une :

1. Valider les inputs (vide / email inconnu / mauvais mot de passe / email déjà pris).
2. **Echo'er** une réponse JSON via `DynamicMessageController::showMessage(...)`.

```php
// Avant
static public function login($email, $password) {
    if (empty($email) || empty($password)) {
        DynamicMessageController::showMessage('error', "Merci de renseigner...");  // ← echo dans le validator
        return false;
    }
    // ...
}
```

### Problème

- **Intestable** : on ne peut pas appeler le validator sans qu'il pollue la sortie HTTP. Tester `ValidatorController::login('', '')` écrit du JSON dans le buffer.
- **Mauvaise séparation des responsabilités** : un validator décide-t-il du format de réponse ? Non. C'est au contrôleur — qui possède le cycle requête/réponse — de décider quoi faire des erreurs.
- **Effet de bord caché** : un appelant croit invoquer une fonction pure et déclenche un `header()` + `echo`.

### Solution retenue

- Le validator retourne désormais un **résultat structuré** :
  ```php
  ['ok' => bool, 'type' => 'success'|'error', 'message' => string]
  ```
- `UserController::login/register` reçoit le résultat, appelle lui-même `DynamicMessageController::showMessage($result['type'], $result['message'])`, puis décide selon `$result['ok']` de continuer (créer la session, créer le user).

### Justification jury

> *"Une fonction de validation retourne une décision, pas une réponse HTTP. C'est au contrôleur — responsable du cycle requête/réponse — de décider comment exposer cette décision au client. Avant, le validator pilotait la réponse à la place du contrôleur. Maintenant, chacun reste dans son rôle."*

### Bug connexe corrigé dans la foulée

Le découplage avait surfacé un bug pré-existant : `DynamicMessageController::showMessage` envoie les headers + le body JSON **avant** que `SessionController::__construct` n'appelle `session_start()`. Cookie de session impossible à poser → login fragile. Voir l'entrée *Audit MVC — finitions* pour le fix (session démarrée dans le bootstrap).

✅ Implémenté (découplage validator + correction du bug session).

---

## N+1 query : les vues fetchaient leur propre data

### Contexte

Plusieurs vues instanciaient un `UserModel` à l'intérieur d'une boucle pour afficher l'auteur de chaque post :

```php
// modules/views/PostView.php — avant
foreach ($this->posts as $post) {
    $user = new UserModel();
    $user = $user->getUserById($post->getUserId());  // ← 1 SELECT par post
    // ... rendu ...
}
```

Pareil dans `MainPostView`, et dans `UserView` qui appelait directement `PostModel::getAllPostsByUserId`. `ResponseView` appelait `UserModel::getNameFromId` pour le titre. Bref, **les vues parlaient au modèle**, sans passer par le contrôleur.

### Problème

- **Performance** : page d'accueil avec 20 posts → 1 requête posts + 20 requêtes users = **21 requêtes**. Linéaire en nombre de posts.
- **Architecture** : violation de la séparation MVC. Les règles du `CLAUDE.md` disent « views: output (HTML / JSON); no business logic » — fetcher la base **est** de la business logic.
- **Testabilité** : impossible d'instancier une vue avec des données factices sans monter une connexion DB.

### Options envisagées

| Option | Pour | Contre |
|---|---|---|
| **A.** Cache mémoire interne à `PostView` (bulk-fetch unique à `show()`) | Minimum de changements, le N+1 disparaît | La vue continue à interroger la base — séparation MVC pas respectée. Smell « view avec DB » reste là. |
| **B.** Map `[user_id => UserModel]` passée à la vue, contrôleur la prépare | Vue redevient pure présentation. Perf optimale. Testabilité gratuite. | Touche 14 fichiers (cascade contrôleur → vue parente → PostView). |
| **C.** SQL avec `JOIN posts/users` retournant tout dans la même ligne | Une seule requête, données dénormalisées disponibles directement | Casse le typage : impossible de retourner un `PostModel` propre, on mélange post et user dans la même row. Refactor plus invasif sur les modèles. |

### Solution retenue : Option B

- Nouvelle méthode `UserModel::getUsersByIds(array $ids): array` — un seul `SELECT ... WHERE id IN (?,?,?)`, retourne une map `[id => UserModel]`.
- `PostView` et `MainPostView` reçoivent `$users` par constructeur (paramètre obligatoire, pas de fallback).
- Chaque contrôleur de page (`Homepage`, `User`, `Response`, `Search`, `Admin`) :
  1. Récupère les posts (ou résultats de recherche).
  2. Extrait les `user_id` distincts.
  3. Appelle `UserModel::getUsersByIds(...)`.
  4. Passe `(posts, users)` à la vue parente, qui transmet à `PostView`.
- `UserView::show` et `ResponseView::show` n'instancient plus de modèle — leurs données arrivent par paramètre.

### Coût en requêtes

| Page | Avant | Après |
|---|---|---|
| Accueil (20 posts) | 21 | **2** |
| Profil user (N posts du même user) | N+1 | **1** (le user est déjà chargé en mémoire) |
| Réponses à un post (M réponses) | M+2 | **2** |

### Justification jury

> *"La vue affiche, le contrôleur fournit. J'ai préchargé tous les utilisateurs nécessaires en une requête batch (`WHERE id IN (...)`) et propagé la map à travers la chaîne contrôleur → vue parente → composant de rendu. Ça résout deux problèmes en un seul refactor : la perf (1 requête au lieu de N) et l'architecture (la vue ne parle plus jamais au modèle directement)."*

### Pièges rencontrés

1. **`IN ()` vide est une erreur SQL en PostgreSQL.** `getUsersByIds([])` doit court-circuiter et retourner `[]` avant de préparer la requête.
2. **Construction sécurisée du `IN`** : `array_fill(0, count($ids), '?')` puis `implode(',', ...)` produit `?,?,?` sans interpolation utilisateur — les valeurs partent en bound params via `execute()`. Pas d'injection possible.
3. **Bugs préexistants surfacés au passage** — corrigés dans la foulée, voir l'entrée *Audit MVC — finitions* ci-dessous.

✅ Implémenté.

---

## Audit MVC — finitions

Suite à la passe d'audit MVC (N+1, validator découplé, helpers déplacés), 4 bugs préexistants ont été identifiés et corrigés dans la foulée.

### 1. `PostToolHeartView` recevait le mauvais user ID

`PostView::show/showAdminPost` et `MainPostView::show` appelaient `PostToolHeartView::show($post, $user->getId())` où `$user` était l'**auteur du post**, pas l'utilisateur courant. Conséquence : l'état `active` du cœur reflétait si l'auteur avait liké son propre post, jamais si l'utilisateur connecté l'avait liké.

**Fix** : substitution par `SessionController::getUserId()` aux 3 occurrences. C'est l'utilisateur dont on veut savoir s'il a déjà liké ce post.

**Réponse jury** : *"Bug fonctionnel silencieux : le code compilait, le rendu marchait, mais l'argument passé n'avait pas la sémantique attendue. C'est précisément le genre de bug qu'un audit forcé par une refonte (ici, le passage de la map users) attrape — parce qu'on relit chaque ligne au lieu de la survoler."*

### 2. Flux login : `headers already sent` empêchait la pose du cookie de session

`UserController::login` echo'e la réponse JSON via `DynamicMessageController::showMessage` **avant** que `SessionController::__construct` n'appelle `session_start()`. Les headers HTTP étant déjà envoyés, `session_start()` ne pouvait plus poser le cookie `PHPSESSID` — le login "marchait" mais la session n'était pas persistée correctement.

**Fix** : `session_start()` appelé dans `public/index.php` juste après l'enregistrement de l'autoloader, **avant tout output possible**. La session est désormais active sur toutes les requêtes ; le `session_start()` défensif dans `SessionController::__construct` (gardé par un check `PHP_SESSION_NONE`) devient un no-op en HTTP mais reste utile en CLI/tests.

**Réponse jury** : *"Une session doit être démarrée avant tout output — c'est une règle PHP, pas une opinion. Centraliser ça dans le bootstrap garantit le bon cycle de vie quel que soit ce que fait le contrôleur ensuite. Le check idempotent dans SessionController reste comme garde-fou."*

### 3. `PostResponsesView` était du code mort

Aucun appelant — un grep `PostResponsesView` ne renvoyait que la déclaration de classe elle-même.

**Fix** : suppression du fichier (`git rm modules/views/PostResponsesView.php`).

**Réponse jury** : *"Code mort identifié à l'audit. Maintenir un fichier qu'aucune route ni vue n'invoque, c'est promettre une fonctionnalité sans personne pour la tester. À supprimer."*

### 4. `$type = $_GET['type']` jamais utilisé

Dans `SearchController::searchResults`, lecture sans usage. Reliquat probable d'une distinction user/post jamais implémentée côté front (la séparation user/post est faite côté admin via deux routes distinctes).

**Fix** : ligne supprimée.

**Réponse jury** : *"Variable lue puis jetée — pas de usage en aval. À supprimer plutôt qu'à garder 'au cas où' : un lecteur futur perd du temps à comprendre pourquoi c'est là."*

✅ Implémenté.
