# Trucs à savoir dire aux jurys — défense orale (Solage)

> Argumentaire à **dire à l'oral / en entretien technique** — à NE PAS écrire dans le dossier
> remis au jury. Chaque bloc = le point du dossier concerné + la ou les phrases à placer.
> Tout est à la première personne : c'est moi qui défends mes choix.

---

## Liste des compétences (chapitre 1)

- « Ma table de compétences est une promesse de couverture : chaque ligne renvoie à un chapitre
  précis où la compétence est démontrée par un livrable concret — un schéma, un extrait de code,
  un test ou une procédure — et non par une simple affirmation. »

**À savoir mentionner si on me le demande :** pour le titre complet, le projet doit
obligatoirement démontrer **C2, C3, C4, C5, C6, C7, C8 et C9**. Les compétences **C1, C10 et
C11** sont surtout vérifiées à l'entretien et au questionnaire ; Solage les couvre quand même
grâce à la chaîne Docker.

---

## Expression des besoins (chapitre 2)

- **Périmètre et limites** : « J'ai préféré un périmètre étroit, entièrement maîtrisé et
  sécurisé, à un périmètre large traité à moitié. Le sujet valorise la sécurité comme
  préoccupation constante : j'ai donc privilégié la qualité et la sécurité du cœur fonctionnel
  sur le nombre de fonctionnalités. »
- **Objectif vs fonctionnalité** : « Un objectif est *mesurable* (permettre la publication d'un
  message) ; la fonctionnalité en est la traduction technique. Et je n'oublie pas les *limites* —
  c'est ce qui montre que j'ai cadré le projet. »
