# Oral de soutenance — Diaporama, entretien technique & questionnaire

> L'oral du titre complet = **3 temps** : **présentation projet (40 min, diaporama)** →
> **entretien technique (45 min)** → (+ **questionnaire professionnel 30 min**, écrit, passé à
> part). Ce guide couvre les trois.
>
> Rappel clé : pendant la **présentation**, le jury **n'intervient pas** ; il a déjà lu ton
> **dossier imprimé**. Ta présentation doit donc **raconter**, pas réciter le dossier.

---

## PARTIE A — Le diaporama (40 min)

### A.0 Règles d'or

- **40 min, ~32‑35 slides**, soit **~1 min 10 par slide**. **Répète chronométré** au moins 3 fois.
- **Une idée par slide.** Slides visuels (schémas, captures, **extraits de code courts**), pas des
  pavés de texte. Le texte, c'est **toi** qui le dis.
- **Raconte une histoire** : besoin → conception → réalisation → **sécurité** → tests → bilan.
  Le fil rouge = *« une application web multicouche, sécurisée à chaque couche »*.
- **Montre du code que tu sais réexpliquer au tableau.** Chaque extrait affiché est une invitation
  à une question : n'affiche que ce que tu maîtrises.
- **Prépare une démo/preuve de sécurité** (capture ou live) : la requête CSRF/IDOR rejetée en
  **403**. C'est le moment le plus mémorable.
- **Garde 2 min de marge** : vise 38 min de contenu pour absorber les imprévus.

### A.1 Déroulé slide par slide

