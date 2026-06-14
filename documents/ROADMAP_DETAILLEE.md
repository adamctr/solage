# Roadmap détaillée — Titre Pro CDA (RNCP37873)

> Le projet **solage** (réseau social type X/Twitter) sert de support à l'examen du Titre Professionnel **Concepteur Développeur d'Applications** (niveau 6, arrêté du 26/04/2023).
>
> Il faut couvrir **les 11 compétences professionnelles** réparties sur **3 CCP**, produire un **dossier de projet (40-60 p. + 40 p. d'annexes)**, un **diaporama (40 min)** et passer **entretien technique + questionnaire (anglais B1)**.

Dernière mise à jour : 2026-05-12

---

## État des lieux

### Stack technique (actuelle)
- PHP 7.4+ / **PostgreSQL** via PDO (anciennement MariaDB, migré)
- MVC custom (`Router`, `Autoloader`, `Migrations`, `AuthMiddleware`, `AdminMiddleware`) avec une couche `modules/validators/` parallèle à `controllers/models/views`
- Composer : `matthiasmullie/minify`, `vlucas/phpdotenv`, `psr/log`
- Logger PSR-3 maison (`src/Logger.php`) → JSON-line sur stdout/stderr
- Sessions PHP, vanilla JS, CSS pur
- **Dockerisé** : FrankenPHP + Caddy + Traefik + Postgres (`docker-compose.yml` dev / `docker-compose.prod.yml` prod TLS Let's Encrypt)

### Fonctionnalités existantes
- Auth : login / register / logout
- Posts : CRUD + upload image
- Réponses imbriquées (`reply_to` / `reply_to_parent`)
- Likes (posts & réponses)
- Profil utilisateur + édition
- Recherche (users, posts)
- Page admin avec recherche
- Minification d'assets (en prod)
- Messages dynamiques (toast)

---

## PHASE 0 — Hygiène immédiate ✅ **TERMINÉE**

- [x] Credentials BDD sortis du code → `.env` + `vlucas/phpdotenv` (`includes/database.php`)
- [x] `.gitignore` complété (`.env`, `.idea/`, `.DS_Store`, `public/log.txt`, uploads)
- [x] Credentials Alwaysdata rotés (mot de passe compromis purgé)
- [x] Dump SQL avec hashes réels remplacé par schéma PostgreSQL propre (`solage.pg.sql`)
- [x] `public/log.txt` supprimé du repo (logs redirigés vers stdout/stderr via `Logger` PSR-3)
- [x] Bug `isAdmin()` corrigé (`SessionController.php:69` → comparaison sur `getRoleName() === 'Admin'`)

---

## PHASE 1 — Sécurité & robustesse du code (CCP1) 🟠 **EN COURS**

> Couvre : *Développer des composants métier sécurisés*, *Développer des interfaces sécurisées*, recommandations ANSSI / OWASP.

### 1.1 Failles à corriger (XSS / IDOR / CSRF / Auth)

- [x] **XSS stocké** : helper `Utils::e()` (`htmlspecialchars` + `ENT_QUOTES | ENT_HTML5` + UTF-8) appliqué sur toutes les vues affichant du contenu user-controlled (`PostView`, `MainPostView`, `UserView`, `AdminView`, `CreatePostView`). Le markup du message dynamique (login/register) est désormais construit côté JS via `escapeHtml()` après la suppression de `DynamicMessageView`.
- [x] **XSS côté JS** : `escapeHtml()` miroir dans `index.js` pour les interpolations `${post.*}` injectées via `innerHTML` (affichage optimiste après création de post)
- [x] **IDOR édition profil** (`UserController::update`) : check `current_user_id === target_user_id || isAdmin()` + 403 + log warning
- [x] **IDOR suppression post** (`PostController::delete`) : check ownership ou admin, 403 + log warning
- [x] **IDOR suppression user** (`UserController::delete`) : check ownership ou admin, 403 + log warning
- [x] **AuthMiddleware** appliqué sur `/api/posts/delete` et `/api/users/delete` (auparavant sans aucun middleware)
- [x] **CSRF** : implémenter un token CSRF en session, vérifié sur tous les POST (`/api/post`, `/api/like`, `/api/posts/delete`, `/api/users/delete`, `/edituser/{id}`, `/login`, `/register`, `/logout`)
- [ ] **Upload d'images** : valider via `getimagesize()` + MIME réel (`finfo`), pas seulement l'extension ; bloquer SVG/PHP ; renommer (déjà fait via `uniqid`) ; limiter la taille en plus de la directive PHP
- [x] **Headers de sécurité** : `Content-Security-Policy`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Strict-Transport-Security` (prod), cookies `HttpOnly` + `Secure` (prod) + `SameSite=Lax`
- [ ] **Régénération d'ID de session** : appeler `session_regenerate_id(true)` après login et logout (anti-fixation)
- [ ] **Brute-force login** : rate-limit ou compteur d'échecs sur `/login` (table `login_attempts` ou cache)

### 1.2 Validation à la volée

- [ ] **Côté JS** : étendre `dynamicMessages.js` avec écouteurs `input`/`blur` sur login, register, edit profil, post → règles : email valide, password ≥ 8 + 1 maj + 1 chiffre, contenu post ≤ N caractères
- [ ] **Côté PHP** : doubler systématiquement la validation dans `UserValidator` (`modules/validators/`) — actuellement `register()` ne vérifie ni format email ni robustesse du mot de passe
- [ ] **Messages d'erreur dynamiques** liés à chaque champ (pas seulement un toast global)

### 1.3 Middleware Admin ✅ **TERMINÉE**

- [x] `AdminMiddleware` créé (`src/AdminMiddleware.php`), distinct d'`AuthMiddleware` (authentification ≠ autorisation)
- [x] Appliqué sur `/admin`, `/admin/search/results/users`, `/admin/search/results/posts`
- [x] Refus → 403 + `Logger::warning('admin.access.denied', …)`
- [x] Suppression cross-user (post / user) déjà couverte par check d'ownership dans les contrôleurs (cf 1.1)

### 1.4 Qualité de code

- [ ] **PHPDoc** systématique (controllers, models, views) : `@param`, `@return`, `@throws`
- [ ] **Convention de nommage** homogène (corriger mix `DataBase`/`Database`)
- [ ] **Indentation** cohérente, supprimer code mort (echo "Requête URI" debug dans `Router.php` si encore présent, blocs commentés résiduels)
- [ ] **`declare(strict_types=1)`** en tête de chaque fichier PHP
- [ ] **PHP_CodeSniffer PSR-12** : faire passer la base à 0 violation

### 1.5 Audit MVC & nettoyage architectural ✅ **TERMINÉE**

> Passe d'audit MVC : la couche présentation ne parle plus à la base, les helpers sont rangés selon leur rôle réel, la réponse JSON est unique pour toute l'app. Voir `documents/Probleme-Solution.md` pour le détail de chaque décision et `documents/Workflow.md` pour le schéma du cycle AJAX.

- [x] **N+1 query** sur les vues corrigé via `UserModel::getUsersByIds` ; `PostView` / `MainPostView` reçoivent une map `[user_id => UserModel]` préchargée par le contrôleur (21 requêtes → 2 sur la page d'accueil avec 20 posts)
- [x] **Helpers déplacés** hors de `modules/controllers/` vers `src/` : `Utils` (escape + sendResponse) et `MinificationController` (build d'assets)
- [x] **`UserValidator` extrait** vers `modules/validators/` (renommé depuis `ValidatorController`) — la validation devient pure (retourne un array, ne fait plus d'echo)
- [x] **`DynamicMessageController` + `DynamicMessageView` supprimés** ; unification de la réponse JSON sur `Utils::sendResponse` (`{success: bool, message: string, data?: object}`) — un seul format pour login/register/API
- [x] **Code mort supprimé** : `PostResponsesView` (aucun appelant), `$type = $_GET['type']` (lu sans usage), méthodes `showAdminInDesktopSidebar`/`InMobileSidebar` (inlinées)
- [x] **Bugs préexistants corrigés** : `PostToolHeartView` recevait l'ID de l'auteur du post au lieu du user courant ; `session_start()` déplacé dans le bootstrap pour éviter `headers already sent` au login

---

## PHASE 2 — Conception & documentation projet (CCP2) ⚪ **À FAIRE**

> Couvre : *Analyser les besoins et maquetter*, *Définir l'architecture logicielle*, *Concevoir et mettre en place une BDD relationnelle*.

### 2.1 Expression des besoins
- [ ] Cahier des charges / expression de besoins (objectifs et limites — projet de formation)
- [ ] Liste des **user stories** ou **cas d'usage** (visiteur s'inscrit, utilisateur poste, like, répond, supprime, admin modère…)
- [ ] Identifier les contraintes : RGPD, RGAA, éco-conception

### 2.2 Maquettes
- [ ] Maquettes Figma de toutes les pages : login, register, home (feed), post détail, profil user, édition profil, admin, recherche, 404
- [ ] **Schéma d'enchaînement** des écrans (storyboard / sitemap)
- [ ] Justifier la charte graphique et les choix UX

### 2.3 Diagrammes UML (obligatoires CCP2)
- [ ] **Diagramme de cas d'utilisation** (acteurs : Visiteur, Utilisateur, Admin)
- [ ] **Diagrammes de séquence** sur 1-2 cas significatifs (ex : « Poster un post avec image », « Répondre à un post »)
- [ ] **Diagramme de classes** (PostModel, UserModel, LikeModel, etc.)
- [ ] **MCD** (modèle conceptuel) de la base
- [ ] **MPD** (modèle physique) — partir de `solage.pg.sql`
- [ ] **Schéma d'architecture multicouche** (Présentation / Contrôleur / Métier / Accès données / SGBD) + rôle sécurité de chaque couche (DICP)

### 2.4 Spécifications techniques
- [ ] Justifier les choix : PHP vanilla MVC vs framework, PostgreSQL vs MariaDB, FrankenPHP vs Apache/Nginx-FPM, Traefik vs Nginx
- [ ] Documenter le routeur, l'autoloader, le système de migrations
- [ ] Documenter la stratégie de sécurité couche par couche

### 2.5 Améliorations BDD
- [x] Système de migrations idempotent (`src/Migrations.php` + `bin/migrate.php` lancé via service Docker)
- [x] `roles.id` fiabilisé par jointure (`getRoleName()`) au lieu d'une comparaison numérique magique
- [x] FK utilisant `user_id` (mot `user` réservé en PostgreSQL → documenté dans `CLAUDE.md`)
- [ ] Ajouter des index manquants si pertinent (audit `EXPLAIN` sur les requêtes feed/recherche)
- [ ] Documenter la sauvegarde / restauration (`pg_dump` / `pg_restore`)
- [ ] Créer un **jeu d'essai** reproductible (seed SQL : 10 users, 30 posts, 50 likes, 20 réponses)

---

## PHASE 3 — Tests & qualité (CCP3 — compétence n°9) ⚪ **À FAIRE**

> C'est un **gros trou actuel** du projet (aucun test).

### 3.1 Tests unitaires
- [ ] Installer **PHPUnit** via Composer (dev dependency)
- [ ] Couvrir : `UserValidator`, `UserModel`, `PostModel`, `LikeModel`, `Router`, `Utils::e()`
- [ ] BDD de test : conteneur Postgres dédié ou SQLite en mémoire (avec adaptateur)

### 3.2 Tests d'intégration
- [ ] Tester les routes (login → poste un post → like → delete) en simulant HTTP
- [ ] Outil : PHPUnit + client HTTP, ou Playwright / Cypress pour le bout-en-bout

### 3.3 Tests de sécurité
- [ ] 1 test d'**injection SQL** sur la recherche (vérifier que les prepared statements neutralisent)
- [ ] 1 test **XSS** sur création de post (payload `<script>` → rendu en `&lt;script&gt;`)
- [ ] 1 test **CSRF** sur action critique (POST sans token → refus)
- [ ] 1 test **IDOR** (Bob essaie d'éditer le profil d'Alice → 403)
- [ ] Audit OWASP ZAP en local + checklist OWASP Top 10 manuelle

### 3.4 Plan de tests rédigé
- [ ] Document listant toutes les fonctionnalités + cas testés (entrée / attendu / obtenu) + écarts
- [ ] Tests d'**acceptation** (parcours utilisateur complet)
- [ ] **Test de charge léger** (Apache Bench ou k6) sur le feed

### 3.5 Qualité de code (statique)
- [ ] **PHPStan** ou **Psalm** niveau 5+ → rapport propre
- [ ] **PHP_CodeSniffer** PSR-12
- [ ] **ESLint** sur les fichiers JS

---

## PHASE 4 — Déploiement & DevOps (CCP3 — compétences n°10 & 11) 🟠 **EN COURS**

> *Nouveauté du référentiel 2023*.

### 4.1 Conteneurisation ✅ **TERMINÉE**

- [x] `Dockerfile` (FrankenPHP + Composer install + extensions PHP)
- [x] `docker-compose.yml` (dev) : Traefik HTTP + FrankenPHP bind-mount + Postgres exposé sur 5432 + service `migrate` one-shot
- [x] `docker-compose.prod.yml` (prod) : Traefik HTTPS via Let's Encrypt + FrankenPHP image (pas de mount) + Postgres non exposé
- [x] Volumes pour les uploads (`./` bind-mount en dev) et la BDD (`postgres-data`)
- [x] Variables d'env via `.env` (template `.env.example`)
- [x] Healthcheck Postgres + `depends_on: condition: service_healthy`
- [x] Caddyfile dédié dans `docker/Caddyfile`

### 4.2 Procédure de déploiement
- [ ] Document `DEPLOYMENT.md` : pré-requis, étapes, rollback
- [ ] Scripts de déploiement (`Makefile`) : `make deploy`, `make rollback`
- [ ] Documenter 3 environnements : dev (Docker local), staging (VPS test ?), prod
- [ ] Documenter la commande `docker compose restart traefik` (cf `CLAUDE.md` — le provider Docker manque parfois les recreations)

### 4.3 CI/CD
- [ ] **GitHub Actions** : workflow déclenché à chaque push
  - [ ] lance PHPStan
  - [ ] lance PHPUnit
  - [ ] lance ESLint
  - [ ] construit l'image Docker (`docker build`)
  - [ ] (bonus) déploie automatiquement sur la prod via SSH
- [ ] Documenter le rapport CI et savoir l'expliquer en entretien

### 4.4 Sauvegardes & résilience
- [ ] Cron de sauvegarde `pg_dump` (vers un volume / S3)
- [ ] Documenter la procédure de restauration

---

## PHASE 5 — Compétences transversales ⚪ **À FAIRE**

### 5.1 Gestion de projet (compétence n°4)
- [ ] Outil collaboratif : **GitHub Projects**, Trello ou Jira
- [ ] Découpage en sprints (Agile / Scrum simplifié)
- [ ] **Comptes rendus** de 3-4 sessions de travail documentés
- [ ] **Planning** (Gantt ou roadmap visuelle) avec planifié vs réalisé

### 5.2 Anglais B1
- [ ] Préparer 2 questions ouvertes type : description du projet en anglais, choix techniques en anglais
- [ ] S'entraîner sur de la doc technique anglaise (PHP.net, OWASP)
- [ ] Annexe README ou section archi en anglais (plus)

### 5.3 Veille technologique
- [ ] Tenir un journal de veille (Feedly, RSS) sur PHP, sécurité, OWASP, PostgreSQL
- [ ] Documenter : sources suivies + vulnérabilités détectées sur le projet (XSS, IDOR déjà documentés dans `Probleme-Solution.md` → exploiter en veille)

### 5.4 Démarche de résolution de problèmes
- [x] Documentation initiée : `documents/Probleme-Solution.md` (XSS, IDOR, Logger PSR-3, Migrations, Authn vs Authz, découplage validator, audit MVC + finitions, suppression `DynamicMessageController`)
- [x] `documents/Workflow.md` : schéma ASCII du cycle AJAX login/register (browser → validator → response → session)
- [ ] Compléter avec 1-2 bugs supplémentaires diagnostiqués + résolus en détail

### 5.5 Accessibilité (RGAA) & RGPD & éco-conception
- [ ] Audit RGAA basique : contraste, `alt`, `aria-label`, navigation clavier
- [ ] Mentions légales + politique de cookies + page de confidentialité (RGPD)
- [ ] Section éco-conception : minification (✅ déjà en place), lazy-load images, poids des assets

---

## PHASE 6 — Livrables d'examen ⚪ **À FAIRE**

### 6.1 Dossier de projet (papier, imprimé)
- [ ] **40 à 60 pages** (hors page de garde, sommaire et annexes), schémas inclus
- [ ] **40 pages d'annexes max**
- [ ] Plan exigé :
  1. Liste des compétences mises en œuvre
  2. Expression des besoins (objectifs, limites)
  3. Environnement technique
  4. Réalisations couvrant les compétences
- [ ] Enrichir avec les éléments du plan « projet en entreprise » (cahier des charges, archi, MCD/MPD, maquettes, UML, sécurité, plan de tests, jeu d'essai, veille)

### 6.2 Diaporama de soutenance (≈ 40 min, 30-35 slides)
- [ ] 1. Présentation perso + projet
- [ ] 2. Expression du besoin
- [ ] 3. Environnement technique + architecture
- [ ] 4. Maquettes + enchaînement
- [ ] 5. MCD/MPD + script création BDD
- [ ] 6. Diagrammes cas d'usage + séquence
- [ ] 7. Captures d'écran + extraits de code (interfaces, métier, accès données)
- [ ] 8. Sécurité (XSS, CSRF, SQLi, IDOR… ce qui a été corrigé)
- [ ] 9. Plan de tests + jeu d'essai
- [ ] 10. Veille techno + vulnérabilités trouvées
- [ ] 11. Synthèse / difficultés / perspectives

### 6.3 Dossier professionnel (DP)
- [ ] Remplir le `Template - vierge - TITRE6.docx` (présentation, parcours, expérience)

### 6.4 Préparation entretien technique (45 min)
- [ ] Anticiper les questions du jury sur chaque compétence non couverte par le projet (NoSQL, microservices, mocking…)
- [ ] Préparer une question fil rouge : « pourquoi avoir fait X plutôt que Y ? » (puiser dans `Probleme-Solution.md`)

### 6.5 Préparation questionnaire pro (30 min)
- [ ] 4 questions : 2 fermées (français), 2 ouvertes (anglais) sur de la doc technique anglaise
- [ ] S'entraîner sur PHP.net en VO

---

## Priorité conseillée

| Ordre | Bloc | État | Effort restant | Risque si non fait |
|---|---|---|---|---|
| 🔴 1 | Phase 0 (hygiène) | ✅ | — | — |
| 🔴 2 | Phase 1 (sécurité) | 🟠 ~60% | 2-3 j (CSRF, headers, upload, brute-force, validation, PHPDoc) | Échec CCP1 |
| 🟠 3 | Phase 2 (conception/UML) | ⚪ | 3-5 j | Échec CCP2 |
| 🟠 4 | Phase 3 (tests) | ⚪ | 3-4 j | Échec CCP3 (compétence 9) |
| 🟡 5 | Phase 4 (DevOps) | 🟠 ~30% | 2-3 j (CI/CD, backups, doc déploiement) | Échec compétences 10-11 |
| 🟡 6 | Phase 5 (transversal) | ⚪ | en parallèle | Pénalité entretien |
| 🔴 7 | Phase 6 (livrables) | ⚪ | 5-7 j | Pas d'examen possible |

**Reste estimé : 3-4 semaines à temps plein** (4.1 + Phase 0 + IDOR/XSS/AdminMiddleware déjà acquis).

---

## Mapping compétences / phases

| # | Compétence professionnelle | CCP | Phases couvrant |
|---|---|---|---|
| 1 | Installer et configurer son environnement de travail | 1 | 4.1 ✅ (Docker), 5.1 (outils collab) |
| 2 | Développer des interfaces utilisateur | 1 | 1.1 ✅, 1.2, 5.5 (RGAA) |
| 3 | Développer des composants métier | 1 | 1.1 ✅ (IDOR), 1.4, 3.1 |
| 4 | Contribuer à la gestion d'un projet informatique | 1 | 5.1 |
| 5 | Analyser les besoins et maquetter une application | 2 | 2.1, 2.2 |
| 6 | Définir l'architecture logicielle d'une application | 2 | 2.3, 2.4 |
| 7 | Concevoir et mettre en place une BDD relationnelle | 2 | 2.3 (MCD/MPD), 2.5 (migrations ✅, jeu d'essai à faire) |
| 8 | Développer des composants d'accès aux données SQL/NoSQL | 2 | 1.1 ✅, 1.4, 3.1 |
| 9 | Préparer et exécuter les plans de tests | 3 | 3.1, 3.2, 3.3, 3.4 |
| 10 | Préparer et documenter le déploiement | 3 | 4.2 |
| 11 | Contribuer à la mise en production DevOps | 3 | 4.1 ✅, 4.3, 4.4 |
| T1 | Communiquer en français et en anglais | — | 5.2, 6.2, 6.4 |
| T2 | Démarche de résolution de problème | — | 5.4 🟠 (initié) |
| T3 | Apprendre en continu | — | 5.3 |
