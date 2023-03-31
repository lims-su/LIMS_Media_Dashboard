<?php
$page_title = "LIMS Posts Import";
require_once('functions.php');
require_once('config.php');
include("header.php"); 

if (!$is_admin) {
  echo "Access denied.";
  exit;
}

// Load the media types and programs into associative arrays
$media_types = array();
$media_programs = array();
$types_file = fopen('media_types.csv', 'r');
$programs_file = fopen('media_programs.csv', 'r');

// Load media types
while (($data = fgetcsv($types_file)) !== false) {
    if(isset($data[0]) && isset($data[1])) {
        $category_name = $data[0];
        $media_type = $data[1];
        $media_types[$category_name] = $media_type;
    }
}

// Load media programs
while (($data = fgetcsv($programs_file)) !== false) {
    $category_name = $data[0];
    $program_name = $data[1];
    $media_programs[$category_name] = $program_name;
}

fclose($types_file);
fclose($programs_file);

// Load the LIMS posts CSV file
$file = fopen('export.csv', 'r');
$header = fgetcsv($file);

$column_indices = array();

// Map the column names to their indices
foreach ($header as $i => $column_name) {
    $column_indices[$column_name] = $i;
}
$rows_csv = 0;
$num_changed = 0;
$economic_news = 0;
while (($data = fgetcsv($file)) !== false) {
    $rows_csv++;
    // Extract data using column names instead of indices
    $title = $data[$column_indices['Title']];
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $data[$column_indices['Date']])->format('M d, Y');
    //$formatted_date = $date->format('M d, Y');
        $permalink = $data[$column_indices['Permalink']];
    $categories = $data[$column_indices['Categories']];

    // Split the categories by the '|' delimiter
    $category_list = explode('|', $categories);


    // Check if the array contains "ECONOMIC NEWS" or "أخبار إقتصادية" regardless of the case
    if (in_array(strtolower("ECONOMIC NEWS"), array_map('strtolower', $category_list)) ||
        in_array(strtolower("أخبار إقتصادية"), array_map('strtolower', $category_list))) {
        $economic_news++;
        continue; //skip economic news category 
    } 
    
    $outputs = array();
    $programs = array();
    
    // Look up the media type and program for each category
    foreach ($category_list as $category) {
      if (isset($media_types[$category])) {
        $outputs[] = $media_types[$category];
      }
      if (isset($media_programs[$category])) {
        $programs[] = $media_programs[$category];
      }
    }
    

    // Check if media output exists in the database
    $result = $db->prepare("SELECT * FROM media_output WHERE media_link = ?");
    $result->execute(array($permalink));
    $media_output = $result->fetch();
    

    if ($media_output) {      
        // Delete existing media types and programs for this media output
        $media_id = $media_output['media_id'];
        $db->prepare("DELETE FROM media_types WHERE media_id = ?")->execute(array($media_id));
        $db->prepare("DELETE FROM media_programs WHERE media_id = ?")->execute(array($media_id));
        $db->prepare("UPDATE media_output SET title = ?, media_date = ? WHERE media_id = ?")->execute(array($title, $date, $media_id));        
    } else {
        // Insert new media output
        $num_changed++;
        $result = $db->prepare("INSERT INTO media_output (title, media_date, media_link) VALUES (?, ?, ?) RETURNING media_id");
        $result->execute(array($title, $date, $permalink));
        $row = $result->fetch();
        $media_id = $row[0];
    }
    
    // Prepare SQL statements for inserting into media_types and media_programs tables
    $insert_type_stmt = $db->prepare("INSERT INTO media_types (media_id, media_type) VALUES (?, ?)");
    $insert_program_stmt = $db->prepare("INSERT INTO media_programs (media_id, media_program) VALUES (?, ?)");

    // Iterate through each element in $outputs array and insert into media_types table
    foreach ($outputs as $media_type) {
        $insert_type_stmt->execute([$media_id, $media_type]);
    }

    // Iterate through each element in $programs array and insert into media_programs table
    foreach ($programs as $program) {
        $insert_program_stmt->execute([$media_id, $program]);
    }
}
fclose($file);
echo"<br>import complete!<br>";
echo "Rows in CSV:" . $rows_csv . "<br>";
echo "New rows added: " . $num_changed . "<br>";
echo "Skipped economic news rows:" . $economic_news . "<br>";
//include("footer.php");
?>
