# Workflow AJAX / Validator / Session

Schéma de référence pour comprendre le cycle complet d'une requête AJAX (login, register, post, like, delete) — toutes suivent le même pattern depuis l'unification de la réponse JSON.

---

## Convention de réponse JSON (unique pour toute l'app)

```json
{
  "success": true | false,
  "message": "Texte affiché à l'utilisateur",
  "data": { ... }            // optionnel, présent uniquement si le contrôleur passe une payload
}
```

Helper unique côté serveur : **`Utils::sendResponse($success, $message, $data = null)`** (`src/Utils.php`).
Côté client, chaque flow lit `data.success`, `data.message`, et reconstruit le DOM nécessaire (message stylé, post inséré, etc.) — pas de HTML pré-rendu envoyé par le serveur.

---

## Workflow login (identique pour register)

```
┌─────────────────────────────────────────────────────────────────────┐
│  BROWSER  ▸  <form id="loginForm"> rendu par UserView::showLoginForm│
│                                                                      │
│  [user clique Submit]                                                │
│        │                                                             │
│        ▼                                                             │
│  public/scripts/dynamicMessages.js                                   │
│   ├─ e.preventDefault()      (empêche le submit HTML natif)          │
│   └─ fetch('/login', {                                               │
│        method: 'POST',                                               │
│        headers: 'application/x-www-form-urlencoded',                 │
│        body: `email=...&password=...`                                │
│      })                                                              │
└──────────────────────────────┬───────────────────────────────────────┘
                               │
                  HTTP POST /login   (AJAX, pas un submit classique)
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│  SERVER (PHP)                                                        │
│                                                                      │
│  public/index.php                                                    │
│   ├─ require autoload                                                │
│   ├─ session_start()       ◄── démarre AVANT tout output             │
│   │                            (garantit que PHPSESSID peut être posé│
│   │                             plus tard si on appelle login())     │
│   └─ Router::match()                                                 │
│           │                                                          │
│           │ routes/api.php : POST /login → UserController#login      │
│           ▼                                                          │
│  ┌─────────────────────────────────────────────────────────────┐     │
│  │  UserController::login()                                    │     │
│  │                                                             │     │
│  │  1. $email    = trim($_POST['email'])                       │     │
│  │     $password = trim($_POST['password'])                    │     │
│  │                                                             │     │
│  │  2. $result = UserValidator::login($email, $password)       │     │
│  │     │                                                       │     │
│  │     │   ┌──────────────────────────────────────────┐        │     │
│  │     └─▶ │ UserValidator (modules/validators/)      │        │     │
│  │         │   - check empty                          │        │     │
│  │         │   - UserModel::getUserByEmail (DB)       │        │     │
│  │         │   - password_verify()                    │        │     │
│  │         │   return [                               │        │     │
│  │         │     'ok'      => bool,                   │        │     │
│  │         │     'type'    => 'success' | 'error',    │        │     │
│  │         │     'message' => '...'                   │        │     │
│  │         │   ]                                      │        │     │
│  │         │   ► PURE : pas d'echo, pas de header     │        │     │
│  │         └──────────────────────────────────────────┘        │     │
│  │                                                             │     │
│  │  3. header('Content-Type: application/json')                │     │
│  │     Utils::sendResponse(                                    │     │
│  │        $result['ok'], $result['message']                    │     │
│  │     )                                                       │     │
│  │     │                                                       │     │
│  │     └─▶ echo json_encode([                                  │     │
│  │           'success' => bool,                                │     │
│  │           'message' => string                               │     │
│  │         ])                          ◄── OUTPUT au client    │     │
│  │                                                             │     │
│  │  4. if ($result['ok']) :                                    │     │
│  │       $user = UserModel::getUserByEmail($email)             │     │
│  │       (new SessionController)->login($user->getId())        │     │
│  │            └─ $_SESSION['user_id'] = ...                    │     │
│  │            └─ $_SESSION['name']    = ...                    │     │
│  │            └─ $_SESSION['image']   = ...                    │     │
│  │            (PHPSESSID a déjà été posé via session_start     │     │
│  │             au bootstrap → cookie OK même après l'echo)     │     │
│  └─────────────────────────────────────────────────────────────┘     │
└──────────────────────────────┬───────────────────────────────────────┘
                               │
                  HTTP 200
                  Set-Cookie: PHPSESSID=...
                  body: {"success": true, "message": "Vous vous êtes bien connecté !"}
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│  BROWSER  ▸  callback fetch                                          │
│                                                                      │
│  .then(response => response.json())                                  │
│  .then(data => {                                                     │
│      if (data.success) {                                             │
│          window.location.href = '/';   ◄── reload page d'accueil     │
│      }                                                               │
│      const type = data.success ? 'success' : 'error';                │
│      messageContainer.innerHTML =                                    │
│          `<div class="${type} dynamicMessage">                       │
│             <p>${escapeHtml(data.message)}</p>                       │
│           </div>`;                                                   │
│      └─ le <div> est construit côté client à partir du message       │
│         (escapeHtml() protège contre XSS si le message contient      │
│          du contenu user-influencé)                                  │
│  })                                                                  │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Workflow API (post / like / delete) — même pattern

```
Browser  ▸  fetch('/api/post', { POST, FormData })
              │
              ▼
