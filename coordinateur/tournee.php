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
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $stmt = $bdd->prepare("DELETE FROM tournee WHERE id_tournee = ?");
    $stmt->execute([$id]);
    $_SESSION['message'] = "Tournée supprimée avec succès.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_tournee'] ?? null;
    $date = $_POST['date_tournee'];
    $chauffeur = $_POST['id_chauffeur'];
    $zone = $_POST['id_zone'];
    $statut = $_POST['statut'];
    $commentaire = $_POST['commentaire'] ?? null;

    if ($id) {
        $stmt = $bdd->prepare("UPDATE tournee SET date_tournee=?, id_chauffeur=?, id_zone=?, statut=?, commentaire=? WHERE id_tournee=?");
        $stmt->execute([$date, $chauffeur, $zone, $statut, $commentaire, $id]);
        $_SESSION['message'] = "Tournée modifiée avec succès.";
    } else {
        $stmt = $bdd->prepare("INSERT INTO tournee (date_tournee, id_chauffeur, id_zone, statut, commentaire) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$date, $chauffeur, $zone, $statut, $commentaire]);
        $_SESSION['message'] = "Tournée ajoutée avec succès.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$chauffeurs = $bdd->query("SELECT id_utilisateur, nom FROM utilisateur WHERE role = 'chauffeur'")->fetchAll(PDO::FETCH_ASSOC);
$zones = $bdd->query("SELECT id_zone, nom_quartier FROM zone")->fetchAll(PDO::FETCH_ASSOC);
$tournees = $bdd->query("SELECT t.*, u.nom AS nom_chauffeur, z.nom_quartier 
                         FROM tournee t 
                         JOIN utilisateur u ON t.id_chauffeur = u.id_utilisateur 
                         JOIN zone z ON t.id_zone = z.id_zone
                         ORDER BY t.date_tournee DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Gestion des Tournées</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .dashboard { display: flex; min-height: 100vh; }
    main.main-content { flex-grow: 1; padding: 30px; background-color: #ecf0f1; }
    .table-section { margin-bottom: 40px; }
    .table-section h3 { margin-bottom: 20px; }
    .btn svg { vertical-align: middle; margin-bottom: 2px; }
  </style>
</head>
<body>
<div class="dashboard">
  <?php include 'sidebar.php'; ?>
  <main class="main-content">
    <h1 class="mb-4">Gestion des Tournées</h1>

    <?php if ($message): ?>
      <?php $isDelete = str_contains(strtolower($message), 'supprimée'); ?>
      <div class="alert <?= $isDelete ? 'alert-danger' : 'alert-success' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#tourneeModal" onclick="openModal()">+ Ajouter une tournée</button>

    <div class="mb-3">
  <?php
$statuts = ['planifiée' => 'Planifiées', 'annulée' => 'Annulées', 'terminée' => 'Terminées'];
$statutActif = $_GET['statut'] ?? 'planifiée';
?>
<div class="mb-4">
  <?php foreach ($statuts as $key => $label): ?>
    <a href="?statut=<?= $key ?>"
       class="btn <?= ($statutActif === $key) ? 'btn-primary' : 'btn-outline-primary' ?> me-2">
       <?= $label ?>
    </a>
  <?php endforeach; ?>
</div>



    <?php
      $label = $statuts[$statutActif];
    ?>
      <div class="table-section">
        <h3><?= $label ?></h3>
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>Date</th>
              <th>Chauffeur</th>
              <th>Zone</th>
              <th>Commentaire</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tournees as $t): ?>
              <?php if ($t['statut'] === $statutActif): ?>
              <tr>
                <td><?= htmlspecialchars($t['date_tournee']) ?></td>
                <td><?= htmlspecialchars($t['nom_chauffeur']) ?></td>
                <td><?= htmlspecialchars($t['nom_quartier']) ?></td>
                <td><?= nl2br(htmlspecialchars($t['commentaire'])) ?></td>
                <td>
                  <button class="btn btn-sm btn-primary"
                    onclick='openModal(<?= json_encode($t, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                      <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708L13 6.707l-3-3L12.146.146zM11.5 2.207L3 10.707V13h2.293l8.5-8.5-2.293-2.293z"/>
                    </svg>
                  </button>
                  <a href="?del=<?= $t['id_tournee'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer la suppression ?')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                      <path d="M5.5 5.5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5.5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM10.5 5.5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5z"/>
                      <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2h3l1-1h4l1 1h3a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118z"/>
                    </svg>
                  </a>
                </td>
              </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
  </main>
</div>

<!-- Modal -->
<div class="modal fade" id="tourneeModal" tabindex="-1" aria-labelledby="tourneeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content" id="tourneeForm">
      <div class="modal-header">
        <h5 class="modal-title" id="tourneeModalLabel">Ajouter / Modifier une tournée</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_tournee" id="id_tournee" />
        <div class="mb-3">
          <label for="date_tournee" class="form-label">Date *</label>
          <input type="date" name="date_tournee" id="date_tournee" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="id_chauffeur" class="form-label">Chauffeur *</label>
          <select name="id_chauffeur" id="id_chauffeur" class="form-select" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($chauffeurs as $ch): ?>
              <option value="<?= $ch['id_utilisateur'] ?>"><?= htmlspecialchars($ch['nom']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="id_zone" class="form-label">Zone *</label>
          <select name="id_zone" id="id_zone" class="form-select" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($zones as $z): ?>
              <option value="<?= $z['id_zone'] ?>"><?= htmlspecialchars($z['nom_quartier']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="statut" class="form-label">Statut *</label>
          <select name="statut" id="statut" class="form-select" required>
            <option value="planifiée">Planifiée</option>
            <option value="annulée">Annulée</option>
            <option value="terminée">Terminée</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="commentaire" class="form-label">Commentaire</label>
          <textarea name="commentaire" id="commentaire" class="form-control" rows="3"></textarea>
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
  const tourneeModal = new bootstrap.Modal(document.getElementById('tourneeModal'));
  const tourneeForm = document.getElementById('tourneeForm');

  function openModal(data = null) {
    tourneeForm.reset();
    document.getElementById('id_tournee').value = '';
    if (data) {
      document.getElementById('tourneeModalLabel').textContent = 'Modifier une tournée';
      document.getElementById('id_tournee').value = data.id_tournee;
      document.getElementById('date_tournee').value = data.date_tournee;
      document.getElementById('id_chauffeur').value = data.id_chauffeur;
      document.getElementById('id_zone').value = data.id_zone;
      document.getElementById('statut').value = data.statut;
      document.getElementById('commentaire').value = data.commentaire || '';
    } else {
      document.getElementById('tourneeModalLabel').textContent = 'Ajouter une tournée';
    }
    tourneeModal.show();
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
