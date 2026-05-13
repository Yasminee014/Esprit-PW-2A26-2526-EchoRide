<?php
// ============================================================
// models/User.php - Classes User et Admin avec accès BDD
// ============================================================

require_once __DIR__ . '/../config.php'; // contient la classe Database

// ════════════════════════════════════════════════════════════
// CLASSE USER (avec méthodes de requêtage)
// ════════════════════════════════════════════════════════════
class User {

    private ?PDO $pdo = null;

    // ─── Constructeur : récupère la connexion PDO ──────────
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // ─── Propriétés ───────────────────────────────────────────
    private ?int    $id        = null;
    private string  $prenom    = '';
    private string  $nom       = '';
    private string  $email     = '';
    private string  $telephone = '';
    private string  $role      = 'passager';
    private string  $statut    = 'actif';
    private string  $password  = '';
    private ?string $createdAt = null;
    private ?string $photo     = null;

    // ─── Getters ──────────────────────────────────────────────
    public function getId(): ?int           { return $this->id; }
    public function getPrenom(): string     { return $this->prenom; }
    public function getNom(): string        { return $this->nom; }
    public function getEmail(): string      { return $this->email; }
    public function getTelephone(): string  { return $this->telephone; }
    public function getRole(): string       { return $this->role; }
    public function getStatut(): string     { return $this->statut; }
    public function getPassword(): string   { return $this->password; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getPhoto(): ?string     { return $this->photo; }

    // ─── Setters ──────────────────────────────────────────────
    public function setId(int $id): void           { $this->id        = $id; }
    public function setPrenom(string $p): void     { $this->prenom    = trim($p); }
    public function setNom(string $n): void        { $this->nom       = trim($n); }
    public function setEmail(string $e): void      { $this->email     = trim(strtolower($e)); }
    public function setTelephone(string $t): void  { $this->telephone = trim($t); }
    public function setRole(string $r): void       { $this->role      = $r; }
    public function setStatut(string $s): void     { $this->statut    = $s; }
    public function setPassword(string $p): void   { $this->password  = $p; }
    public function setCreatedAt(string $d): void  { $this->createdAt = $d; }
    public function setPhoto(?string $p): void     { $this->photo     = $p; }

    // ─── Méthodes statistiques (requêtes BDD) ────────────────
    public function countTotalPassagers(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'passager'");
        return (int) $stmt->fetchColumn();
    }

    public function countActivePassagers(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'passager' AND statut = 'actif'");
        return (int) $stmt->fetchColumn();
    }

    public function countInactivePassagers(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'passager' AND statut = 'inactif'");
        return (int) $stmt->fetchColumn();
    }

    public function countTotalAdmins(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM admins");
        return (int) $stmt->fetchColumn();
    }

    public function getAllPassagers(): array {
        $stmt = $this->pdo->query("SELECT * FROM users WHERE role = 'passager' ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ════════════════════════════════════════════════════════════
// CLASSE ADMIN (optionnelle, reste identique)
// ════════════════════════════════════════════════════════════
class Admin {

    private ?int   $id       = null;
    private string $nom      = '';
    private string $email    = '';
    private string $password = '';

    public function getId(): ?int         { return $this->id; }
    public function getNom(): string      { return $this->nom; }
    public function getEmail(): string    { return $this->email; }
    public function getPassword(): string { return $this->password; }

    public function setId(int $id): void         { $this->id       = $id; }
    public function setNom(string $n): void      { $this->nom      = trim($n); }
    public function setEmail(string $e): void    { $this->email    = trim(strtolower($e)); }
    public function setPassword(string $p): void { $this->password = $p; }
}