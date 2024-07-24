<?php
//init phpspreadsheet

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

$logs = "--- Start parsing ---" . date('d.m.Y H:i:s') . "\n"; 

$path_home = dirname(dirname(dirname(dirname( __DIR__ ))));
require($path_home.'/wp-load.php');

$options = get_option('books_settings_options');

if($options['path_to_image_files'] && $options['path_to_xlsx_file']){

$inputFileName = $path_home.$options['path_to_xlsx_file'];
$image_host = $options['path_to_image_files'];

$logFileName = __DIR__."/logs.txt"; 

$inputFileType = 'Xlsx';

class MyReadFilter implements IReadFilter
{
    public $fromRow;
    public $toRow;

    public function readCell($columnAddress, $row, $worksheetName = '')
    {   
        $fromRow = $this->fromRow;
        $toRow = $this->toRow;

        if ($row >= $fromRow && $row < $toRow) {
            return true;
        }
            return false;
    }

}
function getSheetData($filterSubset, $inputFileType, $inputFileName){

    $reader = IOFactory::createReader($inputFileType);
    $reader->setReadFilter($filterSubset);
    $spreadsheet = $reader->load($inputFileName);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    return $sheetData;
}
function getPostId($title_group_id){

    global $wpdb;
    $tbl = $wpdb->prefix.'postmeta';
    $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $tbl where meta_key ='title_group_id' and meta_value like '%%%s%%'", $title_group_id );
    $get_values = $wpdb->get_col( $prepare_guery );

    if ($get_values){
    return intval(implode("", $get_values));
    } else {
    return '';
    }

}
function updatePostImage($isbn13, $new_post_id, $upload_file){
                  
    //if succesfull insert the new file into the media library (create a new attachment post type)

    $wp_filetype = wp_check_filetype($isbn13.'.jpg', null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_parent' => $new_post_id,
        'post_title' => preg_replace('/\.[^.]+$/', '', $isbn13),
        'post_content' => '',
        'post_status' => 'inherit'
    );


    $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $new_post_id );
    if (!is_wp_error($attachment_id)) {

        require_once(ABSPATH . "wp-admin" . '/includes/image.php');

        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
        wp_update_attachment_metadata( $attachment_id,  $attachment_data );
        set_post_thumbnail( $new_post_id, $attachment_id );
    }
  }
$filterSubset = new MyReadFilter();

$rows_from = 15;
$rows_to = 400;

if($options['books_from']) {
    $rows_from = ($options['books_from']+13);
}

if($options['books_to']) {
    $rows_to = $options['books_to'];
}

