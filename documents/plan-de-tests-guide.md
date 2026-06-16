# Guide — Plan de tests (PHPUnit) : unitaires & sécurité

Guide pas-à-pas pour **préparer et exécuter le plan de tests** de Solage (compétence
**C9**). Couvre le setup PHPUnit sur un projet à autoloader maison, la stratégie de test,
le cahier de tests, puis chaque famille de cas (unitaire pur → mock → intégration BDD →
sécurité → fonctionnel). Chaque étape : où écrire, quoi affirmer, le piège, comment
vérifier, et la « réponse jury » d'une phrase.

> **Rien n'est codé à ta place ici.** Le *plomberie* (commande Composer, `phpunit.xml`,
> `bootstrap.php`) est donné en entier — il n'y a qu'une bonne façon de l'écrire. Mais les
> **corps des méthodes de test** restent à écrire toi-même : pour chaque cas je donne le
> nom, l'arrange/act/assert en clair, les assertions à utiliser et le piège. C'est court et
> c'est là qu'est l'apprentissage.

---

## Le point de départ (la question jury n°1)

> « C9 dit *préparer **et exécuter*** un plan de tests. Qu'est-ce que tu livres au juste ? »

Deux choses, pas une :

1. **Un plan** (ce document + le cahier de tests) : la liste raisonnée des fonctionnalités,
   des cas, des entrées et des résultats attendus. C'est le « préparer ».
2. **Des tests exécutables** (PHPUnit) + leur **trace d'exécution** (suite verte, captures
   des démos sécurité). C'est l'« exécuter ».

Le référentiel C9 demande : un plan qui **couvre les fonctionnalités**, un **environnement
de test** défini, des **tests conformes au plan**, des **résultats cohérents avec
l'attendu**, et plusieurs **types** (unitaire, intégration, **sécurité**, non-régression).

> **Réponse jury :** « J'ai un plan écrit qui mappe chaque fonctionnalité à des cas, un
> environnement PHPUnit reproductible, des tests automatisés pour la logique pure et la
> sécurité, et des tests fonctionnels pour ce qui ne s'automatise pas proprement. La suite
> tourne en une commande et sert aussi de non-régression. »

---

## Stratégie de test — la pyramide, et ce qu'on n'automatise pas

On ne teste pas tout au même niveau. Le périmètre est **assumé**, pas subi.

| Niveau | Cible | Pourquoi ici | Coût |
|---|---|---|---|
| **Unitaire pur** | `Utils::e`, `CsrfHelper` | logique pure, **aucune BDD** → rapide, déterministe, cœur fiable | quasi nul |
| **Unitaire au mock** | `SessionManager` | injecté par constructeur → mock du modèle, **toujours sans BDD** | faible |
| **Intégration** | `UserModel`, `UserValidator`, `SearchModel` | la valeur est *dans* le SQL → il faut une vraie Postgres | moyen (BDD jetable) |
| **Sécurité** | XSS, CSRF, SQLi, mot de passe | transversal aux niveaux ci-dessus | inclus |
| **Fonctionnel / manuel** | CSRF 403, IDOR 403 | dépend de `$_SERVER`, `php://input`, statics → s'automatise mal | faible (curl, captures) |

**La décision qui structure tout :** un composant n'est unit-testable **que si on peut lui
substituer ses dépendances**. Deux cas dans Solage, opposés exprès :

- `SessionManager` reçoit son `UserModel` **par le constructeur** → en test je passe un
  **mock**, zéro BDD. C'est de la vraie injection de dépendance.
- `UserValidator::login()` fait `new UserModel()` **en dur dans la méthode**
  (`UserValidator.php:24`) → impossible de le mocker → je ne peux le tester qu'**en
  intégration**, contre une vraie base.

> **Réponse jury :** « La testabilité est une conséquence du design, pas un ajout. Là où
> j'ai injecté la dépendance je teste au mock sans base ; là où elle est instanciée en dur
> je teste en intégration. Je sais expliquer le refactor (injecter `UserModel` dans le
> validateur) qui le rendrait unitaire — je ne l'ai pas fait pour ne pas toucher du code qui
> marche, et c'est un tradeoff que j'assume. »

**Ce qu'on NE teste pas automatiquement, et pourquoi (à dire avant qu'on le demande) :**

- **Les vues** (`modules/views/`) : elles produisent du HTML, pas de logique. L'anti-XSS
  qu'elles utilisent (`Utils::e`) est testé, lui. Tester le HTML rendu serait fragile.
