# Dossier de projet — Sommaire détaillé & guide de rédaction

> **Cible : 40 à 60 pages** hors page de garde, sommaire et annexes (schémas et illustrations
> **compris**). **Annexes : 40 pages max.**
> Projet : **Solage**. Plan = squelette « formation » enrichi du plan « entreprise » (cf.
> `00-README-DEROULE-EXAMEN.md` §4).
>
> Pour chaque chapitre, ce guide donne : 🎯 ce que le référentiel attend · 🧩 les compétences
> couvertes · ✍️ le contenu Solage concret (vrais fichiers) · 🛠️ ce qui reste à produire ·
> 🗣️ la réponse jury · ⚠️ les pièges.

---

## SOMMAIRE & BUDGET DE PAGES

> Budget cible ≈ **56 p.** (ajustable : plancher 40, plafond 60). Les schémas comptent dans le total.

| § | Chapitre | Pages | Compétences |
|---|---|---:|---|
| | *Page de garde* | — | — |
| | *Sommaire* | — | — |
| **1** | **Liste des compétences mises en œuvre** | **2** | toutes |
| **2** | **Expression des besoins** | **4** | C5 |
| 2.1 | Contexte et problème adressé | | |
| 2.2 | Objectifs du projet | | |
| 2.3 | Périmètre et **limites** (hors‑scope assumé) | | |
| 2.4 | Acteurs, user stories / cas d'usage | | |
| 2.5 | Contraintes (RGPD, RGAA, éco‑conception) | | |
| **3** | **Environnement technique** | **5** | C1 |
| 3.1 | Vue d'ensemble de la stack | | |
| 3.2 | Justification des choix techniques | | |
| 3.3 | Poste de travail & outils (IDE, Git, Composer) | | |
| 3.4 | Conteneurisation (Docker dev / prod) | | |
| **4** | **Réalisations** | **43** | C2→C11 |
| 4.1 | Architecture logicielle multicouche (+ DICP) | 4 | C6 |
| 4.2 | Gestion de projet | 2 | C4 |
| 4.3 | Maquettes & enchaînement des écrans | 3 | C5 |
| 4.4 | Conception de la base : MCD, MPD, script SQL | 4 | C7 |
| 4.5 | Diagrammes UML : cas d'utilisation & séquence | 3 | C6, C5 |
| 4.6 | Composants d'interface utilisateur (capture + code) | 4 | C2 |
| 4.7 | Composants métier | 4 | C3 |
| 4.8 | Composants d'accès aux données | 4 | C8 |
| 4.9 | Autres composants (Router, Middlewares, Logger, Utils) | 3 | C3, C6 |
| 4.10 | Sécurité de l'application | 5 | C2, C3, C8 |
| 4.11 | Plan de tests | 3 | C9 |
| 4.12 | Jeu d'essai de la fonctionnalité la plus représentative | 2 | C9 |
| 4.13 | Veille sécurité (vulnérabilités trouvées & corrigées) | 2 | T3 |
| **5** | **Bilan, difficultés, perspectives** | **2** | T2 |
| | **TOTAL** | **≈ 56** | |
| | *Annexes (≤ 40 p.)* | — | |

> **Conseil de pagination** : vise **50‑54 p.** rédigées. Tu gardes ainsi une marge sous le
> plafond de 60 après ajout des schémas, et tu restes confortablement au‑dessus du plancher de 40.

---

## Page de garde & sommaire (hors décompte)

**Page de garde** : intitulé du titre (Concepteur Développeur d'Applications, niveau 6,
RNCP37873), nom du projet (**Solage**), ton nom/prénom, nom du centre, session d'examen, date.

**Sommaire** : paginé, généré automatiquement, à jour. Le jury lit le dossier **avant** ta
présentation : un sommaire clair et une numérotation propre font la première impression.

---

## 1. Liste des compétences mises en œuvre  ·  2 p.  ·  *toutes*

🎯 **Référentiel** : premier point du plan, pour les deux types de projet. C'est la **table de
correspondance** qui dit au jury « voici où chacune des 11 compétences est démontrée ».

✍️ **Contenu Solage** : un **tableau** à 4 colonnes — *Compétence · Comment elle est mise en
œuvre dans Solage · Artefact/fichier · Page du dossier*. Reprends tel quel le tableau de
`03-MAPPING-COMPETENCES.md` (c'est exactement à ça qu'il sert).

🗣️ **Réponse jury** : *« Cette table est ma promesse de couverture : chaque ligne renvoie à une
page précise du dossier où la compétence est démontrée par un livrable concret. »*

⚠️ **Pièges** : ne mets pas que les compétences « faciles ». Si une compétence est faiblement
couverte (ex. C11 DevOps), **dis‑le et dis où** elle est partiellement traitée — un jury préfère
la lucidité au survol. Mets cette liste **au tout début** : c'est la grille de lecture du jury.

---

## 2. Expression des besoins  ·  4 p.  ·  *C5*

🎯 **Référentiel** : « expression des besoins du projet pour définir **les objectifs et les
limites** du projet ». Critère C5 : *« Les besoins recensés couvrent l'ensemble des exigences
utilisateur »*. Comme Solage est un projet de formation **sans commanditaire réel**, c'est **toi**
qui formules l'expression de besoins (le référentiel l'autorise explicitement).

