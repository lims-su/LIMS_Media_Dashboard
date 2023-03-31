<?php
$page_title = "LIME Posts Import";
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
// Load the LIME posts CSV file
$file = fopen('export-2023-march.csv', 'r');
$header = fgetcsv($file);

$column_indices = array();

// Map the column names to their indices
foreach ($header as $i => $column_name) {
    $column_indices[$column_name] = $i;
}

echo "<table>";
echo "<tr>";
echo "<th>Title</th>";
echo "<th>Date</th>";
echo "<th>Permalink</th>";
echo "<th>Output</th>";
echo "<th>Program</th>";
echo "</tr>";

while (($data = fgetcsv($file)) !== false) {
    // Extract data using column names instead of indices
    $title = $data[$column_indices['Title']];
    $date = DateTime::createFromFormat('d-m-y H:i', $data[$column_indices['Date']])->format('Y-m-d');
    $permalink = $data[$column_indices['Permalink']];
    $categories = $data[$column_indices['Categories']];

    // Split the categories by the '|' delimiter
    $category_list = explode('|', $categories);

    // Initialize the output and program names
    $output = "";
    $program = "";

    // Look up the media type and program for each category
    foreach ($category_list as $category) {
      if (isset($media_types[$category])) {
          if (!empty($output)) { // Check if $output is empty
              $output .= ', '; // Add a comma separator only if $output is not empty
          }
          $output .= $media_types[$category];
      }
      if (isset($media_programs[$category])) {
          if (!empty($program)) { // Check if $program is empty
              $program .= ', '; // Add a comma separator only if $program is not empty
          }
          $program .= $media_programs[$category];
      }
  }
    
    echo "<tr>";
    echo "<td>$title</td>";
    echo "<td>" . date('M j, Y', strtotime($date)) . "</td>";
    echo "<td>$permalink</td>";
    echo "<td>$output</td>";
    echo "<td>$program</td>";
    echo "</tr>";
}

echo "</table>";
fclose($file);
?>