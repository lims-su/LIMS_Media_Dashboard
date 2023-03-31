<?php
require_once('functions.php');
require_once('config.php');

// Set the filename to be exported as
$filename = "lims_media_output_" . date('Y-m-d') . ".csv";

// Set the HTTP headers to download the file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '";');

// Create a file pointer
$output = fopen("php://output", "w");

// Add the UTF-8 BOM header to support special characters in Excel
fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

// Write the column headers to the CSV file
fputcsv($output, array('#', 'Title', 'Output', 'URL', 'Date', 'Author', 'Program', 'Copies'));

// Query the media_output table to retrieve all rows
$stmt = $db->query("
    SELECT media_output.media_id, title, media_date, author, media_link
    FROM media_output 
    ORDER BY media_output.media_date desc, media_output.media_id DESC;
");

// Loop through each row in the result set and add it to the CSV file
$row_num = 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Query the media_types and media_copies tables to get the types and copies for this media_id
    $media_id = $row['media_id'];
    $type_stmt = $db->prepare("SELECT string_agg(media_type, ', ') AS media_type FROM media_types WHERE media_id = :media_id");
    $type_stmt->bindParam(':media_id', $media_id);
    $type_stmt->execute();
    $type_row = $type_stmt->fetch(PDO::FETCH_ASSOC);

    // Query the media_programs and media_copies tables to get the programs and copies for this media_id
    $program_stmt = $db->prepare("SELECT string_agg(media_program, ', ') AS media_program FROM media_programs WHERE media_id = :media_id");
    $program_stmt->bindParam(':media_id', $media_id);
    $program_stmt->execute();
    $program_row = $program_stmt->fetch(PDO::FETCH_ASSOC);

    $copies_stmt = $db->prepare("SELECT COUNT(copy_id) AS copies FROM media_copies WHERE media_id = :media_id");
    $copies_stmt->bindParam(':media_id', $media_id);
    $copies_stmt->execute();
    $copies_row = $copies_stmt->fetch(PDO::FETCH_ASSOC);

    // Write the row data to the CSV file
    fputcsv($output, array(
        $row_num,
        $row['title'],
        $type_row['media_type'],
        $row['media_link'],
        $row['media_date'],
        $row['author'],
        $program_row['media_program'],
        $copies_row['copies']
    ));

    $row_num++; // increment the row number for the next iteration
}

// Close the file pointer
fclose($output);

// Prompt the user to download the file
readfile($filename);
?>
