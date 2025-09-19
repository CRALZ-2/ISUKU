<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '');

$message = '';

// Vérifier que l'ID utilisateur est présent dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID utilisateur manquant');
}

$id_utilisateur = $_GET['id'];

// Récupérer les données utilisateur pour pré-remplir le formulaire
$stmt = $bdd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$id_utilisateur]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Utilisateur introuvable');
}

// Traitement du formulaire en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $genre = $_POST['genre'] ?? '';
    $pays = trim($_POST['pays']);
    $province = trim($_POST['province']);
    $commune = trim($_POST['commune']);
    $quartier = trim($_POST['quartier']);
    $avenue = trim($_POST['avenue']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $actif = isset($_POST['actif']) ? (int)$_POST['actif'] : 1;


    // Validation simple
    if (!$nom || !$prenom || !$email || !$role) {
        $message = "Veuillez remplir tous les champs obligatoires (*)";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide";
    } else {
        try {
            // Vérifier si on change l'email et si celui-ci existe déjà pour un autre utilisateur
            $stmtEmail = $bdd->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ? AND id_utilisateur != ?");
            $stmtEmail->execute([$email, $id_utilisateur]);
            if ($stmtEmail->rowCount() > 0) {
                $message = "Cet email est déjà utilisé par un autre utilisateur.";
            } else {
                // Construire la requête de mise à jour
                if (!empty($password)) {
                    // On met à jour le mot de passe aussi (haché)
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE utilisateur SET nom=:nom, prenom=:prenom, genre=:genre, pays=:pays, province=:province, commune=:commune, quartier=:quartier, avenue=:avenue, telephone=:telephone, email=:email, role=:role, actif=:actif, password=:password WHERE id_utilisateur=:id_utilisateur";
                } else {
                    // Sinon on ne touche pas au mot de passe
                    $sql = "UPDATE utilisateur SET nom=:nom, prenom=:prenom, genre=:genre, pays=:pays, province=:province, commune=:commune, quartier=:quartier, avenue=:avenue, telephone=:telephone, email=:email, role=:role, actif=:actif WHERE id_utilisateur=:id_utilisateur";
                }

                $stmtUpdate = $bdd->prepare($sql);
                $params = [
                    ':nom' => $nom,
                    ':prenom' => $prenom,
                    ':genre' => $genre,
                    ':pays' => $pays,
                    ':province' => $province,
                    ':commune' => $commune,
                    ':quartier' => $quartier,
                    ':avenue' => $avenue,
                    ':telephone' => $telephone,
                    ':email' => $email,
                    ':role' => $role,
                    ':actif' => $actif,
                    ':id_utilisateur' => $id_utilisateur
                ];

                if (!empty($password)) {
                    $params[':password'] = $password_hash;
                }

                $stmtUpdate->execute($params);

                $message = "Utilisateur modifié avec succès !";

                // Mettre à jour la variable $user pour rafraîchir les données du formulaire
                $stmt = $bdd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
                $stmt->execute([$id_utilisateur]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $message = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Modifier utilisateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Open Sans', sans-serif;
      padding: 20px;
    }
    .container {
      max-width: 700px;
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .required {
      color: red;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Modifier utilisateur : <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h2>

    <?php if ($message): ?>
      <div class="alert <?php echo strpos($message, 'succès') !== false ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="id_utilisateur" class="form-label">ID Utilisateur</label>
        <input type="text" id="id_utilisateur" class="form-control" value="<?php echo htmlspecialchars($user['id_utilisateur']); ?>" disabled>
        <small class="form-text text-muted">L'ID utilisateur ne peut pas être modifié.</small>
      </div>
      <div class="mb-3">
        <label for="prenom" class="form-label">Prénom <span class="required">*</span></label>
        <input type="text" name="prenom" id="prenom" class="form-control" required value="<?php echo htmlspecialchars($user['prenom']); ?>">
      </div>
      <div class="mb-3">
        <label for="nom" class="form-label">Nom <span class="required">*</span></label>
        <input type="text" name="nom" id="nom" class="form-control" required value="<?php echo htmlspecialchars($user['nom']); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Genre</label><br />
        <select name="genre" class="form-select">
          <option value="">-- Sélectionner --</option>
          <option value="M" <?php if ($user['genre'] === 'M') echo 'selected'; ?>>Masculin</option>
          <option value="F" <?php if ($user['genre'] === 'F') echo 'selected'; ?>>Féminin</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="pays" class="form-label">Pays</label>
        <input type="text" name="pays" id="pays" class="form-control" value="<?php echo htmlspecialchars($user['pays']); ?>">
      </div>
      <div class="mb-3">
        <label for="province" class="form-label">Province</label>
        <input type="text" name="province" id="province" class="form-control" value="<?php echo htmlspecialchars($user['province']); ?>">
      </div>
      <div class="mb-3">
        <label for="commune" class="form-label">Commune</label>
        <input type="text" name="commune" id="commune" class="form-control" value="<?php echo htmlspecialchars($user['commune']); ?>">
      </div>
      <div class="mb-3">
        <label for="quartier" class="form-label">Quartier</label>
        <input type="text" name="quartier" id="quartier" class="form-control" value="<?php echo htmlspecialchars($user['quartier']); ?>">
      </div>
      <div class="mb-3">
        <label for="avenue" class="form-label">Avenue</label>
        <input type="text" name="avenue" id="avenue" class="form-control" value="<?php echo htmlspecialchars($user['avenue']); ?>">
      </div>
      <div class="mb-3">
        <label for="telephone" class="form-label">Téléphone</label>
        <input type="text" name="telephone" id="telephone" class="form-control" value="<?php echo htmlspecialchars($user['telephone']); ?>">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email <span class="required">*</span></label>
        <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">Rôle <span class="required">*</span></label>
        <select name="role" id="role" class="form-select" required>
          <option value="">-- Sélectionner --</option>
          <option value="client" <?php if ($user['role'] === 'client') echo 'selected'; ?>>Client</option>
          <option value="coordinateur" <?php if ($user['role'] === 'coordinateur') echo 'selected'; ?>>Coordinateur</option>
          <option value="agent" <?php if ($user['role'] === 'agent') echo 'selected'; ?>>Agent</option>
          <option value="chauffeur" <?php if ($user['role'] === 'chauffeur') echo 'selected'; ?>>Chauffeur</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="actif" class="form-label">Statut du compte</label>
        <select name="actif" id="actif" class="form-select" required>
          <option value="1" <?php if ($user['actif'] == 1) echo 'selected'; ?>>Actif</option>
          <option value="0" <?php if ($user['actif'] == 0) echo 'selected'; ?>>Désactivé</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="mot_de_passe" class="form-label">Mot de passe (laisser vide pour ne pas changer)</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Nouveau mot de passe">
      </div>
      

      <button type="submit" class="btn btn-primary">Modifier</button>
      <a href="gestion_utilisateur.php" class="btn btn-secondary">Retour</a>
    </form>
  </div>
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
