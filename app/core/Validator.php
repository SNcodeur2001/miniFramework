<?php

namespace App\Core;

class Validator
{
    private $errors = [];
    
    // Déclaration sans typage pour compatibilité PHP < 7.4
    private static $rules = [];
    
    // Initialisation des règles dans une méthode statique
    private static function initRules()
    {
        if (empty(self::$rules)) {
            self::$rules = [
                'required' => function($value, $key, $message) {
                    return !empty(trim($value)) ? true : $message;
                },
                
                'email' => function($value, $key, $message) {
                    return filter_var($value, FILTER_VALIDATE_EMAIL) ? true : $message;
                },
                
                'phone_senegal' => function($value, $key, $message) {
                    $pattern = '/^(\+221|7[056789])[0-9]{7}$/';
                    return preg_match($pattern, $value) ? true : $message;
                },
                
                'cni_senegal' => function($value, $key, $message) {
                    $pattern = '/^[0-9]{13}$/';
                    return preg_match($pattern, $value) ? true : $message;
                },
                
                // ✨ Nouvelle règle unique générique
                'unique' => function($value, $key, $message, $params = [], $data = []) {
                    try {
                        // Parser les paramètres: "userRepository,telephone"
                        $paramArray = explode(',', (string)$params);
                        $repository = $paramArray[0] ?? '';
                        $field = $paramArray[1] ?? $key;
                        
                        if (empty($repository)) {
                            return true;
                        }
                        
                        // Utiliser le container de dépendances
                        $repo = \App\Core\App::getDependency($repository);
                        
                        // Appeler la méthode isUnique du repository
                        return $repo->isUnique($field, $value) ? true : $message;
                        
                    } catch (\Exception $e) {
                        error_log("Erreur validation unique: " . $e->getMessage());
                        return true;
                    }
                },
                
                'min_length' => function($value, $key, $message, $params = []) {
                    $minLength = isset($params['length']) ? $params['length'] : 3;
                    return strlen(trim($value)) >= $minLength ? true : $message;
                },
                
                'max_length' => function($value, $key, $message, $params = []) {
                    $maxLength = isset($params['length']) ? $params['length'] : 255;
                    return strlen(trim($value)) <= $maxLength ? true : $message;
                },
                
                'alpha_spaces' => function($value, $key, $message) {
                    $pattern = '/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'-]+$/';
                    return preg_match($pattern, $value) ? true : $message;
                },
                
                'alpha_numeric' => function($value, $key, $message) {
                    $pattern = '/^[A-Za-z0-9]+$/';
                    return preg_match($pattern, $value) ? true : $message;
                },
                
                'file_required' => function($value, $key, $message, $params = [], $data = []) {
                    return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK ? true : $message;
                },
                
                'file_image' => function($value, $key, $message, $params = []) {
                    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
                        return true;
                    }
                    
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    $fileType = $_FILES[$key]['type'];
                    
                    return in_array($fileType, $allowedTypes) ? true : $message;
                },
                
                'file_max_size' => function($value, $key, $message, $params = []) {
                    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
                        return true;
                    }
                    
                    $maxSize = isset($params['size']) ? $params['size'] : (5 * 1024 * 1024);
                    $fileSize = $_FILES[$key]['size'];
                    
                    return $fileSize <= $maxSize ? true : $message;
                },
                
                'accepted' => function($value, $key, $message) {
                    return in_array($value, ['1', 'on', 'yes', 'true', true, 1], true) ? true : $message;
                },
                
