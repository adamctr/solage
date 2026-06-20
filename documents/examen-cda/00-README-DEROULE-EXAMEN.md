# Examen CDA — Déroulé complet & guide des livrables

> **Titre professionnel** : Concepteur Développeur d'Applications (CDA)
> **Niveau** : 6 (équiv. Bac+3/4) — **RNCP37873** — Code titre **TP‑01281**
> **Arrêté** : 26/04/2023 (JO 13/05/2023), millésime **04** du référentiel
> **Projet support** : **Solage** — réseau social type X/Twitter (PHP MVC, PostgreSQL, Docker)
>
> Ce dossier (`documents/examen-cda/`) reconstitue, à partir des deux référentiels officiels
> (REAC + RC) et du code réel de Solage, **tout ce qu'il faut produire** pour l'examen et
> **comment** le produire. Tout est aligné sur le projet tel qu'il existe aujourd'hui.

Dernière mise à jour : 2026‑06‑14

---

## 0. Comment utiliser ce dossier

| Fichier | À quoi il sert | Quand t'en servir |
|---|---|---|
| **`00-README-DEROULE-EXAMEN.md`** (ce fichier) | Le déroulé de l'examen, les règles du jeu, le rétro‑planning, la checklist | À lire **en premier**, puis garder comme tableau de bord |
| **`01-DOSSIER-PROJET.md`** | Le **sommaire détaillé** du dossier de projet (40‑60 p.) + guide de rédaction section par section, avec budget de pages, contenu Solage concret et « réponse jury » | Pendant **la rédaction** du dossier |
| **`02-ORAL-SOUTENANCE.md`** | Le **plan du diaporama** (40 min, slide par slide) + notes orateur + préparation de l'entretien technique (45 min) + questionnaire pro (anglais B1) | Pendant **la prépa de l'oral** |
| **`03-MAPPING-COMPETENCES.md`** | Le tableau **11 compétences → critères du référentiel → preuve dans Solage → page du dossier** | Pour **vérifier la couverture** et préparer l'**entretien technique** |

> **Règle d'or de tout le dossier** (issue du `CLAUDE.md` projet) : pour chaque choix, tu dois
> pouvoir donner **la phrase de défense** — « j'ai fait X plutôt que Y parce que Z, en
> acceptant le coût W ». Les guides te fournissent cette phrase à chaque section.

---

## 1. Ce qu'est l'examen (vue d'ensemble)

Le titre CDA se compose de **3 blocs de compétences** (les **CCP** = Certificats de Compétences
Professionnelles), eux‑mêmes découpés en **11 compétences professionnelles** + **3 compétences
transversales**. On passe le **titre complet** : il faut donc couvrir les 3 CCP.

```
TITRE CDA (RNCP37873)
│
├─ CCP 1 — Développer une application sécurisée
│   ├─ C1  Installer et configurer son environnement de travail en fonction du projet
│   ├─ C2  Développer des interfaces utilisateur
│   ├─ C3  Développer des composants métier
│   └─ C4  Contribuer à la gestion d'un projet informatique
│
├─ CCP 2 — Concevoir et développer une application sécurisée organisée en couches
│   ├─ C5  Analyser les besoins et maquetter une application
│   ├─ C6  Définir l'architecture logicielle d'une application
│   ├─ C7  Concevoir et mettre en place une base de données relationnelle
│   └─ C8  Développer des composants d'accès aux données SQL et NoSQL
│
└─ CCP 3 — Préparer le déploiement d'une application sécurisée
    ├─ C9  Préparer et exécuter les plans de tests d'une application
    ├─ C10 Préparer et documenter le déploiement d'une application
    └─ C11 Contribuer à la mise en production dans une démarche DevOps

Transversales (évaluées À TRAVERS les compétences pro, pas séparément) :
    T1  Communiquer en français et en anglais (anglais B1 écrit/compréhension, A2 oral)
    T2  Mettre en œuvre une démarche de résolution de problème
    T3  Apprendre en continu (veille)
```

**Fil rouge du métier** (à répéter au jury) : *la sécurité est une préoccupation **constante**,
à **chaque couche**, en suivant les recommandations **ANSSI / OWASP**, le **RGPD** et le **RGAA**.*
C'est le mot qui revient dans les 11 fiches compétences du référentiel.

---

## 2. Le jour J — déroulé de l'épreuve (titre complet)

