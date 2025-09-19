<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$message = '';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
    header('Location: login.php');
    exit;
}
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Suppression
if (isset($_GET['del'])) {
    $immat = $_GET['del'];
    $stmt = $bdd->prepare("DELETE FROM vehicule WHERE immatriculation = ?");
    if ($stmt->execute([$immat])) {
        $_SESSION['message'] = "Véhicule supprimé avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $immat = trim($_POST['immatriculation'] ?? '');
    $marque = trim($_POST['marque'] ?? '');
    $modele = trim($_POST['modele'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $statut = $_POST['statut'] ?? 'hors service';

    if ($immat === '' || $marque === '' || $modele === '' || $type === '') {
        $message = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $stmt_check = $bdd->prepare("SELECT * FROM vehicule WHERE immatriculation = ?");
        $stmt_check->execute([$immat]);
        $exists = $stmt_check->fetch();

        if ($exists) {
            $stmt = $bdd->prepare("UPDATE vehicule SET marque=?, modele=?, type=?, statut=? WHERE immatriculation=?");
            $success = $stmt->execute([$marque, $modele, $type, $statut, $immat]);
            $_SESSION['message'] = $success ? "Véhicule modifié avec succès." : "Erreur lors de la modification.";
        } else {
            $stmt = $bdd->prepare("INSERT INTO vehicule (immatriculation, marque, modele, type, statut) VALUES (?, ?, ?, ?, ?)");
            $success = $stmt->execute([$immat, $marque, $modele, $type, $statut]);
            $_SESSION['message'] = $success ? "Véhicule ajouté avec succès." : "Erreur lors de l'ajout.";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Récupérer tous les véhicules
$vehicules = $bdd->query("SELECT * FROM vehicule ORDER BY marque, modele")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestion des Véhicules</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      background-color: #f4f6f9;
    }
    .dashboard {
      display: flex;
      min-height: 100vh;
    }
    main.main-content {
      flex-grow: 1;
      padding: 30px;
      background-color: #ecf0f1;
      overflow-y: auto;
    }
  </style>
</head>
<body>
<div class="dashboard">
  <?php include 'sidebar.php'; ?>
  <main class="main-content">
    <h1 class="mb-4">Gestion des Véhicules</h1>

    <?php if ($message): ?>
      <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#vehiculeModal" onclick="openModal()">+ Ajouter un véhicule</button>

    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Immatriculation</th>
          <th>Marque</th>
          <th>Modèle</th>
          <th>Type</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vehicules as $v): ?>
        <tr>
          <td><?= htmlspecialchars($v['immatriculation']) ?></td>
          <td><?= htmlspecialchars($v['marque']) ?></td>
          <td><?= htmlspecialchars($v['modele']) ?></td>
          <td><?= htmlspecialchars($v['type']) ?></td>
          <td><?= htmlspecialchars($v['statut']) ?></td>
          <td>
            <button 
              class="btn btn-sm btn-primary"
              onclick='openModal(<?= json_encode($v, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
              Modifier
            </button>
            <a href="?del=<?= urlencode($v['immatriculation']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>

<!-- Modal -->
<div class="modal fade" id="vehiculeModal" tabindex="-1" aria-labelledby="vehiculeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content" id="vehiculeForm">
      <div class="modal-header">
        <h5 class="modal-title" id="vehiculeModalLabel">Ajouter / Modifier un véhicule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="immatriculation" class="form-label">Immatriculation *</label>
          <input type="text" name="immatriculation" id="immatriculation" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="marque" class="form-label">Marque *</label>
          <input type="text" name="marque" id="marque" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="modele" class="form-label">Modèle *</label>
          <input type="text" name="modele" id="modele" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="type" class="form-label">Type *</label>
          <input type="text" name="type" id="type" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="statut" class="form-label">Statut</label>
          <select name="statut" id="statut" class="form-select">
            <option value="en service">En service</option>
            <option value="en maintenance">En maintenance</option>
            <option value="hors service">Hors service</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const vehiculeModal = new bootstrap.Modal(document.getElementById('vehiculeModal'));
  const vehiculeForm = document.getElementById('vehiculeForm');

  function openModal(vehicule = null) {
    vehiculeForm.reset();
    if (vehicule) {
      document.getElementById('vehiculeModalLabel').textContent = 'Modifier un véhicule';
      document.getElementById('immatriculation').value = vehicule.immatriculation;
      document.getElementById('immatriculation').readOnly = true;
      document.getElementById('marque').value = vehicule.marque;
      document.getElementById('modele').value = vehicule.modele;
      document.getElementById('type').value = vehicule.type;
      document.getElementById('statut').value = vehicule.statut;
    } else {
      document.getElementById('vehiculeModalLabel').textContent = 'Ajouter un véhicule';
      document.getElementById('immatriculation').readOnly = false;
    }
    vehiculeModal.show();
  }
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