- **`Router::match()`** : lit `$_SERVER`, instancie des contrôleurs, fait des `exit`. Le
  seul morceau vraiment unitaire est la transformation `{param}` → regex (`Router.php:40`),
  aujourd'hui **inline** dans `match()`. L'extraire dans une méthode `resolve()` la rendrait
  testable — refactor noté, hors périmètre ici.
- **L'authz au niveau contrôleur** (IDOR) : testée **fonctionnellement** (curl), pas en
  unitaire — voir étape 7.

---

# Partie A — Environnement de test (le « où ça tourne »)

## Étape 0 — Installer PHPUnit et brancher l'autoloader maison

### 0.1 Installer la dépendance dev

Le projet tourne sur **PHP 8.3** (image `dunglas/frankenphp:1.4-php8.3`, `Dockerfile:2`) →
**PHPUnit 11**. Comme pour PHP_CodeSniffer, on l'ajoute en `require-dev` :

```bash
composer require --dev phpunit/phpunit ^11
```

> ⚠️ `vendor/` est versionné dans ce dépôt (comme noté pour phpcs). PHPUnit va donc ajouter
> des fichiers sous `vendor/`. Cohérent avec le choix existant — à garder tel quel, ou à
> sortir du suivi Git séparément (décision globale, hors de ce guide).

Pratique : ajouter un raccourci dans `composer.json` (miroir de la façon dont tu lances
phpcs) :

```json
"scripts": {
  "test": "phpunit"
}
```

→ se lance avec `composer test` ou `php vendor/bin/phpunit`.

### 0.2 Le piège central : pas d'autoload PSR-4

Solage n'a **pas** de section `autoload` dans `composer.json`. Les classes sont en **espace
de noms global**, chargées par un **autoloader maison basé sur les chemins**
(`includes/autoload.php`, qui balaie `routes/`, `modules/*`, `src/`). PHPUnit, lui, charge
ses propres classes via l'autoloader **Composer** (`vendor/autoload.php`).

→ Le bootstrap de test doit **enregistrer les deux** : Composer (pour PHPUnit, phpdotenv,
le logger PSR-3) **puis** l'autoloader du projet (pour les classes de l'app). Les deux
cohabitent sans conflit : Composer gère les classes *avec* namespace, le tien les classes
*sans* namespace.

> **Réponse jury :** « Mes tests chargent les classes de l'app avec **exactement le même
> autoloader que la production** (`includes/autoload.php`), pas un autoloader de test
> parallèle. Zéro divergence test/prod sur le chargement de classes. »

### 0.3 `tests/bootstrap.php` (à créer, en entier)

```php
<?php

declare(strict_types=1);

// 1. Autoloader Composer : PHPUnit, phpdotenv, psr/log.
require __DIR__ . '/../vendor/autoload.php';

// 2. Autoloader maison : charge les classes de l'app (src/, modules/*, routes/)
//    exactement comme public/index.php le fait en production.
require __DIR__ . '/../includes/autoload.php';
Autoloader::register();

// 3. La classe Database vit dans includes/ et n'est PAS couverte par l'autoloader
//    de chemins ci-dessus. On l'inclut ici pour les tests d'intégration.
//    L'include ne se connecte PAS à la base : la connexion est paresseuse
//    (Database::getConnection() au premier appel).
require_once __DIR__ . '/../includes/database.php';
```

> **Le détail qui compte :** `Database` n'est pas dans les dossiers balayés par
> l'autoloader (il ne couvre pas `includes/`). Si tu l'oublies, les tests d'intégration
> échouent sur `Class "Database" not found`. Les tests **purs** (Utils, CsrfHelper) n'en ont
> pas besoin — mais l'inclure est inoffensif (aucune connexion à l'include).

### 0.4 `phpunit.xml` (à créer, à la racine)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         cacheDirectory=".phpunit.cache"
         failOnRisky="true">
    <testsuites>
        <testsuite name="Solage">
            <directory>tests</directory>
        </testsuite>
    </testsuites>

    <!-- Couverture de code (optionnel, nécessite pcov ou xdebug) :
    <source>
        <include>
            <directory>src</directory>
            <directory>modules</directory>
        </include>
    </source>
    -->
