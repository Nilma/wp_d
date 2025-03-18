<?php
/*
Plugin Name: Footer Text Plugin
Author: Nilma Abbas
Author URI: www.example.com
Description: Adds text at bottom of posts.
Version: 1.0
*/
?>
<?php
// Function to add custom text to post content
function add_footer_text($content) {
    return $content . '<p>Custom footer text by Nilma Abbas.</p>';
}
// Hook function to 'the_content'
add_filter('the_content', 'add_footer_text');
?>