<?php

declare(strict_types=1);

require '../vendor/autoload.php';



require_once '../includes/autoload.php';
Autoloader::register();

// Mode d'exécution piloté par l'environnement (conteneur), 'development' par défaut.
// Les assets minifiés sont générés une fois au build de l'image (bin/minify.php),
// pas à chaque requête : Config se contente de servir le bon chemin selon ce mode.
define('APP_ENV', getenv('APP_ENV') ?: 'development');

session_set_cookie_params([
    'httponly' => true,                       // inaccessible au JS (anti vol de session XSS)
    'samesite' => 'Lax',                      // anti-CSRF de base
    'secure'   => APP_ENV === 'production',   // HTTPS only en prod, HTTP toléré en dev
]);

// X-Content-Type-Options: nosniff
// Empêche le navigateur de "deviner" le type MIME (MIME sniffing).
// Exemple: un fichier .txt envoyé en image/png ne sera pas reinterprété en image.
// Protection contre: attaque où un attaquant pousse un fichier text/javascript
// via un input file, espérant que le navigateur le sniff comme script.
header("X-Content-Type-Options: nosniff");

// Referrer-Policy: strict-origin-when-cross-origin
// Contrôle quelle information de referer est envoyée aux domaines tiers.
// Valeur: strict-origin-when-cross-origin = envoyer seulement l'origine (scheme + domain)
//         lors d'une requête cross-origin, jamais le chemin complet.
// Exemple: utilisateur sur https://solage.local/posts/secret123?token=abc
//          - Lien vers autre domaine → envoie referer: https://solage.local (pas le chemin)
//          - Lien vers même domaine → envoie referer complet (https://solage.local/posts/secret123)
// => Protege la vie privée de l'utilisateur si il clique sur un lien vers un domaine tiers,
// tout en permettant au site de connaître le referer complet pour les liens internes.
header("Referrer-Policy: strict-origin-when-cross-origin");

// Content-Security-Policy: default-src 'self'
// Définit les sources de contenu autorisées pour tous les types de ressources.
// Valeur: default-src 'self' = autorise UNIQUEMENT les ressources du même domaine.
// Protection contre: injection XSS (scripts malveillants), CSS injection, font injection, etc.
// Exemple bloqué:
//   - <script src="https://attacker.com/script.js"></script> → BLOQUÉ
//   - <img src="https://external.com/image.png"> → BLOQUÉ
//   - <style>@import url("https://attacker.com/style.css");</style> → BLOQUÉ
// Exemple autorisé:
//   - <script src="/js/app.js"></script> → AUTORISÉ (même origine)
//   - <img src="/images/logo.png"> → AUTORISÉ (même origine)
// Note: pour 3rd-party libs (Google Fonts, Cloudflare, etc), ajouter des exceptions:
//       Content-Security-Policy: default-src 'self'; font-src 'self' https://fonts.googleapis.com
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:;");

// Session démarrée au plus tôt, avant tout output possible.
// Garantit que le cookie PHPSESSID peut être posé même si le contrôleur
// echo'e du JSON avant d'appeler SessionManager::login().
session_start();

require_once '../includes/database.php';

// Instancié avant le routage pour que le token CSRF existe dès le premier rendu.
new SessionManager(new UserModel());

require_once '../routes/index.php';
