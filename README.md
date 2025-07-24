# Mini Framework PHP - MAXIT

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Packagist](https://img.shields.io/packagist/v/mapathe/maxit.svg)](https://packagist.org/packages/mapathe/maxit)

Un mini-framework PHP orienté objet inspiré de Laravel, offrant une architecture MVC complète avec routing, validation, gestion de base de données et système de dépendances.

## 🚀 Installation rapide

```bash
composer create-project mapathe/maxit mon-projet
cd mon-projet
cp .env.exemple .env
```

Configurez votre `.env` puis :

```bash
composer start  # Lance le serveur sur localhost:8000
```

## ✨ Fonctionnalités

- ✅ Architecture MVC complète
- ✅ Système de routing avec middlewares
- ✅ Validation robuste avec messages personnalisés
- ✅ Container de dépendances (DI)
- ✅ Support multi-base de données (MySQL, PostgreSQL, SQLite)
- ✅ Migrations et seeders intégrés
- ✅ Gestion des sessions sécurisée
- ✅ Autoloading PSR-4

## 📋 Configuration

### Variables d'environnement (.env)

```env
APP_URL=http://localhost:8000

# Base de données
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=ma_base
DB_USER=root
DB_PASSWORD=password

# Configuration Twilio (optionnel)
TWILIO_SID=your_sid
TWILIO_TOKEN=your_token
TWILIO_PHONE_NUMBER=+1234567890
```

## 🏗️ Structure du projet

```
mon-projet/
├── app/
│   ├── config/           # Configuration et DI
│   ├── core/            # Classes core du framework
│   ├── controllers/     # Vos contrôleurs
│   ├── entities/        # Vos modèles/entités
│   ├── repositories/    # Accès aux données
│   └── services/        # Services métier
├── migrations/          # Scripts SQL de migration
├── public/             # Point d'entrée web
├── routes/             # Définition des routes
├── seeders/            # Données de test
└── templates/          # Vues/templates
```

## 🛣️ Routing

Dans `routes/route.web.php` :

```php
use App\Controller\UserController;
use App\Core\Router;

// Routes simples
Router::get('/', HomeController::class, 'index');
Router::post('/users', UserController::class, 'store');

// Routes protégées par middleware
Router::get('/dashboard', UserController::class, 'dashboard', ['auth']);
Router::get('/admin', AdminController::class, 'index', ['auth', 'admin']);

// Routes avec paramètres
Router::get('/users/{id}', UserController::class, 'show');
```

## 🎯 Contrôleurs

```php
<?php
namespace App\Controller;

use App\Core\Abstract\AbstractController;
use App\Core\App;

class UserController extends AbstractController
{
    private $userRepository;
    
    public function __construct()
    {
        parent::__construct();
        $this->userRepository = App::getDependency('userRepository');
    }
    
    public function index(): void
    {
        $users = $this->userRepository->selectAll();
        
        $this->renderHtml('users/index.php', [
            'users' => $users,
            'title' => 'Liste des utilisateurs'
        ]);
    }
    
    public function store(): void
    {
        $data = $_POST;
        
        // Validation avec messages personnalisés
        $errors = Validator::validateWithMessages($data, [
            'nom' => [
                'required' => 'Le nom est obligatoire',
                'alpha_spaces' => 'Le nom ne peut contenir que des lettres'
            ],
            'email' => [
                'required' => 'L\'email est obligatoire',
                'email' => 'Format email invalide',
                'unique:userRepository,email' => 'Email déjà utilisé'
            ]
        ]);
        
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            header('Location: /users/create');
            return;
        }
        
        // Créer l'utilisateur
        $user = new User();
        $user->setNom($data['nom'])->setEmail($data['email']);
        
        if ($this->userRepository->insert($user)) {
            $this->session->set('success', 'Utilisateur créé !');
            header('Location: /users');
        }
    }
}
```

## ✅ Validation

### Règles disponibles

```php
use App\Core\Validator;

$errors = Validator::validateWithMessages($_POST, [
    'nom' => [
        'required' => 'Le nom est obligatoire',
        'alpha_spaces' => 'Lettres et espaces uniquement',
        'min_length:2' => 'Minimum 2 caractères'
    ],
    'email' => [
        'required' => 'Email obligatoire',
        'email' => 'Format email invalide',
        'unique:userRepository,email' => 'Email déjà utilisé'
    ],
    'telephone' => [
        'phone_senegal' => 'Format téléphone sénégalais requis (+221...)'
    ],
    'cni' => [
        'cni_senegal' => 'CNI doit contenir 13 chiffres'
    ],
    'photo' => [
        'file_image' => 'Fichier doit être une image (JPG, PNG)',
        'file_max_size:5242880' => 'Taille max 5MB'
    ]
]);
```

### Règles personnalisées

```php
Validator::addRule('mot_de_passe_fort', function($value, $key, $message) {
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $value) ? true : $message;
});
```

## 🗄️ Entités et Repositories

### Entité

```php
<?php
namespace App\Entity;

use App\Core\Abstract\AbstractEntity;

class User extends AbstractEntity
{
    private ?int $id = null;
    private string $nom = '';
    private string $email = '';
    
    // Getters/Setters...
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): self { $this->id = $id; return $this; }
    
    public static function toObject(array $data): static
    {
        return (new static())
            ->setId($data['id'] ?? null)
            ->setNom($data['nom'] ?? '')
            ->setEmail($data['email'] ?? '');
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'email' => $this->email
        ];
    }
}
```

### Repository

```php
<?php
namespace App\Repository;

use App\Core\Abstract\AbstractRepository;
use App\Core\Abstract\Database;
use App\Entity\User;

class UserRepository extends AbstractRepository
{
    private \PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }
    
    public function selectAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM users");
        return array_map([User::class, 'toObject'], $stmt->fetchAll());
    }
    
    public function insert(User $user): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (nom, email) VALUES (?, ?)"
        );
        return $stmt->execute([$user->getNom(), $user->getEmail()]);
    }
    
    public function isUnique(string $field, $value): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE $field = ?");
        $stmt->execute([$value]);
        return $stmt->fetchColumn() == 0;
    }
    
    // Autres méthodes CRUD...
}
```

## 🛡️ Middlewares

### Middleware d'authentification

```php
<?php
namespace App\Core\Middlewares;

class Auth
{
    public function __invoke(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user'])) {
            header('Location: /');
            exit();
        }
    }
}
```

### Enregistrement dans `app/config/middlewares.php`

```php
return [
    'auth' => Auth::class,
    'admin' => AdminOnly::class,
];
```

## 👤 Sessions

```php
use App\Core\Session;

$session = Session::getInstance();

// Définir/récupérer des valeurs
$session->set('user_id', 123);
$userId = Session::get('user_id');

// Vérifications
if ($session->has('user')) {
    // Utilisateur connecté
}

// Protection de route
Session::requireAuth(); // Redirige si non connecté
```

## 📦 Container de dépendances

### Configuration dans `app/config/dependencies.php`

```php
return [
    "core" => [
        "database" => fn() => Database::getConnection(),
        "session" => fn() => Session::getInstance(),
    ],
    
    "repositories" => [
        "userRepository" => fn() => new UserRepository(),
    ],
    
    "services" => [
        "emailService" => fn() => new EmailService(),
    ],
    
    "controllers" => [
        "userController" => fn() => new UserController(),
    ]
];
```

### Utilisation

```php
use App\Core\App;

$userRepository = App::getDependency('userRepository');
$emailService = App::getDependency('emailService');
```

## 🗃️ Migrations & Seeders

### Migration (`migrations/001_users.sql`)

```sql
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
```

### Seeder (`seeders/001_users.sql`)

```sql
INSERT INTO users (nom, email) VALUES
('Jean Dupont', 'jean@email.com'),
('Marie Martin', 'marie@email.com');
```

### Commandes

```bash
# Exécuter migrations
composer run database:migrate

# Avec reset (supprime tout et recrée)
composer run database:migrate -- --reset

# Exécuter seeders
composer run seeder:migrate
```

## 🎨 Templates

### Layout de base (`templates/layout/base.layout.php`)

```php
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Mon App' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">Mon App</a>
        </div>
    </nav>
    
    <main class="container mt-4">
        <?= $contentForLayout ?>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

### Vue (`templates/users/index.php`)

```php
<h1><?= $title ?></h1>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<a href="/users/create" class="btn btn-primary mb-3">Ajouter un utilisateur</a>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user->getId() ?></td>
            <td><?= htmlspecialchars($user->getNom()) ?></td>
            <td><?= htmlspecialchars($user->getEmail()) ?></td>
            <td>
                <a href="/users/<?= $user->getId() ?>" class="btn btn-sm btn-info">Voir</a>
                <a href="/users/<?= $user->getId() ?>/edit" class="btn btn-sm btn-warning">Modifier</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

## 🚀 Commandes CLI

```bash
# Démarrer le serveur de développement
composer start

# Exécuter les migrations
composer run database:migrate
composer run database:migrate -- --reset

# Exécuter les seeders
composer run seeder:migrate
composer run seeder:migrate -- --reset
```

## 🔧 Exemple complet : Système d'authentification

### 1. Route

```php
Router::post('/login', SecurityController::class, 'login');
Router::get('/dashboard', UserController::class, 'dashboard', ['auth']);
```

### 2. Contrôleur

```php
public function login(): void
{
    $errors = Validator::validateWithMessages($_POST, [
        'email' => [
            'required' => 'Email obligatoire',
            'email' => 'Format email invalide'
        ],
        'password' => ['required' => 'Mot de passe obligatoire']
    ]);
    
    if (empty($errors)) {
        $user = $this->userRepository->findByEmail($_POST['email']);
        
        if ($user && password_verify($_POST['password'], $user->getPassword())) {
            $this->session->set('user', $user->toArray());
            header('Location: /dashboard');
            return;
        }
        
        $errors['login'] = 'Identifiants incorrects';
    }
    
    $this->session->set('errors', $errors);
    header('Location: /');
}
```

### 3. Template de connexion

```php
<form method="POST" action="/login">
    <div class="mb-3">
        <input type="email" name="email" class="form-control" 
               placeholder="Email" required>
    </div>
    <div class="mb-3">
        <input type="password" name="password" class="form-control" 
               placeholder="Mot de passe" required>
    </div>
    <button type="submit" class="btn btn-primary">Se connecter</button>
</form>

<?php if (isset($_SESSION['errors'])): ?>
    <div class="alert alert-danger mt-3">
        <?php foreach ($_SESSION['errors'] as $error): ?>
            <div><?= $error ?></div>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>
```

## 📝 Support multi-base de données

Le framework supporte nativement :

- **MySQL** : `DB_DRIVER=mysql`
- **PostgreSQL** : `DB_DRIVER=pgsql`  
- **SQLite** : `DB_DRIVER=sqlite` (avec `DB_PATH`)

## 🔒 Sécurité

- Protection CSRF intégrée dans les formulaires
- Validation et échappement automatique des données
- Sessions sécurisées
- Support des mots de passe hashés
- Middlewares de protection des routes

## 📚 Ressources

- [Packagist](https://packagist.org/packages/mapathe/maxit)
- [Documentation API](docs/)
- [Exemples d'utilisation](examples/)

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :

1. Fork le projet
2. Créer une branche (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit vos changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request


## 👨‍💻 Auteur

**Mapathe Ndiaye**
- Email: mapathendiaye542@gmail.com
- GitHub: [@mapathe](https://github.com/mapathe)

---

⭐ **N'oubliez pas de donner une étoile si ce projet vous aide !**