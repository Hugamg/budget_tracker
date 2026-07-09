<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Tracker — Inscription</title>
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
            <h2>S'inscrire</h2>

            <div class="form__row">
                <div class="form__group">
                    <label>Prénom</label>
                    <input type="text" placeholder="Jean" required>
                </div>
                <div class="form__group">
                    <label>Nom</label>
                    <input type="text" placeholder="Martin" required>
                </div>
            </div>

            <div class="form__group">
                <label>E-mail</label>
                <input type="email" placeholder="jean@example.com" required>
            </div>

            <div class="form__group">
                <label>Mot de passe</label>
                <input type="password" placeholder="Minimum 8 caractères" required>
            </div>

            <div class="form__group">
                <label>Confirmer le mot de passe</label>
                <input type="password" placeholder="••••••••" required>
            </div>

            <div class="form__group">
                <label>Paye mensuelle (€)</label>
                <input type="number" step="0.01" placeholder="1650" required>
            </div>

            <div class="form__check">
                <input type="checkbox" id="terms" required>
                <label for="terms">J'accepte les <a href="#">conditions d'utilisation</a></label>
            </div>

            <button type="submit" class="btn btn--primary btn--full">S'inscrire</button>

            <p class="auth-link">
                Déjà inscrit ? <a href="/?page=login">Se connecter</a>
            </p>
        </form>
    </div>

    <div class="auth-side">
        <h2>Commencez maintenant 🚀</h2>
        <p>Créez votre compte et commencez à gérer vos finances.</p>
        <ul class="auth-benefits">
            <li>✓ Configuration en 2 minutes</li>
            <li>✓ Données sécurisées</li>
            <li>✓ Aucune carte bancaire requise</li>
            <li>✓ Gratuit</li>
        </ul>
    </div>
</div>

</body>
</html>