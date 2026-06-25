# SUIVI.md — suivi des tâches du projet Solage

> Suivi des tâches du projet, **consolidé depuis l'historique Git** (chaque tâche correspond à un
> ou plusieurs commits datés). Le projet s'est déroulé en deux temps : une construction initiale
> (2024), puis une reprise dédiée à la sécurisation et à l'industrialisation (2026).

## Phase 1 — Construction initiale (sept. – nov. 2024)

- [x] Mise en place du dépôt et de la page d'accueil (front) — *2024-09*
- [x] Système de « j'aime » (likes) + appels AJAX — *2024-09*
- [x] Routeur maison et gestion des utilisateurs — *2024-09*
- [x] Module de réponses (réponses imbriquées aux messages) — *2024-10*
- [x] Navigation et routes — *2024-10*
- [x] Minification des assets — *2024-11*
- [x] Comptage (récursif) des réponses d'un message — *2024-11*

## Phase 2 — Reprise : sécurisation & industrialisation (mai – juin 2026)

### Environnement & base de données
- [x] Conteneurisation (Docker) + migration de la base vers PostgreSQL — *2026-05-06*
- [x] Gestion des secrets (`.env` via phpdotenv) + logger PSR-3 — *2026-05-06*
- [x] Jeu d'essai : extraction des données de démo dans `seed.sql` — *2026-05-13*

### Sécurité
- [x] `AdminMiddleware` + protection des routes admin/édition, durcissement des erreurs — *2026-05-06*
- [x] Échappement du contenu utilisateur (anti-XSS) + contrôle d'accès à la suppression (anti-IDOR) — *2026-05-12*
- [x] Protection CSRF + en-têtes de sécurité + injection de dépendance de session — *2026-06-15*

### Architecture & qualité
- [x] Refonte MVC : préchargement des auteurs (correction du N+1), découplage du validateur, déplacement des helpers, correction de 4 bugs — *2026-05-12*
- [x] Extraction du validateur dans `modules/validators/` — *2026-05-12*
- [x] Suppression de `DynamicMessageController`, unification de la réponse JSON — *2026-05-12*
- [x] Mise en place de PHP_CodeSniffer (PSR-12) + reformatage du code — *2026-06-15*

### Interface
- [x] Refonte de la feuille de style (style minimaliste moderne) — *2026-05 / 06*

### Documentation
- [x] Suivi des tâches (`README.md` en Kanban) + journal de décisions (`Probleme-Solution.md`) — *2026-05*
- [x] Dossier de projet + notes de soutenance — *2026-06*
