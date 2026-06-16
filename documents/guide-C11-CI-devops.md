# Guide C11 — Mettre en place l'intégration continue (CI)

> **But du guide** : faire passer **C11 (Contribuer à la mise en production — DevOps)** de
> *Partiel* à *Validé*, en montant un pipeline d'**intégration continue** sur GitHub Actions.
> Tu fais tout toi-même : chaque étape donne le fichier exact, la commande exacte, et la
> vérification à faire **en local avant de pousser**.
>
> **Principe directeur** : le critère officiel du référentiel est *« les scripts d'intégration
> continue s'exécutent **sans erreur** »*. Donc on construit le pipeline **vert à chaque étape** :
> on n'ajoute un outil à la CI qu'une fois qu'il passe en local. On ne pousse jamais un pipeline
> rouge « qu'on corrigera plus tard ».

---

## 1. Où en est C11 aujourd'hui

C11 est *Partiel* parce que **les briques DevOps existent, mais rien ne les automatise**.

| Critère C11 (référentiel) | État actuel | Preuve |
|---|---|---|
| Conteneurs | ✅ Fait | `docker-compose.yml` (dev) + `docker-compose.prod.yml` (prod), `Dockerfile` multi-étapes FrankenPHP |
| Migrations rejouables (idempotentes) | ✅ Fait | `src/Migrations.php` + `bin/migrate.php` (service `migrate` dans les deux stacks) |
| Outil de qualité de code | ✅ Fait | PHP_CodeSniffer (PSR-12), `phpcs.xml` — **passe en vert** (46/46) |
| **Scripts d'intégration continue** | ❌ **Manquant** | aucun fichier dans `.github/workflows/` |
| **Automatisation des tests** | ❌ **Manquant** | pas de PHPUnit, pas de dossier `tests/` *(recoupe C9)* |
| **Rapports CI interprétés** | ❌ **Manquant** | il faut une capture d'un run + son commentaire |

**Ce qu'il reste à produire**, donc : un fichier de pipeline qui, à chaque `push`, lance les
outils de qualité, les tests, et construit l'image (le livrable) — puis une **capture commentée**
du rapport. C'est exactement ce que ce guide te fait faire.

> 💡 Le dépôt distant est déjà `github.com/adamctr/solage` → **GitHub Actions** est le choix
> naturel (c'est le « serveur d'automatisation » du critère C11, hébergé, gratuit, zéro install).

---

## 2. Stratégie en 4 niveaux

| Niveau | Ce que tu ajoutes | Effort | Effet sur C11 |
|---|---|---|---|
| **1 — Cœur** *(obligatoire)* | Pipeline GitHub Actions : `phpcs` + `docker build` | 15 min | **Fait basculer C11** : serveur d'automatisation paramétré, qualité + livrable vérifiés à chaque push, vert |
| **2 — Tests** *(recoupe C9)* | PHPUnit + 2 tests réels (XSS, CSRF) branchés dans la CI | 30–45 min | Coche *« automatisation des tests »* |
| **3 — Analyse statique** *(recommandé)* | PHPStan branché dans la CI | 30 min | 2ᵉ outil de qualité — argument fort à l'oral |
| **4 — Bonus** | ESLint (JS) et/ou déploiement continu (CD) | variable | Pour aller plus loin, non requis |

> **Le minimum défendable, c'est le Niveau 1.** Si tu manques de temps, fais 1 + l'étape finale
> (capture commentée) et C11 est défendable. Les niveaux 2 et 3 le renforcent nettement.

⚠️ **Pré-requis local** : ton PHP local est en **8.2**, le projet cible **8.3** (voir `Dockerfile`).
Ce n'est pas grave : la CI épingle 8.3 pour coller à la prod, et `phpcs`/`phpunit`/`phpstan`
tournent très bien en 8.2 pour tes vérifications locales.

---

## Niveau 1 — Le pipeline cœur (obligatoire)

### Étape 1.1 — Vérifier que la qualité passe en local

Avant d'écrire quoi que ce soit, confirme que la brique qualité est verte (elle l'est) :

```powershell
vendor/bin/phpcs --report=summary
```

Tu dois voir `46 / 46 (100%)` et **aucune erreur** (code de sortie 0). C'est la garantie que la
première étape du pipeline sera verte.

### Étape 1.2 — Créer le fichier de pipeline

Crée le dossier `.github/workflows/` puis le fichier **`.github/workflows/ci.yml`** :

