# Workflow AJAX / Validator / DynamicMessage / Session

Schéma de référence pour comprendre le cycle complet d'une requête login (et register, identique). Utilise les 4 composants que le jury va probablement questionner ensemble : **AJAX**, **UserValidator**, **DynamicMessageController**, **SessionController**.

---

## Workflow login (identique pour register)

```
┌─────────────────────────────────────────────────────────────────────┐
│  BROWSER  ▸  <form id="loginForm"> rendu par UserView::showLoginForm│
│                                                                      │
│  [user clique Submit]                                                │
│        │                                                             │
│        ▼                                                             │
│  public/scripts/index.js                                             │
│   ├─ e.preventDefault()       (empêche le submit HTML natif)         │
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
│  │  3. DynamicMessageController::showMessage(                  │     │
│  │        $result['type'], $result['message']                  │     │
│  │     )                                                       │     │
│  │     │                                                       │     │
│  │     │   ┌────────────────────────────────────────────────┐  │     │
│  │     └─▶ │ DynamicMessageController                       │  │     │
│  │         │   ├─ $html = DynamicMessageView::               │  │     │
│  │         │   │           getDivMessage(type, message)     │  │     │
│  │         │   │   returns:                                 │  │     │
│  │         │   │     <div class="success dynamicMessage">   │  │     │
│  │         │   │       <p>...</p>                           │  │     │
│  │         │   │     </div>                                 │  │     │
│  │         │   ├─ header('Content-Type: application/json')  │  │     │
│  │         │   └─ echo json_encode([                        │  │     │
│  │         │        'success'        => $type,              │  │     │
│  │         │        'divMessageHtml' => $html               │  │     │
│  │         │      ])  ◄── OUTPUT envoyé au client           │  │     │
│  │         └────────────────────────────────────────────────┘  │     │
│  │                                                             │     │
│  │  4. if ($result['ok']) :                                    │     │
│  │       $user = UserModel::getUserByEmail($email)             │     │
│  │       (new SessionController)->login($user->getId())       │     │
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
                  body: {"success":"success",
                         "divMessageHtml":"<div ...>...</div>"}
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│  BROWSER  ▸  callback fetch                                          │
│                                                                      │
│  .then(response => response.json())                                  │
│  .then(data => {                                                     │
│      if (data.success === 'success') {                               │
│          window.location.href = '/';   ◄── reload page d'accueil     │
│      }                                                               │
│      const messageContainer = document.getElementById(               │
│          'messageContainer'                                          │
│      );                                                              │
│      messageContainer.innerHTML = data.divMessageHtml;               │
│      └─ injecte le <div> de feedback dans la page                    │
│  })                                                                  │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Contraste : workflow API (post / like / delete)

L'autre famille d'AJAX du projet (création de post, like, delete) utilise un **helper différent** : `Utils::sendResponse`. Pas de HTML pré-rendu — du JSON brut. C'est important à comprendre parce que le projet a **deux conventions de réponse JSON qui coexistent**.

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
           'data'    => [...]
         ])
              │
              ▼
Browser  ▸  if (data.success) → JS construit le <div class="post"> en DOM
            (PAS de HTML pré-rendu reçu du serveur)
```

### Pourquoi DEUX patterns coexistent ?

| Cas d'usage           | Helper                          | Forme du JSON                              | Raison                                          |
|-----------------------|---------------------------------|---------------------------------------------|-------------------------------------------------|
| login / register      | `DynamicMessageController`      | `{success, divMessageHtml}`                | Le message stylé (`<div class="success">`) est rendu côté serveur pour garantir le styling cohérent. |
| API (post/like/delete)| `Utils::sendResponse`           | `{success, message, data}`                 | Le post à afficher est reconstruit côté JS à partir des données brutes — pas besoin de HTML serveur. |

---

## Responsabilités de chaque couche

| Couche                                | Rôle                                                                 |
|---------------------------------------|----------------------------------------------------------------------|
| `public/scripts/index.js`             | Intercept submit, fetch AJAX, parse JSON, inject HTML, redirect      |
| `public/index.php`                    | Bootstrap : autoload, `session_start()`, dispatch                    |
| `src/Router`                          | Match URL+method → `Controller#method`                               |
| `modules/controllers/UserController`  | Orchestrer : lire `$_POST`, appeler validator, formater réponse, ouvrir session si OK |
| `modules/validators/UserValidator`    | Décider si inputs valides — **retourne un array, AUCUN side-effect HTTP** |
| `modules/controllers/DynamicMessageController` | Sérialiser un message stylé → JSON (avec HTML pré-rendu)        |
| `modules/views/DynamicMessageView`    | Générer le `<div>` HTML du message                                   |
| `src/Utils::sendResponse`             | Sérialiser JSON brut `{success, message, data}` pour les routes API  |
| `modules/controllers/SessionController` | Démarrer + peupler `$_SESSION`                                     |

---

## Points à connaître pour la défense

1. **`UserValidator` retourne un array, n'echo'e jamais.** Avant le refactor, il appelait `DynamicMessageController::showMessage` lui-même — couplé à la sortie HTTP, donc intestable. Maintenant : validator pur, contrôleur décide.

2. **`session_start()` est appelé dans `public/index.php`, pas dans le contrôleur.** Sans ça, l'`echo` de `DynamicMessageController::showMessage` partait avant `session_start`, et le cookie `PHPSESSID` ne pouvait plus être posé → login fragile. Le bootstrap garantit le bon ordre.

3. **Deux conventions JSON coexistent.** `divMessageHtml` (HTML pré-rendu, pour les messages de feedback) vs `data` (champs bruts, pour les API). Pas unifié — à mentionner si pointé en soutenance. Refonte hors scope car les deux flux fonctionnent indépendamment.

4. **`UserValidator` n'est pas un contrôleur** — pas appelé par le router, appelé par `UserController`. Vit dans `modules/validators/` parce qu'il est domaine-spécifique (User), pas framework (`src/`). Parallèle aux dossiers `controllers/`, `models/`, `views/`.

5. **`DynamicMessageController` est encore mal nommé** — pas un contrôleur non plus, juste un sérialiseur. Renommage / fusion possible dans une passe ultérieure (peut être collapsé dans `UserController` puisqu'il n'a qu'un appelant).
