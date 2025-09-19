<?php
session_start();
include('auth_session.php');

// Connexion à la BDD
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '', [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Traitement publication
$success_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publier'])) {
  $titre = trim($_POST['titre']);
  $contenu = trim($_POST['contenu']);
  if (!empty($titre) && !empty($contenu)) {
    $auteur = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
    $stmt = $bdd->prepare("INSERT INTO publication (titre, contenu, auteur) VALUES (?, ?, ?)");
    $stmt->execute([$titre, $contenu, $auteur]);
    $success_msg = "✅ Publication enregistrée.";
  }
}

// Suppression
if (isset($_GET['delete'])) {
  $bdd->prepare("DELETE FROM publication WHERE id_publication = ?")->execute([$_GET['delete']]);
  header("Location: publication.php");
  exit;
}

// Récupération des publications
$publications = $bdd->query("SELECT * FROM publication ORDER BY date_publication DESC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISUKU - Apropos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    
<style>
  .publication-item:hover {
    background-color: #f8f9fa;
    border-left: 4px solid #0d6efd;
    padding-left: 1rem;
    transition: all 0.3s ease;
    cursor: default;
  }
  .card-header {
    letter-spacing: 1.2px;
  }
</style>


    <?php include('header.php'); ?>
</head>
<body>
<section class="container my-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold">Qui sommes-nous ?</h2>
    <p class="text-muted">Isuku Co. — L'innovation verte pour un avenir durable</p>
  </div>

  <div class="row align-items-center mb-5">
    <div class="col-md-6">
      <img src="./images/pexels-fatih-guney-337108406-18794597.jpg" alt="Recyclage" class="img-fluid rounded shadow">
    </div>
    <div class="col-md-6">
      <h3 class="fw-semibold">Notre mission</h3>
      <p>
        Chez <strong>Isuku Co.</strong>, nous croyons fermement que chaque déchet est une opportunité. Notre mission est simple mais ambitieuse : collecter, transformer et valoriser les déchets ménagers, plastiques, électroniques et organiques pour créer un avenir plus propre, plus sûr et plus prospère pour tous.
      </p>
      <p>
        Grâce à un réseau communautaire dynamique et des technologies écologiques innovantes, nous donnons une seconde vie à ce qui était autrefois ignoré.
      </p>
    </div>
  </div>

  <div class="row align-items-center mb-5 flex-md-row-reverse">
    <div class="col-md-6">
      <img src="./images/pic9.jpg" alt="Impact environnemental" class="img-fluid rounded shadow">
    </div>
    <div class="col-md-6">
      <h3 class="fw-semibold">Un impact réel et mesurable</h3>
      <p>
        Nous avons déjà permis la collecte de plusieurs tonnes de déchets, formé des centaines de jeunes à l'entrepreneuriat vert et soutenu des coopératives locales dans la création de produits recyclés à forte valeur ajoutée.
      </p>
      <ul>
        <li>🌍 Réduction de la pollution dans les quartiers urbains</li>
        <li>♻️ Création de matériaux de construction éco-responsables</li>
        <li>👩🏽‍🔧 Création d’emplois pour les jeunes et les femmes</li>
      </ul>
    </div>
  </div>

  <div class="bg-light p-5 rounded shadow-sm mb-5">
    <h3 class="text-center fw-bold">Pourquoi investir dans Isuku Co. ?</h3>
    <p class="lead text-center">
      Investir dans Isuku Co. ce n’est pas seulement soutenir une entreprise – c’est participer à un mouvement mondial pour la protection de l’environnement et le développement durable.
    </p>
    <div class="row mt-4">
      <div class="col-md-4 text-center">
        <i class="fas fa-chart-line fa-2x text-primary mb-3"></i>
        <h5>Croissance rapide</h5>
        <p>Un modèle économique éprouvé et évolutif dans une niche en pleine expansion.</p>
      </div>
      <div class="col-md-4 text-center">
        <i class="fas fa-hands-helping fa-2x text-success mb-3"></i>
        <h5>Impact social</h5>
        <p>Des centaines de vies changées grâce à l’éducation, la sensibilisation et l’emploi vert.</p>
      </div>
      <div class="col-md-4 text-center">
        <i class="fas fa-leaf fa-2x text-warning mb-3"></i>
        <h5>Engagement écologique</h5>
        <p>Un acteur fort de l’économie circulaire et de la lutte contre le changement climatique.</p>
      </div>
    </div>
  </div>
 <div class="card mb-4 shadow-sm" style="max-width: 1000px; margin: auto;">
  <div class="card-header bg-primary text-white fs-5 fw-semibold">
    🗞️ Dernières publications
  </div>
  <div class="card-body bg-white">
    <?php if (empty($publications)) : ?>
      <div class="text-center text-muted fst-italic">Aucune publication disponible pour le moment.</div>
    <?php else : ?>
      <?php foreach ($publications as $pub) : ?>
        <div class="publication-item mb-4 pb-3 border-bottom">
          <h5 class="text-primary fw-bold mb-2"><?= htmlspecialchars($pub['titre']) ?></h5>
          <p class="text-secondary mb-2" style="white-space: pre-line;"><?= nl2br(htmlspecialchars($pub['contenu'])) ?></p>
          <small class="text-muted">
            Publié le <?= date('d/m/Y à H:i', strtotime($pub['date_publication'])) ?> par <strong><?= htmlspecialchars($pub['auteur']) ?></strong>
          </small>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
  <div class="text-center mt-5">
    <a href="contact.php" class="btn btn-lg btn-success px-4 py-2">
      🌱 Rejoignez-nous dans la révolution verte
    </a>
  </div>
</section>
<?php include 'footer.php'; ?>
</body>
</html>
