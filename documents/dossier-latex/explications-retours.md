# Explications simples — préparation orale

Pour chaque point : une **image/métaphore**, l'**explication courte**, et la **phrase jury**
(ce que tu réponds en une phrase si on te pose la question).

---

## 1. Les trois bugs résolus (démarche de résolution de problème)

### a) `STDOUT` n'existe pas en HTTP

🎯 **Image.** `STDOUT` (la « sortie standard ») c'est comme une **porte de service** qui n'existe
que quand PHP tourne *en ligne de commande* (le mode « CLI », comme un terminal). Quand PHP tourne
*derrière un serveur web* (mode HTTP), cette porte n'a jamais été construite. Mon logger essayait
de sortir par une porte absente → mur → erreur 500.

**Explication.** PHP a plusieurs « modes d'exécution » (appelés *SAPI*). En CLI, les constantes
`STDOUT` / `STDERR` existent. En HTTP (FrankenPHP), non. La solution : utiliser les **chemins de
flux** `php://stdout` / `php://stderr`, qui, eux, marchent dans **tous** les modes.

> **Phrase jury.** « `STDOUT` est une constante propre au mode ligne de commande de PHP ; en HTTP
> elle n'existe pas. J'ai remplacé `fwrite(STDOUT, …)` par une écriture sur `php://stdout`, qui
> fonctionne quel que soit le mode d'exécution. »

### b) *Headers already sent* (les en-têtes déjà envoyés)

