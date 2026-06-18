# Diaporama Solage — textes des slides + notes orateur

> **À quoi sert ce fichier.** Pour chaque slide : ce qui doit être **écrit sur la slide**
> (minimal — le reste, c'est toi qui le dis) et les **notes orateur** à coller dans Canva.
>
> **Pourquoi un fichier séparé pour les notes ?** L'API Canva permet d'éditer le texte des
> slides mais **pas** d'écrire les notes d'orateur. Tu les colles toi-même : dans Canva, en bas
> de l'écran, clique **« Notes »** sous chaque slide et colle le bloc 🎤 correspondant.
>
> **Workflow :** (1) tu relis/corriges ce fichier → (2) tu dupliques les 3 slides sécurité
> (section ci-dessous) → (3) je pousse le texte des slides sur Canva → (4) tu colles les notes.
>
> **Tout ce qui est écrit ici est vérifié dans le code réel** (chemins/fichiers cités). Rien
> d'inventé : si le jury creuse, tu peux pointer le fichier.

---

## ⚠️ Étape préalable : 3 slides sécurité à dupliquer dans Canva

L'API ne peut pas **créer** de slides — donc tu fais ces 3 duplications à la main (2 min),
puis je remplis le texte. La sécurité est ton fil rouge : sans slide dédiée, tu perds ton meilleur
argument. On passe de **18 → 21 slides** (bon rythme pour 40 min).

| À faire dans Canva | Résultat | Pourquoi cette position |
|---|---|---|
| **Dupliquer la slide 11** (Architecture Technique) → glisser **juste après la 11** | nouvelle slide **« Sécurité par couche »** (badge §2) | la sécurité juste après l'archi : « pas un module, une propriété transverse » |
| **Dupliquer la slide 15** (Fonctionnalité) → glisser **juste après la démo** | nouvelle slide **« Focus CSRF + démo 403 »** (badge §3) | le moment fort, après avoir montré l'appli qui marche |
| **Dupliquer encore** cette nouvelle slide → la placer **juste après** | nouvelle slide **« Le déclic IDOR »** (badge §3) | enchaîne sur la 2ᵉ faille, lie veille → audit → fix |

Dupliquer = clic droit sur la slide → **Dupliquer la page** (la nouvelle hérite du style/badge).
Dis-moi quand c'est fait, je pousse le texte.

> Les badges de section (1·2·3·4) marquent la **section**, pas le numéro de slide — donc dupliquer
> dans une section ne casse aucune numérotation.

---

## 🔧 Corrections d'incohérences (je les applique en même temps que le texte)

- **Slide 5** (intercalaire « Gestion / Conception ») : son badge dit encore **« 1 · Objectifs du
  projet »** → je le passe en **« 2 »** et j'enlève le faux sous-titre.
- **Titre du design** : actuellement « Votre texte de paragraphe » (placeholder Canva) → je le
  renomme **« Solage — Soutenance CDA »**.
- **Slides MCD (13) et MLD (14)** : actuellement **vides** (juste le titre). Le texte des entités/
  tables, je peux le mettre ; **mais le schéma dessiné (image), non** — l'API n'insère pas de
  diagramme. → soit tu colles ton image MCD/MLD, soit on liste les tables en texte (je propose les
  deux versions dans les slides ci-dessous).

---

# Le diaporama slide par slide

Légende : **🖥️ Sur la slide** = ce qui est affiché (court). **🎤 Notes** = ce que tu dis (à coller).

---

## Slide 1 — Titre  · 0:30

**🖥️ Sur la slide**
- **Solage**
- Concepteur Développeur d'Application — RNCP 37873
- Adam COURTARO · Session 2026
- *(petite ligne)* Un réseau social, prétexte à une application web **sécurisée à chaque couche**

**🎤 Notes**
> Bonjour, je suis Adam Courtaro, je présente **Solage**, mon projet pour le titre de Concepteur
> Développeur d'Application. Solage est un réseau social minimaliste — mais le réseau social est
> surtout un **prétexte** : ce qui m'intéressait, c'était de construire une application web
> **multicouche et sécurisée à chaque couche**, sans framework, pour maîtriser chaque brique.
> Je vais vous raconter le projet en suivant le cycle besoin → conception → réalisation → sécurité
> → tests → déploiement. Le fil rouge, tout du long, c'est la sécurité.

---

## Slide 2 — Sommaire · 0:30

**🖥️ Sur la slide** (garder les 5 sections)
1. Introduction & analyse du projet
2. Gestion / Conception
3. Fonctionnalités représentatives
4. Préparation au déploiement
5. Conclusion

**🎤 Notes**
> Voici le plan. On part de l'analyse du besoin, puis la conception — gestion de projet, maquettes,
> technologies, architecture, base de données et **sécurité**. Ensuite les fonctionnalités
> représentatives avec une démo, la préparation au déploiement — tests et CI/CD — et enfin le bilan.
> Gardez en tête une idée : à chaque section, je reviens sur **comment cette couche est sécurisée**.

---

## Slide 3 — Introduction (§1) · 1:00

**🖥️ Sur la slide** (garder les icônes X/Twitter)
- Solage — un **clone minimaliste de X / Twitter**
- Fil, posts & réponses, likes, profils, recherche, admin
- **Projet de formation**

**🎤 Notes**
> Solage, concrètement : un fil d'actualité, la publication de messages, les réponses en fil de
> discussion, les likes, les profils utilisateurs, une recherche et un espace d'administration.
> J'ai choisi un réseau social parce que c'est un domaine **assez riche** pour exercer toutes les
> compétences du titre : authentification, autorisation, relations en base, requêtes AJAX, sécurité.
> Point important : c'est un **projet de formation**, donc c'est **moi qui formule le besoin** — j'y
> reviens sur la slide objectifs.

---

## Slide 4 — Objectifs du projet (§1) · 1:30

**🖥️ Sur la slide** (garder les icônes)
- **But pédagogique** : maîtriser, pas déléguer
- Objectifs vérifiables : MVC maison · sécurité multicouche · PostgreSQL · Docker
- **Périmètre maîtrisé** > large et bâclé

**🎤 Notes**
> Mon objectif n'est pas commercial, il est **pédagogique** : comprendre le cycle requête-réponse,
> la sécurité, le relationnel, la conteneurisation — en les **écrivant moi-même** plutôt qu'en
> déléguant à un framework.
> J'ai fixé des objectifs **vérifiables** : un routeur et un cycle MVC maison fonctionnels ; une
> sécurité démontrable couche par couche ; une base PostgreSQL avec migrations ; un déploiement
> Docker reproductible.
> Sur le **périmètre**, j'assume des limites : pas de messagerie privée, pas de notifications temps
> réel, pas de modération avancée. C'est un choix de maturité — **un périmètre étroit et bien
> sécurisé vaut mieux qu'un périmètre large et fragile.**

---

## Slide 5 — Intercalaire « Gestion / Conception » (§2) · 0:20

**🖥️ Sur la slide**
- **Gestion / Conception** *(badge corrigé en « 2 »)*

**🎤 Notes**
> On entre dans la conception. Je vais couvrir : comment j'ai géré le projet, les maquettes, les
> choix technologiques, l'architecture, la base de données — et la sécurité, qui irrigue tout ça.

---

## Slide 6 — Parties prenantes (§2) · 0:40

**🖥️ Sur la slide** (garder Fabio barré)
- Adam COURTARO ~~Fabio Voliani~~
- **Projet solo**

**🎤 Notes**
> Le projet devait être à deux ; il est devenu **solo**. C'est un point que j'assume : ça veut dire
> que je **possède chaque ligne** de code — il n'y a pas une brique que je ne saurais pas réexpliquer.
> Le revers, c'est l'absence de relecture par un pair : je l'ai compensée par des outils de qualité
> automatiques — j'y reviens.

---

## Slide 7 — Gestion de projet : comment ça a été géré (§2) · 1:00

**🖥️ Sur la slide**
- **Git** : historique par phases
- **Journal de décisions** (problème → solution)
- Planifier · suivre · rendre compte — **même seul**

**🎤 Notes**
> Même seul, je me suis imposé une vraie gestion. **Git** d'abord : un historique découpé par phases
> (mise en place Docker, MVC, sécurité, tests…), avec des messages de commit qui racontent le
> pourquoi. Ensuite un **journal de décisions** : à chaque difficulté, je documente le problème et la
> solution retenue — par exemple le passage de MariaDB à PostgreSQL, ou la découverte de la faille
> IDOR. Donc on a bien les trois temps attendus : **planification, suivi, comptes rendus**.

---

## Slide 8 — Gestion de projet : aujourd'hui (§2) · 1:00

**🖥️ Sur la slide**
- Ce que je referais autrement :
- **CI/CD dès le départ** · **TDD** · validation serveur systématique
- Un **framework** si contexte pro

**🎤 Notes**
> Avec le recul, qu'est-ce que je changerais ? Quatre choses. **Un** : mettre la CI/CD en place dès
> le premier jour, pas à la fin. **Deux** : faire du **TDD** — écrire le test avant le code, surtout
> pour la sécurité. **Trois** : généraliser la **validation côté serveur** dès le départ (elle est
> encore partielle aujourd'hui, je l'assume). **Quatre** : en contexte professionnel, je partirais
> sur un **framework** comme Symfony — ici le « tout maison » était un choix d'apprentissage assumé,
> pas un choix que je referais en entreprise.

---

## Slide 9 — Conception : Maquettes (§2) · 1:00

**🖥️ Sur la slide** (garder les 2 maquettes)
- Maquettes : **connexion** + **fil d'actualité**
- Conception de l'UI **avant** le code

**🎤 Notes**
> Voici deux maquettes : l'écran de connexion et le fil principal. L'important, c'est que ce sont des
> **maquettes de conception**, faites **avant** de coder, pas des captures de l'appli finie. J'ai
> visé une interface épurée, proche des codes de X/Twitter pour que l'utilisateur ne soit pas perdu :
> une colonne de navigation, un fil central, l'action de publication toujours accessible. Ces
> maquettes ont guidé la structure de mes vues.

---

## Slide 10 — Conception : Technologies (§2) · 1:30

**🖥️ Sur la slide** (garder les logos ; idéalement ajouter FrankenPHP + Traefik)
- **PHP 8.3** (MVC maison) · **PostgreSQL 16** · **Docker**
- **FrankenPHP + Caddy** · **Traefik 3.1**

**🎤 Notes**
> Ma stack, et surtout **pourquoi** chaque brique — c'est là que se voit le raisonnement.
> **PHP en MVC maison** plutôt qu'un framework : pour **maîtriser** le routeur et le cycle
> requête-réponse au lieu de les déléguer à de la magie. Coût assumé : je réécris des briques.
> **PostgreSQL** plutôt que MySQL : un SGBD relationnel rigoureux, typage strict, contraintes solides.
> **FrankenPHP + Caddy** : un serveur applicatif PHP moderne, avec HTTPS quasi automatique.
> **Traefik** en reverse proxy : il route les requêtes et gère les certificats **Let's Encrypt** tout
> seul en production.
> **Docker** pour tout : un environnement **reproductible**, identique de ma machine au serveur.
> La formule générale : **« j'ai choisi X plutôt que Y parce que Z, en acceptant le coût W ».**

---

## Slide 11 — Conception : Architecture Technique (§2) · 1:30

**🖥️ Sur la slide** (garder le schéma MVC)
- **MVC + Front Controller** maison
- Modèles = **tout le SQL** · Vues = **toute la sortie** · Contrôleurs = **autorisation**
- src/ = routeur, middlewares, logger

**🎤 Notes**
> Mon architecture est en couches, avec une règle stricte par couche.
> Le point d'entrée unique, `public/index.php`, fait l'autoload, démarre la session et passe la main
> au **routeur** — c'est le patron **Front Controller**. Le routeur, maison (`src/Router.php`),
> lit l'URL et appelle « Contrôleur#méthode ».
> Ensuite, des règles que je m'interdis de violer : **tout le SQL est dans les modèles**, **toute la
> sortie HTML/JSON est dans les vues**, et **l'autorisation est dans les contrôleurs**. Les briques
> transverses — routeur, middlewares d'authentification et de CSRF, logger — sont dans `src/`.
> Cette discipline, c'est ce qui rend la sécurité **vérifiable** : je sais exactement où regarder.

---

## Slide 12 — 🆕 Sécurité par couche (DICP) (§2) · 1:30

**🖥️ Sur la slide** (petit tableau Couche → Protection)
| Couche | Protection |
|---|---|
| Transport | HTTPS + HSTS (Traefik) |
| Session | cookie HttpOnly · SameSite · Secure |
| Requête | **CSRF sur tout POST → 403** |
| Accès | Auth + Admin + ownership |
| Données | requêtes préparées (anti-SQLi) |
| Sortie | échappement serveur + client (anti-XSS) |
| Preuve | Logger PSR-3 (refus tracés) |

**🎤 Notes**
> Voici ma thèse : **la sécurité n'est pas un module, c'est une propriété transverse**. Je la lis avec
> le **DICP** — Disponibilité, Intégrité, Confidentialité, Preuve, les quatre indicateurs de l'ANSSI.
> Couche par couche : le **transport** est chiffré (HTTPS + HSTS via Traefik) — confidentialité. La
> **session** utilise un cookie `HttpOnly`, `SameSite=Lax` et `Secure` en prod — confidentialité +
> intégrité. Chaque **requête** modifiante est protégée par un **token CSRF vérifié sur tout POST**
> — intégrité. L'**accès** passe par des middlewares d'authentification et d'autorisation plus un
> contrôle de propriété — confidentialité. Les **données** ne passent que par des requêtes préparées
> — intégrité, anti-injection. La **sortie** est systématiquement échappée — intégrité, anti-XSS.
> Et la **preuve** : un logger trace chaque refus (403, token rejeté). Je détaille deux de ces couches
> juste après.

> *Si le jury demande « c'est quoi le DICP ? » → Disponibilité, Intégrité, Confidentialité, Preuve.*

---

## Slide 13 — Conception : Base de données — MCD (§2) · 1:30

**🖥️ Sur la slide** (coller ton image MCD ; à défaut, ce texte)
- 4 entités : **roles · users · posts · likes**
- User *crée* Post · User *aime* Post · Post *répond à* Post · User *a un* Role

**🎤 Notes**
> Au niveau conceptuel, **quatre entités**. **Users**, les utilisateurs. **Posts**, qui portent à la
> fois les messages et les réponses. **Likes**, l'association « un utilisateur aime un post ». Et
> **Roles**, une table de référence : Admin, Utilisateur, Modérateur.
> Les associations : un utilisateur **crée** plusieurs posts ; un utilisateur **aime** plusieurs
> posts — c'est une association N-N, d'où la table `likes` ; un post peut **répondre à** un autre
> post, c'est une relation réflexive qui modélise les fils de discussion ; et un utilisateur **a un**
> rôle. C'est un modèle volontairement simple, mais qui couvre toutes les règles du relationnel :
> entités, associations, cardinalités.

---

## Slide 14 — Conception : Base de données — MLD (§2) · 1:30

**🖥️ Sur la slide** (coller ton image MLD ; à défaut, ce texte)
- Tables réelles + **clés étrangères** + index
- Convention **`user_id`** (`user` réservé en PostgreSQL)
- Migrations **idempotentes** (service `migrate`)

**🎤 Notes**
> Au niveau logique, les tables réelles avec leurs clés étrangères. `users` référence `roles` ;
> `posts.user_id` référence `users` ; `likes` référence `users` **et** `posts`. J'ai posé des
> **index** sur les clés étrangères pour les jointures, et des contraintes `ON DELETE CASCADE` :
> si je supprime un utilisateur, ses posts et ses likes partent avec — intégrité référentielle.
> Un détail qui compte : en PostgreSQL, **`user` est un mot réservé**, donc mes colonnes de clé
> étrangère s'appellent **`user_id`**, jamais `user`.
> Enfin, les évolutions de schéma passent par `src/Migrations.php`, **idempotent** : il s'appuie sur
> `information_schema` pour ne rien recréer en double, et tourne dans un service Docker **`migrate`**
> séparé, avant l'application.

> *Pièges possibles du jury (réponses honnêtes prêtes) :*
> *— « la colonne `likes` dans `posts` ? » → vestige du port MariaDB, plus écrite ; le compte réel se
> fait par `COUNT` sur la table `likes`. Migration de nettoyage non prioritaire.*
> *— « pourquoi `post` et pas `post_id` dans `likes` ? » → petite incohérence au port du schéma ;
> renommer une FK demande un dump/restore, pas prioritaire mais identifié.*

---

## Slide 15 — Démo (intercalaire §3) · 0:20

**🖥️ Sur la slide**
- **Démo**

**🎤 Notes**
> Place à la démonstration. Je vais vous montrer la fonctionnalité la plus représentative — publier
> un message — puis, juste après, **une attaque qui échoue** : c'est là que la sécurité devient
> concrète.

---

## Slide 16 — Fonctionnalité : Publier un message (§3) · 1:30

**🖥️ Sur la slide**
- **Fonctionnalité : publier un message**
- Vue → Contrôleur *(validation inline)* → Modèle → BDD
- **AJAX sans rechargement** · échappement à l'affichage

**🎤 Notes**
> Je prends la fonctionnalité « publier un message », parce qu'elle traverse **toutes les couches**.
> L'utilisateur écrit dans le fil ; le JavaScript envoie la requête en **AJAX** et, **après la réponse
> du serveur**, ajoute le message au fil **sans recharger la page**. Côté serveur, le **contrôleur**
> reçoit la requête et **valide l'entrée** (contenu non vide) ; puis le **modèle**
> `PostModel::createPost` insère via une **requête préparée** — le contenu utilisateur est lié comme
> une donnée, jamais interprété comme du SQL. Au retour, le contenu est **échappé à l'affichage**,
> côté serveur avec `Utils::e()` et côté client avec `escapeHtml()`, parce que le JS reconstruit du
> DOM. Une seule action, mais toutes mes couches de sécurité sont sollicitées d'un coup.

> *Si on demande « où est le validateur dédié pour publier ? » → ma couche `validators/` contient
> `UserValidator` pour l'authentification ; pour les posts, la validation est encore **dans le
> contrôleur**. La généraliser est exactement mon point d'amélioration (cf. slides 8 et 21).*

---

## Slide 17 — 🆕 Focus CSRF + démo 403 (§3) · 1:30  ⭐ moment fort

**🖥️ Sur la slide** (court + 1 extrait)
- **Synchronizer token** — armé par le Router sur **tout POST**
- `random_bytes(32)` · comparaison `hash_equals` · échec → **403 + log**
```php
if ($route['method'] === 'POST') {
    $csrfMiddleware = new CsrfMiddleware();
    $csrfMiddleware->handle();   // 403 si token absent/invalide
}
```

**🎤 Notes**
> Ma protection CSRF, c'est un **Synchronizer Token**. Au **premier rendu** après l'ouverture de
> session, je génère un secret — `bin2hex(random_bytes(32))`, 64 caractères — stocké en session. Je l'expose de deux façons : dans
> un champ caché pour les formulaires, et dans une balise `<meta>` que mon JavaScript renvoie
> automatiquement dans l'en-tête `X-CSRF-Token` sur chaque requête modifiante. Côté serveur, je le
> compare en **temps constant** avec `hash_equals` — pas de fuite par timing.
> Le point clé : cette vérification est **armée par le routeur sur TOUS les POST** (`Router.php`,
> lignes 54-57). Je ne peux donc pas **oublier** un endpoint — c'est structurel.
> **La démo** : j'envoie un POST falsifié, sans token. Réponse : **403 Forbidden**, et une ligne de
> log `csrf.token.rejected`. *(montrer la capture / le live + la ligne de log)* Voilà à quoi
> ressemble une attaque rejetée.

---

## Slide 18 — 🆕 Le déclic IDOR : veille → audit → fix (§3) · 1:30

**🖥️ Sur la slide**
- **IDOR** (OWASP A01) : `/edituser/{id}` — changer l'`id` = éditer **autrui**
- Fix : `current === target || isAdmin()` → **403 + log**
- **3 routes corrigées**

**🎤 Notes**
> Voici l'histoire dont je suis le plus fier, parce qu'elle relie **veille**, **audit** et
> **résolution de problème**. En faisant ma veille de sécurité — OWASP, l'actualité, des cas comme la
> faille URSSAF — je tombe sur la notion d'**IDOR**, *Insecure Direct Object Reference*, le numéro 1
> du Top 10 OWASP. Je me demande : est-ce que **moi** j'ai ça ? J'audite mon code… et oui :
> ma route `/edituser/{id}` modifiait le profil de **n'importe qui** en changeant l'`id` dans l'URL.
> J'avais confondu **authentification** — savoir qui tu es — et **autorisation** — savoir ce que tu as
> le droit de faire.
> Le correctif : un contrôle de **propriété** — `utilisateur courant === cible, ou bien admin`,
> sinon **403 + log**. Et je l'ai appliqué aux **3 routes** concernées : édition profil, suppression
> de compte, suppression de post. C'est exactement la différence entre un middleware d'**auth** et un
> contrôle d'**autorisation**.

---

## Slide 19 — Préparation au déploiement : Tests (§4) · 1:30

**🖥️ Sur la slide**
- **PHPUnit** — 6 classes de test (8 fichiers), ~40 cas
- Unitaire · **mock** · intégration PostgreSQL · **sécurité**
- Un test **prouve l'injection SQL inerte**

**🎤 Notes**
> J'ai une vraie suite de tests **PHPUnit** — 6 classes de test réparties sur 8 fichiers, une
> quarantaine de cas, sur plusieurs niveaux.
> Des tests **unitaires** purs sur l'échappement et le CSRF. Des tests au **mock** avec injection de
> dépendance sur le gestionnaire de session. Des tests d'**intégration** contre une vraie base
> PostgreSQL, chacun dans une **transaction annulée** à la fin pour rester isolé et rejouable. Et —
> ce qui me tient à cœur — des tests de **sécurité** : j'ai un test qui envoie `' OR '1'='1` dans la
> recherche et vérifie qu'il **ne renvoie rien** — la preuve, en vert, que mes requêtes préparées
> rendent l'injection inerte. La suite se lance en une commande, `php vendor/bin/phpunit`.
> Elle sert aussi de filet de **non-régression** : je la rejoue après chaque changement.
> Honnêteté : la **couverture reste à étendre** — c'est un axe que j'assume.

---

## Slide 20 — Préparation au déploiement : déploiement & CI (§4) · 1:30

**🖥️ Sur la slide** (sous-titre à passer de « Github Actions » à ceci)
- **Déploiement RÉEL** : `docker-compose.prod.yml` — Traefik HTTPS, Postgres fermé, service `migrate`, rollback `git` + `pg_dump`
- **CI GitHub Actions** : *plan écrit*, prochaine étape
- En local aujourd'hui : `phpcs` ✓ · `phpunit` ✓ · `docker build` ✓

**🎤 Notes**
> Deux sujets : le déploiement, qui est **réel**, et la CI, où je suis **transparent**.
> Le **déploiement** est documenté (`DEPLOYMENT.md`) et outillé : un `docker-compose.prod.yml` avec
> Traefik en **HTTPS Let's Encrypt**, une image figée pour l'app, **PostgreSQL non exposé** sur le
> réseau, et le service `migrate` qui applique les **migrations additives avant** le démarrage (le
> schéma initial, lui, est chargé une fois par Postgres au premier boot via `solage.pg.sql`). J'ai prévu le
> **rollback** — `git checkout` d'un tag + rebuild, et comme mes migrations sont additives, revenir
> en arrière ne casse rien — et une **sauvegarde `pg_dump`** avant chaque déploiement.
> La **CI**, en revanche : je **n'ai pas encore** de pipeline GitHub Actions qui tourne — et je ne
> vais pas prétendre le contraire. Ce que j'ai : les **briques prêtes** — en local, `phpcs` passe à
> 100 % (46 fichiers, 0 erreur PSR-12), `phpunit` est vert, `docker build` réussit — **et un plan
> d'implémentation détaillé** (`guide-C11-CI-devops.md`). Brancher ces étapes dans GitHub Actions,
> c'est ma **prochaine étape** : techniquement, il ne reste qu'à les automatiser.

> *Distinction à connaître : livraison continue = build/test auto à chaque push ; déploiement continu
> = mise en prod auto. Moi : déploiement **manuel mais documenté**, CI **à brancher**.*

> *Si on creuse le « 0 erreur PSR-12 » : mon `phpcs.xml` exclut **3 règles documentées et justifiées**
> (pas de namespace — autoload maison ; effets de bord sur `index.php` ; longueur de ligne sur les
> vues HTML/SVG). Tout le reste de PSR-12 passe sur 46 fichiers.*

---

## Slide 21 — Conclusion & perspectives (§4) · 1:00

**🖥️ Sur la slide**
- **Bilan** : appli multicouche sécurisée · framework maison maîtrisé · tests réels
- **Perspectives** : CI GitHub Actions · validation serveur systématique · RGAA · upload (MIME réel)
- *Merci — vos questions*

**🎤 Notes**
> En bilan : je suis parti d'un réseau social et j'en ai fait un terrain pour une **application
> multicouche sécurisée à chaque couche** — CSRF, IDOR, injection SQL, XSS, en-têtes, le tout tracé
> par un logger. J'ai écrit le framework **moi-même**, donc je le maîtrise de bout en bout, et j'ai
> une suite de tests réelle qui prouve la sécurité.
> Côté lucidité, je connais mes **angles morts** : automatiser la CI dans GitHub Actions, généraliser
> la **validation serveur**, valider les uploads par leur **type MIME réel**, ajouter
> l'anti-fixation de session, et viser l'accessibilité **RGAA**. Connaître ses angles morts, pour
> moi, c'est une force, pas une faiblesse.
> Ce qui m'a le plus appris : le cycle **veille → audit → correctif** sur l'IDOR. Merci de votre
> attention — je suis prêt pour vos questions.

---

# Annexe — sujets du doc à garder pour l'entretien (pas sur les slides)

Pour rester sur « peu de texte », ces points ne sont **pas** sur des slides dédiées, mais prépare-les
(ils tombent souvent) — ils sont déjà dans `02-ORAL-SOUTENANCE.md` :

- **Diagramme de séquence** « publier un message » couche par couche → tu peux le **dessiner au
  tableau** si on te le demande (c'est la slide 16 racontée en pas-à-pas).
- **Cas d'utilisation** (Visiteur / Utilisateur / Admin) → réponds à l'oral, appuie-toi sur les
  middlewares Auth/Admin.
- **Difficultés techniques résolues** (compétence T2) : les logs en `STDOUT` qui ne marchaient qu'en
  CLI ; les *« headers already sent »* ; le **N+1** sur le fil (résolu par `getUsersByIds`) ; et
  surtout **`strict_types` qui a révélé 3 bugs de typage PDO** (PostgreSQL renvoie les entiers en
  chaînes sous FrankenPHP → cast à la frontière des modèles).
- **Headers de sécurité détaillés** (CSP `default-src 'self'`, `X-Content-Type-Options: nosniff`,
  `Referrer-Policy`) → c'est dans `public/index.php`, à citer si on creuse la slide 12.
- **Questionnaire pro (anglais B1)** : voir partie C du doc oral.

> Tout ce qui précède est **vérifié dans le code**. Chemins clés à pouvoir citer : `src/Router.php`,
> `src/CsrfMiddleware.php`, `src/CsrfHelper.php`, `modules/controllers/PostController.php` &
> `UserController.php`, `modules/models/PostModel.php`, `src/Utils.php`, `public/scripts/index.js`,
> `solage.pg.sql`, `src/Migrations.php`, `tests/`, `DEPLOYMENT.md`, `docker-compose.prod.yml`.
