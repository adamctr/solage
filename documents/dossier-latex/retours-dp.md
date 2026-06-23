Compétences transversales. Elles sont évaluées à travers les compétences professionnelles :
— Communiquer en français et en anglais : dossier et présentation structurés, commentaires
de code et vocabulaire technique en anglais (niveau B1) ;

Vrai ?


savoir si je saurais expliquer démarche résolution pb STDOUT CLI, headers already sent, requête N+1) ;


Voir si on met ça : RGPD. L’application traite des données à caractère personnel (adresse email, mot de passe,
contenus publiés). Le mot de passe n’est jamais stocké en clair : il est haché (bcrypt). La collecte
est minimale (principe de minimisation). Des mentions légales et une information sur la finalité
du traitement doivent accompagner la mise en production.


Vérifier RGAA.

Vérifier Eco conception / Refaire minifications assets

Ca veut dire quoi : PDO plutôt qu’une extension native. Abstraction portable et, surtout, requêtes préparées qui neutralisent l’injection SQL (section 4.8). ?

Ca veut dire quoi ? La désactivation de ATTR_EMULATE_PREPARES force de vraies requêtes préparées côté serveur
PostgreSQL, renforçant la protection contre l’injection SQL.

Ne pas parler de ROADMAP_DETAILLEE.md mais juste du fait que j'utilise README comme kanban

Changer Probleme Solution.md en phrase très courte et humaine (comme : ## Migrations 
Migrations.php appelait à chaque fois les migrations -> Mauvais cycle de requetes.
Passage à un script bin migrate.)

Dire UNIQUEMENT que j'ai été seul à réaliser le projet (d'un commun accord pour des raisons personnelles avec Fabio Voliani on s'est dit que ce serait uniquement moi qui développerait sur le projet).

ENLEVER tout ça
Comptes rendus de session. À partir de l’historique Git et du journal de décisions, j’ai
reconstitué les comptes rendus de mes principales sessions de travail. Chacun consigne l’objectif
visé, le réalisé — rattaché à des commits datés — et le reste à faire identifié en fin de session,
11
Solage — Dossier de projet CDA · RNCP37873
ce dernier alimentant en retour la feuille de route. Le tableau 4.2 en donne la synthèse ; les
comptes rendus détaillés et un extrait du fichier de suivi figurent en annexe A.1.
Date Objectif de la session Reste à faire identifié
6 mai 2026 Industrialiser l’environnement : conteneurisation et migration PostgreSQL
Validation du type MIME des téléversements ; régénération d’ID de session
12 mai 2026 Auditer l’architecture MVC et corriger les
failles applicatives (XSS, IDOR)
Validation serveur systématique ; tests automatisés
13 mai 2026 Fiabiliser le jeu d’essai et la charte graphique
Procédure de sauvegarde / restauration
15 juin 2026 Durcir la sécurité (CSRF, en-têtes) et outiller la qualité (PSR-12)
Intégration continue ; couverture de tests
Table 4.2 – Synthèse des comptes rendus de session (détail en annexe A.1).

-----

La figure MCD coupe ce texte.
Une dette technique assumée. Le schéma physique conserve une table responses héritée
d’une première version, alors que le mécanisme de réponse effectivement utilisé par l’application
14
Solage — Dossier de projet CDA · RNCP37873
ROLE
id
name
UTILISATEUR
id
name
firstname
email
password
image
POST
id
content
date
image
reply_to
reply_to_parent
appartient
publie
aime
created_at
répond à
0,n 1,1
0,n 1,1
0,n 0,n
0,n
0,1
Figure 4.5 – Modèle conceptuel des données (formalisme Merise).
repose sur les colonnes reply_to / reply_to_parent de la table posts. Cette redondance est
identifiée comme une dette à résorber : c’est un point que je signale plutôt que de le masquer.

Aussi, je veux enlever ce texte en question car on a plus "responses" en table et le mcd le montre même pas.

----

Faut que j'arrive à mieux comprendre ça pour l'expliquer: 
La testabilité découle du design. Un composant n’est testable unitairement que si ses
dépendances sont substituables. SessionManager reçoit son UserModel par injection : en test on
lui passe un mock, donc login() et isAdmin() se vérifient sans base. À l’inverse, UserValidator
30
Solage — Dossier de projet CDA · RNCP37873
instancie son modèle en dur (new UserModel()) : non mockable, il est testé en intégration contre
la base réelle. Ce contraste est un choix assumé.

-----
Enlever ça Anomalies relevées et traitement. La campagne n’a pas seulement validé l’existant : elle a
mis au jour trois anomalies latentes, consignées et assorties d’un correctif identifié.
— UserModel::createUser() charge assets/emojiList.php par un include relatif au répertoire
courant : il échoue hors du contexte web. Contourné en test, correctif identifié (chemin
absolu).
— UserModel::getNameFromId() retourne $row->name sans vérifier la ligne : erreur fatale sur
identifiant inconnu. Correctif trivial ($row ? ... : null).
31
Solage — Dossier de projet CDA · RNCP37873
— Utils::sendResponse() omet la clé data lorsqu’elle est falsy (if ($data)) : comportement
révélé par le test du transport JSON, documenté.

----

Enlever ça 
Ce que cette stratégie démontre. Mon plan distingue ce qui s’automatise de ce qui se
démontre. La logique pure et la sécurité automatisable sont couvertes par 40 tests PHPUnit
verts ; le CSRF et l’IDOR, qui dépendent de l’environnement HTTP, sont prouvés par une
requête falsifiée rejouée — 403 plus une ligne de journal à chaque fois. La même suite me sert de
non-régression.

-----
C'est quoi TLS ? Savoir expliquer.

-----

Comprendre ça (ou enlever)
En contexte professionnel
je taguerais les images par SHA de commit et les pousserais sur un registry, pour un rollback par
simple repointage de tag plutôt que par reconstruction depuis Git.

---------------

Maintenant j'ai déployé le site avec dockploy qui est connecté avec webhook deployment à github actions. on a donc un docker compose dokploy. 

------

On parle trop de ça enleve les  (découplage
des couches, suppression d’une requête N+1) 

----

Pareil on parle trop de ça enleve
Constantes STDOUT / STDERR absentes en HTTP. Le logger écrivait via fwrite(STDOUT,
...) : les tests en ligne de commande passaient, mais toute requête HTTP renvoyait une
erreur 500 (« Undefined constant STDOUT »). Diagnostic : ces constantes n’existent que
dans le SAPI CLI de PHP. Correctif : utilisation des flux php://stdout / php://stderr, qui
fonctionnent dans tous les SAPI. Leçon : tester en CLI

-----

Met à jour perspectives d'évolutions avec ça : 
Factoriser/Séparés les css et js 
Loader le js dynamiquement en fonction du layout
Eslint
Validation Upload MIME réel images





