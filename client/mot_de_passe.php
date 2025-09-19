<?php
session_start();

if (!isset($_SESSION["id_utilisateur"])) {
    header("Location: inscription.php");
    exit();
}

try {
    $bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$erreur = "";
$succes = "";

function motDePasseValide($mdp) {
    return strlen($mdp) >= 8 &&
           preg_match('/[A-Z]/', $mdp) &&
           preg_match('/[a-z]/', $mdp) &&
           preg_match('/[0-9]/', $mdp) &&
           preg_match('/[\W]/', $mdp); // Caractères spéciaux
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["envoyer"])) {
    $password = $_POST["password"] ?? "";
    $confirmation = $_POST["confirmation"] ?? "";

    if (empty($password) || empty($confirmation)) {
        $erreur = "Veuillez remplir tous les champs.";
    } elseif ($password !== $confirmation) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (!motDePasseValide($password)) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $bdd->prepare("UPDATE utilisateur SET password = ? WHERE id_utilisateur = ?");
        $update->execute([$hash, $_SESSION["id_utilisateur"]]);

        $succes = "Mot de passe enregistré avec succès.";
        header("refresh:2;url=login.php");
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Choix du mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <?php include('header.php'); ?>
</head>
<body class="bg-light">

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 bg-white rounded p-4 shadow">
            <h2 class="mb-4 text-center">Choisissez un mot de passe</h2>

            <?php if ($erreur): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
            <?php elseif ($succes): ?>
                <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Mot de passe *" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="confirmation" class="form-control" placeholder="Confirmez le mot de passe *" required>
                </div>
                <button type="submit" name="envoyer" class="btn btn-primary w-100">Enregistrer</button>
            </form>
        </div>
    </div>
</main>

<?php include('footer.php'); ?>
</body>
</html>
