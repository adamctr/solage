# Mapping des 11 compétences → preuves dans Solage

> **Le document le plus important pour l'entretien technique** et pour la **section 1 du dossier**
> (« Liste des compétences mises en œuvre »). Pour chaque compétence : les **critères officiels**
> du référentiel (REAC), la **preuve concrète** dans Solage (vrais fichiers), le **statut**, et
> la **page cible** du dossier.
>
> Légende statut : ✅ démontré · 🟠 partiel / à compléter · 🔴 trou à combler.

---

## Synthèse (à recopier en §1 du dossier)

| # | Compétence | CCP | Statut | Preuve principale | Page dossier |
|---|---|:--:|:--:|---|:--:|
| C1 | Installer/configurer l'environnement | 1 | ✅ | Docker, Git, Composer, conteneurs dev=prod | §3 |
| C2 | Développer des interfaces utilisateur | 1 | ✅ | `modules/views/`, JS/AJAX, anti‑XSS, CSP | §4.6 |
| C3 | Développer des composants métier | 1 | ✅ | `modules/controllers/`, IDOR, authz, CSRF | §4.7 |
| C4 | Contribuer à la gestion de projet | 1 | ✅ | Git, `ROADMAP`, journal décisions | §4.2 |
| C5 | Analyser les besoins & maquetter | 2 | ✅ | Expression besoins + maquettes Penpot + enchaînement | §2, §4.3 |
| C6 | Définir l'architecture logicielle | 2 | ✅ | MVC multicouche, DICP, Router/Middlewares | §4.1, §4.9 |
| C7 | Concevoir une BDD relationnelle | 2 | ✅ | `solage.pg.sql`, `Migrations.php`, `seed.sql` | §4.4 |
| C8 | Développer l'accès aux données SQL/NoSQL | 2 | ✅ | `modules/models/`, PDO préparé, anti‑SQLi | §4.8 |
| C9 | Préparer/exécuter les plans de tests | 3 | ✅ | PHPUnit 40 tests (62 assertions) + tests sécurité | §4.11‑4.12 |
| C10 | Préparer/documenter le déploiement | 3 | ✅ | `docker-compose.prod.yml` / `DEPLOYMENT.md` (ancré dans le dossier) | §3.4, §4.9 |
| C11 | Contribuer à la mise en prod DevOps | 3 | ✅ | Conteneurs, migrations, **CI GitHub Actions** (PSR-12, PHPStan, PHPUnit, build image) | §4.9 |

**Obligatoires dans le projet** (titre complet) : **C2, C3, C4, C5, C6, C7, C8, C9**.
**Surtout entretien/questionnaire** : **C1, C10, C11** (mais Solage les couvre → bonus).

---

## Détail par compétence

