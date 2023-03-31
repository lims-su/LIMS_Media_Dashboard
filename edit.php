<?php
require_once('functions.php');
require_once('config.php');


if (isset($_GET['media_id'])) {
    $media_id = $_GET['media_id'];
}
else{
    $media_id = 0;
}

$stmt_typeList = $db->query("SELECT type_id, type_name FROM types_list");
$types = [];
$stmt_progList = $db->query("SELECT program_id, program_name FROM programs_list");
$programs = [];
$links = [];
if ($media_id){ 
    // Retrieve the media output row for the provided media_id
    $stmt = $db->prepare("SELECT * FROM media_output WHERE media_id = :media_id");
    $stmt->execute(['media_id' => $media_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // If no row is found for the provided media_id, redirect to show_all.php
    if (!$row) {
        header('Location: show_all.php');
        exit;
    }

    // Retrieve the programs, types, and links for the media output
    $types_stmt = $db->prepare("SELECT type_id, media_type FROM media_types WHERE media_id = :media_id");
    $programs_stmt = $db->prepare("SELECT program_id, media_program FROM media_programs WHERE media_id = :media_id");
    $links_stmt = $db->prepare("SELECT copy_id, link FROM media_copies WHERE media_id = :media_id");
    $types_stmt->execute(['media_id' => $media_id]);
    $programs_stmt->execute(['media_id' => $media_id]);
    $links_stmt->execute(['media_id' => $media_id]);
    $media_types = $types_stmt->fetchAll(PDO::FETCH_ASSOC);
    $programs = $programs_stmt->fetchAll(PDO::FETCH_ASSOC);
    $links = $links_stmt->fetchAll(PDO::FETCH_ASSOC);
    $link_text = '';
    foreach ($links as $index => $link) {
        if ($index === 0) {
            $link_text .= $link['link']; // don't append line break to first link
        } else {
            $link_text .= "\n" . $link['link']; // append line break to subsequent links
        }
    }
    $typeNames = array();
    foreach ($media_types as $media_type) {
        $typeNames[] = $media_type["media_type"];
    }
    $programNames = array();
    foreach ($programs as $program) {
        $programNames[] = $program["media_program"];
    }
}
else {
    // If no media_id is provided, render the HTML form with empty input fields
    $row = array();
    $typeNames = array();
    $programNames = array();
    $link_text = '';
}
// Handle form submissions to edit the media output row
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the values from the form submission
    $title = $_POST['title'];
    $media_link = $_POST['media_link'];
    $media_date = $_POST['media_date'];
    $author = $_POST['author'];
  //  $media_id = $_POST['media_id'];

    if (!$media_id){
        // Insert a new media output row
        $insert_stmt = $db->prepare("INSERT INTO media_output (title, media_link, media_date, author) VALUES (:title, :media_link, :media_date, :author)");
        $insert_stmt->execute(['title' => $title, 'media_link' => $media_link, 'media_date' => $media_date, 'author' => $author]);
        // Get the media_id of the newly inserted row
        $media_id = $db->lastInsertId();
    }
    else {
        $insert_stmt = $db->prepare("UPDATE media_output SET title=:title, media_link=:media_link, media_date=:media_date, author=:author WHERE media_id = :media_id");
        $insert_stmt->execute(['title' => $title, 'media_link' => $media_link, 'media_date' => $media_date, 'author' => $author, 'media_id' => $media_id]);
    }
    // Delete all existing media_types for the media output
    $delete_media_types_stmt = $db->prepare("DELETE FROM media_types WHERE media_id = :media_id");
    $delete_media_types_stmt->execute(['media_id' => $media_id]);
    // Insert the new media_types for the media output
    
    if (isset($_POST['media_type'])) {
        $selected_media_types = $_POST['media_type'];
        foreach ($selected_media_types as $media_type) {
            $insert_media_type_stmt = $db->prepare("INSERT INTO media_types (media_id, media_type) VALUES (:media_id, :media_type)");
            $insert_media_type_stmt->execute(['media_id' => $media_id, 'media_type' => $media_type]);
        }
    }
    
    
    // Delete all existing programs for the media output
    $delete_programs_stmt = $db->prepare("DELETE FROM media_programs WHERE media_id = :media_id");
    $delete_programs_stmt->execute(['media_id' => $media_id]);
    // Insert the new programs for the media output
    
    if (isset($_POST['program'])) {
        $selected_programs = $_POST['program'];
        foreach ($selected_programs as $program) {
            $insert_program_stmt = $db->prepare("INSERT INTO media_programs (media_id, media_program) VALUES (:media_id, :media_program)");
            $insert_program_stmt->execute(['media_id' => $media_id, 'media_program' => $program]);
        }
    }
    // Delete all existing links for the media output
    $delete_links_stmt = $db->prepare("DELETE FROM media_copies WHERE media_id = :media_id");
    $delete_links_stmt->execute(['media_id' => $media_id]);
    // Insert the new links for the media output
    if (!empty($_POST['links'])) {
        $links = preg_split('/\r\n|\r|\n/', $_POST['links']);
        $links = array_map('trim', $links); // remove spaces at the beginning or end of each line
        $links = array_unique(array_filter($links)); // remove empty lines and duplicate links        
        foreach ($links as $link) {
            $insert_link_stmt = $db->prepare("INSERT INTO media_copies (media_id, link) VALUES (:media_id, :link)");
            $insert_link_stmt->execute(['media_id' => $media_id, 'link' => $link]);
        }
    }
    
// Redirect back to the show_all.php page
header("Location: show_all.php");
exit;
}
if ($media_id == 0){
    $page_title = "Create Media Output";
}
else{
    $page_title = "Edit Media Output";
}
include("header.php"); 
// Render the HTML for the edit page
?>


<body>
    <h1><?php if ($media_id != 0) {echo "Edit Media Output";} else {echo "Create Media Output";} ?></h1>
<form method="POST">
    <label for="title">Title:</label>
    <input type="text" name="title" value="<?php echo isset($row['title']) ? $row['title'] : ''; ?>" placeholder="Media title" required>
    <label for="media_link">Link:</label>
    <input type="text" name="media_link" value="<?php echo isset($row['media_link']) ? $row['media_link'] : ''; ?>" placeholder="Link" required>
    
    <label for="media_date">Date:</label>
    <input type="date" name="media_date" value="<?php echo isset($row['media_date']) ? $row['media_date'] : ''; ?>" required>
    
    
    <label for="author">Author:</label>
<input type="text" name="author" id="authorInput" value="<?php echo isset($row['author']) ? $row['author'] : ''; ?>" placeholder="Author" required list="authorList">

<datalist id="authorList">
    <?php
        $authors = file('authors.csv', FILE_IGNORE_NEW_LINES); // Read authors from file
        foreach ($authors as $author) {
            echo "<option value=\"$author\">";
        }
    ?>
</datalist>

 
   
    <h2>Output</h2>
    
    <fieldset>
        <legend>Select output:</legend>
        <?php while ($row1 = $stmt_typeList->fetch(PDO::FETCH_ASSOC)) { ?>
            <div class='media_type'>
                <input type='checkbox' id='type_<?php echo $row1['type_id']; ?>' name='media_type[]' value='<?php echo $row1['type_name']; ?>' class='styled-checkbox'<?php if (in_array($row1['type_name'], $typeNames)) { echo " checked"; } ?>>
                <label for='type_<?php echo $row1['type_id']; ?>' class='styled-label'><?php echo $row1['type_name']; ?></label>
            </div>
        <?php } ?>
    </fieldset>
    
   
    <h2>Programs</h2>
    <fieldset>
        <legend>Select programs:</legend>
        <?php while ($row = $stmt_progList->fetch(PDO::FETCH_ASSOC)) { ?>
            <div class='prog'>
                <input type='checkbox' id='prog_<?php echo $row['program_id']; ?>' name='program[]' value='<?php echo $row['program_name']; ?>' class='styled-checkbox'<?php if (in_array($row['program_name'], $programNames)) { echo " checked"; } ?>>
                <label for='prog_<?php echo $row['program_id']; ?>' class='styled-label'><?php echo $row['program_name']; ?></label>
            </div>
        <?php } ?>
    </fieldset>
    <h2 style="display: inline-block; margin-right: 10px;">Copies links</h2>
<h2 style="display: inline-block; font-size: 1em;"><small>(One link per line)</small></h2>

    <div class="myTextarea">
    <textarea id="myTextarea" name="links"><?php echo $link_text;?></textarea>
        </div>
    <br>
    <input type="submit" value="Save">
    <button type="button" onclick="location.href='show_all.php';">Cancel</button>
</form>
   
      
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

    </script>

</body>
</html>