                'numeric' => function($value, $key, $message) {
                    return is_numeric($value) ? true : $message;
                }
            ];
        }
    }

    public function __construct()
    {
        $this->errors = [];
    }

    /**
     * ✨ NOUVELLE MÉTHODE : Validation avec règles et messages regroupés
     */
    public static function validateWithMessages($data, $validationRules)
    {
        // Initialiser les règles si nécessaire
        self::initRules();
        
        $validator = new self();
        
        foreach ($validationRules as $field => $rules) {
            $value = isset($data[$field]) ? $data[$field] : '';
            
            foreach ($rules as $rule => $message) {
                $result = $validator->applyRuleWithMessage($field, $value, $rule, $message, $data);
                
                if ($result !== true) {
                    $validator->addError($field, $result);
                    break; // Arrêter à la première erreur pour ce champ
                }
            }
        }
        
        return $validator->getErrors();
    }

    /**
     * ✨ NOUVELLE MÉTHODE : Applique une règle avec son message
     */
    private function applyRuleWithMessage($field, $value, $rule, $message, $data)
    {
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $params = [];
        
        // Parser les paramètres selon le type de règle
        if (isset($ruleParts[1])) {
            switch ($ruleName) {
                case 'min_length':
                case 'max_length':
                    $params['length'] = (int)$ruleParts[1];
                    break;
                case 'file_max_size':
                    $params['size'] = (int)$ruleParts[1];
                    break;
                case 'unique':
                    $params = $ruleParts[1]; // "userRepository,telephone"
                    break;
            }
        }
        
        if (isset(self::$rules[$ruleName])) {
            return self::$rules[$ruleName]($value, $field, $message, $params, $data);
        }
        
        return true; // Règle inconnue = valide
    }

    /**
     * Méthode statique principale pour valider les données (ANCIENNE - pour compatibilité)
     */
    public static function validate($data, $rules, $messages = [])
    {
        // Initialiser les règles si nécessaire
        self::initRules();
        
        $validator = new self();
        
        foreach ($rules as $field => $fieldRules) {
            $value = isset($data[$field]) ? $data[$field] : '';
            
            if (is_string($fieldRules)) {
                $fieldRules = explode('|', $fieldRules);
            }
            
            foreach ($fieldRules as $rule) {
                $validator->applyRule($field, $value, $rule, $messages, $data);
            }
        }
        
        return $validator->getErrors();
    }

    /**
     * Applique une règle de validation spécifique (ANCIENNE MÉTHODE)
     */
    private function applyRule($field, $value, $rule, $messages, $data)
    {
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $params = [];
        
        if (isset($ruleParts[1])) {
            switch ($ruleName) {
                case 'min_length':
                case 'max_length':
                    $params['length'] = (int)$ruleParts[1];
                    break;
                case 'file_max_size':
                    $params['size'] = (int)$ruleParts[1];
                    break;
                case 'unique':
                    $params = $ruleParts[1];
                    break;
            }
        }
        
        $messageKey = "{$field}.{$ruleName}";
        $defaultMessage = $this->getDefaultMessage($field, $ruleName, $params);
        $message = isset($messages[$messageKey]) ? $messages[$messageKey] : 
                  (isset($messages[$field]) ? $messages[$field] : $defaultMessage);
        
        if (isset(self::$rules[$ruleName])) {
            $result = self::$rules[$ruleName]($value, $field, $message, $params, $data);
            
            if ($result !== true) {
                $this->addError($field, $result);
            }
        }
    }

    /**
     * Génère un message d'erreur par défaut
     */
    private function getDefaultMessage($field, $rule, $params = [])
    {
        $fieldName = $this->getFieldDisplayName($field);
        
        $defaultMessages = [
            'required' => "Le champ {$fieldName} est obligatoire.",
            'email' => "Le champ {$fieldName} doit être une adresse email valide.",
            'phone_senegal' => "Le champ {$fieldName} doit être un numéro de téléphone sénégalais valide.",
            'cni_senegal' => "Le numéro CNI doit contenir exactement 13 chiffres (format sénégalais).",
            'cni_unique' => "Ce numéro de CNI est déjà utilisé par un autre compte.",
            'phone_unique' => "Ce numéro de téléphone est déjà utilisé par un autre compte.",
            'min_length' => "Le champ {$fieldName} doit contenir au moins " . (isset($params['length']) ? $params['length'] : 3) . " caractères.",
            'max_length' => "Le champ {$fieldName} ne peut pas dépasser " . (isset($params['length']) ? $params['length'] : 255) . " caractères.",
            'alpha_spaces' => "Le champ {$fieldName} ne peut contenir que des lettres et espaces.",
            'alpha_numeric' => "Le champ {$fieldName} ne peut contenir que des lettres et des chiffres.",
            'file_required' => "Le fichier {$fieldName} est obligatoire.",
            'file_image' => "Le fichier {$fieldName} doit être une image (JPG, JPEG, PNG).",
            'file_max_size' => "Le fichier {$fieldName} ne peut pas dépasser " . $this->formatFileSize(isset($params['size']) ? $params['size'] : (5 * 1024 * 1024)) . ".",
            'accepted' => "Vous devez accepter {$fieldName}.",
            'numeric' => "Le champ {$fieldName} doit être numérique."
        ];
        
        return isset($defaultMessages[$rule]) ? $defaultMessages[$rule] : "Le champ {$fieldName} n'est pas valide.";
    }

    /**
     * Convertit le nom du champ en nom d'affichage
     */
    private function getFieldDisplayName($field)
    {
        $displayNames = [
            'prenom' => 'Prénom',
            'nom' => 'Nom',
            'adresse' => 'Adresse',
            'telephone' => 'Téléphone',
            'loginTelephone' => 'Téléphone',
            'numero_piece_identite' => 'Numéro de pièce d\'identité',
            'photo_recto' => 'Photo recto',
            'photo_verso' => 'Photo verso',
            'terms' => 'les conditions d\'utilisation'
        ];
        
        return isset($displayNames[$field]) ? $displayNames[$field] : ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Formate la taille de fichier en format lisible
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /**
     * Ajouter une règle de validation personnalisée
     */
    public static function addRule($name, $callback)
    {
        self::initRules();
        self::$rules[$name] = $callback;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function addError($key, $message)
    {
        $this->errors[$key] = $message;
    }

    public function isValid()
    {
        return empty($this->errors);
    }

    // Méthodes de compatibilité avec l'ancien code
    public function isEmail($key, $value, $message)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($key, $message);
        }
    }

    public function isEmpty($key, $value, $message)
    {
        if (empty($value)) {
            $this->addError($key, $message);
        }
    }
}
