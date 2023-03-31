<?php
echo '<link rel="stylesheet" type="text/css" href="style.css">';
require_once('functions.php');
require_once('config.php');
include("header.php"); 

if (isset($_GET['media_id'])) {
    $media_id = $_GET['media_id'];
} else {
    header('Location: show_all.php');
    exit; 
}
// delete the row(s) from the media_types, media_copies, media_programs tables that reference the media_id you want to delete
$stmt = $db->prepare("DELETE FROM media_types WHERE media_id = :media_id");
$stmt->bindValue(':media_id', $media_id);
$stmt->execute();

$stmt = $db->prepare("DELETE FROM media_copies WHERE media_id = :media_id");
$stmt->bindValue(':media_id', $media_id);
$stmt->execute();

$stmt = $db->prepare("DELETE FROM media_programs WHERE media_id = :media_id");
$stmt->bindValue(':media_id', $media_id);
$stmt->execute();

$stmt = $db->prepare("DELETE FROM media_output WHERE media_id = :media_id");
$stmt->bindValue(':media_id', $media_id);
$stmt->execute();


// Generate JavaScript code to close the current browser tab
//echo '<script type="text/javascript">';
//echo 'window.close();';
//echo '</script>';


header('Location: show_all.php');
exit;
?>
