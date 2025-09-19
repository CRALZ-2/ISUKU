<?php 
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8', 'root', '');

// Vérifie et met à jour les contrats expirés automatiquement
$bdd->exec("UPDATE contrat SET statut = 'expiré' WHERE date_fin < CURDATE() AND statut != 'expiré'");

// Suppression
if (isset($_GET['del'])) {
    $id_del = intval($_GET['del']);
    $bdd->prepare("DELETE FROM contrat WHERE id_contrat = ?")->execute([$id_del]);
    header("Location: contrat.php");
    exit();
}

// Modification
if (isset($_POST['edit_id'])) {
    $id_edit = intval($_POST['edit_id']);
    $id_utilisateur = $_POST['edit_id_utilisateur'];
    $id_zone = $_POST['edit_id_zone'];
    $type_contrat = $_POST['edit_type_contrat'];
    $statut = $_POST['edit_statut'];
    $date_debut = $_POST['edit_date_debut'];
    $justificatif = $_FILES['edit_justificatif']['name'] ?? null;

    if ($justificatif && $_FILES['edit_justificatif']['type'] == 'application/pdf') {
        $uploadPath = 'uploads/' . basename($justificatif);
        move_uploaded_file($_FILES['edit_justificatif']['tmp_name'], $uploadPath);
    } else {
        $uploadPath = $_POST['justificatif_existant'] ?? null;
    }

    $dateDebutObj = new DateTime($date_debut);
    switch ($type_contrat) {
        case 'mensuel': $dateDebutObj->modify('+1 month'); break;
        case 'trimestriel': $dateDebutObj->modify('+3 months'); break;
        case 'annuel': $dateDebutObj->modify('+1 year'); break;
    }
    $date_fin = $dateDebutObj->format('Y-m-d');

    $stmt = $bdd->prepare("UPDATE contrat SET id_utilisateur=?, id_zone=?, type_contrat=?, statut=?, justificatif=?, date_debut=?, date_fin=? WHERE id_contrat=?");
    $stmt->execute([$id_utilisateur, $id_zone, $type_contrat, $statut, $uploadPath, $date_debut, $date_fin, $id_edit]);

    header("Location: contrat.php");
    exit();
}

// Ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contrat'])) {
    $id_utilisateur = $_POST['id_utilisateur'];
    $id_zone = $_POST['id_zone'];
    $type_contrat = $_POST['type_contrat'];
    $statut = $_POST['statut'] ?? 'en attente';
    $date_debut = $_POST['date_debut'];
    $justificatif = null;

    if (isset($_FILES['justificatif']) && $_FILES['justificatif']['type'] == 'application/pdf') {
        $justificatif = $_FILES['justificatif']['name'];
        $uploadPath = 'uploads/' . basename($justificatif);
        move_uploaded_file($_FILES['justificatif']['tmp_name'], $uploadPath);
    } else {
        $uploadPath = null;
    }

    $dateDebutObj = new DateTime($date_debut);
    switch ($type_contrat) {
        case 'mensuel': $dateDebutObj->modify('+1 month'); break;
        case 'trimestriel': $dateDebutObj->modify('+3 months'); break;
        case 'annuel': $dateDebutObj->modify('+1 year'); break;
    }
    $date_fin = $dateDebutObj->format('Y-m-d');

    $stmt = $bdd->prepare("INSERT INTO contrat (id_utilisateur, id_zone, type_contrat, statut, justificatif, date_debut, date_fin) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id_utilisateur, $id_zone, $type_contrat, $statut, $uploadPath, $date_debut, $date_fin]);

    header("Location: contrat.php");
    exit();
}
// Récupérer utilisateurs pour formulaire
$utilisateurs = $bdd->query("SELECT id_utilisateur, nom FROM utilisateur WHERE role='client' ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer zones (nom_quartier)
$zones = $bdd->query("SELECT id_zone, nom_quartier FROM zone ORDER BY nom_quartier")->fetchAll(PDO::FETCH_ASSOC);

// Types et statuts pour affichage et filtres
$types = ['mensuel', 'trimestriel', 'annuel'];
$statuts = ['attente', 'actif', 'terminé'];

// Fonction pour récupérer contrats filtrés par type et statut
function getContratsByTypeStatut($bdd, $type, $statut) {
    $stmt = $bdd->prepare("
        SELECT c.*, u.nom AS nom_utilisateur, z.nom_quartier 
        FROM contrat c 
        LEFT JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur 
        LEFT JOIN zone z ON c.id_zone = z.id_zone 
        WHERE c.type_contrat = ? AND c.statut = ?
        ORDER BY c.date_debut DESC
    ");
    $stmt->execute([$type, $statut]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestion des Contrats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="container-fluid p-4">
        <h1 class="mb-4">Gestion des Contrats</h1>

        <!-- Bouton Ajouter en modal -->
        <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#addModal">Ajouter un contrat</button>

        <!-- Onglets pour types de contrat -->
        <ul class="nav nav-tabs mb-3" id="typeTabs" role="tablist">
            <?php foreach($types as $index => $type): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $i === 0 ? 'active' : '' ?>" 
                            id="tab-<?= $type ?>" 
                            data-bs-toggle="tab" 
                            data-bs-target="#content-<?= $type ?>" 
                            type="button" role="tab" 
                            aria-controls="content-<?= $type ?>" 
                            aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                        <?= ucfirst($type) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content" id="typeTabsContent">
            <?php foreach ($types as $index => $type): ?>
                <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="content-<?= $type ?>" role="tabpanel" aria-labelledby="tab-<?= $type ?>">

                    <!-- Sous-onglets statut -->
                    <ul class="nav nav-pills mb-3" id="statutTabs-<?= $type ?>" role="tablist">
                        <?php foreach ($statuts as $i => $statut): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $i === 0 ? 'active' : '' ?>" 
                                        id="tab-<?= $type . '-' . $statut ?>" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#content-<?= $type . '-' . $statut ?>" 
                                        type="button" role="tab" 
                                        aria-controls="content-<?= $type . '-' . $statut ?>" 
                                        aria-selected="<?= $i === 0 ? 'true' : 'false' ?>">
                                    <?= ucfirst($statut) ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content" id="statutTabsContent-<?= $type ?>">
                        <?php foreach ($statuts as $i => $statut): 
                            $contrats = getContratsByTypeStatut($bdd, $type, $statut);
                        ?>
                            <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" 
                                 id="content-<?= $type . '-' . $statut ?>" 
                                 role="tabpanel" 
                                 aria-labelledby="tab-<?= $type . '-' . $statut ?>">
                                
                                <?php if (empty($contrats)): ?>
                                    <div class="alert alert-info">Aucun contrat <?= $type ?> au statut <?= $statut ?>.</div>
                                <?php else: ?>
                                    <table class="table table-bordered table-striped align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Client</th>
                                                <th>Quartier</th>
                                                <th>Justificatif</th>
                                                <th>Date début</th>
                                                <th>Date fin</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($contrats as $contrat): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($contrat['nom_utilisateur'] ?? 'Inconnu') ?></td>
                                                    <td><?= htmlspecialchars($contrat['nom_quartier'] ?? 'Inconnu') ?></td>
                                                    <td><?= htmlspecialchars($contrat['justificatif']) ?></td>
                                                    <td><?= htmlspecialchars($contrat['date_debut']) ?></td>
                                                    <td><?= htmlspecialchars($contrat['date_fin']) ?></td>
                                                    <td>
                                                        <!-- Modifier bouton -->
                                                        <button class="btn btn-sm btn-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editModal<?= $contrat['id_contrat'] ?>">
                                                            Modifier
                                                        </button>
                                                        <!-- Supprimer bouton -->
                                                        <a href="contrat.php?del=<?= $contrat['id_contrat'] ?>" 
                                                           onclick="return confirm('Supprimer ce contrat ?')" 
                                                           class="btn btn-sm btn-danger">
                                                           Supprimer
                                                        </a>
                                                    </td>
                                                </tr>

                                                <!-- Modal de modification -->
                                                <div class="modal fade" id="editModal<?= $contrat['id_contrat'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $contrat['id_contrat'] ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <form method="POST" action="contrat.php">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="editModalLabel<?= $contrat['id_contrat'] ?>">Modifier contrat #<?= $contrat['id_contrat'] ?></h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                                                </div>
                                                                <div class="modal-body row g-3">
                                                                    <input type="hidden" name="edit_id" value="<?= $contrat['id_contrat'] ?>" />
                                                                    
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Client</label>
                                                                        <select name="edit_id_utilisateur" class="form-select" required>
                                                                            <?php foreach ($utilisateurs as $user): ?>
                                                                                <option value="<?= htmlspecialchars($user['id_utilisateur']) ?>" <?= $user['id_utilisateur'] == $contrat['id_utilisateur'] ? 'selected' : '' ?>>
                                                                                    <?= htmlspecialchars($user['nom']) ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Quartier</label>
                                                                        <select name="edit_id_zone" class="form-select" required>
                                                                            <?php foreach ($zones as $zone): ?>
                                                                                <option value="<?= htmlspecialchars($zone['id_zone']) ?>" <?= $zone['id_zone'] == $contrat['id_zone'] ? 'selected' : '' ?>>
                                                                                    <?= htmlspecialchars($zone['nom_quartier']) ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Type de contrat</label>
                                                                        <select name="edit_type_contrat" class="form-select" required>
                                                                            <?php foreach($types as $t): ?>
                                                                                <option value="<?= $t ?>" <?= $contrat['type_contrat'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Statut</label>
                                                                        <select name="edit_statut" class="form-select" required>
                                                                            <?php foreach($statuts as $s): ?>
                                                                                <option value="<?= $s ?>" <?= $contrat['statut'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Date de début</label>
                                                                        <input type="date" name="edit_date_debut" class="form-control" value="<?= htmlspecialchars($contrat['date_debut']) ?>" required/>
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <label class="form-label">Justificatif</label>
                                                                       <input type="file" name="justificatif" class="form-control mb-2" accept="application/pdf">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal Ajouter -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="contrat.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Ajouter un nouveau contrat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="add_contrat" value="1" />
                    <div class="col-md-6">
                        <label for="id_utilisateur" class="form-label">Client</label>
                        <select name="id_utilisateur" id="id_utilisateur" class="form-select" required>
                            <option value="">-- Sélectionnez un client --</option>
                            <?php foreach ($utilisateurs as $user): ?>
                                <option value="<?= htmlspecialchars($user['id_utilisateur']) ?>"><?= htmlspecialchars($user['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="id_zone" class="form-label">Quartier</label>
                        <select name="id_zone" id="id_zone" class="form-select" required>
                            <option value="">-- Sélectionnez un quartier --</option>
                            <?php foreach ($zones as $zone): ?>
                                <option value="<?= htmlspecialchars($zone['id_zone']) ?>"><?= htmlspecialchars($zone['nom_quartier']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="type_contrat" class="form-label">Type de contrat</label>
                        <select name="type_contrat" id="type_contrat" class="form-select" required>
                            <option value="">-- Choisir le type --</option>
                            <?php foreach($types as $t): ?>
                                <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="statut" class="form-label">Statut</label>
                        <select name="statut" id="statut" class="form-select" required>
                            <?php foreach($statuts as $s): ?>
                                <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="date_debut" class="form-label">Date de début</label>
                        <input type="date" name="date_debut" id="date_debut" class="form-control" required value="<?= date('Y-m-d') ?>" />
                    </div>
                    <div class="col-12">
                        <label for="justificatif" class="form-label">Justificatif (URL ou description)</label>
                        <input type="file" name="justificatif" class="form-control mb-2" accept="application/pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
