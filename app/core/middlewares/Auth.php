<?php

namespace App\Core\Middlewares;

class Auth
{
    public function __invoke(): void
    {
        // Démarrer la session si pas encore fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            // Rediriger vers la page de connexion
            header('Location: /');
            exit();
        }

        // Optionnel : Vérifier que les données utilisateur sont complètes
        if (!isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
            // Session corrompue, déconnecter
            session_destroy();
            header('Location: /');
            exit();
        }

        // Optionnel : Vérifier si le compte est toujours actif
        $this->validateUserStatus();
    }


    /**
     * Valider le statut de l'utilisateur
     */
    private function validateUserStatus(): void
    {
        try {
            // Si vous voulez vérifier en base que l'utilisateur est toujours actif
            if (isset($_SESSION['user']['statut_compte']) && 
                $_SESSION['user']['statut_compte'] !== 'ACTIF') {
                
                session_destroy();
                header('Location: /?error=compte_inactif');
                exit();
            }
        } catch (\Exception $e) {
            // En cas d'erreur, on log mais on laisse passer
            error_log("Erreur validation statut utilisateur: " . $e->getMessage());
        }
    }
}
