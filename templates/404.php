<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page non trouvée - MAXITSA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: rgb(237, 235, 235);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            margin: 20px;
            position: relative;
            text-align: center;
        }

        .header {
            background: #ff6500;
            color: white;
            text-align: center;
            padding: 40px 20px;
            position: relative;
        }

        .header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="white" opacity="0.1"/><circle cx="80" cy="30" r="1.5" fill="white" opacity="0.1"/><circle cx="60" cy="70" r="1" fill="white" opacity="0.1"/></svg>');
        }

        .logo {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
            z-index: 1;
            position: relative;
        }

        .tagline {
            font-size: 1.1em;
            opacity: 0.9;
            z-index: 1;
            position: relative;
        }

        .error-content {
            padding: 40px 30px;
            background: white;
        }

        .error-code {
            font-size: 8em;
            font-weight: bold;
            color: #ff6500;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(255, 101, 0, 0.1);
        }

        .error-title {
            font-size: 2em;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .error-message {
            font-size: 1.1em;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-icon {
            font-size: 4em;
            color: #ff6b35;
            margin-bottom: 20px;
            opacity: 0.7;
        }

        .btn {
            background: #ff6500;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: transparent;
            color: #ff6500;
            border: 2px solid #ff6500;
        }

        .btn-secondary:hover {
            background: #ff6500;
            color: white;
        }

        .actions {
            margin-top: 30px;
        }

        .suggestions {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #ff6500;
        }

        .suggestions h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.2em;
        }

        .suggestions ul {
            list-style: none;
            text-align: left;
        }

        .suggestions li {
            margin-bottom: 8px;
            color: #666;
        }

        .suggestions li::before {
            content: "→";
            color: #ff6500;
            font-weight: bold;
            margin-right: 10px;
        }

        .suggestions a {
            color: #ff6500;
            text-decoration: none;
            font-weight: 600;
        }

        .suggestions a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }

            .error-content {
                padding: 30px 20px;
            }

            .error-code {
                font-size: 6em;
            }

            .error-title {
                font-size: 1.5em;
            }

            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">MAXITSA</div>
            <div class="tagline">Services de Transfert et Paiements</div>
        </div>

        <div class="error-content">
            <div class="error-icon">🔍</div>
            <div class="error-code">404</div>
            <div class="error-title">Page non trouvée</div>
            <div class="error-message">
                Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
                <br>Vérifiez l'URL ou retournez à l'accueil.
            </div>

            <div class="actions">
                <a href="/" class="btn">Retour à l'accueil</a>
                <a href="javascript:history.back()" class="btn btn-secondary">Page précédente</a>
            </div>

            <div class="suggestions">
                <h3>Que souhaitez-vous faire ?</h3>
                <ul>
                    <li><a href="/">Accéder à la page d'accueil</a></li>
                    <li><a href="/login">Se connecter à votre compte</a></li>
                    <li><a href="/">Créer un nouveau compte</a></li>
                    <li><a href="/contact">Contacter le support</a></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>