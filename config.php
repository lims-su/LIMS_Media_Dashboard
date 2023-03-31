<?php
// Database connection details
$host = 'localhost';
$dbname = 'LIMS_Media';
$user = 'postgres';
$password = '1234';
// dashboard users
$users = [
  'toni1t' => ['password' => '3.="2`f.Sp0).>#~a|', 'role' => 'admin'],
  'user1' => ['password' => 'password1', 'role' => 'editor'],
  'user2' => ['password' => 'password2', 'role' => 'editor']
];

// Start the session
if (!isset($_SESSION)) {
    session_set_cookie_params(0);
    session_start();
}
$is_admin = isset($_SESSION['username']) && isset($users[$_SESSION['username']]['role']) && $users[$_SESSION['username']]['role'] === 'admin';
$is_editor = isset($_SESSION['username']) && isset($users[$_SESSION['username']]['role']) && $users[$_SESSION['username']]['role'] === 'editor';

try {
    $db = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
} catch (PDOException $e) {
    echo "Error connecting to database: " . $e->getMessage();
    exit;
}
?>
