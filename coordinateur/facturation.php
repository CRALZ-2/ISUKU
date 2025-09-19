<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Vérification rôle coordinateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
    header('Location: login.php');
    exit;
}

// Message flash
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message']['text'] ?? '';
    $message_type = $_SESSION['message']['type'] ?? 'error';
    unset($_SESSION['message']);
}

// Dossier upload justificatifs
$uploadDir = __DIR__ . '/uploads/factures/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Suppression facture
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    // Récupérer nom fichier justificatif pour suppression
    $stmt = $bdd->prepare("SELECT justificatif FROM facturation WHERE id_facture = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();

    $del = $bdd->prepare("DELETE FROM facturation WHERE id_facture = ?");
    if ($del->execute([$id])) {
        // Supprimer fichier uploadé si existe
        if ($file && file_exists($uploadDir . $file)) {
            unlink($uploadDir . $file);
        }
        $_SESSION['message'] = ['text' => "Facture supprimée avec succès.", 'type' => 'success'];
    } else {
        $_SESSION['message'] = ['text' => "Erreur lors de la suppression.", 'type' => 'error'];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ajout / modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_facture = isset($_POST['id_facture']) && is_numeric($_POST['id_facture']) ? (int)$_POST['id_facture'] : null;
    $id_contrat = $_POST['id_contrat'] ?? '';
    $id_moyen = $_POST['id_moyen'] ?? '';
    $id_annonce = $_POST['id_annonce'] ?? '';
    $montant = $_POST['montant'] ?? '';
    $statut = $_POST['statut'] ?? 'en attente';

    // Validation
    if (!$id_contrat || !$id_moyen || !$id_annonce || !$montant) {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $message_type = 'error';
    } elseif (!is_numeric($montant) || $montant <= 0) {
        $message = "Le montant doit être un nombre positif.";
        $message_type = 'error';
    } else {
        // Gestion upload justificatif
        $filename = null;
        if (isset($_FILES['justificatif']) && $_FILES['justificatif']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['justificatif'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $message = "Erreur lors de l'upload du fichier.";
                $message_type = 'error';
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['pdf', 'docx'];
                if (!in_array($ext, $allowed_extensions)) {
                    $message = "Le justificatif doit être un fichier PDF ou DOCX.";
                    $message_type = 'error';
                } else {
                    // Générer un nom unique
                    $filename = uniqid('facture_') . '.' . $ext;
                    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                        $message = "Erreur lors de l'enregistrement du fichier.";
                        $message_type = 'error';
                    }
                }
            }
        }

        if (!$message) {
            if ($id_facture !== null) {
                // Modifier
                // Si nouveau fichier uploadé, supprimer ancien fichier
                if ($filename) {
                    $stmtOld = $bdd->prepare("SELECT justificatif FROM facturation WHERE id_facture = ?");
                    $stmtOld->execute([$id_facture]);
                    $oldFile = $stmtOld->fetchColumn();
                    if ($oldFile && file_exists($uploadDir . $oldFile)) {
                        unlink($uploadDir . $oldFile);
                    }
                    $sql = "UPDATE facturation SET id_contrat = ?,id_moyen = ?,id_annonce = ?, montant = ?, statut = ?, justificatif = ? WHERE id_facture = ?";
                    $params = [$id_contrat,$id_moyen,$id_annonce, $montant, $statut, $filename, $id_facture];
                } else {
                    $sql = "UPDATE facturation SET id_contrat = ?,id_moyen = ?,id_annonce = ?, montant = ?, statut = ? WHERE id_facture = ?";
                    $params = [$id_contrat,$id_moyen,$id_annonce, $montant, $statut, $id_facture];
                }
                $update = $bdd->prepare($sql);
                $success = $update->execute($params);
                if ($success) {
                    $_SESSION['message'] = ['text' => "Facture modifiée avec succès.", 'type' => 'success'];
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $message = "Erreur lors de la modification.";
                    $message_type = 'error';
                }
            } else {
                // Ajouter
                $sql = "INSERT INTO facturation (id_contrat,id_moyen,id_annonce, montant, statut, justificatif) VALUES (?,?, ?, ?, ?, ?)";
                $insert = $bdd->prepare($sql);
                $success = $insert->execute([$id_contrat,$id_moyen,$id_annonce, $montant, $statut, $filename]);
                if ($success) {
                    $_SESSION['message'] = ['text' => "Facture ajoutée avec succès.", 'type' => 'success'];
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $message = "Erreur lors de l'ajout.";
                    $message_type = 'error';
                    // En cas d'erreur on peut supprimer le fichier uploadé pour éviter fichiers orphelins
                    if ($filename && file_exists($uploadDir . $filename)) {
                        unlink($uploadDir . $filename);
                    }
                }
            }
        }
    }
}

