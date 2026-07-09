<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Tracker — Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-body">

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <span class="brand-dot brand-dot--large"></span>
            <h1>Budget<br>Tracker</h1>
        </div>

        <form class="form auth-form">
            <h2>Se connecter</h2>

            <div class="form__group">
                <label>E-mail</label>
                <input type="email" placeholder="jean@example.com" required>
            </div>

            <div class="form__group">
                <label>Mot de passe</label>
                <input type="password" placeholder="••••••••" required>
            </div>

            <div class="form__check">
                <input type="checkbox" id="remember">
                <label for="remember">Se souvenir de moi</label>
            </div>

            <button type="submit" class="btn btn--primary btn--full">Connexion</button>

            <p class="auth-link">
                Pas encore de compte ? <a href="/?page=register">S'inscrire</a>
            </p>

            <div class="auth-divider">ou</div>

            <button type="button" class="btn btn--social">
                📧 Continuer avec Google
            </button>
        </form>
    </div>

    <div class="auth-side">
        <h2>Bienvenue 👋</h2>
        <p>Reprendre le contrôle de vos finances, simplement.</p>
        <ul class="auth-benefits">
            <li>✓ Suivi détaillé des dépenses</li>
            <li>✓ Catégorisation automatique</li>
            <li>✓ Graphiques en temps réel</li>
            <li>✓ Objectifs d'épargne</li>
        </ul>
    </div>
</div>

</body>
</html>