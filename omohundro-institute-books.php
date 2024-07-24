<?php
/*
Plugin Name: Omohundro institute books
Description: Omohundro institute books.
Version: 1.0.1
Author: A.M.
Text Domain: oib
Domain Path: /lang
*/

if(!defined('ABSPATH')){
    exit;
}

class OmohundroInstituteBooks
{

    public function register(){

        //register book post type
        add_action('init', [$this,'custom_post_type']);

        //enqueue admin
        add_action('admin_enqueue_scripts', [$this,'enqueue_admin']);

        //enqueue front
        add_action('wp_enqueue_scripts', [$this,'enqueue_front']);

        //load book template
        add_filter('template_include', [$this, 'book_template']);

        //Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);

        //Add links
        add_filter('plugin_action_links_'.plugin_basename(__FILE__), [$this, 'add_plugin_settings_link']);

        add_action('admin_init', [$this, 'settings_init']);


    }

    public function add_plugin_settings_link($links){
        $custom_link = '<a href="admin.php?page=oib_settings">'.esc_html__('Settings', 'oib').'</a>';
        array_push($links, $custom_link);
        return $links;
    }

    static function activation(){
        //update rewrite rules
        flush_rewrite_rules();
    }

    static function deactivation(){
        //update rewrite rules
        flush_rewrite_rules();
    }

        //register settings
    public function settings_init(){

        register_setting('books_settings', 'books_settings_options');

        add_settings_section('books_settings_section', esc_html__('Settings', 'oib'), [$this, 'settings_section_html'], 'oib_settings');

        add_settings_field('title_for_books', esc_html__('Books title', 'oib'), [$this, 'title_for_books_html'], 'oib_settings', 'books_settings_section');
        add_settings_field('description_for_books', esc_html__('Books description', 'oib'), [$this, 'description_for_books_html'], 'oib_settings', 'books_settings_section');
        add_settings_field('path_to_xlsx_file', esc_html__('Path to xlsx file(import)', 'oib'), [$this, 'path_to_xlsx_file_html'], 'oib_settings', 'books_settings_section');
        add_settings_field('path_to_image_files', esc_html__('Path to image files(import)', 'oib'), [$this, 'path_to_image_files_html'], 'oib_settings', 'books_settings_section');
        add_settings_field('books_from', esc_html__('Minimum number of rows in a sheet(Parse Books from row)', 'oib'), [$this, 'books_from_html'], 'oib_settings', 'books_settings_section');
        add_settings_field('books_to', esc_html__('Maximum number of rows in a sheet(Parse Books to row)', 'oib'), [$this, 'books_to_html'], 'oib_settings', 'books_settings_section');
        add_settings_field('link_for_manuscript', esc_html__('Link for MANUSCRIPT SUBMISSIONS', 'oib'), [$this, 'link_for_manuscript_html'], 'oib_settings', 'books_settings_section');
        add_settings_field('link_for_staff', esc_html__('Link for STAFF AND COMMITTEE', 'oib'), [$this, 'link_for_staff_html'], 'oib_settings', 'books_settings_section');
    }

    public function settings_section_html(){
        echo esc_html__('Book catalog and parser settings', 'oib');
    }

    public function link_for_manuscript_html(){ 

        $options = get_option('books_settings_options');?>
        <input type="text" name="books_settings_options[link_for_manuscript]" value="<?php echo isset($options['link_for_manuscript']) ? $options['link_for_manuscript'] : "" ?>" />
  
    <?php }

    public function link_for_staff_html(){ 

        $options = get_option('books_settings_options');?>
        <input type="text" name="books_settings_options[link_for_staff]" value="<?php echo isset($options['link_for_staff']) ? $options['link_for_staff'] : "" ?>" />

    <?php }

    public function title_for_books_html(){ 

        $options = get_option('books_settings_options');?>
        <input type="text" name="books_settings_options[title_for_books]" value="<?php echo isset($options['title_for_books']) ? $options['title_for_books'] : "" ?>" />

    <?php }

    public function description_for_books_html(){ 

        $options = get_option('books_settings_options');?>
        <input type="text" name="books_settings_options[description_for_books]" value="<?php echo isset($options['description_for_books']) ? $options['description_for_books'] : "" ?>" />

    <?php }

    public function path_to_xlsx_file_html(){ 

        $options = get_option('books_settings_options');?>
        <input required  type="text" name="books_settings_options[path_to_xlsx_file]" value="<?php echo isset($options['path_to_xlsx_file']) ? $options['path_to_xlsx_file'] : "" ?>" />

    <?php }

