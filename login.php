<?php
session_start();

// Connexion à la base de données
$host = "localhost";
$dbname = "isukuco";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Parfois contient plusieurs IPs séparées par des virgules
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}


// Traitement du formulaire de connexion
$erreur = ""; 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utilisateur) {
    if ($utilisateur["actif"] == 1) {
        if (password_verify($password, $utilisateur["password"])) {
            // connexion OK, sessions etc...
            $_SESSION["id_utilisateur"] = $utilisateur["id_utilisateur"]; 
            $_SESSION["nom"] = $utilisateur["nom"];
            $_SESSION["prenom"] = $utilisateur["prenom"];
            $_SESSION["role"] = $utilisateur["role"];
            $_SESSION["email"] = $utilisateur["email"];

            // Log connexion
            $ip = getClientIP();
            $navigateur = $_SERVER['HTTP_USER_AGENT'];
            $stmtLog = $pdo->prepare("INSERT INTO journal_connexion (id_utilisateur, role, adresse_ip, navigateur) VALUES (?, ?, ?, ?)");
            $stmtLog->execute([
                $utilisateur["id_utilisateur"],
                $utilisateur["role"],
                $ip,
                $navigateur
            ]);

            // Redirections selon rôle
            if ($utilisateur["role"] === "client") {
                header("Location: ../isukuco/client/dashboard_client.php");
                exit();
            } elseif ($utilisateur["role"] === "agent") {
                header("Location: ../isukuco/agent/dashboard_agent.php");
                exit();
            } elseif ($utilisateur["role"] === "chauffeur") {
                header("Location: ../isukuco/chauffeur/dashboard_chauffeur.php");
                exit();
            } elseif ($utilisateur["role"] === "coordinateur") {
                header("Location: ../isukuco/coordinateur/dashboard_coordinateur.php");
                exit();
            }
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Votre compte est désactivé. Veuillez contacter l'administrateur.";
    }
} else {
    $erreur = "Email ou mot de passe incorrect.";
}

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>ISUKU - Connexion</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Animate.css et FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

    body {
      background: linear-gradient(to right, #e0f7fa, #f4f6f5);
      font-family: 'Inter', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }

    .login-container {
      background: #fff;
      padding: 40px 35px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
      width: 100%;
      max-width: 400px;
      text-align: center;
      animation: fadeInDown 0.9s ease forwards;
    }

    h2 {
      margin-bottom: 25px;
      color: #023e8a;
      font-weight: 600;
      font-size: 1.8rem;
      letter-spacing: 0.05em;
    }

    .input-container {
      position: relative;
      margin-bottom: 20px;
      text-align: left;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
      color: #2c3e50;
      font-size: 0.95rem;
    }

    .input-container input {
  width: 100%;
  padding: 14px 40px 14px 40px;
  border: 1.8px solid #90e0ef;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  outline: none; /* supprime le cadre noir */
  box-sizing: border-box;
}

.input-container input:focus {
  border-color: #0077b6;
  box-shadow: 0 0 6px rgba(0, 119, 182, 0.25);
}


    .input-container i {
      position: absolute;
      left: 12px;
      top: 65%;
      transform: translateY(-50%);
      color: #0077b6;
      font-size: 1.2rem;
      pointer-events: none;
    }

    button {
      background-color: #0077b6;
      color: #fff;
      border: none;
      padding: 14px 25px;
      font-size: 1.1rem;
      border-radius: 30px;
      cursor: pointer;
      width: 100%;
      font-weight: 600;
      letter-spacing: 0.05em;
      transition: background-color 0.3s ease;
      margin-top: 15px;
      box-shadow: 0 4px 8px rgba(0, 119, 182, 0.4);
    }

    button:hover {
      background-color: #023e8a;
      box-shadow: 0 6px 12px rgba(2, 62, 138, 0.7);
    }

    .error-message {
      background-color: #ffebee;
      color: #c62828;
      padding: 12px 15px;
      border: 1.5px solid #ef9a9a;
      border-radius: 10px;
      margin-top: 20px;
      font-weight: 600;
      animation: shake 0.4s ease-in-out;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .footer-link, .back-link {
      margin-top: 18px;
      font-size: 0.9rem;
      color: #0077b6;
    }

    .footer-link a, .back-link a {
      color: #0077b6;
      text-decoration: none;
      font-weight: 600;
    }

    .footer-link a:hover, .back-link a:hover {
      text-decoration: underline;
    }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes shake {
      0% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      50% { transform: translateX(5px); }
      75% { transform: translateX(-5px); }
      100% { transform: translateX(0); }
    }
  </style>
</head>
<body>

<div class="login-container animate__animated animate__fadeInDown" role="main" aria-labelledby="loginTitle">
  <h2 id="loginTitle"><i class="fas fa-recycle" aria-hidden="true"></i> Connexion</h2>

  <form method="POST" novalidate aria-describedby="errorMsg">
    <div class="input-container">
      <label for="email">Adresse e-mail</label>
      <i class="fas fa-envelope" aria-hidden="true"></i>
      <input type="email" id="email" name="email" placeholder="exemple@domaine.com" required aria-required="true" autocomplete="email" />
    </div>

    <div class="input-container">
      <label for="password">Mot de passe</label>
      <i class="fas fa-lock" aria-hidden="true"></i>
      <input type="password" id="password" name="password" placeholder="Votre mot de passe" required aria-required="true" autocomplete="current-password" />
    </div>

    <button type="submit" name="envoyer" aria-label="Se connecter">Se connecter</button>
  </form>

  <?php if (!empty($erreur)): ?>
    <div id="errorMsg" class="error-message" role="alert" aria-live="assertive">
      <i class="fas fa-exclamation-triangle" aria-hidden="true"></i> <?= htmlspecialchars($erreur) ?>
    </div>
  <?php endif; ?>

  <div class="footer-link">
    <p>Pas encore inscrit ? <a href="inscription.php">Créer un compte</a></p>
  </div>

  <div class="back-link">
    <a href="index.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
  </div>
</div>

</body>
</html>
