<?php
// sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
  /* Style sidebar intégré */
  .sidebar {
    background-color: #2c3e50;
    color: #ecf0f1;
    width: 250px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-sizing: border-box;
    flex-shrink: 0;
    height: 100vh;
    overflow-y: auto;
    font-family: 'Open Sans', sans-serif;
  }

  .sidebar h2 {
    text-align: center;
    font-weight: bold;
    margin-bottom: 30px;
  }

  .sidebar ul {
    list-style: none;
    padding-left: 0;
  }

  .sidebar ul li {
    margin: 15px 0;
  }

  .sidebar ul li a {
    color: #ecf0f1;
    text-decoration: none;
    display: block;
    padding: 8px 12px;
    border-radius: 4px;
    transition: all 0.3s ease;
  }

  .sidebar ul li a:hover,
  .sidebar ul li a.active {
    background-color: #0d9855;
    font-weight: bold;
    color: white;
    padding-left: 20px;
  }

  .sidebar .logout a {
    display: block;
    text-align: center;
    background-color: #c0392b;
    color: #ecf0f1;
    padding: 10px 15px;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
    margin-top: 30px;
  }

  .sidebar .logout a:hover {
    background-color: #e74c3c;
  }
</style>

<aside class="sidebar">
  <div>
    <div class="d-flex align-items-center mb-2">
         <img src="./images/logo1.png" alt="Logo Recyclage" style="height: 40px;  margin-right: 2px;">
        <h5 class="mb-0"><strong style="font-weight: 700; font-size: 20px; color: #0d53b1;">ISUKU</strong>
        <span style="font-weight: 400; font-size: 20px; color: #0d9855;">CO.</span></h5>
    </div>
    <ul>
      <li><a href="dashboard_client.php" class="<?= $currentPage == 'dashboard_client.php' ? 'active' : '' ?>">Accueil</a></li>
      
    </ul>
  </div>
  <div class="logout">
    <a href="logout.php">Déconnexion</a>
  </div>
</aside>
