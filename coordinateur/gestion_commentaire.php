<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$message = '';
$typeMessage = 'info';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'valider') {
        $stmt = $bdd->prepare("UPDATE commentaire SET statut = 'valide' WHERE id_commentaire = ?");
        $stmt->execute([$id]);
        $message = "Commentaire validé avec succès.";
        $typeMessage = 'success';
    } elseif ($_GET['action'] === 'invalider') {
        $stmt = $bdd->prepare("UPDATE commentaire SET statut = 'invalide' WHERE id_commentaire = ?");
        $stmt->execute([$id]);
        $message = "Commentaire invalidé.";
        $typeMessage = 'danger';
    }
    header("Location: gestion_commentaire.php?msg=" . urlencode($message) . "&type=" . $typeMessage);
    exit();
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $typeMessage = $_GET['type'] ?? 'info';
}

$commentaires = $bdd->query("SELECT * FROM commentaire ORDER BY date_commentaire DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Gestion des Commentaires</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      display: flex;
      margin: 0;
      font-family: 'Open Sans', sans-serif;
    }
    .main-content {
      flex-grow: 1;
      padding: 30px;
      background-color: #f5f6fa;
    }
    h1 {
      color: #2c3e50;
      margin-bottom: 20px;
    }
    .action-icon {
      cursor: pointer;
      width: 24px;
      height: 24px;
      vertical-align: middle;
      fill: currentColor;
      transition: transform 0.2s ease;
    }
    .action-icon:hover {
      transform: scale(1.2);
    }
    .valider {
      color: #0d9855;
    }
    .invalider {
      color: #c0392b;
    }
    td.status-valide {
      color: #0d9855;
      font-weight: 600;
    }
    td.status-invalide {
      color: #c0392b;
      font-weight: 600;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <h1>Gestion des Commentaires</h1>

  <?php if (!empty($message)): ?>
    <div class="alert alert-<?= htmlspecialchars($typeMessage) ?>" role="alert">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Nom complet</th>
          <th>Objet</th>
          <th>Message</th>
          <th>Date</th>
          <th>Statut</th>
          <th style="width: 80px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($commentaires as $com): ?>
          <tr>
            <td><?= htmlspecialchars($com['nom_complet']) ?></td>
            <td><?= htmlspecialchars($com['objet']) ?></td>
            <td><?= nl2br(htmlspecialchars($com['message'])) ?></td>
            <td><?= htmlspecialchars($com['date_commentaire']) ?></td>
            <td>
                <span class="badge bg-<?= $com['statut'] === 'valide' ? 'success' : 'danger' ?>">
                <?= ucfirst($com['statut']) ?>
                </span>
            </td>

            <td class="text-center">
              <?php if ($com['statut'] === 'valide'): ?>
                <a href="?action=invalider&id=<?= $com['id_commentaire'] ?>" title="Invalider" class="invalider" aria-label="Invalider">
                  <!-- Croix SVG -->
                  <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" >
                    <path d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7a1 1 0 1 0-1.41 1.42L10.59 12l-4.9 4.88a1 1 0 0 0 1.41 1.42L12 13.41l4.88 4.9a1 1 0 0 0 1.42-1.41L13.41 12l4.9-4.88a1 1 0 0 0 0-1.41z"/>
                  </svg>
                </a>
              <?php else: ?>
                <a href="?action=valider&id=<?= $com['id_commentaire'] ?>" title="Valider" class="valider" aria-label="Valider">
                  <!-- Check SVG -->
                  <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" >
                    <path d="M20.285 6.709a1 1 0 0 0-1.414-1.418L9 15.163l-3.868-3.868a1 1 0 0 0-1.414 1.415l4.576 4.577a1 1 0 0 0 1.414 0l9.577-9.577z"/>
                  </svg>
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
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
