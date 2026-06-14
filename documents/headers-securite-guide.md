# Guide — Ajouter les headers de sécurité

Guide pas-à-pas pour implémenter soi-même les en-têtes de sécurité dans Solage.
Ordonné **du plus sûr au plus sensible**. Chaque étape : où écrire, quelle API, le
piège dans le code, comment vérifier, et la « réponse jury » d'une phrase.

## Décision d'architecture (le point que le jury sondera)

Trois couches possibles dans la stack :

| Couche | Fichier | Pour quoi |
|---|---|---|
| **PHP** | `public/index.php` (entrypoint unique) | Headers applicatifs + **cookies de session** (obligatoirement ici) |
| **Caddy** | `docker/Caddyfile` (bloc `header`) | Headers constants, couvre aussi les assets statiques |
| **Traefik** | labels `docker-compose*.yml` | Headers d'edge, surtout HSTS en prod |

**Choix retenu :** headers applicatifs (`CSP`, `X-Content-Type-Options`,
`Referrer-Policy`) + cookies **en PHP** dans `index.php` ; **HSTS au niveau Traefik**
(c'est lui qui parle HTTPS, pas PHP).

> **Réponse jury :** « `index.php` est le point d'entrée unique de toutes les requêtes
> dynamiques — endroit naturel et centralisé pour les en-têtes applicatifs. HSTS je le
> mets à l'edge TLS (Traefik) parce que c'est lui qui termine le HTTPS, pas PHP. »

**Tradeoff assumé :** des headers en PHP ne couvrent **pas** les fichiers statiques
servis directement par Caddy (assets, images). Pour une couverture totale il faudrait
Caddy ; pour ce projet le PHP suffit et est plus simple à expliquer.

---

# Étape 1 — Refactorer les 6 inline (prérequis CSP, zéro risque)

On commence ici : ça ne casse rien et ça débloque une CSP stricte (sans `'unsafe-inline'`).
**Valider que l'app marche encore après cette étape** avant de toucher aux headers.

### 1a. Classe utilitaire `.hidden`
Dans `public/style/style.css` :
```css
.hidden { display: none; }
```
> **Jury :** « Une classe utilitaire au lieu de styles inline, pour que la CSP puisse
> interdire le `style=` inline. »

### 1b. Remplacer les 4 `style="display:none"` par `class="hidden"`
Fichiers : `LayoutView.php:38`, `CreatePostView.php:35-36`, `LoginRegisterLayoutView.php:35`.
Si l'élément a déjà une classe, ajouter `hidden` à la liste.

⚠️ **Piège JS** : `index.js` montre/cache ces éléments avec `element.style.display =
"block"/"none"` (lignes 39, 54, 65, 268, 270). Un style inline écrase une classe, donc
ça marcherait en l'état — mais pour rester cohérent, convertir aussi les toggles :
- `el.style.display = "block"` → `el.classList.remove("hidden")`
- `el.style.display = "none"`  → `el.classList.add("hidden")`
- Concerné : `scrollTopBtn` (268, 270), `removeImageButton` (39, 54, 65).

> **Jury :** « Je pilote la visibilité par une classe, pas par du style inline injecté en
> JS — l'état visuel vit dans le CSS. »

### 1c. Remplacer les 2 `onclick` par `addEventListener`
Pattern déjà utilisé partout dans `index.js` (`getElementById(...).addEventListener(...)`).

**`ResponseView.php:26`** — `onclick="history.back()"` :
1. Donner un id au bouton, ex. `id="backBtn"`.
2. Dans `index.js` :
```js
const backBtn = document.getElementById("backBtn");
if (backBtn) backBtn.addEventListener("click", () => history.back());
```

**`EditUserView.php:36`** — `onclick="window.location.href='/user/<?= ... ?>'"`.
Valeur PHP dynamique → la passer via un **data-attribute** :
1. Dans la vue :
```php
<input type="button" value="Annuler" id="cancelEditBtn"
       data-cancel-url="/user/<?= $this->user->getId() ?>">
```
2. Dans `index.js` :
```js
const cancelBtn = document.getElementById("cancelEditBtn");
if (cancelBtn) cancelBtn.addEventListener("click", () => {
  window.location.href = cancelBtn.dataset.cancelUrl;
});
```
> **Jury :** « Je passe la donnée serveur au JS par un `data-*` plutôt qu'en injectant du
> code dans un `onclick` — ça sépare le markup du comportement et c'est compatible CSP. »

**Vérif étape 1 :** recharger l'app, tester bouton « retour haut de page »,
aperçu/suppression d'image en création de post, bouton Annuler de l'édition profil.
Tout doit marcher comme avant. Re-scanner : plus aucun `onclick=` ni `style="display`.

---

# Étape 2 — Durcir le cookie de session (`public/index.php`)

### 2a. Remonter la détection d'environnement au-dessus de `session_start()`
Aujourd'hui `define('APP_ENV', ...)` est ligne 14, **après** `session_start()` ligne 12.
Le flag `Secure` dépend de l'environnement → déplacer le `define('APP_ENV', ...)`
**avant** `session_start()`.

> **Jury :** « Les paramètres de cookie se figent au démarrage de la session, donc je dois
> connaître l'environnement avant `session_start()`. »

### 2b. Configurer le cookie avant de démarrer la session
Juste avant `session_start()`, avec `session_set_cookie_params()` (forme tableau, PHP 7.3+) :
```php
session_set_cookie_params([
    'httponly' => true,                       // inaccessible au JS (anti vol de session XSS)
    'samesite' => 'Lax',                      // anti-CSRF de base
    'secure'   => APP_ENV === 'production',   // HTTPS only en prod, HTTP toléré en dev
]);
```
`secure` est un **booléen calculé** depuis `APP_ENV` : la prod est sécurisée, le dev (HTTP)
reste fonctionnel.

⚠️ `SessionController.php:9` rappelle `session_start()` (gardé par `session_status()`). Le
premier appel gagne → c'est bien `index.php` qui pose les params, rien à changer dans le
contrôleur. Savoir l'expliquer.

> **Jury :** « HttpOnly empêche le vol de session par XSS, SameSite=Lax couvre le CSRF
> basique, Secure force le HTTPS en prod. »

**Vérif étape 2 :** se connecter → DevTools → Application → Cookies. `PHPSESSID` doit
afficher **HttpOnly ✓** et **SameSite = Lax**. En dev, `Secure` décoché (normal, HTTP).
Le login doit toujours fonctionner.

---

# Étape 3 — Headers applicatifs (`public/index.php`)

`header()` doit précéder tout output. `index.php` est l'entrypoint et n'echo rien : poser
les en-têtes après le bloc session, avant le `require` des routes.

Les deux sans risque :
```php
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
```

- **`X-Content-Type-Options: nosniff`** : empêche le MIME-sniffing (ex. un upload
  interprété comme du JS). Pertinent vu `public/uploaded_files/`.
- **`Referrer-Policy: strict-origin-when-cross-origin`** : ne fuite pas l'URL complète
  vers les sites tiers.

### CSP
Audit clean (scripts externes, fetch same-origin, plus d'inline après l'étape 1) :
```php
header("Content-Security-Policy: default-src 'self'");
```
Si l'aperçu d'upload casse (`img.src = event.target.result` est une *data URL*), élargir
**uniquement** les images :
```php
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:");
```
→ Tester d'abord la version stricte ; ajouter `img-src 'self' data:` **seulement** si la
console signale un blocage sur l'aperçu d'image.

> **Jury :** « `default-src 'self'` : tout vient de mon origine, aucun inline autorisé.
> Défense en profondeur contre le XSS, en complément de l'échappement serveur. »

**Vérif étape 3 :** recharger → DevTools → **Console** (violations CSP en rouge) et
**Network → Headers** de la requête principale (les 3 en-têtes présents). Cliquer partout :
création de post, aperçu image, like, navigation. Zéro erreur CSP = OK.

---

# Étape 4 — HSTS au niveau Traefik (`docker-compose.prod.yml`)

Traefik pose des headers via un **middleware `headers`**, déclaré en labels sur le service
`app` puis **attaché au routeur** `solage`. Dans le bloc `labels:` du service `app` :
```yaml
- traefik.http.middlewares.solage-hsts.headers.stsSeconds=31536000
- traefik.http.middlewares.solage-hsts.headers.stsIncludeSubdomains=true
- traefik.http.routers.solage.middlewares=solage-hsts
```
- Les deux premiers **définissent** le middleware `solage-hsts`.
- Le troisième l'**attache** au routeur existant — sans lui, le middleware ne s'applique
  à rien.

⚠️ Après modif de labels Traefik : `docker compose restart traefik`.
⚠️ **Jamais** de HSTS en dev (HTTP → on se bloquerait l'accès).

> **Jury :** « HSTS appartient à la couche TLS. Dans ma stack c'est Traefik qui termine le
> HTTPS, donc je l'y configure plutôt qu'en PHP qui ne voit que du HTTP interne. »

**Vérif étape 4 :** prod/HTTPS uniquement. Une fois déployé :
`curl -sI https://<domaine> | grep -i strict` →
`strict-transport-security: max-age=31536000; includeSubDomains`.

---

## Ordre de bataille résumé

| # | Fichier(s) | Risque | Vérif |
|---|---|---|---|
| 1 | vues + `style.css` + `index.js` | nul | l'app marche comme avant, plus d'inline |
| 2 | `public/index.php` | faible | DevTools → cookie HttpOnly + SameSite |
| 3 | `public/index.php` | moyen (CSP) | console sans erreur CSP |
| 4 | `docker-compose.prod.yml` | nul en dev | `curl -I` en prod |

Avancer étape par étape, valider chacune avant la suivante. Si une CSP casse quelque chose,
la console indique la directive à ajouter.

## Récap des headers visés

- `Content-Security-Policy: default-src 'self'` (PHP)
- `X-Content-Type-Options: nosniff` (PHP)
- `Referrer-Policy: strict-origin-when-cross-origin` (PHP)
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` (Traefik, prod)
- Cookie session : `HttpOnly` + `SameSite=Lax` + `Secure` (prod) (PHP)