🎯 **Image.** Une réponse HTTP, c'est une **lettre** : d'abord l'**enveloppe** (les *en-têtes*,
dont le cookie de session), ensuite le **contenu** (le corps). Une fois que tu as commencé à écrire
le contenu, **tu ne peux plus revenir coller un timbre sur l'enveloppe**. Mon code envoyait du JSON
(le contenu) *avant* de démarrer la session (qui veut poser le cookie sur l'enveloppe) → trop tard.

**Explication.** `session_start()` pose un cookie via un en-tête HTTP. Tout en-tête doit partir
**avant** le moindre caractère de corps. En démarrant la session tout en haut (`public/index.php`),
avant toute sortie possible, le cookie part toujours à temps.

> **Phrase jury.** « Les en-têtes HTTP doivent précéder le corps de la réponse. Je démarre donc la
> session dans le point d'entrée, avant qu'un contrôleur puisse écrire quoi que ce soit. »

### c) La requête N+1

🎯 **Image.** Tu fais les courses pour 20 plats. La requête N+1, c'est **retourner au magasin 20
fois**, une fois par plat. La correction, c'est faire **une seule liste** et tout acheter d'un coup.

**Explication.** Afficher 20 messages + leur auteur déclenchait 1 requête pour les messages, puis
1 requête **par** message pour aller chercher l'auteur = 21 requêtes. `getUsersByIds()` récupère
**tous** les auteurs en **une** requête `WHERE id IN (…)` → 2 requêtes au total.
Détail PostgreSQL : `IN ()` *vide* est une erreur SQL, donc on court-circuite le cas « aucun id ».

> **Phrase jury.** « J'avais une requête par message pour charger les auteurs. J'ai préchargé tous
> les auteurs en une seule requête `IN`, passant de 21 requêtes à 2 sur le fil. »

---

## 2. PDO « plutôt qu'une extension native »

🎯 **Image.** PDO est une **prise universelle** (comme un adaptateur de voyage) : le même code parle
à PostgreSQL, MySQL, SQLite… Une « extension native » (`pgsql`, `mysqli`) est une **prise propre à
un seul pays** : tu rebranches tout si tu changes de base.

**Explication.** Deux façons de parler à une base en PHP :
- **extension native** (ex. `mysqli`, `pg_*`) : fonctions spécifiques à **un** SGBD ;
- **PDO** (*PHP Data Objects*) : une **interface commune** à tous les SGBD.

J'ai choisi PDO pour 2 raisons : (1) **portable** — passer de MariaDB à PostgreSQL n'a pas exigé de
réécrire la façon d'exécuter les requêtes ; (2) surtout, PDO offre des **requêtes préparées**
propres (valeurs envoyées séparément de la requête), ce qui rend l'**injection SQL** structurellement
impossible.

> **Phrase jury.** « PDO est une couche d'accès commune à plusieurs bases : elle m'a donné la
> portabilité lors de la migration MariaDB → PostgreSQL, et surtout des requêtes préparées qui
> neutralisent l'injection SQL. »

---

## 3. `ATTR_EMULATE_PREPARES => false`

🎯 **Image.** Une requête préparée, c'est un **formulaire à trous**. Deux façons de le remplir :
- **émulation** (réglage par défaut) : c'est **PHP** qui remplit les trous *avant* d'envoyer, puis
  envoie une phrase déjà complète. Le serveur reçoit du texte « tout fait » → il **fait confiance**
  à PHP pour avoir bien échappé.
- **vraies requêtes préparées** : PHP envoie **le formulaire à trous d'un côté** et **les valeurs
  de l'autre**. C'est **PostgreSQL lui-même** qui assemble. Données et instructions ne se mélangent
  jamais.

**Explication.** Par défaut, PDO *émule* les requêtes préparées (il bricole la requête finale côté
PHP). En mettant `ATTR_EMULATE_PREPARES => false`, je force de **vraies** requêtes préparées côté
serveur : la requête (structure) et les valeurs (données) voyagent **séparément** jusqu'à
PostgreSQL. C'est la garantie la plus forte contre l'injection SQL.

> **Phrase jury.** « Je désactive l'émulation pour que ce soit PostgreSQL, et non PHP, qui sépare la
> requête de ses paramètres : structure et données ne se mélangent jamais, l'injection devient
> impossible. »

---

## 4. « La testabilité découle du design » (injection de dépendance)

🎯 **Image.** Une lampe **avec prise** (tu branches l'ampoule que tu veux : une vraie, ou une fausse
pour tester) vs une lampe **avec l'ampoule soudée** (tu testes forcément avec la vraie).

**Explication.** Pour tester une classe **sans base de données**, il faut pouvoir lui donner une
**fausse** dépendance (un *mock*). C'est possible seulement si la dépendance est **branchée de
l'extérieur** :

- `SessionManager` reçoit son `UserModel` **par son constructeur** (`new SessionManager($userModel)`).
  En test, je lui passe un faux `UserModel` → je vérifie `login()` et `isAdmin()` **sans base**.
  C'est l'**injection de dépendance**.
- `UserValidator` fait `new UserModel()` **en dur** à l'intérieur. Impossible de le remplacer par un
  faux → je suis obligé de le tester **contre la vraie base** (test d'intégration).

Ce n'est pas un défaut caché : c'est un **contraste assumé** qui montre que je comprends *pourquoi*
l'un est testable unitairement et l'autre non.

> **Phrase jury.** « Une classe n'est testable sans base que si on peut lui injecter une fausse
> dépendance. `SessionManager` reçoit son modèle par le constructeur, donc je le teste avec un mock ;
> `UserValidator` instancie le sien en dur, donc je le teste en intégration. C'est un choix que
> j'assume et que je sais expliquer. »

---

## 5. TLS (le « S » de HTTPS)

🎯 **Image.** TLS, c'est l'**enveloppe scellée et opaque** dans laquelle voyage ta lettre. Sans
elle (HTTP simple), n'importe quel facteur sur le trajet peut **lire** (mot de passe en clair !) ou
**modifier** le courrier. Avec TLS (HTTPS), le contenu est **chiffré** et **personne ne peut le
falsifier** en route.

**Explication.** TLS (*Transport Layer Security*, l'évolution de SSL) chiffre la communication entre
le navigateur et le serveur. Il apporte deux choses : **confidentialité** (on ne peut pas lire) et
**intégrité** (on ne peut pas modifier sans que ça se voie). Le **cadenas** dans la barre d'adresse
= TLS actif. Dans Solage, c'est **Traefik** (via Dokploy) qui « termine le TLS » : il déchiffre à
l'entrée et obtient le certificat gratuitement via **Let's Encrypt**. L'en-tête **HSTS** dit en plus
au navigateur « ne reviens **jamais** en HTTP non chiffré ».

> **Phrase jury.** « TLS chiffre l'échange navigateur ↔ serveur : il garantit la confidentialité et
> l'intégrité des données en transit. Dans Solage, Traefik termine le TLS avec un certificat
> Let's Encrypt, et HSTS force le navigateur à toujours utiliser HTTPS. »

---

## 6. *Registry* d'images et tag par SHA de commit (la « limite assumée » du déploiement)

🎯 **Image.** Une **image Docker**, c'est un **plat cuisiné sous vide** (l'appli prête à servir).
Aujourd'hui, à chaque déploiement, je **recuisine le plat sur place** à partir de la recette (le
code Git). Un *registry*, c'est un **congélateur d'usine** : je cuisine **une fois**, j'étiquette la
barquette, je la range. Pour revenir en arrière, je ressors **l'ancienne barquette** au lieu de tout
recuisiner.

**Explication.**
- Un ***registry*** (Docker Hub, GitHub Container Registry…) est un **entrepôt d'images** prêtes à
  télécharger.
- **Taguer par SHA de commit** = donner à chaque image le **numéro exact du commit** qui l'a produite
  (`solage:a1b2c3d`). Chaque image est ainsi tracée à une version précise du code.
- **Rollback par repointage de tag** = pour revenir à hier, je dis juste « sers l'image
  `a1b2c3d` » au lieu de **reconstruire** depuis Git. Plus rapide, plus sûr (exactement la même
  image qu'avant, au bit près).

**Ma situation.** Dokploy **reconstruit l'image sur le VPS** à partir de Git à chaque déploiement.
C'est simple et ça marche pour ce projet ; la limite est qu'un retour arrière demande une
reconstruction au lieu d'un simple repointage. C'est un **arbitrage assumé**, pas un oubli.

> **Phrase jury.** « Je construis l'image sur le serveur à partir de Git. En entreprise, je
> publierais l'image taguée par SHA de commit sur un registry, ce qui permet un retour arrière
> instantané en repointant un tag, sans reconstruire. »

---

## 7. Points que j'ai corrigés dans le dossier (et pourquoi)

| Ton retour | Ce que j'ai fait | Pourquoi |
|---|---|---|
| « commentaires de code en anglais : **vrai ?** » | **Corrigé** : non, tes commentaires sont en **français**. J'ai remplacé par une affirmation vraie (lecture de doc anglaise, **nommage du code en anglais**, messages de commit). | Le jury peut ouvrir ton code et vérifier. Une affirmation fausse est un piège ; le nommage anglais (`getUserByEmail`, `isLoggedIn`) suffit pour le B1. |
| RGPD : « on le met ? » | **Gardé.** | C'est un attendu du référentiel et c'est défendable (bcrypt, minimisation). Le garder rapporte des points. |
| « vérifier RGAA » | **Gardé** (claim « socle d'accessibilité »). Vérifié : 24 attributs `alt`/`aria`/`label` dans 7 vues. | Honnête : tu as les bases, pas un audit complet — d'où « socle » et RGAA en perspective. |
| Table `responses` | **Supprimée du dossier** (paragraphe « dette technique » + script SQL annexe + extrait `likes`). | La table **n'existe plus** dans `solage.pg.sql`. Le dossier affichait un schéma faux : un jury l'aurait vu. |
| « binôme » | **Supprimé** : tu as réalisé le projet **seul**. | Conforme à ta consigne. ⚠️ voir action n°2 ci-dessous. |
| ROADMAP / Probleme-Solution / comptes rendus | **README = Kanban**, journal en phrases courtes, paragraphe + tableau « comptes rendus » **retirés**. | Conforme à tes consignes ; allège la partie gestion de projet. |
| STDOUT / N+1 / découplage « on en parle trop » | **Allégé** dans le bilan (gardé une seule fois, là où c'est le plus fort). | Évite la répétition que tu as repérée. |
| Anomalies + « ce que la stratégie démontre » | **Retirés.** | Conforme à ta consigne. |
| Déploiement | **Réécrit** : Dokploy + **déploiement continu** réel (webhook depuis GitHub Actions sur `main`). | Reflète la réalité actuelle ; transforme un « CD pas encore fait » en **CD opérationnel** (gros plus pour la C11). |
| Perspectives | **Mises à jour** : CSS/JS séparés, JS conditionnel, ESLint, MIME upload réel. Retiré « tests + CI » (faits) et « dette responses » (résolue). | Conforme à ta consigne + cohérence (on ne liste pas en « à faire » ce qui est fait). |

---

## 8. Comment marche la minification (et ce qui a été corrigé)

🎯 **Image.** Minifier, c'est **mettre tes vêtements sous vide** avant un voyage : exactement les
mêmes habits, mais le sac est plus petit. Le navigateur télécharge moins d'octets → page plus
rapide et moins de données transférées (éco-conception).

**Concrètement.** Un outil (`matthiasmullie/minify`) lit `style.css` et `index.js` et en produit une
version **sans espaces, sans retours à la ligne, sans commentaires**, avec des noms internes
raccourcis. Le code fait exactement la même chose ; il est juste **illisible et compact**.

**Quand ça se passe.** *(c'est le point clé corrigé)* La minification est faite **une seule fois,
au moment où on construit l'image Docker** (`bin/minify.php`, lancé par le `Dockerfile`), pas à
chaque visite. Résultat : `public/assets/minified/style.min.css` et `index.min.js` sont **déjà
prêts dans l'image**.

**Quel fichier est servi.** `src/Config.php` choisit selon `APP_ENV` :
- **développement** → on sert les sources `style.css` / `index.js` (lisibles, rechargement à chaud) ;
- **production** → on sert les `.min` (compacts).

> **Phrase jury.** « En production je sers des CSS/JS minifiés, générés une seule fois au build de
> l'image. Le navigateur télécharge ~35 % d'octets en moins, sans toucher au code source que je
> garde lisible en développement. »

### Ce qui était cassé et que j'ai réparé
- `MinificationController` utilisait `__DIR__ . '/../../public/…'` : un `..` de trop, qui pointait
  **au-dessus du projet** (`_Projets/public`, hors du dépôt). Corrigé en `/../public/…`.
- `APP_ENV` était figé sur `'development'` en dur → corrigé : lu depuis l'environnement
  (`getenv('APP_ENV')`), `production` est déclaré dans les compose de prod/Dokploy.
- La minification tournait **à chaque requête** (lent) → déplacée au **build** (`bin/minify.php`
  dans le `Dockerfile`), supprimée de `public/index.php`.

**Vérifié :** CSS 25,8 ko → 16,7 ko (−35 %), JS 15,6 ko → 10,0 ko (−36 %). PHPCS + PHPStan verts.
L'affirmation « minification des assets en production » du dossier est désormais **vraie**.

### Solo (fait)
`SUIVI.md` ne mentionne plus de binôme ; la réf. à `ROADMAP_DETAILLEE.md` y est remplacée par le
Kanban `README.md`. Dossier et dépôt disent maintenant la même chose : **projet réalisé seul**.

### Éco-conception — tes autres arguments (rien à faire)
Pagination `LIMIT 20` (vérifiée dans `PostModel`) et suppression du N+1 : de vrais leviers, garde-les.
</content>
</invoke>
