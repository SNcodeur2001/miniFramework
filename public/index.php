<?php
use App\Core\Router;
// Affichage des erreurs (optionnel, à désactiver en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment configuration FIRST
require_once __DIR__ . '/../app/config/bootstrap.php';
// Now load routes
require_once __DIR__ . '/../app/core/Router.php';
Router::resolve();