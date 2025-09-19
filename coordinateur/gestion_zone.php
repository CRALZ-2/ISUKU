<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
    header('Location: login.php');
    exit;
}

// Suppression
if(isset($_GET['del']))
{
    $recpdel = $_GET['del'];
    $delzone = $bdd->prepare("DELETE FROM zone WHERE id_zone = ?");
    $delzone->execute([$recpdel]);
    if ($delzone->execute([$recpdel])) {
    $_SESSION['message'] = "Zone supprimée avec succès.";
} else {
    $_SESSION['message'] = "Erreur lors de la suppression.";
}
header("Location: " . $_SERVER['PHP_SELF']);
exit();
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id_zone']) && is_numeric($_POST['id_zone']) ? (int)$_POST['id_zone'] : null;
    $nom_quartier = trim($_POST['nom_quartier'] ?? '');
    $commune = trim($_POST['commune'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $tarif_mensuel = $_POST['tarif_mensuel'] ?? '';

    if ($nom_quartier === '' || !is_numeric($tarif_mensuel)) {
        $message = "Veuillez renseigner au minimum le nom du quartier et un tarif mensuel valide.";
    } else {
        if ($id !== null) {
            // Modification
            $stmt = $bdd->prepare("UPDATE zone SET nom_quartier = ?, commune = ?, province = ?, tarif_mensuel = ? WHERE id_zone = ?");
            $success = $stmt->execute([$nom_quartier, $commune ?: null, $province ?: null, $tarif_mensuel, $id]);
            $message = $success ? "Zone modifiée avec succès." : "Erreur lors de la modification.";
        } else {
            // Ajout
            $stmt = $bdd->prepare("INSERT INTO zone (nom_quartier, commune, province, tarif_mensuel) VALUES (?, ?, ?, ?)");
            $success = $stmt->execute([$nom_quartier, $commune ?: null, $province ?: null, $tarif_mensuel]);
            if ($success) {
              $_SESSION['message'] = "Zone ajoutée avec succès.";
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $message = "Erreur lors de l'ajout de la zone.";
            }
        }
    }
}



// Récupérer toutes les zones
$zones = $bdd->query("SELECT * FROM zone ORDER BY nom_quartier")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Gestion des Zones de Collecte</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    margin: 0;
    font-family: 'Open Sans', sans-serif;
    background-color: #f4f6f9;
  }
  .dashboard {
    display: flex;
    min-height: 100vh;
    overflow: hidden;
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
    <h1 class="mb-4">Gestion des Zones de Collecte</h1>

    <?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#zoneModal" onclick="openModal()">+ Ajouter une zone</button>

    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Quartier</th>
          <th>Commune</th>
          <th>Province</th>
          <th>Tarif Mensuel</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($zones as $zone): ?>
        <tr>
          <td><?= htmlspecialchars($zone['nom_quartier']) ?></td>
          <td><?= htmlspecialchars($zone['commune']) ?></td>
          <td><?= htmlspecialchars($zone['province']) ?></td>
          <td><?= htmlspecialchars(number_format($zone['tarif_mensuel'], 2, ',', ' ')) ?></td>
          <td>
            <button 
              class="btn btn-sm btn-primary"
              onclick='openModal(<?= json_encode($zone, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'>
              Modifier
            </button>
            <a href="gestion_zone.php?del=<?= htmlspecialchars($zone['id_zone']) ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Confirmer la suppression de cette zone ?')">Supprimer</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>

<!-- Modal Bootstrap -->
<div class="modal fade" id="zoneModal" tabindex="-1" aria-labelledby="zoneModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content" id="zoneForm">
      <div class="modal-header">
        <h5 class="modal-title" id="zoneModalLabel">Ajouter / Modifier une zone</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_zone" id="id_zone" />
        <div class="mb-3">
          <label for="nom_quartier" class="form-label">Nom du quartier *</label>
          <input type="text" name="nom_quartier" id="nom_quartier" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="commune" class="form-label">Commune</label>
          <input type="text" name="commune" id="commune" class="form-control" />
        </div>
        <div class="mb-3">
          <label for="province" class="form-label">Province</label>
          <input type="text" name="province" id="province" class="form-control" />
        </div>
        <div class="mb-3">
          <label for="tarif_mensuel" class="form-label">Tarif mensuel *</label>
          <input type="number" step="0.01" min="0" name="tarif_mensuel" id="tarif_mensuel" class="form-control" required />
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
  const zoneModal = new bootstrap.Modal(document.getElementById('zoneModal'));
  const zoneForm = document.getElementById('zoneForm').value = zone.id_zone;

  function openModal(zone = null) {
    if (zone) {
      document.getElementById('zoneModalLabel').textContent = 'Modifier une zone';
      document.getElementById('id_zone').value = zone.id_zone;
      document.getElementById('nom_quartier').value = zone.nom_quartier;
      document.getElementById('commune').value = zone.commune || '';
      document.getElementById('province').value = zone.province || '';
      document.getElementById('tarif_mensuel').value = zone.tarif_mensuel;
    } else {
      document.getElementById('zoneModalLabel').textContent = 'Ajouter une zone';
      zoneForm.reset();
      document.getElementById('id_zone').value = '';
    }
    zoneModal.show();
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
