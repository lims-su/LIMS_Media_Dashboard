<?php
$page_title = "LIME Media Dashboard - Reporting";
require_once('functions.php');
require_once('config.php');
include("header.php"); 
echo'<body>';
// Query the media_output and media_programs tables to retrieve all rows



$stmt = $db->query("SELECT media_output.media_id, title, media_date, author, media_link
FROM media_output 
WHERE media_date BETWEEN '2022-01-01' AND '2022-12-31'
ORDER BY media_output.media_date desc, media_output.media_id DESC
");//LIMIT 500

echo "<div class='content'>";
echo
'<p><label for="start_date">Select starting date:</label>
<input type="date" id="start_date" name="start_date">
<label for="end_date">Select ending date:</label>
<input type="date" id="end_date" name="end_date"></p>';
$stmt_typeList = $db->query("SELECT type_id, type_name FROM types_list");
$stmt_progList = $db->query("SELECT program_id, program_name FROM programs_list");
$typeNames = array();
$programNames = array();
?>

<h2>Select Outputs</h2>    
    <fieldset>
        <legend>Select output:</legend>
        <?php while ($row1 = $stmt_typeList->fetch(PDO::FETCH_ASSOC)) { ?>
            <div class='media_type'>
                <input type='checkbox' id='type_<?php echo $row1['type_id']; ?>' name='media_type[]' value='<?php echo $row1['type_name']; ?>' class='styled-checkbox'<?php if (in_array($row1['type_name'], $typeNames)) { echo ' checked'; } ?>>
                <label for='type_<?php echo $row1['type_id']; ?>' class='styled-label'><?php echo $row1['type_name']; ?></label>
            </div>
        <?php } ?>
    </fieldset> 
    <h2> Select Programs</h2>
    <fieldset>
        <legend>Select programs:</legend>
        <?php while ($row = $stmt_progList->fetch(PDO::FETCH_ASSOC)) { ?>
            <div class='prog'>
                <input type='checkbox' id='prog_<?php echo $row['program_id']; ?>' name='program[]' value='<?php echo $row['program_name']; ?>' class='styled-checkbox'<?php if (in_array($row['program_name'], $programNames)) { echo ' checked'; } ?>>
                <label for='prog_<?php echo $row['program_id']; ?>' class='styled-label'><?php echo $row['program_name']; ?></label>
            </div>
        <?php } ?>
    </fieldset>
