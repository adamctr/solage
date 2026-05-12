# Roadmap détaillée — Titre Pro CDA (RNCP37873)

> Le projet **solage** (réseau social type X/Twitter) sert de support à l'examen du Titre Professionnel **Concepteur Développeur d'Applications** (niveau 6, arrêté du 26/04/2023).
>
> Il faut couvrir **les 11 compétences professionnelles** réparties sur **3 CCP**, produire un **dossier de projet (40-60 p. + 40 p. d'annexes)**, un **diaporama (40 min)** et passer **entretien technique + questionnaire (anglais B1)**.

---

## État des lieux

### Stack technique
- PHP 7.4+ / MariaDB
- MVC custom (Router, Autoloader, Migrations, AuthMiddleware)
- Composer (`matthiasmullie/minify`)
- Sessions PHP, vanilla JS, CSS pur
- ~3 800 lignes au total

### Fonctionnalités existantes
- Auth : login / register / logout
- Posts : CRUD + upload image
- Réponses imbriquées (`reply_to` / `replyToParent`)
- Likes (posts & réponses)
- Profil utilisateur + édition
- Recherche (users, posts)
- Page admin avec recherche
- Minification d'assets (en prod)
- Messages dynamiques (toast)

### TODO listés dans le README
- Convention de nommage, indentation, PHPDoc partout
- Validations « à la volée » avec messages dynamiques
- Sécurisation modification profil
- Middleware Admin

---

## PHASE 0 — Hygiène immédiate (½ journée)

Ces points sont des **red flags** pour le jury et sont rapides à régler.

1. **Retirer les credentials BDD du code** (`includes/database.php` contient host/user/password en clair, versionnés dans `git log` — `solagecyrineadamfabio` est exposé).
   - Variables d'environnement (`.env` + `vlucas/phpdotenv` ou `getenv` natif).
   - Ajouter `.env`, `public/log.txt`, `.idea/`, `.DS_Store` au `.gitignore`.
   - **Faire tourner les credentials côté Alwaysdata** : le mot de passe est compromis publiquement.
2. **Purger les hashes de mots de passe réels** du dump `solage.sql` versionné (le remplacer par un seed de démo).
3. **Supprimer `public/log.txt`** versionné (rediriger les logs vers `/tmp` ou un dossier hors public).
4. **Corriger le bug `isAdmin()`** dans `SessionController.php:69` : `$_SESSION['role']` stocke l'ID numérique du rôle (1=Admin), pas la chaîne `'admin'` → comparer avec `1` ou faire la jointure sur `roles.name`.

--- DONE ---

---

## PHASE 1 — Sécurité & robustesse du code (CCP1)

> Couvre : *Développer des composants métier sécurisés*, *Développer des interfaces sécurisées*, recommandations ANSSI / OWASP.

### 1.1 Failles à corriger (XSS / IDOR / CSRF / Auth)
- **XSS** : `PostView.php` affiche `$post->getContent()` **sans `htmlspecialchars`** → faille XSS stockée. À corriger sur **tous les contenus utilisateurs** (posts, réponses, name, image path).
- **IDOR sur édition profil** (`UserController::update`) : aucun contrôle que `session.user_id === $userId`. N'importe qui connecté peut éditer n'importe quel profil.
- **IDOR sur suppression post / user** : le commentaire en `PostController::delete:128-131` montre que la vérif d'ownership est désactivée.
- **CSRF** : aucun token sur `/api/post`, `/api/like`, `/api/posts/delete`, `/edituser/{id}`, `/login`, `/register`. 

----

Implémenter un token CSRF en session, vérifié à chaque POST.
- **Upload d'images** : valider via `getimagesize()` et MIME réel (`finfo`), pas seulement l'extension ; bloquer SVG/PHP ; renommer ; limiter la taille.
- **Headers de sécurité** : `Content-Security-Policy`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Strict-Transport-Security`, cookies `HttpOnly` + `Secure` + `SameSite=Lax`.
- **Régénérer l'ID de session** (`session_regenerate_id(true)`) après login/logout pour empêcher la fixation de session.
- **Brute-force** : rate-limit ou compteur d'échecs sur `/login`.

### 1.2 Validation à la volée (TODO du README)
- Côté JS : `dynamicMessages.js` donne déjà la base. Ajouter écouteurs `input`/`blur` sur tous les formulaires (login, register, edit profil, post) avec règles : email valide, mot de passe ≥ 8 caractères + 1 maj + 1 chiffre, contenu post ≤ N caractères.
- Côté PHP : doubler **systématiquement** la validation (`ValidatorController`) — actuellement `register` ne vérifie ni le format email, ni la robustesse du mot de passe.
- Messages d'erreur dynamiques liés à chaque champ (pas seulement un toast global).

### 1.3 Middleware Admin (TODO du README)
- Créer `AdminMiddleware` similaire à `AuthMiddleware`, vérifiant `role === 1`.
- L'appliquer sur les 3 routes admin (`/admin`, `/admin/search/results/users`, `/admin/search/results/posts`) **qui ne sont actuellement protégées par rien**.
- Et sur `/api/users/delete`, `/api/posts/delete` quand l'auteur n'est pas le propriétaire.

### 1.4 Qualité de code (TODO du README)
- **PHPDoc partout** (controllers, models, views) : `@param`, `@return`, `@throws`.
- Convention de nommage homogène : actuellement mix `DataBase`/`Database`, `MainPostView`, etc.
- Indentation cohérente, supprimer code mort (`echo "Requête URI"` debug dans `Router.php`, blocs commentés dans `PostController`).
- Activer `declare(strict_types=1)` en tête de fichier.

---

## PHASE 2 — Conception & documentation projet (CCP2)

> Couvre : *Analyser les besoins et maquetter*, *Définir l'architecture logicielle*, *Concevoir et mettre en place une BDD relationnelle*.

À produire (ces livrables iront **dans le dossier de projet** + annexes).

### 2.1 Expression des besoins
- Cahier des charges / expression de besoins (le projet est en formation → tu rédiges toi-même les objectifs et limites).
- Liste des **user stories** ou **cas d'usage** (visiteur s'inscrit, utilisateur poste, like, répond, supprime, admin modère…).
- Identifier les contraintes : RGPD (mentions légales, consentement), RGAA (accessibilité), éco-conception.

### 2.2 Maquettes
- Maquettes Figma (ou équivalent) de toutes les pages : login, register, home (feed), post détail, profil user, édition profil, admin, recherche, 404.
- **Schéma d'enchaînement** des écrans (storyboard / sitemap).
- Justifier la charte graphique et les choix UX.

### 2.3 Diagrammes UML (obligatoires pour le CCP2)
- **Diagramme de cas d'utilisation** (acteurs : Visiteur, Utilisateur, Admin).
- **Diagrammes de séquence** sur 1 ou 2 cas significatifs (ex : « Poster un post avec image », « Répondre à un post »).
- **Diagramme de classes** (PostModel, UserModel, LikeModel, etc.).
- **MCD** (modèle conceptuel) et **MPD** (modèle physique) de la base.
- **Schéma d'architecture multicouche** (Présentation / Contrôleur / Métier / Accès données / SGBD) avec rôle sécurité de chaque couche (DICP).

### 2.4 Spécifications techniques
- Justifier les choix : pourquoi PHP vanilla MVC vs framework, pourquoi MariaDB, etc.
- Documenter le routeur, l'autoloader, le système de migrations.
- Documenter la stratégie de sécurité couche par couche (validation en présentation, ré-validation côté serveur, requêtes préparées en couche d'accès données).

### 2.5 Améliorations BDD à envisager
- Créer un répertoire `migrations/` versionné (vérifier ce que fait actuellement `Migrations.php`).
- Ajouter des index manquants si pertinent.
- Renommer / fiabiliser le champ `roles.id` (utiliser une `ENUM` ou la jointure pour fiabiliser `isAdmin()`).
- Documenter la sauvegarde / restauration de la base de test (utilitaires `mysqldump`).
- Créer un **jeu d'essai complet et reproductible** (pas seulement les 3 lignes du dump actuel).

---

## PHASE 3 — Tests & qualité (CCP3 — compétence n°9)

> Couvre : *Préparer et exécuter les plans de tests*. C'est un **gros trou actuel** du projet (aucun test).

### 3.1 Tests unitaires
- Mettre en place **PHPUnit** via Composer.
- Couvrir minimum : `ValidatorController`, `UserModel`, `PostModel`, `LikeModel`, `Router`.
- Mocker la BDD (sqlite en mémoire ou base de test dédiée).

### 3.2 Tests d'intégration
- Tester les routes (login → poste un post → like → delete) en simulant les requêtes HTTP.
- Outil : PHPUnit + bibliothèque HTTP, ou Cypress / Playwright pour le bout en bout.

### 3.3 Tests de sécurité
- Documenter et exécuter au moins :
  - 1 test d'**injection SQL** sur la recherche.
  - 1 test **XSS** sur création de post.
  - 1 test **CSRF** sur action critique.
  - 1 test **IDOR** (édition d'un autre utilisateur).
- Outils : OWASP ZAP en local, ou checklist OWASP Top 10 manuelle.

### 3.4 Plan de tests rédigé
- Document listant **toutes les fonctionnalités** + les cas testés (entrée / attendu / obtenu) + analyse des écarts.
- Inclure tests d'**acceptation** (parcours utilisateur complet).
- Inclure un **test de charge léger** (Apache Bench ou k6) sur le feed.

### 3.5 Qualité de code
- **PHPStan** ou **Psalm** niveau 5+ → rapport propre.
- **PHP_CodeSniffer** sur PSR-12.
- **ESLint** sur les fichiers JS.

---

## PHASE 4 — Déploiement & DevOps (CCP3 — compétences n°10 & 11)

> Couvre : *Préparer et documenter le déploiement*, *Contribuer à la mise en production DevOps*. C'est une **nouveauté du référentiel 2023** et c'est entièrement absent aujourd'hui.

### 4.1 Conteneurisation
- **Dockerfile** PHP-Apache (ou PHP-FPM + Nginx).
- **docker-compose.yml** avec services : `app`, `db` (MariaDB), `phpmyadmin`.
- Volumes pour les uploads et la BDD.
- Variables d'env via fichier `.env`.
- Reproduction locale d'un environnement proche prod.

### 4.2 Procédure de déploiement
- Document `DEPLOYMENT.md` : pré-requis, étapes, rollback.
- Scripts de déploiement (shell ou Makefile) : `make deploy`, `make rollback`.
- 3 environnements documentés : dev (local Docker), SIT/test (Alwaysdata staging ?), prod (Alwaysdata).

### 4.3 CI/CD
- **GitHub Actions** (ou GitLab CI) : un workflow qui à chaque push :
  - lance PHPStan,
  - lance PHPUnit,
  - lance ESLint,
  - construit l'image Docker,
  - (bonus) déploie automatiquement sur Alwaysdata via SSH/FTP.
- Documenter le **rapport CI** et savoir l'expliquer en entretien.

### 4.4 Sauvegardes & résilience
- Cron de sauvegarde BDD.
- Documenter la procédure de restauration.

---

## PHASE 5 — Compétences transversales

### 5.1 Gestion de projet (compétence n°4)
- Mettre tout le travail dans un outil collaboratif : **GitHub Projects**, Trello ou Jira.
- Découper en sprints (méthode Agile / Scrum simplifié).
- **Comptes rendus** de sessions de travail (au moins 3-4 documentés).
- **Planning** (Gantt ou roadmap visuelle) avec planifié vs réalisé.

### 5.2 Anglais B1
- Préparer **2 questions ouvertes type** : description du projet en anglais, choix techniques en anglais.
- S'entraîner à lire de la doc technique en anglais (PHP.net, OWASP) et à la commenter.
- Avoir un README ou un dossier annexe en anglais sur l'architecture peut être un plus.

### 5.3 Veille technologique
- Tenir un journal de veille (Feedly, RSS, Twitter techno…) sur PHP, sécurité, OWASP.
- Documenter dans le dossier : sources suivies, vulnérabilités détectées sur le projet (ex : on a découvert une faille XSS lors de la veille → corrigée).

### 5.4 Démarche de résolution de problèmes
- Documenter **un cas concret** de bug rencontré sur le projet, ta démarche de diagnostic, les tests menés, la correction.

### 5.5 Accessibilité (RGAA) & RGPD & éco-conception
- Audit RGAA basique : contraste, alt, aria-label, navigation clavier.
- Mentions légales + politique de cookies + page de confidentialité (RGPD).
- Une section éco-conception : minification (déjà en place), lazy-load images (mentionné comme « pas fait » dans le README), poids des assets.

---

## PHASE 6 — Livrables d'examen

D'après le **RC** (référentiel d'évaluation), tu dois produire :

### 6.1 Le dossier de projet (papier, imprimé)
- **40 à 60 pages** (hors page de garde, sommaire et annexes), schémas inclus.
- **40 pages d'annexes max**.
- Plan exigé (puisque projet de formation, pas en entreprise) :
  1. Liste des compétences mises en œuvre.
  2. Expression des besoins (objectifs, limites).
  3. Environnement technique.
  4. Réalisations couvrant les compétences.
- Pratique : **dépasser ce plan minimaliste** en y intégrant les éléments du plan « projet en entreprise » (cahier des charges, archi, MCD/MPD, maquettes, UML, sécurité, plan de tests, jeu d'essai, veille). Le jury y est habitué.

### 6.2 Le diaporama de soutenance (≈ 40 min, 30-35 slides)
Structure type :
1. Présentation perso + projet
2. Expression du besoin
3. Environnement technique + architecture
4. Maquettes + enchaînement
5. MCD/MPD + script création BDD
6. Diagrammes cas d'usage + séquence
7. Captures d'écran + extraits de code (interfaces, métier, accès données)
8. Sécurité (XSS, CSRF, SQLi… ce que tu as corrigé)
9. Plan de tests + jeu d'essai
10. Veille techno + vulnérabilités trouvées
11. Synthèse / difficultés / perspectives

### 6.3 Le dossier professionnel (DP)
- Document AFPA séparé (le `Template - vierge - TITRE6.docx` dans `/documents/`) à remplir avec ta présentation, ton parcours, ton expérience.

### 6.4 Préparation entretien technique (45 min)
- Anticiper les questions du jury sur **chaque compétence non couverte par le projet** (ex : NoSQL, microservices, mocking… s'ils ne sont pas dans ton appli).
- Préparer une question fil rouge : « pourquoi avoir fait X plutôt que Y ? ».

### 6.5 Préparation questionnaire pro (30 min)
- 4 questions : 2 fermées (français), 2 ouvertes (anglais) sur de la doc technique anglaise.
- S'entraîner sur PHP.net en VO.

---

## Priorité conseillée

| Ordre | Bloc | Effort | Risque si non fait |
|---|---|---|---|
| 🔴 1 | Phase 0 (hygiène) | 0,5 j | Credentials exposés, élimination directe |
| 🔴 2 | Phase 1 (sécurité) | 4-6 j | Échec CCP1 (sécurité = critère central) |
| 🟠 3 | Phase 2 (conception/UML) | 3-5 j | Échec CCP2 (rien à présenter sur la conception) |
| 🟠 4 | Phase 3 (tests) | 3-4 j | Échec CCP3 (compétence 9) |
| 🟡 5 | Phase 4 (DevOps) | 3-4 j | Échec compétences 10-11 (nouveau référentiel 2023) |
| 🟡 6 | Phase 5 (transversal) | en parallèle | Pénalité sur entretien |
| 🔴 7 | Phase 6 (livrables) | 5-7 j | Pas d'examen possible |

**Total estimé : 4 à 6 semaines à temps plein**, à ajuster selon ce qui est déjà rédigé hors-repo (Figma, journal de bord…).

---

## Mapping compétences / phases

| # | Compétence professionnelle | CCP | Phases couvrant |
|---|---|---|---|
| 1 | Installer et configurer son environnement de travail | 1 | 4.1 (Docker), 5.1 (outils collab) |
| 2 | Développer des interfaces utilisateur | 1 | 1.1, 1.2, 5.5 (RGAA) |
| 3 | Développer des composants métier | 1 | 1.1, 1.4, 3.1 |
| 4 | Contribuer à la gestion d'un projet informatique | 1 | 5.1 |
| 5 | Analyser les besoins et maquetter une application | 2 | 2.1, 2.2 |
| 6 | Définir l'architecture logicielle d'une application | 2 | 2.3, 2.4 |
| 7 | Concevoir et mettre en place une BDD relationnelle | 2 | 2.3 (MCD/MPD), 2.5 |
| 8 | Développer des composants d'accès aux données SQL/NoSQL | 2 | 1.1, 1.4, 3.1 |
| 9 | Préparer et exécuter les plans de tests | 3 | 3.1, 3.2, 3.3, 3.4 |
| 10 | Préparer et documenter le déploiement | 3 | 4.2 |
| 11 | Contribuer à la mise en production DevOps | 3 | 4.1, 4.3, 4.4 |
| T1 | Communiquer en français et en anglais | — | 5.2, 6.2, 6.4 |
| T2 | Démarche de résolution de problème | — | 5.4 |
| T3 | Apprendre en continu | — | 5.3 |
