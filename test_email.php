<?php
// ============================================================
// test_email.php — Page de diagnostic email
// SUPPRIMER CE FICHIER après avoir confirmé que l'email marche
// ============================================================

// Sécurité basique : accessible uniquement en local
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    http_response_code(403);
    die('Accès refusé - Réservé à localhost');
}

require_once __DIR__ . '/config.php';

$result  = '';
$error   = '';
$logPath = BASE_PATH . 'libs' . DIRECTORY_SEPARATOR . 'PHPMailer-master' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

// Envoi test si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testEmail = trim($_POST['email'] ?? '');
    if (filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        $sent = sendResetCodeEmail($testEmail, '123456', 'Utilisateur Test');
        if ($sent) {
            $result = '✅ Email envoyé avec succès à ' . htmlspecialchars($testEmail);
        } else {
            $error = '❌ Échec de l\'envoi. Consultez les logs PHP (error_log) pour plus de détails.';
        }
    } else {
        $error = '❌ Adresse email invalide.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Test Email - EcoRide</title>
<style>
  body { font-family: monospace; background: #0A2F44; color: #fff; padding: 2rem; max-width: 700px; margin: 0 auto; }
  h1 { color: #00B4D8; }
  .card { background: #0D1F3A; border: 1px solid rgba(0,180,216,0.3); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
  .ok  { color: #4cff9a; }
  .err { color: #ff6b6b; }
  .warn { color: #ffa500; }
  input[type=email] { width: 100%; padding: 0.7rem 1rem; border-radius: 8px; border: 1px solid rgba(0,180,216,0.4); background: rgba(10,47,68,0.8); color: #fff; font-size: 1rem; margin-bottom: 1rem; }
  button { background: linear-gradient(135deg,#00B4D8,#0077B6); color: #fff; border: none; padding: 0.7rem 2rem; border-radius: 25px; cursor: pointer; font-size: 1rem; }
  .step { margin-bottom: 0.5rem; }
</style>
</head>
<body>

<h1>🔧 Diagnostic Email — EcoRide</h1>
<p style="color:#90CAF9;">Page de test accessible uniquement depuis localhost.</p>

<?php if ($result): ?><div class="card"><p class="ok"><?= $result ?></p></div><?php endif; ?>
<?php if ($error):  ?><div class="card"><p class="err"><?= $error ?></p></div><?php endif; ?>

<!-- Diagnostics automatiques -->
<div class="card">
  <h2 style="color:#00B4D8;margin-top:0;">📋 Vérifications</h2>

  <div class="step">
    <?php $exists = is_dir($logPath); ?>
    <span class="<?= $exists ? 'ok' : 'err' ?>"><?= $exists ? '✅' : '❌' ?></span>
    Dossier PHPMailer :
    <code style="color:#90CAF9;"><?= htmlspecialchars($logPath) ?></code>
  </div>

  <div class="step">
    <?php $excOk = file_exists($logPath . 'Exception.php'); ?>
    <span class="<?= $excOk ? 'ok' : 'err' ?>"><?= $excOk ? '✅' : '❌' ?></span>
    Exception.php
  </div>

  <div class="step">
    <?php $mailOk = file_exists($logPath . 'PHPMailer.php'); ?>
    <span class="<?= $mailOk ? 'ok' : 'err' ?>"><?= $mailOk ? '✅' : '❌' ?></span>
    PHPMailer.php
  </div>

  <div class="step">
    <?php $smtpOk = file_exists($logPath . 'SMTP.php'); ?>
    <span class="<?= $smtpOk ? 'ok' : 'err' ?>"><?= $smtpOk ? '✅' : '❌' ?></span>
    SMTP.php
  </div>

  <div class="step">
    <?php $ssl = extension_loaded('openssl'); ?>
    <span class="<?= $ssl ? 'ok' : 'err' ?>"><?= $ssl ? '✅' : '❌' ?></span>
    Extension PHP OpenSSL
    <?php if (!$ssl): ?><span class="err"> ← Activer dans php.ini : extension=openssl</span><?php endif; ?>
  </div>

  <div class="step">
    <span class="ok">ℹ️</span>
    Compte Gmail configuré : <code style="color:#90CAF9;">charcheriabir18@gmail.com</code>
  </div>
</div>

<!-- Formulaire de test -->
<div class="card">
  <h2 style="color:#00B4D8;margin-top:0;">📤 Envoyer un email de test</h2>
  <form method="POST">
    <label style="color:#90CAF9;display:block;margin-bottom:0.4rem;">Adresse email destinataire :</label>
    <input type="email" name="email" placeholder="votre@email.com" required>
    <button type="submit">Envoyer le test</button>
  </form>
</div>

<!-- Instructions si problème -->
<div class="card">
  <h2 style="color:#ffa500;margin-top:0;">⚠️ Si l'envoi échoue — Étapes à vérifier</h2>
  <ol style="color:#90CAF9;line-height:2;">
    <li>Va sur <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#00B4D8;">myaccount.google.com/apppasswords</a></li>
    <li>Crée un nouveau <strong style="color:#fff;">Mot de passe d'application</strong> pour "Mail" / "Autre"</li>
    <li>Copie le mot de passe généré (16 caractères sans espaces)</li>
    <li>Remplace la valeur de <code style="color:#4cff9a;">$mail->Password</code> dans <code>config.php</code></li>
    <li>La <strong style="color:#fff;">vérification en 2 étapes</strong> doit être activée sur le compte Gmail</li>
  </ol>
</div>

<p style="color:#6B6B6B;font-size:0.8rem;">⚠️ Supprime ce fichier <code>test_email.php</code> après utilisation.</p>
</body>
</html>
