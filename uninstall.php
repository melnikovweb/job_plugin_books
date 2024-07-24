<?php

if(!defined('WP_UNINSTALL_PLUGIN')){
    exit;
}

//delete posts

// global $wpdb;
// $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type IN ('book');");

//or

$books = get_posts(['post_type'=>'book','numberposts'=>-1]);
foreach($books as $book){
    wp_delete_post($book->ID, true);
}

//remove tax

