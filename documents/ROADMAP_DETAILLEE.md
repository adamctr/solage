# Roadmap détaillée — Titre Pro CDA (RNCP37873) — projet Solage

> Le projet **Solage** (réseau social type X/Twitter) sert de support à l'examen du Titre
> Professionnel **Concepteur Développeur d'Applications** (niveau 6, arrêté du 26/04/2023).
>
> Il faut couvrir **les 11 compétences professionnelles** réparties sur **3 CCP**, produire un
> **dossier de projet (40-60 p. + 40 p. d'annexes)**, un **diaporama (≈ 40 min)**, un **dossier
> professionnel (DP)**, et passer **entretien technique + questionnaire (anglais B1)**.

**Dernière mise à jour : 2026-06-20** (réécriture complète après audit du dépôt — code, dossier,
infra, git — et vérification croisée des statuts).

> **Légende** : ✅ fait/validé · 🟠 partiel · 🔴 à développer · ⚪ non commencé.
> Le **statut faisant autorité** des compétences est le tableau de `01-competences.tex`. Cette
> roadmap s'y aligne ; les nuances « partiel » relevées dans le code/dossier sont signalées comme
> **dette assumée** ou **artefact dossier à produire**, sans changer le statut officiel.

---

## 0. L'enjeu n°1 à comprendre avant tout — le bloc CCP3

Le titre se valide **par blocs de CCP**. Les 11 compétences se répartissent ainsi :

| CCP | Intitulé | Compétences | État |
|---|---|---|---|
| **CCP1** | Développer une application sécurisée | C1, C2, C3, C4 | ✅ **validé** (4/4 OK) |
| **CCP2** | Concevoir et développer une application en couches | C5, C6, C7, C8 | ✅ **validé** (4/4 OK) |
| **CCP3** | Préparer le déploiement d'une application sécurisée | C9, C10, C11 | 🟠 C9 ✅, C10 ✅, reste **C11** (CI) partiel |

> **Conséquence directe** : CCP1 et CCP2 sont acquis. **Tout le risque restant est concentré sur le
> CCP3.** Sans CCP3 validé, on n'obtient **pas le titre complet** — seulement deux blocs sur trois.
> **C9 et C10 sont faits ; il ne reste que C11 (pipeline CI) en partiel sur le CCP3.**

La quasi-totalité du travail technique restant (sections 4 et 5) sert à **boucler le CCP3**.

---

## 1. État des lieux (2026-06-16)

### Stack technique
- **PHP 8.3** (image `dunglas/frankenphp:1.4-php8.3` ; PHP 8.2 en local) · **PostgreSQL 16** via PDO
  (prepared statements, `ATTR_EMULATE_PREPARES=false`).
- **MVC maison** : `Router` (routes → `Controller#method`), `Autoloader` par chemins (espace de noms
  global, choix assumé), `Migrations` idempotentes, middlewares (`Auth`, `Admin`, `Csrf`).
- Couche `modules/validators/` parallèle à `controllers/ models/ views/`.
- Composer : `matthiasmullie/minify`, `vlucas/phpdotenv`, `psr/log` ; dev : `squizlabs/php_codesniffer`.
- Logger PSR-3 maison (`src/Logger.php`) → JSON-line sur stdout/stderr.
- **Dockerisé** : FrankenPHP + Caddy + Traefik + Postgres. `docker-compose.yml` (dev, HTTP) /
  `docker-compose.prod.yml` (prod, HTTPS Let's Encrypt + HSTS). Image multi-étapes (vendor `--no-dev`
  + runtime). Service `migrate` one-shot ordonnancé **avant** l'app.

### Fonctionnalités
Auth (login/register/logout) · posts CRUD + upload image · réponses imbriquées
(`reply_to`/`reply_to_parent`) · likes · favoris · profil + édition · recherche (users/posts) · page
admin · minification d'assets (prod) · messages dynamiques (toasts AJAX).

### Historique Git (deux phases nettes)
- **Phase étudiante** : 2024-09-25 → 2024-11-12 (routeur, users, likes, réponses, posts imbriqués).
- **~18 mois de pause.**
- **Reprise (sécurisation & industrialisation)** : 2026-05-06 → 2026-06-15 (Docker + migration
  PostgreSQL, sécurité XSS/IDOR/CSRF/headers, refonte MVC, validateurs, PSR-12, CSS, dossier CDA).
- HEAD = `ad92fac` (2026-06-15), branche `main`.
- ⚠️ **Travail non commité au 2026-06-16** : tout le `documents/dossier-latex/` (chapitres + PDF), les
  3 guides récents (`DEPLOYMENT.md`, `guide-C11-CI-devops.md`, `plan-de-tests-guide.md`) et le dossier
  `maquettes/` (non suivi). → voir section 9 (hygiène Git).

---

## 2. Statut des 11 compétences (source : `01-competences.tex`)

| # | Compétence | CCP | Statut | Preuve principale | Ce qui reste |
|---|---|---|---|---|---|
| C1 | Installer/configurer l'environnement | 1 | ✅ | Docker dev=prod (FrankenPHP/Caddy/Traefik/PG), Git, Composer | — |
| C2 | Développer des interfaces | 1 | ✅ | `modules/views/`, JS vanilla/AJAX, anti-XSS, CSP | 1-2 **captures d'écran** réelles en regard du code (§6) |
| C3 | Développer des composants métier | 1 | ✅ | Contrôleurs, authz, contrôle IDOR, CSRF, validateur pur | (dette sécu §5 : à annoncer, ne change pas le statut) |
| C4 | Gestion de projet | 1 | ✅ | Git, feuille de route, journal de décisions, CR de session | Aisance **orale** (assumer le solo / l'absence d'outil type Jira) |
| C5 | Besoins & maquettes | 2 | ✅ | Expression des besoins, 10 maquettes Penpot | **Storyboard/enchaînement** + maquettes **desktop** manquantes (§7) |
| C6 | Architecture logicielle | 2 | ✅ | MVC multicouche, Router, middlewares, DICP, UML TikZ | — |
| C7 | Base de données relationnelle | 2 | ✅ | `solage.pg.sql`, migrations idempotentes, `seed.sql`, MCD/MLD/MPD | FK+index `reply_to` manquants (§7) ; pg_dump déjà dans `DEPLOYMENT.md` §7 |
| C8 | Accès aux données SQL/NoSQL | 2 | ✅ | `modules/models/`, PDO préparé, fix N+1 | Savoir **parler NoSQL** (non utilisé) ; tests (C9) |
| **C9** | **Préparer/exécuter les plans de tests** | **3** | ✅ | 40 tests PHPUnit (unit. + intégration + sécurité) verts, jeu d'essai exécuté | — |
| **C10** | **Préparer/documenter le déploiement** | **3** | ✅ | `docker-compose.prod.yml`, Traefik HTTPS, `DEPLOYMENT.md` ancré (§4.13) | — |
| **C11** | **Mise en production (DevOps)** | **3** | 🟠 **Partiel** | Conteneurs, migrations idempotentes, PSR-12 outillé | **Pipeline CI** (GitHub Actions) + rapport interprété (§4.2) |
| T1 | Communiquer FR/EN (B1) | — | 🟠 | Dossier structuré, vocabulaire EN, fiche FR/EN | **Entraînement questionnaire anglais** (§8) |
| T2 | Résolution de problème | — | ✅ | `05-bilan.tex` (STDOUT CLI, headers already sent, N+1) | — |
| T3 | Apprendre en continu (veille) | — | ✅ | Veille → fix IDOR (URSSAF), sources OWASP/ANSSI/CVE | Dater les entrées de veille |

---

## 3. ⭐ Ordre d'exécution recommandé — les 3 guides

> C'est le plan de bataille pour boucler le CCP3. Trois guides pas-à-pas existent
> (`plan-de-tests-guide.md` = C9, `guide-C11-CI-devops.md` = C11, `DEPLOYMENT.md` = C10). Ils se
> **chevauchent sur PHPUnit** — l'ordre ci-dessous évite le doublon.

| # | Quoi | Guide | Durée | Pourquoi à ce moment |
|---|---|---|---|---|
| **1** | **CI Niveau 1** (`phpcs` + `docker build`) | `guide-C11-CI-devops.md` | ~15 min | Indépendant ; **fait basculer le cœur de C11** ; crée le pipeline vert où tout se branche |
| **2 ✅** | **Plan de tests complet** (PHPUnit + tous les tests) | `plan-de-tests-guide.md` | ~3-4 h | **C9 — fait** ; plomberie PHPUnit de référence en place |
| **3** | **CI Niveau 2** = ajouter `vendor/bin/phpunit` au pipeline | `guide-C11-CI-devops.md` | ~2 min | Les tests existent (étape 2) → coche « tests automatisés » de C11 |
| **4** | **CI Niveau 3** (PHPStan) + **capture + interprétation** du run vert | `guide-C11-CI-devops.md` | ~30 min | 2ᵉ outil qualité + critère C11 « rapports CI interprétés » |
| **5 ✅** | **Déploiement** : `DEPLOYMENT.md` ancré au dossier (§4.13) + statut flippé — **fait** | `DEPLOYMENT.md` | ~20 min | Livrable déjà écrit ; purement éditorial |

### ⚠️ Le piège à éviter (chevauchement C9 ↔ C11)
Les deux guides créent `phpunit.xml` + `tests/bootstrap.php`. **Ne les crée qu'une fois.**
- Le **guide C9 est la référence** : son `bootstrap.php` inclut `includes/database.php` (indispensable
  aux tests d'intégration), contrairement au bootstrap réduit du guide C11.
- À l'**étape 3 (CI Niveau 2)**, **saute** la partie « installer PHPUnit / créer phpunit.xml » du
  guide C11 → ajoute seulement l'étape `vendor/bin/phpunit`.
- **Règle dure unique : C9 (étape 2) avant CI-Niveau-2 (étape 3).** Et « jamais un pipeline rouge » :
  un outil n'entre dans la CI qu'une fois vert en local.

---

## 4. Travail technique restant — CCP3 (priorité maximale)

### 4.1 — C9 · Tests (PHPUnit) · ✅ fait
> Guide : `documents/plan-de-tests-guide.md`. **Fait** : `tests/` + `phpunit.xml` en place,
> 40 tests verts (unitaires, intégration Postgres, sécurité).

- [x] Installer `phpunit/phpunit ^11` (require-dev) + `phpunit.xml` + `tests/bootstrap.php` (celui du
      guide C9, qui inclut `database.php`) ; `.phpunit.cache/` dans `.gitignore` ; smoke test vert.
- [x] **Unitaires purs (sans BDD)** : `UtilsTest` (anti-XSS `Utils::e`), `CsrfHelperTest`
      (token 64 hex, `verifyToken` bon/mauvais/vide/null), `isAjax`/`sendResponse`.
- [x] **Unitaire au mock** : `SessionManagerTest` (`UserModel` mocké → DI, zéro BDD).
- [x] **Intégration (vraie Postgres, transaction + rollback)** : `UserModel` + `UserValidator`
      (login/register, mot de passe hashé), **injection SQL** sur `SearchModel` (charge inerte).
- [x] **Fonctionnel (curl, app lancée)** : CSRF POST sans token → **403** + log ; IDOR Bob→Alice → **403** + log.
- [x] **Cahier de tests** T01-T14 (fonctionnalité → cas → entrée/attendu/**obtenu**/écart) + **jeu
      d'essai** « Publier un message » → reprendre dans le dossier (`04e-tests.tex`, annexes).
- [x] Capturer la suite verte (`--testdox`) + les 2 démos 403.

### 4.2 — C11 · Intégration continue (GitHub Actions) · 🟠
> Guide : `documents/guide-C11-CI-devops.md`. Briques DevOps faites ; **aucune CI** (`.github/workflows/` vide).

- [ ] **Niveau 1** : `.github/workflows/ci.yml` — job `qualite` (setup-php 8.3 + `composer install` +
      `vendor/bin/phpcs`) + job `image` (`docker build`). → vert dans l'onglet Actions.
- [ ] **Niveau 2** (après §5.1) : ajouter l'étape `vendor/bin/phpunit` au job `qualite`.
- [ ] **Niveau 3** : PHPStan (`phpstan.neon` level 1, baseline si besoin) branché dans la CI.
- [ ] **Obligatoire** : capturer un run vert + **rédiger 3-4 phrases d'interprétation** (critère
      « rapports CI interprétés ») dans `04e-tests.tex`.
- [ ] (Bonus, oral) savoir décrire le **CD** cible (job SSH sur `main` verte) sans l'implémenter.

### 4.3 — C10 · Déploiement · ✅ (ancré au dossier)
> `DEPLOYMENT.md` **existe et couvre les 8 axes** (pré-requis, `.env`, procédure, smoke test, mise à
> jour, rollback code+données, sauvegarde/restauration `pg_dump`, tableau 3 environnements).

- [x] Remplacer le bloc `\begin{todo}{Procédure de déploiement (DEPLOYMENT.md)}` (`04e-tests.tex:100`)
      par une vraie sous-section **citant** `DEPLOYMENT.md`.
- [x] **Flipper le statut** `01-competences.tex:30` : `todoOrange` Partiel → `juryGreen` OK ; aligner
      les MD `examen-cda/` (🟠 → ✅).
- [ ] (Optionnel) déploiement **live** sur un VPS (serveur + DNS A + ports 80/443) + captures
      `docker compose ps` / `curl` HTTPS. **N'affecte pas la note** « préparer et documenter ».

---

## 5. Dette de sécurité assumée — à corriger OU à défendre (§ jury)

> CCP1 est validé et la **sécurité de base est solide et vérifiée** (voir §10). Mais l'audit du code a
> confirmé des **manques réels**. La posture senior : **les nommer soi-même** au jury et présenter le
> correctif. Les corriger maintenant est rapide et renforce le dossier (chapitre « renforcements »).

- [ ] **`APP_ENV` codé en dur** à `'development'` (`public/index.php:12`) → le flag cookie **`Secure`
      n'est JAMAIS posé** et tout le branchement prod est mort. **Le point le plus facilement
      attaquable à l'oral.** Correctif : lire `APP_ENV` depuis l'environnement (déjà géré côté Docker).
- [ ] **`session_regenerate_id(true)`** absent après login/logout (`SessionManager`) → **fixation de
      session**. Correctif trivial.
- [ ] **Anti-brute-force** sur `/login` absent (aucun compteur/temporisation). Au minimum un délai
      progressif ou un compteur d'échecs.
- [ ] **Validation serveur** dans `UserValidator` incomplète : `register()`/`login()` ne vérifient que
      le non-vide + l'unicité de l'email. Ajouter `filter_var(FILTER_VALIDATE_EMAIL)` + robustesse mot
      de passe (longueur/complexité).
- [ ] **Validation MIME des uploads** absente (`PostController` ne vérifie que l'extension) → un `.php`
      renommé `.png` passe. Ajouter `getimagesize()` / `finfo`. (Risque atténué par le renommage `uniqid`.)
- [ ] **Anti-clickjacking** : `X-Frame-Options` / CSP `frame-ancestors` absents (`public/index.php`).
      *(Note : HSTS, lui, est bien présent — posé par Traefik en prod, `docker-compose.prod.yml:72-74`.)*

---

## 6. Finalisation du dossier LaTeX (8 blocs `\begin{todo}` restants)

> Chapitres **déjà rédigés intégralement** : `01-competences`, `02-besoins`, `03-environnement`,
> `04a-architecture`, `05-bilan`. Restent 8 encadrés orange « À DÉVELOPPER », concentrés sur le CCP3.

- [x] **1.** Implémentation/exécution PHPUnit + capture du rapport — `04e-tests.tex:33` *(dépend §4.1)*.
- [x] **2.** Colonne « Obtenu » du jeu d'essai + **analyse des écarts** — `04e-tests.tex:77` *(§4.1)*.
- [x] **3.** Sous-section citant `DEPLOYMENT.md` — `04e-tests.tex:100` *(§4.3)*.
- [ ] **4.** Pipeline CI réel + capture — `04e-tests.tex:120` *(§4.2)*.
- [ ] **5.** Procédure `pg_dump`/`pg_restore` — `04b-conception.tex:184` *(déjà dans `DEPLOYMENT.md` §7
      → y renvoyer)*.
- [ ] **6.** 1-2 **captures d'écran** réelles des interfaces en regard du code — `04c-developpement.tex:42` *(C2)*.
- [x] **7.** Jeux de tests complets en annexe (code + sorties) — `annexes.tex` *(§4.1)*.
- [ ] **8.** Garder le bloc « renforcements sécurité » comme **axes d'amélioration** — `04d-securite.tex:114` *(cf §5)*.

> Les blocs 1-2-7 dépendent tous de la **création effective des tests** (§5.1) → c'est le verrou.

---

## 7. Compléments conception & conformité

- [ ] **C5 — Storyboard / enchaînement des écrans** : le critère l'exige explicitement ; `maquettes/`
      ne contient que des écrans isolés. À produire (schéma de navigation reliant les maquettes).
- [ ] **C5 — Maquettes desktop incomplètes** : seules `connexion`, `inscription`, `admin` existent en
      desktop ; `accueil`, `detail-post`, `profil`, `recherche` n'existent **qu'en mobile**. Compléter
      ou assumer le choix « mobile-first » explicitement.
- [ ] **Wording maquettes** : trancher **Penpot vs Figma** (le dossier dit Penpot, un guide dit Figma)
      — aligner sur l'outil réellement utilisé avant l'oral (le jury demandera).
- [ ] **C7 — BDD** : `posts.reply_to` et `posts.reply_to_parent` sont des `INT NULL` **sans FK ni
      index** (auto-référence `posts`→`posts` non contrainte) alors que `responses.reply_to`, lui, a
      FK + index. Ajouter FK `ON DELETE SET NULL` + index, ou documenter le choix.
- [ ] **C7 — Migrations / question jury à préparer** : `migrate()` ajoute des colonnes (`reply_to`,
      `reply_to_parent`, `image`) **déjà présentes** dans `solage.pg.sql` et supprime `username` (qui
      n'existe pas) → sur base fraîche, c'est un quasi **no-op** (héritage du portage MariaDB→PG).
      Idempotence réelle, mais système non exercé en install neuve. Réponse à préparer.
- [ ] **RGPD** : emails + mots de passe = données personnelles. Ajouter mentions légales / politique de
      confidentialité, base légale, durée de conservation, droit à l'effacement (lié à `ON DELETE`).
- [ ] **RGAA** : documenter **2 mesures concrètes** (labels de formulaire, contraste, `alt`, navigation
      clavier) — exigé par C5.
- [ ] **Éco-conception** : relier explicitement aux preuves (minification ✅, fix N+1 réutilisé comme
      argument, poids des assets).

---

## 8. Livrables d'examen (non techniques) — deadlines propres

- [ ] **Diaporama de soutenance** (~30-35 slides, ≤ 40 min) : **seul le plan slide-par-slide existe**
      (`02-ORAL-SOUTENANCE.md` §A) → produire les slides + **répéter chronométré ×3** + préparer la
      **démo sécurité** (403 CSRF/IDOR live ou capture).
- [ ] **Dossier Professionnel (DP)** : gabarit `Template - vierge - TITRE6.docx` **vierge** → à remplir
      (parcours, expérience ; support de l'entretien final).
- [ ] **Questionnaire professionnel anglais B1** (épreuve écrite **avant** l'oral, 30 min, sans
      internet) : s'entraîner sur de la doc technique EN + rédiger 2 réponses ouvertes ; mémoriser la
      fiche de vocabulaire FR/EN.
- [x] Prépa entretien technique : banque de questions par compétence (`02-ORAL` §B, `trucs-a-dire-aux-jurys-oral.md`).

---

## 9. Hygiène Git (à faire rapidement)

Du travail réel est sur le disque mais **absent de l'historique** (donc non daté, non sauvegardé) :

- [x] Commiter `documents/dossier-latex/maquettes/` (non suivi).
- [x] Commiter `DEPLOYMENT.md`, `documents/guide-C11-CI-devops.md`, `documents/plan-de-tests-guide.md` (non suivis).
- [x] Commiter les modifications du `documents/dossier-latex/` (chapitres + PDF) et des notes orales.

---

## 10. Ce qui est déjà fait (rappel — à valoriser au dossier/oral)

> Acquis vérifiés par lecture du code et de l'infra. À ne pas refaire ; à **savoir présenter**.

**Sécurité (CCP1)** — double échappement XSS (`Utils::e` serveur + `escapeHtml` client) · **CSRF
Synchronizer Token appliqué structurellement à TOUT POST** par le `Router` (pas route par route) ·
**contrôle IDOR « propriétaire-ou-admin » + 403 + log** sur `/edituser/{id}`, `/api/posts/delete`,
`/api/users/delete` · `AuthMiddleware` / `AdminMiddleware` câblés · `bcrypt` · **prepared statements
partout** · en-têtes CSP / `X-Content-Type-Options` / `Referrer-Policy` · cookies `HttpOnly`+`SameSite`
· **HSTS via Traefik (prod)** · `declare(strict_types=1)` généralisé.

**Architecture & qualité (CCP1/CCP2)** — MVC multicouche strict (SQL en modèles, échappement en vues,
autorisation en contrôleurs/middlewares) · fix **N+1** (`getUsersByIds`) · validateur **pur** ·
réponse JSON unifiée · **PSR-12 outillé** (`phpcs` vert 46/46).

**BDD & infra (CCP2/CCP3 partiel)** — schéma PG normalisé (FK, `ON DELETE`, index sur les FK) ·
`seed.sql` (dev only) · **migrations idempotentes** (`information_schema`) · Docker dev+prod · image
**multi-étapes** · service `migrate` one-shot **bloquant** (`service_completed_successfully`) ·
healthcheck Postgres · secrets `.env`/phpdotenv + garde-fous `${VAR:?}` · prod durcie (HTTPS auto,
Postgres non exposé) · **`DEPLOYMENT.md`** complet.

**Dossier & oral** — 5 chapitres rédigés sans todo · UML + MCD/MLD/MPD en TikZ · 10 maquettes Penpot ·
veille IDOR documentée · plan de diaporama · banque de questions jury · fiche anglais B1.

---

## 11. Priorité globale & estimation

| Ordre | Bloc | Réf. | État | Effort | Risque si non fait |
|---|---|---|---|---|---|
| 🔴 1 | CI Niveau 1 (phpcs + build) | §3 étape 1, §4.2 | ⚪ | ~15 min | — (quick win, sécurise le cœur C11) |
| ✅ 2 | **C9 — Tests PHPUnit + plan exécuté** | §3 étape 2, §4.1 | ✅ | ~3-4 h | fait |
| 🔴 3 | CI Niveaux 2-3 + interprétation | §3 étapes 3-4, §4.2 | ⚪ | ~35 min | C11 reste partiel |
| ✅ 4 | C10 — `DEPLOYMENT.md` ancré + statut flippé | §3 étape 5, §4.3 | ✅ | ~20 min | fait |
| 🟠 5 | Finaliser les 8 blocs dossier | §6 | 🟠 | 1-2 j | Dossier incomplet |
| 🟠 6 | Diaporama + DP + anglais B1 | §8 | ⚪ | 2-3 j | Pas de soutenance / entretien |
| 🟡 7 | Dette sécurité (correctifs) | §5 | ⚪ | ~½ j | Points perdus à l'oral (sinon à défendre) |
| 🟡 8 | Compléments C5/C7, RGPD/RGAA | §7 | 🟠 | 1 j | Challenges jury sur C5/C7 |
| 🟢 9 | Hygiène Git | §9 | ⚪ | 15 min | Travail non sauvegardé |

**Chemin critique = ordre 1 → 4** (boucler le CCP3), puis dossier + livrables oraux en parallèle.

---

## 12. Points jury à préparer (consolidés)

- **C8** (quasi-certain) : pourquoi une requête préparée empêche l'injection SQL ? → séparation
  code/données ; démo `SearchModel`.
- **C2** : XSS — pourquoi échapper à la **sortie** et pas à l'entrée ? → ne pas perdre l'info d'origine.
- **C3/C6** : authentification vs autorisation ? CSRF (Synchronizer Token, `hash_equals`, armé par le
  Router) ? un IDOR concret ?
- **C6** : c'est quoi le **DICP** ? où vit la sécurité couche par couche ?
- **C7** : MCD vs MPD ? pourquoi `user_id` ? **pourquoi migrer des colonnes déjà dans le schéma** (no-op
  sur base fraîche → héritage MariaDB→PG) ? sauvegarde/restauration ?
- **C1/C11** : image vs conteneur ? rôle du `migrate` one-shot ? **DevOps en une phrase ? CI vs CD ?
  ton pipeline ? idempotence ?**
- **C9** : test unitaire vs intégration ? non-régression ? comment tester une faille ? (savoir en
  parler même si l'implémentation est récente).
- **C10** : étapes du déploiement ? rollback ? environnements dev/staging/prod ? *(limite assumée :
  images mutables sans registry, staging non matérialisé).*
- **C5** : besoin ≠ fonctionnalité ? quel outil de maquette ? RGAA (2 mesures) ? RGPD sur Solage ?
- **Dette à annoncer soi-même** (§5) : `APP_ENV` codé en dur, pas de `session_regenerate_id`, pas
  d'anti-brute-force, validation serveur partielle, MIME upload, clickjacking → « voici le manque,
  voici le correctif ».
- **Questions ouvertes de fin** : si tu refaisais le projet ? point faible de l'app ? ce qui t'a le
  plus appris ? (cycle veille → audit → fix IDOR).
