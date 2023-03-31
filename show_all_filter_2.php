<?php
$page_title = "LIMS Media Dashboard";
require_once('functions.php');
require_once('config.php');
include("header.php"); 
?>


<!-- Add these lines to your <head> tag to import the library stylesheets and scripts -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js">

let table = new DataTable('#my-table');
</script>


<?php


echo'<body>';
if (isset($_POST['delete']) && isset($_POST['media_id'])) {
    // User has confirmed the delete action, so delete the row from the database
    $media_id = $_POST['media_id'];
    $delete_stmt = $db->prepare("DELETE FROM media_output WHERE media_id = :media_id");
    $delete_stmt->bindParam(':media_id', $media_id);
    $delete_stmt->execute();
}

// Query the media_output and media_programs tables to retrieve all rows
$stmt = $db->query("
    SELECT media_output.media_id, title, media_date, author, media_link
    FROM media_output 
    ORDER BY media_output.media_date desc, media_output.media_id DESC;
");

echo "<div class='content'>";
// Add a link to create a new media output row
echo"   <div class='menu'>
        <p>
        <a href='edit.php' class='button'>Create Post</a>
        <a title='Get last 30 days posts from limslb.com website - except أخبار اقتصادية' href='get_posts.php' class='button'>Get posts</a>";
        echo"<a href='filters.php' class='button'>Apply filters</a>";
        if ($is_admin) {
          echo "<a href='import_posts_CSV.php' class='button'>Import posts from CSV</a>";
        }
        else{
          echo "<a href='import_posts_CSV.php'><c class='button_disabled'>Import posts from CSV</c></a>";
        }
       
   echo"</p></div>";
//echo '<form method="post" action="export.php"><input type="submit" name="export" value="Export"></form>';
// Start building the HTML table
//echo '<div id="table-container" style="height: 580px; overflow: scroll; ">';
echo "<table id='my-table'>";
echo "<thead><tr><th>#</th><th>Title</th><th>Output</th><th>URL</th><th>Date</th><th>Author</th><th>Program</th><th>Copies</th><th>Edit</th><th>Delete</th></tr><thead>";
echo "<tbody>";
// Loop through each row in the result set and add it to the HTML table
$row_num = 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $row_num . "</td>"; // display the row number instead of media_id
    echo "<td title = '". $row['title'] ."'>" . $row['title'] . "</td>";
       // Query the media_types and media_copies tables to get the types and copies for this media_id
       $media_id = $row['media_id'];
       $type_stmt = $db->prepare("SELECT string_agg(media_type, ', ') AS media_type FROM media_types WHERE media_id = :media_id");
       $type_stmt->bindParam(':media_id', $media_id);
       $type_stmt->execute();
       $type_row = $type_stmt->fetch(PDO::FETCH_ASSOC);
    echo "<td>" . $type_row['media_type'] . "</td>";
    echo "<td><a href='" . $row['media_link'] . "' target='_blank'>" . $row['media_link'] . "</a></td>";
    echo "<td>" . $row['media_date'] . "</td>";
    echo "<td>" . $row['author'] . "</td>";
    
    // Query the media_programs and media_copies tables to get the programs and copies for this media_id
    $media_id = $row['media_id'];
    $program_stmt = $db->prepare("SELECT string_agg(media_program, ', ') AS media_program FROM media_programs WHERE media_id = :media_id");
    $program_stmt->bindParam(':media_id', $media_id);
    $program_stmt->execute();
    $program_row = $program_stmt->fetch(PDO::FETCH_ASSOC);

    $copies_stmt = $db->prepare("SELECT COUNT(copy_id) AS copies FROM media_copies WHERE media_id = :media_id");
    $copies_stmt->bindParam(':media_id', $media_id);
    $copies_stmt->execute();
    $copies_row = $copies_stmt->fetch(PDO::FETCH_ASSOC);

    echo "<td>" . $program_row['media_program'] . "</td>";
    echo "<td>" . $copies_row['copies'] . "</td>";
    echo "<td><a href='edit.php?media_id=" . $row['media_id'] . "'>Edit</a></td>";
    echo "<td><a href='delete.php?media_id=" . $row['media_id'] . "' onclick='return confirm(\"Are you sure you want to delete this row?\")'>Delete</a></td>";
    echo "</tr>";
    $row_num++; // increment the row number for the next iteration
}

// Finish building
echo "</tbody>";
echo "</table>";
//echo "</div>"; // close div table container
echo "</div>"; // close div content

// Close the database connection
$db = null;
?>
</body>
