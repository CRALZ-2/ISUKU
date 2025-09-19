<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
// Mise à jour automatique des statuts expirés
$bdd->query("UPDATE assignation_vehicule 
             SET statut = 'terminé' 
             WHERE date_fin IS NOT NULL 
             AND date_fin < CURDATE() 
             AND statut = 'actif'");


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
if (isset($_GET['del'])) {
    $id_del = (int)$_GET['del'];
    $stmt = $bdd->prepare("DELETE FROM assignation_vehicule WHERE id_assignation_vehicule = ?");
    if ($stmt->execute([$id_del])) {
        $_SESSION['message'] = "Assignation supprimée avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_assignation = isset($_POST['id_assignation_vehicule']) && is_numeric($_POST['id_assignation_vehicule']) ? (int)$_POST['id_assignation_vehicule'] : null;
    $id_chauffeur = trim($_POST['id_chauffeur'] ?? '');
    $immatriculation = trim($_POST['immatriculation'] ?? '');
    $date_assigned = $_POST['date_assigned'] ?? '';
    $date_fin = $_POST['date_fin'] ?? null;
    if ($date_fin === '') $date_fin = null; // null si vide
    $statut = $_POST['statut'] ?? 'actif';

    // Validation simple
    if (!$id_chauffeur || !$immatriculation || !$date_assigned) {
        $message = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Vérification des chevauchements
        $query = "SELECT COUNT(*) FROM assignation_vehicule 
                  WHERE immatriculation = ? 
                  AND statut = 'actif' 
                  AND (
                      (? BETWEEN date_assigned AND IFNULL(date_fin, '9999-12-31')) 
                      OR 
                      (? BETWEEN date_assigned AND IFNULL(date_fin, '9999-12-31'))
                      OR
                      (date_assigned BETWEEN ? AND IFNULL(?, '9999-12-31'))
                  )";

        $stmt = $bdd->prepare($query);
        $stmt->execute([$immatriculation, $date_assigned, $date_fin ?? $date_assigned, $date_assigned, $date_fin ?? $date_assigned]);
        $count = $stmt->fetchColumn();

        // Si modification, on exclut la ligne courante (optionnel)
        if ($id_assignation !== null) {
            $query2 = "SELECT COUNT(*) FROM assignation_vehicule 
                       WHERE immatriculation = ? 
                       AND statut = 'actif' 
                       AND id_assignation_vehicule != ?
                       AND (
                           (? BETWEEN date_assigned AND IFNULL(date_fin, '9999-12-31')) 
                           OR 
                           (? BETWEEN date_assigned AND IFNULL(date_fin, '9999-12-31'))
                           OR
                           (date_assigned BETWEEN ? AND IFNULL(?, '9999-12-31'))
                       )";

            $stmt2 = $bdd->prepare($query2);
            $stmt2->execute([$immatriculation, $id_assignation, $date_assigned, $date_fin ?? $date_assigned, $date_assigned, $date_fin ?? $date_assigned]);
            $count = $stmt2->fetchColumn();
        }

        if ($count > 0) {
            $message = "Ce véhicule est déjà assigné à un chauffeur pendant cette période.";
            $message_type = 'danger';
        } else {
            // Pas de chevauchement, on insère ou modifie

            if ($id_assignation !== null) {
                // Modification
                $sql = "UPDATE assignation_vehicule SET id_chauffeur = ?, immatriculation = ?, date_assigned = ?, date_fin = ?, statut = ? WHERE id_assignation_vehicule = ?";
                $stmt = $bdd->prepare($sql);
                $success = $stmt->execute([$id_chauffeur, $immatriculation, $date_assigned, $date_fin, $statut, $id_assignation]);
                $message = $success ? "Assignation modifiée avec succès." : "Erreur lors de la modification.";
                $message_type = 'success';
            } else {
                // Insertion
                $sql = "INSERT INTO assignation_vehicule (id_chauffeur, immatriculation, date_assigned, date_fin, statut) VALUES (?, ?, ?, ?, ?)";
                $stmt = $bdd->prepare($sql);
                $success = $stmt->execute([$id_chauffeur, $immatriculation, $date_assigned, $date_fin, $statut]);
                $message = $success ? "Assignation créée avec succès." : "Erreur lors de l'ajout.";
                $message_type = 'success';
            }
        }
    }
}


// Récupérer toutes les assignations avec jointure pour infos chauffeurs et véhicules
$assignations = $bdd->query("
    SELECT a.*, u.nom, u.prenom, v.marque, v.modele 
    FROM assignation_vehicule a
    JOIN utilisateur u ON a.id_chauffeur = u.id_utilisateur
    JOIN vehicule v ON a.immatriculation = v.immatriculation
    ORDER BY a.date_assigned DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des chauffeurs (role = 'chauffeur')
$chauffeurs = $bdd->prepare("SELECT id_utilisateur, nom, prenom FROM utilisateur WHERE role = 'chauffeur' ORDER BY nom, prenom");
$chauffeurs->execute();
$chauffeurs = $chauffeurs->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des véhicules
$vehicules = $bdd->query("SELECT immatriculation, marque, modele FROM vehicule ORDER BY marque, modele")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Assignation Véhicules aux Chauffeurs</title>
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
    <h1 class="mb-4">Assignation Véhicules aux Chauffeurs</h1>

    <?php if ($message): ?>
      <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>


    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#assignationModal" onclick="openModal()">+ Ajouter une assignation</button>

    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Chauffeur</th>
          <th>Véhicule</th>
          <th>Date assignée</th>
          <th>Date fin</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($assignations as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['nom'] . ' ' . $a['prenom']) ?></td>
          <td><?= htmlspecialchars($a['marque'] . ' ' . $a['modele'] . ' (' . $a['immatriculation'] . ')') ?></td>
          <td><?= htmlspecialchars($a['date_assigned']) ?></td>
          <td><?= htmlspecialchars($a['date_fin'] ?? '') ?></td>
          <td>
            <?php if($a['statut'] === 'actif'): ?>
                 <span class="badge bg-primary">Actif</span>  <!-- bleu -->
            <?php else: ?>
                 <span class="badge bg-danger">Terminé</span>  <!-- rouge -->
            <?php endif; ?>
          </td>
          <td>
            <button 
              class="btn btn-sm btn-primary"
              onclick='openModal(<?= json_encode($a, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'>
              Modifier
            </button>
            <a href="?del=<?= htmlspecialchars($a['id_assignation_vehicule']) ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Confirmer la suppression de cette assignation ?')">Supprimer</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>

<!-- Modal Bootstrap -->
<div class="modal fade" id="assignationModal" tabindex="-1" aria-labelledby="assignationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content" id="assignationForm">
      <div class="modal-header">
        <h5 class="modal-title" id="assignationModalLabel">Ajouter / Modifier une assignation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_assignation_vehicule" id="id_assignation_vehicule" />

        <div class="mb-3">
          <label for="id_chauffeur" class="form-label">Chauffeur *</label>
          <select name="id_chauffeur" id="id_chauffeur" class="form-select" required>
            <option value="">-- Sélectionner un chauffeur --</option>
            <?php foreach ($chauffeurs as $chauffeur): ?>
            <option value="<?= htmlspecialchars($chauffeur['id_utilisateur']) ?>">
              <?= htmlspecialchars($chauffeur['nom'] . ' ' . $chauffeur['prenom']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="immatriculation" class="form-label">Véhicule *</label>
          <select name="immatriculation" id="immatriculation" class="form-select" required>
            <option value="">-- Sélectionner un véhicule --</option>
            <?php foreach ($vehicules as $v): ?>
            <option value="<?= htmlspecialchars($v['immatriculation']) ?>">
              <?= htmlspecialchars($v['marque'] . ' ' . $v['modele'] . ' (' . $v['immatriculation'] . ')') ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="date_assigned" class="form-label">Date assignée *</label>
          <input type="date" name="date_assigned" id="date_assigned" class="form-control" required />
        </div>

        <div class="mb-3">
          <label for="date_fin" class="form-label">Date fin</label>
          <input type="date" name="date_fin" id="date_fin" class="form-control" />
        </div>

        <div class="mb-3">
          <label for="statut" class="form-label">Statut</label>
          <select name="statut" id="statut" class="form-select">
            <option value="actif">Actif</option>
            <option value="terminé">Terminé</option>
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
  const assignationModal = new bootstrap.Modal(document.getElementById('assignationModal'));

  function openModal(assignation = null) {
    if (assignation) {
      document.getElementById('assignationModalLabel').textContent = 'Modifier une assignation';
      document.getElementById('id_assignation_vehicule').value = assignation.id_assignation_vehicule;
      document.getElementById('id_chauffeur').value = assignation.id_chauffeur;
      document.getElementById('immatriculation').value = assignation.immatriculation;
      document.getElementById('date_assigned').value = assignation.date_assigned;
      document.getElementById('date_fin').value = assignation.date_fin || '';
      document.getElementById('statut').value = assignation.statut;
    } else {
      document.getElementById('assignationModalLabel').textContent = 'Ajouter une assignation';
      document.getElementById('assignationForm').reset();
      document.getElementById('id_assignation_vehicule').value = '';
    }
    assignationModal.show();
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