### C1 — Installer et configurer son environnement de travail · ✅
**Critères référentiel** : outils de dev installés · outils de **gestion de versions/collaboration**
installés · **conteneurs implémentent les services requis** · doc technique comprise (FR/**EN B1**).

| Critère | Preuve Solage |
|---|---|
| Outils de dev | PHP 7.4+, Composer (`composer.json`/`.lock`), IDE |
| Gestion de versions | **Git** (historique de commits conventionnels) |
| Conteneurs | `Dockerfile` (FrankenPHP), `docker-compose.yml` (Traefik + FrankenPHP + Postgres + `migrate`), `docker/Caddyfile` |
| Services requis | Postgres + healthcheck, `depends_on: service_healthy` |
| Doc EN | lecture php.net / Docker / OWASP en anglais |

**Question jury type** : image vs conteneur ? rôle du service `migrate` one‑shot ?

---

### C2 — Développer des interfaces utilisateur · ✅
**Critères** : conforme à la conception · **adaptée au support** · charte respectée · réglementation
respectée · **tests des composants** · **validation systématique des entrées** · gestion
**erreurs/exceptions** · failles **XSS/CSRF** et parades · doc EN.

| Critère | Preuve Solage |
|---|---|
| Interfaces | `modules/views/*` (Layout, MainPost, Post, CreatePost, User, Admin, Search, 404) |
| Asynchrone/AJAX | `public/scripts/index.js`, `dynamicMessages.js` (`fetch`) ; schéma `Workflow.md` |
| Anti‑XSS | `Utils::e()` (serveur) + `escapeHtml()` (client, rendu optimiste) |
| CSP | `default-src 'self'; img-src 'self' data:` (`public/index.php`) |
| Validation entrées | 🟠 à étendre côté JS (email, mdp, longueur) |
| Tests composants | 🔴 à produire |

**Question jury type** : pourquoi échapper à l'output ? c'est quoi le rendu optimiste ?

---

### C3 — Développer des composants métier · ✅
**Critères** : **POO** · composants **sécurisés** (auth, permissions, **validation**) · style
**défensif** · règles de nommage · code documenté · **tests unitaires et de sécurité**.

| Critère | Preuve Solage |
|---|---|
| Sécurité serveur | `PostController::delete` → check **IDOR** ownership‑ou‑admin → 403 + `Logger::warning` |
| Authn/Authz | `SessionManager` : `isLoggedIn()` vs `isAdmin()` (`getRoleName()==='Admin'`) |
| Validation pure | `UserValidator` retourne un array, **aucun echo** (testable) |
| Style défensif | gestion `try/catch` + `Logger::error`, cas d'exception (upload, contenu vide) |
| Tests | 🔴 à produire (unitaires + sécurité) |

**Question jury type** : authn vs authz ? un IDOR concret ? style défensif ?

---

### C4 — Contribuer à la gestion d'un projet informatique · ✅
**Critères** : tâches **planifiées** · **suivi** rapproché · procédures qualité · environnement de
dev adéquat · **outils collaboratifs** · **comptes rendus** structurés.

| Critère | Preuve Solage |
|---|---|
| Planification | `ROADMAP_DETAILLEE.md` (phases 0→6, priorités, risques) |
| Suivi | Git (commits par fonctionnalité), états ✅/🟠/⚪ |
| Comptes rendus | `Probleme-Solution.md` (journal de décisions) |
| Limite assumée | projet solo : pas d'outil type Jira/Trello ni Gantt — suivi par Git + `ROADMAP` + journal `Probleme-Solution.md` ; à assumer à l'oral |

**Question jury type** : comment planifies‑tu seul ? agile ou séquentiel ?

---

### C5 — Analyser les besoins et maquetter · ✅
**Critères** : besoins couvrant les exigences · **maquettes** conformes · **enchaînement formalisé
par un schéma** · dossier de conception structuré · RGAA · RGPD.

| Critère | Preuve Solage |
|---|---|
| Besoins/objectifs/limites | dossier §2 (rédigé) |
| User stories / acteurs | Visiteur / Utilisateur / Admin |
| **Maquettes** | ✅ 10 maquettes **Penpot** (login, register, feed, post, profil, edit, search, admin, 404) |
| **Enchaînement** | ✅ storyboard / plan de navigation (figure `enchainement.tex`) |
| RGPD/RGAA | mentions légales, `alt`/contraste/clavier (à formaliser) |

**Question jury type** : besoin ≠ fonctionnalité ? RGAA, deux mesures concrètes ?

---

### C6 — Définir l'architecture logicielle · ✅
**Critères** : architecture **multicouche répartie sécurisée** · **rôle de chaque couche** selon la
**stratégie de sécurité** · **éco‑conception** identifiée. Savoir : **DICP**, design/security patterns.

| Critère | Preuve Solage |
|---|---|
| Multicouche | Présentation (views) / Contrôle (controllers) / Validation (validators) / Métier‑Modèle (models) / Framework (`src/`) / Bootstrap (`includes/`) |
| Rôle sécurité par couche | tableau **DICP** (dossier §4.1) |
| Patterns | MVC, Middleware, Front Controller (`index.php`), Injection de dépendance (`SessionManager`) |
| Éco‑conception | `LIMIT 20` sur le feed, minification d'assets, N+1 corrigé |

**Question jury type** : c'est quoi DICP ? où vit la sécurité dans ton archi ?

---

### C7 — Concevoir et mettre en place une BDD relationnelle · ✅
**Critères** : **schéma conceptuel** respecte le relationnel · **schéma physique** conforme · règles
de **nommage** · **intégrité/sécurité/confidentialité** · **base de test restaurable**.

| Critère | Preuve Solage |
|---|---|
| MCD/MPD | à dessiner depuis `solage.pg.sql` (users, roles, posts, responses, likes, users_favorites_posts) |
| Nommage | **`user_id`** (mot `user` réservé en PostgreSQL) |
| Intégrité | FK `ON DELETE CASCADE/SET NULL`, `UNIQUE(email)`, `NOT NULL`, index |
| Script création | `solage.pg.sql` (init), **évolutions** via `src/Migrations.php` (idempotent, `information_schema`) |
| Base de test | `seed.sql` (jeu d'essai) |
| **À documenter** | sauvegarde/restauration `pg_dump`/`pg_restore` |

**Question jury type** : MCD vs MPD ? pourquoi `user_id` ? migration vs `ALTER` manuel ?

---

### C8 — Développer des composants d'accès aux données SQL et NoSQL · ✅
**Critères** : CRUD conforme · **cas d'exception** · **intégrité/confidentialité** · **conflits
d'accès** · **toutes les entrées contrôlées** · tests unitaires + sécurité. Savoir : **injection SQL
et parades**, transactions/isolation, relationnel vs NoSQL.

| Critère | Preuve Solage |
|---|---|
| CRUD préparé | `PostModel::createPost/getPostById/getPosts/delete`, `LikeModel`, `UserModel`, `SearchModel` |
| Anti‑injection | **requêtes préparées PDO** partout (`bindValue`/`bindParam`/`execute`) |
| Perf / N+1 | `UserModel::getUsersByIds` (1 requête `IN` au lieu de N) ; garde `IN ()` vide |
| Cas d'exception | `try/catch (PDOException)` + `Logger::error` (plus de `var_dump` qui fuyait en HTTP) |
| NoSQL | non utilisé → **savoir en parler** (clé/valeur, avantages/inconvénients) |

**Question jury type** : pourquoi une requête préparée bloque l'injection ? une transaction ?

---

### C9 — Préparer et exécuter les plans de tests · ✅
**Critères** : plan couvrant **toutes les fonctionnalités** · **environnement de test** · tests
conformes au plan · résultats cohérents avec l'attendu. Types : unitaires, intégration, **sécurité**,
non‑régression, charge, acceptation.

| Critère | Preuve Solage |
|---|---|
| Environnement de test | **PHPUnit 11** (dépendance dev) + vraie base Postgres, transaction annulée au teardown |
| Plan de tests | tableau fonctionnalité → cas → entrée/attendu/obtenu/écart |
| Unitaires | `UserValidator`, `Utils::e()`, `PostModel`/`LikeModel`, `Router` |
| **Sécurité** | SQLi (recherche), XSS (post), CSRF (POST sans token → 403), IDOR (Bob→Alice → 403) |
| Jeu d'essai | « Publier un message » (entrée/attendu/obtenu + écarts) |

**✅ C9 implémenté : 40 tests (62 assertions) verts — `tests/` (unitaires + intégration + sécurité), voir dossier §4.11.**

---

### C10 — Préparer et documenter le déploiement · ✅
**Critères** : **procédure de déploiement rédigée** · **scripts de déploiement documentés** ·
**environnements de tests définis** · veille déploiement.

| Critère | Preuve Solage |
|---|---|
| Stack prod | `docker-compose.prod.yml` (Traefik HTTPS Let's Encrypt, FrankenPHP image, Postgres non exposé) |
| Scripts de déploiement | les deux `docker-compose*.yml` + `bin/migrate.php` / service `migrate` (déploiement déclaratif, pas de `deploy.sh`) |
| Procédure rédigée | **`DEPLOYMENT.md`** (pré‑requis, étapes, **rollback**), environnements dev/staging/prod |

**Question jury type** : étapes de ton déploiement ? rollback ? environnements ?

---

### C11 — Contribuer à la mise en production DevOps · ✅
**Critères** : **outils de qualité de code** · **automatisation des tests** · **scripts d'intégration
continue sans erreur** · serveur d'automatisation paramétré · rapports CI interprétés. Savoir :
conteneurs, `docker compose`, **git server**, scripts CI (YAML), Linux.

| Critère | Preuve Solage |
|---|---|
| Conteneurs | `docker compose` (stacks dev + prod), images FrankenPHP |
| Idempotence | `src/Migrations.php` (rejouable sans casse) |
| **À produire** | **GitHub Actions** : PHPStan + PHPUnit + ESLint + `docker build` (+ deploy SSH bonus) |
| Qualité de code | PHPStan/Psalm, PHP_CodeSniffer PSR‑12 (à mettre en place) |

**Question jury type** : DevOps en une phrase ? CI vs CD ? ton pipeline ?

---

## Compétences transversales (évaluées à travers les pro)

| Transv. | Comment Solage la démontre |
|---|---|
| **T1 — Communiquer FR/EN** | Dossier + oral en français structuré ; commentaires de code & vocabulaire en anglais ; questionnaire B1 |
| **T2 — Résolution de problème** | `Probleme-Solution.md` : `STDOUT` CLI‑only, `headers already sent`, N+1, `IN ()` vide — symptôme→diagnostic→correctif |
| **T3 — Apprendre en continu (veille)** | Déclic **IDOR/URSSAF** → audit → fix ; sources OWASP/ANSSI/CVE |

---

## Comment t'en servir

1. **Dossier §1** : recopie la table de synthèse, en remplaçant la colonne « Page » par les pages
   réelles une fois le dossier paginé.
2. **Avant l'oral** : pour chaque ligne 🟠/🔴, prépare **une phrase** qui dit *où ça en est* et
   *quel est le plan* — le jury valorise la lucidité.
3. **Entretien technique** : relis la « Question jury type » de chaque compétence ; ce sont les
   angles d'attaque les plus probables.
