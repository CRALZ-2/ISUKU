<?php
session_start();

try {
    $bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["envoyer"])) {
    // Nettoyage et sécurisation des données
    $id_utilisateur = htmlspecialchars(trim($_POST["id_utilisateur"] ?? ""));
    $nom = htmlspecialchars(trim($_POST["nom"] ?? ""));
    $prenom = htmlspecialchars(trim($_POST["prenom"] ?? ""));
    $genre = htmlspecialchars(trim($_POST["genre"] ?? ""));
    $email = htmlspecialchars(trim($_POST["email"] ?? ""));
    $pays = htmlspecialchars(trim($_POST["pays"] ?? ""));
    $province = htmlspecialchars(trim($_POST["province"] ?? ""));
    $commune = htmlspecialchars(trim($_POST["commune"] ?? ""));
    $quartier = htmlspecialchars(trim($_POST["quartier"] ?? ""));
    $avenue = htmlspecialchars(trim($_POST["avenue"] ?? ""));
    $telephone = htmlspecialchars(trim($_POST["telephone"] ?? ""));
    $role = "client";

    // Validation simple côté serveur
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Veuillez saisir une adresse email valide.";
    } elseif (empty($id_utilisateur) || empty($nom) || empty($prenom) || empty($genre) || empty($pays) || empty($province) || empty($commune) || empty($quartier) || empty($avenue) || empty($telephone)) {
        $erreur = "Tous les champs sont obligatoires.";
    } else {
        // Vérification doublons
        $verif = $bdd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ? OR email = ?");
        $verif->execute([$id_utilisateur, $email]);

        if ($verif->rowCount() > 0) {
            $erreur = "Un utilisateur avec ce numéro ou cet email existe déjà.";
        } else {
            // Insertion en base
            $insert = $bdd->prepare("INSERT INTO utilisateur (
                id_utilisateur, nom, prenom, genre, pays, province, commune, quartier, avenue, telephone, email, role
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $insert->execute([
                $id_utilisateur, $nom, $prenom, $genre, $pays, $province,
                $commune, $quartier, $avenue, $telephone, $email, $role
            ]);

            $_SESSION["id_utilisateur"] = $id_utilisateur;
            header("Location: mot_de_passe.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISUKU - Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <?php include('header.php'); ?>
</head>
<body class="bg-light">

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6 bg-white rounded p-4 shadow">
            <h2 class="mb-4 text-center">Inscription sur notre site</h2>
            <?php if ($erreur): ?>
                <div class="alert alert-danger"><?= $erreur ?></div>
            <?php endif; ?>
            <form method="POST" novalidate>
                <div class="mb-3">
                    <input type="text" name="id_utilisateur" class="form-control" placeholder="Numéro de la Carte d'identité nationale *" required value="<?= htmlspecialchars($_POST['id_utilisateur'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="nom" class="form-control" placeholder="Nom *" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="prenom" class="form-control" placeholder="Prénom *" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Genre *</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="genre" value="M" id="genreM" <?= (($_POST['genre'] ?? '') === 'M') ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="genreM">Masculin</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="genre" value="F" id="genreF" <?= (($_POST['genre'] ?? '') === 'F') ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="genreF">Féminin</label>
                    </div>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email *" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="pays" class="form-control" placeholder="Pays *" required value="<?= htmlspecialchars($_POST['pays'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="province" class="form-control" placeholder="Province *" required value="<?= htmlspecialchars($_POST['province'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="commune" class="form-control" placeholder="Commune *" required value="<?= htmlspecialchars($_POST['commune'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="quartier" class="form-control" placeholder="Quartier *" required value="<?= htmlspecialchars($_POST['quartier'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="avenue" class="form-control" placeholder="Avenue *" value="<?= htmlspecialchars($_POST['avenue'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <input type="text" name="telephone" class="form-control" placeholder="Téléphone *" required value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                </div>
                <button type="submit" name="envoyer" class="btn btn-primary w-100">S'inscrire</button>
            </form>
        </div>
    </div>
</main>

<?php include('footer.php'); ?>
</body>
</html>
