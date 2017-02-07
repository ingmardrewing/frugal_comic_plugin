<?php
/*
Plugin Name: Frugal Comic Plugin
*/

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

    // Now display the settings editing screen
    $html .= '<div class="wrap">';

    // header
    $html .= "<h2>" . __( 'Frugal Comic Pugin Settings', 'fcp-admin-menu' ) . "</h2>";

    // settings form
    $html .= '<form name="form1" method="post" action="">';
    $html .= '<input type="hidden" name="' . $hidden_field_name . '" value="Y">';
    $html .= '<p><label for="'.$data_field_name.'">' . __("Post-ID of first issue:", 'fcp-admin-menu' ) . '</label>' ;
    $html .= '<input type="text" name="' . $data_field_name . '" value="'. $opt_val .'" size="20">';
    $html .= '</p><hr />';
    $html .= '<p><label for="'.$data2_field_name.'">' . __("Image file name pattern:", 'fcp-admin-menu' ) . '</label>' ;
    $html .= '<input type="text" name="' . $data2_field_name . '" value="'. $opt2_val .'" size="20">';
    $html .= '</p><hr />';

    $html .= '<p class="submit">';
    $html .= '<input type="submit" name="Submit" class="button-primary" value="' . esc_attr(__('Save Changes')) .'" />';
    $html .= '</p></form></div>';

    echo $html;
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
  $header_elements  = fcp_get_header_link_tags( $post );
  $header_elements .= fcp_add_og_meta( $post );
  echo $header_elements;
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

function fcp_add_og_meta ( $post ){

  $comic_image_url = get_post_meta( $post->ID, '_fcp_comic_image_url', true ); 
  $this_title = get_the_title( $post );
  $this_url   = get_permalink($post);

  $tags  = '<meta property="og:image" content="' . $comic_image_url . '">' . "\n";
  $tags .= '<meta property="og:title" content="' . $this_title . '">' . "\n";
  $tags .= '<meta property="og:url" content="' . $this_url . '">' . "\n";
  $tags .= '<meta property="og:type" content="website">' . "\n";

  return '';
}

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
  $first_issue_id = get_option( 'fcp_post_id_of_first_issue', 1 );
  $file_name_pattern = get_option( 'fcp_file_name_pattern', 'DevAbode_\d+.png' );
  $newest_issue_id = wp_get_recent_posts(array('numberposts' => 1, 'post_status' => 'publish'))[0]['ID'];

  $first_issue_url  = get_permalink( $first_issue_id );
  $prev_issue_url   = get_permalink( get_adjacent_post(false,'',true) )  ;
  $next_issue_url   = get_permalink( get_adjacent_post(false,'',false) ) ;
  $newest_issue_url = get_permalink( $newest_issue_id);

  $navi= fcp_get_navigation( $first_issue_url, $prev_issue_url, $next_issue_url, $newest_issue_url );
  $img = fcp_get_image_html( $content, $next_issue_url );
  $soc_med = ''; # fcp_get_socmed();

  if( preg_match( '/' . $file_name_pattern . '/', $content  ) ){
    return $img . $navi . $soc_med ;
  }
  return $content  ;
}

function fcp_get_socmed () {
  global $post;
  $this_url = get_permalink($post);
  $this_perm_urlenc = urlencode( get_permalink( $post ) );
  $this_title_urlenc = urlencode( get_the_title( $post ) );

  $html  = '<div style="text-align:right;">';
  $html .= '<div style="font-size:12px;display:inline-block;padding: 0 5px;">If you like the comic it would be fantastic if you could ';
  $html .= '<a href="https://www.facebook.com/sharer/sharer.php?u=' .$this_perm_urlenc ;
  $html .= '" target="_blank" style="font-size:12px;display:inline-block;background-color:#3B5998;color:#FFFFFF;padding:0 5px;">post it on Facebook</a>';
  $html .= '<a href="https://twitter.com/intent/tweet?text=' . $this_title_urlenc .'&url=' .$this_perm_urlenc ;
  $html .= '&related=twitterapi%2Ctwitter" target="_blank" style="font-size:12px;display:inline-block;background-color:#2FC2EF;color:#FFFFFF;padding:0 5px;">';
  $html .= 'or tweet about it</a>';
  $html .= '<div style="font-size:12px;display:inline-block;padding: 0 5px;"> Thanks! :)</div></div></div>';

  return $html;
}

function fcp_get_image_html( $content, $url ){
  $image_pattern = '/(<img[^>]+>)/';
  preg_match( $image_pattern, $content, $match );
  return '<p><a href="' . $url . '" rel="next">' . $match[0] . '</a></p>';
}

function fcp_get_comic_preload_js($next_img_url ) {
  $js = "<script language='javascript'>jQuery(window).load(function(){ ";
  if( ! empty( $this_img_url ) ){
    $js .= " var img = jQuery('<img>')[0]; img.src = \"" . $next_img_url . "\";";
  }
  $js .= '});</script>';
  return $js;
}

function fcp_get_navigation ( $first_issue_url, $prev_issue_url, $next_issue_url, $newest_issue_url ){
  global $post;
  $this_issue_url = get_permalink( $post->ID ) ;
  $is_first_post =  $this_issue_url === $first_issue_url ;
  $is_newest_post = $this_issue_url === $newest_issue_url ; 

  $html = '<div id="stripnav" style="width: 100%;text-align:right; margin-bottom:80px;"><ul>';
  $html .= fcp_add_navi_li( ! $is_newest_post, $newest_issue_url,  "newest &gt;&gt;");
  $html .= fcp_add_navi_li( ! $is_newest_post, $next_issue_url,   "next&gt;");
  $html .= fcp_add_navi_li( ! $is_first_post,  $prev_issue_url,   "&lt; previous");
  $html .= fcp_add_navi_li( ! $is_first_post,  $first_issue_url, "&lt;&lt; first");
  $html .= '</div>';
  
  return $html;
}

function fcp_add_navi_li ( $add_navi_link, $url, $label ){
  $html = '<li style="list-style-type:none;float:right;margin-right:10px;">';
  if( $add_navi_link ) {
    $html .= "<a href='". $url  ."'>".$label."</a>";
  }
  $html .= '</li>';
  return $html;
}

function fcp_link_page_js($url) {
  return "<script language='javascript'>jQuery(window).load(function(){ var img = jQuery('<img>')[0]; img.src = \"" . $url . "\"; console.log(img); });</script>";
}

function fcp_inner_custom_box( $post ) {
  $id = $post->ID;
  $value = get_post_meta( $post->ID, '_fcp_comic_image_url', true ); 
  $value = $value ? $value : '';
  
  $html  = '<label for="fcp_field">Comic Image URI for Post ID ' . $id . '</label> ';
  $html .= '<input name="fcp_field" id="fcp_field" class="postbox" value="'. $value .'" style="width:100%;" />';

  echo $html;
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
