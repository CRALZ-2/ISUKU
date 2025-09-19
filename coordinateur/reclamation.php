<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$message = '';
$typeMessage = 'info';

$statuts_valides = ['ouverte', 'traitement', 'résolue'];
$statut_classes = [
    'ouverte' => 'warning',
    'traitement' => 'danger',
    'résolue' => 'success'
];

// Sécurité simple (à adapter)
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'coordinateur' && $_SESSION['role'] !== 'agent')) {
    header('Location: login.php');
    exit;
}

// Gestion changement de statut
if (isset($_GET['action'], $_GET['id'], $_GET['statut']) && $_GET['action'] === 'changer_statut') {
    $id = (int)$_GET['id'];
    $nouveauStatut = $_GET['statut'];

    if (in_array($nouveauStatut, $statuts_valides)) {
        $stmt = $bdd->prepare("UPDATE reclamation SET statut = ? WHERE id_reclamation = ?");
        $stmt->execute([$nouveauStatut, $id]);
        $message = "Statut changé en '" . htmlspecialchars($nouveauStatut) . "' avec succès.";
        $typeMessage = 'info';
    } else {
        $message = "Statut invalide.";
        $typeMessage = 'danger';
    }

    header("Location: reclamation.php?msg=" . urlencode($message) . "&type=" . $typeMessage);
    exit();
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $typeMessage = $_GET['type'] ?? 'info';
}

// Récupérer réclamations par statut
$reclamationsParStatut = [];
foreach ($statuts_valides as $statut) {
    $stmt = $bdd->prepare("
        SELECT r.*, u.nom, u.prenom
        FROM reclamation r
        JOIN utilisateur u ON r.id_client = u.id_utilisateur
        WHERE r.statut = ?
        ORDER BY r.date_reclamation DESC
    ");
    $stmt->execute([$statut]);
    $reclamationsParStatut[$statut] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Gestion des Réclamations</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      background-color: #f4f6f9;
      margin: 0;
      display: grid;
      grid-template-columns: 250px 1fr;
      height: 100vh;
    }
    main.main-content {
      padding: 30px;
      overflow-y: auto;
      background-color: #ecf0f1;
    }
    .nav-tabs .nav-link.active {
      background-color: #0d53b1;
      color: white;
    }
    table {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .table th, .table td {
      vertical-align: middle !important;
    }
    .badge-warning {
      background-color: #f0ad4e !important;
      color: white;
    }
    .badge-danger {
      background-color: #d9534f !important;
      color: white;
    }
    .badge-success {
      background-color: #5cb85c !important;
      color: white;
    }
    .action-icon {
      cursor: pointer;
      width: 24px;
      height: 24px;
      display: inline-block;
      margin-right: 10px;
      transition: transform 0.2s ease;
    }
    .action-icon:hover {
      transform: scale(1.3);
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
  <h1>Gestion des Réclamations Clients</h1>

  <?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($typeMessage) ?>" role="alert">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <!-- Onglets statut -->
  <ul class="nav nav-tabs" id="statutTabs" role="tablist">
    <?php foreach ($statuts_valides as $index => $statut): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                id="tab-<?= $statut ?>" 
                data-bs-toggle="tab" 
                data-bs-target="#content-<?= $statut ?>" 
                type="button" 
                role="tab" 
                aria-controls="content-<?= $statut ?>" 
                aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
          <?= ucfirst($statut) ?>
        </button>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="tab-content mt-3" id="statutTabsContent">
    <?php foreach ($statuts_valides as $index => $statut): ?>
      <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="content-<?= $statut ?>" role="tabpanel" aria-labelledby="tab-<?= $statut ?>">
        <?php if (empty($reclamationsParStatut[$statut])): ?>
          <p>Aucune réclamation <?= htmlspecialchars($statut) ?> trouvée.</p>
        <?php else: ?>
          <table class="table table-striped table-bordered align-middle">
            <thead>
              <tr>
                <th>Client</th>
                <th>Objet</th>
                <th>Message</th>
                <th>Date</th>
                <th>Statut</th>
                <th style="min-width: 150px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reclamationsParStatut[$statut] as $r): ?>
                <tr>
                  <td><?= htmlspecialchars($r['nom'] . ' ' . $r['prenom']) ?></td>
                  <td><?= htmlspecialchars($r['objet']) ?></td>
                  <td><?= nl2br(htmlspecialchars($r['message'])) ?></td>
                  <td><?= htmlspecialchars($r['date_reclamation']) ?></td>
                  <td>
                    <span class="badge badge-<?= $statut_classes[$r['statut']] ?>">
                      <?= ucfirst($r['statut']) ?>
                    </span>
                  </td>
                  <td>
                    <?php
                      foreach ($statuts_valides as $st) {
                        if ($st !== $r['statut']) {
                          $color = '';
                          $iconSvg = '';
                          switch ($st) {
                            case 'ouverte':
                              $color = '#f0ad4e'; // orange
                              $iconSvg = '<circle cx="12" cy="12" r="10" fill="none" stroke="'.$color.'" stroke-width="2"/>';
                              break;
                            case 'traitement':
                              $color = '#d9534f'; // rouge
                              $iconSvg = '<polygon points="8,5 19,12 8,19" fill="'.$color.'"/>';
                              break;
                            case 'résolue':
                              $color = '#5cb85c'; // vert
                              $iconSvg = '<path fill="'.$color.'" d="M20.285 6.709a1 1 0 0 0-1.414-1.418L9 15.163l-3.868-3.868a1 1 0 0 0-1.414 1.415l4.576 4.577a1 1 0 0 0 1.414 0l9.577-9.577z"/>';
                              break;
                          }
                          echo '<a href="?action=changer_statut&id=' . $r['id_reclamation'] . '&statut=' . $st . '" title="Passer en ' . ucfirst($st) . '" class="action-icon" onclick="return confirm(\'Changer le statut en '.htmlspecialchars($st).' ?\')">';
                          echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="img" aria-label="Changer en ' . ucfirst($st) . '">';
                          echo $iconSvg;
                          echo '</svg>';
                          echo '</a>';
                        }
                      }
                    ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
