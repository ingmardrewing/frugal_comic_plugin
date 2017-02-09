<?php
/*
Plugin Name: Frugal Comic Plugin
*/
include 'Formats.php';

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

    //must check that the user has the required capability 
    if (!current_user_can('manage_options')) {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names 
    $opt_name = 'fcp_post_id_of_first_issue';
    $opt2_name = 'fcp_file_name_pattern';

    $hidden_field_name = 'fcp_submit_hidden';
    $data_field_name = 'fcp_post_id_of_first_issue';
    $data2_field_name = 'fcp_file_name_pattern';

    // Read in existing option value from database
    $opt_val = get_option( $opt_name );
    $opt2_val = get_option( $opt2_name );

    // output
    $html = '';

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'

    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];
        $opt2_val = $_POST[ $data2_field_name ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );
        update_option( $opt2_name, $opt2_val );

        // Put a "settings saved" message on the screen
        $msg = __('settings saved.', 'fcp-admin-menu' );
        $html .= '<div class="updated"><p><strong>' . $msg . '</strong></p></div>';
    }

    $format = Formats::get_settings_editing_html();
    // Now display the settings editing screen
    echo sprintf( $format, 
       __( 'Frugal Comic Pugin Settings', 'fcp-admin-menu' ),
      $hidden_field_name ,
      $data_field_name,
      __("Post-ID of first issue (defaults to '8'):", 'fcp-admin-menu' ),
      $data_field_name ,
      $opt_val ,
      $data2_field_name,
      __("Image file name pattern (defaults to 'DevAbode_\d+.png' ):", 'fcp-admin-menu' ),
      $data2_field_name ,
      $opt2_val ,
      esc_attr(__('Save Changes'))
    );
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

    $start_issue_url = get_permalink(get_option('fcp_post_id_of_first_issue'))  ;
    $start_issue_title = get_the_title(get_option('fcp_post_id_of_first_issue'));
    $start_link = '<link ref="start" title="' . $start_issue_title . '" href="'. $start_issue_url .'" />';

    $prev_issue_url = get_permalink(get_adjacent_post(false,'',true))  ;
    $title2 = get_the_title(get_adjacent_post(false,'',true));
    $prev_link = '<link ref="prev" title="'. $title2 .'" href="'. $prev_issue_url .'" />';

    $next_url = get_permalink(get_adjacent_post(false,'',false));
    $this_url = get_permalink($post);

    $tags = $start_link . "\n";
    $tags .= $prev_link . "\n";

    if( $next_url != $this_url ){
      $next_issue_url = get_permalink(get_adjacent_post(false,'',false)) ;
      $title = get_the_title(get_adjacent_post(false,'',false));
      $next_link = '<link ref="next" title="'. $title .'" href="'. $next_issue_url .'" />';
      $prefetch_link = '<link ref="prefetch" title="'. $title .'" href="'. $next_issue_url .'" />';

      $tags .= $next_link . "\n";
      $tags .= $prefetch_link . "\n";
    }

    return $tags ;
};

function fcp_modify_content( $content ){
  global $post;

  $first_comic_url = get_post_meta( $first_issue_post_id, '_fcp_comic_image_url', true );
  $next_post = get_next_post();
  $next_comic_image_url = get_post_meta( $next_post->ID, '_fcp_comic_image_url', true ); 

  if( ! empty( $next_post ) && empty( $next_comic_image_url ) ){
    return $content;
  }

  $new_content = fcp_rewrite_content( $content ); 

  if( empty( $next_post )){
    $js = fcp_get_comic_preload_js( $first_comic_url , get_post_meta( $post->ID, '_fcp_comic_image_url', true ) ) ;
    return $js . $new_content ;
  }

  $js = fcp_get_comic_preload_js( $next_comic_image_url ) ;
  return $js . $new_content;
}

function fcp_rewrite_content( $content ) {
  $first_id = get_option( 'fcp_post_id_of_first_issue', 8 );
  $file_name_pattern = get_option( 'fcp_file_name_pattern', 'DevAbode_\d+.png' );
  $newest_id = wp_get_recent_posts(array('numberposts' => 1, 'post_status' => 'publish'))[0]['ID'];

  $first_url  = get_permalink( $first_id );
  $prev_url   = get_permalink( get_adjacent_post(false,'',true) )  ;
  $next_url   = get_permalink( get_adjacent_post(false,'',false) ) ;
  $newest_url = get_permalink( $newest_id);

  global $post;
  $this_url = get_permalink( $post->ID ) ;
  $navi= fcp_get_navigation( $this_url, $first_url, $prev_url, $next_url, $newest_url );
  $img = fcp_get_image_html( $content, $next_url );

  if( preg_match( '/' . $file_name_pattern . '/', $content  ) ){
    return $img . $navi . $soc_med ;
  }
  return $content ;
}

function fcp_get_image_html( $content, $url ){
  $image_pattern = '/(<img[^>]+>)/';
  preg_match( $image_pattern, $content, $match );
  if( $match[0] ){
    $format = Formats::get_comicpage_html();
    return sprintf( $format, add_get_p($url), $match[0] );
  }
  return '';
}

function fcp_get_comic_preload_js($next_img_url ) {
  if( ! empty( $next_img_url ) ){
    $format = Formats::get_preload_js();
    return sprintf( $format, $next_img_url );
  }
  return '';
}

function fcp_get_navigation ( $this_url, $first_url, $prev_url, $next_url, $newest_url ){
  $is_first=  $this_url === $first_url ;
  $is_newest = $this_url === $newest_url ; 
  $format = Formats::get_navi_format();
  $lnwst = "newest &gt;&gt;";
  $lnxt = "next&gt;";
  $lprv = "&lt; previous";
  $lfrst = "&lt;&lt; first";
  return sprintf( $format, 
   fcp_add_navi_li( ! $is_newest, add_get_p($newest_url), $lnwst),
   fcp_add_navi_li( ! $is_newest, add_get_p($next_url), $lnxt),
   fcp_add_navi_li( ! $is_first,  add_get_p($prev_url), $lprv),
   fcp_add_navi_li( ! $is_first,  add_get_p($first_url), $lfrst)
  );
}

function add_get_p ( $url ){
  if( $_GET['_s'] ){
    return $url . '?_s=' . $_GET['_s'];
  }
  global $post;
  return $url . '?_s=' . $post->ID;
}

function fcp_add_navi_li ( $add_navi_link, $url, $label ){
  $outer_format = Formats::get_listelem_outer_format();
  $inner_format = Formats::get_listelem_inner_format();
  $inner = '';
  if( $add_navi_link ) {
    $inner = sprintf( $inner_format, $url, $label );
  }
  return sprintf( $outer_format, $inner );
}

function fcp_inner_custom_box( $post ) {
  $id = $post->ID;
  $value = get_post_meta( $post->ID, '_fcp_comic_image_url', true ); 
  $value = $value ? $value : '';
  $format = Formats::get_inner_custom_box_format();
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
?>
