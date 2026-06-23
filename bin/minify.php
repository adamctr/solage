<?php

declare(strict_types=1);

// CLI : génère les assets minifiés (CSS/JS) une seule fois, au build de l'image.
// Lancé par le Dockerfile. En production, Config sert ces fichiers (cf. src/Config.php) ;
// en développement on sert les sources non minifiées, pour le rechargement à chaud.

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../includes/autoload.php';
Autoloader::register();

$outputDir = __DIR__ . '/../public/assets/minified';
if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
    fwrite(STDERR, "Impossible de créer $outputDir\n");
    exit(1);
}

(new MinificationController())->minifyAssets();
echo "Assets minifiés -> public/assets/minified/\n";
