# Dossier de projet Solage — source LaTeX

Source LaTeX du **dossier de projet** pour le titre professionnel *Concepteur Développeur
d'Applications* (RNCP 37873). Tous les schémas (architecture, MCD, MLD, cas d'utilisation,
séquence, classes, enchaînement) sont générés en **TikZ** — aucune image externe n'est requise.

## Compilation

Nécessite une distribution LaTeX (**MiKTeX** sur Windows, ou **TeX Live**). Les packages
spéciaux (`tcolorbox`, `listings`, `tikz`, `babel-french`) sont standards ; MiKTeX les installe
automatiquement à la première compilation.

### Le plus simple — latexmk

```bash
latexmk -pdf main.tex
```

### À la main — pdflatex (3 passes)

Trois passes sont nécessaires (table des matières + références croisées + signets) :

```bash
pdflatex main.tex
pdflatex main.tex
pdflatex main.tex
```

Le résultat est `main.pdf`.

> Éditeurs : sous **TeXstudio** / **TeXworks** / **Overleaf**, ouvrir `main.tex`, choisir le
> moteur **pdfLaTeX** et compiler (deux à trois fois). Sur Overleaf, déposer tout le dossier
> `dossier-latex/` tel quel.

## Arborescence

```
dossier-latex/
├── main.tex                 ← racine : à compiler
├── preamble.tex             ← packages, styles, couleurs, boîtes (jury / à développer / note)
├── titlepage.tex            ← page de garde
├── chapters/
│   ├── 01-competences.tex   ← liste des compétences mises en œuvre
│   ├── 02-besoins.tex       ← expression des besoins (objectifs, limites)
│   ├── 03-environnement.tex ← environnement technique (stack, Docker)
│   ├── 04a-architecture.tex ← architecture multicouche + DICP, gestion de projet
│   ├── 04b-conception.tex   ← maquettes, MCD/MLD/MPD, diagrammes UML
│   ├── 04c-developpement.tex← interfaces, métier, accès données, framework
│   ├── 04d-securite.tex     ← XSS, CSRF, injection SQL, IDOR, en-têtes
│   ├── 04e-tests.tex        ← plan de tests, jeu d'essai, déploiement, DevOps, veille
│   ├── 05-bilan.tex         ← bilan, difficultés, perspectives
│   └── annexes.tex          ← script SQL, code, jeux de tests
└── figures/                 ← schémas TikZ (.tex)
    ├── architecture.tex  mcd.tex  mld.tex
    ├── usecase.tex  sequence-post.tex  classes.tex  enchainement.tex
```

## Dépannage

- **Erreurs d'espacement dans les blocs de code** avec une vieille version de `babel-french` :
  ajouter, juste après le chargement de `babel` dans `preamble.tex`,
  `\frenchsetup{StandardLayout=true}` (désactive les espaces fines automatiques avant
  `; : ! ?`). Inutile avec une distribution récente (TeX Live / MiKTeX ≥ 2020).
- **Package manquant** : sous MiKTeX, accepter l'installation automatique proposée ; sous
  TeX Live, `tlmgr install tcolorbox listings pgf` au besoin.
- Toujours compiler **deux à trois fois** pour stabiliser la table des matières et les
  références croisées.

## Zones « À DÉVELOPPER »

Les parties qui restent à produire (tests PHPUnit, CI/CD, captures d'écran,
procédures de déploiement et de sauvegarde, journal de veille) sont signalées par des **encadrés
orange « À DÉVELOPPER »** précisant exactement quoi y mettre. Elles correspondent aux points
encore ouverts du projet — à compléter avant de figer le dossier.

## Couverture des contraintes du référentiel

- **40 à 60 pages** hors page de garde, sommaire et annexes (schémas compris) : le squelette est
  dimensionné pour ~50 pages une fois les zones « à développer » remplies.
- **Annexes ≤ 40 pages.**
- Plan « projet de formation » (4 parties) enrichi des livrables de conception attendus
  (architecture, MCD/MPD, UML, sécurité, tests, jeu d'essai, veille).
