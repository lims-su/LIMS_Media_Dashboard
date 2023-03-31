<?php
//this code delete a type with all it's media_outputs and related rows in other tables
$page_title = "LIME Posts Import";
require_once('functions.php');
require_once('config.php');
include("header.php"); 

if (!$is_admin) {
    echo "Access denied.";
    exit;
  }
// define the media_type you want to delete
$media_type = 'ECONOMIC NEWS';

// prepare a query to select all media_id's with the given media_type from media_types table
$select_media_ids_stmt = $db->prepare("SELECT media_id FROM media_types WHERE media_type = :media_type");

// execute the query with the media_type parameter
$select_media_ids_stmt->execute(['media_type' => $media_type]);

// fetch all media_id's into an array
$media_ids = $select_media_ids_stmt->fetchAll(PDO::FETCH_COLUMN);

// prepare a query to delete rows from media_copies table
$delete_media_copies_stmt = $db->prepare("DELETE FROM media_copies WHERE media_id = :media_id");

// prepare a query to delete rows from media_programs table
$delete_media_programs_stmt = $db->prepare("DELETE FROM media_programs WHERE media_id = :media_id");

// prepare a query to delete rows from media_types table
$delete_media_types_stmt = $db->prepare("DELETE FROM media_types WHERE media_id = :media_id");

// prepare a query to delete rows from media_output table
$delete_media_output_stmt = $db->prepare("DELETE FROM media_output WHERE media_id = :media_id");

// loop through each media_id in the array
foreach ($media_ids as $media_id) {
    // bind the media_id parameter to each of the prepared statements
    $delete_media_copies_stmt->bindParam(':media_id', $media_id);
    $delete_media_programs_stmt->bindParam(':media_id', $media_id);
    $delete_media_types_stmt->bindParam(':media_id', $media_id);
    $delete_media_output_stmt->bindParam(':media_id', $media_id);

    // execute the prepared statements to delete the rows
    $delete_media_copies_stmt->execute();
    $delete_media_programs_stmt->execute();
    $delete_media_types_stmt->execute();
    $delete_media_output_stmt->execute();
}

// close the database connection
$db = null;
?>
