# Suivi qualité de code — PHPDoc, strict_types, PSR-12, MVC

Document de suivi de la passe qualité demandée. Mis à jour au fur et à mesure.

## Objectif

| # | Tâche | État |
|---|-------|------|
| 1 | PHPDoc systématique (controllers, models, views) : `@param`, `@return`, `@throws` | ✅ fait |
| 2 | Nommage homogène (corriger le mélange `DataBase` / `Database`) | ✅ fait (8 occurrences) |
| 3 | Indentation cohérente + suppression du code mort (debug, blocs commentés) | ✅ fait |
| 4 | `declare(strict_types=1)` en tête de chaque fichier PHP | ✅ fait + vérifié bout-en-bout |
| 5 | PHP_CodeSniffer PSR-12 : 0 violation | ✅ fait (0 erreur, 0 warning) |
| 6 | Vérifier le respect de l'esprit MVC partout | ✅ fait (voir `mvc-problemes-solutions.md`) |

## Outillage

- **PHP_CodeSniffer** ajouté en `require-dev` (`squizlabs/php_codesniffer` 3.13.5).
- Règles dans `phpcs.xml` (standard **PSR-12**, périmètre = code first-party, `vendor/` et `public/assets/emojiList.php` exclus).
- Lancer le contrôle : `php vendor/bin/phpcs` — corriger l'auto-corrigeable : `php vendor/bin/phpcbf`.
- Remarque : `vendor/` est versionné dans ce dépôt ; l'ajout de phpcs ajoute donc des fichiers sous `vendor/`. À garder ou retirer du commit selon préférence.

## Mesures PSR-12

| Étape | Erreurs | Warnings | Fichiers |
|-------|---------|----------|----------|
| Baseline (avant) | 396 | 36 | 46 |
| Après phpcbf | 59 | 46 | 42 |
| Final | **0** | **0** | 0 |

Deux règles désactivées de façon assumée et **documentées dans `phpcs.xml`** :

