<?php
declare(strict_types=1);

require_once __DIR__ . '/../../modele/Database.php';
require_once __DIR__ . '/../../modele/LostFoundFrontRepository.php';

$pdo = Database::getConnection();
$repository = new LostFoundFrontRepository($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = trim((string) ($_POST['action'] ?? ''));

  if ($action === 'create_declaration') {
    $repository->create([
      'titre' => trim((string) ($_POST['titre'] ?? '')),
      'description' => trim((string) ($_POST['description'] ?? '')),
      'categorie' => trim((string) ($_POST['categorie'] ?? '')),
      'lieu_perte' => trim((string) ($_POST['lieu_perte'] ?? '')),
      'photo_url' => trim((string) ($_POST['photo_url'] ?? '')),
      'date_perte' => trim((string) ($_POST['date_perte'] ?? '')),
      'statut' => trim((string) ($_POST['statut'] ?? 'perdu')),
      'trajet_id' => (int) ($_POST['trajet_id'] ?? 0),
      'passager_id' => isset($_POST['passager_id']) ? (int) $_POST['passager_id'] : null,
      'anonyme_nom' => trim((string) ($_POST['anonyme_nom'] ?? '')),
      'user_id' => isset($_POST['passager_id']) ? (int) $_POST['passager_id'] : null,
      'user_nom' => null,
    ]);
  }

  if ($action === 'add_comment') {
    $repository->addComment(
      (int) ($_POST['declaration_id'] ?? 0),
      isset($_POST['conducteur_id']) ? (int) $_POST['conducteur_id'] : null,
      null,
      trim((string) ($_POST['message'] ?? '')),
      null
    );
  }

  if ($action === 'update_declaration') {
    $stmt = $pdo->prepare(
      'UPDATE declarations
       SET titre = :titre,
         description = :description,
         categorie = :categorie,
         lieu_perte = :lieu_perte,
         date_perte = :date_perte
       WHERE id = :id'
    );
    $stmt->execute([
      ':titre' => trim((string) ($_POST['titre'] ?? '')),
      ':description' => trim((string) ($_POST['description'] ?? '')),
      ':categorie' => trim((string) ($_POST['categorie'] ?? '')),
      ':lieu_perte' => trim((string) ($_POST['lieu_perte'] ?? '')),
      ':date_perte' => trim((string) ($_POST['date_perte'] ?? '')),
      ':id' => (int) ($_POST['id'] ?? 0),
    ]);
  }

  if ($action === 'delete_declaration') {
    $stmt = $pdo->prepare('DELETE FROM declarations WHERE id = :id');
    $stmt->execute([':id' => (int) ($_POST['id'] ?? 0)]);
  }

  header('Location: lostfound_front.php');
  exit;
}

$rawDeclarations = $repository->findPublished();
$initialObjets = array_map(
  static fn(array $row): array => [
    'id' => (int) $row['id'],
    'title' => (string) ($row['titre'] ?? ''),
    'description' => (string) ($row['description'] ?? ''),
    'categorie' => (string) ($row['categorie'] ?? ''),
    'photo_url' => (string) ($row['photo_url'] ?? ''),
    'date_perte' => (string) ($row['date_perte'] ?? ''),
    'statut' => (string) ($row['statut'] ?? 'perdu'),
    'trajet_id' => (int) ($row['trajet_id'] ?? 0),
    'passager_id' => isset($row['passager_id']) ? (int) $row['passager_id'] : null,
    'anonyme_nom' => $row['anonyme_nom'] ?? null,
    'lieu_perte' => (string) ($row['lieu_perte'] ?? ''),
  ],
  $rawDeclarations
);

