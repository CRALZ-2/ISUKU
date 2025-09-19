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

// Sécurité : seul le coordinateur peut accéder
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
    header('Location: login.php');
    exit;
}

// Suppression
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $stmt = $bdd->prepare("DELETE FROM annonce_collecte WHERE id_annonce = ?");
    $stmt->execute([$id]);
    $_SESSION['message'] = "Annonce supprimée avec succès.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_annonce = $_POST['id_annonce'] ?? null;
    $id_tournee = $_POST['id_tournee'];
    $msg = $_POST['message'];

    if ($id_annonce) {
        $stmt = $bdd->prepare("UPDATE annonce_collecte SET id_tournee=?, message=? WHERE id_annonce=?");
        $stmt->execute([$id_tournee, $msg, $id_annonce]);
        $_SESSION['message'] = "Annonce modifiée avec succès.";
    } else {
        $stmt = $bdd->prepare("INSERT INTO annonce_collecte (id_tournee, message) VALUES (?, ?)");
        $stmt->execute([$id_tournee, $msg]);
        $_SESSION['message'] = "Annonce ajoutée avec succès.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Données des tournées
$tournees = $bdd->query("SELECT t.id_tournee, t.date_tournee, z.nom_quartier FROM tournee t JOIN zone z ON t.id_zone = z.id_zone WHERE statut='planifiée' ORDER BY t.date_tournee DESC")->fetchAll(PDO::FETCH_ASSOC);

// Annonces existantes
$annonces = $bdd->query("SELECT a.*, t.date_tournee, z.nom_quartier
                         FROM annonce_collecte a
                         JOIN tournee t ON a.id_tournee = t.id_tournee
                         JOIN zone z ON t.id_zone = z.id_zone
                         ORDER BY a.date_annonce DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des Annonces</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .dashboard { display: flex; min-height: 100vh; }
    main.main-content { flex-grow: 1; padding: 30px; background-color: #ecf0f1; }
  </style>
</head>
<body>
<div class="dashboard">
  <?php include 'sidebar.php'; ?>
  <main class="main-content">
    <h1 class="mb-4">Gestion des Annonces de Collecte</h1>

    <?php if ($message): ?>
      <div class="alert <?= str_contains(strtolower($message), 'supprimée') ? 'alert-danger' : 'alert-success' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#annonceModal" onclick="openModal()">+ Nouvelle annonce</button>

    <table class="table table-bordered table-striped align-middle">
      <thead>
        <tr>
          <th>Date Tournée</th>
          <th>Zone</th>
          <th>Message</th>
          <th>Date Annonce</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($annonces as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['date_tournee']) ?></td>
          <td><?= htmlspecialchars($a['nom_quartier']) ?></td>
          <td><?= nl2br(htmlspecialchars($a['message'])) ?></td>
          <td><?= htmlspecialchars($a['date_annonce']) ?></td>
          <td>
            <button class="btn btn-sm btn-primary edit-btn"
              data-id="<?= $a['id_annonce'] ?>"
              data-tournee="<?= $a['id_tournee'] ?>"
              data-message="<?= htmlspecialchars($a['message'], ENT_QUOTES) ?>">
              Modifier
            </button>
            <a href="?del=<?= $a['id_annonce'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette annonce ?')">Supprimer</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>

<!-- Modal -->
<div class="modal fade" id="annonceModal" tabindex="-1" aria-labelledby="annonceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content" id="annonceForm">
      <div class="modal-header">
        <h5 class="modal-title" id="annonceModalLabel">Nouvelle annonce</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_annonce" id="id_annonce">
        <div class="mb-3">
          <label for="id_tournee" class="form-label">Tournée *</label>
          <select name="id_tournee" id="id_tournee" class="form-select" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($tournees as $t): ?>
              <option value="<?= $t['id_tournee'] ?>"><?= htmlspecialchars($t['date_tournee'] . ' - ' . $t['nom_quartier']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="message" class="form-label">Message *</label>
          <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
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
  const modal = new bootstrap.Modal(document.getElementById('annonceModal'));
  const form = document.getElementById('annonceForm');

  // ouvrir une modal vide (ajout)
  function openModal() {
    form.reset();
    document.getElementById('annonceModalLabel').textContent = 'Nouvelle annonce';
    document.getElementById('id_annonce').value = '';
    modal.show();
  }

  // écoute sur les boutons Modifier
  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('id_annonce').value = btn.dataset.id;
      document.getElementById('id_tournee').value = btn.dataset.tournee;
      document.getElementById('message').value = btn.dataset.message;
      document.getElementById('annonceModalLabel').textContent = 'Modifier l\'annonce';
      modal.show();
    });
  });
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