// Récupérer toutes les factures avec infos contrat (et client si souhaité)
$factures = $bdd->query("SELECT f.*, u.nom AS nom_client,m.nom_moyen,a.message FROM facturation f JOIN (contrat c JOIN utilisateur u ON c.id_utilisateur=u.id_utilisateur) ON f.id_contrat = c.id_contrat JOIN moyen_paiement m ON m.id_moyen=f.id_moyen JOIN annonce_collecte a ON a.id_annonce=f.id_annonce ORDER BY f.date_facture DESC")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les contrats valides
$contrats = $bdd->query("SELECT id_contrat, u.nom AS nom_client FROM contrat c JOIN utilisateur u ON c.id_utilisateur=u.id_utilisateur WHERE u.role='client' AND c.statut='actif' ORDER BY nom_client")->fetchAll(PDO::FETCH_ASSOC);
$moyens = $bdd->query("SELECT id_moyen, nom_moyen FROM moyen_paiement WHERE actif='1' ORDER BY nom_moyen")->fetchAll(PDO::FETCH_ASSOC);
$annonces = $bdd->query("SELECT id_annonce, message,date_annonce FROM annonce_collecte ORDER BY date_annonce")->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Gestion des Factures</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
</style>
</head>
<body>

<div class="dashboard">
  <?php include 'sidebar.php'; ?>

  <main class="main-content">
    <h1 class="mb-4">Gestion des Factures</h1>

    <?php if ($message): ?>
    <div class="alert <?= $message_type === 'success' ? 'alert-primary' : 'alert-danger' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#factureModal" onclick="openModal()">+ Ajouter une facture</button>

    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Contrat / Client</th>
          <th>Moyen de paiement</th>
          <th>Annonce</th>
          <th>Date Facture</th>
          <th>Montant</th>
          <th>Justificatif</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($factures as $facture): ?>
        <tr>
          <td><?= htmlspecialchars($facture['nom_client']) ?></td>
          <td><?= htmlspecialchars($facture['nom_moyen']) ?></td>
          <td><?= htmlspecialchars($facture['message']) ?></td>
          <td><?= htmlspecialchars($facture['date_facture']) ?></td>
          <td><?= number_format($facture['montant'], 2, ',', ' ') ?></td>
          <td>
  <?php
    $clientDir = 'uploads/justificatifs_factures/';
    $coordDir = 'uploads/factures/';
    $filename = $facture['justificatif'];

    $filePath = '';
    if ($filename) {
        if (file_exists($coordDir . $filename)) {
            $filePath = $coordDir . $filename;
        } elseif (file_exists($clientDir . $filename)) {
            $filePath = $clientDir . $filename;
        }
    }

    if ($filePath) {
        echo '<a href="' . htmlspecialchars($filePath) . '" target="_blank" class="btn btn-sm btn-outline-primary">Voir</a>';
    } else {
        echo '-';
    }
  ?>
