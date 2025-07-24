<?php

namespace App\Core;

class App
{
    private static array $dependencies = [];
    private static array $config = [];
    private static bool $configLoaded = false;

    public static function getDependencies(): array
    {
        if (!self::$configLoaded) {
            self::$config = require dirname(__DIR__) . '/config/dependencies.php';
            self::$configLoaded = true;
        }
        return self::$config;
    }

    public static function getDependency(string $key): mixed
    {
        // Si la dépendance est déjà instanciée, la retourner
        if (isset(self::$dependencies[$key])) {
            return self::$dependencies[$key];
        }

        // Charger la configuration si pas encore fait
        if (!self::$configLoaded) {
            self::getDependencies();
        }

        // Chercher dans toutes les catégories
        foreach (self::$config as $category => $items) {
            if (isset($items[$key])) {
                // Exécuter la closure et stocker le résultat
                self::$dependencies[$key] = $items[$key]();
                return self::$dependencies[$key];
            }
        }

        throw new \Exception("La dépendance '$key' est introuvable.");
    }

    // Méthode pour compatibilité (avec la faute de frappe)
    // public static function getDependencie(string $key): mixed
    // {
    //     return self::getDependency($key);
    // }

    // // Méthode pour compatibilité (première ligne de votre constructeur)
    // public static function getdependencie(string $key): mixed
    // {
    //     return self::getDependency($key);
    // }
}
