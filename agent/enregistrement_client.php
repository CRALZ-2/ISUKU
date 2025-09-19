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

$message = '';
$modification = false;
$clientData = [];

// Vérifier si on est en modification
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $stmt = $bdd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ? AND role = 'client'");
    $stmt->execute([$id_edit]);
    $clientData = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($clientData) {
        $modification = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_utilisateur'] ?? uniqid('cli_');
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $pays = $_POST['pays'] ?? '';
    $province = $_POST['province'] ?? '';
    $commune = $_POST['commune'] ?? '';
    $quartier = $_POST['quartier'] ?? '';
    $avenue = $_POST['avenue'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    try {
        if (isset($_POST['modifier'])) {
            if (!empty($mot_de_passe)) {
                $mot_de_passe = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $stmt = $bdd->prepare("UPDATE utilisateur SET nom=?, prenom=?, genre=?, pays=?, province=?, commune=?, quartier=?, avenue=?, telephone=?, email=?, mot_de_passe=? WHERE id_utilisateur=?");
                $stmt->execute([$nom, $prenom, $genre, $pays, $province, $commune, $quartier, $avenue, $telephone, $email, $mot_de_passe, $id]);
            } else {
                $stmt = $bdd->prepare("UPDATE utilisateur SET nom=?, prenom=?, genre=?, pays=?, province=?, commune=?, quartier=?, avenue=?, telephone=?, email=? WHERE id_utilisateur=?");
                $stmt->execute([$nom, $prenom, $genre, $pays, $province, $commune, $quartier, $avenue, $telephone, $email, $id]);
            }
            $message = 'Client modifié avec succès';
        } else {
            $mot_de_passe = password_hash('client123', PASSWORD_DEFAULT);
            $stmt = $bdd->prepare("INSERT INTO utilisateur (id_utilisateur, nom, prenom, genre, pays, province, commune, quartier, avenue, telephone, email, role, mot_de_passe) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'client', ?)");
            $stmt->execute([$id, $nom, $prenom, $genre, $pays, $province, $commune, $quartier, $avenue, $telephone, $email, $mot_de_passe]);
            $message = 'Client enregistré avec succès';
        }
        header("Refresh:1");
    } catch (PDOException $e) {
        $message = 'Erreur : ' . $e->getMessage();
    }
}

$clients = $bdd->query("SELECT * FROM utilisateur WHERE role = 'client' ORDER BY date_inscription DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des Clients</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <style>
    body { background-color: #f5f7fa; font-family: 'Segoe UI', sans-serif; padding-top: 40px; }
    .card { border-radius: 16px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .card:hover { transform: translateY(-3px); }
    .card-title { font-weight: 600; }
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

<div class="container mt-5 pt-4">
  <?php if ($message): ?>
    <div class="alert alert-info text-center"> <?= htmlspecialchars($message) ?> </div>
  <?php endif; ?>

  <h2 class="mb-4"><?= $modification ? 'Modifier' : 'Enregistrer' ?> un client</h2>
  <form method="POST">
    <input type="hidden" name="id_utilisateur" value="<?= $clientData['id_utilisateur'] ?? '' ?>">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label>Nom</label>
        <input type="text" name="nom" class="form-control" required value="<?= $clientData['nom'] ?? '' ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label>Prénom</label>
        <input type="text" name="prenom" class="form-control" required value="<?= $clientData['prenom'] ?? '' ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label>Genre</label>
        <select name="genre" class="form-control" required>
          <option value="">-- Choisir --</option>
          <option value="H" <?= (isset($clientData['genre']) && $clientData['genre'] === 'M') ? 'selected' : '' ?>>Homme</option>
          <option value="F" <?= (isset($clientData['genre']) && $clientData['genre'] === 'F') ? 'selected' : '' ?>>Femme</option>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label>Pays</label>
        <input type="text" name="pays" class="form-control" required value="<?= $clientData['pays'] ?? 'Burundi' ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label>Province</label>
        <input type="text" name="province" class="form-control" required value="<?= $clientData['province'] ?? '' ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label>Commune</label>
        <input type="text" name="commune" class="form-control" required value="<?= $clientData['commune'] ?? '' ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label>Quartier</label>
        <input type="text" name="quartier" class="form-control" required value="<?= $clientData['quartier'] ?? '' ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label>Avenue</label>
        <input type="text" name="avenue" class="form-control" required value="<?= $clientData['avenue'] ?? '' ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label>Téléphone</label>
        <input type="text" name="telephone" class="form-control" required value="<?= $clientData['telephone'] ?? '' ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required value="<?= $clientData['email'] ?? '' ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label>Mot de passe (laisser vide si inchangé)</label>
        <input type="password" name="mot_de_passe" class="form-control">
      </div>
    </div>
    <button type="submit" name="<?= $modification ? 'modifier' : 'ajouter' ?>" class="btn btn-success"><?= $modification ? 'Modifier' : 'Enregistrer' ?></button>
    <a href="dashboard_agent.php" class="btn btn-secondary">Retour</a>
  </form>

  <hr>
  <h3 class="mt-5">Liste des clients</h3>
  <table class="table table-bordered table-hover mt-3">
    <thead class="table-dark">
      <tr>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Genre</th>
        <th>Téléphone</th>
        <th>Email</th>
        <th>Adresse</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($clients as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['nom']) ?></td>
          <td><?= htmlspecialchars($c['prenom']) ?></td>
          <td><?= htmlspecialchars($c['genre']) ?></td>
          <td><?= htmlspecialchars($c['telephone']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['quartier'] . ', ' . $c['commune'] . ', ' . $c['province']) ?></td>
          <td><?= htmlspecialchars($c['date_inscription']) ?></td>
          <td>
            <a href="?edit=<?= $c['id_utilisateur'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
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
