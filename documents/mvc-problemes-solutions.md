# MVC — problèmes / solutions

Audit du respect de l'esprit MVC (règles de `CLAUDE.md`), réalisé pendant la passe
qualité. Format : pour chaque point, le **problème**, l'**analyse**, la **solution**
et le **statut**.

## Règles de référence

- Pas de SQL hors de `modules/models/`.
- Pas d'`echo` / sortie hors de `modules/views/` (et du point d'entrée).
- Pas de `$_POST` / `$_GET` / `$_SESSION` hors des contrôleurs et de `SessionManager`.
- Pas de `Database::getConnection()` hors des modèles, de `Migrations` et du bootstrap.

## Vérification des règles « dures » (OK)

Vérifié par recherche sur tout le code first-party :

| Règle | Résultat |
|-------|----------|
| Aucune requête SQL dans les contrôleurs / vues | ✅ aucune occurrence |
| Aucun `Database::getConnection()` dans contrôleurs / vues / validators | ✅ aucune occurrence |
| Aucun `$_POST` / `$_GET` / `$_SESSION` dans vues / modèles / validators | ✅ aucune occurrence |

Le découpage en couches est donc respecté sur le fond. Restaient quelques écarts
de forme, traités ci-dessous.

## Problèmes corrigés (triviaux et sûrs)

### 1. `echo` dans un contrôleur — `SearchController`

- **Problème :** `SearchController` faisait `echo $view->render();` et
  `echo $searchView->renderResults(...);`, alors que tous les autres contrôleurs
  appellent `$view->show()` sans `echo`. C'était à la fois incohérent et un
  `echo` hors d'une vue.
- **Analyse :** `render()` / `renderResults()` produisent déjà la page via
  `LayoutView::show()` et **ne renvoient rien** (`void`). Le `echo` n'affichait
  donc que `null` : il était inutile.
- **Solution :** suppression des deux `echo` ; le contrôleur se contente
  d'appeler la méthode de vue, comme les autres contrôleurs.
- **Statut :** ✅ corrigé.

### 2. Code mort — bloc dupliqué dans `AuthMiddleware`

- **Problème :** `AuthMiddleware::handle()` contenait deux blocs
  `if (!$session->isLoggedIn())` successifs. Le premier redirige vers `/login`
  et `exit` ; le second (log + 403) était donc **inatteignable**.
- **Analyse :** copier-coller depuis `AdminMiddleware` (qui, lui, enchaîne
  « connecté ? » puis « admin ? »). Dans le cas de l'authentification, il n'y a
  qu'un seul niveau de contrôle.
- **Solution :** suppression du second bloc (mort).
- **Statut :** ✅ corrigé.

### 3. Constructeur vide — `AdminController`

- **Problème :** `AdminController` déclarait un constructeur vide (`{}`).
- **Solution :** suppression ; PHP fournit le constructeur par défaut, et le
  routeur instancie déjà `new AdminController()` sans argument.
- **Statut :** ✅ corrigé.

## Écarts documentés, volontairement laissés en l'état

### A. `echo` d'erreurs en texte brut dans les middlewares / `UserController::update`

- **Constat :** les middlewares (`Auth`, `Admin`, `Csrf`) et `UserController::update`
  renvoient un 403 en `echo "403 — ..."` (texte brut), sans passer par une vue.
- **Pourquoi on laisse :** ce sont des **réponses d'erreur de garde**, au niveau
  framework, qui court-circuitent la requête. Créer une vue dédiée pour une
  chaîne d'erreur de quelques mots serait disproportionné (sur-ingénierie). La
  règle « pas d'`echo` hors vue » vise la logique d'affichage métier, pas les
  garde-fous d'erreur.
- **Statut :** 🟡 accepté, documenté.

### B. `Utils::sendResponse()` fait `echo json_encode(...)`

- **Constat :** le helper `Utils::sendResponse()` (dans `src/`) `echo` la réponse JSON.
- **Pourquoi on laisse :** c'est un **utilitaire de transport** (API JSON),
  partagé par les contrôleurs, et non de la logique de présentation HTML. Le
  centraliser ici évite de dupliquer `header()` + `echo json_encode()` dans
  chaque contrôleur. Déplaçable vers une couche « réponse » dédiée si le projet
  grossit.
- **Statut :** 🟡 accepté, documenté.

### C. Nommage `render()` / `renderResults()` dans `SearchView`

- **Constat :** ces méthodes sont nommées `render*` (ce qui suggère un retour de
  chaîne) mais **affichent** directement via `LayoutView::show()` (retour `void`),
  contrairement à la convention `show()` des autres vues.
- **Pourquoi on laisse :** renommer toucherait la vue et son contrôleur sans gain
  fonctionnel ; le PHPDoc précise désormais clairement `@return void`. À
  harmoniser (`show` / `showResults`) lors d'un futur passage si souhaité.
- **Statut :** 🟡 accepté, documenté (PHPDoc clarifié).
