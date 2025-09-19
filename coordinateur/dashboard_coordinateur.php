<?php 
session_start(); 
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8', 'root', ''); 
$message = '';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
    header('Location: login.php');
    exit;
}

// Requête pour compter les tournées planifiées
$query = $bdd->query("SELECT COUNT(*) AS total FROM tournee WHERE statut = 'planifiée'");
$result = $query->fetch();
$totalTournees = $result['total'];

// Requête pour compter les clients
$query = $bdd->query("SELECT COUNT(*) AS total FROM utilisateur WHERE role = 'client'");
$result = $query->fetch();
$totalClients = $result['total'];
// Requête pour compter les vehicules
$query = $bdd->query("SELECT COUNT(*) AS total FROM vehicule");
$result = $query->fetch();
$totalVehicules = $result['total'];
// Requête pour gerer les annonces
$query = $bdd->query("SELECT message FROM annonce_collecte");
$result = $query->fetch();
$messageAnnonces = $result['message'];
// Requête pour gerer les annonces
$query = $bdd->query("SELECT COUNT(*) AS total FROM zone");
$result = $query->fetch();
$totalQuartier = $result['total'];
// Requête pour gerer les reclammations
$query = $bdd->query("SELECT COUNT(*) AS total FROM reclamation WHERE statut = 'traitement'");
$result = $query->fetch();
$totalReclamation = $result['total'];


?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Accueil Coordinateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      background-color: #f4f6f9;
      margin: 0;
    }
    .dashboard {
      display: flex;
      height: 100vh;
    }
    .main-content {
      flex-grow: 1;
      background-color: #ecf0f1;
      padding: 30px;
    }
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .welcome {
      font-size: 1.5rem;
      color: #0d53b1;
      font-weight: bold;
    }
    .user-initial {
      background-color: #0d9855;
      color: white;
      border-radius: 50%;
      width: 45px;
      height: 45px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: bold;
      font-size: 1rem;
    }
    .card-title {
      font-size: 1.1rem;
      font-weight: bold;
    }
    .card {
      border-left: 5px solid #0d53b1;
      transition: all 0.3s;
    }
    .card:hover {
      transform: scale(1.02);
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }
    .card-body {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 150px;
}
.list-group-item {
  border: none;
  background-color: #ffffff;
  border-radius: 8px;
  margin-bottom: 10px;
  padding: 15px 20px;
  transition: background-color 0.2s;
}
.list-group-item:hover {
  background-color: #f0f4f8;
}
  </style>
</head>
<body>
  <div class="dashboard">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
      <header>
        <div class="welcome">Bienvenue <?php echo isset($_SESSION["prenom"]) ? $_SESSION["prenom"] . " " . $_SESSION["nom"] : "Coordinateur"; ?></div>
        <div class="user-initial">
          <?php echo strtoupper(substr($_SESSION["prenom"] ?? 'C', 0, 1)); ?>
        </div>
      </header>
      <section class="content mt-4">
              <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        <div class="col">
          <div class="card shadow-sm h-100">
            <div class="card-body text-center">
              <h5 class="card-title">Tournées programmées</h5>
              <p class="card-text display-6"><?php echo $totalTournees; ?></p>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card shadow-sm h-100">
            <div class="card-body text-center">
              <h5 class="card-title">Clients</h5>
              <p class="card-text display-6"><?php echo $totalClients; ?></p>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card shadow-sm h-100">
            <div class="card-body text-center">
              <h5 class="card-title">Véhicules</h5>
              <p class="card-text display-6"><?php echo $totalVehicules; ?></p>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card shadow-sm h-100">
            <div class="card-body text-center">
              <h5 class="card-title">Zones couvertes</h5>
              <p class="card-text display-6"><?php echo $totalQuartier; ?></p>
            </div>
          </div>
        </div>
      </div>
        <div class="mt-5">
          <h4 class="mb-4 fw-bold text-primary">Résumé des activités récentes</h4>
          <div class="list-group shadow-sm">
            <div class="list-group-item d-flex align-items-center">
              <span class="me-3 fs-4 text-success">📍</span>
              <span class="flex-grow-1"><?php echo htmlspecialchars($messageAnnonces); ?></span>
            </div>
            <div class="list-group-item d-flex align-items-center">
              <span class="me-3 fs-4 text-warning">⚠️</span>
              <span class="flex-grow-1">Il y a <strong><?php echo $totalReclamation; ?></strong> reclamation en cours.</span>
            </div>
            <div class="list-group-item d-flex align-items-center">
              <span class="me-3 fs-4 text-info">♻️</span>
              <span class="flex-grow-1">Il existe <strong><?php echo $totalQuartier; ?></strong> quartier(s) aujourd’hui.</span>
            </div>
          </div>
        </div>
      </section>
    </main>
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