🧩 **Compétences** : C5 (analyser les besoins).

### 2.1 Contexte et problème  (≈ 1 p.)
✍️ Présente Solage : un **réseau social de microblogging** (type X/Twitter). Le « problème »
pédagogique : se confronter à **toutes les briques d'une application web sécurisée multicouche**
(auth, CRUD, relations imbriquées, upload, recherche, modération) sur un périmètre maîtrisable.

### 2.2 Objectifs  (≈ 1 p.)
✍️ Liste d'objectifs **vérifiables** : permettre à un visiteur de **s'inscrire / se connecter** ;
à un utilisateur de **publier** un message (avec image), **répondre** (fil imbriqué), **liker**,
**éditer son profil**, **rechercher** ; à un **admin** de **modérer**. Objectif transverse :
**sécurité à chaque couche** (ANSSI/OWASP) + **RGPD/RGAA**.

### 2.3 Périmètre et limites  (≈ 1 p.)  ⭐
✍️ **Le hors‑scope assumé est un point fort de maturité.** Déclare ce qui n'est **pas** dans le
périmètre et **pourquoi** : pas de messagerie privée, pas de notifications temps réel, pas de
fédération, pas de paiement. Justifie : *concentrer l'effort sur la **qualité et la sécurité** du
cœur plutôt que sur la largeur fonctionnelle.*

🗣️ **Réponse jury** : *« J'ai préféré un périmètre étroit entièrement maîtrisé et sécurisé à un
périmètre large à moitié fait. Le référentiel valorise la sécurité constante : je l'ai privilégiée
sur le nombre de fonctionnalités. »*

### 2.4 Acteurs & user stories  (≈ 0,5 p.)
✍️ Trois acteurs : **Visiteur**, **Utilisateur connecté**, **Administrateur**. Quelques user
stories au format *« En tant que…, je veux…, afin de… »*. Elles serviront de base au **diagramme
de cas d'utilisation** (§4.5) et au **plan de tests** (§4.11) — boucle vertueuse à montrer.