    public function path_to_image_files_html(){ 

        $options = get_option('books_settings_options');?>
        <input required  type="text" placeholder="https://uncpress-us.imgix.net/covers/" name="books_settings_options[path_to_image_files]" value="<?php echo isset($options['path_to_image_files']) ? $options['path_to_image_files'] : "" ?>" />

    <?php }

    public function books_from_html(){ 

        $options = get_option('books_settings_options');?>
        <input type="number" placeholder="leave blank if not needed" name="books_settings_options[books_from]" value="<?php echo isset($options['books_from']) ? $options['books_from'] : "" ?>" />

    <?php }

    public function books_to_html(){ 

        $options = get_option('books_settings_options');?>
        <input type="number" placeholder="leave blank if not needed" name="books_settings_options[books_to]" value="<?php echo isset($options['books_to']) ? $options['books_to'] : "" ?>" />

    <?php }

    //Add menu page
    public function add_admin_menu(){
        add_menu_page(
            esc_html__( 'Institute Books Settings Page', 'oib' ),
            esc_html__('Books Settings','oib'),
            'manage_options',
            'oib_settings',
            [$this, 'oib_page'],
            'dashicons-book',
            100
        );
    }

    //Books Admin Html
    public function oib_page(){
        require_once plugin_dir_path(__FILE__).'admin/admin.php';
    }

        //Custom template for books
    public function book_template($template){

        if(is_post_type_archive('book')){

            $exist_in_theme = get_template_directory().'/templates/archive-book/archive-book.php';

            if(file_exists($exist_in_theme)){
                return $exist_in_theme;
            } else {
                return plugin_dir_path(__FILE__).'templates/archive-book.php';
            }

        } elseif (is_singular( 'book' )){

            $exist_in_theme = get_template_directory().'/templates/single-book/single-book.php';

            if(file_exists($exist_in_theme)){
                return $exist_in_theme;
            } else {
                return plugin_dir_path(__FILE__).'templates/single-book.php';
            }

        }
        return $template;
    }

        
    public function enqueue_admin(){
        //init admin assets
    }
        
    public function enqueue_front(){
        //init front assets
        wp_enqueue_style('oibStyle', plugins_url('assets/css/style.css', __FILE__));
        wp_enqueue_script('oibIsotope', plugins_url('assets/js/isotope.js', __FILE__), array('jquery'), null, true);
        wp_enqueue_script('oibScript', plugins_url('assets/js/front.js', __FILE__), array('jquery'), null, true);
    }

    public function custom_post_type(){
        register_post_type('book', [
            'public'        => true,
            "has_archive"   => true,
            'rewrite'       => ['slug'=>'books'],
            'label'         => esc_html__('Books', 'oib'),
            'supports'      => ["title", "editor", "excerpt", "trackbacks", "custom-fields", "revisions", "thumbnail", "author", "page-attributes", "post_tag"],
            'taxonomies' => array('post_tag'),
    
        ]);

        $labels = array(
            'name'              => _x( 'Book Categories', 'taxonomy general name', 'oib' ),
            'singular_name'     => _x( 'Book Category', 'taxonomy singular name', 'oib' ),
            'search_items'      => __( 'Search Book Categories', 'oib' ),
            'all_items'         => __( 'All Book Categories', 'oib' ),
            'view_item'         => __( 'View Book Category', 'oib' ),
            'parent_item'       => __( 'Parent Book Category', 'oib' ),
            'parent_item_colon' => __( 'Parent Book Category:', 'oib' ),
            'edit_item'         => __( 'Edit Book Category', 'oib' ),
            'update_item'       => __( 'Update Book Category', 'oib' ),
            'add_new_item'      => __( 'Add New Book Category', 'oib' ),
            'new_item_name'     => __( 'New Book Category Name', 'oib' ),
            'not_found'         => __( 'No Book Categories Found', 'oib' ),
            'back_to_items'     => __( 'Back to Book Categories', 'oib' ),
            'menu_name'         => __( 'Book Category', 'oib' ),
        );
    
        $args = array(
            'labels'            => $labels,
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'category' ),
            'show_in_rest'      => true,
        );

        register_taxonomy('categories', 'book', $args);
    }
}

if(class_exists('OmohundroInstituteBooks')){
   $omohundroInstituteBooks = new OmohundroInstituteBooks();
   $omohundroInstituteBooks->register();
}