> Le référentiel (plan formation) demande : expression des besoins → environnement technique →
> réalisations. On suit ce squelette, **enrichi** des livrables de conception (archi, maquettes,
> MCD/MPD, UML, sécurité, tests, jeu d'essai, veille) — comme le dossier.

| # | Slide | Durée | Contenu clé | Note orateur |
|---:|---|---:|---|---|
| 1 | **Titre** | 0:30 | Solage · CDA RNCP37873 · ton nom · session | Accroche : « un réseau social, prétexte à une appli sécurisée multicouche » |
| 2 | **Toi + le projet** | 1:30 | Qui tu es (parcours court), pitch Solage en 2 phrases | Détendu, regarde le jury |
| 3 | **Sommaire de la présentation** | 0:30 | Le plan que tu vas suivre | Pose le fil rouge sécurité |
| 4 | **Expression des besoins** | 1:30 | Problème, objectifs **vérifiables** | « Projet de formation : c'est moi qui formule le besoin » |
| 5 | **Périmètre & limites** | 1:00 | Ce qui est **hors‑scope** et **pourquoi** | Montre la maturité : étroit + sécurisé > large + bâclé |
| 6 | **Environnement technique** | 1:30 | La stack (tableau visuel) | Nomme chaque brique |
| 7 | **Justification des choix** | 1:30 | MVC maison, PostgreSQL, FrankenPHP, Traefik | « X plutôt que Y parce que Z » |
| 8 | **Gestion de projet** | 1:30 | Roadmap (phases), Git, journal décisions | Planification + suivi + comptes rendus, même solo |
| 9 | **Architecture multicouche** | 2:00 | Le schéma en couches | Le SQL en modèles, l'output en vues, l'autorisation en contrôleurs |
| 10 | **Sécurité par couche (DICP)** | 2:00 | Tableau DICP par couche | « La sécurité n'est pas un module, c'est transverse » |
| 11 | **Maquettes** | 1:30 | 1‑2 écrans (login + feed) | Conception, pas captures de l'app |
| 12 | **Enchaînement des écrans** | 1:00 | Le schéma de navigation | Parcours visiteur → user → admin |
| 13 | **MCD** | 1:30 | Entités‑associations | Règles du relationnel |
| 14 | **MPD + script** | 1:30 | Tables réelles, FK, index, `user_id` | « `user` réservé en PostgreSQL → `user_id` » |
| 15 | **Cas d'utilisation** | 1:30 | Acteurs × cas | Visiteur/User/Admin |
| 16 | **Diagramme de séquence** | 1:30 | « Publier un message » couche par couche | Montre les middlewares sécurité dans le flux |
| 17 | **Interface utilisateur** | 1:30 | Capture + extrait de vue + AJAX | Rendu optimiste, escaping client |
| 18 | **Composant métier** | 1:30 | `PostController::delete` (check IDOR) | « ownership‑ou‑admin → 403 + log » |
| 19 | **Composant d'accès aux données** | 1:30 | `PostModel::createPost` (requête préparée) | « code SQL / données séparés → injection impossible » |
| 20 | **Autres composants** | 1:30 | Router + Middlewares + Logger PSR‑3 | « framework maison : je possède chaque ligne » |
| 21 | **Sécurité — vue d'ensemble** | 1:00 | Le tableau OWASP → correctif | XSS, CSRF, SQLi, IDOR, headers |
| 22 | **Focus CSRF** | 1:30 | Synchronizer token, armé par le Router sur tout POST | `hash_equals`, `random_bytes` |
| 23 | **Démo : attaque rejetée** | 1:30 | Capture/live : POST falsifié → **403** + log `csrf.denied` | **Le moment fort** |
| 24 | **Le déclic IDOR (veille→fix)** | 1:30 | URSSAF → audit → 3 routes corrigées | Lie sécurité + veille + résolution de problème |
| 25 | **Plan de tests** | 1:30 | Types de tests, tableau du plan | Unitaires + **sécurité** + non‑régression |
| 26 | **Jeu d'essai** | 1:30 | « Publier un message » : entrée/attendu/obtenu + écarts | La fonctionnalité la plus représentative |
| 27 | **Veille sécurité** | 1:00 | Sources + vulnérabilité trouvée/corrigée | OWASP, ANSSI, CVE |
| 28 | **Difficultés résolues** | 1:30 | `STDOUT` CLI‑only, `headers already sent`, N+1 | Démontre T2 (résolution de problème) |
| 29 | **Bilan & perspectives** | 1:00 | Satisfactions + axes (CI/CD, validation, RGAA) | Lucide sur les angles morts |
| 30 | **Merci / questions** | 0:30 | Slide de clôture | Transition vers l'entretien |

**Total ≈ 39 min** (marge incluse). Slides « focus » (22‑24) compressibles si tu débordes ;
**ne sacrifie jamais** la sécurité (21‑24), le jeu d'essai (26) ni les tests (25).

### A.2 Ce qui fait gagner des points à l'oral

- **Dire les tradeoffs** : « j'ai choisi X en acceptant le coût W ». C'est la marque du niveau 6.
- **Assumer les manques** : « la validation serveur est encore partielle, voici mon plan ». Le jury
  préfère la lucidité au vernis.
- **Relier les compétences** : montre qu'un même fait (le fix IDOR) prouve **C3 + T2 + T3** à la fois.
- **Vocabulaire pro FR/EN** : OWASP, IDOR, CSRF, prepared statement, middleware, DICP, idempotent.

---

## PARTIE B — L'entretien technique (45 min)

Le jury **questionne** à partir de ton dossier et de ta présentation, pour **vérifier la maîtrise
des 11 compétences** + sonder celles **non couvertes** par le projet. Prépare **une fiche
question/réponse par compétence**. Voici les questions les plus probables et l'angle de réponse.

### Questions quasi‑certaines (prépare‑les par cœur)

- **« Pourquoi une requête préparée empêche l'injection SQL ? »**
  → Parce qu'elle **sépare le code SQL des données** : la requête est compilée avec des
  emplacements (`?`/`:param`), puis les valeurs utilisateur sont **liées** comme données, jamais
  interprétées comme du SQL. Démo : `PostModel::createPost`.
- **« XSS : pourquoi échapper à la sortie et pas à l'entrée ? »**
  → Pour **ne pas perdre l'information d'origine** (un user peut légitimement écrire `<`). On stocke
  brut, on échappe **à l'affichage** (`Utils::e()` serveur + `escapeHtml()` JS car le JS reconstruit
  du DOM).