```yaml
name: CI

# Déclenché à chaque push sur n'importe quelle branche.
on: [push]

jobs:
  # ---- Job 1 : qualité du code -------------------------------------------
  qualite:
    name: Qualité du code (PHP 8.3)
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Installer PHP 8.3 + Composer
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          tools: composer:v2
          coverage: none

      - name: Mettre en cache les dépendances Composer
        uses: actions/cache@v4
        with:
          path: vendor
          key: composer-${{ hashFiles('composer.lock') }}
          restore-keys: composer-

      - name: Installer les dépendances
        run: composer install --no-interaction --no-progress

      - name: Vérifier le style PSR-12 (PHP_CodeSniffer)
        run: vendor/bin/phpcs --report=full

  # ---- Job 2 : construction du livrable ----------------------------------
  image:
    name: Build de l'image Docker (livrable)
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Construire l'image
        run: docker build -t solage-app .
```

**Pourquoi cette forme (réponses jury) :**

- **Deux jobs parallèles** → deux pastilles vertes lisibles dans l'interface : « le code est
  propre » *et* « le livrable se construit ».
- **`phpcs`** = mon outil de qualité de code, exécuté automatiquement à chaque push (plus de
  « j'ai oublié de lancer le linter »).
- **`docker build`** = je vérifie que **le livrable se construit** à chaque modification ; une
  image qui ne build pas est détectée immédiatement, pas le jour du déploiement.
- **PHP 8.3** dans la CI = **même version qu'en production** (FrankenPHP `php8.3`) ; je teste sur
  l'environnement cible, pas sur ma machine.

### Étape 1.3 — Vérifier, committer, pousser

```powershell
git add .github/workflows/ci.yml
git commit -m "ci: ajout du pipeline d'intégration continue (PSR-12 + build image)"
git push
```

### Étape 1.4 — Lire le rapport

Va sur **GitHub → onglet *Actions***. Tu vois ton run « CI » avec les deux jobs. Attends les deux
✅. Si l'un est ❌, ouvre-le, lis l'étape rouge, corrige **en local**, re-pousse.

> ✅ **À ce stade C11 bascule** : tu as un serveur d'automatisation paramétré, des scripts CI qui
> s'exécutent sans erreur, et la construction du livrable vérifiée à chaque push. Les niveaux
> suivants renforcent.

---

## Niveau 2 — Automatiser les tests (PHPUnit) · recoupe C9

C11 demande *« l'automatisation des tests »* ; C9 demande les tests eux-mêmes. **On fait d'une
pierre deux coups** : on installe PHPUnit, on écrit 2 tests qui collent à ton travail de sécurité
(échappement XSS et jetons CSRF), puis on les branche dans la CI.

### Étape 2.1 — Installer PHPUnit

```powershell
composer require --dev phpunit/phpunit ^11
```

### Étape 2.2 — Configurer PHPUnit

Crée **`phpunit.xml`** à la racine :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         failOnWarning="true">
    <testsuites>
        <testsuite name="Solage">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

Crée **`tests/bootstrap.php`** (il branche l'autoloader maison pour que les tests trouvent tes
classes de `src/`) :

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';   // PHPUnit + dépendances
require __DIR__ . '/../includes/autoload.php'; // autoloader maison du projet
Autoloader::register();
```

### Étape 2.3 — Écrire deux tests réels

Ces deux tests **passent tels quels** et démontrent des protections que tu défends déjà (anti-XSS,
anti-CSRF) — parfait pour le jury.

Crée **`tests/UtilsTest.php`** :

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase
{
    public function testEchappeLesCaracteresHtml(): void
    {
        // Utils::e() est le rempart anti-XSS utilisé dans les vues.
        $this->assertSame('&lt;script&gt;', Utils::e('<script>'));
    }

    public function testEAccepteNull(): void
    {
        $this->assertSame('', Utils::e(null));
    }
}
```

Crée **`tests/CsrfHelperTest.php`** :

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CsrfHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testLeJetonEstStableDansLaSession(): void
    {
        $jeton = CsrfHelper::getToken();
        $this->assertSame($jeton, CsrfHelper::getToken());
    }

    public function testVerifyTokenAccepteLeBonJetonRejetteLesMauvais(): void
    {
        $jeton = CsrfHelper::getToken();
        $this->assertTrue(CsrfHelper::verifyToken($jeton));
        $this->assertFalse(CsrfHelper::verifyToken('mauvais-jeton'));
        $this->assertFalse(CsrfHelper::verifyToken(null));
    }
}
```

### Étape 2.4 — Vérifier en local

```powershell
vendor/bin/phpunit
```

Tu dois voir `OK (4 tests, ...)` en vert. Tant que ce n'est pas vert, **on ne touche pas à la CI**.

### Étape 2.5 — Brancher dans la CI

Dans `.github/workflows/ci.yml`, ajoute cette étape dans le job **`qualite`**, juste après
l'étape PSR-12 :

```yaml
      - name: Lancer les tests unitaires (PHPUnit)
        run: vendor/bin/phpunit
