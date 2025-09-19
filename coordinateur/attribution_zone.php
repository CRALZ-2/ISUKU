<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Message flash
$message = '';
$message_type = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message']['text'] ?? '';
    $message_type = $_SESSION['message']['type'] ?? 'error'; // défaut erreur
    unset($_SESSION['message']);
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
    header('Location: login.php');
    exit;
}

// Mise à jour automatique des statuts expirés
$updateStatus = $bdd->prepare("UPDATE attribution_zone SET statut = 'terminé' WHERE statut = 'actif' AND date_fin IS NOT NULL AND date_fin < CURDATE()");
$updateStatus->execute();

// Suppression
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $del = $bdd->prepare("DELETE FROM attribution_zone WHERE id_attribution = ?");
    if ($del->execute([$id])) {
        $_SESSION['message'] = ['text' => "Attribution supprimée avec succès.", 'type' => 'success'];

    } else {
        $_SESSION['message'] = ['text' => "Erreur lors de la suppression.", 'type' => 'error'];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id_attribution']) && is_numeric($_POST['id_attribution']) ? (int)$_POST['id_attribution'] : null;
    $id_utilisateur = $_POST['id_utilisateur'] ?? '';
    $id_zone = $_POST['id_zone'] ?? '';
    $date_attribution = $_POST['date_attribution'] ?? '';
    $date_fin = $_POST['date_fin'] ?? null;
    $statut = $_POST['statut'] ?? 'actif';
    if ($date_fin === '') $date_fin = null;

    // Validation simple
    if (!$id_utilisateur || !$id_zone || !$date_attribution) {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $message_type = 'error';
    } elseif ($date_fin !== null && $date_fin < $date_attribution) {
        $message = "La date de fin doit être supérieure ou égale à la date de début.";
        $message_type = 'error';
    } else {
        // Vérifier chevauchements
        // Pas d'autre attribution avec même utilisateur et même zone qui chevauche la période choisie
        $queryOverlap = "SELECT COUNT(*) FROM attribution_zone 
            WHERE id_utilisateur = ? AND id_zone = ? AND id_attribution != ? 
            AND statut = 'actif' 
            AND (
                (date_attribution <= ? AND (date_fin IS NULL OR date_fin >= ?)) OR
                (date_attribution <= ? AND (date_fin IS NULL OR date_fin >= ?)) OR
                (date_attribution >= ? AND (date_fin IS NULL OR date_attribution <= ?))
            )";

        $stmtOverlap = $bdd->prepare($queryOverlap);
        $stmtOverlap->execute([
            $id_utilisateur,
            $id_zone,
            $id ?? 0,
            $date_attribution, $date_attribution,
            $date_fin ?? $date_attribution, $date_fin ?? $date_attribution,
            $date_attribution, $date_fin ?? $date_attribution,
        ]);
        $countOverlap = $stmtOverlap->fetchColumn();

        if ($countOverlap > 0) {
            $message = "Cette attribution chevauche une période existante pour le même utilisateur et zone.";
        } else {
            if ($id !== null) {
                // Modifier
                $update = $bdd->prepare("UPDATE attribution_zone SET id_utilisateur = ?, id_zone = ?, date_attribution = ?, date_fin = ?, statut = ? WHERE id_attribution = ?");
                $success = $update->execute([$id_utilisateur, $id_zone, $date_attribution, $date_fin, $statut, $id]);

                if ($success) {
                               $message = "Attribution modifiée avec succès.";
                               $message_type = 'success';
                              } else {
                                  $message = "Erreur lors de la modification.";
                                  $message_type = 'error';
                              }
            } else {
                // Ajouter
                $insert = $bdd->prepare("INSERT INTO attribution_zone (id_utilisateur, id_zone, date_attribution, date_fin, statut) VALUES (?, ?, ?, ?, 'actif')");
                $success = $insert->execute([$id_utilisateur, $id_zone, $date_attribution, $date_fin]);
                if ($success) {
                    $_SESSION['message'] = ['text' => "Attribution ajoutée avec succès.", 'type' => 'success'];
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $message = "Erreur lors de l'ajout de l'attribution.";
                    $message_type = 'error';
                }
            }
        }
    }
}

