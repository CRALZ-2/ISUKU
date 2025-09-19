<?php
session_start();
include('auth_session.php');

$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$erreur_password = "";
$succes_password = "";
$message_success = "";

// Sécurité : client uniquement
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}

$id_client = $_SESSION['id_utilisateur'];
$prenom = $_SESSION['prenom'] ?? 'Client';
$nom = $_SESSION['nom'] ?? '';

// Statistiques
$stmt = $bdd->prepare("SELECT COUNT(*) FROM contrat WHERE id_utilisateur = ? AND statut = 'actif'");
$stmt->execute([$id_client]);
$totalContrats = $stmt->fetchColumn();

$stmt = $bdd->prepare("SELECT COUNT(*) FROM facturation f INNER JOIN contrat c ON f.id_contrat=c.id_contrat WHERE c.id_utilisateur = ?");
$stmt->execute([$id_client]);
$totalFactures = $stmt->fetchColumn();

$stmt = $bdd->prepare("SELECT COUNT(*) FROM reclamation WHERE id_client = ?");
$stmt->execute([$id_client]);
$totalReclamations = $stmt->fetchColumn();


//modification client

$stmt = $bdd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$id_client]);
$clientData = $stmt->fetch(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_profil'])) {
    $ouvrirModal = true; // au début

    // 1. Vérifier si le profil a été modifié
    $profilModifie = false;
    $champs = ['nom', 'prenom', 'genre', 'telephone', 'email', 'pays', 'province', 'commune', 'quartier', 'avenue'];

    foreach ($champs as $champ) {
        if ($_POST[$champ] !== $clientData[$champ]) {
            $profilModifie = true;
            break;
        }
    }

// 2. Si profil modifié, faire la mise à jour
    if ($profilModifie) {
        $update = $bdd->prepare("
            UPDATE utilisateur SET
                nom = ?, prenom = ?, genre = ?, telephone = ?, email = ?,
                pays = ?, province = ?, commune = ?, quartier = ?, avenue = ?
            WHERE id_utilisateur = ?
        ");
        $update->execute([
            $_POST['nom'], $_POST['prenom'], $_POST['genre'], $_POST['telephone'], $_POST['email'],
            $_POST['pays'], $_POST['province'], $_POST['commune'], $_POST['quartier'], $_POST['avenue'],
            $id_client
        ]);
        $message_success = "✅ Profil mis à jour avec succès.";
        $ouvrirModal = true;
    }

    if (!empty($_POST['password']) && !empty($_POST['confirmation'])) {
    $password = $_POST['password'];
    $confirmation = $_POST['confirmation'];

    // Vérification sécurité
    if ($password === $confirmation) {
        if (strlen($password) >= 8 &&
            preg_match('/[A-Z]/', $password) &&
            preg_match('/[a-z]/', $password) &&
            preg_match('/[0-9]/', $password) &&
            preg_match('/[\W]/', $password)) {

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $updatePwd = $bdd->prepare("UPDATE utilisateur SET password = ? WHERE id_utilisateur = ?");
            $updatePwd->execute([$hash, $id_client]);
            $succes_password = "🔐 Mot de passe mis à jour avec succès.";
            $ouvrirModal = true;
        } else {
            $erreur_password = "❌ Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
            $ouvrirModal = true;
        }
    } else {
        $erreur_password = "❌ Les mots de passe ne correspondent pas.";
        $ouvrirModal = true;
    }
}



    // Rafraîchir les données après modification
    $stmt = $bdd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$id_client]);
    $clientData = $stmt->fetch(PDO::FETCH_ASSOC);

}

// Dernière annonce
$annonce = $bdd->query("SELECT message FROM annonce_collecte ORDER BY id_annonce DESC LIMIT 1");
$dernierMessage = $annonce->fetchColumn();

// Moyens de paiement actifs
$moyens = $bdd->query("SELECT * FROM moyen_paiement WHERE actif = 1 ORDER BY nom_moyen")->fetchAll(PDO::FETCH_ASSOC);

// Zones de collecte
$zones = $bdd->query("SELECT * FROM zone ORDER BY nom_quartier")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Client - Isuku Co.</title>

  <!-- Bootstrap & Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f5f5f5;
      font-family: 'Segoe UI', sans-serif;
      padding-top: 90px;
    }
    .card {
      border-left: 5px solid #0d53b1;
      transition: all 0.3s;
    }
    .card:hover {
      transform: scale(1.02);
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }
    .quick-access a {
      width: 100%;
      margin-bottom: 10px;
    }
    .user-initial {
      background-color: #0d9855;
      color: white;
      border-radius: 50%;
      width: 45px;
      height: 45px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: bold;
      font-size: 1rem;
    }

    /* NAVBAR */
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
    .user-initial {
  background-color: #0d9855;
  color: white;
  border-radius: 50%;
  width: 45px;
  height: 45px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
  font-size: 1rem;
  text-decoration:none;
  cursor: pointer; /* indique que c’est cliquable */
  border: none; /* pas de bordure bouton */
}