</phpunit>
```

Crée le dossier `tests/`. Ajoute `.phpunit.cache/` au `.gitignore`.

**Vérif étape 0 :** crée un test bidon `tests/SmokeTest.php` avec une seule assertion
`assertTrue(true)`, lance `php vendor/bin/phpunit` → **1 test, 1 assertion, OK (vert)**.
Si c'est vert, l'environnement est bon ; supprime le smoke test.

### 0.5 Où tournent les tests : hôte vs conteneur (le tradeoff)

- **Tests purs + sécurité (parties B & C)** : aucune base, aucun `pdo_pgsql`. Lance-les
  **sur l'hôte** comme tu lances phpcs : `php vendor/bin/phpunit`. C'est le cœur fiable,
  identique partout.
- **Tests d'intégration (partie D)** : besoin de `pdo_pgsql` et d'une Postgres. La base dev
  est exposée sur `localhost:5432` (`docker-compose.yml:77`). Sur l'hôte, active
  `extension=pdo_pgsql` dans le `php.ini`, et pointe `DB_HOST=localhost`.

> ⚠️ **Nuance runtime déjà documentée :** sous FrankenPHP, `pdo_pgsql` renvoie les colonnes
> entières **en chaînes** ; en CLI elles peuvent revenir en `int` (voir
> `qualite-code-psr12-suivi.md`). Nos assertions s'appuient sur les **getters qui castent**
> (`getId()` caste en `int`, `getRole()` en `?string`) → elles sont **stables quel que soit
> le runtime**. La fidélité au runtime web reste assurée par le **smoke test bout-en-bout**
> déjà au dossier. Pour aller au bout, on *peut* lancer la suite dans le conteneur `app`
> (mêmes types que la prod), au prix de rendre les dépendances dev visibles dans l'image —
> sur-ingénierie pour le diplôme, l'hôte + smoke test web suffit.

---

# Partie B — Tests unitaires purs (le cœur, sans BDD)

## Étape 1 — `Utils::e()` : l'échappement anti-XSS (le premier test)

C'est le **meilleur premier test** : fonction pure, une ligne, sécurité. `Utils::e()`
(`Utils.php:43`) fait `htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')`.

**Fichier :** `tests/UtilsTest.php`. Voici **l'unique squelette complet** que je te donne —
pour fixer la forme PHPUnit. Tous les autres cas, tu écris le corps toi-même.

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class UtilsTest extends TestCase
{
    #[Test]
    public function e_neutralise_une_balise_script(): void
    {
        $out = Utils::e('<script>alert(1)</script>');

        // À toi : choisis tes assertions. Idée : la balise brute a disparu,
        // et les chevrons sont encodés.
        $this->assertStringNotContainsString('<script>', $out);
        $this->assertStringContainsString('&lt;script&gt;', $out);
    }
}
```

**Cas à écrire toi-même (mêmes outils, autres entrées) :**

| Cas | Entrée | Attendu |
|---|---|---|
| Chevrons | `<b>x</b>` | ne contient plus `<b>`, contient `&lt;b&gt;` |
| Guillemets doubles | `il a dit "oui"` | contient `&quot;` |
| Apostrophe | `O'Brien` | contient `&apos;` **(et non `&#039;`)** |
| Esperluette | `a & b` | contient `&amp;` |
| `null` | `null` | chaîne vide `''` |

> ⚠️ **Piège qui fait échouer le 1er essai :** avec `ENT_HTML5`, l'apostrophe devient
> `&apos;`, **pas** `&#039;` (qui est la sortie en `ENT_HTML401`). Si tu asseres `&#039;`,
> le test rougit alors que le code est correct. Affirme `&apos;`.

> **Réponse jury :** « `Utils::e` est ma défense XSS à la sortie. Le test prouve qu'une
> charge `<script>` ressort inerte (`&lt;script&gt;`), pour les chevrons, guillemets,
> apostrophe, esperluette, et que `null` ne casse rien. »

**Vérif étape 1 :** `php vendor/bin/phpunit --filter UtilsTest` → vert.

## Étape 2 — `CsrfHelper` : la sécurité CSRF en unitaire

`CsrfHelper` (`src/CsrfHelper.php`) est pur au sens test : il ne touche que `$_SESSION`,
qui n'est **qu'un tableau** qu'on pilote depuis le test. Aucune base, aucun `session_start`
nécessaire.

