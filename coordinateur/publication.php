<?php
session_start();
include('auth_session.php');

// Connexion à la BDD
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Vérification rôle coordinateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
  header("Location: login.php");
  exit;
}

// Traitement publication
$success_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publier'])) {
  $titre = trim($_POST['titre']);
  $contenu = trim($_POST['contenu']);
  if (!empty($titre) && !empty($contenu)) {
    $auteur = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
    $stmt = $bdd->prepare("INSERT INTO publication (titre, contenu, auteur) VALUES (?, ?, ?)");
    $stmt->execute([$titre, $contenu, $auteur]);
    $success_msg = "✅ Publication enregistrée.";
  }
}

// Suppression
if (isset($_GET['delete'])) {
  $bdd->prepare("DELETE FROM publication WHERE id_publication = ?")->execute([$_GET['delete']]);
  header("Location: publication.php");
  exit;
}

// Récupération des publications
$publications = $bdd->query("SELECT * FROM publication ORDER BY date_publication DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Publications - Coordinateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      display: flex;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f6f8;
    }

    .main-content {
      flex-grow: 1;
      padding: 40px 20px;
      margin-left: 250px;
    }

    .btn-publish {
      background-color: #0d9855;
      border: none;
    }

    .btn-publish:hover {
      background-color: #0b7f45;
    }

    .btn-delete {
      background-color: #f44336;
      border: none;
    }

    .btn-delete:hover {
      background-color: #d32f2f;
    }

    .card-header.bg-pub {
      background-color: #0d53b1;
      color: white;
    }

    .card-footer {
      font-size: 0.85rem;
      color: #555;
    }

    .alert {
      max-width: 800px;
    }
  </style>
</head>
<body>

  <?php include('sidebar.php'); ?>

  <div class="main-content">
    <h4 class="mb-4">🗞️ Portail de publications</h4>

    <?php if (!empty($success_msg)) : ?>
      <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>

    <!-- Formulaire -->
    <form method="post" class="bg-white border rounded shadow-sm p-4 mb-5" style="max-width: 800px;">
      <h5 class="mb-3 text-primary">📢 Nouvelle publication</h5>
      <div class="mb-3">
        <label>Titre</label>
        <input type="text" name="titre" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Contenu</label>
        <textarea name="contenu" rows="4" class="form-control" required></textarea>
      </div>
      <button type="submit" name="publier" class="btn btn-publish text-white">Publier</button>
    </form>

    <!-- Liste des publications -->
    <?php if (empty($publications)) : ?>
      <div class="alert alert-info">Aucune publication enregistrée.</div>
    <?php else : ?>
      <?php foreach ($publications as $pub) : ?>
        <div class="card mb-4 shadow-sm" style="max-width: 800px;">
          <div class="card-header bg-pub d-flex justify-content-between align-items-center">
            <strong><?= htmlspecialchars($pub['titre']) ?></strong>
            <a href="publication.php?delete=<?= $pub['id_publication'] ?>" class="btn btn-sm btn-delete text-white" onclick="return confirm('Supprimer cette publication ?')">
              <i class="fas fa-trash"></i>
            </a>
          </div>
          <div class="card-body">
            <?= nl2br(htmlspecialchars($pub['contenu'])) ?>
          </div>
          <div class="card-footer">
            Publié par <strong><?= htmlspecialchars($pub['auteur']) ?></strong> le <?= date('d/m/Y à H:i', strtotime($pub['date_publication'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
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
