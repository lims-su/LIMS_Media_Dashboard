<?php
$page_title = "LIME Media Dashboard - Filters";
require_once('functions.php');
require_once('config.php');
include("header.php"); 
$stmt_typeList = $db->query("SELECT type_id, type_name FROM types_list");
$types = [];
$stmt_progList = $db->query("SELECT program_id, program_name FROM programs_list");
$programs = [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Media Output Table</title>

    </head>
<body>
    <div class="form-container">
        <h1>Media Output Table</h1>
        
        <form method="post">
        <div class="filter">
            <label for="start-date">Start Date:</label>
            <input type="date" id="start-date" name="start_date" value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>">
            <label for="end-date">End Date:</label>
            <input type="date" id="end-date" name="end_date" value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>">
            </div>                
            <h2> Choose Output</h2> 
            <fieldset>
                <legend>Select output:</legend>
                <?php while ($row1 = $stmt_typeList->fetch(PDO::FETCH_ASSOC)) { ?>
                    <div class='media_type'>
                    <input type='checkbox' id='type_<?php echo $row1['type_id']; ?>' name='media_type[]' value='<?php echo $row1['type_name']; ?>' class='styled-checkbox' <?php if(empty($_POST) || (isset($_POST['media_type']) && in_array($row1['type_name'], $_POST['media_type']))) echo 'checked'; ?>>
                        <label for='type_<?php echo $row1['type_id']; ?>' class='styled-label'><?php echo $row1['type_name']; ?></label>
                    </div>
                <?php } ?>
            </fieldset>          
            <h2>Choose Programs</h2>
            <fieldset>
                <legend>Select programs:</legend>
                <?php while ($row = $stmt_progList->fetch(PDO::FETCH_ASSOC)) { ?>
                    <div class='prog'>
                        <input type='checkbox' id='prog_<?php echo $row['program_id']; ?>' name='program[]' value='<?php echo $row['program_name']; ?>' class='styled-checkbox' <?php if(empty($_POST) || (isset($_POST['program']) && in_array($row['program_name'], $_POST['program']))) echo 'checked';?>>
                        <label for='prog_<?php echo $row['program_id']; ?>' class='styled-label'><?php echo $row['program_name']; ?></label>
                    </div>
                <?php } ?>
            </fieldset>
            <input type="submit" value="Submit">
        </form>
        
    </div>
</body>
</html>
<?php
// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the start and end dates from the form data
    $start_date = date('Y-m-d', strtotime($_POST['start_date']));
    $end_date = date('Y-m-d', strtotime($_POST['end_date']));
    if (isset($_POST['media_type'])) {
        $selected_media_types = $_POST['media_type'];
    }
    if (isset($_POST['program'])) {
        $selected_programs = $_POST['program'];
    }
    $selected_media_types_str = implode(',', $selected_media_types);
    $selected_programs_str = implode(',', $selected_programs);
    $selected_media_types = explode(',', $selected_media_types_str);
    $selected_programs = explode(',', $selected_programs_str);
    print_r ($selected_media_types);
    // Construct the SQL query to retrieve data from the media_output table
    $sql = "SELECT mo.media_id, mo.title, mo.media_date, mo.author, mo.media_link, mc.link, mp.media_program, mt.media_type
        FROM media_output mo
        LEFT JOIN media_copies mc ON mo.media_id = mc.media_id
        LEFT JOIN media_programs mp ON mo.media_id = mp.media_id
        LEFT JOIN media_types mt ON mo.media_id = mt.media_id
        WHERE mo.media_date BETWEEN :start_date AND :end_date
        AND mt.media_type IN (:selected_media_types)
        AND mp.media_program IN (:selected_programs)";


    // Prepare and execute the SQL query
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);
    $stmt->bindParam(':selected_media_types', $selected_media_types_str, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 2000);
    $stmt->bindParam(':selected_programs', $selected_programs_str, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 2000);
    
    $stmt->execute();

    echo "<table>";
echo "<tr><th>#</th><th>Title</th><th>Output</th><th>URL</th><th>Date</th><th>Author</th><th>Program</th><th>Copies</th></tr>";

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

    echo "<td>" . $program_row['program'] . "</td>";
    echo "<td>" . $copies_row['copies'] . "</td>";
    echo "</tr>";
    $row_num++; // increment the row number for the next iteration
}

// Finish building
echo "</table>";
}
?>