> **Note de cohérence :** l'API réelle est `CsrfHelper::getToken()` / `verifyToken()` /
> `field()`. (L'ancien `csrf-securite-guide.md` parle de `SessionController::getCsrfToken` :
> c'était l'API *prévue* ; l'implémentation finale est `CsrfHelper`. Teste le code réel.)

**Fichier :** `tests/CsrfHelperTest.php`. **Isolation obligatoire :** vide la session avant
chaque test.

```php
protected function setUp(): void
{
    $_SESSION = [];
}
```

**Cas à écrire :**

| # | Méthode | Arrange | Act | Assert |
|---|---|---|---|---|
| 1 | `getToken` | session vide | `getToken()` | longueur **64**, `ctype_xdigit()` vrai |
| 2 | `getToken` stable | session vide | l'appeler **2×** | `assertSame($a, $b)` (un token *par session*) |
| 3 | `verifyToken` bon | `$_SESSION['csrf_token']='abc'` | `verifyToken('abc')` | `true` |
| 4 | `verifyToken` mauvais | idem | `verifyToken('xyz')` | `false` |
| 5 | `verifyToken` vide | idem | `verifyToken('')` | `false` |
| 6 | `verifyToken` null | idem | `verifyToken(null)` | `false` |
| 7 | `verifyToken` sans session | session vide | `verifyToken('abc')` | `false` |
| 8 | `field` | session vide | `field()` | contient `name="csrf_token"` et le token |

> ⚠️ **Pièges :** (a) longueur **64** car `bin2hex(random_bytes(32))` = 32 octets → 64 hex.
> (b) Le cas 7 prouve que sans token en session **rien** ne passe (le `!empty(...)` de
> `verifyToken`). (c) Tu ne peux pas tester la *résistance au timing* de `hash_equals` en
> unitaire — affirme la **correction** (bon/mauvais), et **dis** que `hash_equals` est
> timing-safe.

> **Réponse jury :** « Le token fait 32 octets de `random_bytes` (CSPRNG), il est stable par
> session, et la vérification rejette le token absent, vide, nul ou faux. La comparaison est
> `hash_equals` (temps constant) — je le teste fonctionnellement, je l'argumente pour le
> timing. »

## Étape 3 — `Utils::isAjax()` et `Utils::sendResponse()`

Deux cas rapides, toujours sans base. `isAjax()` lit `$_SERVER`, `sendResponse()` fait un
`echo` (donc on **capture la sortie**).

| Méthode | Arrange / Act | Assert | Piège |
|---|---|---|---|
| `isAjax` vrai | `$_SERVER['HTTP_X_REQUESTED_WITH']='XMLHttpRequest'` | `true` | remets `$_SERVER` propre en `tearDown` |
| `isAjax` faux | en-tête absent | `false` | — |
| `sendResponse` | `ob_start(); Utils::sendResponse(true,'ok'); $json=ob_get_clean();` | `json_decode($json,true)` == `['success'=>true,'message'=>'ok']` | l'`echo` → **capture avec `ob_*`** |
| `sendResponse` + data falsy | `sendResponse(true,'ok',[])` | la clé `data` est **absente** | `if ($data)` est faux pour `[]`, `0`, `''` → comportement à documenter (voir « À signaler ») |

> **Réponse jury :** « `sendResponse` est mon transport JSON unique ; je teste qu'il sérialise
> bien la forme `{success, message[, data]}`. Le test révèle aussi que `data` est omis quand
> il est *falsy* — un comportement que je connais et que je sais corriger si besoin. »

---

# Partie C — Test unitaire au mock (le payoff de la DI)

## Étape 4 — `SessionManager` avec un `UserModel` mocké

`SessionManager` (`src/SessionManager.php`) reçoit `UserModel` **par le constructeur**
(`SessionManager.php:13`). En test, on passe un **mock** → **aucune base**.

> **Le point clé à montrer :** `createMock(UserModel::class)` **ne lance pas** le vrai
> constructeur. Or le vrai `UserModel::__construct` appelle `Database::getConnection()`
> (`UserModel.php:26`) **inconditionnellement**. Le mock court-circuite ça → c'est
> précisément *pourquoi* la DI rend le test possible sans base.

**Fichier :** `tests/SessionManagerTest.php`. `setUp` : `$_SESSION = [];`.

**Cas 1 — `login()` peuple la session :**
- *Arrange :* `$user = $this->createMock(UserModel::class);` puis stub
  `getName()→'Bob'`, `getImage()→'🦊'`, `getRole()→'2'`.
  `$model = $this->createMock(UserModel::class);` stub `getUserById(5)→$user`.
- *Act :* `$sm = new SessionManager($model); $sm->login(5);`
- *Assert :* `$_SESSION['user_id']===5`, `$_SESSION['name']==='Bob'`,
  `$sm->isLoggedIn()===true`, `$sm->getUser()===$user`.

**Cas 2 — `isAdmin()` vrai/faux :**
- *Arrange :* `$_SESSION['user_id']=1;` `$admin = createMock(UserModel)` stub
  `getRoleName()→'Admin'`; `$model` stub `getUserById(1)→$admin`.
- *Act :* `$sm = new SessionManager($model);` (le constructeur charge l'utilisateur depuis
  la session).
- *Assert :* `assertTrue($sm->isAdmin())`. Refais avec `getRoleName()→'Utilisateur'` →
  `assertFalse`.

> ⚠️ **Pièges :**
> - **Ne retourne pas un vrai `UserModel`** depuis le stub `getUserById` : `new
>   UserModel($row)` rappelle `Database::getConnection()` → tu retombes sur la base. Retourne
>   un **mock** de `UserModel`.
> - Le constructeur lit `$_SESSION['user_id']` : avec `$_SESSION=[]` (cas 1) il **n'appelle
>   pas** `getUserById` → pas d'attente de mock surprise. Pour le cas 2 on **veut** qu'il
>   l'appelle, donc on pose `user_id` avant.
> - **`session_start()`** tourne dans le constructeur. En PHPUnit (CLI), tant qu'aucun test
>   n'a rien affiché avant, ça passe sans warning. Si tu vois un warning « headers already
>   sent », ajoute `#[RunInSeparateProcess]` et `#[PreserveGlobalState(false)]` sur ces
>   tests.

> **Réponse jury :** « `SessionManager` est ma démonstration d'injection de dépendance : je
> lui passe un `UserModel` mocké, donc je teste `login()` et `isAdmin()` **sans base, en
> millisecondes**. Le mock saute le constructeur qui, sinon, ouvrirait une connexion. C'est
> le contraste exact avec `UserValidator` qui, lui, instancie son modèle en dur. »

---

# Partie D — Tests d'intégration (vraie Postgres, isolés)

Ici on teste ce dont la valeur **est** dans le SQL : `UserModel`, `UserValidator`,
`SearchModel`. Besoin de `pdo_pgsql` + la Postgres dev (`localhost:5432`).

## Étape 5 — La base de test isolée (transaction + rollback)

Le piège des tests BDD : ils polluent la base. Solution standard : **chaque test dans une
transaction, annulée à la fin**. La base revient à son état exact, les tests sont rejouables
à l'infini.

**Fichier :** `tests/Integration/DatabaseTestCase.php` — une classe de base abstraite :

```php
abstract class DatabaseTestCase extends \PHPUnit\Framework\TestCase
{
    protected \PDO $db;

    protected function setUp(): void
    {
        $this->db = Database::getConnection(); // singleton : même PDO que les modèles
        $this->db->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->db->rollBack(); // tout ce que le test a inséré disparaît
    }
}
```

> **Pourquoi c'est défendable :** `Database` est un **singleton** → le PDO de la transaction
> est **le même** que celui qu'utilisent les modèles. Donc ce qu'un modèle insère pendant le
> test est annulé au `rollBack`. Pas de base de test séparée à gérer, zéro pollution,
> répétable.
>
> ⚠️ **Pièges :** (a) ne **relance pas** `beginTransaction` dans un test (pgsql PDO ne gère
> pas les transactions imbriquées sans savepoints). (b) Les tests lisent aussi les données du
> `seed.sql` présentes en base — pour être déterministe, **insère tes propres données** dans
> la transaction plutôt que de dépendre du seed.

> **Réponse jury :** « Chaque test d'intégration tourne dans une transaction annulée au
> teardown : la base est jetable et l'état est identique avant/après. Comme c'est un
> singleton PDO, modèles et test partagent la même transaction. »

## Étape 6 — `UserModel` + `UserValidator` (CRUD, mot de passe)

`tests/Integration/UserModelTest.php extends DatabaseTestCase`.

**Cas à écrire (arrange = INSERT direct en PDO dans la transaction, voir le piège) :**

| # | Cas | Arrange | Act | Assert |
|---|---|---|---|---|
| 1 | lecture par email | INSERT user (email `t@test.io`, password = `password_hash('secret',PASSWORD_BCRYPT)`) | `(new UserModel())->getUserByEmail('t@test.io')` | non null, `getEmail()==='t@test.io'` |
| 2 | email inconnu | — | `getUserByEmail('nope@x.io')` | `null` |
| 3 | **mot de passe hashé** | user du cas 1 | lire `getPassword()` | **≠ `'secret'`** et `password_verify('secret', $hash)===true` |
| 4 | validateur login OK | user du cas 1 | `UserValidator::login('t@test.io','secret')` | `['ok'=>true, ...]` |
| 5 | validateur login mauvais mdp | idem | `UserValidator::login('t@test.io','faux')` | `ok=false`, message « ne correspond pas » |
| 6 | validateur login email absent | — | `UserValidator::login('nope@x.io','x')` | `ok=false`, message « n'existe pas » |
| 7 | validateur register email pris | user du cas 1 | `UserValidator::register('t@test.io','x')` | `ok=false`, « déjà utilisé » |
| 8 | validateur register nouveau | — | `UserValidator::register('libre@x.io','x')` | `ok=true` |
| 9 | champs vides | — | `login('','')` / `register('','')` | `ok=false`, « renseigner vos informations » |

> ⚠️ **Piège majeur révélé par le test — `createUser()` n'est pas testable tel quel.**
> `UserModel::createUser()` fait `include 'assets/emojiList.php'` (`UserModel.php:182`),
> **chemin relatif** au répertoire courant. En prod le docroot est `public/` → le fichier
> résout (`public/assets/emojiList.php`). En PHPUnit le CWD est la **racine** → le fichier
> n'existe pas là → l'insertion casse. C'est un **vrai couplage au docroot** que le test met
> au jour. Pour les cas ci-dessus, **insère via PDO direct** (`$this->db->prepare('INSERT
> INTO users ...')`) au lieu de `createUser`. Si tu veux tester `createUser` lui-même,
> `chdir(__DIR__.'/../../public')` dans le test — mais surtout, **signale ce couplage au
> jury** (voir « À signaler »). C'est exactement la valeur d'un plan de tests : il a trouvé
> un défaut latent.

> **Réponse jury :** « L'intégration prouve la chaîne complète validateur → modèle → SQL →
> `password_verify` : un mot de passe est stocké hashé (jamais en clair), une connexion
> valide passe, une mauvaise est rejetée. Et écrire ces tests a **révélé un couplage** :
> `createUser` dépend du docroot via un `include` relatif — je l'ai documenté. »

## Étape 7 — Sécurité d'intégration : injection SQL

`tests/Integration/SqlInjectionTest.php extends DatabaseTestCase`. On prouve que les
requêtes préparées rendent une charge d'injection **inerte**.

- *Arrange :* INSERT un user, puis 1 post de contenu `'Bonjour le monde'` (FK `user_id`).
- *Cas A (la charge est inerte) :* `(new SearchModel())->searchPosts("' OR '1'='1")`
  → la méthode entoure le terme de `%...%` → le `LIKE` cherche littéralement la chaîne
  `%' OR '1'='1%` → **0 résultat** (et **aucune exception**). Assert `count === 0`.
- *Cas B (la recherche marche quand même) :* `searchPosts('Bonjour')` → **1 résultat**.

Ensemble, A+B prouvent que l'entrée est traitée comme **donnée** (motif `LIKE` littéral),
jamais comme **SQL**. Si l'injection marchait, le cas A renverrait *toutes* les lignes.

> **Réponse jury :** « La parade SQLi est structurelle : requêtes préparées PDO partout
> (`SearchModel.php:38`). Le test le prouve — une charge `' OR '1'='1` ne renvoie rien et ne
> lève rien, alors qu'un terme normal fonctionne : l'entrée est une donnée, pas du code. »

---

# Partie E — Tests fonctionnels (la démo jury, ce qui ne s'automatise pas)

Certaines protections vivent au niveau HTTP/contrôleur (lecture de `$_SERVER`,
`php://input`, méthodes statiques, `exit`). Les unit-tester demanderait de lourds refactors.
On les valide **fonctionnellement**, app lancée — et **on capture la preuve** (comme la démo
d'attaque du guide CSRF).

## Étape 8 — CSRF : un POST sans token → 403

`CsrfMiddleware` est armé par le `Router` sur **tout** POST (`Router.php:54`).

- **Démo :** rejoue un POST avec un mauvais token. En `curl` :
  `curl -i -X POST http://localhost/api/like -H 'X-CSRF-Token: bidon' -H 'Content-Type: application/json' -d '{}'`
- **Attendu :** `HTTP/1.1 403`, corps « 403 — Token CSRF refusé. », ligne
  `csrf.token.rejected` dans les logs (`CsrfMiddleware.php:14`).

## Étape 9 — IDOR : Bob supprime un post d'Alice → 403

`PostController::delete` vérifie *propriétaire-ou-admin* (`PostController.php:153`).

- **Démo :** connecté en **Bob**, tente de supprimer un `postId` appartenant à **Alice** :
  `curl -i -X POST http://localhost/api/posts/delete -b 'PHPSESSID=...<session de Bob>...' -H 'X-CSRF-Token: <token de Bob>' -d '{"postId": <id d'Alice>}'`
- **Attendu :** `HTTP/1.1 403`, JSON « Vous n'avez pas la permission… », ligne
  `post.delete.forbidden` dans les logs.

> **Réponse jury :** « Pour le CSRF et l'IDOR, la preuve est une requête falsifiée rejouée :
> 403 + ligne de log à chaque fois. Je n'ai pas unit-testé ces contrôleurs (statics,
> superglobales, `exit` → refactor lourd) ; le test fonctionnel est ici plus honnête et tout
> aussi probant. »

---

# Le plan de tests (le cahier — à joindre au dossier)

C'est l'artefact « préparé » de C9 : chaque fonctionnalité → cas → entrée → attendu, plus
les colonnes **Obtenu** / **Écart** que tu remplis **à l'exécution**. Reprends-le tel quel
dans le dossier.

| ID | Fonctionnalité | Cas | Entrée | Attendu | Type | Obtenu | Écart |
|---|---|---|---|---|---|---|---|
| T01 | Anti-XSS | échappe `<script>` | `<script>…` | sortie encodée `&lt;…` | unit | | |
| T02 | Anti-XSS | apostrophe/guillemets/`&`/`null` | divers | entités correctes, `null`→`''` | unit | | |
| T03 | CSRF token | génération | session vide | 64 hex, stable/session | unit | | |
| T04 | CSRF token | vérification | bon/mauvais/vide/nul/sans-session | true / false×4 | unit/sécu | | |
| T05 | Transport JSON | `sendResponse` | `(true,'ok')` | `{success,message}` | unit | | |
| T06 | Session | `login()` peuple la session | userId mocké | `$_SESSION` rempli, `isLoggedIn` | unit (mock) | | |
| T07 | Autorisation | `isAdmin()` | rôle Admin / non-Admin | true / false | unit (mock) | | |
| T08 | Comptes | lecture par email | email connu / inconnu | user / null | intég. | | |
| T09 | Mot de passe | stockage hashé | mdp `secret` | hash ≠ clair, `verify` ok | sécu (intég.) | | |
| T10 | Connexion | validateur login | bon / mauvais mdp / email absent / vide | ok / 3× erreurs | intég. | | |
| T11 | Inscription | validateur register | email pris / libre / vide | erreur / ok / erreur | intég. | | |
| T12 | Recherche | injection SQL inerte | `' OR '1'='1` + terme normal | 0 résultat / 1 résultat | sécu (intég.) | | |
| T13 | CSRF (bout-en-bout) | POST sans token | curl mauvais token | 403 + log | fonctionnel | | |
| T14 | IDOR | suppression non-propriétaire | Bob → post d'Alice | 403 + log | fonctionnel | | |

## Jeu d'essai détaillé — « Publier un message »

Le mapping C9 demande un jeu d'essai déroulé sur une fonctionnalité. Modèle à remplir :

| Champ | Valeur |
|---|---|
| **Fonctionnalité** | Création d'un post (`POST /api/post`) |
| **Pré-condition** | utilisateur connecté (session valide), token CSRF présent |
| **Entrée** | `data = {"content":"Bonjour le monde","replyTo":0,"replyToParent":0}`, sans image |
| **Étapes** | 1. se connecter · 2. soumettre le formulaire de post · 3. observer la réponse |
| **Attendu** | JSON `success=true`, un `id` numérique, le post visible en tête de feed |
| **Obtenu** | *(à remplir à l'exécution — capture réseau + capture du feed)* |
| **Écart** | *(à remplir — aucun si conforme)* |
| **Cas négatif** | `content=""` → `success=false`, « contenu vide » (`PostController.php:68`) |

---

# Exécuter le plan (et le relire)

```bash
php vendor/bin/phpunit                 # toute la suite
php vendor/bin/phpunit --filter Csrf   # une classe/un cas
php vendor/bin/phpunit --testdox       # sortie lisible : une phrase par test
```

- `--testdox` donne une sortie « cahier » (chaque test en une phrase) — pratique en capture
  pour le dossier.
- La suite verte **est** la preuve d'exécution + sert de **non-régression** : tu la rejoues
  après chaque changement.
- Couverture (optionnel) : décommente le bloc `<source>` du `phpunit.xml`, installe `pcov`,
  lance `php vendor/bin/phpunit --coverage-text`. À ne montrer que si le chiffre est honnête
  sur le périmètre testé — ne vise pas un % pour le %.

> **Réponse jury :** « *Préparer* = ce cahier de tests qui mappe les fonctionnalités. *
> Exécuter* = `phpunit` vert + les deux démos sécurité 403. La même suite est ma
> non-régression. »

---

# À signaler avant de coder (les findings que le plan révèle)

L'honnêteté sur ces points rapporte des points — un plan de tests **sert** à les trouver.

- **`createUser()` couplé au docroot** (`UserModel.php:182`) : `include 'assets/emojiList.php'`
  est relatif au CWD → casse hors contexte web. Révélé par l'étape 6. À mentionner (et,
  hors périmètre, à corriger via un chemin absolu).
- **`getNameFromId()` plante sur id inconnu** (`UserModel.php:128`) : `return $row->name;`
  sans vérifier `$row` → fatale si l'id n'existe pas. Un test du cas « id absent » le
  documente ; correctif trivial (`$row ? $row->name : null`) hors périmètre.
- **`sendResponse` omet `data` falsy** (`Utils.php:22`) : `if ($data)` est faux pour `[]`,
  `0`, `''`. Comportement à connaître (test T05) — pas forcément un bug, mais à savoir
  expliquer.
- **Dérive de doc CSRF** : `csrf-securite-guide.md` décrit `SessionController::getCsrfToken`,
  le code livré est `CsrfHelper::getToken`. Teste et cite le **code réel**.
- **`Router::match()` non unitaire** : la regex `{param}` est inline (`Router.php:40`).
  L'extraire (`resolve()`) la rendrait testable — refactor noté, non requis pour C9.

---

# Ordre de bataille résumé

| # | Fichier(s) à créer | Effet | Vérif |
|---|---|---|---|
| 0 | `composer require`, `phpunit.xml`, `tests/bootstrap.php` | environnement | smoke test vert |
| 1 | `tests/UtilsTest.php` | anti-XSS (T01-02, T05) | filter UtilsTest vert |
| 2 | `tests/CsrfHelperTest.php` | CSRF unitaire (T03-04) | filter CsrfHelper vert |
| 3 | (dans UtilsTest) | `isAjax`/`sendResponse` | vert |
| 4 | `tests/SessionManagerTest.php` | DI au mock (T06-07) | vert, **sans BDD** |
| 5 | `tests/Integration/DatabaseTestCase.php` | base jetable (txn) | rollback OK |
| 6 | `tests/Integration/UserModelTest.php` | CRUD + mdp + validateur (T08-11) | vert |
| 7 | `tests/Integration/SqlInjectionTest.php` | SQLi inerte (T12) | vert |
| 8 | — (curl) | CSRF 403 (T13) | 403 + log |
| 9 | — (curl) | IDOR 403 (T14) | 403 + log |

Avancer étape par étape : la partie B (sans base) d'abord — elle est fiable partout et
constitue déjà un livrable C9 valable. Les parties D/E la **renforcent** (intégration +
sécurité réelle).

## Récap des artefacts

- `composer.json` : `phpunit/phpunit` en `require-dev` + script `test` (PHP)
- `phpunit.xml` (config) + `tests/bootstrap.php` (réutilise l'autoloader maison)
- `tests/UtilsTest.php`, `tests/CsrfHelperTest.php`, `tests/SessionManagerTest.php` (unitaires)
- `tests/Integration/DatabaseTestCase.php` + `UserModelTest.php` + `SqlInjectionTest.php`
- Le **cahier de tests** (table ci-dessus) + le **jeu d'essai** « Publier un message »
- Les **traces d'exécution** : capture `--testdox` + captures des 403 CSRF/IDOR
