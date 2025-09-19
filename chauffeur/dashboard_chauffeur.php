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

// Véhicule assigné
$vehicule = $bdd->prepare("SELECT v.* FROM assignation_vehicule av 
    JOIN vehicule v ON av.immatriculation = v.immatriculation 
    WHERE av.id_chauffeur= ? AND av.statut = 'actif' 
    ORDER BY av.date_assigned DESC LIMIT 1");
$vehicule->execute([$id_chauffeur]);
$vehicule = $vehicule->fetch(PDO::FETCH_ASSOC);

// Zones assignées
$zones = $bdd->prepare("SELECT z.* FROM attribution_zone az 
    JOIN zone z ON az.id_zone = z.id_zone 
    WHERE az.id_utilisateur = ? AND az.statut = 'actif'");
$zones->execute([$id_chauffeur]);
$zones = $zones->fetchAll(PDO::FETCH_ASSOC);

// Informations du chauffeur
$profil = $bdd->prepare("SELECT nom, email, telephone FROM utilisateur WHERE id_utilisateur = ?");
$profil->execute([$id_chauffeur]);
$profil = $profil->fetch(PDO::FETCH_ASSOC);

$stmt = $bdd->prepare("SELECT COUNT(*) FROM tournee WHERE id_chauffeur = ? AND statut = 'terminée'");
$stmt->execute([$id_chauffeur]);
$totalTournees = $stmt->fetchColumn();
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
    .count-display {
    margin-top: 5px;
    font-weight: bold;
    font-size: 1.4rem;
    color: #333;
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
<div class="text-center mt-5">
  <h2 class="mb-4">Bienvenue sur votre tableau de bord, <?= htmlspecialchars($_SESSION['nom']) ?> !</h2>
  <p class="lead">Utilisez le menu ci-dessus pour consulter vos informations :</p>
  
  <div class="row justify-content-center mt-4">
    <div class="col-md-3 mb-3">
      <a href="zone_v.php" class="text-decoration-none">
        <div class="card text-center shadow-sm p-3">
          <i class="fas fa-map-marked-alt fa-2x text-primary mb-2"></i>
          <h5 class="card-title">Zones assignées</h5>
        </div>
      </a>
    </div>
    <div class="col-md-3 mb-3">
      <a href="tournee_v.php" class="text-decoration-none">
        <div class="card text-center shadow-sm p-3">
          <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
          <h5 class="card-title">Tournées</h5>
          <div class="count-display"><?php echo $totalTournees; ?></div>
        </div>
      </a>
    </div>
    <div class="col-md-3 mb-3">
      <a href="mon_profil.php" class="text-decoration-none">
        <div class="card text-center shadow-sm p-3">
          <i class="fas fa-user fa-2x text-info mb-2"></i>
          <h5 class="card-title">Mon Profil</h5>
        </div>
      </a>
    </div>
  </div>
</div>

<div class="container">
  <h2 class="text-center mb-4">Votre vehicule assigné!</h2>

  <!-- Véhicule assigné -->
  <div class="card mb-4" id="vehicule">
    <div class="card-body">
      <h4 class="card-title">🚛 Véhicule assigné</h4>
      <?php if ($vehicule): ?>
        <p><strong>Marque :</strong> <?= htmlspecialchars($vehicule['marque']) ?></p>
        <p><strong>Modèle :</strong> <?= htmlspecialchars($vehicule['modele']) ?></p>
        <p><strong>Immatriculation :</strong> <?= htmlspecialchars($vehicule['immatriculation']) ?></p>
      <?php else: ?>
        <p class="text-muted">Aucun véhicule actuellement assigné.</p>
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