Durée totale de l'épreuve pour le candidat : **≈ 2 h 15**. Présence du jury : **1 h 45**.

| # | Épreuve | Durée | Ce que le jury évalue | Conditions |
|---|---|---|---|---|
| 0 | **Lecture du dossier** par le jury | (hors présence) | Le dossier de projet imprimé | Le jury lit **avant** ta présentation |
| 1 | **Questionnaire professionnel** | **30 min** | Compétences non couvertes par le projet + anglais B1 | Écrit, **sans internet**, surveillé, tous candidats ensemble. **Évalué avant** ta présentation |
| 2 | **Présentation du projet** (diaporama) | **40 min** | C2→C9 (cf. tableau) | Tu présentes seul, **le jury n'intervient pas** |
| 3 | **Entretien technique** | **45 min** | Les 11 compétences, à partir du dossier + présentation | Questions/réponses avec le jury |
| 4 | **Entretien final** | **≈ 20 min** | Vision globale du métier, à partir du **Dossier Professionnel (DP)** | Le jury dispose de tout ton dossier |

**Questionnaire professionnel (détail)** — 4 questions :
- **2 questions fermées** à choix unique, posées **en français** ;
- **2 questions ouvertes** posées **en anglais**, à partir d'une **documentation technique en
  anglais**, réponses courtes **rédigées en anglais**.

> ⚠️ **Ordre réel** : le questionnaire (écrit) est souvent passé **en premier** (le matin, en
> groupe) et **corrigé avant** ta présentation. Prépare l'anglais technique en amont.

---

## 3. Les livrables à produire

| Livrable | Format | Contrainte | Statut Solage |
|---|---|---|---|
| **Dossier de projet** | Imprimé (PDF) | **40 à 60 pages** hors page de garde / sommaire / annexes — **schémas et illustrations compris** | À rédiger → `01-DOSSIER-PROJET.md` |
| **Annexes du dossier** | Imprimé (PDF) | **40 pages max** | À assembler (code, maquettes, jeux de tests) |
| **Diaporama de soutenance** | PPT/PDF | ~30‑40 slides pour 40 min | À faire → `02-ORAL-SOUTENANCE.md` |
| **Dossier Professionnel (DP)** | `Template - vierge - TITRE6.docx` | Parcours / expérience du candidat | À remplir (gabarit fourni par le centre) |

> Le **dossier de projet** ≠ le **dossier professionnel (DP)**. Le DP parle de **toi** (parcours,
> expériences) et sert à l'**entretien final**. Le dossier de projet parle de **Solage**.

---

## 4. Choix de plan : « formation » enrichi du plan « entreprise »  ⭐ décision structurante

Le référentiel (RC) prévoit **deux plans** de dossier selon le contexte du projet :

- **Projet en entreprise** → plan **détaillé** (cahier des charges, présentation entreprise,
  gestion de projet, spécifications fonctionnelles **avec UML/MCD/MPD**, spécifications
  techniques, réalisations, sécurité, plan de tests, jeu d'essai, veille).
- **Projet en formation** → plan **court** : (1) liste des compétences, (2) expression des
  besoins, (3) environnement technique, (4) réalisations.

**Solage est un projet de formation.** Le plan court reste parfaitement valable — et il peut, lui
aussi, faire 40‑60 pages (sa 4ᵉ partie « réalisations » est un fourre‑tout élastique) et prouver
les 11 compétences. **Son défaut n'est pas d'être « trop court », c'est d'être *plat* :** il
n'énumère pas les livrables de conception. Or ces livrables — maquettes + enchaînement, MCD/MPD,
diagrammes UML, schéma d'architecture, plan de tests, jeu d'essai, sécurité, veille — sont
**exigés par les critères d'évaluation des compétences** (RC §3.2 / REAC), **quel que soit le
plan**. Pris au pied de la lettre, le plan formation invite donc à **oublier des artefacts qui
sont notés** et à sous‑structurer le dossier.

> ⚠️ La fourchette **40‑60 p. dépend du niveau du titre, pas du plan** : le plan formation doit
> l'atteindre tout autant. Ne défends jamais l'idée que « le plan court ne permet pas 40‑60 p. » —
> un jury te corrigerait.

➡️ **Décision retenue** : garder le **squelette « formation » en 4 grandes parties** (conforme au
référentiel), mais **sous‑structurer la partie « Réalisations » selon les sections du plan
« entreprise »** — architecture multicouche, maquettes + enchaînement, MCD/MPD, script SQL,
diagrammes de cas d'utilisation et de séquence, extraits de code (interfaces / métier / accès
données / autres), éléments de sécurité, plan de tests, jeu d'essai, veille. Ce n'est pas du
contenu « en plus » : c'est exactement ce que les compétences imposent — je le rends simplement
**explicite** pour ne rien oublier.

