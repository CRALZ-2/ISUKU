<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'chauffeur') {
    header('Location: login.php');
    exit;
}

$id_chauffeur = $_SESSION['id_utilisateur'] ?? null;

// Si le chauffeur confirme une tournée comme terminée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_tournee_terminee'])) {
    $id_tournee_terminee = (int)$_POST['id_tournee_terminee'];
    $stmt = $bdd->prepare("UPDATE tournee SET statut = 'terminée' WHERE id_tournee = ? AND id_chauffeur = ?");
    $stmt->execute([$id_tournee_terminee, $id_chauffeur]);
}

// Tournées assignées
$tournees = $bdd->prepare("SELECT t.*, z.nom_quartier, z.commune, z.province 
    FROM tournee t 
    JOIN zone z ON z.id_zone = t.id_zone 
    WHERE t.id_chauffeur = ? 
    ORDER BY t.date_tournee DESC");
$tournees->execute([$id_chauffeur]);
$tournees = $tournees->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Chauffeur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <style>
    body { background-color: #f5f7fa; font-family: 'Segoe UI', sans-serif; padding-top: 80px; }
    .card { border-radius: 16px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
    .card-title { font-weight: 600; }
    .status-planifiée { background-color: #17a2b8; color: #fff; }
    .status-en-cours { background-color: #ffc107; color: #000; }
    .status-terminée { background-color: #28a745; color: #fff; }
    .navbar { background-color: #263238; }
    .nav-link { color: #ffffff !important; font-weight: 500; font-size: 1.05rem; padding: 10px 15px; transition: 0.3s; border-radius: 6px; }
    .nav-link:hover { background-color: #37474f; color: #00bcd4 !important; }
    .btn-deconnexion { background-color: #f44336; color: white !important; border-radius: 20px; padding: 6px 15px; font-size: 0.9rem; }
    .btn-deconnexion:hover { background-color: #d32f2f; }
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
          <a class="nav-link" href="dashboard_chauffeur.php"><i class="fas fa-truck"></i> Véhicule</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="zone_v.php"><i class="fas fa-map-marked-alt"></i> Zones</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="tournee_v.php"><i class="fas fa-calendar-check"></i> Tournées</a>
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

  <!-- Tournées -->
  <div class="card mb-4" id="tournees">
    <div class="card-body">
      <h4 class="card-title">📆 Tournées assignées</h4>
      <?php if ($tournees): ?>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Date</th>
              <th>Zone</th>
              <th>Commentaire</th>
              <th>Statut</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tournees as $tournee): ?>
              <tr>
                <td><?= htmlspecialchars($tournee['date_tournee']) ?></td>
                <td><?= htmlspecialchars($tournee['nom_quartier']) ?> (<?= htmlspecialchars($tournee['commune']) ?>)</td>
                <td><?= htmlspecialchars($tournee['commentaire'] ?? '-') ?></td>
                <td>
                  <span class="badge 
                    <?= $tournee['statut'] === 'planifiée' ? 'status-planifiée' : 
                        ($tournee['statut'] === 'en cours' ? 'status-en-cours' : 'status-terminée') ?>">
                    <?= ucfirst($tournee['statut']) ?>
                  </span>
                </td>
                <td>
                  <?php if ($tournee['statut'] !== 'terminée'): ?>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="id_tournee_terminee" value="<?= $tournee['id_tournee'] ?>">
                      <button type="submit" class="btn btn-sm btn-success">Marquer comme terminée</button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted">Fini</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted">Aucune tournée assignée.</p>
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
