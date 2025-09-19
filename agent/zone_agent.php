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

// Zones assignées
$zones = $bdd->prepare("SELECT z.* FROM attribution_zone az 
    JOIN zone z ON az.id_zone = z.id_zone 
    WHERE az.id_utilisateur = ? AND az.statut = 'actif'");
$zones->execute([$id_agent]);
$zones = $zones->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Zone -Agent</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <style>
    body {
      background-color: #f5f7fa;
      font-family: 'Segoe UI', sans-serif;
      padding-top: 70px;
      min-height: 100vh;
    }
    .card {
      border-radius: 16px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
      transition: transform 0.2s;
      min-height: 300px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .card:hover { transform: translateY(-3px); }
    .card-title { font-weight: 600; }
    .navbar { background-color: #263238; }
    .nav-link {
      color: #ffffff !important;
      font-weight: 500;
      font-size: 1.05rem;
      padding: 10px 15px;
      transition: 0.3s;
      border-radius: 6px;
    }
    .nav-link:hover {
      background-color: #37474f;
      color: #00bcd4 !important;
    }
    .btn-deconnexion {
      background-color: #f44336;
      color: white !important;
      border-radius: 20px;
      padding: 6px 15px;
      font-size: 0.9rem;
    }
    .btn-deconnexion:hover {
      background-color: #d32f2f;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top shadow animate__animated animate__fadeInDown">
  <div class="container">
    <img src="./images/logo1.png" alt="Logo Recyclage" style="height: 40px; margin-right: 2px;">
    <h5 class="mb-0">
      <strong style="font-weight: 700; font-size: 20px; color: #0d53b1;">ISUKU</strong>
      <span style="font-weight: 400; font-size: 20px; color: #0d9855;">CO.</span>
    </h5>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item mx-2">
          <a class="nav-link" href="dashboard_agent.php"><i class="fas fa-home"></i> Accueil</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="zone_agent.php"><i class="fas fa-map-marked-alt"></i> Zones</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="collecte_agent.php"><i class="fas fa-trash"></i> Collectes</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="mon_profil.php"><i class="fas fa-user"></i> Mon Profil</a>
        </li>
        <li class="nav-item ms-3">
          <a class="btn btn-deconnexion" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <h2 class="mb-4 mt-2">Bienvenue, <?= $_SESSION['nom'] ?? 'Agent' ?></h2>
  <p class="lead">Voici vos zones de collecte actuellement assignées.</p>

  <div class="alert alert-info">Total de zones assignées : <?= count($zones) ?></div>

  <!-- Zones assignées -->
  <div class="card mb-4" id="zones">
    <div class="card-body">
      <h4 class="card-title">📍 Zones de collecte</h4>
      <?php if ($zones): ?>
        <ul class="list-group">
          <?php foreach ($zones as $zone): ?>
            <li class="list-group-item">
              <?= htmlspecialchars($zone['nom_quartier']) ?> - <?= htmlspecialchars($zone['commune']) ?>, <?= htmlspecialchars($zone['province']) ?>
              <span class="badge bg-primary float-end">
                <?= number_format($zone['tarif_mensuel'], 0, ',', ' ') ?> FBu/mois
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="text-center text-muted">
          <img src="images/no-zones.svg" alt="Aucune zone" style="max-height: 200px;" class="mb-3">
          <p>Aucune zone de collecte ne vous a encore été assignée.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

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
