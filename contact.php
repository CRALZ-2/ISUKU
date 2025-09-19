<?php
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '');
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message_envoye = false;
$erreur_envoi = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["Envoyer"])) {
    $nom_complet = htmlspecialchars(trim($_POST["nom_complet"]));
    $objet = htmlspecialchars(trim($_POST["objet"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    if (!empty($nom_complet) && !empty($objet) && !empty($message)) {
        try {
            $stmt = $bdd->prepare("INSERT INTO commentaire (nom_complet, objet, message) VALUES (?, ?, ?)");
            $stmt->execute([$nom_complet, $objet, $message]);
            $message_envoye = true;
        } catch (PDOException $e) {
            $erreur_envoi = true;
        }
    } else {
        $erreur_envoi = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>ISUKU - Contactez-nous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include('header.php'); ?>
</head>
<body class="bg-light">

<main class="container py-2">
    <div class="text-center mb-5">
        <h1 class="display-4">Contactez-nous</h1>
        <p class="lead">Merci de nous faire confiance. Nous répondrons rapidement à votre message.</p>
    </div> 

    <div class="row">
        <!-- Coordonnées -->
        <div class="col-md-5 mb-4">
            <img src="./images/logo1.png" alt="Contact" style="height: 40px; margin-right: 2px;">
            <h5>Besoin d’aide ?</h5>
            <p>Appelez-nous directement au :</p>
            <a href="tel:66261686" class="text-info fw-bold" style="text-decoration: none;">(+257) 61 00 00 00</a>
        </div>

        <!-- Formulaire -->
        <div class="col-md-7">
            <form method="POST" class="bg-white p-4 rounded shadow-sm">
                <h4 class="mb-4">Envoyez-nous un message</h4>
                <div class="mb-3">
                    <input type="text" name="nom_complet" class="form-control" placeholder="Nom complet *" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="objet" class="form-control" placeholder="Objet *" required>
                </div>
                <div class="mb-3">
                    <textarea name="message" rows="5" class="form-control" placeholder="Votre message *" required></textarea>
                </div>
                <button type="submit" name="Envoyer" class="btn btn-info w-100">Envoyer le message</button>
            </form>
        </div>
    </div>
</main>

<?php include('footer.php'); ?>
</body>
</html>
