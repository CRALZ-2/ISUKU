<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}

$id_client = $_SESSION['id_utilisateur'];

$reclamations = $bdd->prepare("SELECT * FROM reclamation WHERE id_client = ? ORDER BY date_reclamation DESC");
$reclamations->execute([$id_client]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes réclamations</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      padding-top: 90px;
      background-color: #f5f5f5;
      font-family: 'Segoe UI', sans-serif;
    }

    .navbar {
      background-color: #263238;
    }

    .navbar-brand img {
      height: 40px;
      margin-right: 10px;
    }

    .nav-link {
      color: #ffffff !important;
      font-weight: 500;
      font-size: 1.05rem;
      padding: 10px 15px;
      transition: background-color 0.3s, color 0.3s;
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

    .badge.ouverte {
      background-color: #fbc02d;

    }

    .badge.traitement {
      background-color: #2196f3;
    }

    .badge.résolue {
      background-color: #d9534f;
    }

    table {
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
    }

    .table thead {
      background-color: #0d53b1;
      color: white;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top shadow">
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
        <li class="nav-item mx-2"><a class="nav-link" href="dashboard_client.php"><i class="fas fa-home"></i> Accueil</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="contrat_client.php"><i class="fas fa-file-contract"></i> Contrats</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="mes_factures.php"><i class="fas fa-calendar-plus"></i> Factures</a></li>
        <li class="nav-item mx-2"><a class="nav-link active" href="mes_reclamations.php"><i class="fas fa-comment-dots"></i> Réclamations</a></li>
        <li class="nav-item ms-3"><a class="btn btn-deconnexion" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📬 Mes réclamations</h4>
    <a href="deposer_reclamation.php" class="btn btn-success"><i class="fas fa-plus"></i> Nouvelle réclamation</a>
  </div>

  <?php if ($reclamations->rowCount() > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Date</th>
            <th>Objet</th>
            <th>Message</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($reclamations as $rec): ?>
          <tr>
            <td><?= date('d/m/Y H:i', strtotime($rec['date_reclamation'])) ?></td>
            <td><?= htmlspecialchars($rec['objet'] ?: 'Aucun') ?></td>
            <td><?= nl2br(htmlspecialchars($rec['message'])) ?></td>
            <td>
              <span class="badge 
                <?= $rec['statut'] === 'ouverte' ? 'ouverte' : 
                   ($rec['statut'] === 'traitement' ? 'traitement' : 'résolue') ?>">
                <?= ucfirst($rec['statut']) ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center">
      Vous n'avez encore soumis aucune réclamation.
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
