# Solage — Suivi de projet

Tableau de suivi des tâches du projet. Statuts : **À faire → En cours → À finaliser → Terminé**, plus les éléments écartés du périmètre.

## À faire
- Validation du **type MIME réel** des images uploadées (aujourd'hui : contrôle de l'extension uniquement)
- Anti-fixation de session : régénérer l'identifiant de session à la connexion
- Accessibilité **RGAA** (contrastes, libellés de formulaires, navigation clavier)
- Étendre la couverture de tests : autorisations (ownership / IDOR) et échappement des vues
- Généralisation de la validation côté serveur : validateur dédié aux **posts** (celui des utilisateurs existe déjà)
- Formulaires : validation « à la volée » avec messages d'information dynamiques côté client

## En cours



## À finaliser

- PHPDoc, convention de nommage et indentation homogènes sur **tout** le code source (passe qualité finale)

## Terminé

**Architecture & socle**
- Structure des dossiers et du projet (MVC + Front Controller)
- Autoloader maison (basé sur les chemins, sans namespace)
- Routeur maison (URL → `Contrôleur#méthode`, paramètres `{id}`, middlewares appliqués automatiquement)
- Connexion base de données (PDO PostgreSQL, singleton, requêtes préparées)
- Point d'entrée unique `public/index.php` (session, en-têtes de sécurité, routeur)

**Base de données**
- MCD & MLD → schéma SQL `solage.pg.sql` (`roles`, `users`, `posts`, `likes` + index sur les clés étrangères)
- Migrations idempotentes (`src/Migrations.php`, service Docker `migrate` au démarrage)
- Modèles User, Post, Like, Search (CRUD, recherche, chargement groupé `getUsersByIds` anti N+1)

**Fonctionnalités**
- Inscription / connexion / déconnexion (email + mot de passe)
- Fil d'actualité (20 derniers posts + auteurs)
- Publication de messages en AJAX (texte + image)
- Réponses en fil de discussion (`reply_to` / `reply_to_parent`)
- Likes (ajout / retrait)
- Profils utilisateurs (consultation + édition)
- Suppression de compte et de message
- Recherche (messages + utilisateurs)
- Espace d'administration (tableau de bord + recherche admin)

**Sécurité**
- Protection CSRF : token en session, vérifié sur **tous les POST** via middleware (comparaison `hash_equals`)
- Authentification (`AuthMiddleware`) et accès réservé admin (`AdminMiddleware`)
- Contrôle de propriété / anti-IDOR sur édition de profil, suppression de compte et de message (`courant === cible || admin` → 403 + log)
- Requêtes préparées partout (anti-injection SQL)
- Échappement systématique des sorties via `Utils::e()` (anti-XSS)
- Cookie de session HttpOnly · SameSite · Secure (en production)
- En-têtes HTTP de sécurité (CSP, X-Content-Type-Options, Referrer-Policy)
- Hachage des mots de passe (bcrypt) ; validateur d'authentification (`UserValidator`)
- Journalisation PSR-3 (`src/Logger.php`) : refus 403, échecs de connexion et erreurs tracés

**Tests**
- Suite PHPUnit : unitaires (CSRF, session, utils), intégration PostgreSQL (chaque test en transaction annulée), sécurité (injection SQL prouvée inerte)

**Déploiement & outillage**
- Environnement Docker de développement (Traefik + FrankenPHP + PostgreSQL)
- Environnement Docker de production (`docker-compose.prod.yml` : HTTPS Let's Encrypt, HSTS, PostgreSQL non exposé)
- Linting `phpcs` PSR-12 (exclusions documentées et justifiées)
- `.env.example` versionné, secrets chargés via phpdotenv

- Intégration continue (GitHub Actions) : automatiser `phpcs` + PHPUnit + build Docker à chaque push

## On ne fera pas

*Optimisations SEO / performance hors du périmètre d'un projet de formation centré sur l'architecture et la sécurité :*
- Chargement différé (lazy loading) des images sous la ligne de flottaison
- Inscription du site sur des annuaires
- Référencement local et réseaux sociaux
- Redimensionnement / compression automatique des images
