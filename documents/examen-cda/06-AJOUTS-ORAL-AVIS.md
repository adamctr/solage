# Combler les 15 min manquantes — avis sur tes 5 idées

> Contexte : simulation à **25 min**, cible **40 min**. Il manque ~15 min.
> Bonne nouvelle : un déficit se corrige plus facilement qu'un dépassement (tu ajoutes
> du contenu que tu maîtrises, tu ne coupes rien dans la panique).
>
> ⚠️ **Tout ce qui suit est vérifié dans le repo réel** (présence/absence des fichiers).
> La CI a depuis été construite (run vert) → ce n'est plus un piège mais un atout. Il reste
> **une** idée qui repose sur un artefact **inexistant** (Lighthouse/GreenIT) → piège.

---

## Verdict en un coup d'œil

| Idée | Verdict | Minutes gagnées | Existe dans le repo ? |
|---|---|---|---|
| **Simuler attaques CSRF / IDOR (échouent)** | ⭐ **À FAIRE, priorité 1** | +4 à 6 min | ✅ code présent (middlewares + check ownership) |
| **Parler du Router** | ✅ Garder, mais serré | +2 à 3 min | ✅ `src/Router.php` |
| **`Database.php`** | ✅ Garder (angle sécu/secrets) | +1 à 2 min | ✅ `includes/database.php` |
| **Autoloader** | ⚠️ Risqué — en passant seulement | +0 à 1 min | ✅ `includes/autoload.php` (mais piège, voir plus bas) |
| **Pipeline CI/CD** | ✅ **FAIT** — atout à montrer | +3 à 4 min | ✅ `.github/workflows/ci.yml`, run vert |
| **Lighthouse / GreenIT** | ⛔ **PIÈGE** — aucun rapport | 0 tant que non produit | ❌ rien (juste cité dans le référentiel) |

---

## Le détail, idée par idée

### 1. ⭐ Simuler les attaques CSRF / IDOR — TON MEILLEUR AJOUT

C'est le levier le plus rentable **et** le plus impressionnant. Un jury retient une démo
d'attaque qui échoue bien plus qu'un discours.

**Ce que tu montres en live (terminal ou Postman) :**
- **CSRF** : un POST sur `/api/like` ou `/api/posts/delete` **sans** le header `X-CSRF-Token`
  (ou avec un token bidon) → **403 + log `csrf.denied`**. Puis le même POST **avec** le bon
  token → succès. La différence est visible à l'écran.
- **IDOR** : connecté en utilisateur lambda, tu tentes de supprimer le post d'un autre / éditer
  un profil qui n'est pas le tien en changeant l'`id` → **403 + log**. Tu montres la ligne du
  fix : `current === target || isAdmin()`.

> **Phrase jury** : « Je ne vous décris pas la faille, je vous la joue : voici la requête
> malveillante, voici le 403. La mutation est impossible sans le secret de session, et l'accès
> à la ressource d'autrui est refusé par le contrôle de propriété. »

**Pourquoi ça marche** : tu racontes le **bug AVANT le fix** (« avant, je changeais l'`id` et je
supprimais le post de n'importe qui »), puis tu prouves que c'est mort. Récit problème → preuve.

➡️ **+4 à 6 min de contenu de très haute qualité.** Prépare les requêtes à l'avance (un petit
script `curl` ou une collection Postman) pour ne pas bricoler en direct.

---

### 2. ✅ Le Router — oui, mais tenu en laisse

`src/Router.php` est du code que tu as écrit → légitime. Il porte un point fort sécurité :
**c'est lui qui arme le CSRF sur tous les POST** (le CSRF est global au router, pas par-route ;
Auth/Admin restent par-route). Ça, c'est un bon angle.

> **Phrase jury** : « Mon router mappe URL → `Controller#method`, exécute les middlewares de
> route (Auth/Admin) et **arme le rejet CSRF sur tout POST**. Un POST sans token n'atteint
> jamais le contrôleur. »

⚠️ **Le risque** : trop de plomberie framework = le jury se demande où est l'**application**.
Le CDA évalue le développement applicatif, pas ta capacité à réécrire Symfony.
→ **Max 2-3 min**, et enchaîne vite sur ce que le router *protège* (la démo CSRF du point 1).

---

### 3. ✅ `Database.php` — angle sécurité, pas plomberie

