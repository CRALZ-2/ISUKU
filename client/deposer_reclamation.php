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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $objet = $_POST['objet'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!empty($message)) {
        $stmt = $bdd->prepare("INSERT INTO reclamation (id_client, objet, message) VALUES (?, ?, ?)");
        $stmt->execute([$id_client, $objet, $message]);

        $_SESSION['success_reclamation'] = "✅ Réclamation envoyée avec succès.";
        header("Location: deposer_reclamation.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Déposer une réclamation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
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

    .fade-out {
      transition: opacity 1s ease-in-out;
      opacity: 0;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
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
        <li class="nav-item mx-2"><a class="nav-link" href="dashboard_client.php"><i class="fas fa-home"></i> Accueil</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="contrat_client.php"><i class="fas fa-file-contract"></i> Contrats</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="mes_factures.php"><i class="fas fa-calendar-plus"></i> Factures</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="mes_reclamations.php"><i class="fas fa-comment-dots"></i> Réclamations</a></li>
        <li class="nav-item ms-3"><a class="btn btn-deconnexion" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- MESSAGE FLASH -->
<?php if (isset($_SESSION['success_reclamation'])): ?>
  <div id="successMessage" class="alert alert-success text-center mx-5 mt-3">
    <?= $_SESSION['success_reclamation'] ?>
    <?php unset($_SESSION['success_reclamation']); ?>
  </div>
<?php endif; ?>

<!-- FORMULAIRE -->
<div class="container mt-4">
  <h3 class="mb-4">📝 Déposer une réclamation</h3>
  <form method="post">
    <div class="mb-3">
      <label for="objet" class="form-label">Objet</label>
      <input type="text" name="objet" id="objet" class="form-control" maxlength="150">
    </div>
    <div class="mb-3">
      <label for="message" class="form-label">Message</label>
      <textarea name="message" id="message" rows="5" class="form-control" required></textarea>
    </div>
    <button type="submit" class="btn btn-success">Envoyer</button>
    <a href="mes_reclamations.php" class="btn btn-secondary">Voir mes réclamations</a>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Faire disparaître le message après 2 secondes
  setTimeout(() => {
    const msg = document.getElementById('successMessage');
    if (msg) {
      msg.classList.add('fade-out');
      setTimeout(() => msg.remove(), 1000); // après l’animation
    }
  }, 2000);
</script>

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