```

Committe, pousse, vérifie le ✅ dans l'onglet Actions.

> 🔗 **Lien avec C9** : ces 2 tests sont un point de départ. Pour C9, étoffe `tests/` avec les cas
> listés dans le dossier (validation `UserValidator`, contrôle de propriété anti-IDOR, etc.).
> Chaque test ajouté tourne automatiquement dans la CI — c'est ça, l'automatisation des tests.

---

## Niveau 3 — Analyse statique (PHPStan) · recommandé

Un **deuxième** outil de qualité, qui détecte des bugs sans exécuter le code (types incohérents,
méthodes inexistantes, variables non définies). Argument solide à l'oral : *« PSR-12 vérifie la
forme, PHPStan vérifie le fond. »*

### Étape 3.1 — Installer PHPStan

```powershell
composer require --dev phpstan/phpstan
```

### Étape 3.2 — Configurer PHPStan

Crée **`phpstan.neon`** à la racine. On **démarre au niveau 1** (on monte les niveaux
progressivement — démarche honnête et défendable) :

```neon
parameters:
    level: 1
    paths:
        - bin
        - includes
        - modules
        - src
```

> Pas besoin de configurer l'autoloader maison ici : PHPStan lit directement les fichiers des
> dossiers listés, il y trouve donc tes classes globales.

### Étape 3.3 — Lancer et obtenir le vert

```powershell
vendor/bin/phpstan analyse
```

- **S'il ne reste que quelques erreurs triviales** → corrige-les (c'est le but).
- **S'il reste des erreurs que tu ne veux pas traiter maintenant** → fige-les dans une *baseline*,
  ce qui rend la CI verte tout en gardant la dette visible et datée :

```powershell
vendor/bin/phpstan analyse --generate-baseline
```

Cela crée `phpstan-baseline.neon`. Ajoute-le en haut de `phpstan.neon` :

```neon
includes:
    - phpstan-baseline.neon

parameters:
    level: 1
    paths:
        - bin
        - includes
        - modules
        - src
```

> **Réponse jury sur la baseline** : *« J'ai introduit l'analyse statique sur du code existant.
> La baseline gèle les alertes héritées pour que la CI reste verte, mais elle est versionnée et
> dégonfle à chaque correction : tout nouveau code est analysé strictement. »* C'est exactement
> la pratique en entreprise.

### Étape 3.4 — Brancher dans la CI

Toujours dans le job `qualite`, après l'étape PSR-12 :

```yaml
      - name: Analyse statique (PHPStan)
        run: vendor/bin/phpstan analyse --no-progress --memory-limit=512M