- **Maquettes (inspiration d'un produit existant, type X/Twitter)** : « J'ai réutilisé
  volontairement des conventions d'interface éprouvées du microblogging plutôt que d'inventer une
  UI nouvelle — c'est un choix d'apprenabilité et de simplicité, pas un manque de travail. Ma
  conception, c'est l'adaptation : périmètre réduit, charte minimaliste propre à Solage,
  enchaînement de mes écrans. » → Si on me demande si j'ai maquetté *avant* de coder : « J'ai
  travaillé avec un produit existant comme référence vivante ; j'ai ensuite formalisé les
  maquettes de mes propres écrans pour le dossier. »
- **Outil de maquettage (Penpot, open source)** : « J'utilise Figma au quotidien, mais j'ai voulu
  éprouver Penpot, l'alternative libre — même logique que mon MVC maison : comprendre l'outil
  plutôt que le subir. » → relie à la compétence transversale *apprendre en continu*.
- **Mon passé d'intégrateur / web designer** : « Mon expérience d'intégrateur et web designer en
  alternance m'a donné le réflexe de penser hiérarchie visuelle, contraste et responsive dès la
  maquette — la conception d'interface n'est pas une case que je coche, c'est un métier que j'ai
  pratiqué. »

---

## Environnement technique (chapitre 3)

- **Choix de la pile** : « Chaque brique de ma pile répond à un compromis que je sais défendre.
  J'ai choisi le MVC maison pour la maîtrise pédagogique, en acceptant de réécrire des briques
  qu'un framework offrirait — c'est précisément ce qui me permet d'expliquer chaque ligne de mon
  code d'infrastructure. »
- **Docker** : « Les conteneurs me donnent un environnement de développement conforme à la
  production. La même image sert au dev et à la prod ; seules la configuration réseau et la
  terminaison TLS diffèrent. »

---

## Architecture (chapitre 4 — architecture)

- **Multicouche + sécurité** : « Chaque couche a une responsabilité *et* un rôle de sécurité : le
  SQL ne vit que dans les modèles, l'échappement que dans les vues, l'autorisation dans les
  contrôleurs et les middlewares. La sécurité n'est pas un module, c'est une propriété transverse
  à l'architecture. »
- **DICP** (savoir réciter) : Disponibilité, Intégrité, Confidentialité, Preuve.
- **Gestion de projet** : « J'ai réalisé l'essentiel du projet — un binôme a contribué au tout
  début, en 2024. J'ai planifié avec ma feuille de route et suivi mes tâches au fil des commits
  Git (chaque tâche = un commit daté), consolidés dans un fichier `SUIVI.md`. J'ai aussi fixé mes
  règles de qualité et tenu un journal de décisions. Le projet s'est fait en deux temps :
  construction en 2024, puis reprise sécurité / industrialisation en 2026. »

---

## Conception : base de données et UML (chapitre 4 — conception)

- **Base de données** : « Le schéma part des besoins et respecte les règles du relationnel. La
  contrainte "`user` réservé en PostgreSQL" a dicté ma règle de nommage `user_id`. Les évolutions
  passent par des migrations idempotentes, vérifiées contre `information_schema`, pas par des
  `ALTER` manuels non tracés. »
- **UML** : « Le diagramme de cas d'utilisation montre *qui peut quoi* ; le diagramme de séquence
  montre *comment la requête circule* couche par couche, middlewares de sécurité compris. J'ai
  choisi la publication d'un message parce que c'est le cas qui traverse le plus de couches. »

---

## Développement (chapitre 4 — développement)

- **Interfaces** : « La vue n'affiche que des données préparées par le contrôleur ; depuis mon
  audit d'architecture, plus aucune vue n'interroge la base. Tout contenu utilisateur est échappé
  à l'affichage — côté serveur par `Utils::e()`, côté client par `escapeHtml()`, car le
  JavaScript reconstruit du DOM. »
- **Composants métier** : « Mes composants métier valident les entrées et vérifient
  l'autorisation *sur la ressource*, pas seulement l'authentification. L'IDOR sur la suppression
  est bloqué par un contrôle "propriétaire ou administrateur", journalisé pour audit. Le
  validateur, lui, est pur : il décide, le contrôleur expose. »
- **Accès aux données** : « Toutes mes requêtes sont préparées : la structure SQL est figée
  d'abord, les valeurs sont liées ensuite comme données — jamais interprétées comme du SQL. J'ai
  aussi corrigé une requête N+1, 21 requêtes ramenées à 2 sur le fil, en préchargeant les auteurs
  avec une seule requête `IN`, en prenant soin du cas `IN ()` vide. »
- **Socle framework** : « J'ai écrit le socle moi-même : routeur, middlewares, logger PSR-3,
  utilitaires. Le CSRF est armé par le routeur sur tout POST — je ne peux pas oublier un
  *endpoint*. Le logger suit PSR-3 : je pourrais le remplacer par Monolog sans toucher au code
  appelant. »

---

## Sécurité (chapitre 4 — sécurité)

- **Posture générale** : « Je traite la sécurité couche par couche et faille par faille, en
  référence à l'OWASP Top 10. Là où c'est incomplet — validation serveur, anti-fixation de
  session, contrôle MIME des téléversements — je le sais et je l'ai planifié. Je préfère nommer
  mes angles morts que prétendre être 100 % sécurisé. »
- **Défense en profondeur (CSRF)** : « Le cookie de session est déjà en `SameSite=Lax`
  (protection navigateur) ; le jeton de synchronisation ajoute une protection *applicative*,
  indépendante du navigateur. Je garde les deux. »

---

## Tests, jeu d'essai et veille (chapitre 4 — tests)

- **Jeu d'essai** : « J'ai choisi *Publier un message* parce que c'est la fonctionnalité qui
  traverse toute l'architecture et concentre toutes les protections : validation, téléversement,
  anti-XSS, autorisation, CSRF. Mon jeu d'essai couvre le cas nominal *et* les cas limites et
  malveillants. »
- **Veille (le déclic IDOR)** : « Ma veille n'est pas décorative : un article sur les fuites de
  données dans le secteur de la santé, revenant sur la faille IDOR de l'URSSAF, m'a fait auditer
  mes propres routes et y trouver la même classe de faille. Veille → audit → correctif →
  documentation : c'est un cycle que je tiens, et il démontre à la fois la résolution de problème
  et l'apprentissage continu. »
  - **Si on creuse** : « L'IDOR, c'est l'OWASP A01 — *Broken Access Control* — n°1 du Top 10
    2021, la faille la plus répandue du web. La leçon : **authentification ≠ autorisation**.
    Être loggé ne suffit jamais ; je revérifie les droits sur *la ressource demandée*, pas
    seulement l'identité. » (les trois routes concernées : `/edituser/{id}`,
    `/api/users/delete`, `/api/posts/delete`).

---

## Bilan (chapitre 5)

- « Mes difficultés les plus instructives sont des bugs *silencieux* : le code marchait, mais la
  sémantique était fausse. C'est l'audit — pas le compilateur — qui les a attrapés. Et mes axes
  d'amélioration, je les nomme : un dossier lucide vaut mieux qu'un dossier qui se prétend
  parfait. »

---

> Pour l'argumentaire complet (déroulé du diaporama slide par slide, banque de questions de
> l'entretien technique, préparation du questionnaire anglais B1), voir
> `examen-cda/02-ORAL-SOUTENANCE.md`.
