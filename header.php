<!-- header.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>ISUKU CO. - Recyclage & Collecte</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- Animate.css -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

  <!-- Style personnalisé -->
  <style>
    body {
      background-color: #f5f5f5;
      font-family: 'Segoe UI', sans-serif;
      padding-top: 90px;
    }

    /* Nouveau fond sombre et élégant */
    .navbar {
      background-color: #263238;
    }

    .navbar-brand img {
      height: 40px;
      margin-right: 10px;
    }

    .nav-link {
      color: #ffffff !important;
      font-weight: 500;
      font-size: 1.05rem;
      padding: 10px 15px;
      transition: background-color 0.3s, color 0.3s;
      border-radius: 6px;
    }

    .nav-link:hover {
      background-color: #37474f;
      color: #00bcd4 !important;
    }

    /* Lien se connecter moins visible */
    .nav-link.dimmed {
      color: rgba(255, 255, 255, 0.6) !important;
    }

    /* Bouton inscription mis en avant */
    .btn-inscription {
      background-color: #00bcd4;
      color: white !important;
      border-radius: 25px;
      font-weight: bold;
      padding: 8px 20px;
      transition: background-color 0.3s ease;
      animation: pulseLoop 3s ease-in-out infinite;
    }

    .btn-inscription:hover {
      background-color: #0097a7;
    }

    @keyframes pulseLoop {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.08); }
    }
  </style>
</head>
<body>

<!-- Menu de navigation -->
<nav class="navbar navbar-expand-lg fixed-top shadow animate__animated animate__fadeInDown">
  <div class="container">
  

    <a class="navbar-brand d-flex align-items-center text-white" href="index.php">
        <img src="./images/logo1.png" alt="Logo Recyclage" style="height: 40px; margin-right: 2px;">
    <h5 class="mb-0">
      <strong style="font-weight: 700; font-size: 20px; color: #0d53b1;">ISUKU</strong>
      <span style="font-weight: 400; font-size: 20px; color: #0d9855;">CO.</span>
    </h5>
    </a>


    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item mx-2">
          <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Accueil</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="apropos.php"><i class="fas fa-leaf"></i> À propos</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="programme.php"><i class="fas fa-recycle"></i> Programme</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link dimmed" href="login.php"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
        </li>
        <li class="nav-item ms-3">
          <a class="btn btn-inscription animate__animated animate__bounceIn" href="inscription.php">
            <i class="fas fa-user-plus"></i> Inscription
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