PostController::create()
 ├─ valide $_POST['data'] + $_FILES['image']
 ├─ PostModel->createPost()
 └─ Utils::sendResponse(true, "Succès", ['id'=>.., 'content'=>..])
      └─ echo json_encode([
           'success' => true,
           'message' => 'Succès',
           'data'    => [ ... ]
         ])
              │
              ▼
Browser  ▸  if (data.success) { construit <div class="post"> en DOM via innerHTML }
            (utilise escapeHtml() pour chaque champ user-controlled)
```

**Seule différence** avec login/register : la présence d'un `data` (la payload du nouveau post / like / etc.) que le client utilise pour construire le markup. Le wrapper JSON est strictement le même.

---

## Responsabilités de chaque couche

| Couche                                | Rôle                                                                 |
|---------------------------------------|----------------------------------------------------------------------|
| `public/scripts/dynamicMessages.js`   | Login/register : intercept submit, fetch, build message `<div>`, redirect |
| `public/scripts/index.js`             | API actions : create post / like / delete, fetch, build post `<div>` |
| `public/index.php`                    | Bootstrap : autoload, `session_start()`, dispatch                    |
| `src/Router`                          | Match URL+method → `Controller#method`                               |
| `modules/controllers/UserController`  | Orchestre login/register : `$_POST` → validator → `sendResponse` → session si OK |
| `modules/validators/UserValidator`    | Décide si inputs valides — **retourne un array, AUCUN side-effect HTTP** |
| `modules/controllers/PostController`  | Idem pour create/delete post (validation + ownership inline)         |
| `modules/controllers/LikeController`  | Idem pour like                                                       |
| `src/Utils::sendResponse`             | Sérialise la réponse JSON unifiée `{success, message, data?}`        |
| `modules/controllers/SessionController` | Démarre + peuple `$_SESSION`                                       |

---

## Points à connaître pour la défense

1. **`UserValidator` retourne un array, n'echo'e jamais.** Avant le refactor, il appelait un sérialiseur HTTP lui-même — couplé à la sortie, donc intestable. Maintenant : validator pur, contrôleur décide.

2. **`session_start()` est appelé dans `public/index.php`, pas dans le contrôleur.** Sans ça, l'`echo` de la réponse JSON partait avant `session_start`, et le cookie `PHPSESSID` ne pouvait plus être posé → login fragile. Le bootstrap garantit le bon ordre.

3. **Une seule convention JSON dans l'app.** `{success: bool, message: string, data?: object}` partout. Helper unique `Utils::sendResponse`. Le précédent doublon (`DynamicMessageController` qui renvoyait du HTML pré-rendu) a été supprimé : il imposait deux formats pour une seule app et coûtait plus en complexité qu'il ne gagnait en cohérence visuelle.

4. **Le markup des feedback messages est construit côté client.** Le serveur renvoie le texte du message, le client wrap dans un `<div class="success|error dynamicMessage">`. La protection XSS est assurée par `escapeHtml()` JS, miroir du `Utils::e()` PHP.

5. **`UserValidator` n'est pas un contrôleur** — pas appelé par le router, appelé par `UserController`. Vit dans `modules/validators/` parce qu'il est domaine-spécifique (User), pas framework (`src/`). Parallèle aux dossiers `controllers/`, `models/`, `views/`.
