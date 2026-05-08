<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Stripe requise | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0A1628, #0D1F3A);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(231,76,60,0.4);
            border-radius: 20px;
            padding: 2.5rem;
            max-width: 620px;
            width: 90%;
            text-align: center;
        }
        .icon { font-size: 3rem; color: #e74c3c; margin-bottom: 1rem; }
        h1 { font-size: 1.5rem; margin-bottom: 0.5rem; color: #f8d7da; }
        p { color: #A7A9AC; margin-bottom: 1.5rem; line-height: 1.6; }
        .steps {
            text-align: left;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 1.2rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        .steps ol { padding-left: 1.2rem; }
        .steps li { margin-bottom: 0.6rem; color: #CFE6FF; font-size: 0.9rem; }
        .steps code {
            background: rgba(97,179,250,0.15);
            color: #61B3FA;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        .test-card {
            background: rgba(39,174,96,0.1);
            border: 1px solid rgba(39,174,96,0.3);
            border-radius: 10px;
            padding: 0.8rem 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: #a8f0c6;
        }
        .test-card strong { color: #27ae60; }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(25,118,210,0.4); }
    </style>
</head>
<body>
<div class="card">
    <div class="icon"><i class="fas fa-key"></i></div>
    <h1>Configuration Stripe requise</h1>
    <p>Les clés API Stripe ne sont pas encore configurées. Suivez les étapes ci-dessous pour activer le paiement par carte.</p>

    <div class="steps">
        <ol>
            <li>Créez un compte gratuit sur <strong>stripe.com</strong></li>
            <li>Dans le Dashboard → <strong>Developers → API keys</strong> (mode <strong>Test</strong> activé)</li>
            <li>Copiez <code>Secret key</code> et <code>Publishable key</code></li>
            <li>Collez-les dans le fichier <code>Config/StripeConfig.php</code></li>
        </ol>
    </div>

    <div class="test-card">
        <strong>Carte de test Stripe :</strong><br>
        Numéro : <code>4242 4242 4242 4242</code> &nbsp;|&nbsp; Expiration : <code>12/34</code> &nbsp;|&nbsp; CVV : <code>123</code>
    </div>

    <a href="javascript:history.back()" class="btn"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
</body>
</html>