`includes/database.php` est défendable **si tu le cadres sur la sécurité**, pas sur le pattern :
- secrets chargés via `phpdotenv` depuis `.env` **gitignoré** (jamais en dur) ;
- **fail-fast** : si une variable d'env manque → `RuntimeException` au démarrage ;
- PDO en `ERRMODE_EXCEPTION` + **`EMULATE_PREPARES => false`** (vraies requêtes préparées côté
  serveur → pas d'injection SQL).

> **Phrase jury** : « Une seule porte vers la base, des secrets hors du dépôt, et des requêtes
> réellement préparées côté serveur — `EMULATE_PREPARES` à `false`. L'injection SQL est fermée
> structurellement. »

⚠️ **Ne défends PAS le singleton pour lui-même** : si on te demande « pourquoi un singleton ? »,
réponds usage (« une connexion par requête, simple, suffisant pour ce périmètre ») et n'en fais
pas un argument d'architecture. → **1 à 2 min.**

---

### 4. ⚠️ L'autoloader — le plus faible, à mentionner en passant

`includes/autoload.php` cherche les classes dans `routes/`, `modules/*`, `src/`. Ça marche, mais :

**C'est un piège à questions.** Le jury peut demander :
- *« Pourquoi un autoloader maison alors que vous utilisez déjà Composer pour `vendor/` ? »*
- *« Pourquoi pas du PSR-4 ? »* → réponse honnête : tes classes applicatives **ne sont pas
  namespacées** (le nom de classe = nom de fichier, cherché dans plusieurs dossiers). Ça expose
  que l'app n'a pas de namespaces — un point qu'on pourrait te reprocher.

➡️ **Ne bâtis pas une slide dessus.** Une phrase en passant suffit : « Composer autoloade les
dépendances ; un petit autoloader SPL maison résout mes classes applicatives. » Si on creuse,
tu assumes le choix de simplicité. **N'essaie pas d'en faire un temps fort** — c'est du temps à
risque pour peu de valeur.

---

### 5. ✅ Pipeline CI/CD — FAIT : à présenter en vérité

**Vérifié : `.github/workflows/ci.yml` en place, run vert.** À chaque push, deux jobs : `qualite`
(PSR-12 + PHPStan + PHPUnit contre une PostgreSQL éphémère) et `image` (`docker build`). **C11 est
passée à *Validé***, et le dossier (`04e-tests.tex`) documente le pipeline avec la capture du run
vert et son interprétation.

**Double gain encaissé :**
1. une **compétence validée** (compte dans la note, pas juste dans le temps de parole) ;
2. **du contenu de démo** : tu montres le run vert et tu **interprètes le rapport** (+3 à 4 min).

> **Phrase jury** : « À chaque push, GitHub Actions vérifie le style, l'analyse statique et les
> tests sur une base jetable, puis construit l'image. Run vert = aucune régression n'entre dans la
> branche ; run rouge = l'étape fautive est pointée et je corrige avant d'intégrer. »

⚠️ **Distingue bien CI et CD** : tu as l'**intégration** continue (vérif à chaque push) ; le
**déploiement** continu reste manuel et documenté. Savoir l'expliquer suffit — n'annonce pas un CD
que tu n'as pas.

---

### 6. ⛔ Lighthouse / GreenIT — PIÈGE : aucun rapport

**Vérifié : aucun rapport Lighthouse ni GreenIT/EcoIndex dans le repo.** Ces termes
n'apparaissent que dans `REAC.txt` (le référentiel officiel), pas comme livrables.

Même règle que la CI : **ne montre pas un score que tu n'as pas mesuré.**

**MAIS — c'est le quick win le plus facile de la liste** (artefacts produits en ~10 min) :
- **Lighthouse** : intégré à Chrome DevTools (onglet *Lighthouse* → *Analyze*). Tu lances sur le
  fil et le profil, tu exportes le rapport (perf, accessibilité, bonnes pratiques, SEO).
- **GreenIT / EcoIndex** : `ecoindex.fr` ou l'extension *GreenIT-Analysis* → note éco + grammes
  de CO₂ par page.

➡️ **Produis les rapports d'abord** (capture + 2 chiffres clés à citer). Alors seulement ça
devient un atout (accessibilité + éco-conception, deux axes que le jury aime). **Sans le
rapport, tu te tais.** Si les scores sont mauvais, soit tu corriges, soit tu n'en parles pas —
un mauvais score affiché se retourne contre toi.

---

## Plan recommandé pour combler les 15 min

**Ordre de priorité (du plus sûr au plus risqué) :**

| # | Ajout | Effort avant l'oral | Min gagnées | Condition |
|---|---|---|---|---|
| 1 | **Démo CSRF + IDOR en live** | préparer les requêtes (~20 min) | **+5** | ✅ rien à construire, le code existe |
| 2 | **Démo fonctionnelle complète** (inscription→post→like→recherche→admin) | scénario à dérouler | **+4** | déjà dans ton app |
| 3 | **Tradeoffs / périmètre assumé** (1 phrase « X plutôt que Y car Z » par choix) | aucun | **+3** | tu sais déjà |
| 4 | **Router** (angle « arme le CSRF ») + **Database.php** (angle sécu) | aucun | **+3** | ✅ fichiers présents |
| 5 | **CI/CD** (run vert montré + interprété) | aucun (déjà construit) | **+3 à 4** | ✅ pipeline en place, run vert |
| 6 | **Lighthouse + EcoIndex** (2 chiffres) | **produire ~10 min** | **+2** | ⚠️ SEULEMENT si tu produis les rapports |

**Si tu ne fais rien d'autre :** points 1 + 2 + 3 + 4 = **~+15 min** → tu es à 40, **sans rien
construire**, uniquement avec ce qui existe déjà. C'est ton filet de sécurité.

**Le point 5 (CI/CD) est déjà construit** — il **valide une compétence** en plus de remplir du
temps, intègre-le sans hésiter. Le point 6 (Lighthouse/GreenIT) est le bonus le plus rapide qui
reste à produire.

**À NE PAS faire :** une slide dédiée à l'autoloader, et surtout **ne jamais annoncer Lighthouse
sans le rapport sous la main** (la CI, elle, a son run vert).

---

## Le piège qui reste : Lighthouse

CI/CD et Lighthouse correspondent à de **vraies compétences que le jury évalue depuis le dossier
de toute façon** (C11, accessibilité, éco-conception). Pour la **CI, l'artefact est produit** (run
vert) → tu peux la montrer sans risque. Pour **Lighthouse, le principe tient** : produis le rapport
d'abord, ensuite seulement tu le montres. La différence entre un atout et un piège, c'est
l'artefact sous la main.
