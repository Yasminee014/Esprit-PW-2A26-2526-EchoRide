<?php
// ============================================================
// config.php - Configuration générale, connexion BDD et email
// Projet : EcoRide1
// ============================================================

// ─── CONSTANTES BASE DE DONNÉES ───────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'Ecoride1');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ─── CONSTANTES CHEMINS ────────────────────────────────────
define('BASE_URL',  'http://localhost/projetG/');
define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// ─── SESSION ───────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── CLASSE DATABASE (Singleton PDO) ──────────────────────
class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct() {
        $this->connect();
    }

    private function connect(): void {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}








// ─── FONCTION EMAIL (PHPMailer) ───────────────────────────
// PHPMailer est dans : libs/PHPMailer-master/src/
function sendResetCodeEmail(string $to, string $code, string $name): bool {
    $srcPath = BASE_PATH . 'libs' . DIRECTORY_SEPARATOR
             . 'PHPMailer-master' . DIRECTORY_SEPARATOR
             . 'src' . DIRECTORY_SEPARATOR;

    if (!is_dir($srcPath)) {
        error_log('PHPMailer introuvable : ' . $srcPath);
        return false;
    }

    require_once $srcPath . 'Exception.php';
    require_once $srcPath . 'PHPMailer.php';
    require_once $srcPath . 'SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'charcheriabir18@gmail.com';
        $mail->Password   = 'hfoyydutcmpeyzub';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        $mail->setFrom('no-reply@ecoride.com', 'Eco Ride');
        $mail->addAddress($to, $name);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = '🔐 Réinitialisation de votre mot de passe - Eco Ride';
        $mail->Body    = '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#0A1628;font-family:\'Segoe UI\',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#0A1628;padding:40px 20px;">
    <tr><td align="center">
      <table width="100%" style="max-width:520px;background:#0D1F3A;border-radius:20px;overflow:hidden;border:1px solid rgba(25,118,210,0.3);">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#1976D2,#1976D2);padding:30px;text-align:center;">
            <div style="font-size:36px;margin-bottom:8px;">🌿</div>
            <h1 style="color:#fff;margin:0;font-size:1.8rem;letter-spacing:1px;">Eco<span style="color:#0D1F3A">Ride</span></h1>
            <p style="color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:0.9rem;">Covoiturage Intelligent et Écologique</p>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:35px 35px 25px;">
            <p style="color:#61B3FA;font-size:0.9rem;margin:0 0 8px;">Bonjour <strong style="color:#fff;">' . htmlspecialchars($name) . '</strong>,</p>
            <h2 style="color:#fff;font-size:1.2rem;margin:0 0 16px;">Réinitialisation de votre mot de passe</h2>
            <p style="color:#61B3FA;font-size:0.88rem;line-height:1.6;margin:0 0 24px;">
              Vous avez demandé la réinitialisation de votre mot de passe. Utilisez le code ci-dessous pour continuer. Ce code est valable <strong style="color:#fff;">15 minutes</strong>.
            </p>

            <!-- Code box -->
            <div style="background:rgba(25,118,210,0.1);border:2px dashed #1976D2;border-radius:16px;padding:24px;text-align:center;margin:0 0 24px;">
              <p style="color:#61B3FA;font-size:0.8rem;margin:0 0 8px;text-transform:uppercase;letter-spacing:2px;">Votre code de vérification</p>
              <div style="font-size:2.8rem;font-weight:700;letter-spacing:10px;color:#1976D2;font-family:monospace;">' . $code . '</div>
            </div>

            <div style="background:rgba(255,165,0,0.08);border-left:3px solid #ffa500;border-radius:0 10px 10px 0;padding:12px 16px;margin:0 0 24px;">
              <p style="color:#ffa500;font-size:0.82rem;margin:0;">⚠️ Si vous n\'avez pas demandé cette réinitialisation, ignorez cet email. Votre mot de passe reste inchangé.</p>
            </div>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:rgba(0,0,0,0.2);padding:18px 35px;text-align:center;border-top:1px solid rgba(25,118,210,0.15);">
            <p style="color:#A7A9AC;font-size:0.78rem;margin:0;">© ' . date('Y') . ' Eco Ride — Tous droits réservés</p>
            <p style="color:#A7A9AC;font-size:0.75rem;margin:4px 0 0;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';
        $mail->AltBody = "Bonjour $name,\n\nVotre code de réinitialisation de mot de passe : $code\n\nCe code est valable 15 minutes.\n\nSi vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n© " . date('Y') . " Eco Ride";

        $mail->send();
        return true;

    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('Erreur PHPMailer : ' . $mail->ErrorInfo);
        return false;
    }
}
