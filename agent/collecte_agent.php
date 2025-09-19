<?php
session_start();
include('auth_session.php');
// Vérification rôle agent
if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit;
}

$id_agent = $_SESSION['id_utilisateur'];

try {
    $bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die("Erreur connexion BDD : " . $e->getMessage());
}

$message = isset($_GET['success']) ? $_GET['success'] : '';


// Création dossier signatures si inexistant
$signDir = __DIR__ . '/signatures';
if (!is_dir($signDir)) {
    mkdir($signDir, 0777, true);
}

// Nettoyage
function clean($str) {
    return htmlspecialchars(trim($str));
}

// SUPPRESSION
if (isset($_GET['del'])) {
    $id_del = (int)$_GET['del'];
    // Récup signature existante
    $stmt = $bdd->prepare("SELECT signature_client FROM collecte WHERE id_collecte = ? AND id_agent = ?");
    $stmt->execute([$id_del, $id_agent]);
    $sig = $stmt->fetchColumn();
    if ($sig && file_exists(__DIR__ . '/' . $sig)) {
        unlink(__DIR__ . '/' . $sig);
    }
    $delStmt = $bdd->prepare("DELETE FROM collecte WHERE id_collecte = ? AND id_agent = ?");
    $delStmt->execute([$id_del, $id_agent]);
    header("Location: collecte_agent.php?success=" . urlencode("Collecte supprimée."));
    exit;
}


// AJOUT / MODIFICATION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_collecte = isset($_POST['id_collecte']) && is_numeric($_POST['id_collecte']) ? (int)$_POST['id_collecte'] : 0;
    $id_tournee = isset($_POST['id_tournee']) ? (int)$_POST['id_tournee'] : 0;
    $id_client = isset($_POST['id_client']) ? clean($_POST['id_client']) : '';
    $date_collecte = isset($_POST['date_collecte']) ? clean($_POST['date_collecte']) : '';
    $statut = 'effectuée';
    $signatureDataUrl = $_POST['signature_data'] ?? '';

    // Validation simple
    if ($id_tournee <= 0 || empty($id_client)) {
        $message = "Veuillez remplir tous les champs.";
    } else {
        $signaturePath = null;
        // Si signature envoyée, on la sauvegarde
        if ($signatureDataUrl && preg_match('/^data:image\/png;base64,/', $signatureDataUrl)) {
            $data = base64_decode(substr($signatureDataUrl, strpos($signatureDataUrl, ',') + 1));
            $filename = "signatures/sig_" . time() . "_" . rand(1000,9999) . ".png";
            file_put_contents(__DIR__ . '/' . $filename, $data);
            $signaturePath = $filename;
        }

        if ($id_collecte > 0) {
            // Modification: si signature nouvelle, supprimer l'ancienne
            if ($signaturePath) {
                $old = $bdd->prepare("SELECT signature_client FROM collecte WHERE id_collecte = ? AND id_agent = ?");
                $old->execute([$id_collecte, $id_agent]);
                $oldPath = $old->fetchColumn();
                if ($oldPath && file_exists(__DIR__ . '/' . $oldPath)) {
                    unlink(__DIR__ . '/' . $oldPath);
                }
                $stmt = $bdd->prepare("UPDATE collecte SET id_tournee=?, id_client=?, statut=?, signature_client=? WHERE id_collecte=? AND id_agent=?");
                $stmt->execute([$id_tournee, $id_client, $statut, $signaturePath, $id_collecte, $id_agent]);
            } else {
                $stmt = $bdd->prepare("UPDATE collecte SET id_tournee=?, id_client=?, statut=? WHERE id_collecte=? AND id_agent=?");
                $stmt->execute([$id_tournee, $id_client, $statut, $id_collecte, $id_agent]);
            }
            header("Location: collecte_agent.php?success=" . urlencode("Collecte modifiée."));
            exit;
        } else {
            // Ajout
            $stmt = $bdd->prepare("INSERT INTO collecte (id_tournee, id_agent, id_client, statut, signature_client) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_tournee, $id_agent, $id_client, $statut, $signaturePath]);
            header("Location: collecte_agent.php?success=" . urlencode("Collecte ajoutée."));
            exit;
        }
    }
}

// Récup données pour formulaire
$tournees = $bdd->prepare("SELECT id_tournee, date_tournee FROM tournee ORDER BY date_tournee DESC");
$tournees->execute();
$tournees = $tournees->fetchAll(PDO::FETCH_ASSOC);