### 2.5 Contraintes réglementaires  (≈ 0,5 p.)
✍️ **RGPD** (données perso : email, mot de passe haché, posts → mentions légales, finalité,
minimisation), **RGAA** (accessibilité : `alt`, contraste, navigation clavier, `aria-*`),
**éco‑conception** (minification d'assets déjà en place, poids des images, requêtes maîtrisées).

⚠️ **Pièges** : « objectifs » ≠ « fonctionnalités ». Un objectif est **mesurable**. Et n'oublie
**jamais** les **limites** : c'est un attendu explicite du référentiel, souvent négligé.

---

## 3. Environnement technique  ·  5 p.  ·  *C1*

🎯 **Référentiel** : « l'environnement technique ». Critères C1 : outils de dev installés, outils
de **gestion de versions et de collaboration**, **conteneurs** implémentant les services requis,
doc technique comprise (y compris **en anglais**, B1).

🧩 **Compétences** : C1 (installer/configurer l'environnement).

### 3.1 Vue d'ensemble de la stack  (≈ 1 p.)
✍️ Tableau de la stack réelle :

| Couche | Techno | Rôle |
|---|---|---|
| Langage | **PHP 7.4+** (`declare strict_types` visé) | Logique applicative |
| SGBD | **PostgreSQL 16** via **PDO** | Persistance relationnelle |
| Serveur applicatif | **FrankenPHP** (+ Caddy intégré) | Exécute PHP, sert le statique |
| Reverse proxy / edge | **Traefik v3** | Routage, TLS (Let's Encrypt en prod) |
| Conteneurs | **Docker / docker compose** | Repro dev = prod |
| Dépendances | **Composer** (`minify`, `phpdotenv`, `psr/log`) | Gestion de paquets |
| Front | **HTML/CSS pur + JavaScript vanilla** (fetch/AJAX) | Interfaces |
| Versioning | **Git** | Gestion de versions |

### 3.2 Justification des choix  (≈ 2 p.)  ⭐
✍️ **C'est ici que se voit le niveau 6.** Pour chaque choix, un mini « X plutôt que Y parce que Z » :
- **MVC maison plutôt qu'un framework (Symfony/Laravel)** : objectif pédagogique — **comprendre
  et posséder** le routeur, l'autoloader, le cycle requête/réponse, plutôt que déléguer à de la
  « magie » non maîtrisée. Coût assumé : on réécrit des briques que le framework offrirait.
- **PostgreSQL plutôt que MariaDB** : typage strict, `SERIAL`/séquences, robustesse, conformité
  SQL. *(Migration réelle MariaDB→PostgreSQL déjà effectuée — anecdote à valoriser.)*
- **FrankenPHP plutôt qu'Apache/Nginx‑FPM** : un seul binaire, Caddy intégré (TLS auto), worker
  mode performant, image Docker simple.
- **Traefik plutôt que Nginx en reverse proxy** : configuration **par labels Docker**, TLS
  Let's Encrypt automatique, découverte dynamique des services.
- **PDO plutôt qu'une extension native** : abstraction, **requêtes préparées** (anti‑injection),
  portable.

🗣️ **Réponse jury** : *« Chaque choix est un compromis explicite. J'ai choisi le MVC maison pour
la maîtrise pédagogique, en acceptant de réécrire des briques qu'un framework fournirait. »*

### 3.3 Poste de travail & outils  (≈ 1 p.)
✍️ IDE, **Git** (+ stratégie de branches/commits), **Composer** (`composer.json`/`composer.lock`),
gestion des **secrets** (`.env` gitignoré + `vlucas/phpdotenv`, template `.env.example`).
Mentionne savoir lire la **doc technique en anglais** (PHP.net, OWASP, Docker) — critère B1 de C1.

### 3.4 Conteneurisation  (≈ 1 p.)
✍️ Décris les **deux** stacks (renvoi détaillé au §4.9/DevOps) :
- **`docker-compose.yml`** (dev) : Traefik HTTP, FrankenPHP **bind‑mount** `./` (hot reload),
  Postgres exposé sur 5432, service **`migrate`** one‑shot, **healthcheck** Postgres +
  `depends_on: condition: service_healthy`.
- **`docker-compose.prod.yml`** (prod) : Traefik **HTTPS** (Let's Encrypt), FrankenPHP **image**
  (pas de mount), Postgres non exposé.

🗣️ **Réponse jury** : *« Les conteneurs me donnent un environnement de dev conforme à la prod —
exactement le critère C1 : "les conteneurs implémentent les services requis". »*

⚠️ **Pièges** : ne te contente pas de **lister** la stack — **justifie**. Un jury challenge
toujours « pourquoi pas un framework ? ». La réponse « maîtrise pédagogique + sécurité que je
contrôle » est solide **si** tu peux expliquer chaque brique que tu as écrite.

---

## 4. Réalisations  ·  43 p.  ·  *C2 → C11*

> Cœur du dossier. Le référentiel (plan formation) demande « les réalisations permettant la mise
> en œuvre des compétences » — on y verse **tout** le contenu de conception et de code.

### 4.1 Architecture logicielle multicouche (+ DICP)  ·  4 p.  ·  *C6*

🎯 **Référentiel** C6 : *« L'architecture est conforme aux bonnes pratiques d'une architecture
multicouche répartie sécurisée »*, *« le rôle de chaque couche est défini en tenant compte de la
**stratégie de sécurité** »*, *« les besoins d'éco‑conception sont identifiés »*. Savoir attendu :
indicateurs **DICP** (Disponibilité, Intégrité, Confidentialité, Preuve).

✍️ **Contenu Solage** :
- **Schéma d'architecture** en couches (à dessiner) :
  ```
  Navigateur (HTML/CSS/JS, fetch)
        │  HTTP(S)
  ┌─────▼─────────────────────────────────────────────┐
  │ Edge : Traefik (TLS, routage)                      │
  │ Serveur applicatif : FrankenPHP/Caddy              │
  ├────────────────────────────────────────────────────┤
  │ public/index.php  → bootstrap (autoload, session,  │
  │                      headers sécurité, CSRF token) │
  │ routes/           → URL → "Controller#method"      │
  │ ── Présentation : modules/views/                   │
  │ ── Contrôle     : modules/controllers/             │
  │ ── Validation   : modules/validators/              │
  │ ── Métier/Modèle: modules/models/  (SQL ici only)  │
  │ ── Framework    : src/ (Router, Middlewares,       │
  │                   Logger, Utils, Migrations,       │
  │                   SessionManager)                  │
  │ ── Bootstrap    : includes/ (autoload, database)   │
  ├────────────────────────────────────────────────────┤
  │ SGBD : PostgreSQL (PDO, requêtes préparées)        │
  └────────────────────────────────────────────────────┘
  ```
- **Règles de layering** (depuis `CLAUDE.md`, à citer) : **pas de SQL hors `modules/models/`** ;
  **pas d'`echo`/output hors `modules/views/`** (et entrypoint) ; **pas de `$_POST/$_GET/$_SESSION`
  hors contrôleurs et `SessionManager`** ; **pas de `Database::getConnection()` hors modèles,
  `Migrations` et bootstrap**.
- **Rôle sécurité de chaque couche (tableau DICP)** :

| Couche | Disponibilité | Intégrité | Confidentialité | Preuve |
|---|---|---|---|---|
| Edge (Traefik) | TLS, isolation réseau | HTTPS (anti‑MITM) | HTTPS, HSTS | logs d'accès |
| Bootstrap (`index.php`) | — | headers CSP/nosniff | cookies `HttpOnly/Secure/SameSite` | — |
| Middlewares (`src/`) | — | CSRF (anti‑forgery) | Auth/Admin (contrôle d'accès) | `Logger::warning` sur refus |
| Contrôleurs | gestion d'erreurs | validation des entrées, IDOR | autorisation par ressource | logs applicatifs |
| Modèles (PDO) | transactions | requêtes **préparées** (anti‑SQLi) | accès données contrôlé | — |
| Vues | — | **échappement `Utils::e()`** (anti‑XSS) | pas de fuite de données | — |
| SGBD (PostgreSQL) | contraintes FK, index | contraintes d'intégrité, types | comptes/droits | — |

🗣️ **Réponse jury** : *« Chaque couche a une responsabilité **et** un rôle de sécurité. Le SQL
vit uniquement dans les modèles, l'échappement uniquement dans les vues, l'autorisation dans les
contrôleurs et middlewares. La sécurité n'est pas un module, c'est une propriété transverse. »*

⚠️ **Piège** : le DICP est un **savoir explicitement listé** dans le référentiel C6. Si tu ne
sais pas réciter Disponibilité/Intégrité/Confidentialité/Preuve, le jury le sentira.

### 4.2 Gestion de projet  ·  2 p.  ·  *C4*

🎯 **Référentiel** C4 : tâches **planifiées**, **suivi** rapproché de la planification, **outils
collaboratifs** choisis selon la méthode, **comptes rendus** structurés.

✍️ **Contenu Solage** : méthode **itérative légère** (proche Kanban). Appuie‑toi sur :
- **Git** comme outil de suivi (historique de commits par fonctionnalité, messages conventionnels) ;
- **`ROADMAP_DETAILLEE.md`** = ta planification réelle (phases 0→6, états ✅/🟠/⚪, priorités, risques) ;
- **`Probleme-Solution.md`** = tes comptes rendus de décisions techniques.

🛠️ **À produire si manquant** : un **outil collaboratif** visible (GitHub Projects / Trello /
un tableau Kanban), un **planning visuel** (Gantt ou roadmap planifié vs réalisé), 3‑4
**comptes rendus** de sessions de travail.

🗣️ **Réponse jury** : *« Ma roadmap est ma planification, mon journal Problème/Solution sont mes
comptes rendus, et Git matérialise le suivi : chaque fonctionnalité est une série de commits
traçables. »*

⚠️ **Piège** : C4 est **obligatoire** pour le titre complet. Ne la traite pas par‑dessus la jambe
sous prétexte que tu es seul : montre **planification + suivi + comptes rendus**, même solo.

### 4.3 Maquettes & enchaînement des écrans  ·  3 p.  ·  *C5*

🎯 **Référentiel** C5 : *« Les maquettes sont réalisées conformément au cahier des charges »*,
*« l'enchaînement des maquettes est formalisé par un schéma »*. Savoir : **RGAA**, ergonomie UX.

✍️ **Contenu Solage** : maquettes des écrans : **login, register, accueil (feed), détail d'un
post, profil utilisateur, édition profil, recherche, admin, 404**.

🛠️ **À produire si manquant** : refaire des **maquettes propres** (Figma) — même *a posteriori*,
c'est attendu. + un **schéma d'enchaînement** (sitemap/storyboard) reliant les écrans (flèches
login→feed→post→profil…). Justifie la **charte graphique** (style minimaliste récent, cf. commits
CSS) et 2‑3 choix UX (simplicité, minimalité des affichages — savoir C5).

🗣️ **Réponse jury** : *« Les maquettes ont guidé l'intégration ; l'enchaînement formalise les
parcours. J'ai appliqué les principes UX du référentiel : simplicité et minimalité. »*

⚠️ **Piège** : sans **schéma d'enchaînement**, le critère C5 n'est pas rempli. Une capture
d'écran de l'app **n'est pas** une maquette : il faut le travail de conception (Figma).

### 4.4 Conception de la base : MCD, MPD, script SQL  ·  4 p.  ·  *C7*

🎯 **Référentiel** C7 : *« Le schéma conceptuel respecte les règles du relationnel »*, *« le
schéma physique est conforme aux besoins »*, *« les règles de nommage sont respectées »*,
*« l'intégrité, la sécurité et la confidentialité des données sont assurées »*, *« base de test
avec jeu d'essai complet, restaurable »*.

✍️ **Contenu Solage** (source : `solage.pg.sql`) :
- **MCD** (modèle entités‑associations) à dessiner. Entités : **users, roles, posts, responses,
  likes, users_favorites_posts**. Associations : un user *a un* role ; un user *publie* des posts ;
  un post *peut répondre à* un post (`reply_to` / `reply_to_parent`, auto‑relation) ; un user
  *like* des posts/réponses ; un user *met en favori* des posts (table de jointure).
- **MPD** (modèle physique) : reprends les tables réelles avec types (`SERIAL`, `VARCHAR`, `TEXT`,
  `TIMESTAMP`, `INT`), **clés primaires**, **clés étrangères** avec `ON DELETE CASCADE/SET NULL`,
  **index** (`users_role_idx`, `posts_user_idx`, `responses_*_idx`, `likes_*_idx`…).
- **Règle de nommage à défendre** : le mot **`user` est réservé en PostgreSQL** → les colonnes de
  clé étrangère s'appellent **`user_id`** (documenté dans `CLAUDE.md`). Bel exemple de « règle de
  nommage en vigueur » (critère C7).
- **Script de création** : `solage.pg.sql` (chargé une fois via
  `/docker-entrypoint-initdb.d/`). **Évolutions additives** via **`src/Migrations.php`**
  (idempotent, basé sur `information_schema`) → c'est le « script de modification de la BDD ».
- **Intégrité** : contraintes `NOT NULL`, `UNIQUE(email)`, FK référentielles.
- **Jeu d'essai / base de test** : **`seed.sql`** (10 users, posts, likes, réponses).
- **Sauvegarde/restauration** : `pg_dump` / `pg_restore` (à documenter — critère « restaurable »).

🛠️ **À produire** : les **diagrammes** MCD et MPD propres (Looping, Mocodo, draw.io, ou Mermaid).
Documenter la **procédure de sauvegarde/restauration**.

🗣️ **Réponse jury** : *« Le schéma part des besoins, respecte le relationnel, et la contrainte
PostgreSQL "user réservé" a dicté ma règle de nommage `user_id`. Les évolutions passent par des
migrations idempotentes, pas par des `ALTER` manuels non tracés. »*

⚠️ **Pièges** : MCD ≠ MPD (conceptuel vs physique — ne montre pas qu'un seul). Le critère
« restaurable » impose de **parler de la sauvegarde**. Ne réintroduis jamais une colonne `user`
nue (cf. `CLAUDE.md`).

### 4.5 Diagrammes UML : cas d'utilisation & séquence  ·  3 p.  ·  *C6, C5*

🎯 **Référentiel** : *« diagramme du comportement des fonctionnalités de type cas d'utilisation »*
+ *« diagramme du détail des cas les plus significatifs de type diagramme de séquence »*.

✍️ **Contenu Solage** :
- **Diagramme de cas d'utilisation** : acteurs **Visiteur / Utilisateur / Admin** → cas
  (s'inscrire, se connecter, publier, répondre, liker, éditer profil, rechercher, supprimer,
  modérer). Montre l'héritage Admin ⊃ Utilisateur ⊃ Visiteur et les `<<include>>` (ex. « publier »
  include « être authentifié »).
- **Diagramme(s) de séquence** sur **1‑2 cas significatifs**. Le meilleur candidat : **« Publier
  un message avec image »** (Navigateur → `fetch /api/post` FormData → `Router::match` →
  `CsrfMiddleware` → `AuthMiddleware` → `PostController::create` → validation upload →
  `PostModel::createPost` (INSERT préparé) → `Utils::sendResponse` JSON → rendu optimiste JS).
  `documents/Workflow.md` contient déjà le **flux login** en ASCII : convertis‑le en séquence UML
  propre comme second exemple.

🛠️ **À produire** : diagrammes propres (PlantUML / Mermaid / draw.io).

🗣️ **Réponse jury** : *« Le cas d'utilisation montre le "qui peut quoi", le diagramme de séquence
montre le "comment ça circule" couche par couche, middlewares de sécurité compris. »*

⚠️ **Piège** : choisis un cas **vraiment significatif** (qui traverse toutes les couches +
sécurité), pas un trivial. « Publier un post » ou « Se connecter » sont parfaits.

### 4.6 Composants d'interface utilisateur  ·  4 p.  ·  *C2*

🎯 **Référentiel** C2 : interface conforme à la conception, **adaptée au support** (responsive),
**charte respectée**, **réglementation respectée**, code **documenté**, **tests** des composants
graphiques, **validation systématique des entrées**, gestion des **erreurs/exceptions**, **XSS/CSRF**.

✍️ **Contenu Solage** :
- **Capture d'écran** d'une interface (ex. le feed ou la création de post) **+ le code de la vue
  correspondante** (`modules/views/MainPostView.php` / `PostView.php` / `CreatePostView.php`).
- **AJAX / asynchrone** : `public/scripts/index.js` (création de post, like, suppression via
  `fetch`), `public/scripts/dynamicMessages.js` (login/register). Renvoie au schéma `Workflow.md`.
- **Affichage optimiste** : après création, le post est injecté dans le DOM via `innerHTML` —
  d'où la nécessité d'un **`escapeHtml()` JS** miroir de `Utils::e()` (sécurité côté client).
- **Validation des entrées** côté JS (à compléter : règles email/mot de passe/longueur).
- **Responsive / charte** : CSS pur, design minimaliste (cf. commits `style(css)`).

🗣️ **Réponse jury** : *« La vue n'affiche que des données déjà préparées par le contrôleur (plus
de SQL en vue depuis l'audit MVC), et tout contenu utilisateur est échappé à l'affichage —
côté serveur par `Utils::e()`, côté client par `escapeHtml()` car le JS reconstruit du DOM. »*

⚠️ **Pièges** : C2 exige **tests des composants** et **validation systématique** — deux points
aujourd'hui faibles (cf. §4.11). Mentionne honnêtement l'état et le plan.

### 4.7 Composants métier  ·  4 p.  ·  *C3*

🎯 **Référentiel** C3 : composants métier **sécurisés**, **style défensif**, **POO**, **gestion de
la sécurité serveur** (authentification, permissions, **validation des entrées**), code documenté,
**tests unitaires et de sécurité**.

✍️ **Contenu Solage** — extraits de code à insérer (les plus significatifs) :
- **`PostController::create`** (`modules/controllers/PostController.php`) : lecture `$_POST`,
  **traitement d'upload** (taille, extensions autorisées, renommage `uniqid()`, `move_uploaded_file`),
  validation du contenu, appel modèle, réponse JSON unifiée, gestion d'exception + `Logger::error`.
- **`PostController::delete`** : **contrôle d'autorisation IDOR** —
  `if ($post->getUserId() !== $userId && !$session->isAdmin())` → `403` + `Logger::warning`.
  **Exemple de sécurité métier en or pour le jury.**
- **`SessionManager`** (`src/SessionManager.php`) : **authentification vs autorisation** —
  `isLoggedIn()` (authn) vs `isAdmin()` (authz, via `getRoleName() === 'Admin'`), injection de
  dépendance du `UserModel` au constructeur.
- **`UserValidator`** (`modules/validators/UserValidator.php`) : **validation pure** — retourne
  `['ok'=>bool,'type'=>…,'message'=>…]`, **aucun effet de bord HTTP** (donc testable).

🗣️ **Réponse jury** : *« Mes composants métier valident les entrées et vérifient l'**autorisation
sur la ressource**, pas seulement l'authentification. L'IDOR sur la suppression est bloqué par un
check ownership‑ou‑admin, journalisé pour audit. Le validateur est pur : il décide, le contrôleur
expose. »*

⚠️ **Pièges** : montre le **style défensif** (cas d'exception gérés, entrées validées). Le
référentiel insiste sur « valider **systématiquement** les entrées » — c'est un axe à renforcer
(validation serveur encore partielle dans `UserValidator`).

### 4.8 Composants d'accès aux données  ·  4 p.  ·  *C8*

🎯 **Référentiel** C8 : traitements **CRUD** conformes, **cas d'exception** pris en compte,
**intégrité/confidentialité** maintenues, **conflits d'accès** gérés, **toutes les entrées
contrôlées**, **tests unitaires et de sécurité par composant**. Savoirs : **injection SQL et
parades**, **requêtes paramétrées**, transactions/isolation.

✍️ **Contenu Solage** — extraits de `modules/models/` :
- **`PostModel::createPost`** : `INSERT` en **requête préparée** avec `bindValue` nommés —
  l'exemple canonique d'**anti‑injection SQL**.
- **`PostModel::getPostById` / `getPosts`** : `SELECT` + `LEFT JOIN likes` + `GROUP BY` (comptage
  des likes), `LIMIT 20` (éco‑conception : on ne charge pas tout le feed).
- **`UserModel::getUsersByIds(array $ids)`** : correction du **N+1 query** — un seul
  `SELECT … WHERE id IN (?,?,…)` au lieu d'une requête par post. **Piège géré** : `IN ()` vide est
  une **erreur SQL en PostgreSQL** → court‑circuit `if empty($ids) return []`. Construction du `IN`
  par `array_fill` + `implode` (placeholders), valeurs en **bound params** → pas d'injection.
- **Gestion d'erreur** : `try/catch (PDOException)` + `Logger::error('…failed', [...])` au lieu de
  `var_dump` (qui **fuyait dans la réponse HTTP** — anti‑pattern corrigé, cf. `Probleme-Solution.md`).

🗣️ **Réponse jury** : *« Toutes les requêtes sont préparées : les entrées utilisateur partent en
paramètres liés, jamais concaténées — l'injection SQL est structurellement impossible. J'ai aussi
corrigé un N+1 (21 requêtes → 2 sur le feed) en préchargeant les auteurs en une requête `IN`. »*

⚠️ **Pièges** : sache expliquer **pourquoi** une requête préparée neutralise l'injection
(séparation **code SQL / données**). C'est **LA** question quasi‑certaine du jury sur C8. Mentionne
NoSQL même si non utilisé (savoir « avantages/inconvénients relationnel vs non‑relationnel »).

### 4.9 Autres composants (Router, Middlewares, Logger, Utils)  ·  3 p.  ·  *C3, C6*

🎯 **Référentiel** : *« extraits de code d'autres composants (contrôleurs, utilitaires…) »*. C'est
là que tu montres le **framework maison** — preuve de maîtrise architecturale.

✍️ **Contenu Solage** :
- **`src/Router.php`** : routes en regex (`/{id}` → capture), dispatch `Controller#method`,
  exécution du **middleware de route** puis **CsrfMiddleware sur tout POST** (sécurité par défaut),
  404 → `page404View`.
- **`src/AuthMiddleware` / `src/AdminMiddleware`** : **authn vs authz** séparés explicitement
  (deux classes). Refus admin → `403` + `Logger::warning('admin.access.denied', …)`.
- **`src/CsrfMiddleware` + `src/CsrfHelper`** : **Synchronizer Token** — token par session
  (`random_bytes(32)`), comparé en **temps constant** (`hash_equals`), armé par le **Router sur
  tout POST** (« sécurité par défaut bat sécurité opt‑in »). Cf. `csrf-securite-guide.md`.
- **`src/Logger.php`** : **PSR‑3** maison, sortie **JSON‑line** sur `stdout`/`stderr` (convention
  Docker). Piège vaincu : `STDOUT`/`STDERR` n'existent qu'en **CLI** → usage des wrappers
  `php://stdout` (sinon **500** en HTTP). Belle anecdote de résolution de problème (T2).
- **`src/Utils.php`** : `e()` (échappement XSS), `sendResponse()` (réponse JSON unifiée
  `{success, message, data?}`), `csrfField()`.
- **`src/Migrations.php`** : migrations **idempotentes** via `information_schema`.

🗣️ **Réponse jury** : *« J'ai écrit le framework moi‑même : routeur, middlewares, logger PSR‑3,
helpers. Je peux défendre chaque ligne — c'est le but du MVC maison. Le CSRF est armé par le
routeur sur tout POST : je ne peux pas oublier un endpoint. »*

⚠️ **Piège** : ces composants sont ta **force** (tu les possèdes). Mais chaque ligne montrée peut
être questionnée — ne montre que ce que tu sais **réexpliquer au tableau**.

### 4.10 Sécurité de l'application  ·  5 p.  ·  *C2, C3, C8*  ⭐⭐

🎯 **Référentiel** (transverse, omniprésent) : OWASP, ANSSI, failles web (XSS, CSRF, injection),
gestion des identités, cryptographie de base, validation des entrées.

✍️ **Contenu Solage** — structure recommandée : **un sous‑chapitre par faille (OWASP)**, format
*menace → où elle était → correctif → preuve* :

| Faille (OWASP) | Correctif Solage | Fichier(s) |
|---|---|---|
| **XSS stocké** (A03) | Échappement à l'**output** : `Utils::e()` (serveur) + `escapeHtml()` (JS) | vues + `index.js` |
| **CSRF** (A01/forgery) | **Synchronizer Token** armé par le Router sur tout POST, `hash_equals` | `CsrfMiddleware`, `CsrfHelper` |
| **Injection SQL** (A03) | **Requêtes préparées** PDO partout | `modules/models/*` |
| **IDOR / Broken Access Control** (A01) | Check **ownership‑ou‑admin** + `403` + log | `PostController::delete`, `UserController` |
| **Authn ≠ Authz** | `AuthMiddleware` (loggé) vs `AdminMiddleware` (rôle) | `src/*Middleware.php` |
| **Mots de passe** | `password_hash()` / `password_verify()` (bcrypt) | `UserValidator`, `UserModel` |
| **En‑têtes** | CSP `default-src 'self'`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, **HSTS** (Traefik, prod) | `public/index.php`, prod |
| **Cookies de session** | `HttpOnly` + `SameSite=Lax` + `Secure` (prod) | `public/index.php` |
| **Upload** | Extensions autorisées, renommage `uniqid()` | `PostController::create` |

➡️ **Mets en avant le « déclic IDOR / URSSAF »** (cf. `trucs à retenirs…md`) : une faille IDOR
réelle médiatisée t'a fait auditer Solage et trouver la **même classe de faille** sur
`/edituser/{id}`, `/api/users/delete`, `/api/posts/delete`. **Cycle vertueux veille→audit→fix→doc**
qui démontre **C3 + T3 + T2** d'un coup. Détaille‑le.

🛠️ **À renforcer** (honnêteté = points) : **validation serveur systématique** (email, robustesse
mdp), **`session_regenerate_id(true)`** au login (anti‑fixation), **rate‑limit** brute‑force,
validation d'upload par **MIME réel** (`finfo` + `getimagesize`) en plus de l'extension.

🗣️ **Réponse jury** : *« La sécurité est traitée couche par couche et faille par faille, en
référence à l'OWASP Top 10. Là où c'est incomplet — validation serveur, anti‑fixation — je le sais
et je l'ai planifié. »*

⚠️ **Pièges** : ne prétends pas être « 100 % sécurisé ». Le jury **respecte** un candidat qui
connaît ses angles morts et les nomme. Sur l'upload, sois honnête : la validation par **extension
seule** est insuffisante — annonce le durcissement MIME.

### 4.11 Plan de tests  ·  3 p.  ·  *C9*  🔴 PRIORITÉ

🎯 **Référentiel** C9 (**obligatoire** pour le projet) : *« Le plan de tests couvre l'ensemble des
fonctionnalités »*, *« un environnement de tests est créé »*, *« les tests exécutés sont conformes
au plan »*, *« les résultats obtenus sont cohérents avec les attendus »*. Types : unitaires,
intégration, **sécurité**, non‑régression, charge, acceptation.

✍️ **Contenu Solage à produire** (cf. `00-README §6` — **c'est le trou n°1**) :
- **Environnement de test** : conteneur Postgres dédié (ou SQLite en mémoire) + **PHPUnit**.
- **Tableau du plan de tests** : *Fonctionnalité · Type · Donnée d'entrée · Résultat attendu ·
  Résultat obtenu · Écart*. Couvre login, register, publier, liker, répondre, supprimer, recherche,
  modération.
- **Tests unitaires** : `UserValidator`, `Utils::e()`, `PostModel`/`LikeModel`, `Router`.
- **Tests de sécurité** (4 emblématiques) :
  - **Injection SQL** sur la recherche → la requête préparée neutralise le payload ;
  - **XSS** : poster `<script>alert(1)</script>` → rendu `&lt;script&gt;…` (non exécuté) ;
  - **CSRF** : POST sans token → **403** ;
  - **IDOR** : Bob tente d'éditer le profil d'Alice → **403**.
- (Bonus) **test de charge** léger (Apache Bench / k6) sur le feed ; **analyse statique**
  (PHPStan/Psalm).

🗣️ **Réponse jury** : *« Mon plan de tests part des fonctionnalités et descend jusqu'aux tests de
sécurité qui rejouent les failles que j'ai corrigées : chaque correctif a son test de
non‑régression. »*

⚠️ **Piège** : **sans cette section, C9 (obligatoire) tombe.** Même un socle modeste mais **réel
et exécutable** vaut infiniment mieux qu'un plan théorique non implémenté.

### 4.12 Jeu d'essai de la fonctionnalité la plus représentative  ·  2 p.  ·  *C9*

🎯 **Référentiel** : *« jeu d'essai de la fonctionnalité la plus représentative (données en
entrée, données attendues, données obtenues) et analyse des écarts éventuels »*. **Attendu
explicite, à l'oral comme à l'écrit.**

✍️ **Contenu Solage** : choisis **« Publier un message »** (elle traverse toutes les couches :
interface → contrôleur → validation → upload → modèle → SQL → réponse → rendu). Présente un
**tableau de jeu d'essai** :

| Cas | Entrée | Attendu | Obtenu | Écart |
|---|---|---|---|---|
| Nominal | contenu « Hello » | post créé, id retourné, 200 | … | aucun |
| Contenu vide | `""` | refus « contenu vide » | … | … |
| Image trop lourde | 12 Mo | refus « image trop lourde » | … | … |
| Extension interdite | `.php` | refus « type non autorisé » | … | … |
| Payload XSS | `<script>` | stocké brut, **rendu échappé** | … | … |
| Non authentifié | sans session | **403** (AuthMiddleware) | … | … |

🗣️ **Réponse jury** : *« J'ai choisi "Publier un message" parce que c'est la fonctionnalité qui
traverse toute l'architecture et concentre toutes les protections : validation, upload, anti‑XSS,
autorisation. Le jeu d'essai couvre le nominal **et** les cas limites/malveillants. »*

⚠️ **Piège** : « la plus représentative » = celle qui **traverse le plus de couches**, pas la plus
simple. Et **l'analyse des écarts** est exigée : commente chaque écart (ou son absence).

### 4.13 Veille sécurité  ·  2 p.  ·  *T3*

🎯 **Référentiel** : *« description de la veille, effectuée durant le projet, sur les vulnérabilités
de sécurité, description des vulnérabilités trouvées et des failles potentiellement corrigées »*.

✍️ **Contenu Solage** :
- **Sources de veille** : OWASP (Top 10), ANSSI, PHP.net (security advisories), CVE, blogs/RSS.
- **Vulnérabilité concrète trouvée** : l'**IDOR** (déclencheur : article sur la faille IDOR de
  l'**URSSAF**, cf. `trucs à retenirs…md`) → audit Solage → 3 routes vulnérables → correctif.
- **Autres** : XSS stocké, fuite d'info via `var_dump` dans les `catch`, cookies non durcis.
- **Format** : *source → ce que j'ai appris → vulnérabilité détectée dans Solage → correctif → date*.

🗣️ **Réponse jury** : *« Ma veille n'est pas décorative : un article sur l'IDOR de l'URSSAF m'a
fait auditer mes propres routes et trouver la même faille. Veille → audit → correctif →
documentation, c'est un cycle que je tiens. »*

⚠️ **Piège** : une veille **crédible** cite des **sources datées** et au moins **une vulnérabilité
réellement trouvée et corrigée dans Solage**. Le lien veille→correctif est ce qui prouve T3.

---

## 5. Bilan, difficultés, perspectives  ·  2 p.  ·  *T2*

✍️ **Contenu** :
- **Satisfactions** : framework maison maîtrisé, sécurité multicouche, migration MariaDB→PostgreSQL,
  dockerisation dev=prod.
- **Difficultés rencontrées & résolues** (démontre T2) : le piège `STDOUT` CLI‑only (Logger),
  le `headers already sent` au login (session démarrée trop tard), le N+1, le `IN ()` vide. Pour
  chacune : symptôme → diagnostic → correctif. (Tout est dans `Probleme-Solution.md`.)
- **Perspectives** : CI/CD GitHub Actions, durcissement validation/upload, anti‑fixation de
  session, factorisation CSS/JS, accessibilité RGAA poussée.

🗣️ **Réponse jury** : *« Mes difficultés les plus instructives sont des bugs silencieux —
le code "marchait" mais la sémantique était fausse. C'est l'audit, pas le compilateur, qui les a
attrapés. »*

---

## Annexes (≤ 40 p.)

Le référentiel demande, pour la **fonctionnalité la plus représentative** :
- les **maquettes** des interfaces ;
- les **captures d'écran** d'interfaces **+ le code** correspondant ;
- le **code des composants métier** les plus significatifs ;
- le **code des composants d'accès aux données** les plus significatifs ;
- le **code des autres composants** (contrôleurs, utilitaires…) ;
- les **jeux de tests complets** (unitaires, intégration, sécurité) avec entrée/attendu/obtenu.

⚠️ **Pièges annexes** : les annexes **complètent**, elles ne **remplacent pas** le corps. Ne
déporte pas en annexe ce qui doit être commenté dans les Réalisations. Le **code long** va en
annexe ; les **extraits significatifs commentés** vont dans le corps (§4.6‑4.9).

---

## Rappels de forme (le jury lit le dossier AVANT de te voir)

- **Pagination** continue, **sommaire** à jour, **titres numérotés**.
- **Schémas lisibles** en N&B (le dossier est imprimé) — pas de capture illisible.
- **Extraits de code** : courts, **commentés**, avec le **chemin du fichier** en légende.
- **Orthographe/grammaire irréprochables** (critère explicite de T1 « communication écrite »).
- **Français** clair et structuré ; quelques éléments en **anglais** (commentaires de code, un
  court résumé) valorisent le B1.
- **Cohérence** : la liste des compétences (§1) doit pointer vers des pages **réelles** du dossier.
