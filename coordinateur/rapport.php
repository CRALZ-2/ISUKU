<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Vérification de rôle coordinateur (optionnel si déjà géré dans la sidebar)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
    header('Location: login.php');
    exit;
}

// Initialisation message flash
$message = '';
$message_type = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message']['text'] ?? '';
    $message_type = $_SESSION['message']['type'] ?? 'error';
    unset($_SESSION['message']);
}

// Suppression d'un rapport
if (isset($_GET['delete'])) {
    $id_to_delete = (int) $_GET['delete'];

    // Vérifier que le rapport appartient bien au coordinateur connecté
    $check = $bdd->prepare("SELECT fichier FROM rapport WHERE id_rapport = ? AND id_coordinateur = ?");
    $check->execute([$id_to_delete, $_SESSION['id_utilisateur']]);
    $rapport = $check->fetch(PDO::FETCH_ASSOC);

    if ($rapport) {
        // Supprimer le fichier lié s'il existe
        if ($rapport['fichier'] && file_exists('uploads/rapports/' . $rapport['fichier'])) {
            unlink('uploads/rapports/' . $rapport['fichier']);
        }
        // Supprimer le rapport en base
        $del = $bdd->prepare("DELETE FROM rapport WHERE id_rapport = ? AND id_coordinateur = ?");
        $del->execute([$id_to_delete, $_SESSION['id_utilisateur']]);

        $_SESSION['message'] = ['text' => 'Rapport supprimé avec succès.', 'type' => 'error'];
    } else {
        $_SESSION['message'] = ['text' => 'Rapport introuvable ou accès refusé.', 'type' => 'error'];
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Chargement du rapport à modifier (si demandé)
$rapportToEdit = null;
if (isset($_GET['edit'])) {
    $id_to_edit = (int) $_GET['edit'];

    $editStmt = $bdd->prepare("SELECT * FROM rapport WHERE id_rapport = ? AND id_coordinateur = ?");
    $editStmt->execute([$id_to_edit, $_SESSION['id_utilisateur']]);
    $rapportToEdit = $editStmt->fetch(PDO::FETCH_ASSOC);

    if (!$rapportToEdit) {
        $_SESSION['message'] = ['text' => 'Rapport introuvable ou accès refusé.', 'type' => 'error'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Enregistrement du rapport (ajout ou modification)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_coordinateur = $_SESSION['id_utilisateur'];
    $id_rapport = $_POST['id_rapport'] ?? null;  // id_rapport caché pour modification
    $titre = $_POST['titre'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    $date_rapport = $_POST['date_rapport'] ?? date('Y-m-d');
    $fichier_nom = null;

    // Upload du fichier joint (optionnel)
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/rapports/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fichier_nom = time() . '_' . basename($_FILES['fichier']['name']);
        move_uploaded_file($_FILES['fichier']['tmp_name'], $uploadDir . $fichier_nom);
    }

    if ($titre && $contenu) {
        if ($id_rapport) {
            // Modification d'un rapport existant
            if ($fichier_nom) {
                // Supprimer ancien fichier
                $oldFileStmt = $bdd->prepare("SELECT fichier FROM rapport WHERE id_rapport = ? AND id_coordinateur = ?");
                $oldFileStmt->execute([$id_rapport, $id_coordinateur]);
                $oldFile = $oldFileStmt->fetchColumn();
                if ($oldFile && file_exists($uploadDir . $oldFile)) {
                    unlink($uploadDir . $oldFile);
                }
                $stmt = $bdd->prepare("UPDATE rapport SET titre = ?, contenu = ?, date_rapport = ?, fichier = ? WHERE id_rapport = ? AND id_coordinateur = ?");
                $stmt->execute([$titre, $contenu, $date_rapport, $fichier_nom, $id_rapport, $id_coordinateur]);
            } else {
                $stmt = $bdd->prepare("UPDATE rapport SET titre = ?, contenu = ?, date_rapport = ? WHERE id_rapport = ? AND id_coordinateur = ?");
                $stmt->execute([$titre, $contenu, $date_rapport, $id_rapport, $id_coordinateur]);
            }
            $_SESSION['message'] = ['text' => 'Rapport modifié avec succès.', 'type' => 'success'];
        } else {
            // Nouveau rapport
            $stmt = $bdd->prepare("INSERT INTO rapport (id_coordinateur, titre, contenu, date_rapport, fichier) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_coordinateur, $titre, $contenu, $date_rapport, $fichier_nom]);
            $_SESSION['message'] = ['text' => 'Rapport enregistré avec succès.', 'type' => 'success'];
        }
    } else {
        $_SESSION['message'] = ['text' => 'Veuillez remplir tous les champs obligatoires.', 'type' => 'error'];
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Récupérer les rapports du coordinateur connecté
$rapports = $bdd->prepare("SELECT * FROM rapport WHERE id_coordinateur = ? ORDER BY date_creation DESC");
$rapports->execute([$_SESSION['id_utilisateur']]);
$rapportList = $rapports->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rapports d’activités</title>
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
    <h1 class="mb-4">Rapports d'activités</h1>

    <?php if ($message): ?>
      <div class="alert <?= $message_type === 'success' ? 'alert-primary' : 'alert-danger' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#rapportModal">+ Nouveau Rapport</button>

    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Titre</th>
          <th>Date</th>
          <th>Contenu</th>
          <th>Fichier joint</th>
          <th>Actions</th> 
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rapportList as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['titre']) ?></td>
          <td><?= htmlspecialchars($r['date_rapport']) ?></td>
          <td><?= nl2br(htmlspecialchars($r['contenu'])) ?></td>
          <td>
            <?php if ($r['fichier']): ?>
              <a href="uploads/rapports/<?= urlencode($r['fichier']) ?>" class="btn btn-sm btn-outline-primary" download>Télécharger</a>
            <?php else: ?>
              Aucun
            <?php endif; ?>
          </td>
          <td>
            <a href="?edit=<?= $r['id_rapport'] ?>" class="btn btn-sm btn-warning me-1" title="Modifier">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
    <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2L2 11.207V13h1.793L14 3.793 11.207 2z"/>
  </svg>
</a>
<a href="?delete=<?= $r['id_rapport'] ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Voulez-vous vraiment supprimer ce rapport ?')">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
    <path d="M5.5 5.5A.5.5 0 0 1 6 5h4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5H6a.5.5 0 0 1-.5-.5v-7zM4.118 4.5L4 5h8l-.118-.5H4.118zM2.5 3a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v1h-11V3z"/>
  </svg>
</a>

          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>

<!-- Modal de création/modification du rapport -->
<div class="modal fade" id="rapportModal" tabindex="-1" aria-labelledby="rapportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="post" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rapportModalLabel">
          <?= $rapportToEdit ? 'Modifier le rapport d’activités' : 'Nouveau rapport d’activités' ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_rapport" value="<?= htmlspecialchars($rapportToEdit['id_rapport'] ?? '') ?>">

        <div class="mb-3">
          <label for="date_rapport" class="form-label">Date du Rapport *</label>
          <input type="date" name="date_rapport" id="date_rapport" class="form-control" 
                 value="<?= htmlspecialchars($rapportToEdit['date_rapport'] ?? date('Y-m-d')) ?>" required />
        </div>

        <div class="mb-3">
          <label for="titre" class="form-label">Titre *</label>
          <input type="text" name="titre" id="titre" class="form-control" 
                 value="<?= htmlspecialchars($rapportToEdit['titre'] ?? '') ?>" required />
        </div>

        <div class="mb-3">
          <label for="contenu" class="form-label">Contenu *</label>
          <textarea name="contenu" id="contenu" class="form-control" rows="5" required><?= htmlspecialchars($rapportToEdit['contenu'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label for="fichier" class="form-label">Fichier joint (PDF, DOC, JPG, PNG, etc.)</label>
          <input type="file" name="fichier" id="fichier" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
          <?php if (!empty($rapportToEdit['fichier'])): ?>
            <small>Fichier actuel : <a href="uploads/rapports/<?= urlencode($rapportToEdit['fichier']) ?>" target="_blank"><?= htmlspecialchars($rapportToEdit['fichier']) ?></a></small>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary"><?= $rapportToEdit ? 'Modifier' : 'Enregistrer' ?></button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Si on est en mode édition, ouvrir automatiquement la modale
  <?php if ($rapportToEdit): ?>
    var rapportModal = new bootstrap.Modal(document.getElementById('rapportModal'));
    rapportModal.show();
  <?php endif; ?>
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