// Récupérer toutes les assignations
$attributions = $bdd->query("
    SELECT az.*, u.nom, u.prenom, u.role, z.nom_quartier
    FROM attribution_zone az
    JOIN utilisateur u ON az.id_utilisateur = u.id_utilisateur
    JOIN zone z ON az.id_zone = z.id_zone
    ORDER BY az.statut DESC, az.date_attribution DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les utilisateurs agents et chauffeurs
$users = $bdd->prepare("SELECT id_utilisateur, nom, prenom, role FROM utilisateur WHERE role IN ('agent','chauffeur') ORDER BY nom, prenom");
$users->execute();
$usersList = $users->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les zones
$zones = $bdd->query("SELECT id_zone, nom_quartier FROM zone ORDER BY nom_quartier")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Attribution des Zones aux Utilisateurs</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    font-family: 'Open Sans', sans-serif;
    background-color: #f4f6f9;
    margin: 0;
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
  .status-actif {
    color: #0d6efd; /* bleu Bootstrap */
    font-weight: bold;
  }
  .status-termine {
    color: #dc3545; /* rouge Bootstrap */
    font-weight: bold;
  }
</style>
</head>
<body>

<div class="dashboard">
  <?php include 'sidebar.php'; ?>

  <main class="main-content">
    <h1 class="mb-4">Attribution des Zones aux Utilisateurs</h1>

    <?php if ($message): ?>
    <div class="alert <?= $message_type === 'success' ? 'alert-primary' : 'alert-danger' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>


    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#attributionModal" onclick="openModal()">+ Ajouter une attribution</button>

    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Utilisateur</th>
          <th>Rôle</th>
          <th>Zone</th>
          <th>Date Début</th>
          <th>Date Fin</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($attributions as $attr): ?>
        <tr>
          <td><?= htmlspecialchars($attr['nom'] . ' ' . $attr['prenom']) ?></td>
          <td><?= htmlspecialchars($attr['role']) ?></td>
          <td><?= htmlspecialchars($attr['nom_quartier']) ?></td>
          <td><?= htmlspecialchars($attr['date_attribution']) ?></td>
          <td><?= htmlspecialchars($attr['date_fin'] ?? '-') ?></td>
          <td>
            <?php if ($attr['statut'] === 'actif'): ?>
              <span class="badge bg-primary">Actif</span>
            <?php else: ?>
              <span class="badge bg-danger">Terminé</span>
            <?php endif; ?>
          </td>

          <td>
            <button class="btn btn-sm btn-primary"
              onclick='openModal(<?= json_encode($attr, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'>
              Modifier
            </button>
            <a href="?del=<?= $attr['id_attribution'] ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Confirmer la suppression de cette attribution ?')">
               Supprimer
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>

<!-- Modal Bootstrap -->
<div class="modal fade" id="attributionModal" tabindex="-1" aria-labelledby="attributionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content" id="attributionForm">
      <div class="modal-header">
        <h5 class="modal-title" id="attributionModalLabel">Ajouter / Modifier une attribution</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_attribution" id="id_attribution" />
        
        <div class="mb-3">
          <label for="id_utilisateur" class="form-label">Utilisateur (Agent / Chauffeur) *</label>
          <select name="id_utilisateur" id="id_utilisateur" class="form-select" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($usersList as $user): ?>
              <option value="<?= htmlspecialchars($user['id_utilisateur']) ?>">
                <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom'] . " (" . $user['role'] . ")") ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="id_zone" class="form-label">Zone *</label>
          <select name="id_zone" id="id_zone" class="form-select" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($zones as $zone): ?>
              <option value="<?= htmlspecialchars($zone['id_zone']) ?>"><?= htmlspecialchars($zone['nom_quartier']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="date_attribution" class="form-label">Date Début *</label>
          <input type="date" name="date_attribution" id="date_attribution" class="form-control" required />
        </div>

        <div class="mb-3">
          <label for="date_fin" class="form-label">Date Fin</label>
          <input type="date" name="date_fin" id="date_fin" class="form-control" />
          <div class="form-text">Laisser vide si indéfinie</div>
        </div>
        <div class="mb-3" id="statut_group" style="display: none;">
        <label for="statut" class="form-label">Statut *</label>
        <select name="statut" id="statut" class="form-select">
          <option value="actif">Actif</option>
          <option value="terminé">Terminé</option>
        </select>
      </div>
      </div>
      
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const modal = new bootstrap.Modal(document.getElementById('attributionModal'));
  const form = document.getElementById('attributionForm');

  function openModal(data = null) {
  form.reset();

  const statutGroup = document.getElementById('statut_group');

  if (data) {
    document.getElementById('id_attribution').value = data.id_attribution;
    document.getElementById('id_utilisateur').value = data.id_utilisateur;
    document.getElementById('id_zone').value = data.id_zone;
    document.getElementById('date_attribution').value = data.date_attribution;
    document.getElementById('date_fin').value = data.date_fin ?? '';
    document.getElementById('statut').value = data.statut ?? 'actif';

    // Afficher le champ statut
    statutGroup.style.display = 'block';
  } else {
    document.getElementById('id_attribution').value = '';

    // Cacher le champ statut à l'ajout
    statutGroup.style.display = 'none';
  }

  modal.show();
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