- **« Authentification vs autorisation ? »**
  → Authn = *qui tu es* (`AuthMiddleware`, loggé). Authz = *ce que tu as le droit de faire*
  (`AdminMiddleware` + check ownership). L'**IDOR** vient de confondre les deux.
- **« C'est quoi un IDOR ? Donne ton exemple. »**
  → *Insecure Direct Object Reference* (OWASP A01). Sur Solage : `/edituser/{id}` modifiait le
  profil de n'importe qui en changeant l'`id`. Fix : `current_user === target || isAdmin()` → 403.
- **« C'est quoi le DICP ? »**
  → Disponibilité, Intégrité, Confidentialité, Preuve — les 4 indicateurs de sécurité d'un SI
  (ANSSI). Je les mappe couche par couche (cf. dossier §4.1).
- **« Comment fonctionne ta protection CSRF ? »**
  → **Synchronizer Token** : un secret par session (`random_bytes(32)`), exposé en `<meta>`, renvoyé
  par le client (header `X-CSRF-Token`), comparé en **temps constant** (`hash_equals`). **Armé par
  le Router sur tout POST** → impossible d'oublier un endpoint.
- **« Pourquoi pas un framework (Symfony/Laravel) ? »**
  → Choix **pédagogique** : maîtriser routeur/cycle requête‑réponse/sécurité plutôt que déléguer à
  de la magie. Coût assumé : je réécris des briques. En entreprise, je prendrais un framework.

### Banque de questions par compétence

| Comp. | Questions probables | Où est la réponse |
|---|---|---|
| **C1** | Différence image/conteneur ? Pourquoi un service `migrate` séparé ? Rôle de Traefik vs FrankenPHP ? | `docker-compose*.yml`, `00-README §2` |
| **C2** | Rendu optimiste, c'est quoi ? Comment tu rends l'UI responsive ? RGAA : 2 mesures concrètes ? | `index.js`, vues, dossier §4.6 |
| **C3** | Style défensif ? Comment tu gères les exceptions ? Où valides‑tu les entrées ? | `PostController`, `UserValidator` |
| **C4** | Comment planifies‑tu seul ? Agile vs séquentiel ? Tes comptes rendus ? | `ROADMAP`, `Probleme-Solution` |
| **C5** | Besoin ≠ fonctionnalité ? user story ? RGPD sur Solage ? | dossier §2 |
| **C6** | Pourquoi multicouche ? Rôle sécurité de chaque couche ? Un design pattern utilisé ? | dossier §4.1 |
| **C7** | MCD vs MPD ? Pourquoi `user_id` ? Migration vs `ALTER` manuel ? Sauvegarde/restauration ? | `solage.pg.sql`, `Migrations.php` |
| **C8** | Injection SQL : parade + **pourquoi** ? Une transaction, c'est quoi ? NoSQL : avantages/inconvénients ? | `modules/models/*` |
| **C9** | Test unitaire vs intégration ? Non‑régression ? Comment tu testes une faille ? | dossier §4.11‑4.12 |
| **C10** | Étapes de ton déploiement ? Rollback ? Environnements (dev/staging/prod) ? | `docker-compose.prod.yml` |
| **C11** | DevOps en une phrase ? CI/CD ? Ton pipeline ? Idempotence ? | Docker, `Migrations.php` |

> **C9, C10, C11** sont les plus susceptibles d'être creusées (compétences faiblement couvertes par
> le projet aujourd'hui). **Prépare‑les en priorité** : même si le projet ne les démontre pas
> totalement, tu dois **savoir en parler** (un pipeline GitHub Actions type, une procédure de
> rollback, la différence livraison/déploiement continus).

