<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Vérifier rôle coordinateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
    header("Location: login.php");
    exit;
}

$search = $_GET['search_user'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';

$params = [];
$where = [];

$sql = "
    SELECT l.*, u.nom, u.prenom 
    FROM journal_connexion l 
    LEFT JOIN utilisateur u ON l.id_utilisateur = u.id_utilisateur
";

// Filtrage par nom/prénom
if (!empty($search)) {
    $where[] = "(u.nom LIKE ? OR u.prenom LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Filtrage par date de début
if (!empty($date_debut)) {
    $where[] = "DATE(l.date_connexion) >= ?";
    $params[] = $date_debut;
}

// Filtrage par date de fin
if (!empty($date_fin)) {
    $where[] = "DATE(l.date_connexion) <= ?";
    $params[] = $date_fin;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY l.date_connexion DESC";

$stmt = $bdd->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Historique des connexions - Coordinateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f5f5;
    }
    .table thead {
      background-color: #0d53b1;
      color: white;
    }
    .table tbody tr:hover {
      background-color: #f1f1f1;
    }
    .container {
      max-width: 1200px;
    }
.bg-light {
  min-height: 100vh;
  border-right: 1px solid #ddd;
}

  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 p-0 bg-light">
      <?php include('sidebar.php'); ?>
    </div>

    <!-- Contenu principal -->
    <div class="col-md-9 col-lg-10 px-4 py-4">
      <h3 class="mb-4">🧾 Historique des connexions</h3>
<!-- Formulaire de recherche -->
<form method="get" class="row g-3 mb-4">
  <div class="col-md-4">
    <label for="search_user" class="form-label">Rechercher un utilisateur</label>
    <input type="text" id="search_user" name="search_user" class="form-control" placeholder="Nom ou prénom..." value="<?= htmlspecialchars($_GET['search_user'] ?? '') ?>">
  </div>
  <div class="col-md-3">
    <label for="date_debut" class="form-label">Date de début</label>
    <input type="date" id="date_debut" name="date_debut" class="form-control" value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">
  </div>
  <div class="col-md-3">
    <label for="date_fin" class="form-label">Date de fin</label>
    <input type="date" id="date_fin" name="date_fin" class="form-control" value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">
  </div>
  <div class="col-md-2 d-flex align-items-end">
    <button type="submit" class="btn btn-primary me-2">Rechercher</button>
    <a href="log_connexion.php" class="btn btn-secondary">Réinitialiser</a>
  </div>
</form>

      <?php if (isset($_GET['search_user']) && $_GET['search_user'] !== ''): ?>
  <?php if (count($logs) === 0): ?>
    <div class="alert alert-warning">Aucun résultat trouvé pour "<?= htmlspecialchars($_GET['search_user']) ?>".</div>
  <?php else: ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle">
            <thead>
              <tr>
                <th>Utilisateur</th>
                <th>Adresse IP</th>
                <th>Navigateur</th>
                <th>Date de connexion</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
                <tr>
                  <td><?= htmlspecialchars($log['prenom'] . ' ' . $log['nom']) ?></td>
                  <td><?= htmlspecialchars($log['adresse_ip']) ?></td>
                  <td><?= htmlspecialchars(substr($log['navigateur'], 0, 60)) ?>...</td>
                  <td><?= date('d/m/Y H:i:s', strtotime($log['date_connexion'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
<?php endif; ?>

    </div>

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
