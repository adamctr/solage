# Contenu des boîtes Canva — prêt à coller

> **À quoi sert ce fichier.** Pour chaque slide, le **texte exact** qui va dans la (ou les)
> boîte(s) de corps — la table DICP, le bloc de code CSRF, les puces, etc. Deux usages :
> tu le colles toi-même, **ou** tu crées une boîte vide (repère `.`) et je la remplis via l'API.
>
> **Rappel des contraintes API.** L'API Canva n'édite que des boîtes **déjà existantes** : elle ne
> crée pas de boîte, n'insère ni tableau, ni image, ni code en bloc, ni les notes d'orateur, et ne
> réordonne pas les pages. D'où ce partage : **toi** = créer les boîtes / coller les images / structurer ;
> **moi** = écrire le texte dedans.
>
> **Style slide = court.** Ces blocs sont volontairement ramassés (le détail, tu le dis à l'oral —
> voir les notes 🎤 dans `04-DIAPORAMA-TEXTES-NOTES.md`).

---

## Étape 0 — structure (à faire une fois, à la main)

1. Supprimer les pages **16** et **17** (les deux « Demo » dupliquées par erreur).
2. **Dupliquer la page 18** (« Fonctionnalité : ») **×2** → 3 slides de contenu d'affilée.
   - Ordre obtenu : **15** Démo · **16** Publier · **17** CSRF · **18** IDOR.
3. Sur chaque slide « à créer » ci-dessous : ajouter une zone de texte, taper un point `.` comme
   repère (le plus propre : copier une boîte de corps existante puis la vider à `.`).

Quand c'est prêt → dis-moi, je remplis tout.

---

## Section 1 — Introduction

### Slide 1 · Titre — ✅ déjà rempli (boîte optionnelle)
*Déjà en place : « Concepteur Développeur d'Application — RNCP 37873 / Adam COURTARO · Session 2026 ».*
Boîte slogan **optionnelle** :
```
Un réseau social, prétexte à une application web sécurisée à chaque couche
```

### Slide 3 · Introduction — optionnel
*Boîtes existantes : « Fonctionnalités essentielles » + « Projet de formation ».*
Si tu veux détailler, une boîte :
```
Clone minimaliste de X / Twitter
Fil · posts & réponses · likes · profils · recherche · admin
```

### Slide 4 · Objectifs — à créer (1 boîte) ou réutiliser « But pédagogique »
```
But pédagogique : maîtriser, pas déléguer
Objectifs vérifiables : MVC maison · sécurité multicouche · PostgreSQL · Docker
Périmètre maîtrisé > large et bâclé
```

---

## Section 2 — Gestion / Conception

### Slide 6 · Parties prenantes — à créer (1 boîte)
*Adam COURTARO en place, Fabio Voliani barré.*
```
Projet solo
```

### Slide 7 · Gestion de projet (comment ça a été géré) — à créer (1 boîte)
```
Git : historique par phases
Journal de décisions (problème → solution)
Planifier · suivre · rendre compte — même seul
```

### Slide 8 · Gestion de projet (aujourd'hui) — à créer (1 boîte)
```
Ce que je referais autrement :
CI/CD dès le départ · TDD · validation serveur systématique
Un framework si contexte pro
```

### Slide 9 · Maquettes — optionnel (2 maquettes déjà là)
```
Maquettes : connexion + fil d'actualité
Conception de l'UI avant le code
```

### Slide 10 · Technologies — optionnel (logos déjà là)
```
PHP 8.3 (MVC maison) · PostgreSQL 16 · Docker
FrankenPHP + Caddy · Traefik 3.1
```

### Slide 11 · Architecture technique — à créer (1 boîte, à gauche du schéma)
```
MVC + Front Controller maison
Modèles = tout le SQL · Vues = toute la sortie · Contrôleurs = autorisation
src/ = routeur, middlewares, logger
```

### Slide 12 · Sécurité par couche (DICP) ⭐ — à créer (1 boîte large)
*Titre déjà posé. La pièce maîtresse de ta soutenance.*

Lecture (pour toi) :

| Couche | Protection |
|---|---|
| Transport | HTTPS + HSTS (Traefik) |
| Session | cookie HttpOnly · SameSite · Secure |
| Requête | **CSRF sur tout POST → 403** |
| Accès | Auth + Admin + ownership |
| Données | requêtes préparées (anti-SQLi) |
| Sortie | échappement serveur + client (anti-XSS) |
| Preuve | Logger PSR-3 (refus tracés) |

