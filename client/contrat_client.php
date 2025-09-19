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

$contrats = $bdd->prepare("SELECT * FROM contrat WHERE id_utilisateur = ? ORDER BY date_debut DESC");
$contrats->execute([$id_client]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes Contrats</title>
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

    .badge.attente {
      background-color: #ffc107;
      color: black;
    }

    .badge.actif {
      background-color: #4caf50;
    }

    .badge.terminé {
      background-color: #2196f3;
    }

    .badge.expiré {
      background-color: #f44336;
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
        <li class="nav-item mx-2"><a class="nav-link active" href="contrat_client.php"><i class="fas fa-file-contract"></i> Contrats</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="mes_factures.php"><i class="fas fa-calendar-plus"></i> Factures</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="mes_reclamations.php"><i class="fas fa-comment-dots"></i> Réclamations</a></li>
        <li class="nav-item ms-3"><a class="btn btn-deconnexion" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h4 class="mb-3">📄 Mes contrats</h4>

  <?php if ($contrats->rowCount() > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Type</th>
            <th>Date début</th>
            <th>Date fin</th>
            <th>Statut</th>
            <th>Justificatif</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contrats as $c): ?>
            <tr>
              <td><?= ucfirst($c['type_contrat']) ?></td>
              <td><?= date('d/m/Y', strtotime($c['date_debut'])) ?></td>
              <td><?= date('d/m/Y', strtotime($c['date_fin'])) ?></td>
              <td>
                <span class="badge 
                  <?= $c['statut'] === 'en attente' ? 'attente' : 
                     ($c['statut'] === 'actif' ? 'actif' : 
                     ($c['statut'] === 'terminé' ? 'terminé' : 'expiré')) ?>">
                  <?= ucfirst($c['statut']) ?>
                </span>
              </td>
              <td>
                <?php if (!empty($c['justificatif']) && file_exists('./uploads/' . $c['justificatif'])): ?>
                  <a href="<?= './uploads/' . $c['justificatif'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                    📥 Télécharger
                  </a>
                <?php else: ?>
                  <span class="text-muted">Aucun</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center">
      Aucun contrat enregistré pour l’instant.
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
