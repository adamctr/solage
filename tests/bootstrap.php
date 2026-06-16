<?php

declare(strict_types=1);

/**
 * Amorçage des tests : on reproduit l'ordre de public/index.php.
 *   1. autoloader Composer (PHPUnit, phpdotenv, psr/log) ;
 *   2. autoloader maison (classes de l'app : src/, modules/*, routes/) ;
 *   3. Database, qui vit dans includes/ (hors des dossiers balayés par l'autoloader).
 *
 * L'include de Database n'ouvre aucune connexion : elle est paresseuse
 * (Database::getConnection() au premier appel, dans les tests d'intégration).
 */

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../includes/autoload.php';
Autoloader::register();

require __DIR__ . '/../includes/database.php';