> **Réponse jury** : *« Mon projet est un projet de formation, je suis donc le plan en quatre
> parties du référentiel. J'ai sous‑structuré la partie Réalisations selon les livrables de
> conception — architecture, MCD/MPD, UML, sécurité, tests — parce que ce sont les critères
> d'évaluation des compétences qui les exigent : je les rends explicites pour démontrer chacune
> des onze compétences sans en oublier. »*

C'est ce plan enrichi qui est détaillé dans **`01-DOSSIER-PROJET.md`**.

---

## 5. Couverture des 11 compétences par Solage (synthèse)

Détail complet (critères + preuves + pages) dans **`03-MAPPING-COMPETENCES.md`**. Synthèse :

| # | Compétence | Couverture | Preuve principale dans Solage |
|---|---|---|---|
| C1 | Environnement de travail | ✅ Solide | Docker (FrankenPHP+Caddy+Traefik+Postgres), Git, Composer, conteneurs |
| C2 | Interfaces utilisateur | ✅ Solide | Vues `modules/views/`, JS vanilla, AJAX, escaping XSS, CSP |
| C3 | Composants métier | ✅ Solide | Contrôleurs `modules/controllers/`, auth, IDOR, validation, CSRF |
| C4 | Gestion de projet | ✅ OK | Git, roadmap, journal Problème/Solution ; absence d'outil collaboratif assumée (projet solo) |
| C5 | Analyser besoins & maquetter | ✅ OK | Expression de besoins + 10 maquettes Penpot + enchaînement (storyboard) |
| C6 | Architecture logicielle | ✅ Solide | MVC multicouche, Router, Middlewares, schéma DICP |
| C7 | Base de données relationnelle | ✅ Solide | `solage.pg.sql`, migrations idempotentes, FK, index, seed |
| C8 | Composants d'accès aux données | ✅ Solide | `modules/models/`, PDO prepared statements, anti‑injection SQL |
| C9 | **Plans de tests** | ✅ OK | 40 tests PHPUnit (unitaires + intégration + sécurité), suite verte |
| C10 | Préparer/documenter déploiement | ✅ Documenté | `docker-compose.prod.yml`, Traefik HTTPS, `DEPLOYMENT.md` (ancré au dossier) |
| C11 | Mise en production DevOps | 🟠 À compléter | Conteneurs ✅, `bin/migrate.php` — **manque** CI/CD (GitHub Actions) |

**Compétences obligatoirement démontrées par le projet** (RC, titre complet) :
**C2, C3, C4, C5, C6, C7, C8, C9.** Les compétences **C1, C10, C11** sont surtout vérifiées à
l'**entretien technique** et au **questionnaire** — mais Solage les couvre aussi (Docker), ce qui
est un **bonus** à mettre en avant.

---

## 6. ⚠️ Le risque n°1 : les tests (C9 est OBLIGATOIRE)

**C9 « Préparer et exécuter les plans de tests » fait partie des compétences que le projet doit
obligatoirement démontrer.** Or aujourd'hui Solage n'a **aucun test automatisé**. C'est le point
le plus à risque de tout l'examen.

