<?php

get_header();

$options = get_option('books_settings_options');

$categories = get_terms(array(
    'taxonomy' => 'categories',
    'hide_empty' => 0,
    'order' => 'ASC'
));

$categories_arr = [];

foreach ($categories as $category) {
    $categories_arr[] = [
        'slug' =>  $category->slug,
        'name' =>  $category->name,
    ];
}
$query=new WP_Query(array('post_type' => 'book',));
$number_of_posts = $query->found_posts;
?>

<div class="wrapper oib">
<h1><?php echo $options['title_for_page']; ?></h1>

<h2  class="oib-books-title"><?php echo $options['title_for_books']; ?></h2>


<div class="container">
<div id="oib-filters" class="oib-button-group">

  <button class="oib-button btn btn-danger btn-primary" data-filter="*"><?php esc_html_e('show all', 'oib') ?></button>

  <?php
    foreach ($categories_arr as $category) {?>
     <button class="oib-button btn btn-primary" data-filter=".<?php echo $category['slug'] ?>"><?php echo $category['name'] ?></button>
    <?php } ?>
</div>
</div>
<div class="oib-books-description"><?php echo $options['description_for_books']; ?></div>

<div class="oib-search-wrapper">
<div class="oib-search">
    <div class="oib-search-close" id="oib-quicksearch-close"><?php esc_html_e('Clear', 'oib') ?></div>
    <input class="oib-search-input" type="text" id="oib-quicksearch" placeholder="<?php esc_html_e('Search ...', 'oib') ?>" />  
    <div class="oib-search-number"><?php echo '1-'.$number_of_posts.' '.__('of', 'oib').' '.$number_of_posts; ?></div>
</div>
</div>

<div class="container">
    <div class="oib-grid">
    <?php

        if(have_posts()){

            while(have_posts()){ the_post();
                $preview_link = get_field( "preview_link" );
                $author_name = get_field( "author_name" );
                $cat_slug = wp_get_post_terms( get_the_ID(), 'categories', array('fields' => 'slugs'));
                $cat_name = wp_get_post_terms( get_the_ID(), 'categories', array('fields' => 'names'));

                $attr = array(
                    'class'	=> 'align-left',
                    'alt'=> trim(strip_tags(get_the_title())),
                    
                );
            ?>
            

                <div class="oib-book-item <?php echo implode(" ", $cat_slug) ?>">
                    <?php the_post_thumbnail([178, 235], $attr); ?>

                    <div class="oib-book-item-info">
                    
                        <?php 
                        if($cat_name){ ?>
                            <div class="oib-book-item-category">
                                <?php echo $cat_name[0]; ?>
                            </div>
                        <?php } ?>

                        <div class="oib-book-item-links">
                            <a href="<?php the_permalink(); ?>"><?php esc_html_e('Details', 'oib') ?></a>


                            <?php if($preview_link){ ?>
                                <div class="oib-book-item-line">|</div>
                                <div class="oib-book-item-line">|</div>
                                <div class="oib-book-item-line">|</div>
                                <a href="<?php echo $preview_link; ?>"><?php esc_html_e('Preview', 'oib') ?></a>
                            <?php } ?>
                        </div>

                        <div class="oib-book-item-noindex">
                        <noindex>
                        <?php if($author_name){ echo $author_name; } ?>
                        <?php if(the_title()){ echo the_title(); } ?>
                        </noindex> 
                        </div>
                    
                    </div>
                </div>
                <?php
                

            }

        } else {
            echo esc_html__('No posts', 'oib');
        }
    ?>
    </div>    
    </div>
</div>






<?php get_footer();