```

Vérifie en local (`vendor/bin/phpstan analyse`) **avant** de pousser.

---

## Niveau 4 — Bonus (facultatif)

À ne faire que si les niveaux 1–3 sont bouclés et qu'il te reste du temps.

### 4a — Lint du JavaScript (ESLint)

Tu as 2 fichiers JS écrits à la main (`public/scripts/index.js`, `public/scripts/dynamicMessages.js`).
Un lint JS complète l'argument « qualité multi-langage ». Cela ajoute une chaîne Node :
`npm init -y`, `npm install -D eslint`, un fichier `eslint.config.js`, puis un job CI avec
`actions/setup-node` + `npx eslint public/scripts`. **Optionnel** — ne le fais que si tu es à
l'aise avec l'outillage Node, sinon ça ajoute du bruit pour peu de points.

### 4b — Déploiement continu (le « CD » de CI/CD)

Le **CD** automatise le déploiement après un build vert (souvent via SSH vers ton serveur, ou
publication de l'image dans un registre). C'est un **bonus** : il faut un serveur, des secrets, et
ça dépasse le strict attendu C11. **À l'oral, il suffit de savoir l'expliquer** : *« mon pipeline
fait l'intégration continue ; le déploiement continu serait l'étape suivante — un job qui, sur la
branche `main` verte, se connecte en SSH au serveur, fait `git pull` + `docker compose up -d` +
migrations. Je ne l'ai pas activé faute de serveur permanent. »* Savoir distinguer **CI vs CD** et
décrire ton CD cible vaut autant que de le coder.

---

## Étape finale (obligatoire) — Interpréter le rapport CI

C'est un critère C11 à part entière : *« les rapports de l'intégration continue sont interprétés »*.

1. **Capture** un run vert depuis l'onglet *Actions* (la vue avec les jobs et leurs ✅).
2. **Capture aussi** le détail d'une étape (ex. la sortie `46 / 46` de PHPCS, ou `OK (n tests)`).
3. **Rédige 3–4 phrases d'interprétation** dans le dossier, par exemple :

> *« À chaque push, le pipeline installe PHP 8.3, vérifie le style PSR-12, lance les tests
> unitaires et construit l'image Docker. Le run ci-dessous est vert : les 4 tests passent, le code
> respecte PSR-12 et l'image se construit. Un run rouge bloquerait le merge et pointerait l'étape
> fautive — par exemple un test cassé par une régression — que je corrige avant d'intégrer. »*

C'est cette phrase qui prouve que tu **interprètes** le rapport, pas seulement que tu l'as produit.

---

## Mettre le dossier à jour (pour que la note suive)

Une fois la CI réelle en place, **aligne le dossier** (il décrit aujourd'hui la CI comme « à
implémenter ») :

1. **`documents/dossier-latex/chapters/04e-tests.tex`** (§ DevOps, ~ligne 112) : remplace le bloc
   `\begin{todo}{Pipeline...}` + le squelette « à implémenter » par **ton vrai `ci.yml`**, la
   **capture** du run vert (un `figure`) et le **paragraphe d'interprétation** ci-dessus.
2. **`documents/dossier-latex/chapters/01-competences.tex`** (ligne C11, ~31) : remplace
   `\textcolor{todoOrange}{\textbf{Partiel}}` par le style « validé » utilisé pour tes autres
   compétences OK.
3. **`documents/examen-cda/03-MAPPING-COMPETENCES.md`** et
   **`documents/examen-cda/00-README-DEROULE-EXAMEN.md`** : passe le statut C11 de 🟠 à ✅.

---

## Récapitulatif : critères C11 → preuves

| Critère officiel C11 | Preuve après ce guide |
|---|---|
| Conteneurs / `docker compose` | `docker-compose{,.prod}.yml`, `Dockerfile`, **build vérifié en CI** |
| Outils de qualité de code | PHP_CodeSniffer (PSR-12) + PHPStan, **lancés en CI** |
| Automatisation des tests | PHPUnit dans `tests/`, **lancé en CI** |
| Scripts d'IC sans erreur | `.github/workflows/ci.yml` **vert** |
| Serveur d'automatisation paramétré | **GitHub Actions** (déclenché à chaque push) |
| Rapports d'IC interprétés | Capture du run vert + paragraphe d'interprétation |

---

## Phrases pour le jury

- **DevOps en une phrase** : *« Rapprocher le développement et l'exploitation pour livrer souvent
  et de façon fiable — chez moi : conteneurs reproductibles, migrations idempotentes et un pipeline
  qui vérifie chaque push automatiquement. »*
- **CI vs CD** : *« L'intégration continue valide chaque modification (qualité, tests, build) à
  chaque push ; le déploiement continu pousse ensuite automatiquement en production. J'ai
  l'intégration continue ; le déploiement reste manuel et documenté. »*
- **Ton pipeline** : *« À chaque push, GitHub Actions installe PHP 8.3, vérifie PSR-12, lance
  PHPStan et PHPUnit, et construit l'image Docker. Deux jobs parallèles, vert en ~2 minutes. »*
- **Idempotence** *(souvent demandé)* : *« Mes migrations testent l'existence avant d'agir
  (`information_schema`), donc je peux les rejouer sans casser la base — indispensable quand un
  pipeline les applique à chaque déploiement. »*

---

## ⚠️ Honnêteté (ne te tire pas une balle dans le pied)

- **Ne décris dans le dossier que ce que tu as réellement branché.** Si tu t'arrêtes au Niveau 1,
  parle de PSR-12 + build, pas de PHPStan/PHPUnit. Un jury qui ouvre ton `ci.yml` et n'y trouve pas
  ce que tu décris, c'est le pire scénario.
- **Garde une capture d'un run vert daté** : c'est ta preuve que « les scripts s'exécutent sans
  erreur ».
- **Assume le CD manuel.** Le déploiement continu n'est pas exigé ; savoir l'expliquer suffit. Ne
  bricole pas un faux job de déploiement qui ne tourne jamais.
