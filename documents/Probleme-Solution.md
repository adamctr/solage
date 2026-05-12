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