register_activation_hook(__FILE__, [ $omohundroInstituteBooks, 'activation' ]);
register_deactivation_hook(__FILE__, [ $omohundroInstituteBooks, 'deactivation' ]); 

include __DIR__.'/parser/ParserLauncher.php';

// add fields
if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array(
        'key' => 'group_6408defb7e647',
        'title' => 'Post type: Book',
        'fields' => array(
            array(
                'key' => 'field_6408defb3335d',
                'label' => 'Publication info',
                'name' => '',
                'aria-label' => '',
                'type' => 'accordion',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'open' => 0,
                'multi_expand' => 0,
                'endpoint' => 0,
            ),
            array(
				'key' => 'field_651c04e5a0ec5',
				'label' => 'Featured book',
				'name' => 'featured_book',
				'aria-label' => '',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => false,
				'ui' => 0,
				'ui_on_text' => '',
				'ui_off_text' => '',
			),
            array(
                'key' => 'field_6408df613335e',
                'label' => 'Publisher',
                'name' => 'publisher',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_6408dfa53335f',
                'label' => 'Imprint',
                'name' => 'imprint',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_6408dfb533360',
                'label' => 'Published (date)',
                'name' => 'published',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_6408dfb917767',
                'label' => 'Paperback Publication Date',
                'name' => 'paperback_published',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_6408dfb113451',
                'label' => 'Reprint Date',
                'name' => 'reprint_date',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_64178c549c39d',
                'label' => 'Pages',
                'name' => 'pages',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_6408dfdf33361',
                'label' => 'Cloth ISBN',
                'name' => 'cloth_isbn',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_6408dfb005454',
                'label' => 'Paperback ISBN',
                'name' => 'paperback_isbn',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_6408dff233362',
                'label' => 'eBook ISBN',
                'name' => 'ebook_isbn',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_6421bb37c46ee',
                'label' => 'Title Group Id',
                'name' => 'title_group_id',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_640ed8a2749d9',
                'label' => 'Main info',
                'name' => '',
                'aria-label' => '',
                'type' => 'accordion',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'open' => 0,
                'multi_expand' => 0,
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_64ec4e4d741df',
                'label' => 'Subtitle',
                'name' => 'subtitle',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_640ed8dd749da',
                'label' => 'Author name',
                'name' => 'author_name',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '33',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_640ed8dd105sd',
                'label' => 'Editors',
                'name' => 'editors',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '33',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_640ed905749db',
                'label' => 'Add to cart link',
                'name' => 'add_to_cart_link',
                'aria-label' => '',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '33',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_640ed933749dc',
                'label' => 'Preview link',
                'name' => 'preview_link',
                'aria-label' => '',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '33',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_64ec4ff9741e0',
                'label' => 'Book status',
                'name' => 'book_status',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_64ec504e741e2',
                'label' => 'Book Price',
                'name' => 'book_price',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_640eda3989276',
                'label' => 'Descriptions',
                'name' => '',
                'aria-label' => '',
                'type' => 'accordion',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'open' => 0,
                'multi_expand' => 0,
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_640eda9489277',
                'label' => 'Description',
                'name' => 'description',
                'aria-label' => '',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 0,
            ),
            array(
                'key' => 'field_640edaed89278',
                'label' => 'About The Author',
                'name' => 'about_the_author',
                'aria-label' => '',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 0,
            ),
            array(
                'key' => 'field_640edb0689279',
                'label' => 'Awards',
                'name' => 'awards',
                'aria-label' => '',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 0,
            ),
            array(
                'key' => 'field_640edb188927a',
                'label' => 'Reviews',
                'name' => 'reviews',
                'aria-label' => '',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 0,
            ),
            array(
                'key' => 'field_6429da338a4c7',
                'label' => 'Book Custom Fields',
                'name' => '',
                'aria-label' => '',
                'type' => 'accordion',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'open' => 0,
                'multi_expand' => 0,
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_6408dfa59c5l1',
                'label' => 'Video Heading',
                'name' => 'video_heading',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_6429da688a4c8',
                'label' => 'Video Frame',
                'name' => 'video_frame',
                'aria-label' => '',
                'type' => 'oembed',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'width' => '',
                'height' => '',
            ),
            array(
                'key' => 'field_6429da6877f73',
                'label' => 'Custom content',
                'name' => 'custom_content',
                'aria-label' => '',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'width' => '',
                'height' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'book',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ));
    
    endif;		

    