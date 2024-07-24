<h1 class="oib_title"><?php esc_html_e('Books Settings', 'oib') ?></h1>
<?php settings_errors(); ?>
<div calss="oib_content"> 
    <form method="post" action="options.php">
        <?php 
            settings_fields('books_settings');
            do_settings_sections('oib_settings');
            submit_button();
        ?>
    </form>
</div>