for (; $rows_from <= $rows_to; $rows_from+=10) {

$filterSubset->fromRow=$rows_from-13;
$filterSubset->toRow=$rows_from;

$sheetData = getSheetData($filterSubset, $inputFileType, $inputFileName);

$sheetData = array_slice($sheetData, $rows_from-13); 






if (!empty($sheetData)) {

    $prevID = '';

    foreach ($sheetData as $value) {

        if ($prevID === $value['A'] or $value['A'] == ''){
            continue;
        } else {
            $prevID = $value['A'];
        }
           
            
        
        if ($value['AY'] !== ''){
            $awards = $value['AY'];
        } else {
            $awards = '';
        }

        $isbn13 =  $value['A'];

        $post_id = getPostId($value['A']);

        $reviews = '';
        $reviews .= $value['AD'] ? $value['AD'] : '';
        $reviews .= $value['AF'] ? '<br><br>'. $value['AF'] : '';
        $reviews .= $value['AH'] ? '<br><br>'. $value['AH'] : '';
        $reviews .= $value['AJ'] ? '<br><br>'. $value['AJ'] : '';
        $reviews .= $value['AL'] ? '<br><br>'. $value['AL'] : '';
        $reviews .= $value['AN'] ? '<br><br>'. $value['AN'] : '';
        $reviews .= $value['AP'] ? '<br><br>'. $value['AP'] : '';
        $reviews .= $value['AR'] ? '<br><br>'. $value['AR'] : '';
        $reviews .= $value['AT'] ? '<br><br>'. $value['AT'] : '';
        $reviews .= $value['AV'] ? '<br><br>'. $value['AV'] : '';
        $reviews .= $value['AX'] ? '<br><br>'. $value['AX'] : '';

        $awards = '';

        if ($value['AY']){
            $awards .= $value['AY'].', '.$value['BC'].' ('.$value['AZ'].') ';
        }
        if ($value['BD']){
            $awards .= $value['BD'].', '.$value['BH'].' ('.$value['BE'].')';
        }

        if (!$post_id){
             //create post if doesn't exist

            $my_post = array(
                'post_title'    => $value['C'],
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type' => 'book'
            );

            $new_post_id = wp_insert_post( $my_post );


    
            $post_fields = array(
                'author_name'       => $value['I'],
                'add_to_cart_link'  => $value['CA'],
                'preview_link'      => $value['BY'],
                'publisher'         => $value['E'],
                'imprint'           => $value['F'],
                'published'         => $value['U'],
                'pages'             => $value['Z'],
                'description'       => $value['AB'],
                'about_the_author'  => $value['J'],
                'reviews'           => $reviews,
                'cloth_isbn'        => $value['A'],
                'title_group_id'    => $value['A'],

                'awards'            => $awards,
                'subtitle'          => $value['D'],
                'book_price'        => $value['T'],
                'book_status'       => $value['B'],
                
            );

            foreach($post_fields as $key => $value) {
                update_post_meta( $new_post_id, $key, $value );
            }
            

          $books_category = [];

            if(null !== ([$post_fields['book_status']])){
                if(strcasecmp(implode([$post_fields['book_status']]),"04 Active") == 0) {
                    array_push($books_category, "AVAILABLE TITLES");

                }
            }

            if(null !== ([$post_fields['awards']])){
                if(strlen(implode([$post_fields['awards']])) == 0 ) {

                } else {
                    array_push($books_category, "PRIZE-WINNING");
                }
            }

            if(null !== ([$post_fields['published']])){
                $date = (int)(implode([$post_fields['published']]));
                if($date >= 20200101) {
                    array_push($books_category, "FORTHCOMING & NEW");
                }
            }
       
            wp_set_post_terms( $new_post_id, $books_category, 'categories' );


            $attachment = new WP_Http();
            $attachment = $attachment->request( $image_host.$isbn13.'.jpg', array(
                'headers' => array( 
                    'X-app-action' => 'importing'
                ),
            ) );

            $upload_file = wp_upload_bits($isbn13.'.jpg', null, $attachment[ 'body' ]);

            if(!$upload_file['error']) {
                updatePostImage($isbn13, $new_post_id, $upload_file);
                $logs .= "image for post imported (WP_POST_ID=". $new_post_id .")  //"; 
            }
            $logs .= 'New Book with id="'.$new_post_id. '" created'. "\n"; 

        } else {
            //update post if exist
            $post_fields = array(
                'add_to_cart_link'  => $value['CA'],
                'awards'            => $awards,
                'reviews'           => $reviews,
            );
           
            if (get_post_meta( $post_id, 'add_to_cart_link' ) != [$post_fields['add_to_cart_link']] or
                get_post_meta( $post_id, 'awards' ) != [$post_fields['awards']] or
                get_post_meta( $post_id, 'reviews' ) != [$post_fields['reviews']]
            ){

                foreach($post_fields as $key => $value) {
                    update_post_meta( $post_id, $key, $value );
                }
                $logs .= 'Existing Book with id="'.$post_id. '" updated'." // \n"; 

            } else {
                $logs .= 'Book id="'.$post_id. '" not updated'." // \n"; 
            }
        } 
         
    }
};
}

$logs .= "\n all posts imported\n"; 
$logs .= "--- finish --- " . date('d.m.Y H:i:s') . "\n \n"; 
echo 'Successfully';
file_put_contents($logFileName, $logs); 
}