</td>

          <td>
            <?php
              $class = 'secondary';
              if ($facture['statut'] === 'payée') $class = 'success';
              elseif ($facture['statut'] === 'en attente') $class = 'warning';
              elseif ($facture['statut'] === 'annulée') $class = 'danger';
            ?>
            <span class="badge bg-<?= $class ?>"><?= htmlspecialchars(ucfirst($facture['statut'])) ?></span>
          </td>
          <td>
            <div class="d-flex flex-wrap gap-1">
              <button class="btn btn-sm btn-primary" 
                onclick='openModal(<?= json_encode($facture, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' title="Modifier">
                <i class="bi bi-pencil-square"></i>
              </button>
          
              <a href="impression_facture.php?id_facture=<?= $facture['id_facture'] ?>" 
                 class="btn btn-sm btn-secondary" 
                 target="_blank" 
                 title="Imprimer la facture">
                <i class="bi bi-printer"></i>
              </a>
          
              <a href="?del=<?= $facture['id_facture'] ?>" 
                 class="btn btn-sm btn-danger" 
                 onclick="return confirm('Confirmer la suppression de cette facture ?')" 
                 title="Supprimer">
                <i class="bi bi-trash3"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>

<!-- Modal Bootstrap -->
<div class="modal fade" id="factureModal" tabindex="-1" aria-labelledby="factureModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" enctype="multipart/form-data" class="modal-content" id="factureForm">
      <div class="modal-header">
        <h5 class="modal-title" id="factureModalLabel">Ajouter / Modifier une facture</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_facture" id="id_facture" />

        <div class="mb-3">
          <label for="id_contrat" class="form-label">Contrat *</label>
          <select name="id_contrat" id="id_contrat" class="form-select" required>
            <option value="">-- Sélectionner un contrat --</option>
            <?php foreach ($contrats as $contrat): ?>
              <option value="<?= htmlspecialchars($contrat['id_contrat']) ?>">
                <?= htmlspecialchars($contrat['nom_client']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="id_moyen" class="form-label">Moyen de paiement *</label>
          <select name="id_moyen" id="id_moyen" class="form-select" required>
            <option value="">-- Sélectionner un moyen --</option>
            <?php foreach ($moyens as $moyen): ?>
              <option value="<?= htmlspecialchars($moyen['id_moyen']) ?>">
                <?= htmlspecialchars($moyen['nom_moyen']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="id_annonce" class="form-label">Annonce *</label>
          <select name="id_annonce" id="id_annonce" class="form-select" required>
            <option value="">-- Sélectionner l'annonce --</option>
            <?php foreach ($annonces as $annonce): ?>
              <option value="<?= htmlspecialchars($annonce['id_annonce']) ?>">
                <?= htmlspecialchars($annonce['message']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="montant" class="form-label">Montant *</label>
          <input type="number" name="montant" id="montant" step="0.01" min="0" class="form-control" required />
        </div>

        <div class="mb-3">
          <label for="statut" class="form-label">Statut *</label>
          <select name="statut" id="statut" class="form-select" required>
            <option value="en attente">En attente</option>
            <option value="payée">Payée</option>
            <option value="annulée">Annulée</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="justificatif" class="form-label">Justificatif (PDF & DOCX uniquement)</label>
          <input type="file" name="justificatif" id="justificatif"
       accept=".pdf, .docx"
       class="form-control" />

          <div class="form-text">Laissez vide pour ne pas modifier le fichier actuel.</div>
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
  const modal = new bootstrap.Modal(document.getElementById('factureModal'));
  const form = document.getElementById('factureForm');

  function openModal(data = null) {
    form.reset();

    if (data) {
      document.getElementById('id_facture').value = data.id_facture;
      document.getElementById('id_contrat').value = data.id_contrat;
      document.getElementById('id_moyen').value = data.id_moyen;
      document.getElementById('id_annonce').value = data.id_annonce;
      document.getElementById('montant').value = data.montant;
      document.getElementById('statut').value = data.statut;
    } else {
      document.getElementById('id_facture').value = '';
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
<script>
// Faire disparaître les alertes après 2 secondes (2000 ms)
setTimeout(function() {
  const alertBox = document.querySelector('.alert');
  if (alertBox) {
    alertBox.style.transition = 'opacity 0.5s ease';
    alertBox.style.opacity = '0';
    setTimeout(() => alertBox.remove(), 500); // Supprime l'élément après la transition
  }
}, 2000);
</script>

</body>
</html>
