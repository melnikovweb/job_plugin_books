<?php
get_header();

$author_name = get_field( "author_name" );
$add_to_cart_link = get_field( "add_to_cart_link" );
$publisher = get_field( "publisher" );
$imprint = get_field( "imprint" );
$pages = get_field( "pages" );
$cloth_isbn = get_field( "cloth_isbn" );
$ebook_isbn = get_field( "ebook_isbn" );
$preview_link = get_field( "preview_link" );
$description = get_field( "description" );
$about_the_author = get_field( "about_the_author" );
$awards = get_field( "awards" );
$reviews = get_field( "reviews" );

if(get_field( "published" ))
$published = str_split(get_field( "published" ), 2);

?>
<div class="wrapper oib-single">
    <div class="oib-single-row">
        <div class="oib-single-left-column">
            <div class="oib-single-thumbnail">
                <?php the_post_thumbnail([292, 445]); ?>
            </div>
        </div>

        <div class="oib-single-midle-column">
            <h1><?php the_title(); ?></h1>
            <?php if($author_name){ ?>
              <div class="oib-single-author"><?php echo $author_name; ?></div>
            <?php } if($add_to_cart_link){ ?>
              <a class="oib-single-cart btn btn-danger" target="_blank" href="<?php echo $add_to_cart_link; ?>"><?php esc_html_e('Add to Cart', 'oib') ?></a>
            <?php } ?>
        </div>

        <div class="oib-single-right-column">
            <div class="oib-single-info">
            <?php 
            if($publisher){
                echo '<div>'; 
                esc_html_e('Publisher: ', 'oib');
                echo '<strong>'.$publisher.'</strong></div>';} 
            if($imprint){
                echo '<div>'; 
                esc_html_e('Imprint: ', 'oib');
                echo '<strong>'.$imprint.'</strong></div>';} 
            if($published){
                echo '<div>'; 
                esc_html_e('Published: ', 'oib');
                echo '<strong>'.$published[2].'/'.$published[0].$published[1].'</strong></div>';} 
            if($pages){
                echo '<div>'; 
                esc_html_e('Pages: ', 'oib');
                echo '<strong>'.$pages.'</strong></div>';} 
            if($cloth_isbn){
                echo '<div>'; 
                esc_html_e('Cloth ISBN: ', 'oib');
                echo '<strong>'.$cloth_isbn.'</strong></div>';} 
            if($ebook_isbn){
                echo '<div>'; 
                esc_html_e('Ebook ISBN: ', 'oib');
                echo '<strong>'.$ebook_isbn.'</strong></div>';} 
            ?>
            </div>
            <div class="oib-single-links">
                <?php if($preview_link){ ?>
                    <a class="btn btn-outline-primary" target="_blank" href="<?php echo $preview_link; ?>"><?php esc_html_e('Preview', 'oib') ?></a>
                <?php } ?>
                <a class="btn btn-outline-primary" href="<?php echo get_post_type_archive_link('book'); ?>"><?php esc_html_e('See All', 'oib') ?></a>
            </div>
        </div>
    </div>

    <div class="oib-single-descriptions">
        <?php
        if($description){ ?>
            <h2 class="oib-single-title"><?php esc_html_e('Description', 'oib') ?></h2>
            <?php
                echo $description;
            }
        if($about_the_author){ ?>
            <h2 class="oib-single-title"><?php esc_html_e('About The Author', 'oib') ?></h2>
            <?php
                echo $about_the_author;
            }
        if($awards){ ?>
            <h2 class="oib-single-title"><?php esc_html_e('Awards', 'oib') ?></h2>
            <?php
                echo $awards;
            }
        if($reviews){ ?>
            <h2 class="oib-single-title"><?php esc_html_e('Reviews', 'oib') ?></h2>
            <?php
                echo $reviews;
            }
            ?>


        <div class="oib-single-print-wrapper">
            <h2 class="oib-single-title"><?php esc_html_e('Related titles', 'oib') ?></h2>
            <a class="oib-single-print btn btn-primary" href="#" onclick="window.print();return false;"><?php esc_html_e('Print This Page?', 'oib') ?></a>
        </div>
    </div>

    <div class="oib-single-related-wrapper">
    <?php 
$tags = get_the_tags();
$tags_arr = '';
if($tags){
foreach ($tags as $tag) { 
    $tags_arr .= $tag->slug.',';
}
} else {
    $tags_arr = ''  ;
}
$query=new WP_Query(array('post_type' => 'book', 'tag' => $tags_arr, 'posts_per_page' => 7,));

if($query->have_posts()){
while ( $query->have_posts() ) : $query->the_post();


$cat_slug = wp_get_post_terms( get_the_ID(), 'categories', array('fields' => 'slugs'));
$cat_name = wp_get_post_terms( get_the_ID(), 'categories', array('fields' => 'names'));
?>

<div class="oib-single-related-item <?php echo implode(" ", $cat_slug) ?>">
    <?php the_post_thumbnail(array(178, 235)); ?>

    <div class="oib-single-related-item-info">
    
        <?php 
        if($cat_name){ ?>
            <div class="oib-single-related-item-category">
                <?php echo $cat_name[0]; ?>
            </div>
        <?php } ?>

        <div class="oib-single-related-item-links">
            <a href="<?php the_permalink(); ?>">Details</a>


            <?php if($preview_link){ ?>
                <div class="oib-single-related-item-line">|</div>
                <div class="oib-single-related-item-line">|</div>
                <div class="oib-single-related-item-line">|</div>
                <a href="<?php echo $preview_link; ?>">Preview</a>
            <?php } ?>
        </div>
    
        </div>
</div>

<?php 
endwhile;
wp_reset_postdata();
}

?>
</div>











</div>

<?php get_footer();