$clients = $bdd->prepare("
    SELECT u.id_utilisateur, u.nom 
    FROM contrat co
    JOIN utilisateur u ON u.id_utilisateur = co.id_utilisateur
    WHERE u.role = 'client' AND co.statut = 'actif'
    GROUP BY u.id_utilisateur
    ORDER BY u.nom
");
$clients->execute();
$clients = $clients->fetchAll(PDO::FETCH_ASSOC);


$collectes = $bdd->prepare("
    SELECT c.*, t.date_tournee, u.nom AS client_nom 
    FROM collecte c
    JOIN tournee t ON c.id_tournee = t.id_tournee
    JOIN utilisateur u ON c.id_client = u.id_utilisateur
    WHERE c.id_agent = ?
    ORDER BY c.date_collecte DESC
");
$collectes->execute([$id_agent]);
$collectes = $collectes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Collectes avec signature</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <style>
        body { background-color: #f5f7fa; font-family: 'Segoe UI', sans-serif; padding-top: 80px; }
        canvas { border:1px solid #ccc; border-radius: 5px; width: 100%; height: 150px; }
        .signature-img { max-width: 100px; cursor: pointer; }
         .card { border-radius: 16px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .card:hover { transform: translateY(-3px); }
    .card-title { font-weight: 600; }
    .navbar { background-color: #263238; }
    .nav-link { color: #ffffff !important; font-weight: 500; font-size: 1.05rem; padding: 10px 15px; transition: 0.3s; border-radius: 6px; }
    .nav-link:hover { background-color: #37474f; color: #00bcd4 !important; }
    .btn-deconnexion { background-color: #f44336; color: white !important; border-radius: 20px; padding: 6px 15px; font-size: 0.9rem; }
    .btn-deconnexion:hover { background-color: #d32f2f; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top shadow animate__animated animate__fadeInDown">
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
        <li class="nav-item mx-2">
          <a class="nav-link" href="dashboard_agent.php"><i class="fas fa-home"></i> Accueil</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="zone_agent.php"><i class="fas fa-map-marked-alt"></i> Zones</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="collecte_agent.php"><i class="fas fa-trash"></i> Collectes</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="mon_profil.php"><i class="fas fa-user"></i> Mon Profil</a>
        </li>
        <li class="nav-item ms-3">
          <a class="btn btn-deconnexion" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
    <h1>Gestion Collectes</h1>

    <?php if ($message): ?>
  <div id="alertMessage" class="alert alert-success text-center">
    <?= htmlspecialchars($message) ?>
  </div>
  <script>
    setTimeout(() => {
      const msg = document.getElementById('alertMessage');
      if (msg) {
        msg.style.transition = "opacity 0.5s ease-out";
        msg.style.opacity = "0";
        setTimeout(() => msg.remove(), 500);
      }
    }, 1000);
  </script>
<?php endif; ?>


    <form method="post" onsubmit="return saveSignature()">
        <input type="hidden" name="id_collecte" id="id_collecte" value="0" />
        <div class="mb-3">
            <label for="id_tournee" class="form-label">Tournée</label>
            <select name="id_tournee" id="id_tournee" class="form-select" required>
                <option value="">-- Choisir une tournée --</option>
                <?php foreach ($tournees as $t): ?>
                    <option value="<?= $t['id_tournee'] ?>"><?= htmlspecialchars($t['date_tournee']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="id_client" class="form-label">Client</label>
            <select name="id_client" id="id_client" class="form-select" required>
                <option value="">-- Choisir un client --</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= htmlspecialchars($c['id_utilisateur']) ?>"><?= htmlspecialchars($c['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <input type="hidden" name="statut" value="effectuée">
        </div>

        <div class="mb-3">
            <label>Signature client</label>
            <canvas id="canvasSig"></canvas>
            <input type="hidden" name="signature_data" id="signature_data" />
            <button type="button" class="btn btn-secondary mt-2" onclick="clearCanvas()">Effacer la signature</button>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>

    <hr />

    <h2>Liste des collectes</h2>

    <?php if (empty($collectes)): ?>
        <p>Aucune collecte enregistrée.</p>
    <?php else: ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Tournée (date)</th>
                    <th>Client</th>
                    <th>Date collecte</th>
                    <th>Statut</th>
                    <th>Signature</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($collectes as $col): ?>
                    <tr>
                        <td><?= htmlspecialchars($col['date_tournee']) ?></td>
                        <td><?= htmlspecialchars($col['client_nom']) ?></td>
                        <td><?= htmlspecialchars($col['date_collecte']) ?></td>
                        <td><?= htmlspecialchars($col['statut']) ?></td>
                        <td>
                            <?php if ($col['signature_client'] && file_exists(__DIR__ . '/' . $col['signature_client'])): ?>
                                <img src="<?= $col['signature_client'] ?>" class="signature-img" alt="Signature" onclick="window.open('<?= $col['signature_client'] ?>','_blank')" />
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='editCollecte(<?= json_encode($col) ?>)'>Modifier</button>
                            <a href="?del=<?= $col['id_collecte'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette collecte ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
const canvas = document.getElementById('canvasSig');
const ctx = canvas.getContext('2d');
let drawing = false;

function resizeCanvas() {
    canvas.width = canvas.offsetWidth;
    canvas.height = 150;
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#000';
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

canvas.addEventListener('mousedown', e => {
    drawing = true;
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
});
canvas.addEventListener('mousemove', e => {
    if (!drawing) return;
    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
});
canvas.addEventListener('mouseup', () => drawing = false);
canvas.addEventListener('mouseout', () => drawing = false);

function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function saveSignature() {
    const dataURL = canvas.toDataURL('image/png');
    document.getElementById('signature_data').value = dataURL;
    return true; // autorise l'envoi du formulaire
}

function resetForm() {
    document.getElementById('id_collecte').value = 0;
    document.querySelector('form').reset();
    clearCanvas();
}

// Charge les données dans le formulaire pour modification
function editCollecte(data) {
    document.getElementById('id_collecte').value = data.id_collecte;
    document.getElementById('id_tournee').value = data.id_tournee;
    document.getElementById('id_client').value = data.id_client;
    // Format date pour input datetime-local (ex: 2025-06-19T19:30)
    const d = new Date(data.date_collecte);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth()+1).padStart(2,'0');
    const dd = String(d.getDate()).padStart(2,'0');
    const hh = String(d.getHours()).padStart(2,'0');
    const mi = String(d.getMinutes()).padStart(2,'0');
    document.getElementById('date_collecte').value = `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
    document.getElementById('statut').value = data.statut;

    clearCanvas();
    if (data.signature_client) {
        const img = new Image();
        img.onload = function() {
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        };
        img.src = data.signature_client;
    }
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
