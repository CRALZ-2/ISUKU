<?php
$bdd = new PDO('mysql:host=localhost;dbname=isukuco;charset=utf8mb4', 'root', '');
$id = $_GET['id_facture'] ?? null;

if (!$id) {
    exit("Aucune facture spécifiée.");
}

$stmt = $bdd->prepare("SELECT f.*, u.nom AS nom_client, u.prenom, m.nom_moyen, a.message 
                       FROM facturation f 
                       JOIN contrat c ON f.id_contrat = c.id_contrat 
                       JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                       JOIN moyen_paiement m ON f.id_moyen = m.id_moyen 
                       JOIN annonce_collecte a ON f.id_annonce = a.id_annonce 
                       WHERE f.id_facture = ?");
$stmt->execute([$id]);
$facture = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$facture) {
    exit("Facture introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Facture à imprimer</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f9f9f9;
      margin: 0;
      padding: 0;
    }

    .facture-container {
      max-width: 800px;
      margin: 50px auto;
      background: #fff;
      border: 1px solid #ddd;
      padding: 40px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .header {
      text-align: center;
      border-bottom: 2px solid #0d53b1;
      padding-bottom: 10px;
      margin-bottom: 30px;
    }

    .header h2 {
      margin: 0;
      color: #0d53b1;
    }

    .section {
      margin-bottom: 20px;
    }

    .section h4 {
      margin-bottom: 10px;
      color: #333;
      border-bottom: 1px solid #ccc;
      padding-bottom: 5px;
    }

    .info-item {
      display: flex;
      justify-content: space-between;
      padding: 5px 0;
      font-size: 16px;
    }

    .info-item span:first-child {
      font-weight: bold;
      color: #555;
    }

    .footer {
      text-align: center;
      margin-top: 40px;
      font-size: 14px;
      color: #888;
    }

    .print-btn {
      text-align: center;
      margin-top: 30px;
    }

    .print-btn button {
      background-color: #0d53b1;
      color: #fff;
      padding: 10px 25px;
      border: none;
      font-size: 16px;
      cursor: pointer;
      border-radius: 4px;
    }

    .print-btn button:hover {
      background-color: #073e8d;
    }

    @media print {
      .print-btn {
        display: none;
      }

      body {
        background: white;
      }

      .facture-container {
        box-shadow: none;
        border: none;
        margin: 0;
      }
    }
  </style>
</head>
<body>

<div class="facture-container">
  <div class="header">
    <h2>FACTURE N° <?= htmlspecialchars($facture['id_facture']) ?></h2>
  </div>

  <div class="section">
    <h4>Informations Client</h4>
    <div class="info-item"><span>Nom :</span> <span><?= htmlspecialchars($facture['nom_client'] . ' ' . $facture['prenom']) ?></span></div>
    <div class="info-item"><span>Contrat :</span> <span><?= htmlspecialchars($facture['id_contrat']) ?></span></div>
  </div>

  <div class="section">
    <h4>Détails de la Facture</h4>
    <div class="info-item"><span>Date de Facturation :</span> <span><?= htmlspecialchars($facture['date_facture']) ?></span></div>
    <div class="info-item"><span>Montant :</span> <span><?= number_format($facture['montant'], 2, ',', ' ') ?> FBU</span></div>
    <div class="info-item"><span>Moyen de paiement :</span> <span><?= htmlspecialchars($facture['nom_moyen']) ?></span></div>
    <div class="info-item"><span>Annonce liée :</span> <span><?= htmlspecialchars($facture['message']) ?></span></div>
    <div class="info-item"><span>Statut :</span> <span><?= htmlspecialchars(ucfirst($facture['statut'])) ?></span></div>
  </div>

  <div class="footer">
    Merci pour votre confiance.  
    <br>ISUKU CO. - Tous droits réservés.
  </div>

  <div class="print-btn">
    <button onclick="window.print()">Imprimer cette facture</button>
  </div>
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
