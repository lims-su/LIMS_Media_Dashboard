<?php
$page_title = "Login";
session_start();
echo '<link rel="stylesheet" type="text/css" href="style.css">';
require_once('functions.php');
require_once('config.php');
include("header.php"); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  if (isset($users[$username]) && $users[$username]['password'] === $password) {
    $_SESSION['username'] = $username;
    $_SESSION['is_admin'] = true;
    header('Location: show_all.php');
    exit;
  } else {
    $error = 'Invalid username or password';
  }
  

}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
</head>
<body>
  <?php
  if (isset($_SESSION['username'])) {
    // User is logged in, show logout button
    header('Location: show_all.php');
    //echo '<div><p>Welcome, '.$_SESSION['username'].'!</p><form method="post" action="logout.php"><input type="submit" value="Logout"></form></div>';
  } else {
    // User is not logged in, show login form
    echo '<div><h1>Login</h1>';
    if (!empty($error)) {
      echo '<p>'.$error.'</p>';
    }
    echo '<form id="login" method="post">';
    echo '<div>';
    echo '<label for="username">Username:</label>';
    echo '<input type="text" name="username" id="username">';
    echo '</div>';
    echo '<div>';
    echo '<label for="password">Password:</label>';
    echo '<input type="password" name="password" id="password">';
    echo '</div>';
    echo '<div>';
    echo '<input type="submit" value="Login">';
    echo '</div>';
    echo '</form></div>';
  }
  ?>