À mettre dans **une boîte texte** (je remplis ça) :
```
Transport — HTTPS + HSTS (Traefik)
Session — cookie HttpOnly · SameSite · Secure
Requête — CSRF sur tout POST → 403
Accès — Auth + Admin + ownership
Données — requêtes préparées (anti-SQLi)
Sortie — échappement serveur + client (anti-XSS)
Preuve — Logger PSR-3 (refus tracés)
```
> Si tu préfères un **vrai tableau Canva** (Éléments → Tableaux, 2 colonnes × 7 lignes) : recopie
> les 7 lignes ci-dessus. Plus joli, mais c'est toi qui le remplis (l'API n'écrit pas dans les cellules).

### Slide 13 · Base de données — MCD — colle ton image, sinon 1 boîte
*Idéal : colle ton schéma MCD (image). À défaut, une boîte :*
```
4 entités : roles · users · posts · likes
User crée Post · User aime Post · Post répond à Post · User a un Role
```

### Slide 14 · Base de données — MLD — colle ton image, sinon 1 boîte
*Idéal : colle ton schéma MLD (image). À défaut, une boîte :*
```
Tables réelles + clés étrangères + index
Convention user_id (user réservé en PostgreSQL)
Migrations idempotentes (service migrate)
```

---

## Section 3 — Fonctionnalités représentatives

### Slide 15 · Démo (intercalaire) — ✅ rien à faire
*« Demo » centré, parfait tel quel.*

### Slide 16 · Fonctionnalité : publier un message — titre + 1 boîte
*Titre à corriger (je le fais) :* **Fonctionnalité : publier un message**
Boîte :
```
Vue → Contrôleur (validation inline) → Modèle → BDD
AJAX sans rechargement · échappement à l'affichage
```

### Slide 17 · Focus CSRF + démo 403 ⭐ — titre + 2 boîtes
*Titre à corriger (je le fais) :* **Focus CSRF + démo 403**
Boîte 1 (puces) :
```
Synchronizer token — armé par le Router sur tout POST
random_bytes(32) · comparaison hash_equals · échec → 403 + log
```
Boîte 2 (code — tape `code` comme repère) :
```php
if ($route['method'] === 'POST') {
    $csrfMiddleware = new CsrfMiddleware();
    $csrfMiddleware->handle();   // 403 si token absent/invalide
}
```

### Slide 18 · Le déclic IDOR ⭐ — titre + 1 boîte
*Titre à corriger (je le fais) :* **Le déclic IDOR : veille → audit → fix**
Boîte :
```
IDOR (OWASP A01) : /edituser/{id} — changer l'id = éditer autrui
Fix : current === target || isAdmin() → 403 + log
3 routes corrigées
```

---

## Section 4 — Préparation au déploiement & Conclusion

### Slide 19 · Tests — à créer (1 boîte)
```
PHPUnit — 6 classes de test (8 fichiers), ~40 cas
Unitaire · mock · intégration PostgreSQL · sécurité
Un test prouve l'injection SQL inerte
```

### Slide 20 · Déploiement & CI — à créer (1 boîte)
*Sous-titre déjà corrigé en « Déploiement & CI ».*
```
Déploiement RÉEL : docker-compose.prod.yml — Traefik HTTPS, Postgres fermé, service migrate, rollback git + pg_dump
CI GitHub Actions : plan écrit, prochaine étape
En local : phpcs ✓ · phpunit ✓ · docker build ✓
```

### Slide 21 · Conclusion & perspectives — à créer (1 boîte)
```
Bilan : appli multicouche sécurisée · framework maison maîtrisé · tests réels
Perspectives : CI GitHub Actions · validation serveur systématique · RGAA · upload (MIME réel)
Merci — vos questions
```

---

## Récap — qui fait quoi

| Slide | Action | Qui |
|---|---|---|
| 16, 17 (Demo dup) | supprimer | toi |
| 18 → dupliquer ×2 | structure section 3 | toi |
| 13, 14 | coller les images MCD/MLD | toi |
| 12 (option tableau) | construire le tableau Canva | toi (sinon boîte texte = moi) |
| toutes les boîtes `.` | créer les zones vides | toi |
| **tout le texte des boîtes** | **remplir** | **moi (via API)** |
| titres slides 16/17/18 | corriger | moi |
| notes d'orateur 🎤 | coller (bouton « Notes ») | toi |
