<?php
function getWebsiteRoot() {
  $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
  $host = $_SERVER['HTTP_HOST'];
  $uri = $_SERVER['REQUEST_URI'];
  $parts = explode('?', $uri, 2);
  $path = $parts[0];
  return $protocol . "://" . $host . "/" ;
}


function process_posts() {
    $after_date = date('Y-m-d\TH:i:s', strtotime('-30 days'));
    $url = 'https://limslb.com/wp-json/wp/v2/posts?categories_exclude=2485&after=' . $after_date . '&per_page=100&_embed';

    $response = file_get_contents($url);
    if (!$response) {
        echo "Failed to retrieve posts";
    } else {
        $posts = json_decode($response, true);

        // Establish a connection to the PostgreSQL database
        $host = 'localhost';
        $dbname = 'LIMS_Media';
        $user = 'postgres';
        $password = '1234';

        try {
            $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Failed to connect to the database: " . $e->getMessage();
            die();
        }

        if (count($posts) > 0) {
            $counter = 0;
            foreach ($posts as $post) {
                $counter++;

                // Check if the post URL exists in the media_output table
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM media_output WHERE media_link = ?");
                $stmt->execute([$post['link']]);
                $count = $stmt->fetchColumn();

                if ($count == 0) {
                    // Extract the relevant data from the post and insert it into the media_output table
                    $title = $post['title']['rendered'];
                    $media_link = $post['link'];
                    $media_date = date('Y-m-d', strtotime($post['date']));

                    $stmt = $pdo->prepare("INSERT INTO media_output (title, media_link, media_date) VALUES (?, ?, ?)");
                    $stmt->execute([$title, $media_link, $media_date]);
                }
            }

            return "Processed $counter posts";
        } else {
            return "No posts found";
        }
    }
}
?>
