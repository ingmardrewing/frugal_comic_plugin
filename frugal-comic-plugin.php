<?php
/*
Plugin Name: Frugal Comic Plugin
Version: 20170211
*/
include 'FcpFormats.php';
include 'FcpContent.php';
include 'FcpCustomBox.php';
include 'FcpHead.php';
include 'FcpAdminMenu.php';

add_action( 'admin_menu', 'fcp_plugin_menu' );
add_action( 'add_meta_boxes', 'fcp_add_custom_box' );
add_action( 'save_post', 'fcp_save_postdata' );

add_action( 'wp_head', 'fcp_add_html_header_elements');
add_filter( 'the_content', 'fcp_modify_content');

/** Step 1. */
function fcp_plugin_menu() {
	add_options_page( 'Frugal Comic Plugin Options', 'Frugal Comic Plugin', 'manage_options', 'fcp-admin-menu', 'fcp_plugin_options' );
}

function fcp_plugin_options() {
  $admin_menu = new AdminMenu();
  $admin_menu->handle_request();
}

function fcp_modify_content( $content ){
  $c = new FcpContent();
  return $c->process_content( $content );
}

/* Adds a meta box to the post edit screen */
function fcp_add_custom_box() {
  $fcb = new FcpCustomBox();
  $fcb->add_box();
}

function fcp_add_html_header_elements (){
  $fh = new FcpHead();
  echo $fh->add_box();
}

/* save the image url of the comic page image */
function fcp_save_postdata( $post_id ) {
  if ( array_key_exists('fcp_field', $_POST ) ) {
    if( $_POST['fcp_field'] ){
      update_post_meta( $post_id,
          '_fcp_comic_image_url',
          $_POST['fcp_field']
      );
    }
    else {
      $content_post = get_post($post_id);
      $content = $content_post->post_content;
      $dom = new DOMDocument();
      $dom->loadHTML($content);
      $img = $dom->getElementsByTagName('img')->item(0);
      $src = NULL;
      if($img != NULL){
        $src = $img->getAttribute('src');
      }
      if( $src != NULL ) {
        update_post_meta( $post_id,
          '_fcp_comic_image_url',
          $src
        );
      }
    }
  }
}

#
#
#    if( empty( $next_post )){
#      $js = $this->get_comic_preload_js( $this->first_image_url , get_post_meta( $this->id, '_fcp_comic_image_url', true ) ) ;
#      return $js . $new_content ;
#    }
#
#    $js = $this->get_comic_preload_js( $next_comic_image_url ) ;
#    return $js . $new_content;
#
#
#
#  function get_comic_preload_js($next_img_url ) {
#    if( ! empty( $next_img_url ) ){
#      $format = FcpFormats::get_preload_js();
#      return sprintf( $format, $next_img_url );
#    }
#    return '';
#  }
#

?>




