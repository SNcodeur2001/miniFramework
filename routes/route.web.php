<?php

use App\Controller\CompteController;
use App\Controller\SecurityController;
use App\Core\Router;

// Routes publiques (pour les invités uniquement)
Router::get('/', SecurityController::class, 'index');
Router::post('/login', SecurityController::class, 'login');
Router::post('/register', CompteController::class, 'register');

// Routes protégées (nécessitent une authentification)
Router::get('/dashboard-client', CompteController::class, 'showDashboardClient', ['auth']);
Router::get('/dashboard-gestionnaire', CompteController::class, 'showAllComptes', ['auth']);

Router::get('/transactions', CompteController::class, 'showTransactions', ['auth']);
Router::get('/logout', CompteController::class, 'logout', ['auth']);

// Formulaire de recherche de compte (par un gestionnaire)
Router::get('/recherche-compte', CompteController::class, 'searchForm', ['auth']);
// Ajouter cette ligne avec les autres routes CLIENT
Router::get('/mes-transactions', CompteController::class, 'showMesTransactions', ['auth']);

// Traitement du formulaire (POST)
Router::post('/recherche-compte', CompteController::class, 'handleSearch', ['auth']);
// Routes pour la gestion des comptes (ajouter avec les autres routes CLIENT)
Router::get('/gestion-comptes', CompteController::class, 'showGestionComptes', ['auth']);



Router::post('/make-compte-principal', CompteController::class, 'makeComptePrincipal', ['auth']);

// Voir toutes les transactions d’un compte spécifique avec filtres (par ID)
Router::get('/compte/{id}/detail', CompteController::class, 'showCompteDetail', ['auth']);
Router::get('/compte/{id}/transactions', CompteController::class, 'showTransactions', ['auth']);
Router::get('/comptes', CompteController::class, 'showAllComptes', ['auth']);
Router::get('/ajouter-compte-secondaire', CompteController::class, 'showAddSecondaryAccount', ['auth']);
Router::post('/ajouter-compte-secondaire', CompteController::class, 'handleAddSecondaryAccount', ['auth']);
Router::post('/basculer-compte', CompteController::class, 'switchAccount', ['auth']);

