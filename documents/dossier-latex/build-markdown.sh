#!/usr/bin/env bash
# =============================================================================
#  build-markdown.sh — exporte le dossier LaTeX en Markdown (GitHub-flavored)
#
#  Usage :  bash build-markdown.sh
#  Sortie : main.md   (à côté de main.pdf — le PDF n'est pas touché)
#
#  Ce que fait le script :
#   1. copie les sources dans un dossier temporaire (build/) ;
#   2. remplace les colonnes maison L{..}/C{..} par p{..} pour que pandoc
#      convertisse les tableaux tabularx en vrais tableaux Markdown ;
#   3. réinjecte les libellés des boîtes jury/info/todo (perdus sinon, car
#      définis dans le tcolorbox que pandoc ne lit pas) ;
#   4. lance pandoc (LaTeX -> GFM) ;
#   5. préfixe les images par maquettes/ pour qu'elles s'affichent.
#
#  Limite assumée : les schémas TikZ (archi, MCD, MLD, UML) ne se convertissent
#  pas en Markdown — seule leur légende est conservée. Pour ces schémas,
#  référez-vous au main.pdf.
# =============================================================================
set -euo pipefail
cd "$(dirname "$0")"

PANDOC="${PANDOC:-$HOME/bin/pandoc.exe}"
command -v "$PANDOC" >/dev/null 2>&1 || PANDOC="pandoc"

BUILD=build
rm -rf "$BUILD"
mkdir -p "$BUILD/chapters"
cp main.tex preamble.tex titlepage.tex "$BUILD/"
cp chapters/*.tex "$BUILD/chapters/"

# 2a. neutralise les \input{figures/...} (schémas TikZ non convertibles ;
#     leur légende, définie dans le chapitre, est conservée)
sed -i 's/\\input{figures\/[^}]*}//g' "$BUILD"/chapters/*.tex

# 2b. colonnes maison -> p{} (n'apparaissent que dans les specs tabularx)
sed -i 's/L{/p{/g; s/C{/p{/g' "$BUILD"/chapters/*.tex

# 3. libellés des boîtes (le contenu de l'argument de todo reste en tête)
sed -i 's/\\begin{jury}/\\begin{jury}\\textbf{Réponse jury —} /g' "$BUILD"/chapters/*.tex
sed -i 's/\\begin{info}/\\begin{info}\\textbf{Note —} /g'           "$BUILD"/chapters/*.tex
sed -i 's/\\begin{todo}{/\\begin{todo}{\\textbf{À développer —} /g'  "$BUILD"/chapters/*.tex

# 4. conversion
( cd "$BUILD" && "$PANDOC" main.tex -f latex -t gfm --wrap=none -o ../main.md )

# 5. chemins d'images
sed -i 's#src="#src="maquettes/#g' main.md

rm -rf "$BUILD"
echo "OK -> main.md ($(wc -l < main.md) lignes)"
