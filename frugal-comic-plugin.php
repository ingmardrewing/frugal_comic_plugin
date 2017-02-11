<?php
/*
Plugin Name: Frugal Comic Plugin
Version: 20170211
*/
include 'FcpFormats.php';
include 'FcpContent.php';
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
    $screens = array( 'post', 'my_cpt' );
    foreach ( $screens as $screen ) {
        add_meta_box(
            'fcp_box_id',            // Unique ID
            'Frugal Comic Plugin',      // Box title
            'fcp_inner_custom_box',  // Content callback
             $screen                      // post type
        );
    }
}

function fcp_add_html_header_elements (){
  global $post;
  echo fcp_get_header_link_tags( $post );
}

function fcp_get_header_link_tags( $post ){
  $start_url
    = get_permalink(get_option('fcp_post_id_of_first_issue'))  ;
  $start_title
    = get_the_title(get_option('fcp_post_id_of_first_issue'));

  $prev_url = get_permalink(get_adjacent_post(false,'',true))  ;
  $pref_title = get_the_title(get_adjacent_post(false,'',true));
  $next_url = get_permalink(get_adjacent_post(false,'',false));
  $this_url = get_permalink($post);

  $next_line = '';
  if( $next_url != $this_url ){
    $next_issue_url = get_permalink(get_adjacent_post(false,'',false)) ;
    $title = get_the_title(get_adjacent_post(false,'',false));
    $pref_format = FcpFormats::get_next_and_prefetch_headerlinks();
    $next_line = sprintf( $pref_format,
      $title, $next_issue_url, $title , $next_issue_url );
  }

  $headerlink_format = FcpFormats::get_headerlinks();
  return sprintf( $headerlink_format,
                    $start_title,
                    $start_url,
                    $prev_title,
                    $prev_url,
                    $next_line
  );
  return $tags ;
}


function fcp_inner_custom_box( $post ) {
  $id = $post->ID;
  $value = get_post_meta( $post->ID, '_fcp_comic_image_url', true );
  $value = $value ? $value : '';
  $format = FcpFormats::get_inner_custom_box_format();
  echo sprintf( $format, $id, $value );
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




