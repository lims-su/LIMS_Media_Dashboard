<?php
$page_title = "Get media from LIMSLB.COM";
require_once('functions.php');
require_once('config.php');
include("header.php"); 
echo'<body>';
$logout = getWebsiteRoot()."lims_media2/logout.php";
$isLoggedIn = isset($_SESSION['username']);
if (!isset($_SESSION['username'])&& basename($_SERVER['REQUEST_URI']) !== 'login.php') {
  header('Location: login.php');
  exit;}
$after_date = date('Y-m-d\TH:i:s', strtotime('-30 days'));
$url = 'https://limslb.com/wp-json/wp/v2/posts?categories_exclude=2485&after=' . $after_date . '&per_page=100&_embed';
$response = file_get_contents($url);
if (!$response) {
    echo "Failed to retrieve posts";
    exit;
} 
$posts = json_decode($response, true);
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Failed to connect to the database: " . $e->getMessage();
    die();
}
// Read media types from CSV file
$media_types = array();
$csv_file = 'media_types.csv';
$row = 1;
if (($handle = fopen($csv_file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($row == 1) { // Skip header row
            $row++;
            continue;
        }
        $media_types[$data[0]] = $data[1];
    }
    fclose($handle);
}
$media_programs = array();
$csv_file = 'media_programs.csv';
$row = 1;
if (($handle = fopen($csv_file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($row == 1) { // Skip header row
            $row++;
            continue;
        }
        $media_programs[$data[0]] = $data[1];
    }
    fclose($handle);
}
//print_r($media_types);
if (count($posts) > 0) {
    $counter = 0;
    foreach ($posts as $post) {
        // Check if the post URL exists in the media_output table
        // Prepare the SQL statement
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM media_output WHERE media_link = ?");
        $stmt->execute([$post['link']]);
        $count = $stmt->fetchColumn();
        if ($count == 0) {
            // Extract the post data
            $post_title = $post['title']['rendered'];
            $post_link = $post['link'];
            $post_date = date('Y-m-d', strtotime($post['date']));
            $post_categories = array_column($post['_embedded']['wp:term'][0], 'name');;
            $types_to_add = array();
            // Iterate over each element in $post_categories array
            foreach ($post_categories as $category) {
                // Check if the category exists as a key in $media_types array
                if (array_key_exists($category, $media_types)) {
                    // Add the corresponding value to $types_to_add array
                    $types_to_add[] = $media_types[$category];
                }
            }
            $programs_to_add = array();
            foreach ($post_categories as $category) {
                // Check if the category exists as a key in $media_types array
                if (array_key_exists($category, $media_programs)) {
                    // Add the corresponding value to $types_to_add array
                    $programs_to_add[] = $media_programs[$category];
                }
            }
            // Insert data into the 'media_output' table
            $title = $post_title;
            $media_date = $post_date;
            $media_link = $post_link;
            $stmt = $pdo->prepare("INSERT INTO media_output (title, media_date, media_link) VALUES (:title, :media_date, :media_link)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':media_date', $media_date);
            $stmt->bindParam(':media_link', $media_link);
            $stmt->execute();
            // Get the 'media_id' of the newly inserted row
            $media_id = $pdo->lastInsertId();
            // Insert data into the 'media_types' table
            foreach ($types_to_add as $type) {
                $stmt = $pdo->prepare("INSERT INTO media_types (media_id, media_type) VALUES (:media_id, :media_type)");
                $stmt->bindParam(':media_id', $media_id);
                $stmt->bindParam(':media_type', $type);
                $stmt->execute();
            }
            foreach ($programs_to_add as $program) {
                $stmt = $pdo->prepare("INSERT INTO media_programs (media_id, media_program) VALUES (:media_id, :media_program)");
                $stmt->bindParam(':media_id', $media_id);
                $stmt->bindParam(':media_program', $program);
                $stmt->execute();
            }
            $counter++;
        }
    }
}
echo "<div class='content'>";
echo "<p>Inserted " . $counter . " new media items into the database.</p>";
echo "<p><a href='show_all.php' class='button'>Back to Media Dashboard</a></p>";
echo "</div>";
echo "</body>";
?>