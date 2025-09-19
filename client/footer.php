<!-- footer.php -->
<footer class="footer mt-5 text-white pt-4 pb-3" style="background-color: #263238;">
  <div class="container">
    <div class="row">

      <!-- Logo + description -->
      <div class="col-md-4 mb-3">
        <!-- footer.php (extrait modifié) -->
<div class="d-flex align-items-center mb-2">
  <a class="navbar-brand d-flex align-items-center text-white" href="index.php">
        <img src="./images/logo1.png" alt="Logo Recyclage" style="height: 40px; margin-right: 2px;">
    <h5 class="mb-0">
      <strong style="font-weight: 700; font-size: 20px; color: #0d53b1;">ISUKU</strong>
      <span style="font-weight: 400; font-size: 20px; color: #0d9855;">CO.</span>
    </h5>
    </a>
</div>
<p class="small">Collecte intelligente des déchets pour un avenir plus propre et durable. 🌱</p>

      </div>

      <!-- Liens utiles -->
      <div class="col-md-4 mb-3">
        <h6>Liens utiles</h6>
        <ul class="list-unstyled">
          <li><a href="index.php" class="footer-link">Accueil</a></li>
          <li><a href="apropos.php" class="footer-link">À propos</a></li>
          <li><a href="programme.php" class="footer-link">Programme</a></li>
          <li><a href="contact.php" class="footer-link">Contact</a></li>
        </ul>
      </div>

      <!-- Contact + réseaux sociaux -->
      <div class="col-md-4 mb-3">
        <h6>Contact</h6>
        <p class="mb-1">
             <i class="fas fa-envelope"></i> 
             <a href="mailto:contact@isukuco.org">contact@isukuco.org</a>
        </p>
        <p>
          <i class="fas fa-phone"></i> 
          <a href="tel:+257 61 00 00 00">+257 61 00 00 00</a>
        </p>
        <div class="social-icons mt-2">
          <a href="https://web.facebook.com/profile.php?id=61577157317858" class="me-2 text-white" target="_blank"><i class="fab fa-facebook-f"></i></a>
          <a href="https://www.youtube.com/@isukuco" class="me-2 text-white" target="_blank"><i class="fab fa-youtube"></i></a>
          <a href="https://www.instagram.com/isukuco/" class="me-2 text-white" target="_blank"><i class="fab fa-instagram"></i></a>
        </div>
      </div>

    </div>

    <hr style="border-color: #455a64;">

    <div class="text-center small">
      &copy; <?= date('Y') ?> ISUKU CO. Tous droits réservés.
    </div>
  </div>
</footer>

<style>
.footer-link {
  color: #cfd8dc;
  text-decoration: none;
  transition: color 0.3s;
}
.footer-link:hover {
  color: #00bcd4;
  text-decoration: underline;
}
.social-icons a:hover {
  color: #00bcd4;
}
footer a[href^="mailto:"],
footer a[href^="tel:"] {
  color: inherit;           /* couleur du texte parent, pas bleu */
  text-decoration: none;    /* pas de soulignement */
  transition: color 0.3s, text-decoration 0.3s;
}

footer a[href^="mailto:"]:hover,
footer a[href^="tel:"]:hover,
footer a[href^="mailto:"]:focus,
footer a[href^="tel:"]:focus {
  color: #0d6efd;           /* bleu Bootstrap au survol */
  
  outline: none;
}


</style>

<!-- Bootstrap JS (à placer ici pour que les éléments interactifs fonctionnent) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
