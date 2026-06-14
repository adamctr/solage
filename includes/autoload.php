<?php

declare(strict_types=1);

class Autoloader
{
    /**
     * Enregistre l'autoloader auprès de la SPL.
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Charge le fichier d'une classe en la cherchant dans les dossiers du projet.
     *
     * @param string $class Nom de la classe à charger.
     * @return void
     */
    public static function autoload($class)
    {
        $paths = [
            __DIR__ . '/../routes/' . str_replace('\\', '/', $class) . '.php',
            __DIR__ . '/../modules/views/' . str_replace('\\', '/', $class) . '.php',
            __DIR__ . '/../modules/models/' . str_replace('\\', '/', $class) . '.php',
            __DIR__ . '/../modules/controllers/' . str_replace('\\', '/', $class) . '.php',
            __DIR__ . '/../modules/validators/' . str_replace('\\', '/', $class) . '.php',
            __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php'
        ];

        foreach ($paths as $file) {
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
}