$commentStmt = $pdo->query('SELECT id, declaration_id, user_id, message, created_at FROM commentaires ORDER BY id DESC');
$rawCommentaires = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
$initialSignalements = array_map(
  static fn(array $row): array => [
    'id' => (int) $row['id'],
    'message' => (string) ($row['message'] ?? ''),
    'date_signalement' => (string) ($row['created_at'] ?? ''),
    'conducteur_id' => isset($row['user_id']) ? (int) $row['user_id'] : 0,
    'objet_id' => (int) ($row['declaration_id'] ?? 0),
  ],
  $rawCommentaires
);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Objets perdus | EcoRide Front Office</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --blue: #1976D2;
      --blue-light: #61B3FA;
      --dark: #0A1628;
      --dark2: #0D1F3A;
      --dark3: #0F3B6E;
      --white: #F4F5F7;
      --grey: #A7A9AC;
      --green: #27ae60;
      --red: #e74c3c;
      --yellow: #f1c40f;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #0A1628;
      color: #fff;
      transition: background .25s, color .25s;
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background: url('https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=2000&q=80') center/cover no-repeat;
      opacity: .2;
      z-index: -2;
      pointer-events: none;
    }

    body::after {
      content: '';
      position: fixed;
      inset: 0;
      background: linear-gradient(140deg, rgba(8, 20, 38, .9), rgba(12, 31, 58, .84));
      z-index: -1;
      pointer-events: none;
    }

    body.light-mode {
      background: #f5f5f5;
      color: #333;
    }

    body.light-mode::before {
      opacity: .08;
    }

    body.light-mode::after {
      background: linear-gradient(140deg, rgba(245, 247, 250, .8), rgba(245, 247, 250, .65));
    }

    body.light-mode .navbar {
      background: #fff;
      box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
    }

    body.light-mode .logo,
    body.light-mode .dropdown-btn,
    body.light-mode .user-info,
    body.light-mode .theme-btn {
      color: #1976D2;
    }

    body.light-mode .dropdown-content {
      background: #fff;
      border: 1px solid #e0e0e0;
    }

    body.light-mode .dropdown-content a {
      color: #333;
    }

    body.light-mode .hero-section {
      background: linear-gradient(135deg, #1565C0, #0D47A1);
    }

    body.light-mode .filters-bar,
    body.light-mode .publish-card,
    body.light-mode .lost-card,
    body.light-mode .modal,
    body.light-mode .filter-group {
      background: #fff;
      border-color: #e0e0e0;
      color: #333;
    }

    body.light-mode .filter-group input,
    body.light-mode .filter-group select,
    body.light-mode .fg input,
    body.light-mode .fg textarea,
    body.light-mode .fg select,
    body.light-mode .comment-box textarea {
      color: #333;
    }

    body.light-mode .status-badge {
      border-color: transparent;
    }

    .navbar {
      background: linear-gradient(90deg, #1976D2, #0F3B6E);
      padding: .8rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
      border-bottom: 1px solid rgba(97, 179, 250, .24);
    }

    .nav-left {
      display: flex;
      align-items: center;
      gap: 1.2rem;
      flex-wrap: wrap;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 1.3rem;
      font-weight: 700;
      color: #fff;
      text-decoration: none;
    }

    .logo i {
      color: #61B3FA;
    }

    .dropdown {
      position: relative;
      display: inline-block;
    }

    .dropdown-btn {
      background: rgba(255, 255, 255, .1);
      color: #fff;
      padding: .6rem 1.2rem;
      border: 1px solid rgba(97, 179, 250, .4);
      border-radius: 30px;
      font-size: .9rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .dropdown-btn:hover {
      background: rgba(255, 255, 255, .2);
    }

    .dropdown-content {
      display: none;
      position: absolute;
      top: 110%;
      left: 0;
      min-width: 240px;
      background: linear-gradient(145deg, #0D1F3A, #122A4A);
      border: 1px solid rgba(97, 179, 250, .3);
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, .4);
      z-index: 200;
      overflow: hidden;
    }

    .dropdown-content.show {
      display: block;
      animation: fadeInDown .25s ease;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .dropdown-content a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: .8rem 1.2rem;
      color: #fff;
      text-decoration: none;
      font-size: .85rem;
      transition: all .2s;
    }

    .dropdown-content a i {
      width: 20px;
      color: #61B3FA;
    }

    .dropdown-content a:hover {
      background: rgba(97, 179, 250, .15);
      padding-left: 1.5rem;
    }

    .dropdown-content a.active {
      background: rgba(25, 118, 210, .3);
      border-left: 3px solid #61B3FA;
    }

    .dropdown-divider {
      height: 1px;
      background: rgba(97, 179, 250, .2);
      margin: .3rem 0;
    }

    .nav-right {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, .1);
      padding: .45rem 1rem;
      border-radius: 30px;
      font-size: .85rem;
      border: 1px solid rgba(97, 179, 250, .25);
    }

    .theme-btn {
      background: rgba(255, 255, 255, .1);
      border: 1px solid rgba(97, 179, 250, .25);
      color: #fff;
      padding: .45rem .8rem;
      border-radius: 30px;
      cursor: pointer;
    }

    .container {
      max-width: 1240px;
      margin: 0 auto;
      padding: 1.4rem;
    }

    .hero-section {
      background: linear-gradient(135deg, #1976D2, #0F3B6E);
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 1.2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1.5rem;
      border: 1px solid rgba(97, 179, 250, .3);
    }

    .hero-content h1 {
      font-size: 1.8rem;
      margin-bottom: .35rem;
    }

    .hero-content .highlight {
      color: #61B3FA;
    }

    .hero-content p {
      color: rgba(255, 255, 255, .84);
      margin-bottom: .9rem;
      max-width: 620px;
    }

    .hero-stats {
      display: flex;
      gap: 1.2rem;
      flex-wrap: wrap;
      margin-bottom: .8rem;
    }

    .hero-stats .stat {
      text-align: center;
      min-width: 100px;
    }

    .hero-stats .number {
      font-size: 1.35rem;
      font-weight: 700;
    }

    .hero-stats .label {
      font-size: .72rem;
      opacity: .75;
      text-transform: uppercase;
      letter-spacing: .6px;
    }

    .hero-btn {
      background: white;
      color: #1976D2;
      padding: .7rem 1.3rem;
      border-radius: 30px;
      text-decoration: none;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
    }

    .hero-btn:hover {
      transform: translateY(-2px);
    }

    .hero-icon {
      font-size: 4.2rem;
      opacity: .5;
      animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    .switcher {
      margin-bottom: 1rem;
      display: flex;
      gap: .55rem;
      flex-wrap: wrap;
    }

    .users-modal-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: .6rem;
      margin-bottom: .8rem;
    }

    .chip {
      background: rgba(255, 255, 255, .1);
      border: 1px solid rgba(97, 179, 250, .3);
      color: #fff;
      border-radius: 999px;
      padding: .35rem .78rem;
      font-size: .78rem;
      cursor: pointer;
      transition: all .2s;
    }

    .chip.active {
      background: #1976D2;
      border-color: #61B3FA;
    }

    .filters-bar {
      display: flex;
      flex-wrap: wrap;
      gap: .8rem;
      margin-bottom: 1.1rem;
      background: rgba(255, 255, 255, .05);
      border: 1px solid rgba(97, 179, 250, .16);
      padding: .9rem;
      border-radius: 16px;
      backdrop-filter: blur(2px);
    }

    .filter-group {
      background: rgba(255, 255, 255, .08);
      border-radius: 30px;
      padding: .46rem .95rem;
      display: flex;
      align-items: center;
      gap: 8px;
      border: 1px solid rgba(97, 179, 250, .24);
    }

    .filter-group i {
      color: #61B3FA;
    }

    .filter-group input,
    .filter-group select {
      background: transparent;
      border: none;
      color: #fff;
      outline: none;
      padding: .2rem;
      min-width: 130px;
      font-family: inherit;
      font-size: .84rem;
    }

    .filter-group select option {
      background: #0D1F3A;
    }

    .btn-reset {
      background: rgba(231, 76, 60, .2);
      border: 1px solid rgba(231, 76, 60, .4);
      color: #e74c3c;
      padding: .5rem 1rem;
      border-radius: 30px;
      cursor: pointer;
    }

    .section-title {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: .9rem;
      gap: .8rem;
      flex-wrap: wrap;
    }

    .section-title h2 {
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .count-badge {
      background: rgba(97, 179, 250, .2);
      border: 1px solid rgba(97, 179, 250, .35);
      padding: .3rem .8rem;
      border-radius: 20px;
      font-size: .8rem;
    }

    .content-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1rem;
      align-items: start;
    }

    .publish-card {
      background: rgba(255, 255, 255, .07);
      border-radius: 16px;
      border: 1px solid rgba(97, 179, 250, .15);
      padding: 1rem;
      backdrop-filter: blur(3px);
      position: sticky;
      top: 86px;
    }

    .publish-card h3 {
      margin-bottom: .8rem;
      font-size: 1rem;
      color: #61B3FA;
      display: flex;
      align-items: center;
      gap: 7px;
    }

    .fg {
      margin-bottom: .7rem;
    }

    .fg label {
      display: block;
      font-size: .74rem;
      color: #A7A9AC;
      margin-bottom: .24rem;
      text-transform: uppercase;
      letter-spacing: .4px;
    }

    .fg input,
    .fg textarea,
    .fg select,
    .comment-box textarea {
      width: 100%;
      background: rgba(255, 255, 255, .07);
      border: 1px solid rgba(97, 179, 250, .24);
      color: #fff;
      padding: .52rem .72rem;
      border-radius: 9px;
      font-size: .84rem;
      font-family: inherit;
      outline: none;
    }

    .fg textarea,
    .comment-box textarea {
      min-height: 84px;
      resize: vertical;
    }

    .fg select option {
      background: #0D1F3A;
    }

    .btn-main {
      width: 100%;
      background: linear-gradient(135deg, #1976D2, #61B3FA);
      border: none;
      color: white;
      padding: .72rem;
      border-radius: 30px;
      cursor: pointer;
      font-weight: 600;
    }

    .btn-main:hover {
      opacity: .92;
    }

    .lost-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1rem;
    }

    .lost-card {
      background: rgba(255, 255, 255, .07);
      border-radius: 16px;
      overflow: hidden;
      transition: transform .25s, box-shadow .25s;
      border: 1px solid rgba(97, 179, 250, .15);
      animation: fadeInUp .45s ease forwards;
      opacity: 0;
      backdrop-filter: blur(2px);
    }

    .lost-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 28px rgba(0, 0, 0, .3);
      border-color: #61B3FA;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(25px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .card-head {
      padding: .9rem .95rem .5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: .6rem;
      flex-wrap: wrap;
    }

    .card-id {
      font-size: .76rem;
      color: #A7A9AC;
      letter-spacing: .4px;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border-radius: 30px;
      padding: .2rem .62rem;
      font-size: .72rem;
      border: 1px solid transparent;
      font-weight: 600;
    }

    .status-perdu {
      background: rgba(231, 76, 60, .16);
      color: #ff9087;
      border-color: rgba(231, 76, 60, .36);
    }

    .status-retrouve {
      background: rgba(241, 196, 15, .16);
      color: #f9d761;
      border-color: rgba(241, 196, 15, .34);
    }

    .status-restitue {
      background: rgba(39, 174, 96, .16);
      color: #7fe2a4;
      border-color: rgba(39, 174, 96, .34);
    }

    .card-content {
      padding: 0 .95rem .9rem;
    }

    .card-title {
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: .32rem;
      line-height: 1.4;
    }

    .card-desc {
      font-size: .84rem;
      color: #c4d4ea;
      margin-bottom: .7rem;
      line-height: 1.45;
      min-height: 44px;
    }

    .tags {
      display: flex;
      flex-wrap: wrap;
      gap: .45rem;
      margin-bottom: .72rem;
    }

    .tag {
      background: rgba(255, 255, 255, .1);
      border: 1px solid rgba(97, 179, 250, .25);
      border-radius: 999px;
      padding: .22rem .58rem;
      font-size: .71rem;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      color: #dceaff;
    }

    .card-actions {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: .45rem;
    }

    .action-btn {
      border: 1px solid rgba(97, 179, 250, .3);
      color: #fff;
      background: rgba(255, 255, 255, .08);
      border-radius: 30px;
      font-size: .76rem;
      padding: .45rem .5rem;
      cursor: pointer;
    }

    .action-btn:hover {
      background: rgba(97, 179, 250, .2);
    }

    .action-danger {
      border-color: rgba(231, 76, 60, .34);
      color: #ff9f98;
      background: rgba(231, 76, 60, .12);
    }

    .empty-state {
      grid-column: 1 / -1;
      text-align: center;
      padding: 2rem;
      background: rgba(255, 255, 255, .05);
      border-radius: 16px;
      border: 1px dashed rgba(97, 179, 250, .3);
    }

    .empty-state i {
      font-size: 2rem;
      opacity: .35;
      margin-bottom: .55rem;
      display: block;
    }

    .modal-bg {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .66);
      backdrop-filter: blur(3px);
      z-index: 999;
      align-items: center;
      justify-content: center;
      padding: .9rem;
    }

    .modal-bg.open {
      display: flex;
    }

    .modal {
      background: linear-gradient(145deg, #0D1F3A, #122A4A);
      border: 1px solid rgba(97, 179, 250, .22);
      border-radius: 18px;
      padding: 1.05rem;
      width: 95%;
      max-width: 860px;
      max-height: 92vh;
      overflow-y: auto;
    }

    .modal-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: .8rem;
      gap: .8rem;
      flex-wrap: wrap;
    }

    .modal-title {
      font-size: 1rem;
      color: #61B3FA;
    }

    .btn-outline {
      background: rgba(255, 255, 255, .08);
      border: 1px solid rgba(97, 179, 250, .28);
      color: #fff;
      padding: .45rem .8rem;
      border-radius: 20px;
      cursor: pointer;
      font-size: .8rem;
    }

    .comment {
      background: rgba(255, 255, 255, .06);
      border: 1px solid rgba(97, 179, 250, .18);
      border-radius: 10px;
      padding: .6rem .7rem;
      margin-bottom: .5rem;
    }

    .comment-meta {
      font-size: .72rem;
      color: #A7A9AC;
      margin-bottom: .2rem;
    }

    .comment-box {
      margin-top: .65rem;
    }

    .thread-image {
      border: 1px solid rgba(97, 179, 250, .25);
      background: rgba(255, 255, 255, .05);
      border-radius: 12px;
      padding: .7rem;
      margin-bottom: .7rem;
    }

    .thread-image img {
      width: 100%;
      border-radius: 10px;
      border: 1px solid rgba(97, 179, 250, .3);
      display: block;
      max-height: 280px;
      object-fit: cover;
    }

    .muted {
      color: #9bb2cf;
      font-size: .76rem;
    }

    .form-error {
      display: none;
      background: rgba(231, 76, 60, .16);
      border: 1px solid rgba(231, 76, 60, .35);
      color: #ffd6d2;
      padding: .55rem .75rem;
      border-radius: 9px;
      margin-bottom: .8rem;
      font-size: .8rem;
    }

    .form-error.show {
      display: block;
    }

    .field-error {
      display: none;
      color: #ffb3ab;
      font-size: .74rem;
      margin-top: .3rem;
    }

    .field-error.show {
      display: block;
    }

    .input-invalid {
      border-color: #e74c3c !important;
      box-shadow: 0 0 0 2px rgba(231, 76, 60, .16);
    }

    @media (max-width: 980px) {
      .content-grid {
        grid-template-columns: 1fr;
      }

      .publish-card {
        position: static;
      }
    }

    @media (max-width: 768px) {
      .navbar {
        padding: .8rem 1rem;
      }

      .hero-section {
        padding: 1.3rem;
      }

      .hero-content h1 {
        font-size: 1.35rem;
      }

      .hero-icon {
        font-size: 3rem;
      }

      .lost-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="nav-left">
      <a href="#" class="logo"><i class="fas fa-leaf"></i><span>EcoRide</span></a>
      <div class="dropdown">
        <button class="dropdown-btn" id="menuBtn"><i class="fas fa-bars"></i><span>Menu</span></button>
        <div class="dropdown-content" id="dropdownMenu">
          <a href="#" class="active"><i class="fas fa-box-open"></i> Objets perdus</a>
          <a href="#" id="openUsersTab"><i class="fas fa-users"></i> Changer utilisateur</a>
          <a href="#publications"><i class="fas fa-list"></i> Mes publications</a>
          <a href="#" id="quickThreadLink"><i class="fas fa-comments"></i> Derniers commentaires</a>
          <div class="dropdown-divider"></div>
          <a href="../Back%20office/lostfound_admin.php"><i class="fas fa-shield-alt"></i> Administration</a>
        </div>
      </div>
    </div>
    <div class="nav-right">
      <button id="themeToggle" class="theme-btn" type="button"><i class="fas fa-moon"></i></button>
      <div class="user-info"><i class="fas fa-user-circle"></i><span id="currentUserName">Sophie Martin</span></div>
    </div>
  </nav>

  <div class="container">
    <section class="hero-section">
      <div class="hero-content">
        <h1><i class="fas fa-box-open"></i> Suivi des <span class="highlight">objets perdus</span></h1>
        <p>Publiez, suivez les commentaires des conducteurs et trouvez rapidement votre objet grace aux mises a jour en temps reel.</p>
        <div class="hero-stats">
          <div class="stat"><div class="number" id="heroTotal">0</div><div class="label">Declarations</div></div>
          <div class="stat"><div class="number" id="heroOpen">0</div><div class="label">A retrouver</div></div>
          <div class="stat"><div class="number" id="heroResolved">0</div><div class="label">Resolues</div></div>
        </div>
        <button id="openPublishModalBtn" class="hero-btn" type="button">Nouvelle publication <i class="fas fa-plus"></i></button>
      </div>
      <div class="hero-icon"><i class="fas fa-magnifying-glass-location"></i></div>
    </section>

    <section class="filters-bar">
      <div class="filter-group"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Rechercher titre, lieu, declarant..."></div>
      <div class="filter-group"><i class="fas fa-layer-group"></i>
        <select id="filterCategory">
          <option value="">Toutes categories</option>
          <option value="electronique">Electronique</option>
          <option value="vetement">Vetement</option>
          <option value="document">Document</option>
          <option value="bagage">Bagage</option>
          <option value="autre">Autre</option>
        </select>
      </div>
      <div class="filter-group"><i class="fas fa-circle-info"></i>
        <select id="filterStatus">
          <option value="">Tous statuts</option>
          <option value="perdu">Perdu</option>
          <option value="retrouve">Retrouve</option>
          <option value="restitue">Restitue</option>
        </select>
      </div>
      <button id="resetFilters" class="btn-reset" type="button"><i class="fas fa-rotate-left"></i> Reinitialiser</button>
    </section>

    <section class="section-title" id="publications">
      <h2><i class="fas fa-thumbtack"></i> Publications objets perdus</h2>
      <span class="count-badge" id="countBadge">0 publication</span>
    </section>

    <section class="content-grid">
      <div class="lost-grid" id="rows"></div>
    </section>
  </div>

  <div class="modal-bg" id="detailModal">
    <div class="modal">
      <div class="modal-head">
        <h3 class="modal-title" id="detailTitle">Details</h3>
        <button class="btn-outline" id="closeDetail" type="button">Fermer</button>
      </div>
      <div id="detailBody"></div>
    </div>
  </div>

  <div class="modal-bg" id="threadModal">
    <div class="modal">
      <div class="modal-head">
        <h3 class="modal-title" id="threadTitle">Sous-publications</h3>
        <button class="btn-outline" id="closeThread" type="button">Fermer</button>
      </div>
      <div class="thread-image">
        <div class="muted" style="margin-bottom:.4rem">Apercu image de la publication selectionnee</div>
        <img id="threadPreviewImage" alt="Image publication" src="https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1200&q=60">
      </div>
      <div id="threadBody"></div>
    </div>
  </div>

  <div class="modal-bg" id="usersModal">
    <div class="modal" style="max-width:540px;">
      <div class="users-modal-head">
        <h3 class="modal-title"><i class="fas fa-users"></i> Choisir un utilisateur</h3>
        <button class="btn-outline" id="closeUsers" type="button">Fermer</button>
      </div>
      <div class="switcher" id="userSwitcher"></div>
    </div>
  </div>

  <div class="modal-bg" id="publishModal">
    <div class="modal" style="max-width:620px;">
      <div class="modal-head">
        <h3 class="modal-title"><i class="fas fa-pen-to-square"></i> Nouvelle declaration</h3>
        <button class="btn-outline" id="closePublish" type="button">Fermer</button>
      </div>
      <form id="publishForm" novalidate>
        <div id="publishFormError" class="form-error" aria-live="polite"></div>
        <div class="fg">
          <label for="title">Titre</label>
          <input id="title" name="titre" type="text" placeholder="Ex: Sac noir oublie ligne B">
          <div id="titleError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="description">Description</label>
          <textarea id="description" name="description" placeholder="Decrivez votre objet"></textarea>
          <div id="descriptionError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="category">Categorie</label>
          <select id="category" name="categorie">
            <option value="">Choisir...</option>
            <option value="electronique">Electronique</option>
            <option value="vetement">Vetement</option>
            <option value="document">Document</option>
            <option value="bagage">Bagage</option>
            <option value="autre">Autre</option>
          </select>
          <div id="categoryError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="objectStatus">Statut de l'objet</label>
          <select id="objectStatus" name="statut">
            <option value="perdu">Perdu</option>
            <option value="retrouve">Retrouve</option>
            <option value="restitue">Restitue</option>
          </select>
          <div id="objectStatusError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="place">Lieu de perte</label>
          <input id="place" name="lieu_perte" type="text" placeholder="Ex: Gare centrale">
          <div id="placeError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="photoFile">Photo (image)</label>
          <input id="photoFile" type="file" accept="image/png,image/jpeg,image/webp,image/gif">
          <div class="muted" style="margin-top:.3rem">Formats acceptes: JPG, PNG, WEBP, GIF (max 2 Mo)</div>
          <div id="photoFileError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="lostDate">Date de perte</label>
          <input id="lostDate" name="date_perte" type="date">
          <div id="lostDateError" class="field-error" aria-live="polite"></div>
        </div>
        <button class="btn-main" type="submit"><i class="fas fa-paper-plane"></i> Publier</button>
      </form>
    </div>
  </div>

  <script>
    const OBJETS_KEY = 'declarations';
    const SIGNALEMENTS_KEY = 'commentaires';
    const INITIAL_OBJETS = <?php echo json_encode($initialObjets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const INITIAL_SIGNALEMENTS = <?php echo json_encode($initialSignalements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    const users = [
      { id: 1, name: 'Sophie Martin' },
      { id: 2, name: 'Youssef Belaid' },
      { id: 3, name: 'Camille Bernard' },
      { id: 4, name: 'Antoine Girard' },
      { id: 5, name: 'Lea Martin' }
    ];

    const trajetMap = {
      201: 'Paris -> Lyon',
      202: 'Lille -> Bruxelles',
      203: 'Marseille -> Nice',
      204: 'Bordeaux -> Toulouse',
      205: 'Nantes -> Rennes'
    };

    const els = {
      rows: document.getElementById('rows'),
      userSwitcher: document.getElementById('userSwitcher'),
      currentUserName: document.getElementById('currentUserName'),
      searchInput: document.getElementById('searchInput'),
      filterCategory: document.getElementById('filterCategory'),
      filterStatus: document.getElementById('filterStatus'),
      resetFilters: document.getElementById('resetFilters'),
      countBadge: document.getElementById('countBadge'),
      heroTotal: document.getElementById('heroTotal'),
      heroOpen: document.getElementById('heroOpen'),
      heroResolved: document.getElementById('heroResolved'),
      publishForm: document.getElementById('publishForm'),
      publishModal: document.getElementById('publishModal'),
      detailModal: document.getElementById('detailModal'),
      threadModal: document.getElementById('threadModal')
    };

    let currentUserId = 1;
    let activePostId = 0;
    let objets = [];
    let signalements = [];

    function seedDemoDataIfNeeded() {
    }

    function saveObjets() {
    }

    function saveSignalements() {
    }

    function nextId(list) {
      if (!list.length) {
        return 1;
      }
      return list.reduce((max, item) => Math.max(max, Number(item.id) || 0), 0) + 1;
    }

    function loadData() {
      objets = Array.isArray(INITIAL_OBJETS) ? JSON.parse(JSON.stringify(INITIAL_OBJETS)) : [];
      signalements = Array.isArray(INITIAL_SIGNALEMENTS) ? JSON.parse(JSON.stringify(INITIAL_SIGNALEMENTS)) : [];
    }

    function submitServerAction(action, payload) {
      const form = document.createElement('form');
      form.method = 'post';
      form.action = 'lostfound_front.php';

      const addField = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value == null ? '' : String(value);
        form.appendChild(input);
      };

      addField('action', action);
      Object.keys(payload || {}).forEach((key) => addField(key, payload[key]));
      document.body.appendChild(form);
      form.submit();
    }

    function nowIso() {
      return new Date().toISOString();
    }

    function escapeHtml(value) {
      const div = document.createElement('div');
      div.textContent = String(value == null ? '' : value);
      return div.innerHTML;
    }

    function labelCategory(value) {
      const map = {
        electronique: 'Electronique',
        vetement: 'Vetement',
        document: 'Document',
        bagage: 'Bagage',
        autre: 'Autre'
      };
      return map[value] || value || 'Autre';
    }

    function formatDate(dateValue) {
      const d = new Date(dateValue);
      if (Number.isNaN(d.getTime())) {
        return dateValue || '-';
      }
      return d.toLocaleDateString('fr-FR');
    }

    function formatDateTime(dateValue) {
      const d = new Date(dateValue);
      if (Number.isNaN(d.getTime())) {
        return dateValue || '-';
      }
      return d.toLocaleString('fr-FR');
    }

    function hasLengthBetween(value, min, max) {
      const len = (value || '').trim().length;
      return len >= min && len <= max;
    }

    function isValidDateYmd(value) {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) {
        return false;
      }
      const parts = String(value).split('-').map(Number);
      if (parts.length !== 3) {
        return false;
      }
      const [year, month, day] = parts;
      const d = new Date(year, month - 1, day);
      return d.getFullYear() === year && d.getMonth() === month - 1 && d.getDate() === day;
    }

    function isDateNotInFuture(value) {
      if (!isValidDateYmd(value)) {
        return false;
      }
      const todayYmd = currentDateYmd();
      return value <= todayYmd;
    }

    function getImageValidationError(file) {
      if (!file) {
        return null;
      }

      const allowedTypes = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
      const maxBytes = 2 * 1024 * 1024;

      if (!allowedTypes.includes(file.type)) {
        return 'Format image invalide. Utilisez JPG, PNG, WEBP ou GIF.';
      }

      if (file.size > maxBytes) {
        return 'Image trop lourde (max 2 Mo).';
      }

      return null;
    }

    function readFileAsDataUrl(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(String(reader.result || ''));
        reader.onerror = () => reject(new Error('Lecture image impossible.'));
        reader.readAsDataURL(file);
      });
    }

    function currentDateYmd() {
      const today = new Date();
      const y = String(today.getFullYear());
      const m = String(today.getMonth() + 1).padStart(2, '0');
      const d = String(today.getDate()).padStart(2, '0');
      return y + '-' + m + '-' + d;
    }

    function clearPublishValidation() {
      const formError = document.getElementById('publishFormError');
      formError.textContent = '';
      formError.classList.remove('show');

      ['title','description','category','objectStatus','place','photoFile','lostDate'].forEach((fieldId) => {
        const input = document.getElementById(fieldId);
        const error = document.getElementById(fieldId + 'Error');
        if (input) input.classList.remove('input-invalid');
        if (error) {
          error.textContent = '';
          error.classList.remove('show');
        }
      });
    }

    function setPublishFormError(message) {
      const formError = document.getElementById('publishFormError');
      formError.textContent = message;
      formError.classList.add('show');
    }

    function setPublishFieldError(fieldId, message) {
      const input = document.getElementById(fieldId);
      const error = document.getElementById(fieldId + 'Error');
      if (input) input.classList.add('input-invalid');
      if (error) {
        error.textContent = message;
        error.classList.add('show');
      }
    }

    function clearPublishFieldError(fieldId) {
      const input = document.getElementById(fieldId);
      const error = document.getElementById(fieldId + 'Error');
      if (input) input.classList.remove('input-invalid');
      if (error) {
        error.textContent = '';
        error.classList.remove('show');
      }
    }

    function declarantName(obj) {
      if (obj.passager_id) {
        const user = users.find((u) => u.id === Number(obj.passager_id));
        return user ? user.name : ('Passager #' + obj.passager_id);
      }
      return 'Anonyme - ' + (obj.anonyme_nom || 'Externe');
    }

    function placeLabel(obj) {
      if (obj.lieu_perte && String(obj.lieu_perte).trim() !== '') {
        return obj.lieu_perte;
      }
      return trajetMap[obj.trajet_id] || ('Trajet #' + (obj.trajet_id || '-'));
    }

    function titleLabel(obj) {
      if (obj.title && String(obj.title).trim() !== '') {
        return String(obj.title).trim();
      }
      const text = String(obj.description || '').trim();
      if (!text) {
        return 'Objet sans titre';
      }
      return text.length > 38 ? text.slice(0, 38) + '...' : text;
    }

    function descLabel(obj) {
      const text = String(obj.description || '').trim();
      if (!text) {
        return 'Aucune description';
      }
      return text.length > 120 ? text.slice(0, 120) + '...' : text;
    }

    function statusClass(status) {
      if (status === 'retrouve') {
        return 'status-retrouve';
      }
      if (status === 'restitue') {
        return 'status-restitue';
      }
      return 'status-perdu';
    }

    function statusLabel(status) {
      if (status === 'retrouve') {
        return 'Retrouve';
      }
      if (status === 'restitue') {
        return 'Restitue';
      }
      return 'Perdu';
    }

    function commentsForObject(objetId) {
      return signalements
        .filter((row) => Number(row.objet_id) === Number(objetId))
        .sort((a, b) => new Date(a.date_signalement) - new Date(b.date_signalement));
    }

    function getObjectById(id) {
      return objets.find((o) => Number(o.id) === Number(id));
    }

    function getFilteredObjets() {
      const q = (els.searchInput.value || '').trim().toLowerCase();
      const selectedCategory = (els.filterCategory.value || '').trim();
      const selectedStatus = (els.filterStatus.value || '').trim();

      return objets.filter((obj) => {
        const title = titleLabel(obj).toLowerCase();
        const desc = descLabel(obj).toLowerCase();
        const declarant = declarantName(obj).toLowerCase();
        const place = placeLabel(obj).toLowerCase();

        const searchOk = !q || title.includes(q) || desc.includes(q) || declarant.includes(q) || place.includes(q);
        const categoryOk = !selectedCategory || (obj.categorie || '') === selectedCategory;
        const statusOk = !selectedStatus || (obj.statut || 'perdu') === selectedStatus;

        return searchOk && categoryOk && statusOk;
      });
    }

    function renderStats() {
      const total = objets.length;
      const open = objets.filter((obj) => (obj.statut || 'perdu') === 'perdu').length;
      const resolved = objets.filter((obj) => ['retrouve', 'restitue'].includes(obj.statut || 'perdu')).length;

      els.heroTotal.textContent = String(total);
      els.heroOpen.textContent = String(open);
      els.heroResolved.textContent = String(resolved);
    }

    function renderUsers() {
      els.userSwitcher.innerHTML = users.map((u) => (
        '<button class="chip ' + (u.id === currentUserId ? 'active' : '') + '" data-user="' + u.id + '">' + escapeHtml(u.name) + '</button>'
      )).join('');

      els.userSwitcher.querySelectorAll('[data-user]').forEach((btn) => {
        btn.addEventListener('click', () => {
          currentUserId = Number(btn.getAttribute('data-user'));
          const current = users.find((u) => u.id === currentUserId);
          els.currentUserName.textContent = current ? current.name : 'Utilisateur';
          renderUsers();
          renderAll();
        });
      });
    }

    function renderRows() {
      const rows = getFilteredObjets().sort((a, b) => Number(b.id) - Number(a.id));
      els.countBadge.textContent = rows.length + (rows.length > 1 ? ' publications' : ' publication');

      if (!rows.length) {
        els.rows.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>Aucune publication ne correspond aux filtres.</p></div>';
        return;
      }

      els.rows.innerHTML = rows.map((obj, index) => {
        const owner = Number(obj.passager_id) === currentUserId;
        const commentsCount = commentsForObject(obj.id).length;
        const ownerActions = owner
          ? '<button class="action-btn" data-action="edit" data-id="' + obj.id + '"><i class="fas fa-pen"></i> Modifier</button>' +
            '<button class="action-btn action-danger" data-action="delete" data-id="' + obj.id + '"><i class="fas fa-trash"></i> Supprimer</button>'
          : '<button class="action-btn" type="button" disabled>Lecture seule</button><button class="action-btn" type="button" disabled>-</button>';

        return '<article class="lost-card" style="animation-delay:' + (index * 0.06).toFixed(2) + 's">' +
          '<div class="card-head">' +
            '<span class="card-id">#' + obj.id + '</span>' +
            '<span class="status-badge ' + statusClass(obj.statut || 'perdu') + '"><i class="fas fa-circle"></i> ' + escapeHtml(statusLabel(obj.statut || 'perdu')) + '</span>' +
          '</div>' +
          '<div class="card-content">' +
            '<div class="card-title">' + escapeHtml(titleLabel(obj)) + '</div>' +
            '<div class="card-desc">' + escapeHtml(descLabel(obj)) + '</div>' +
            '<div class="tags">' +
              '<span class="tag"><i class="fas fa-user"></i> ' + escapeHtml(declarantName(obj)) + '</span>' +
              '<span class="tag"><i class="fas fa-layer-group"></i> ' + escapeHtml(labelCategory(obj.categorie || 'autre')) + '</span>' +
              '<span class="tag"><i class="fas fa-location-dot"></i> ' + escapeHtml(placeLabel(obj)) + '</span>' +
              '<span class="tag"><i class="fas fa-calendar-days"></i> ' + escapeHtml(formatDate(obj.date_perte)) + '</span>' +
              '<span class="tag"><i class="fas fa-comments"></i> ' + commentsCount + '</span>' +
            '</div>' +
            '<div class="card-actions">' +
              '<button class="action-btn" data-action="detail" data-id="' + obj.id + '"><i class="fas fa-eye"></i> Details</button>' +
              '<button class="action-btn" data-action="thread" data-id="' + obj.id + '"><i class="fas fa-comments"></i> Commentaires</button>' +
              ownerActions +
            '</div>' +
          '</div>' +
        '</article>';
      }).join('');
    }

    function renderAll() {
      renderStats();
      renderRows();
    }

    function openDetail(id) {
      const obj = getObjectById(id);
      if (!obj) {
        return;
      }

      activePostId = id;
      const comments = commentsForObject(id);
      document.getElementById('detailTitle').textContent = 'Details publication #' + obj.id;

      const commentsHtml = comments.length
        ? comments.map((c) => (
          '<div class="comment"><div class="comment-meta">#' + c.id + ' - Conducteur #' + escapeHtml(String(c.conducteur_id)) + ' - ' + escapeHtml(formatDateTime(c.date_signalement)) + '</div><div>' + escapeHtml(c.message) + '</div></div>'
        )).join('')
        : '<div class="comment"><div class="comment-meta">Aucun commentaire</div><div>Pas encore de signalement conducteur.</div></div>';

      document.getElementById('detailBody').innerHTML =
        '<div class="comment" style="margin-bottom:.7rem">' +
          '<div class="modal-title" style="margin-bottom:.35rem">' + escapeHtml(titleLabel(obj)) + '</div>' +
          '<div class="muted">Declarant: ' + escapeHtml(declarantName(obj)) + '</div>' +
          '<div class="muted">Categorie: ' + escapeHtml(labelCategory(obj.categorie || 'autre')) + ' | Statut: ' + escapeHtml(statusLabel(obj.statut || 'perdu')) + '</div>' +
          '<div class="muted">Lieu: ' + escapeHtml(placeLabel(obj)) + ' | Date: ' + escapeHtml(formatDate(obj.date_perte)) + '</div>' +
          '<p style="margin-top:.45rem">' + escapeHtml(obj.description || '') + '</p>' +
        '</div>' +
        '<div class="modal-title" style="margin-bottom:.5rem">Commentaires</div>' +
        commentsHtml +
        '<form id="addCommentForm" class="comment-box">' +
          '<textarea id="newComment" placeholder="Ajouter un commentaire conducteur"></textarea>' +
          '<button class="btn-main" style="margin-top:.5rem" type="submit"><i class="fas fa-paper-plane"></i> Publier commentaire</button>' +
        '</form>';

      els.detailModal.classList.add('open');

      document.getElementById('addCommentForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const msg = document.getElementById('newComment').value.trim();
        if (!msg) {
          return;
        }

        submitServerAction('add_comment', {
          declaration_id: id,
          conducteur_id: 100 + currentUserId,
          message: msg
        });
      });
    }

    function openThread(id) {
      const obj = getObjectById(id);
      if (!obj) {
        return;
      }

      activePostId = id;
      const comments = commentsForObject(id);
      document.getElementById('threadTitle').textContent = 'Commentaires publication #' + obj.id;
      document.getElementById('threadPreviewImage').src = obj.photo_url || 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1200&q=60';

      const commentsHtml = comments.length
        ? comments.map((c) => (
          '<div class="comment"><div class="comment-meta">#' + c.id + ' - Conducteur #' + escapeHtml(String(c.conducteur_id)) + ' - ' + escapeHtml(formatDateTime(c.date_signalement)) + '</div><div>' + escapeHtml(c.message) + '</div></div>'
        )).join('')
        : '<div class="comment"><div class="comment-meta">Aucun commentaire</div><div>Ajoutez le premier commentaire pour cette publication.</div></div>';

      document.getElementById('threadBody').innerHTML = commentsHtml +
        '<form id="newRootComment" class="comment-box">' +
          '<textarea id="rootMsg" placeholder="Nouveau commentaire principal"></textarea>' +
          '<button class="btn-main" style="margin-top:.5rem" type="submit"><i class="fas fa-plus"></i> Publier</button>' +
        '</form>';

      els.threadModal.classList.add('open');

      document.getElementById('newRootComment').addEventListener('submit', (e) => {
        e.preventDefault();
        const message = document.getElementById('rootMsg').value.trim();
        if (!message) {
          return;
        }

        submitServerAction('add_comment', {
          declaration_id: id,
          conducteur_id: 100 + currentUserId,
          message
        });
      });
    }

    function editPost(id) {
      const obj = getObjectById(id);
      if (!obj || Number(obj.passager_id) !== currentUserId) {
        return;
      }

      const nextTitle = prompt('Nouveau titre:', titleLabel(obj));
      if (nextTitle === null) {
        return;
      }

      const nextDescription = prompt('Nouvelle description:', obj.description || '');
      if (nextDescription === null) {
        return;
      }

      const nextCategory = prompt('Categorie (electronique, vetement, document, bagage, autre):', obj.categorie || 'autre');
      if (nextCategory === null) {
        return;
      }

      const nextPlace = prompt('Lieu de perte:', obj.lieu_perte || placeLabel(obj));
      if (nextPlace === null) {
        return;
      }

      const nextDate = prompt('Date de perte (YYYY-MM-DD):', obj.date_perte || '');
      if (nextDate === null) {
        return;
      }

      const allowed = ['electronique', 'vetement', 'document', 'bagage', 'autre'];
      const normalizedCategory = String(nextCategory).trim().toLowerCase();
      const normalizedTitle = String(nextTitle).trim();
      const normalizedDescription = String(nextDescription).trim();
      const normalizedPlace = String(nextPlace).trim();
      const normalizedDate = String(nextDate).trim();

      if (!hasLengthBetween(normalizedTitle, 5, 120)
        || !hasLengthBetween(normalizedDescription, 10, 1200)
        || !allowed.includes(normalizedCategory)
        || !hasLengthBetween(normalizedPlace, 3, 120)
        || !isValidDateYmd(normalizedDate)
        || !isDateNotInFuture(normalizedDate)) {
        alert('Modification invalide. Verifiez les champs saisis.');
        return;
      }

      submitServerAction('update_declaration', {
        id,
        titre: normalizedTitle,
        description: normalizedDescription,
        categorie: normalizedCategory,
        lieu_perte: normalizedPlace,
        date_perte: normalizedDate
      });
    }

    function removePost(id) {
      const obj = getObjectById(id);
      if (!obj || Number(obj.passager_id) !== currentUserId) {
        return;
      }

      if (!confirm('Supprimer cette publication ?')) {
        return;
      }

      submitServerAction('delete_declaration', { id });
    }

    function bindRowsActions() {
      els.rows.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) {
          return;
        }
        const action = btn.getAttribute('data-action');
        const id = Number(btn.getAttribute('data-id'));

        if (action === 'detail') {
          openDetail(id);
          return;
        }

        if (action === 'thread') {
          openThread(id);
          return;
        }

        if (action === 'edit') {
          editPost(id);
          return;
        }

        if (action === 'delete') {
          removePost(id);
        }
      });
    }

    function bindFilters() {
      els.searchInput.addEventListener('input', renderRows);
      els.filterCategory.addEventListener('change', renderRows);
      els.filterStatus.addEventListener('change', renderRows);

      els.resetFilters.addEventListener('click', () => {
        els.searchInput.value = '';
        els.filterCategory.value = '';
        els.filterStatus.value = '';
        renderRows();
      });
    }

    function bindPublishForm() {
      ['title','description','place'].forEach((fieldId) => {
        document.getElementById(fieldId).addEventListener('input', () => clearPublishFieldError(fieldId));
      });
      ['category','objectStatus','lostDate','photoFile'].forEach((fieldId) => {
        document.getElementById(fieldId).addEventListener('change', () => clearPublishFieldError(fieldId));
      });

      els.publishForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearPublishValidation();

        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const category = document.getElementById('category').value.trim();
        const objectStatus = document.getElementById('objectStatus').value.trim();
        const place = document.getElementById('place').value.trim();
        const photoFile = document.getElementById('photoFile').files[0] || null;
        const date = document.getElementById('lostDate').value.trim();
        const allowedCategories = ['electronique', 'vetement', 'document', 'bagage', 'autre'];
        const allowedStatus = ['perdu', 'retrouve', 'restitue'];
        const errors = {};

        if (!hasLengthBetween(title, 5, 120)) {
          errors.title = 'Titre invalide (entre 5 et 120 caracteres).';
        }

        if (!hasLengthBetween(description, 10, 1200)) {
          errors.description = 'Description invalide (entre 10 et 1200 caracteres).';
        }

        if (!allowedCategories.includes(category)) {
          errors.category = 'Choisissez une categorie valide.';
        }

        if (!allowedStatus.includes(objectStatus)) {
          errors.objectStatus = 'Choisissez un statut valide.';
        }

        if (!hasLengthBetween(place, 3, 120)) {
          errors.place = 'Lieu de perte invalide (entre 3 et 120 caracteres).';
        }

        if (!isValidDateYmd(date) || !isDateNotInFuture(date)) {
          errors.lostDate = 'Date de perte invalide ou future.';
        }

        const imageError = getImageValidationError(photoFile);
        if (imageError) {
          errors.photoFile = imageError;
        }

        const errorFields = Object.keys(errors);
        if (errorFields.length > 0) {
          setPublishFormError('Veuillez corriger les champs en rouge.');
          errorFields.forEach((fieldId) => setPublishFieldError(fieldId, errors[fieldId]));
          const firstField = document.getElementById(errorFields[0]);
          if (firstField) firstField.focus();
          return;
        }

        let photoUrl = '';
        if (photoFile) {
          try {
            photoUrl = await readFileAsDataUrl(photoFile);
          } catch (_) {
            setPublishFormError('Impossible de lire l image selectionnee.');
            setPublishFieldError('photoFile', 'Reessayez avec une autre image.');
            return;
          }
        }

        submitServerAction('create_declaration', {
          titre: title,
          description,
          categorie: category,
          statut: objectStatus,
          lieu_perte: place,
          photo_url: photoUrl,
          date_perte: date,
          trajet_id: 201 + (Math.floor(Math.random() * 5)),
          passager_id: currentUserId
        });
      });
    }

    function bindModals() {
      document.getElementById('closeDetail').addEventListener('click', () => els.detailModal.classList.remove('open'));
      document.getElementById('closeThread').addEventListener('click', () => els.threadModal.classList.remove('open'));
      document.getElementById('closePublish').addEventListener('click', () => els.publishModal.classList.remove('open'));
      document.getElementById('closeUsers').addEventListener('click', () => document.getElementById('usersModal').classList.remove('open'));

      [els.detailModal, els.threadModal, els.publishModal, document.getElementById('usersModal')].forEach((modal) => {
        modal.addEventListener('click', (e) => {
          if (e.target === modal) {
            modal.classList.remove('open');
          }
        });
      });

      document.getElementById('openPublishModalBtn').addEventListener('click', () => {
        els.publishModal.classList.add('open');
        document.getElementById('title').focus();
      });
    }

    function bindNavbar() {
      const menuBtn = document.getElementById('menuBtn');
      const dropdown = document.getElementById('dropdownMenu');
      const quickThreadLink = document.getElementById('quickThreadLink');
      const openUsersTab = document.getElementById('openUsersTab');

      menuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('show');
      });

      window.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
          dropdown.classList.remove('show');
        }
      });

      quickThreadLink.addEventListener('click', (e) => {
        e.preventDefault();
        dropdown.classList.remove('show');
        if (!objets.length) {
          return;
        }
        const target = getObjectById(activePostId) || objets[objets.length - 1];
        if (target) {
          openThread(target.id);
        }
      });

      openUsersTab.addEventListener('click', (e) => {
        e.preventDefault();
        dropdown.classList.remove('show');
        document.getElementById('usersModal').classList.add('open');
      });

      const themeToggle = document.getElementById('themeToggle');
      if (localStorage.getItem('theme_front_lostfound') === 'light') {
        document.body.classList.add('light-mode');
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
      }

      themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light-mode');
        const isLight = document.body.classList.contains('light-mode');
        localStorage.setItem('theme_front_lostfound', isLight ? 'light' : 'dark');
        themeToggle.innerHTML = isLight ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
      });
    }

    function init() {
      document.getElementById('lostDate').setAttribute('max', currentDateYmd());

      const current = users.find((u) => u.id === currentUserId);
      els.currentUserName.textContent = current ? current.name : 'Utilisateur';

      loadData();

      bindRowsActions();
      bindFilters();
      bindPublishForm();
      bindModals();
      bindNavbar();
      renderUsers();
      renderAll();

      window.addEventListener('storage', () => {
        renderAll();
      });
    }

    init();
  </script>
</body>
</html>
