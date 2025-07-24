<?php
use App\Core\Router;
use App\Core\Session;
use App\Core\Abstract\Database;
use App\Core\Validator;
use App\Service\FileUploadService;
use App\Repository\UserRepository;
use App\Repository\CompteRepository;
use App\Controller\SecurityController;
use App\Controller\CompteController;
use App\Repository\TransactionRepository;
use App\Service\SmsService;
use App\Service\CompteService;
use App\Controller\TransactionController;

return [
    "core" => [
        "router" => fn() => new Router(),
        "database" => fn() => Database::getConnection(),
        "session" => fn() => Session::getInstance(),
        "validator" => fn() => new Validator(),
    ],

    "services" => [
        "fileUploadService" => fn() => new FileUploadService(),
        "smsService" => fn() => new SmsService(),
        "compteService" => fn() => new CompteService(),
    ],

    "repositories" => [
        "userRepository" => fn() => new UserRepository(),
        "compteRepository" => fn() => new CompteRepository(),
        "transactionRepository" => fn() => new TransactionRepository(),
    ],

    "controllers" => [
        "securityController" => fn() => new SecurityController(),
        "compteController" => fn() => new CompteController(),
        "transactionController" => fn() => new TransactionController(),
    ]
];
