<? php //this is test code for displaying retrieved data in table! not save on db
$after_date = date( 'Y-m-d\TH:i:s', strtotime( '-30 days' ) );
$url = 'https://limslb.com/wp-json/wp/v2/posts?categories_exclude=2485&after=' . $after_date . '&per_page=100';
echo $url;
$response = file_get_contents( $url );
if ( ! $response ) {
  echo "Failed to retrieve posts";
} else {
  $posts = json_decode( $response, true );

  if ( count( $posts ) > 0 ) {
    echo "<table><thead><tr><th>#</th><th>Title</th><th>URL</th><th>Date and Time</th><th>Categories</th><th>Tags</th></tr></thead><tbody>";
    $counter = 0;
    foreach ( $posts as $post ) {
        $counter++;
        echo "<tr><td>" . $counter . "</td><td>" . $post['title']['rendered'] . "</td><td><a href='" . $post['link'] . "'>" . $post['link'] . "</a></td><td>" . $post['date'] . "</td><td>";

        // Get the categories for the post
        $post_categories_url = 'https://limslb.com/wp-json/wp/v2/posts/' . $post['id'] . '/categories';
        $post_categories_response = file_get_contents( $post_categories_url );
        $post_categories = json_decode( $post_categories_response, true );

        if ( count( $post_categories ) > 0 ) {
            $category_ids = array_column($post_categories, 'id');
            echo implode(', ', $category_ids);
        }
          
        echo "</td><td>";
        if (isset($post['_embedded']['wp:term'][1])) {
            $tags = array_column($post['_embedded']['wp:term'][1], 'name');
            echo implode(', ', $tags);
        }
          
        echo "</td></tr>";
    }

    echo "</tbody></table>";
  } else {
    echo "No posts found";
  }
}
?>