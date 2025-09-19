<?php 
session_start(); 
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8', 'root', ''); 

// Sécurité simple: vérifier si utilisateur connecté en coordinateur (à adapter selon ton système)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinateur') {
    header('Location: login.php');
    exit;
}

$message = '';
$roles = ['client', 'agent', 'chauffeur', 'coordinateur'];

// Suppression utilisateur ciblée
if(isset($_GET['del']))
{
    $recpdel=$_GET['del'];
    $deluti = $bdd->query("DELETE FROM utilisateur WHERE id_utilisateur = '$recpdel'");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();

    
}



if (isset($_POST['delete_selected']) && !empty($_POST['selected_ids'])) {
    $ids = $_POST['selected_ids'];
    $placeholders = rtrim(str_repeat('?,', count($ids)), ',');
    $stmt = $bdd->prepare("DELETE FROM utilisateur WHERE id_utilisateur IN ($placeholders)");
    $stmt->execute($ids);
    $_SESSION['message'] = count($ids) . " utilisateur(s) supprimé(s).";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Récupérer utilisateurs par rôle
$usersByRole = [];
foreach ($roles as $role) {
    $stmt = $bdd->prepare("SELECT * FROM utilisateur WHERE role = ? ORDER BY nom, prenom");
    $stmt->execute([$role]);
    $usersByRole[$role] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Gestion des Utilisateurs - Dashboard Coordinateur</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    font-family: 'Open Sans', sans-serif;
    background-color: #f4f6f9;
    margin: 0;
  }
  .dashboard {
    display: grid;
    grid-template-columns: 250px 1fr;
    height: 100vh;
  }
  main.main-content {
    flex-grow: 1;
    padding: 30px;
    background-color: #ecf0f1;
    overflow-y: auto;
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
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar.php'; ?>

  <main class="main-content">
    <section class="content mt-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <?php if (!empty($_SESSION['message'])): ?>
           <div class="alert alert-info text-center">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
      </div>
    <?php endif; ?>

        <h3>Gestion des Utilisateurs</h3>
        <a href="ajouter_utilisateur.php" class="btn btn-success">+ Ajouter un utilisateur</a>
      </div>

  <!-- Onglets Bootstrap -->
  <ul class="nav nav-tabs" id="roleTabs" role="tablist">
    <?php foreach ($roles as $index => $role): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" id="tab-<?php echo $role; ?>" data-bs-toggle="tab" data-bs-target="#content-<?php echo $role; ?>" type="button" role="tab" aria-controls="content-<?php echo $role; ?>" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
          <?php echo ucfirst($role) . 's'; ?>
        </button>
      </li>
    <?php endforeach; ?>
  </ul>

      <div class="tab-content mt-3" id="roleTabsContent">
        <?php foreach ($roles as $index => $role): ?>
          <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" id="content-<?php echo $role; ?>" role="tabpanel" aria-labelledby="tab-<?php echo $role; ?>">
            <?php if (empty($usersByRole[$role])): ?>
              <p>Aucun <?= htmlspecialchars($role) ?> trouvé.</p>
            <?php else: ?>
              <form method="post" action="">
 <table class="table table-striped table-bordered align-middle">
                <thead>
                  <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Genre</th>
                    <th>Pays</th>
                    <th>Province</th>
                    <th>Commune</th>
                    <th>Quartier</th>
                    <th>Avenue</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($usersByRole[$role] as $user): ?>
                    <tr>
                      <td><input type="checkbox" name="selected_ids[]" value="<?= htmlspecialchars($user['id_utilisateur']) ?>"></td>
                      <td><?= htmlspecialchars($user['id_utilisateur']) ?></td>
                      <td><?= htmlspecialchars($user['nom']) ?></td>
                      <td><?= htmlspecialchars($user['prenom']) ?></td>
                      <td><?= htmlspecialchars($user['genre']) ?></td>
                      <td><?= htmlspecialchars($user['pays']) ?></td>
                      <td><?= htmlspecialchars($user['province']) ?></td>
                      <td><?= htmlspecialchars($user['commune']) ?></td>
                      <td><?= htmlspecialchars($user['quartier']) ?></td>
                      <td><?= htmlspecialchars($user['avenue']) ?></td>
                      <td><?= htmlspecialchars($user['telephone']) ?></td>
                      <td><?= htmlspecialchars($user['email']) ?></td>
                      <td>
                          <a href="modifier_utilisateur.php?id=<?= urlencode($user['id_utilisateur']); ?>" class="btn btn-sm btn-primary" title="Modifier">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                              <path d="M12.854.146a.5.5 0 0 1 .11.638l-10 12a.5.5 0 0 1-.775.094l-3-3a.5.5 0 0 1 .094-.775l12-10a.5.5 0 0 1 .571.043zM11.207 3.5L4 10.707V12h1.293L12.5 5.793 11.207 3.5z"/>
                            </svg>
                          </a>
                          <a href="gestion_utilisateur.php?del=<?php echo htmlspecialchars($user['id_utilisateur']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet utilisateur ?')" title="Supprimer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                              <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm-5-1A1.5 1.5 0 0 1 5 3h6a1.5 1.5 0 0 1 1.5 1.5V5h-9v-.5zM14.5 5a.5.5 0 0 1 .5.5v.5a.5.5 0 0 1-.5.5H1.5a.5.5 0 0 1-.5-.5v-.5a.5.5 0 0 1 .5-.5h13z"/>
                            </svg>
                          </a>
                       </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <button type="submit" name="delete_selected" class="btn btn-danger mt-2" onclick="return confirm('Confirmer la suppression multiple ?')">
                Supprimer la sélection
              </button>
            </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>
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
<script>
document.getElementById('selectAll').addEventListener('change', function() {
  const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
  for (let box of checkboxes) {
    box.checked = this.checked;
  }
});
</script>

</body>
</html>
