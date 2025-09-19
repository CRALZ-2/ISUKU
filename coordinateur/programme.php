<?php
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8', 'root', '');
$req = $bdd->query("SELECT * FROM zone");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>ISUKU - Programme</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f5;
      font-family: 'Segoe UI', sans-serif;
    }

    .section {
      background-color: #fff;
      padding: 40px;
      border-radius: 10px;
      margin: 40px auto;
      max-width: 1000px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .section h1, .section h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 20px;
    }

    .section p, .section li {
      font-size: 1.1rem;
      line-height: 1.8;
      color: #333;
    }

    .tarif-box {
      background-color: #1e4620;
      color: #fff;
      padding: 20px;
      border-left: 5px solid #66bb6a;
      margin: 30px 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
    }

    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #43a047;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f1f1f1;
    }
  </style>
  <?php include('header.php'); ?>
</head>
<body>
  <main>
    <section class="section">
      <h1>Programme de Collecte Mensuel - ISUKU</h1>
      <p>
        Dans un monde confronté à une croissance urbaine rapide et à une production croissante de déchets,
        ISUKU propose une solution numérique innovante pour transformer la gestion des déchets. Notre programme vise
        à automatiser les processus, faciliter les paiements, optimiser la logistique et offrir une expérience utilisateur transparente
        et adaptée à différents contextes locaux, que ce soit dans les grandes villes ou les zones périurbaines à travers le monde.
      </p>
      <h2><i class="fas fa-calendar-alt"></i> Notre Approche Structurée</h2>
      <p>
        Chaque utilisateur peut s’enregistrer en ligne, être géolocalisé automatiquement et associé à une zone tarifaire
        adaptée à sa localisation. Un calendrier interactif permet de suivre les dates de passage de nos équipes, assurant
        une meilleure planification et visibilité du service.
      </p>
      <div class="tarif-box">
        <i class="fas fa-coins"></i> <strong>Tarification Flexible :</strong><br>
        Paiement mensuel, trimestriel ou annuel possible. Chaque transaction génère un reçu numérique sécurisé
        disponible dans votre espace personnel.
      </div>
      <h2><i class="fas fa-truck-moving"></i> Logistique Optimisée</h2>
      <p>
        Nos opérations intègrent un système intelligent de gestion de flotte. Chaque tournée est affectée à un véhicule,
        un agent et un chauffeur. Tous les incidents ou anomalies peuvent être signalés en temps réel, permettant une
        amélioration continue de la qualité du service.
      </p>
      

      <h2><i class="fas fa-map-marked-alt"></i> Zones Desservies</h2>
      <p>Voici les quartiers actuellement inclus dans notre programme :</p>
      <table>
        <thead>
          <tr>
            <th>Quartier</th>
            <th>Commune</th>
            <th>Province</th>
            <th>Tarif</th>
          </tr>
        </thead>
        <tbody>
          <?php while($zone = $req->fetch()) { ?>
          <tr>
            <td><?= htmlspecialchars($zone['nom_quartier']) ?></td>
            <td><?= htmlspecialchars($zone['commune']) ?></td>
            <td><?= htmlspecialchars($zone['province']) ?></td>
            <td><?= htmlspecialchars($zone['tarif_mensuel']) ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </section>
  </main>
  <?php include('footer.php'); ?>
</body>
</html>