/*modal style*/
.modal-content {
  border-radius: 15px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
  border: none;
}

.modal-header {
  background-color: #0d53b1;
  color: white;
  border-bottom: 1px solid #dee2e6;
}

.modal-header .modal-title {
  font-weight: bold;
}

.modal-body label {
  font-weight: 600;
  color: #444;
}

.modal-body input,
.modal-body select {
  border-radius: 8px;
  border: 1px solid #ced4da;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.modal-body input:focus,
.modal-body select:focus {
  border-color: #0d9855;
  box-shadow: 0 0 0 0.2rem rgba(13, 152, 85, 0.25);
}

.modal-footer .btn-success {
  background-color: #0d9855;
  border: none;
}

.modal-footer .btn-success:hover {
  background-color: #0b7f45;
}

.modal-footer .btn-secondary {
  background-color: #6c757d;
  border: none;
}

.modal-footer .btn-secondary:hover {
  background-color: #5a6268;
}
.count-display {
    margin-top: 5px;
    font-weight: bold;
    font-size: 2rem;
    color: #333;
  }

  </style>
</head>
<body>

<!-- HEADER NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top shadow animate__animated animate__fadeInDown">
  <div class="container">
    <img src="./images/logo1.png" alt="Logo Recyclage" style="height: 40px;  margin-right: 2px;">
        <h5 class="mb-0"><strong style="font-weight: 700; font-size: 20px; color: #0d53b1;">ISUKU</strong>
        <span style="font-weight: 400; font-size: 20px; color: #0d9855;">CO.</span></h5>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item mx-2">
          <a class="nav-link" href="dashboard_client.php"><i class="fas fa-home"></i> Accueil</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="contrat_client.php"><i class="fas fa-file-contract"></i> Contrats</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="mes_factures.php"><i class="fas fa-calendar-plus"></i> Factures</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="mes_reclamations.php"><i class="fas fa-comment-dots"></i> Réclamations</a>
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

<!-- CONTENU -->
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Bienvenue <?php echo $prenom . ' ' . $nom; ?></h3>
    <!-- Ajoute l'attribut data-bs-toggle et data-bs-target pour Bootstrap -->
<button type="button" class="user-initial btn btn-link p-0 border-0" data-bs-toggle="modal" data-bs-target="#profilModal">
  <?php echo strtoupper(substr($prenom, 0, 1)); ?>
</button>
  </div>

  <div class="row">
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Contrats actifs</h5>
          <div class="count-display"><?php echo $totalContrats; ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Factures reçus</h5>
          <div class="count-display"><?php echo $totalFactures; ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Réclamations envoyées</h5>
           <div class="count-display"><?php echo $totalReclamations; ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-5">
    <h4>Accès rapide</h4>
    <div class="row quick-access mt-3">
      <div class="col-md-6 col-lg-3">
        <a href="contrat_client.php" class="btn btn-outline-primary">📄 Mes contrats</a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="mes_factures.php" class="btn btn-outline-success">🧧 Factures</a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="deposer_reclamation.php" class="btn btn-outline-warning">📝 Faire une réclamation</a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="mes_reclamations.php" class="btn btn-outline-info">📬 Voir mes réclamations</a>
      </div>
    </div>
  </div>
<div class="container mt-5">
  <div class="row">

    <!-- Moyens de paiement -->
    <div class="col-md-6">
      <div class="card border-info shadow-sm mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0">💳 Moyens de Paiement Disponibles</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($moyens)): ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($moyens as $m): ?>
              <li class="list-group-item">
                <strong>Type :</strong> <?= htmlspecialchars($m['type']) ?><br>
                <strong>Nom :</strong> <?= htmlspecialchars($m['nom_moyen']) ?><br>
                <?php if ($m['type'] === 'banque'): ?>
                  <strong>Banque :</strong> <?= htmlspecialchars($m['nom_banque']) ?><br>
                  <strong>Nom du compte :</strong> <?= htmlspecialchars($m['nom_compte']) ?><br>
                  <strong>N° Compte :</strong> <?= htmlspecialchars($m['numero_compte']) ?><br>
                <?php elseif ($m['type'] === 'mobile_money'): ?>
                  <strong>Mobile Money :</strong> <?= htmlspecialchars($m['nom_moyen']) ?><br>
                  <strong>Numéro :</strong> <?= htmlspecialchars($m['numero_compte']) ?><br>
                <?php endif; ?>
                <?php if (!empty($m['description'])): ?>
                  <strong>Note :</strong> <?= nl2br(htmlspecialchars($m['description'])) ?>
                <?php endif; ?>
              </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted">Aucun moyen de paiement disponible.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Zones de collecte -->
    <div class="col-md-6">
      <div class="card border-success shadow-sm mb-4">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">🗺️ Zones de Collecte</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($zones)): ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($zones as $z): ?>
              <li class="list-group-item">
                <strong>Quartier :</strong> <?= htmlspecialchars($z['nom_quartier']) ?><br>
                <strong>Commune :</strong> <?= htmlspecialchars($z['commune']) ?><br>
                <strong>Province :</strong> <?= htmlspecialchars($z['province']) ?><br>
                <strong>Tarif mensuel :</strong> <?= number_format($z['tarif_mensuel'], 2, ',', ' ') ?> FBu
              </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted">Aucune zone de collecte enregistrée.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

  <?php if ($dernierMessage): ?>
    <div class="alert alert-info mt-4">
      📢 <?php echo htmlspecialchars($dernierMessage); ?>
    </div>
  <?php endif; ?>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Modal Profil Utilisateur -->
