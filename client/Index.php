<?php
session_start();

try {
    $bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8', 'root', '');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

$sql = "SELECT nom_complet, message, date_commentaire 
        FROM commentaire
        WHERE statut = 'valide' 
        ORDER BY date_commentaire DESC 
        LIMIT 3";

$stmt = $bdd->query($sql);
$commentaires = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISUKU - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <?php include('header.php'); ?>
    <style>
  /* Adaptation couleurs sur la page */
  .text-primary {
    color: #006666 !important; /* Bleu pétrole */
  }
  .btn-primary {
    background-color: #d35400 !important; /* orange brûlé */
    border-color: #d35400 !important;
  }
  .btn-primary:hover {
    background-color: #e67e22 !important; /* orange plus clair */
    border-color: #e67e22 !important;
  }
  h2, h3 {
    color: #357a38; /* vert olive */
  }
  body {
    background-color: #f0e6d2; /* beige sable doux */
    color: #2c3e50; /* texte sombre pour lisibilité */
  }
  .bg-light {
    background-color: #e9f5f2 !important; /* vert très clair pour les témoignages */
  }
  .fas.fa-check-circle.text-success {
    color: #357a38 !important; /* harmoniser vert */
  }
</style>
</head>
<body>
<!-- index.php -->

<div class="container mt-5">

  <!-- Section Hero -->
  <section class="text-center mb-5">
    <h1 class="display-4 fw-bold text-primary">Bienvenue chez Isuku Co.</h1>
    <p class="lead mx-auto" style="max-width: 700px;">
      Ensemble, transformons les déchets en opportunités pour un avenir durable.  
      Grâce à notre service innovant de collecte et recyclage, chaque geste compte pour la planète.
    </p>
    <a href="inscription.php" class="btn btn-primary btn-lg mt-3 animate__animated animate__pulse">
      Rejoignez-nous dès aujourd’hui !
    </a>
  </section>

  <!-- Section image + texte -->
  <section class="row align-items-center mb-5">
    <div class="col-md-6">
      <img src="./images/camion4.jpg" 
           alt="Collecte de déchets" class="img-fluid rounded shadow" />
    </div>
    <div class="col-md-6">
      <h2 class="mb-3">Pourquoi recycler avec Isuku Co. ?</h2>
      <p>
        Chaque jour, des tonnes de déchets sont produits. Pourtant, beaucoup peuvent être réutilisés pour créer de nouvelles ressources.  
        Chez Isuku Co., nous facilitons cette transition écologique grâce à un service de collecte fiable, une transformation responsable, et une sensibilisation forte auprès des communautés.
      </p>
      <p>
        Recycler, c’est préserver nos forêts, économiser l’eau, réduire la pollution et protéger la biodiversité.  
        Ensemble, construisons un monde plus propre et plus sain pour nous et nos enfants.
      </p>
    </div>
  </section>

  <!-- Section avantages -->
  <section class="text-center mb-5">
    <h3>Nos atouts pour un impact durable</h3>
    <div class="row justify-content-center mt-4">
      <div class="col-md-4 mb-3">
        <i class="fas fa-truck fa-3x text-success mb-3"></i>
        <h5>Collecte régulière</h5>
        <p>Un service fiable, ponctuel, adapté à vos besoins pour une gestion optimale des déchets.</p>
      </div>
      <div class="col-md-4 mb-3">
        <i class="fas fa-leaf fa-3x text-success mb-3"></i>
        <h5>Recyclage écologique</h5>
        <p>Nos procédés respectent la nature et favorisent la transformation en matériaux utiles.</p>
      </div>
      <div class="col-md-4 mb-3">
        <i class="fas fa-users fa-3x text-success mb-3"></i>
        <h5>Communauté engagée</h5>
        <p>Accompagnement et sensibilisation pour que chacun devienne acteur du changement.</p>
      </div>
    </div>
  </section>

  <!-- Section témoignages -->
  <section class="mb-5">
    <h3 class="text-center mb-4">Ils nous font confiance</h3>
    <div class="row justify-content-center">

<section class="mb-5">
  <h3 class="text-center mb-4">Avis récents de notre communauté</h3>
  <div class="row justify-content-center">
    <?php if (count($commentaires) > 0): ?>
      <?php foreach ($commentaires as $com): ?>
        <div class="col-md-4 mb-4">
          <div class="p-4 bg-light rounded shadow-sm">
            <p><em>"<?= htmlspecialchars($com['message']) ?>"</em></p>
            <h6 class="text-end">- <?= htmlspecialchars($com['nom_complet']) ?>, le <?= date('d/m/Y', strtotime($com['date_commentaire'])) ?></h6>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center">Aucun commentaire disponible pour le moment.</p>
    <?php endif; ?>
  </div>
</section>

    </div>
  </section>

  <!-- Section call to action finale -->
  <section class="text-center mb-5">
    <h2>Prêt à agir pour la planète ?</h2>
    <p class="mb-4">Inscrivez-vous dès maintenant et rejoignez la communauté Isuku Co. pour un environnement sain et durable.</p>
    <a href="inscription.php" class="btn btn-success btn-lg animate__animated animate__heartBeat" style="background-color:#357a38; border:none;">
      Je m’inscris !
    </a>
  </section>

</div>
</body>

<?php include 'footer.php'; ?>
</html>
