<?php
// ============================================================
// models/User.php
// Classes : User + Admin  —  Propriétés, Getters & Setters
// Toute la logique métier (requêtes SQL) se trouve dans les
// contrôleurs : UserController.php et AdminController.php
// ============================================================

require_once __DIR__ . '/../config.php';

// ════════════════════════════════════════════════════════════
// CLASSE USER
// ════════════════════════════════════════════════════════════
class User {

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
}

// ════════════════════════════════════════════════════════════
// CLASSE ADMIN
// ════════════════════════════════════════════════════════════
class Admin {

    // ─── Propriétés ───────────────────────────────────────────
    private ?int   $id       = null;
    private string $nom      = '';
    private string $email    = '';
    private string $password = '';

    // ─── Getters ──────────────────────────────────────────────
    public function getId(): ?int         { return $this->id; }
    public function getNom(): string      { return $this->nom; }
    public function getEmail(): string    { return $this->email; }
    public function getPassword(): string { return $this->password; }

    // ─── Setters ──────────────────────────────────────────────
    public function setId(int $id): void         { $this->id       = $id; }
    public function setNom(string $n): void      { $this->nom      = trim($n); }
    public function setEmail(string $e): void    { $this->email    = trim(strtolower($e)); }
    public function setPassword(string $p): void { $this->password = $p; }
}