### Questions « ouvertes » de fin (presque sûres)

- **« Si tu refaisais le projet, que changerais‑tu ? »** → CI/CD dès le départ, tests en TDD,
  validation serveur systématique, framework si contexte pro.
- **« Quel est le point faible de ton application ? »** → Couverture de tests à étendre, validation
  d'upload par MIME réel, anti‑fixation de session. **Connaître ses angles morts = force.**
- **« Qu'est‑ce qui t'a le plus appris ? »** → Le cycle veille→audit→fix sur l'IDOR ; les bugs
  silencieux attrapés par l'audit MVC.

---

## PARTIE C — Le questionnaire professionnel (30 min, écrit)

### Format (à connaître pour ne pas être surpris)

- Passé **sur poste, sans internet, sous surveillance**, **tous les candidats en même temps**,
  **corrigé avant** ta présentation.
- Tu étudies une **documentation technique rédigée en anglais**, puis tu réponds à **4 questions** :
  - **2 questions fermées** à choix unique, **en français** ;
  - **2 questions ouvertes**, **posées en anglais**, **réponses courtes rédigées en anglais**.
- Niveau visé : **anglais B1** (compréhension écrite + expression écrite).

### Comment t'y préparer

1. **Lis de la vraie doc technique en anglais** régulièrement : **php.net**, **OWASP**,
   **PostgreSQL docs**, **Docker docs**, **MDN**. C'est exactement le registre de l'épreuve.
2. **Entraîne‑toi à résumer en anglais** un paragraphe technique en 2‑3 phrases.
3. **Fiche de vocabulaire** (à apprendre) :

| FR | EN |
|---|---|
| requête préparée | prepared statement |
| faille / vulnérabilité | flaw / vulnerability |
| injection SQL | SQL injection |
| contrôle d'accès | access control |
| couche | layer |
| intergiciel | middleware |
| déploiement | deployment |
| intégration continue | continuous integration |
| base de données | database |
| clé étrangère | foreign key |
| sauvegarde | backup |
| échappement | escaping |
| droits / permissions | permissions |

### Exemple d'entraînement (à refaire seul)

> **Doc (extrait, EN)** : *“Prepared statements separate SQL code from data. The query is sent to
> the database with placeholders, and parameters are bound separately, so user input is never
> interpreted as SQL.”*
>
> **Q (EN, ouverte)** : *“In your own words, why do prepared statements prevent SQL injection?”*
> **Réponse attendue (EN, courte)** : *“Because the SQL code and the user data are sent separately.
> The query structure is fixed first, then the values are bound as data, so user input cannot
> change the query and cannot be executed as SQL.”*

> **Q (EN, ouverte)** : *“Give one other measure to secure a web form.”*
> **Réponse** : *“Validate all inputs on the server side and add a CSRF token checked on every
> POST request.”*

⚠️ **Pièges questionnaire** : ne réponds pas en français aux questions anglaises (réponses
**en anglais** exigées). Réponses **courtes et justes** > longues et approximatives. Pour les 2
questions fermées (FR), méfie‑toi des distracteurs proches.

---

## Checklist veille de l'oral

- [ ] Diaporama exporté en **PDF** (police/mise en page figées) + sauvegarde clé USB
- [ ] Diaporama **répété chronométré** ≤ 40 min (au moins 3 passages)
- [ ] **Démo sécurité** prête (capture 403 CSRF/IDOR + ligne de log)
- [ ] **Fiches entretien** : les 7 questions quasi‑certaines + 1 fiche par compétence
- [ ] **C9/C10/C11** révisées en priorité (faible couverture projet)
- [ ] **Anglais** : 3 lectures de doc technique + 4 réponses ouvertes d'entraînement
- [ ] **Dossier de projet imprimé** apporté, **DP** rempli apporté
- [ ] Tenue pro, arriver **en avance**, eau, montre/chrono discret
