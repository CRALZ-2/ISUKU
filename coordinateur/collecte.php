<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Vérification rôle coordinateur ou agent
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['coordinateur', 'agent'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur']; // id de l'agent ou coordinateur connecté
$message = "";

// Création dossier signatures s'il n'existe pas
$signDir = __DIR__ . '/signatures';
if (!is_dir($signDir)) {
    mkdir($signDir, 0777, true);
}

// Fonction pour nettoyer input
function clean($str) {
    return htmlspecialchars(trim($str));
}

// Traitement suppression
if (isset($_GET['del'])) {
    $id_del = (int)$_GET['del'];
    $stmt = $bdd->prepare("SELECT signature_client FROM collecte WHERE id_collecte = ?");
    $stmt->execute([$id_del]);
    $sig = $stmt->fetchColumn();
    if ($sig) {
        $pathSig = __DIR__ . '/' . $sig;
        if (file_exists($pathSig)) unlink($pathSig);
    }
    $bdd->prepare("DELETE FROM collecte WHERE id_collecte = ?")->execute([$id_del]);
    header("Location: collecte.php");
    exit;
}

// Traitement ajout / modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_collecte'])) {
    $id_collecte = isset($_POST['id_collecte']) && is_numeric($_POST['id_collecte']) ? (int)$_POST['id_collecte'] : 0;
    $id_tournee = (int)$_POST['id_tournee'];
    $id_agent = ($role === 'agent') ? $id_utilisateur : clean($_POST['id_agent']);
    $id_client = clean($_POST['id_client']);
    $date_collecte = clean($_POST['date_collecte']);
    $statut = in_array($_POST['statut'], ['prévue','effectuée','annulée']) ? $_POST['statut'] : 'prévue';

    // Gestion de la signature électronique (base64)
    $signatureDataUrl = $_POST['signature_data'] ?? '';

    $signaturePath = null;
    if ($signatureDataUrl) {
        // Exemple: data:image/png;base64,iVBORw0KGgoAAAANS...
        if (preg_match('/^data:image\/png;base64,/', $signatureDataUrl)) {
            $data = base64_decode(substr($signatureDataUrl, strpos($signatureDataUrl, ',') + 1));
            $filename = "signatures/sig_" . time() . '_' . rand(1000,9999) . ".png";
            file_put_contents(__DIR__ . '/' . $filename, $data);
            $signaturePath = $filename;
        }
    }

    if ($id_collecte > 0) {
        // Modification
        if ($signaturePath) {
            // Supprimer ancienne signature
            $old = $bdd->prepare("SELECT signature_client FROM collecte WHERE id_collecte = ?");
            $old->execute([$id_collecte]);
            $oldPath = $old->fetchColumn();
            if ($oldPath && file_exists(__DIR__ . '/' . $oldPath)) unlink(__DIR__ . '/' . $oldPath);
            $stmt = $bdd->prepare("UPDATE collecte SET id_tournee=?, id_agent=?, id_client=?, date_collecte=?, statut=?, signature_client=? WHERE id_collecte=?");
            $stmt->execute([$id_tournee, $id_agent, $id_client, $date_collecte, $statut, $signaturePath, $id_collecte]);
        } else {
            $stmt = $bdd->prepare("UPDATE collecte SET id_tournee=?, id_agent=?, id_client=?, date_collecte=?, statut=? WHERE id_collecte=?");
            $stmt->execute([$id_tournee, $id_agent, $id_client, $date_collecte, $statut, $id_collecte]);
        }
        $message = "Collecte modifiée avec succès.";
    } else {
        // Ajout
        $stmt = $bdd->prepare("INSERT INTO collecte (id_tournee, id_agent, id_client, date_collecte, statut, signature_client) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_tournee, $id_agent, $id_client, $date_collecte, $statut, $signaturePath]);
        header("Location: collecte.php");
        $message = "Collecte ajoutée avec succès.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Gestion des Collectes - ISUKU Co.</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  #signature-canvas {border:1px solid #ccc; border-radius:5px;}
  .signature-img {max-width:100px; cursor:pointer;}
  .modal-lg {max-width: 700px;}
  <style>
    /* Conteneur principal pour sidebar + contenu */
    .main-container {
      display: flex;
      min-height: 100vh;
      /* optionnel : ajuster hauteur selon besoin */
    }

    /* Sidebar */
    .sidebar {
      width: 250px; /* largeur fixe sidebar */
      background-color: #222;
      color: white;
      /* si sidebar.php n'a pas de classe 'sidebar', adapte */
      min-height: 100vh;
      position: fixed; /* ou static selon sidebar.php */
      top: 0;
      left: 0;
      overflow-y: auto;
      padding: 20px;
      box-sizing: border-box;
      z-index: 10;
    }

    /* Contenu principal à droite */
    .content {
      margin-left: 250px; /* même largeur que sidebar */
      padding: 20px;
      flex-grow: 1;
      background-color: #f8f9fa;
      min-height: 100vh;
      box-sizing: border-box;
    }
  </style>
</style>
</head>
<body>
<?php include('sidebar.php'); ?>

<div class="container mt-4" style="margin-left:260px;">
  <h2>Gestion des Collectes</h2>
  <?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
  <?php endif; ?>

  <!-- Bouton ajout -->
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalCollecte" onclick="openForm();">Ajouter une collecte</button>

  <!-- Onglets statut -->
  <ul class="nav nav-tabs mb-3" id="statutTabs" role="tablist">
    <?php
    $statuts = ['prévue','effectuée','annulée'];
    foreach ($statuts as $index => $st) {
      $active = ($index === 0) ? 'active' : '';
      echo "<li class='nav-item'><button class='nav-link $active' id='{$st}-tab' data-bs-toggle='tab' data-bs-target='#tab-{$st}' type='button' role='tab'>{$st}</button></li>";
    }
    ?>
  </ul>

  <div class="tab-content" id="statutTabsContent">
  <?php foreach ($statuts as $index => $st): ?>
    <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" id="tab-<?php echo $st; ?>" role="tabpanel">
      <?php
      // Chargement des collectes du statut
      $sql = "SELECT c.*, t.date_tournee, u1.nom AS agent_nom, u2.nom AS client_nom
              FROM collecte c
              JOIN tournee t ON c.id_tournee = t.id_tournee
              JOIN utilisateur u1 ON c.id_agent = u1.id_utilisateur
              JOIN utilisateur u2 ON c.id_client = u2.id_utilisateur
              WHERE c.statut = ? ORDER BY c.date_collecte DESC";
      $stmt = $bdd->prepare($sql);
      $stmt->execute([$st]);
      $collectes = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (count($collectes) === 0) {
          echo "<p>Aucune collecte avec le statut '{$st}'.</p>";
      } else {
          echo '<table class="table table-striped table-bordered">';
          echo '<thead><tr><th>Tournée (date)</th><th>Agent</th><th>Client</th><th>Date Collecte</th><th>Signature</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';
          foreach ($collectes as $col) {
              echo '<tr>';
              echo '<td>' . htmlspecialchars($col['date_tournee']) . '</td>';
              echo '<td>' . htmlspecialchars($col['agent_nom']) . '</td>';
              echo '<td>' . htmlspecialchars($col['client_nom']) . '</td>';
              echo '<td>' . htmlspecialchars($col['date_collecte']) . '</td>';
              echo '<td>';
              if ($col['signature_client']) {
                  echo '<img src="' . htmlspecialchars($col['signature_client']) . '" alt="Signature" class="signature-img" onclick="window.open(\'' . htmlspecialchars($col['signature_client']) . '\',\'_blank\')" />';
              } else {
                  echo '—';
              }
              echo '</td>';
              echo '<td>' . htmlspecialchars($col['statut']) . '</td>';
              echo '<td>';
              echo '<button class="btn btn-sm btn-warning me-1" onclick=\'openForm(' . json_encode($col) . ')\'>✏️</button>';
              echo '<a href="collecte.php?del=' . $col['id_collecte'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Confirmer la suppression ?\')">🗑️</a>';
              echo '</td>';
              echo '</tr>';
          }
          echo '</tbody></table>';
      }
      ?>
    </div>
  <?php endforeach; ?>
  </div>

  <!-- Modal ajout / modif collecte -->
  <div class="modal fade" id="modalCollecte" tabindex="-1" aria-labelledby="modalCollecteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="formCollecte" method="POST" onsubmit="return prepareSignature();" novalidate>
        <input type="hidden" name="form_collecte" value="1" />
        <input type="hidden" name="id_collecte" id="id_collecte" value="0" />
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalCollecteLabel">Ajouter / Modifier Collecte</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">

            <div class="mb-3">
              <label for="id_tournee" class="form-label">Tournée</label>
              <select class="form-select" id="id_tournee" name="id_tournee" required>
                <option value="">-- Choisir une tournée --</option>
                <?php
                // Chargement des tournées valides
                $query = $bdd->query("SELECT id_tournee, date_tournee FROM tournee WHERE statut = 'planifiée' ORDER BY date_tournee DESC");
                $tours = $query->fetchAll(PDO::FETCH_ASSOC);
                foreach ($tours as $tour) {
                    echo '<option value="' . $tour['id_tournee'] . '">' . htmlspecialchars($tour['date_tournee']) . '</option>';
                }
                ?>
              </select>
            </div>

            <?php if ($role === 'coordinateur'): ?>
            <div class="mb-3">
              <label for="id_agent" class="form-label">Agent</label>
              <select class="form-select" id="id_agent" name="id_agent" required>
                <option value="">-- Choisir un agent --</option>
                <?php
                $agents = $bdd->prepare("SELECT id_utilisateur, nom FROM utilisateur WHERE role = 'agent'");
                $agents->execute();
                foreach ($agents->fetchAll() as $agent) {
                    echo '<option value="' . $agent['id_utilisateur'] . '">' . htmlspecialchars($agent['nom']) . '</option>';
                }
                ?>
              </select>
            </div>
            <?php else: ?>
              <input type="hidden" id="id_agent" name="id_agent" value="<?php echo $id_utilisateur; ?>" />
            <?php endif; ?>

            <div class="mb-3">
              <label for="id_client" class="form-label">Client</label>
              <select class="form-select" id="id_client" name="id_client" required>
                <option value="">-- Choisir un client --</option>
                <?php
                $clients = $bdd->prepare("SELECT u.id_utilisateur,co.id_contrat, nom FROM contrat co JOIN utilisateur u ON u.id_utilisateur=co.id_utilisateur  WHERE role = 'client' AND statut='actif'");
                $clients->execute();
                foreach ($clients->fetchAll() as $client) {
                    echo '<option value="' . $client['id_utilisateur'] . '">' . htmlspecialchars($client['nom']) . '</option>';
                }
                ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="date_collecte" class="form-label">Date de collecte</label>
              <input type="date" class="form-control" id="date_collecte" name="date_collecte" value="<?php echo date('Y-m-d'); ?>" required />
            </div>

            <div class="mb-3">
              <label for="statut" class="form-label">Statut</label>
              <select class="form-select" id="statut" name="statut" required>
                <option value="prévue">Prévue</option>
                <option value="effectuée">Effectuée</option>
                <option value="annulée">Annulée</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Signature client</label>
              <br />
              <canvas id="signature-canvas" width="600" height="200"></canvas>
              <br />
              <button type="button" class="btn btn-sm btn-secondary mt-1" onclick="clearCanvas()">Effacer la signature</button>
              <input type="hidden" name="signature_data" id="signature_data" />
            </div>

          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Enregistrer</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          </div>
        </div>
      </form>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let canvas, ctx, drawing = false, currentPos = {x:0, y:0};

function initCanvas() {
    canvas = document.getElementById('signature-canvas');
    ctx = canvas.getContext('2d');
    ctx.strokeStyle = "#222";
    ctx.lineWidth = 2;
    ctx.lineCap = "round";
    ctx.lineJoin = "round";

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);

    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);
}

function getPos(e) {
    let rect = canvas.getBoundingClientRect();
    if (e.touches) {
        return {
            x: e.touches[0].clientX - rect.left,
            y: e.touches[0].clientY - rect.top
        };
    } else {
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }
}

function startDrawing(e) {
    e.preventDefault();
    drawing = true;
    currentPos = getPos(e);
    ctx.beginPath();
    ctx.moveTo(currentPos.x, currentPos.y);
}

function draw(e) {
    if (!drawing) return;
    e.preventDefault();
    let pos = getPos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
}

function stopDrawing(e) {
    if (!drawing) return;
    e.preventDefault();
    drawing = false;
}

function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function openForm(data=null) {
    clearCanvas();
    if (data) {
        document.getElementById('id_collecte').value = data.id_collecte;
        document.getElementById('id_tournee').value = data.id_tournee;
        <?php if ($role === 'coordinateur'): ?>
        document.getElementById('id_agent').value = data.id_agent;
        <?php endif; ?>
        document.getElementById('id_client').value = data.id_client;
        document.getElementById('date_collecte').value = data.date_collecte;
        document.getElementById('statut').value = data.statut;

        // Charger la signature si elle existe
        if(data.signature_client){
            let img = new Image();
            img.onload = function() {
                clearCanvas();
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            }
            img.src = data.signature_client;
        }
    } else {
        document.getElementById('formCollecte').reset();
        document.getElementById('id_collecte').value = 0;
    }
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCollecte'));
    modal.show();
}

function prepareSignature() {
    const dataUrl = canvas.toDataURL('image/png');
    document.getElementById('signature_data').value = dataUrl;
    return true;
}

window.onload = initCanvas;
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalElement = document.getElementById('modalCollecte');

  // Réinitialiser le formulaire et la signature à chaque fermeture
  modalElement.addEventListener('hidden.bs.modal', function () {
    document.getElementById('formCollecte').reset();
    document.getElementById('id_collecte').value = 0;
    clearCanvas();
  });

  // Supprimer tout backdrop restant manuellement si jamais
  modalElement.addEventListener('hide.bs.modal', function () {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
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
