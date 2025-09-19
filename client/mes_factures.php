<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}

$id_client = $_SESSION['id_utilisateur'];
$message = '';
$message_type = '';

// Dossier upload justificatifs
$uploadDir = __DIR__ . '/uploads/justificatifs_factures/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Gestion upload justificatif par client (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_facture_upload'])) {
    $id_facture_upload = (int)$_POST['id_facture_upload'];

    // Vérifier que la facture appartient bien au client
    $stmt = $bdd->prepare("SELECT id_facture, justificatif FROM facturation WHERE id_facture = ? AND id_contrat IN (SELECT id_contrat FROM contrat WHERE id_utilisateur = ?)");
    $stmt->execute([$id_facture_upload, $id_client]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$facture) {
        $message = "Facture introuvable ou accès refusé.";
        $message_type = "danger";
    } elseif (!isset($_FILES['justificatif_upload']) || $_FILES['justificatif_upload']['error'] === UPLOAD_ERR_NO_FILE) {
        $message = "Veuillez choisir un fichier PDF à uploader.";
        $message_type = "warning";
    } else {
        $file = $_FILES['justificatif_upload'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $message = "Erreur lors de l'upload du fichier.";
            $message_type = "danger";
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['png', 'jpg'];
            if (!in_array($ext, $allowedExtensions)) {
                $message = "Le justificatif doit être un fichier en JPG ou PNG.";
                $message_type = "warning";
            } else {
                $filename = uniqid('justif_') . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    // Supprimer ancien justificatif si existant
                    if ($facture['justificatif'] && file_exists($uploadDir . $facture['justificatif'])) {
                        unlink($uploadDir . $facture['justificatif']);
                    }
                    // Mettre à jour la facture avec le nouveau justificatif + reset statut à "en attente de validation"
                    $update = $bdd->prepare("UPDATE facturation SET justificatif = ?, statut = 'en attente' WHERE id_facture = ?");
                    $update->execute([$filename, $id_facture_upload]);

                    $message = "Justificatif uploadé avec succès, en attente de validation.";
                    $message_type = "success";
                } else {
                    $message = "Erreur lors de l'enregistrement du fichier.";
                    $message_type = "danger";
                }
            }
        }
    }
}

// Récupérer factures du client avec détails
$stmt = $bdd->prepare("
    SELECT f.*, 
           c.type_contrat, c.date_debut, c.date_fin,
           a.message AS message_annonce,
           m.nom_moyen
    FROM facturation f
    JOIN contrat c ON f.id_contrat = c.id_contrat
    JOIN annonce_collecte a ON f.id_annonce = a.id_annonce
    LEFT JOIN moyen_paiement m ON f.id_moyen = m.id_moyen
    WHERE c.id_utilisateur = ?
    ORDER BY f.date_facture DESC
");
$stmt->execute([$id_client]);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mes Factures - ISUKU CO.</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
  body { padding-top: 80px; background: #f9fafb; }
  .badge-status {
    font-size: 0.9rem;
    font-weight: 600;
  }
  .navbar {
      background-color: #263238;
    }

    .navbar-brand img {
      height: 40px;
      margin-right: 10px;
    }

    .nav-link {
      color: #ffffff !important;
      font-weight: 500;
      font-size: 1.05rem;
      padding: 10px 15px;
      transition: background-color 0.3s, color 0.3s;
      border-radius: 6px;
    }

    .nav-link:hover {
      background-color: #37474f;
      color: #00bcd4 !important;
    }

    .btn-deconnexion {
      background-color: #f44336;
      color: white !important;
      border-radius: 20px;
      padding: 6px 15px;
      font-size: 0.9rem;
    }

    .btn-deconnexion:hover {
      background-color: #d32f2f;
    }

    .badge.ouverte {
      background-color: #fbc02d;
      color: black;
    }

    .badge.en-cours {
      background-color: #2196f3;
    }

    .badge.résolue {
      background-color: #4caf50;
    }

    table {
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
    }

    .table thead {
      background-color: #0d53b1;
      color: white;
    }

</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top shadow">
  <div class="container">
    <img src="./images/logo1.png" alt="Logo Recyclage" style="height: 40px; margin-right: 2px;">
    <h5 class="mb-0">
      <strong style="font-weight: 700; font-size: 20px; color: #0d53b1;">ISUKU</strong>
      <span style="font-weight: 400; font-size: 20px; color: #0d9855;">CO.</span>
    </h5>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item mx-2"><a class="nav-link" href="dashboard_client.php"><i class="fas fa-home"></i> Accueil</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="contrat_client.php"><i class="fas fa-file-contract"></i> Contrats</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="mes_factures.php"><i class="fas fa-calendar-plus"></i> Factures</a></li>
        <li class="nav-item mx-2"><a class="nav-link active" href="mes_reclamations.php"><i class="fas fa-comment-dots"></i> Réclamations</a></li>
        <li class="nav-item ms-3"><a class="btn btn-deconnexion" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2>📄 Mes Factures</h2>

  <?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if (count($factures) === 0): ?>
    <div class="alert alert-warning">Vous n'avez aucune facture enregistrée pour le moment.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-dark">
          <tr>
            <th>Date facture</th>
            <th>Contrat (Type)</th>
            <th>Période contrat</th>
            <th>Annonce</th>
            <th>Moyen de paiement</th>
            <th>Montant (FCFA)</th>
            <th>Statut</th>
            <th>Justificatif</th>
            <th>Uploader un justificatif</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($factures as $f): ?>
          <tr>
             <td><?= htmlspecialchars(date('d/m/Y', strtotime($f['date_facture']))) ?></td>
             <td><?= htmlspecialchars(ucfirst($f['type_contrat'])) ?></td>
             <td><?= htmlspecialchars(date('d/m/Y', strtotime($f['date_debut']))) ?> - <?= htmlspecialchars(date('d/m/Y', strtotime($f['date_fin']))) ?></td>
             <td><?= htmlspecialchars($f['message_annonce']) ?></td>
             <td><?= htmlspecialchars($f['nom_moyen'] ?? '—') ?></td>
             <td><?= number_format($f['montant'], 0, ',', ' ') ?></td>
             <td>
               <?php
                 $statut = $f['statut'];
                 $class = 'secondary';
                 if ($statut === 'payée') $class = 'success';
                 elseif ($statut === 'en attente') $class = 'warning';
                 elseif ($statut === 'annulée') $class = 'danger';
               ?>
               <span class="badge bg-<?= $class ?> badge-status"><?= ucfirst($statut) ?></span>
             </td>
             <td>
               <?php if ($f['justificatif'] && file_exists($uploadDir . $f['justificatif'])): ?>
                 <a href="uploads/justificatifs_factures/<?= urlencode($f['justificatif']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Voir</a>
               <?php else: ?>
                 <span class="text-muted">-</span>
               <?php endif; ?>
             </td>
             <td>
               <form method="post" enctype="multipart/form-data" class="d-flex align-items-center" style="gap:8px;">
                 <input type="hidden" name="id_facture_upload" value="<?= $f['id_facture'] ?>">
                 <input type="file" name="justificatif_upload" accept=".png,.jpg" required class="form-control form-control-sm" />
                 <button type="submit" class="btn btn-sm btn-primary">Uploader</button>
               </form>
             </td>
           </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  let inactivityTime = function () {
    let timer;
    let timeoutDuration = 300000; // 5 minutes en ms

    function resetTimer() {
      clearTimeout(timer);
      timer = setTimeout(() => {
        window.location.href = "login.php?timeout=1";
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
