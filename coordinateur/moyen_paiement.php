<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8', 'root', '');
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$types = [
    'espèces' => 'Espèces',
    'mobile_money' => 'Mobile Money',
    'banque' => 'Banque',
    'autre' => 'Autre'
];
$typeActif = $_GET['type'] ?? 'espèces';


// Ajout
if (isset($_POST['ajouter'])) {
    $type = $_POST['type'];
    $nom_moyen = $_POST['nom_moyen'];
    $nom_compte = $_POST['nom_compte'];
    $nom_banque = $_POST['nom_banque'];
    $numero_compte = $_POST['numero_compte'];
    $description = $_POST['description'];
    $actif = isset($_POST['actif']) ? 1 : 0;

    $stmt = $bdd->prepare("INSERT INTO moyen_paiement (type, nom_moyen, nom_compte, nom_banque, numero_compte, description, actif) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$type, $nom_moyen, $nom_compte, $nom_banque, $numero_compte, $description, $actif]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Suppression
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $bdd->prepare("DELETE FROM moyen_paiement WHERE id_moyen = ?")->execute([$id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Modification
if (isset($_POST['modifier'])) {
    $id = $_POST['id_moyen'];
    $type = $_POST['type'];
    $nom_moyen = $_POST['nom_moyen'];
    $nom_compte = $_POST['nom_compte'];
    $nom_banque = $_POST['nom_banque'];
    $numero_compte = $_POST['numero_compte'];
    $description = $_POST['description'];
    $actif = isset($_POST['actif']) ? 1 : 0;

    $stmt = $bdd->prepare("UPDATE moyen_paiement 
                           SET type=?, nom_moyen=?, nom_compte=?, nom_banque=?, numero_compte=?, description=?, actif=? 
                           WHERE id_moyen=?");
    $stmt->execute([$type, $nom_moyen, $nom_compte, $nom_banque, $numero_compte, $description, $actif, $id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Lecture
$stmt = $bdd->prepare("SELECT * FROM moyen_paiement WHERE type = ? ORDER BY date_creation DESC");
$stmt->execute([$typeActif]);
$moyens = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Moyens de Paiement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>
<body>

<?php include("sidebar.php"); ?>

<div class="main-content">
    <h2>Moyens de Paiement</h2>
    <button class="btn btn-success my-3" data-bs-toggle="modal" data-bs-target="#addModal">Ajouter un moyen</button>
<div class="mb-3">
  <?php foreach ($types as $key => $label): ?>
    <a href="?type=<?= $key ?>"
       class="btn <?= ($typeActif === $key) ? 'btn-primary' : 'btn-outline-primary' ?> me-2">
       <?= $label ?>
    </a>
  <?php endforeach; ?>
</div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Type</th>
                <th>Nom</th>
                <th>Compte</th>
                <th>Banque</th>
                <th>Numéro</th>
                <th>Description</th>
                <th>Actif</th>
                <th>Date création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($moyens as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['type']) ?></td>
                    <td><?= htmlspecialchars($m['nom_moyen']) ?></td>
                    <td><?= htmlspecialchars($m['nom_compte']) ?></td>
                    <td><?= htmlspecialchars($m['nom_banque']) ?></td>
                    <td><?= htmlspecialchars($m['numero_compte']) ?></td>
                    <td><?= htmlspecialchars($m['description']) ?></td>
                    <td><?= $m['actif'] ? 'Oui' : 'Non' ?></td>
                    <td><?= $m['date_creation'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $m['id_moyen'] ?>">Modifier</button>
                        <a href="?supprimer=<?= $m['id_moyen'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce moyen ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php foreach ($moyens as $m): ?>
<!-- Modal modification -->
<div class="modal fade" id="editModal<?= $m['id_moyen'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <input type="hidden" name="id_moyen" value="<?= $m['id_moyen'] ?>">
            <div class="modal-header"><h5 class="modal-title">Modifier le moyen</h5></div>
            <div class="modal-body">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option <?= $m['type'] == 'espèces' ? 'selected' : '' ?>>espèces</option>
                    <option <?= $m['type'] == 'mobile_money' ? 'selected' : '' ?>>mobile_money</option>
                    <option <?= $m['type'] == 'banque' ? 'selected' : '' ?>>banque</option>
                    <option <?= $m['type'] == 'autre' ? 'selected' : '' ?>>autre</option>
                </select>
                <label>Nom du moyen</label>
                <input name="nom_moyen" value="<?= $m['nom_moyen'] ?>" class="form-control" required>
                <label>Nom du compte</label>
                <input name="nom_compte" value="<?= $m['nom_compte'] ?>" class="form-control">
                <label>Nom de la banque</label>
                <input name="nom_banque" value="<?= $m['nom_banque'] ?>" class="form-control">
                <label>Numéro du compte</label>
                <input name="numero_compte" value="<?= $m['numero_compte'] ?>" class="form-control">
                <label>Description</label>
                <textarea name="description" class="form-control"><?= $m['description'] ?></textarea>
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" name="actif" value="1" <?= $m['actif'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Actif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="modifier" class="btn btn-primary">Modifier</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

</div>


<!-- Modal ajout -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Ajouter un moyen</h5></div>
            <div class="modal-body">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="espèces">espèces</option>
                    <option value="mobile_money">mobile_money</option>
                    <option value="banque">banque</option>
                    <option value="autre">autre</option>
                </select>
                <label>Nom du moyen</label><input name="nom_moyen" class="form-control" required>
                <label>Nom du compte</label><input name="nom_compte" class="form-control">
                <label>Nom de la banque</label><input name="nom_banque" class="form-control">
                <label>Numéro du compte</label><input name="numero_compte" class="form-control">
                <label>Description</label><textarea name="description" class="form-control"></textarea>
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" name="actif" value="1" checked>
                    <label class="form-check-label">Actif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="ajouter" class="btn btn-success">Ajouter</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
