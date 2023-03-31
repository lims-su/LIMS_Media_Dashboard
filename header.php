<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" href="lims_dashboard_icon.png" type="image/png">
    <link href="//db.onlinewebfonts.com/c/7712e50ecac759e968ac145c0c4a6d33?family=Droid+Arabic+Kufi" rel="stylesheet" type="text/css"/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<?php
  $logout = getWebsiteRoot()."lims_media2/logout.php";
  $isLoggedIn = isset($_SESSION['username']);
  if (!isset($_SESSION['username'])&& basename($_SERVER['REQUEST_URI']) !== 'login.php') {
    header('Location: login.php');
    exit;
} 
?>
<div class="header">
<a href="<?php echo '//' . $_SERVER['HTTP_HOST'].'/lims_media2' ?>">
<h3><img src="logo.png" alt="LIMS Media Output Logo">LIMS Media Dashboard</h3></a>
  <?php if ($isLoggedIn) { ?>
    <p class="username"><?php echo $_SESSION['username']; ?></p>
    <a href="<?php echo $logout; ?>" class="logout-btn">Logout</a>
  <?php } ?>
</div>