- `PSR1.Classes.ClassDeclaration.MissingNamespace` — le projet est en espace de noms global avec un autoloader maison basé sur les chemins (choix d'architecture).
- `Generic.Files.LineLength` sur `modules/views/*` uniquement — HTML + SVG inline impossibles à couper proprement. Le code PHP (modèles, contrôleurs, `src/`) reste, lui, sous 120 caractères (lignes longues coupées : SQL multi-lignes, constructeurs `PostModel`, etc.).

## Point clé — `declare(strict_types=1)` : 3 défauts de typage révélés par les tests

Le mode strict rejette à l'exécution tout retour/argument scalaire du mauvais type. La vérification a été faite **de bout en bout via le serveur web réel** (FrankenPHP, même image que la prod), pas seulement en CLI — et c'est déterminant : **le CLI du conteneur et le runtime FrankenPHP mappent différemment les types `pdo_pgsql`**.

Constat décisif : **sous FrankenPHP, `pdo_pgsql` renvoie les colonnes entières sous forme de chaînes** (comportement par défaut bien connu du driver ; un premier sondage en CLI renvoyait des `int`, ce qui était trompeur — d'où l'importance du smoke test web). Le mode strict a donc mis au jour 3 vrais défauts de typage latents, corrigés **à la frontière du modèle** :

1. `UserModel::getRole(): ?string` renvoyait l'`int` de la colonne `role` → **cassait la connexion** (`SessionManager::login`). Corrigé par cast `?string`.
2. `PostModel::getId/getUserId/getLikes/getPostParentId/getReplyTo` (typés `int`/`?int`) renvoyaient des chaînes → **500 sur toutes les pages affichant des posts**. Corrigé par cast dans le getter (les getters de comptage `getResponsesCount`, etc. castaient déjà).
3. `CreatePostView` passait un `int` (id de post) à `htmlspecialchars()` — le mode strict refuse la coercition même pour les fonctions internes. Corrigé en affichant l'id brut (`<?= $post->getId() ?>`), comme partout ailleurs dans les vues.

**Réponse jury :** « le mode strict a révélé que `pdo_pgsql` nous renvoie les entiers en chaînes ; j'ai casté au niveau du modèle pour honorer les types déclarés, sans sur-caster les getters de texte, et j'ai validé toute l'appli en bout-en-bout. »

**Validation finale (serveur web, session admin) :** 11 pages GET + like (toggle) + création/suppression de post → **tous 200/OK** sous `strict_types`.

## Constats de nommage / code mort

- `DataBase::getConnection()` au lieu de `Database::` : `modules/models/PostModel.php` (5×) et `modules/models/UserModel.php` (3×). Fonctionne (PHP insensible à la casse) mais incohérent → à uniformiser sur `Database`.
- Code mort de debug : `//var_dump($_FILES);` (`PostController.php`), `//var_dump($routeArray);` (`Router.php`). Le `echo "Requête URI"` mentionné a déjà été retiré.
- Code mort retiré : `//header("Location: /admin");` (`UserController::delete`), `use Couchbase\View;` (import fantôme dans l'ex-`page404View`), commentaire `//` vide (`Router`), bloc `isLoggedIn` dupliqué inatteignable (`AuthMiddleware`), constructeur vide (`AdminController`).
- Renommage `page404View` → `Page404View` (PascalCase) : classe + fichier (`git mv`) + référence dans `Router`. Important pour le conteneur Linux (système de fichiers sensible à la casse).

## Audit MVC — voir doc dédiée

Détail complet (problème / solution) dans **`mvc-problemes-solutions.md`**. Résumé :

- Règles « dures » (pas de SQL, pas de superglobales, pas de `Database::getConnection()` hors des bonnes couches) : **toutes respectées** (vérifié par recherche sur tout le code).
- **Corrigé** : `echo` dans `SearchController`, bloc dupliqué mort dans `AuthMiddleware`, constructeur vide dans `AdminController`.
- **Documenté et laissé** : `echo` d'erreurs 403 (middlewares / `UserController::update`), `Utils::sendResponse()` (helper de transport JSON), nommage `render*` de `SearchView` (PHPDoc clarifié).

## Ordre d'exécution prévu

1. Nommage `DataBase`→`Database` + retrait debug commenté (mécanique, sûr).
2. `declare(strict_types=1)` en tête de chaque fichier.
3. PHPDoc (controllers, models, views).
4. `phpcbf` (auto-fix) puis corrections manuelles PSR-12 → 0 erreur.
5. Audit MVC finalisé + correctifs validés.
6. Vérification : `php -l` sur tous les fichiers, `phpcs` à 0, smoke test Docker sur les routes clés.

## Suivi par fichier

Légende : ✅ fait · — sans objet

| Couche | strict_types | PHPDoc | PSR-12 |
|--------|:---:|:---:|:---:|
| Bootstrap (`public/index.php`, `includes/`, `bin/`) | ✅ | — * | ✅ |
| Routes (`routes/`) | ✅ | — * | ✅ |
| Framework (`src/`) | ✅ | ciblé ** | ✅ |
| Contrôleurs (`modules/controllers/`) | ✅ | ✅ | ✅ |
| Modèles (`modules/models/`) | ✅ | ✅ | ✅ |
| Validators (`modules/validators/`) | ✅ | ✅ | ✅ |
| Vues (`modules/views/`) | ✅ | ✅ | ✅ |
| `public/assets/emojiList.php` (données) | ✅ | — | exclu |

\* Fichiers procéduraux (point d'entrée, câblage des routes) : pas de classe à documenter.

\*\* Le périmètre PHPDoc demandé portait sur contrôleurs / modèles / vues. Dans `src/`, les visibilités de méthodes et docblocks ont été complétés là où PSR-12 ou la cohérence l'exigeaient (`Utils`, `Autoloader`) ; les autres classes conservent leurs docblocks existants.

(47 fichiers PHP first-party traités au total.)