À produire **avant** de figer le dossier (cf. `ROADMAP_DETAILLEE.md` Phase 3) :
- **PHPUnit** installé (dépendance dev Composer) ;
- tests unitaires sur `UserValidator`, `Utils::e()`, un modèle (`PostModel`/`LikeModel`), le `Router` ;
- **tests de sécurité** : 1 injection SQL (recherche), 1 XSS (création post), 1 CSRF (POST sans
  token → 403), 1 IDOR (Bob édite le profil d'Alice → 403) ;
- un **plan de tests rédigé** (fonctionnalité → cas → entrée / attendu / obtenu / écart) ;
- le **jeu d'essai de la fonctionnalité la plus représentative** (ex. « Poster un message »).

> Même un socle modeste mais **réel et exécutable** (10‑15 tests verts + 4 tests de sécurité)
> suffit à transformer un trou rédhibitoire en compétence démontrée. C'est **la priorité**.

---

## 7. Rétro‑planning conseillé (à caler sur ta date d'examen)

Repère = **date de remise du dossier** (souvent ~2 semaines avant l'oral). Compte ~3‑4 semaines
de travail à temps plein pour tout finir proprement.

| Quand | Bloc | Sortie attendue |
|---|---|---|
| **S‑6 à S‑5** | Combler les trous techniques | Tests (C9) ✅, validation côté serveur, finir CSRF/headers, `DEPLOYMENT.md`, CI minimal |
| **S‑5 à S‑4** | Conception formelle | Maquettes Figma + enchaînement, MCD/MPD au propre, diagrammes cas d'usage + séquence |
| **S‑4 à S‑2** | **Rédaction du dossier** | Dossier 40‑60 p. selon `01-DOSSIER-PROJET.md` + annexes |
| **S‑2** | **Remise du dossier imprimé** | PDF figé, relu, paginé, sommaire à jour |
| **S‑2 à S‑1** | Diaporama + répétitions | Slides selon `02-ORAL-SOUTENANCE.md`, oral chronométré à 40 min |
| **S‑1** | Entretien technique + anglais | Fiches questions/réponses par compétence, anglais B1, DP rempli |
| **Jour J** | Examen | 😎 |

---

## 8. Checklist livrables (à cocher)

**Dossier de projet (40‑60 p.)**
- [ ] Page de garde + sommaire paginé (hors décompte)
- [ ] 1. Liste des compétences mises en œuvre (tableau de mapping)
- [ ] 2. Expression des besoins (objectifs **et limites**)
- [ ] 3. Environnement technique (stack + justifications + Docker)
- [ ] 4. Réalisations :
  - [ ] Architecture logicielle multicouche + rôle sécurité de chaque couche (DICP)
  - [ ] Maquettes + schéma d'enchaînement des écrans
  - [ ] MCD + MPD + script de création BDD
  - [ ] Diagramme de cas d'utilisation
  - [ ] Diagramme(s) de séquence (1‑2 cas significatifs)
  - [ ] Extraits de code : interface utilisateur + capture d'écran
  - [ ] Extraits de code : composant métier
  - [ ] Extraits de code : composant d'accès aux données
  - [ ] Extraits de code : autres composants (Router, Middleware, Utils…)
  - [ ] Éléments de sécurité (XSS, CSRF, SQLi, IDOR, headers, hash…)
  - [ ] Plan de tests
  - [ ] Jeu d'essai de la fonctionnalité la plus représentative (entrée/attendu/obtenu + écarts)
  - [ ] Veille sécurité (sources + vulnérabilités trouvées + corrigées)
- [ ] Annexes (≤ 40 p.) : maquettes, captures + code, jeux de tests complets

**Oral**
- [ ] Diaporama ~30‑40 slides (40 min chrono)
- [ ] Démo de l'attaque CSRF/IDOR (capture ou live) prête
- [ ] Fiches entretien technique (11 compétences)
- [ ] Anglais B1 : lecture de doc technique + 2 réponses ouvertes

**Administratif**
- [ ] Dossier Professionnel (DP) `Template - vierge - TITRE6.docx` rempli
- [ ] Dossier de projet **imprimé** dans les délais du centre

---

## 9. Sources de référence (dans ce repo)

- `documents/EDUCENTRE_RNCP37873_REAC.pdf` — Référentiel Emploi Activités Compétences (le métier,
  les 11 compétences, les savoir‑faire) → extrait texte : `documents/REAC.txt`
- `documents/EDUCENTRE_RNCP37873_RC.pdf` — Référentiel de Certification (modalités d'évaluation,
  plans de dossier, déroulé) → extrait texte : `documents/RC.txt`
- `documents/ROADMAP_DETAILLEE.md` — l'avancement réel, phase par phase
- `documents/Probleme-Solution.md` — le **journal de décisions** (mine d'or pour l'entretien)
- `documents/Workflow.md` — le cycle AJAX/validator/session (schéma)
- `documents/csrf-securite-guide.md` / `documents/headers-securite-guide.md` — sécurité appliquée
- `documents/trucs à retenirs pour dire au diplome.md` — anecdotes (déclic IDOR / URSSAF)
