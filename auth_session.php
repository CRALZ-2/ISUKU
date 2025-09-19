<?php
$inactivite_max = 300;

if (isset($_SESSION['derniere_activite'])) {
    if ((time() - $_SESSION['derniere_activite']) > $inactivite_max) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit;
    }
}
$_SESSION['derniere_activite'] = time();
?>
