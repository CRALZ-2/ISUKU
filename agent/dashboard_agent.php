<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.php');
    exit;
}

$id_agent = $_SESSION['id_utilisateur'] ?? null;
$nom_agent = $_SESSION['nom'] ?? 'Agent';

$stmt = $bdd->query("SELECT COUNT(*) AS total_clients FROM utilisateur WHERE role = 'client'");
$totalClients = $stmt->fetch(PDO::FETCH_ASSOC)['total_clients'] ?? 0;

$stmt = $bdd->prepare("SELECT COUNT(*) FROM collecte WHERE id_agent = ? AND statut = 'effectuée'");
$stmt->execute([$id_agent]);
$totalCollectes = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Agent</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <style>
    body {
      background-color: #f1f3f6;
      font-family: 'Segoe UI', sans-serif;
      padding-top: 70px;
    }

    .navbar {
      background-color: #263238;
    }

    .nav-link {
      color: white !important;
      font-weight: 500;
      transition: 0.3s;
      border-radius: 6px;
    }

    .nav-link:hover {
      background-color: #37474f;
      color: #00bcd4 !important;
    }

    .btn-deconnexion {
      background-color: #e53935;
      color: #fff !important;
      border-radius: 20px;
      padding: 6px 15px;
      font-size: 0.9rem;
    }

    .btn-deconnexion:hover {
      background-color: #c62828;
    }

    .dashboard-header {
      margin-bottom: 25px;
      padding: 30px 20px;
      background: linear-gradient(to right, #00b09b, #96c93d);
      color: white;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      text-align: center;
    }

    .dashboard-header h2 {
      margin-bottom: 10px;
      font-weight: bold;
    }

    .dashboard-cards .card {
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transition: transform 0.2s ease-in-out;
    background-color: #ffffff;
    height: 100%;
  }

  .dashboard-cards .card:hover {
    transform: translateY(-5px);
  }

  .dashboard-cards i {
    font-size: 2.5rem;
  }

  .count-display {
    margin-top: 5px;
    font-weight: bold;
    font-size: 1.4rem;
    color: #333;
  }

  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top shadow">
  <div class="container">
    <img src="./images/logo1.png" alt="Logo" style="height: 40px; margin-right: 10px;">
    <h5 class="mb-0 text-white">
      <strong style="color: #0d53b1;">ISUKU</strong><span style="color: #0d9855;"> CO.</span>
    </h5>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item mx-2"><a class="nav-link" href="dashboard_agent.php"><i class="fas fa-home"></i> Accueil</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="zone_agent.php"><i class="fas fa-map-marked-alt"></i> Zones</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="collecte_agent.php"><i class="fas fa-trash"></i> Collectes</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="mon_profil.php"><i class="fas fa-user"></i> Mon Profil</a></li>
        <li class="nav-item ms-3"><a class="btn btn-deconnexion" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">

  <div class="dashboard-header">
    <h2>Bienvenue, <?= htmlspecialchars($nom_agent) ?> 👋</h2>
    <p class="mb-0">Accédez rapidement à vos fonctions principales ci-dessous.</p>
  </div>

  <div class="row g-4 dashboard-cards">
    <div class="col-sm-6 col-lg-3 d-flex">
      <a href="zone_agent.php" class="text-decoration-none w-100">
        <div class="card text-center p-4">
          <i class="fas fa-map-marked-alt text-primary"></i>
          <h5 class="card-title mt-3">Zones assignées</h5>
        </div>
      </a>
    </div>

    <div class="col-sm-6 col-lg-3 d-flex">
      <a href="collecte_agent.php" class="text-decoration-none w-100">
        <div class="card text-center p-4">
          <i class="fas fa-trash text-success"></i>
          <h5 class="card-title mt-3">Mes Collectes</h5>
          <div class="count-display"><?= htmlspecialchars($totalCollectes) ?></div>
        </div>
      </a>
    </div>

    <div class="col-sm-6 col-lg-3 d-flex">
      <a href="mon_profil.php" class="text-decoration-none w-100">
        <div class="card text-center p-4">
          <i class="fas fa-user text-info"></i>
          <h5 class="card-title mt-3">Mon Profil</h5>
        </div>
      </a>
    </div>

    <div class="col-sm-6 col-lg-3 d-flex">
      <a href="enregistrement_client.php" class="text-decoration-none w-100">
        <div class="card text-center p-4">
          <i class="fas fa-user-plus text-warning"></i>
          <h5 class="card-title mt-3">Ajouter un Client</h5>
          <div class="count-display"><?= htmlspecialchars($totalClients) ?></div>
        </div>
      </a>
    </div>
  </div>
  <div class="mt-5 p-4 rounded bg-white shadow-sm">
  <h4 class="mb-3 text-success"><i class="fas fa-leaf"></i> Votre mission</h4>
  <p>En tant qu'agent, vous jouez un rôle clé dans le maintien d'un environnement propre pour notre communauté.</p>
  <p class="mb-4"><strong>Astuce :</strong> pensez à vérifier régulièrement vos zones et vos clients actifs pour éviter les oublis.</p>

  <div class="border-start border-3 ps-3 text-secondary" style="border-color: #0d9855 !important;">
    <i class="fas fa-quote-left fa-sm me-2 text-success"></i>
    <em style="font-size: 1.1rem;">Un environnement propre est un environnement sain.</em>
    <div class="mt-2 text-end"><small>– ISUKU CO.</small></div>
  </div>
</div>


</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  let inactivityTime = function () {
    let timer;
    let timeoutDuration = 300000; // 5 minutes en ms

    function resetTimer() {
      clearTimeout(timer);
      timer = setTimeout(() => {
        window.location.href = "logout.php?timeout=1";
      }, timeoutDuration);
    }

    // Détection des activités de l'utilisateur
    window.onload = resetTimer;
    document.onmousemove = resetTimer;
    document.onkeypress = resetTimer;
    document.onclick = resetTimer;
    document.onscroll = resetTimer;
  };

  inactivityTime();
</script>
</body>
</html>