<div class="modal fade" id="profilModal" tabindex="-1" aria-labelledby="profilModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profilModalLabel">Profil de <?php echo htmlspecialchars($prenom . ' ' . $nom); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <?php if (!empty($succes_password)): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $succes_password ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php elseif (!empty($erreur_password)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $erreur_password ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

      <form method="post">
        <div class="modal-body">
    <div class="row">
      <?php if (isset($message_success)): ?>
  <div class="alert alert-success w-100 text-center animate__animated animate__fadeInDown">
    <?php echo $message_success; ?>
  </div>
<?php endif; ?>

    <input type="hidden" name="modifier_profil" value="1">
    
    <div class="col-md-6 mb-3">
      <label><strong>Nom :</strong></label>
      <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($clientData['nom']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label><strong>Prénom :</strong></label>
      <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($clientData['prenom']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label><strong>Genre :</strong></label>
      <select name="genre" class="form-control" required>
        <option value="M" <?php if ($clientData['genre'] === 'M') echo 'selected'; ?>>Masculin</option>
        <option value="F" <?php if ($clientData['genre'] === 'F') echo 'selected'; ?>>Féminin</option>
      </select>
    </div>
    <div class="col-md-6 mb-3">
      <label><strong>Téléphone :</strong></label>
      <input type="text" name="telephone" class="form-control" value="<?php echo htmlspecialchars($clientData['telephone']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label><strong>Email :</strong></label>
      <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($clientData['email']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label><strong>Pays :</strong></label>
      <input type="text" name="pays" class="form-control" value="<?php echo htmlspecialchars($clientData['pays']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label><strong>Province :</strong></label>
      <input type="text" name="province" class="form-control" value="<?php echo htmlspecialchars($clientData['province']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label><strong>Commune :</strong></label>
      <input type="text" name="commune" class="form-control" value="<?php echo htmlspecialchars($clientData['commune']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label><strong>Quartier :</strong></label>
      <input type="text" name="quartier" class="form-control" value="<?php echo htmlspecialchars($clientData['quartier']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label><strong>Avenue :</strong></label>
      <input type="text" name="avenue" class="form-control" value="<?php echo htmlspecialchars($clientData['avenue']); ?>" required>
    </div>
    <div class="col-12 mt-3">
  <a href="#" class="text-primary" data-bs-toggle="collapse" data-bs-target="#changerMotDePasse" style="text-decoration:none;">🔐 Modifier mon mot de passe</a>
</div>

<!-- Formulaire de modification du mot de passe -->
<div id="changerMotDePasse" class="collapse mt-3">
  <div class="col-12 mb-2">
    <label><strong>Nouveau mot de passe :</strong></label>
    <input type="password" name="password" class="form-control">
  </div>
  <div class="col-12 mb-3">
    <label><strong>Confirmer le mot de passe :</strong></label>
    <input type="password" name="confirmation" class="form-control">
  </div>
</div>

  </div>
  <div class="modal-footer">
    <button type="submit" class="btn btn-success">💾 Enregistrer</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
  </div>
</div>
  </form>
    </div>
  </div>
</div>
<script>
  // Faire disparaître l'alerte après 2 secondes (2000 millisecondes)
  setTimeout(function() {
    const alert = document.getElementById('successAlert');
    if (alert) {
      alert.classList.add('animate__fadeOutUp');
      setTimeout(() => alert.remove(), 1000); // suppression après animation
    }
  }, 2000);
  setTimeout(function () {
  let alerts = document.querySelectorAll('.alert');
  alerts.forEach(function (alert) {
    alert.classList.add('fade');
    setTimeout(() => alert.remove(), 1000);
  });
}, 3000);

</script>
<?php if (!empty($ouvrirModal)): ?>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    var modal = new bootstrap.Modal(document.getElementById('profilModal'));
    modal.show();
  });
</script>
<?php endif; ?>
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
