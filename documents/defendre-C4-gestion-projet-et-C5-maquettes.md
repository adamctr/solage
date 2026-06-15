# Comment défendre C4 (gestion de projet) et C5 (besoins / maquettes)

> Deux points faibles du dossier Solage — mais les deux se défendent très bien à condition de
> les **cadrer honnêtement**. Voici quoi dire et quoi écrire pour chacun.

---

## C4 — Contribuer à la gestion d'un projet informatique (en solo)

**Rassure-toi d'abord** : le référentiel autorise *explicitement* la gestion **complète en
autonomie** pour un petit projet (« il peut mener en autonomie la gestion complète d'un projet
informatique »). Travailler seul ≠ pas de gestion de projet. Tu as joué **tous les rôles**.

### Ce que tu peux revendiquer honnêtement (tu l'as déjà fait, il faut juste le nommer)

| Attendu C4 | Ce que tu présentes |
|---|---|
| Méthode choisie | **Itérative / Kanban**, justifiée : seul + périmètre qui évolue → l'itératif bat le cycle en V |
| Planification | Ta **feuille de route** (phases, priorités, risque par CCP) = ton backlog |
| Suivi des tâches | **Git** : une fonctionnalité = une série de commits traçables |
| Outils collaboratifs | **Git / GitHub** — l'outil collaboratif standard, que j'utiliserais aussi en équipe |
| Procédures qualité | Tes **règles que tu t'es fixées** (séparation des couches, requêtes préparées systématiques, échappement systématique) + auto-revue de code |
| Comptes rendus | Ton **journal de décisions** = journal de bord / comptes rendus de session |

### Ce qui fait passer C4 de « Partiel » à « OK » (rapide à produire)

1. un **tableau Kanban GitHub Projects** (2-3 captures) → coche la case « outil collaboratif » ;
2. un **planning visuel** simple (Gantt ou roadmap datée, planifié vs réalisé) ;
3. **3-4 comptes rendus** courts (date · objectif · réalisé · reste à faire) — tu les tires de
   ton historique Git + ton journal.

### Phrase pour le jury

> *« Projet solo = gestion de projet à une personne : j'ai planifié avec ma feuille de route,
> suivi via Git, défini des règles de qualité et les ai appliquées en auto-revue, et tenu un
> journal de bord. Le référentiel prévoit justement la gestion complète en autonomie pour un
> petit projet. »*

⚠️ Ne **fabrique pas** de fausses réunions ni de faux coéquipiers. Assume le solo — c'est
défendable tel quel.

---

## C5 — Expression des besoins + maquettes (« j'ai recopié Twitter »)

### Les besoins : aucun problème

Un projet de formation s'auto-formule ses besoins, et prendre Twitter comme **référence
fonctionnelle** est du *benchmark* légitime. Tu n'inventes pas un besoin, tu pars d'un produit
connu et tu **réduis le périmètre** — c'est même une bonne démarche.

### Les maquettes : ça passe, avec 3 nuances

S'inspirer d'un produit éprouvé est une **décision UX saine**, pas un raccourci. Réutiliser des
patterns connus (fil, zone de saisie, réponses imbriquées) maximise l'**apprenabilité** et colle
aux principes du référentiel (« simplicité, minimalité »). Donc oui, **ça passe** — à condition
de :

1. **Produire TES maquettes, de TES écrans Solage** (Figma), pas montrer des captures de Twitter.
   Tu as codé l'app → tu maquettes tes propres écrans (login, fil, détail).
2. **Pas d'assets / logo / marque de Twitter** : ton app s'appelle Solage, avec sa propre
   identité. Inspiration de *layout* = ok ; copie d'éléments de marque = non.
3. **Ta contribution de conception, c'est l'adaptation** : périmètre réduit cohérent, charte
   minimaliste, et l'**enchaînement des écrans** (déjà fait dans le dossier).

### Phrase pour le jury

> *« J'ai réutilisé volontairement des conventions d'interface éprouvées plutôt que d'inventer
> une UI nouvelle — c'est un choix d'apprenabilité et de simplicité, pas un manque de travail.
> Ma conception, c'est l'adaptation : un périmètre réduit, une charte propre et l'enchaînement
> de mes écrans. »*

Si on te demande « tu as maquetté avant de coder ? », sois honnête :

> *« J'ai travaillé avec Twitter comme référence vivante ; j'ai formalisé les maquettes de mes
> écrans pour le dossier. »*

---

## Prochaines étapes possibles

- Réécrire la **section « Gestion de projet » du dossier** avec ce cadrage solo (ça la muscle et
  ajoute du volume légitime) **et** la section maquettes avec la justification « conventions
  éprouvées ».
- Ajouter ces **2 phrases de défense** (C4 et C5) au document
  `trucs-a-dire-aux-jurys-oral.md`.
- Produire les artefacts qui fiabilisent C4 (board Kanban, Gantt, comptes rendus) et C5
  (maquettes Figma de tes écrans).
