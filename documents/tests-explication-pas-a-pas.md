# Tests Solage — explication pas à pas (installation + chaque fonction)

But de ce document : **comprendre la suite de tests réellement livrée**, ligne par ligne, pour
pouvoir la défendre à l'oral. On part de l'installation (les 3 fichiers de plomberie), puis on
lit **chaque fichier de test et chaque méthode**, en répondant à chaque fois à trois questions :
*qu'est-ce qu'on affirme ?*, *pourquoi c'est écrit comme ça ?*, *quel est le piège ?*.

> Document compagnon : `plan-de-tests-guide.md` contient le **cahier de tests** (le mapping
> fonctionnalité → cas, l'artefact « préparé » de la compétence C9) et les démos sécurité
> fonctionnelles (CSRF/IDOR en curl). Ici on explique le **code des tests automatisés**.

Inventaire de ce qui est livré :

```
phpunit.xml                              ← configuration PHPUnit
tests/bootstrap.php                      ← amorçage (autoloaders + Database)
tests/UtilsTest.php                      ← unitaire pur (anti-XSS, AJAX, JSON)
tests/CsrfHelperTest.php                 ← unitaire pur (sécurité CSRF)
tests/SessionManagerTest.php             ← unitaire au mock (injection de dépendance)
tests/Integration/DatabaseTestCase.php   ← classe de base : transaction + rollback
tests/Integration/UserModelTest.php      ← intégration BDD (comptes, mot de passe)
tests/Integration/PostModelTest.php      ← intégration BDD (publication)
tests/Integration/SqlInjectionTest.php   ← sécurité d'intégration (SQLi inerte)
```

22 tests unitaires + ~13 tests d'intégration, lançables en **une commande**.

---

## Partie 0 — Installer l'environnement (la plomberie)

Trois fichiers, et un seul ajout de dépendance. Rien de magique : chaque ligne a une raison.

### 0.1 La dépendance — `composer.json`

```json
"require-dev": {
  "squizlabs/php_codesniffer": "^3.10",
  "phpunit/phpunit": "11"
}
```

- **`require-dev`** et pas `require` : PHPUnit ne sert qu'au développement, il n'a rien à faire
  dans l'image de production. Même logique que PHP_CodeSniffer, déjà là.
- **Version 11** : le projet cible **PHP 8.3** (image `dunglas/frankenphp:1.4-php8.3`). PHPUnit 11
  est la ligne compatible PHP 8.2/8.3. On ne prend pas une version au hasard, on la cale sur le
  runtime.

Installation : `composer require --dev phpunit/phpunit ^11`. On lance ensuite avec
`php vendor/bin/phpunit`.

> **Réponse jury :** « PHPUnit est en `require-dev`, version 11 calée sur mon PHP 8.3 — il n'entre
> pas dans l'image de prod. »

### 0.2 L'amorçage — `tests/bootstrap.php`

C'est le fichier le plus important à comprendre, parce qu'il résout un piège propre à ce projet.

```php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';   // 1.

require __DIR__ . '/../includes/autoload.php';  // 2.
Autoloader::register();

require __DIR__ . '/../includes/database.php';   // 3.
```

**Le piège central : Solage n'a pas d'autoload PSR-4.** Il n'y a pas de section `"autoload"` dans
`composer.json`. Les classes de l'app (`Utils`, `UserModel`, `SessionManager`…) sont en **espace de
noms global** et chargées par un **autoloader maison basé sur les chemins**
(`includes/autoload.php`), qui balaie `routes/`, `modules/*`, `src/`. PHPUnit, lui, charge **ses**
classes via l'autoloader **Composer** (`vendor/autoload.php`). Il faut donc brancher **les deux**, et
dans le bon ordre :

1. **`vendor/autoload.php`** — l'autoloader Composer. Il charge PHPUnit lui-même, phpdotenv,
   psr/log. Sans lui, `use PHPUnit\Framework\TestCase` ne résout pas.
2. **`includes/autoload.php` + `Autoloader::register()`** — l'autoloader **maison**, exactement
   celui que `public/index.php` utilise en production. C'est lui qui trouvera `Utils`, `CsrfHelper`,
   `UserModel`, etc. quand un test les nomme.
3. **`includes/database.php`** — la classe `Database` vit dans `includes/`, un dossier que
   l'autoloader maison **ne balaie pas** (il ne couvre que `routes/`, `modules/*`, `src/`). Si on ne
   l'inclut pas ici, les tests d'intégration plantent sur `Class "Database" not found`.

**Le détail défendable :** mes tests chargent les classes de l'app avec **le même autoloader que la
production**, pas un autoloader de test parallèle. Donc zéro divergence test/prod sur le chargement.

**Pourquoi l'`include` de `Database` ne fait rien de dangereux :** la connexion est **paresseuse**.
`require database.php` ne fait que *définir* la classe ; aucune connexion n'est ouverte. `Database`
expose `getConnection()` qui instancie la connexion **au premier appel seulement** — appel qui
n'arrive que dans les tests d'intégration. Les tests purs (Utils, CsrfHelper) incluent donc
`Database` sans jamais toucher une base.

> **Réponse jury :** « Le bootstrap enregistre les deux autoloaders — Composer pour PHPUnit, le mien
> pour l'app — puis inclut `Database`, qui vit hors des dossiers autoloadés. L'include n'ouvre
> aucune connexion : elle est paresseuse, déclenchée seulement par les tests d'intégration. »

### 0.3 La configuration — `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Tests">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

Ligne par ligne :

- **`bootstrap="tests/bootstrap.php"`** — fait charger le fichier ci-dessus **avant** tout test.
  C'est ce qui relie la config à l'amorçage.
- **`colors="true"`** — sortie verte/rouge lisible en terminal (confort de capture pour le dossier).
- **`cacheDirectory=".phpunit.cache"`** — PHPUnit y met son cache ; le dossier est gitignoré.
- **`<testsuite><directory>tests</directory>`** — PHPUnit ramasse **automatiquement** tout fichier
  `*Test.php` sous `tests/` (et `tests/Integration/`). Ajouter un test = créer un fichier, rien à
  déclarer. C'est pour ça que `DatabaseTestCase.php` (qui ne finit pas par `Test`) n'est pas pris
  pour une suite : c'est une classe de base abstraite, pas un test.

> **Réponse jury :** « Une seule suite qui balaie `tests/`. Le `bootstrap` y est branché, donc tout
> test démarre avec mes deux autoloaders en place. Pas de config par fichier. »

### 0.4 Où tournent les tests (le tradeoff assumé)

- **Tests purs + mock + sécurité applicative** : aucune base. Ils tournent partout, sur l'hôte comme
  dans le conteneur, en quelques millisecondes. C'est le cœur fiable.
- **Tests d'intégration** : besoin de l'extension `pdo_pgsql` et d'une Postgres. La base dev est
  exposée sur `localhost:5432` (`docker-compose.yml`). Sur l'hôte : activer `extension=pdo_pgsql`
  et pointer `DB_HOST=localhost` dans le `.env`. Sinon, les lancer dans le conteneur `app`.

> **Tradeoff à dire :** « Je lance le cœur (pur + sécurité) sur l'hôte, c'est identique partout. Les
> tests d'intégration ont besoin d'une vraie Postgres — je les pointe sur la base dev en localhost.
> Je n'embarque pas PHPUnit dans l'image de prod pour les jouer là-bas : ce serait alourdir l'image
> pour un gain nul au regard du diplôme. »

---

## Partie 1 — La décision qui structure toute la suite

Avant de lire les tests, **la seule idée à avoir comprise** : un composant est testable en unitaire
(sans base) **seulement si on peut lui substituer ses dépendances**. Solage contient les deux cas,
et c'est ce contraste qui justifie la structure de la suite :

| Composant | Sa dépendance à la base | Conséquence test |
|---|---|---|
| `SessionManager` | reçoit `UserModel` **par le constructeur** (injection) | on passe un **mock** → **unitaire, sans base** |
| `UserValidator::login()` | fait `new UserModel()` **en dur dans la méthode** | impossible à mocker → **seulement en intégration** |

C'est la **pyramide des tests** appliquée ici :

| Niveau | Cible | Pourquoi à ce niveau |
|---|---|---|
| Unitaire pur | `Utils`, `CsrfHelper` | logique pure, aucune dépendance → rapide, déterministe |
| Unitaire au mock | `SessionManager` | dépendance injectée → on la remplace par un mock |
| Intégration | `UserModel`, `UserValidator`, `PostModel`, `SearchModel` | la valeur **est dans le SQL** → il faut une vraie base |
| Sécurité | XSS, CSRF, mot de passe, SQLi | transversal, réparti sur les niveaux ci-dessus |

> **Réponse jury :** « La testabilité est une conséquence du design, pas un ajout après coup. Là où
> j'ai injecté la dépendance, je teste au mock sans base. Là où elle est instanciée en dur, je teste
> en intégration. Je sais nommer le refactor qui rendrait le validateur unitaire — injecter
> `UserModel` — et j'assume de ne pas l'avoir fait pour ne pas toucher du code qui marche. »

---

## Partie 2 — Tests unitaires purs

### 2.1 `tests/UtilsTest.php` — anti-XSS, AJAX, transport JSON

`Utils` contient trois fonctions statiques **pures** (aucune base) : `e()` (échappement HTML),
`isAjax()` (lit `$_SERVER`), `sendResponse()` (sérialise du JSON). C'est le **meilleur premier
test** : pur, déterministe, et `e()` est une fonction de sécurité.

**L'en-tête du fichier :**

```php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class UtilsTest extends TestCase
```

- **`extends TestCase`** — toute classe de test hérite de `TestCase` ; c'est elle qui fournit les
  `assert*`.
- **`#[Test]`** (l'attribut PHP 8) — marque une méthode comme test, à la place de préfixer son nom
  par `test`. On gagne des **noms de méthodes en français explicites**
  (`e_neutralise_une_balise_script`) qui se lisent comme un cahier en `--testdox`. C'est PHPUnit 11
  qui rend cette syntaxe moderne disponible.
- **`final`** — la classe n'a pas vocation à être étendue ; on le signale.

**Le `tearDown()` :**

```php
protected function tearDown(): void
{
    unset($_SERVER['HTTP_X_REQUESTED_WITH']);
}
```

`isAjax()` lit la superglobale `$_SERVER`. Les superglobales sont **partagées entre tests** dans le
même process. Si un test pose `HTTP_X_REQUESTED_WITH` et ne nettoie pas, le test suivant en hérite et
devient non déterministe. `tearDown()` (joué **après chaque test**) remet l'état propre. **Règle d'or
des tests : chaque test part d'un état connu.**

**Les méthodes de test, une par une :**

| Méthode | Ce qu'elle prouve | Pourquoi écrite ainsi |
|---|---|---|
| `e_neutralise_une_balise_script` | `<script>` ressort `&lt;script&gt;` | le cas XSS canonique : on vérifie que la balise brute **a disparu** ET que la version encodée **est présente** (deux assertions : ce qui ne doit plus être là, et ce qui doit y être) |
| `e_encode_les_chevrons` | `<b>` → `&lt;b&gt;` | même garantie sur une balise inoffensive : l'échappement est systématique, pas ciblé sur `script` |
| `e_encode_les_guillemets_doubles` | `"` → `&quot;` | `ENT_QUOTES` encode aussi les guillemets — important car on injecte parfois dans des attributs `attr="..."` |
| `e_encode_l_apostrophe_en_apos` | `'` → `&apos;` (et **plus** d'apostrophe brute) | **piège documenté** : avec `ENT_HTML5`, l'apostrophe devient `&apos;`, **pas** `&#039;`. Asserter `&#039;` ferait rougir le test alors que le code est correct |
| `e_encode_l_esperluette` | `&` → `&amp;` | l'esperluette doit être encodée en premier, sinon les autres entités casseraient |
| `e_transforme_null_en_chaine_vide` | `e(null)` → `''` | `e()` accepte `?string` ; le `?? ''` interne évite un `TypeError` quand une valeur de base est `NULL`. On teste le contrat, pas juste le cas nominal |
| `isAjax_vrai_avec_l_entete_xmlhttprequest` | en-tête présent → `true` | on **arrange** `$_SERVER` puis on vérifie ; le `tearDown` nettoiera |
| `isAjax_faux_sans_l_entete` | en-tête absent → `false` | le cas négatif, indispensable : un test qui ne vérifie que le « oui » ne prouve rien |
| `sendResponse_serialise_success_et_message` | `(true,'ok')` → `{"success":true,"message":"ok"}` | `sendResponse` fait un `echo` → on **capture la sortie** avec `ob_start()` / `ob_get_clean()`, puis on `json_decode` pour comparer des **structures**, pas des chaînes (robuste à l'ordre des clés/espaces) |
| `sendResponse_inclut_data_quand_non_vide` | `data` présent quand fourni | vérifie la branche `if ($data)` côté « vrai » |
| `sendResponse_omet_data_quand_falsy` | `data=[]` → clé `data` **absente** | vérifie la branche « faux » : `if ($data)` est faux pour `[]`. **Finding** : `data` est aussi omis pour `0` ou `''`. Pas forcément un bug, mais un comportement que le test **documente** |

**La technique `ob_start()` à comprendre** (elle revient) :

```php
ob_start();                       // commence à capturer tout echo
Utils::sendResponse(true, 'ok');  // au lieu d'aller à l'écran...
$json = ob_get_clean();           // ...le texte atterrit dans $json

$this->assertSame(
    ['success' => true, 'message' => 'ok'],
    json_decode($json, true)      // on compare des tableaux, pas du texte brut
);
```

On teste une fonction qui *affiche* sans laisser fuiter quoi que ce soit dans la sortie de PHPUnit,
et on compare la **structure décodée** (insensible aux espaces ou à l'ordre).

> **Réponse jury :** « `Utils::e` est ma défense XSS à la sortie : le test prouve qu'une charge
> `<script>` ressort inerte, et couvre chevrons, guillemets, apostrophe, esperluette, et `null`.
> `sendResponse` est mon transport JSON unique ; je capture sa sortie avec `ob_*` et je compare la
> structure décodée. Le test a même documenté un détail : `data` est omis quand il est *falsy*. »

### 2.2 `tests/CsrfHelperTest.php` — la sécurité CSRF en unitaire

`CsrfHelper` est **pur au sens test** : il ne touche que `$_SESSION`, qui n'est **qu'un tableau**
qu'on pilote depuis le test. Aucune base, aucun `session_start()` nécessaire. Rappel du code testé :

```php
public static function getToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
public static function verifyToken(?string $token): bool {
    return !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], (string) $token);
}
```

**Le `setUp()` — isolation obligatoire :**

```php
protected function setUp(): void { $_SESSION = []; }
```

`setUp()` tourne **avant chaque test**. On vide `$_SESSION` pour que chaque test parte d'une session
neuve. Sinon le token généré par un test « fuirait » dans le suivant et fausserait `getToken_stable`.

**Les méthodes, une par une :**

| Méthode | Ce qu'elle prouve | Pourquoi écrite ainsi |
|---|---|---|
| `getToken_genere_64_caracteres_hexadecimaux` | longueur **64**, `ctype_xdigit` vrai | `bin2hex(random_bytes(32))` = 32 octets → **64** caractères hexa. On vérifie la **forme** d'un token issu d'un CSPRNG, pas sa valeur (imprévisible) |
| `getToken_est_stable_dans_une_meme_session` | deux appels → **même** token | le `if (empty(...))` garantit **un token par session**, pas un par appel. Si chaque page régénérait le token, le formulaire et la session divergeraient |
| `verifyToken_accepte_le_bon_token` | token en session == fourni → `true` | le cas nominal |
| `verifyToken_rejette_un_mauvais_token` | mauvais token → `false` | la protection de base |
| `verifyToken_rejette_une_chaine_vide` | `''` → `false` | un attaquant pourrait envoyer un champ vide ; il ne doit pas passer |
| `verifyToken_rejette_null` | `null` → `false` | le paramètre est `?string` ; un POST sans le champ donne `null` |
| `verifyToken_rejette_quand_aucun_token_en_session` | session vide → `false` même avec un « bon » token | prouve que le `!empty($_SESSION['csrf_token'])` **court-circuite** : sans token côté serveur, **rien** ne passe (pas de comparaison `hash_equals('', '')` qui renverrait `true`) |
| `field_rend_un_input_cache_avec_le_token` | HTML contient `name="csrf_token"` et le token | `field()` génère l'input caché des formulaires ; on vérifie qu'il porte bien le bon token |

**Le piège qu'on ne peut PAS tester en unitaire — à dire :** `verifyToken` utilise `hash_equals`,
une comparaison **à temps constant** (anti-timing-attack). On **ne peut pas** prouver la résistance
au timing dans un test unitaire. On teste donc la **correction** (bon/mauvais/vide/null) et on
**argumente** que `hash_equals` est timing-safe. Distinguer ce qu'un test prouve de ce qu'il
n'atteint pas, c'est une réponse de senior.

> **Réponse jury :** « Le token CSRF, c'est 32 octets de `random_bytes` (CSPRNG), stable par session.
> La vérification rejette le token absent, vide, nul ou faux — et surtout, sans token côté serveur,
> rien ne passe. La comparaison est `hash_equals`, à temps constant : je le teste pour la
> correction, je l'argumente pour le timing. »

---

## Partie 3 — Test unitaire au mock (le bénéfice de l'injection de dépendance)

### `tests/SessionManagerTest.php`

C'est **la démonstration** que l'injection de dépendance paie. `SessionManager` reçoit son
`UserModel` par le constructeur :

```php
public function __construct(UserModel $userModel) {
    $this->userModel = $userModel;
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    if (isset($_SESSION['user_id'])) {
        $this->user = $this->userModel->getUserById($_SESSION['user_id']);
    }
}
```

**Le point clé.** Le **vrai** `UserModel::__construct` appelle `Database::getConnection()` — donc
instancier un vrai `UserModel` ouvre une connexion. Mais `createMock(UserModel::class)` crée un
**faux** objet de la même classe **sans appeler son constructeur réel**. On lui dit quoi répondre,
méthode par méthode. Résultat : on teste `login()` et `isAdmin()` **sans jamais toucher la base**.
C'est *exactement* ce que l'injection rend possible : le `SessionManager` ne sait pas si on lui passe
un vrai modèle ou un mock, il utilise juste ce qu'on lui donne.

**Le `setUp()` — subtil, et important à savoir expliquer :**

```php
protected function setUp(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_cookies', '0');
        session_start();
    }
    $_SESSION = [];
}
```

- On démarre la session **nous-mêmes, en premier**. Ainsi, quand le constructeur de `SessionManager`
  fait son `if (session_status() === PHP_SESSION_NONE) session_start()`, la session **est déjà
  active** → il ne la relance pas (ce qui réinitialiserait `$_SESSION` et écraserait nos valeurs).
- **`session.use_cookies = '0'`** : en CLI, `session_start()` essaie d'envoyer un cookie via un
  en-tête HTTP. Comme PHPUnit a déjà produit de la sortie, ça déclencherait un warning « headers
  already sent ». On désactive les cookies : en test on ne valide que **l'état serveur**
  (`$_SESSION`), pas l'envoi d'en-têtes.
- `$_SESSION = []` à la fin : on repart d'une session vide à chaque test.

**Les méthodes :**

**`login_peuple_la_session_depuis_le_modele`** — le test le plus instructif :

```php
$user = $this->createMock(UserModel::class);
$user->method('getName')->willReturn('Garnier');
$user->method('getImage')->willReturn('🛡️');
$user->method('getRole')->willReturn('3');

$model = $this->createMock(UserModel::class);
$model->method('getUserById')->with(2)->willReturn($user);

$session = new SessionManager($model);
$session->login(2);

$this->assertSame(2, $_SESSION['user_id']);
$this->assertSame('Garnier', $_SESSION['name']);
// ... + isLoggedIn() true, getUser() === $user
```

- **Deux mocks** : `$user` (l'utilisateur renvoyé) et `$model` (le `UserModel` injecté).
  `$model->getUserById(2)` est programmé pour renvoyer `$user`.
- **Pourquoi `$user` est lui-même un mock et PAS un vrai `UserModel` ?** Parce que `new UserModel(...)`
  rappellerait `Database::getConnection()` → on retomberait sur la base. Le mock court-circuite.
- On vérifie ensuite que `login(2)` a bien **recopié** les champs du modèle dans `$_SESSION`
  (`name`, `image`, `role`) et que `isLoggedIn()` passe à `true`, `getUser()` renvoie l'objet.
  Autrement dit : `login()` est le pont modèle → session, et le test prouve que le pont fait passer
  les bonnes données.

**`isAdmin_vrai_pour_le_role_admin` / `isAdmin_faux_pour_un_role_non_admin`** :

```php
$admin = $this->createMock(UserModel::class);
$admin->method('getRoleName')->willReturn('Admin');
$model = $this->createMock(UserModel::class);
$model->method('getUserById')->with(1)->willReturn($admin);

$_SESSION['user_id'] = 1;          // ← pour que le CONSTRUCTEUR charge l'utilisateur
$session = new SessionManager($model);
$this->assertTrue($session->isAdmin());
```

- Ici on pose `$_SESSION['user_id'] = 1` **avant** de construire le `SessionManager`, parce que
  `isAdmin()` se base sur `$this->user`, qui est chargé **par le constructeur** quand `user_id`
  existe en session. C'est le miroir du test `login` : là on voulait `$_SESSION` **vide** pour que le
  constructeur n'appelle pas `getUserById` ; ici on le veut **rempli** pour qu'il l'appelle.
- Le second test (rôle `Modérateur` → `false`) prouve que `isAdmin()` compare **strictement** à
  `'Admin'` et ne laisse pas passer un autre rôle. Le cas négatif est ce qui donne sa valeur au test.

> **Réponse jury :** « `SessionManager` est ma preuve d'injection de dépendance : je lui passe un
> `UserModel` mocké, donc je teste `login()` et `isAdmin()` sans base, en millisecondes. Le mock
> saute le constructeur qui, sinon, ouvrirait une connexion. C'est le contraste exact avec
> `UserValidator`, qui instancie son modèle en dur et n'est donc testable qu'en intégration. »

---

## Partie 4 — Tests d'intégration (vraie Postgres, isolés)

Ici on teste ce dont la valeur **est dans le SQL** : `UserModel`, `UserValidator`, `PostModel`,
`SearchModel`. Pas de mock : on veut prouver que les vraies requêtes font la vraie chose. Le défi :
**ne pas polluer la base**. La solution est dans la classe de base.

### 4.1 `tests/Integration/DatabaseTestCase.php` — la base jetable

```php
abstract class DatabaseTestCase extends TestCase
{
    protected PDO $db;

    protected function setUp(): void {
        $this->db = Database::getConnection();
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    protected function tearDown(): void {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }
}
```

**Le pattern : chaque test dans une transaction annulée à la fin.** C'est le pattern standard des
tests BDD, et il tient sur **un fait** : `Database` est un **singleton**. `getConnection()` renvoie
**toujours le même PDO**. Donc :

1. `setUp()` ouvre une transaction sur ce PDO partagé.
2. Le test appelle un modèle (ex. `createPost()`). Le modèle fait son `INSERT`… **sur le même PDO**,
   donc **dans la même transaction**.
3. `tearDown()` fait `rollBack()` → **tout ce que le test a écrit disparaît**.

Résultat : la base revient à son état **exact** avant/après chaque test. Les tests sont **rejouables
à l'infini** sans accumuler de données. Pas de base de test séparée à gérer.

**Les deux `if (...->inTransaction())` :** garde-fous. PostgreSQL via PDO **ne gère pas les
transactions imbriquées** sans savepoints. Si un test (ou un teardown précédent) a déjà laissé une
transaction ouverte/fermée, ces gardes évitent un `beginTransaction` sur transaction déjà ouverte ou
un `rollBack` sur transaction déjà annulée — qui lèveraient une exception parasite.

**`abstract`** : cette classe n'est **pas** une suite de tests, c'est un socle. La marquer `abstract`
(et ne pas la nommer `*Test`) garantit que PHPUnit ne tente pas de l'exécuter seule.

> **Réponse jury :** « Chaque test d'intégration tourne dans une transaction annulée au teardown : la
> base est jetable, l'état identique avant/après. Comme `Database` est un singleton PDO, le modèle et
> le test partagent la même transaction — donc le rollback annule aussi ce que le modèle a inséré.
> Zéro pollution, zéro base de test à maintenir. »

### 4.2 `tests/Integration/UserModelTest.php` — comptes & mot de passe

```php
final class UserModelTest extends DatabaseTestCase
```

**Le helper privé `insertUser()` :**

```php
private function insertUser(string $email, string $plainPassword): int {
    $stmt = $this->db->prepare(
        'INSERT INTO users (name, email, password, role)
         VALUES (:name, :email, :password, :role) RETURNING id'
    );
    $stmt->execute([
        ':name'=>'Test', ':email'=>$email,
        ':password'=>password_hash($plainPassword, PASSWORD_BCRYPT),
        ':role'=>2,
    ]);
    return (int) $stmt->fetchColumn();
}
```

- **Pourquoi un INSERT direct en PDO et pas `createUser()` du modèle ?** Parce que `createUser()` est
  couplé au docroot (voir le test dédié plus bas) — l'utiliser pour *arranger* tous les autres tests
  les ferait tous casser pour une raison sans rapport. On **arrange** avec un INSERT minimal et
  contrôlé, on **teste** le modèle séparément. Séparer l'arrangement de ce qu'on vérifie est une
  bonne pratique.
- **`password_hash(...PASSWORD_BCRYPT)`** dès l'insertion : on veut une donnée **réaliste** (un hash,
  comme en prod), pour ensuite prouver que le modèle/`password_verify` fonctionnent dessus.
- **`RETURNING id`** : syntaxe PostgreSQL pour récupérer l'id généré en une requête.
- L'INSERT vit **dans la transaction** ouverte par `setUp()` → annulé au `tearDown()`.

**Les tests :**

| Méthode | Ce qu'elle prouve | Pourquoi écrite ainsi |
|---|---|---|
| `getUserByEmail_retourne_l_utilisateur_existant` | email connu → objet, bon email | la lecture de base : on insère, on relit via le modèle, on vérifie l'identité |
| `getUserByEmail_retourne_null_si_inconnu` | email absent → `null` | le cas négatif : le modèle ne renvoie pas un objet vide ou une erreur, mais `null` (contrat que les appelants attendent) |
| `le_mot_de_passe_est_stocke_hashe` | hash **≠** `'secret'` **et** `password_verify('secret', $hash)` vrai | **test de sécurité clé** : on prouve que rien n'est stocké en clair, et que le hash est valide. Deux assertions complémentaires |
| `createUser_stocke_un_mot_de_passe_hashe` | `createUser()` insère bien un mdp hashé | teste la **vraie** méthode du modèle — voir le piège du `chdir` |
| `login_valide_avec_les_bons_identifiants` | `UserValidator::login(bon)` → `ok=true` | la chaîne complète validateur → modèle → SQL → `password_verify` |
| `login_refuse_un_mauvais_mot_de_passe` | mauvais mdp → `ok=false`, message « mot de passe » | cas négatif ; on vérifie aussi le **message**, pas juste le booléen |
| `login_refuse_un_email_inexistant` | email absent → `ok=false`, « n'existe pas » | la branche `if (!$user)` du validateur |
| `login_refuse_des_champs_vides` | `('','')` → `ok=false` | la garde `empty()` en tête de validateur |
| `register_refuse_un_email_deja_utilise` | email pris → `ok=false`, « déjà utilisé » | unicité de l'email |
| `register_accepte_un_email_libre` | email libre → `ok=true` | le cas nominal d'inscription |

**Le piège majeur, révélé par le test `createUser` — à mettre en avant :**

```php
$cwd = getcwd();
chdir(__DIR__ . '/../../public');     // on se place dans le docroot
try {
    $ok = (new UserModel())->createUser('Nouveau', 'create@test.io', 'secret');
} finally {
    chdir($cwd);                       // on restaure TOUJOURS, même en cas d'exception
}
```

`UserModel::createUser()` fait `include 'assets/emojiList.php'` — un **chemin relatif** au répertoire
courant. En production le docroot est `public/`, donc le fichier résout. Mais en PHPUnit le répertoire
courant est la **racine du projet** → le fichier n'existe pas là → l'insertion casserait. Le test
contourne en se plaçant temporairement dans `public/` (`chdir`), avec un `try/finally` pour
**toujours** restaurer le répertoire (sinon les tests suivants partiraient du mauvais dossier).

**C'est exactement la valeur d'un plan de tests : écrire ce test a mis au jour un vrai couplage au
docroot.** À l'oral, ce n'est pas une faiblesse, c'est un point fort : « mes tests ont trouvé un
défaut latent. Le correctif (chemin absolu via `__DIR__`) est trivial, je l'ai laissé hors périmètre
pour ne pas mélanger avec l'ajout des tests. »

> **Réponse jury :** « L'intégration prouve la chaîne complète validateur → modèle → SQL →
> `password_verify` : un mot de passe est stocké hashé, jamais en clair ; une bonne connexion passe,
> une mauvaise est rejetée avec le bon message. Et écrire ces tests a révélé un couplage :
> `createUser` dépend du docroot via un `include` relatif — je l'ai documenté. »

### 4.3 `tests/Integration/PostModelTest.php` — la publication

Même socle (`extends DatabaseTestCase`), même logique d'arrangement.

**Deux helpers :**

- `insertUser()` — insère un auteur (la table `posts` a une clé étrangère `user_id` → il faut un
  utilisateur réel pour pouvoir créer un post).
- `newPost($userId, $content)` — fabrique un `PostModel` non encore persisté. Le constructeur prend
  beaucoup de paramètres (`id, user_id, content, date, likes, replyTo, image, replyToParent`) ; le
  helper masque ce bruit pour que chaque test reste lisible.

**Les tests :**

| Méthode | Ce qu'elle prouve | Pourquoi écrite ainsi |
|---|---|---|
| `createPost_insere_et_renvoie_un_id` | `createPost()` renvoie un id > 0 (pas `false`) | la création doit confirmer son succès par un id exploitable |
| `getPostById_lit_le_post_cree` | on relit le post créé : bon contenu, bon `user_id` | cycle écriture→lecture ; on vérifie que ce qu'on a écrit est bien ce qu'on relit |
| `getPostById_retourne_null_si_inconnu` | id improbable (`999999999`) → `null` | cas négatif ; pas d'exception sur id absent |
| `delete_supprime_le_post` | après `delete()`, le post est introuvable | on prouve l'effet **et** sa conséquence : `delete()` renvoie `true`, puis `getPostById` renvoie `null` |
| `delete_retourne_false_si_post_inexistant` | supprimer un id absent → `false` | `delete` distingue « supprimé » de « rien à supprimer » |
| `getAllPostsByUserId_ne_retourne_que_les_posts_de_l_auteur` | 2 posts créés → `count === 2` | on insère **nos** données dans la transaction et on compte, sans dépendre du `seed.sql` (déterminisme) |

**Le point déterminisme à savoir expliquer :** le dernier test crée exactement 2 posts pour **son**
utilisateur (fraîchement inséré) et vérifie `count === 2`. Comme l'utilisateur est créé dans la
transaction, **aucun** post du `seed.sql` ne lui appartient → le compte est exact et reproductible.
Si on avait testé sur un utilisateur du seed, le compte aurait dépendu des données pré-existantes.

> **Réponse jury :** « `PostModel` couvre le parcours "publier" : créer (id renvoyé), lire (ce que
> j'écris, je le relis), supprimer (introuvable après). Je crée mes propres données dans la
> transaction, donc mes comptes sont déterministes, indépendants du seed. »

### 4.4 `tests/Integration/SqlInjectionTest.php` — la preuve anti-injection

Le test de sécurité le plus parlant. Il prouve **par l'exemple** que les requêtes préparées rendent
une charge d'injection **inerte**. Code testé (`SearchModel::searchPosts`) :

```php
$query = '%' . $query . '%';
// ...
WHERE p.content LIKE ?
// $this->db->prepare($sql)->execute([$query]);
```

**Le `setUp()` — l'arrangement :**

```php
protected function setUp(): void {
    parent::setUp();                  // ← ouvre la transaction (ne PAS l'oublier)
    // insère 1 utilisateur + 1 post au contenu unique :
    // self::MARQUEUR = 'SqliProbe_4242_contenu_unique'
}
```

- **`parent::setUp()`** d'abord : sinon on perd la transaction de la classe de base et le nettoyage
  ne se ferait pas. On **étend** le comportement parent, on ne le remplace pas.
- On insère un post au contenu **unique** (`MARQUEUR`), pour que le test « recherche normale » ait
  exactement 1 résultat à trouver, sans ambiguïté avec d'autres données.

**Les deux tests, qui ne valent **qu'ensemble** :**

| Méthode | Entrée | Attendu | Ce que ça prouve |
|---|---|---|---|
| `une_charge_d_injection_reste_inerte` | `' OR '1'='1` | **0** résultat, **aucune** exception | la charge est entourée de `%...%` et envoyée en **paramètre lié** → cherchée **littéralement** (`LIKE '%'' OR ''1''=''1%'`). Aucune ligne ne contient ce texte. Si l'injection marchait, on aurait **toutes** les lignes |
| `une_recherche_normale_fonctionne` | le `MARQUEUR` | **1** résultat | la recherche **fonctionne** quand même sur une entrée légitime |

**Pourquoi les deux ensemble :** le premier seul pourrait passer pour une mauvaise raison (et si la
recherche ne marchait jamais ?). Le second prouve que la recherche est **réellement opérationnelle**.
Ensemble : l'entrée est traitée comme **donnée** (un motif `LIKE` littéral), **jamais** comme du SQL.
C'est la définition même d'une requête préparée, démontrée et non juste affirmée.

> **Réponse jury :** « La parade SQLi est structurelle : requêtes préparées PDO partout, jamais
> d'interpolation. Le test le prouve par l'exemple — une charge `' OR '1'='1` ne renvoie rien et ne
> lève rien, alors qu'un terme normal renvoie son post. L'entrée est une donnée, pas du code. »

---

## Partie 5 — Exécuter la suite et lire la sortie

```bash
php vendor/bin/phpunit                 # toute la suite
php vendor/bin/phpunit --testdox       # une phrase lisible par test (sortie "cahier")
php vendor/bin/phpunit --filter Csrf   # une seule classe / un seul cas
```

- **`--testdox`** transforme les noms de méthodes en phrases : c'est la capture idéale pour le
  dossier (chaque test devient une ligne de spécification lisible).
- **La suite verte EST la preuve d'exécution** demandée par C9, **et** sert de **non-régression** :
  on la rejoue après chaque modification ; si une régression casse un comportement testé, ça rougit.

**La note honnête sur les « deprecations » (à connaître, ça peut être demandé) :** sur une machine en
**PHP 8.4**, la suite affiche `OK, but there were issues` avec quelques *deprecations*. Elles ne
viennent **pas** du code de test : ce sont des appels internes de **PHPUnit 11.0.0** que **PHP 8.4**
signale comme dépréciés (paramètres implicitement nullables). Sous **PHP 8.3** — la cible Docker du
projet — elles n'apparaissent pas. À dire tel quel : « les tests passent, les avertissements sont
internes à PHPUnit sous PHP 8.4, pas dans mon code, et disparaissent sur le PHP 8.3 ciblé. »
Au besoin, on les masque avec `--display-deprecations` pour les inspecter, ou en figeant un
PHPUnit 11 patch plus récent.

---

## Partie 6 — Antisèche jury (à réviser la veille)

**La phrase d'ouverture :** « J'ai une suite PHPUnit en une commande : unitaires purs pour la logique
et la sécurité, un test au mock pour montrer l'injection de dépendance, et des tests d'intégration
isolés par transaction contre une vraie Postgres, dont une preuve anti-injection SQL. »

| Question probable | Réponse d'une phrase |
|---|---|
| « Pourquoi un bootstrap maison ? » | « Le projet n'a pas d'autoload PSR-4 : je branche l'autoloader Composer (PHPUnit) **et** mon autoloader maison (l'app), exactement comme en prod. » |
| « Comment tu testes sans casser la base ? » | « Chaque test d'intégration tourne dans une transaction annulée au teardown ; comme `Database` est un singleton, le modèle et le test partagent ce PDO, donc le rollback annule tout. » |
| « Pourquoi un mock pour `SessionManager` mais pas pour `UserValidator` ? » | « `SessionManager` reçoit son modèle par injection → je le mocke, zéro base. `UserValidator` fait `new UserModel()` en dur → je ne peux le tester qu'en intégration. La testabilité suit le design. » |
| « C'est quoi ta preuve anti-injection ? » | « Une charge `' OR '1'='1` passée en paramètre lié ne renvoie rien et ne lève rien, alors qu'un terme normal renvoie son post : l'entrée est une donnée, pas du SQL. » |
| « Ces tests ont-ils trouvé quelque chose ? » | « Oui : `createUser` est couplé au docroot via un `include` relatif. Le test l'a révélé, je le contourne par `chdir` et je l'ai documenté ; le correctif est un chemin absolu. » |
| « Et ce qui n'est pas testé automatiquement ? » | « Le CSRF et l'IDOR bout-en-bout (ils dépendent de `$_SERVER`, `php://input`, de méthodes statiques) : je les valide fonctionnellement en curl — 403 + ligne de log. Plus honnête qu'un refactor lourd juste pour le test. » |

**La seule grande idée à ne pas rater :** *la testabilité est une conséquence du design.* Là où j'ai
injecté la dépendance, je teste au mock sans base ; là où je ne l'ai pas fait, je teste en
intégration ; et là où le code lit des superglobales bas-niveau, je teste fonctionnellement. Le choix
du niveau de test est **délibéré**, jamais subi.
