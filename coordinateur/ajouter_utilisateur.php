<?php
session_start();
include('auth_session.php');
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et sécuriser les données du formulaire
    $id_utilisateur = trim($_POST['id_utilisateur']);
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

    // Validation simple
    if (!$id_utilisateur || !$nom || !$prenom || !$email || !$role || !$password) {
        $message = "Veuillez remplir tous les champs obligatoires (*)";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide";
    } else {
        // Hachage du mot de passe
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insertion dans la BDD
        $stmt = $bdd->prepare("INSERT INTO utilisateur 
            (id_utilisateur, nom, prenom, genre, pays, province, commune, quartier, avenue, telephone, email, role, password, date_inscription)
            VALUES (:id_utilisateur, :nom, :prenom, :genre, :pays, :province, :commune, :quartier, :avenue, :telephone, :email, :role, :password, CURDATE())");

        try {
            $stmt->execute([
                ':id_utilisateur' => $id_utilisateur,
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
                ':password' => $password_hash
            ]);
            $message = "Utilisateur ajouté avec succès !";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $message = "Erreur : un utilisateur avec cet ID ou email existe déjà.";
            } else {
                $message = "Erreur lors de l'ajout : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Ajouter un utilisateur</title>
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
    <h2>Ajouter un utilisateur</h2>
    <?php if ($message): ?>
      <div class="alert <?php echo strpos($message, 'succès') !== false ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="id_utilisateur" class="form-label">ID Utilisateur <span class="required">*</span></label>
        <input type="text" name="id_utilisateur" id="id_utilisateur" class="form-control" required value="<?php echo htmlspecialchars($_POST['id_utilisateur'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="prenom" class="form-label">Prénom <span class="required">*</span></label>
        <input type="text" name="prenom" id="prenom" class="form-control" required value="<?php echo htmlspecialchars($_POST['prenom'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="nom" class="form-label">Nom <span class="required">*</span></label>
        <input type="text" name="nom" id="nom" class="form-control" required value="<?php echo htmlspecialchars($_POST['nom'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Genre</label><br />
        <select name="genre" class="form-select" >
          <option value="">-- Sélectionner --</option>
          <option value="M" <?php if (($_POST['genre'] ?? '') === 'M') echo 'selected'; ?>>Masculin</option>
          <option value="F" <?php if (($_POST['genre'] ?? '') === 'F') echo 'selected'; ?>>Féminin</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="pays" class="form-label">Pays</label>
        <input type="text" name="pays" id="pays" class="form-control" value="<?php echo htmlspecialchars($_POST['pays'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="province" class="form-label">Province</label>
        <input type="text" name="province" id="province" class="form-control" value="<?php echo htmlspecialchars($_POST['province'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="commune" class="form-label">Commune</label>
        <input type="text" name="commune" id="commune" class="form-control" value="<?php echo htmlspecialchars($_POST['commune'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="quartier" class="form-label">Quartier</label>
        <input type="text" name="quartier" id="quartier" class="form-control" value="<?php echo htmlspecialchars($_POST['quartier'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="avenue" class="form-label">Avenue</label>
        <input type="text" name="avenue" id="avenue" class="form-control" value="<?php echo htmlspecialchars($_POST['avenue'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="telephone" class="form-label">Téléphone</label>
        <input type="text" name="telephone" id="telephone" class="form-control" value="<?php echo htmlspecialchars($_POST['telephone'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email <span class="required">*</span></label>
        <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">Rôle <span class="required">*</span></label>
        <select name="role" id="role" class="form-select" required>
          <option value="">-- Sélectionner --</option>
          <option value="client" <?php if (($_POST['role'] ?? '') === 'client') echo 'selected'; ?>>Client</option>
          <option value="coordinateur" <?php if (($_POST['role'] ?? '') === 'coordinateur') echo 'selected'; ?>>Coordinateur</option>
          <option value="agent" <?php if (($_POST['role'] ?? '') === 'agent') echo 'selected'; ?>>Agent</option>
          <option value="chauffeur" <?php if (($_POST['role'] ?? '') === 'chauffeur') echo 'selected'; ?>>Chauffeur</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Mot de passe <span class="required">*</span></label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary">Ajouter</button>
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
