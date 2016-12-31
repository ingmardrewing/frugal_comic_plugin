<?php
/*
Plugin Name: FrugalComicPlugin
*/

/* Adds a meta box to the post edit screen */
add_action( 'add_meta_boxes', 'fcp_add_custom_box' );
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

add_action('wp_head', 'fcp_add_link_tags_to_head');
function fcp_add_link_tags_to_head(){

    global $post;

    $start_issue_url = 'http://devabo.de/2013/08/01/a-step-in-the-dark/'  ;
    $start_link = '<link ref="start" title="A step in the dark" href="'. $start_issue_url .'" />';

    $prev_issue_url = get_permalink(get_adjacent_post(false,'',true))  ;
    $title2 = get_the_title(get_adjacent_post(false,'',true));
    $prev_link = '<link ref="prev" title="'. $title2 .'" href="'. $prev_issue_url .'" />';

    $next_url = get_permalink(get_adjacent_post(false,'',false));
    $this_url = get_permalink($post);

    echo $start_link . "\n";
    echo $prev_link . "\n";

    if( $next_url != $this_url ){
      $next_issue_url = get_permalink(get_adjacent_post(false,'',false)) ;
      $title = get_the_title(get_adjacent_post(false,'',false));
      $next_link = '<link ref="next" title="'. $title .'" href="'. $next_issue_url .'" />';
      $prefetch_link = '<link ref="prefetch" title="'. $title .'" href="'. $next_issue_url .'" />';
      echo $next_link . "\n";
      echo $prefetch_link . "\n";
    }
};

add_action('wp_head', 'fcp_og_meta');
function fcp_og_meta (){
  global $post;
  $comic_image_url = get_post_meta( $post->ID, '_fcp_comic_image_url', true ); 
  $this_title = get_the_title( $post );
  $this_url = get_permalink($post);

  echo '<meta property="og:image" content="' . $comic_image_url . '">' . "\n";
  echo '<meta property="og:title" content="' . $this_title . '">' . "\n";
  echo '<meta property="og:url" content="' . $this_url . '">' . "\n";
  echo '<meta property="og:type" content="website">' . "\n";
}

add_filter('the_content', 'fcp_modify_content');
function fcp_modify_content( $content ){
  global $post;
  $comic_image_url = get_post_meta( $post->ID, '_fcp_comic_image_url', true ); 
  $next_post = get_next_post();
  $next_comic_image_url = get_post_meta( $next_post->ID, '_fcp_comic_image_url', true ); 

  $js = "";
  if( empty( $next_post )){
    $js = fcp_write_comic_preload_js("https://s3-us-west-1.amazonaws.com/devabode-us/comicstrips/DevAbode_0001.png" ) ;
  }
  else if( empty( $next_comic_image_url ) ){
    return $content ;
  }
  else{
    $js = fcp_write_comic_preload_js($next_comic_image_url ) ;
  }

 return $js . fcp_rewrite_content($content, $this_img_url, get_permalink($next_post->ID) ) ;
}

function fcp_rewrite_content($content, $this_img_url, $next_permalink = NULL) {
  global $post;
  $next_issue_url = get_permalink(get_adjacent_post(false,'',false)) ;

  $navi= fcp_write_navigation(8, $dom);
  $soc_med = fcp_write_socmed();
  $img = fcp_get_image_html( $content, $next_issue_url );

  if( preg_match( '/DevAbode_\d+.png/', $content  ) ){
    return '' . $img . $navi . $soc_med ;
  }
  return $content ;
}

function fcp_write_socmed (){
  global $post;
  $this_url = get_permalink($post);
  $this_perm_urlenc = urlencode( get_permalink( $post ) );
  $this_title_urlenc = urlencode( get_the_title( $post ) );

  return <<<END
<div style="text-align:right;">
  <div style="font-size:12px;display:inline-block;padding: 0 5px;">
    If you like the comic it would be fantastic if you could 
    <a href="https://www.facebook.com/sharer/sharer.php?u=${this_perm_urlenc}" target="_blank" style="font-size:12px;display:inline-block;background-color:#3B5998;color:#FFFFFF;padding:0 5px;">post it on Facebook</a>
    <a href="https://twitter.com/intent/tweet?text=${this_title_urlenc}&url=${this_perm_urlenc}&related=twitterapi%2Ctwitter" target="_blank" style="font-size:12px;display:inline-block;background-color:#2FC2EF;color:#FFFFFF;padding:0 5px;">or tweet about it</a>
    <div style="font-size:12px;display:inline-block;padding: 0 5px;"> Thanks! :)</div>
  </div>
</div>
END;
}

function fcp_get_image_html( $content, $url ){
  $image_pattern = '/(<img[^>]+>)/';
  preg_match( $image_pattern, $content, $match );
  return '<p><a href="' . $url . '" rel="next">' . $match[0] . '</a></p>';
}

function fcp_write_comic_preload_js($next_img_url ) {
  $js = "<script language='javascript'>jQuery(window).load(function(){ ";
  if( ! empty( $this_img_url ) ){
    $js .= " var img = jQuery('<img>')[0]; img.src = \"" . $next_img_url . "\";";
  }
  $js .= '});</script>';
  return $js;
}

function fcp_write_navigation ( $min_post_id , $dom ){
  $latest_cpt = wp_get_recent_posts(array('numberposts' => 1, 'post_status' => 'publish'));
  $my_latest_post_id = $latest_cpt[0]['ID'];

  $is_newest_post =  get_the_ID() < $min_post_id ) && ( $latest_cpt[0]['ID'] != get_the_ID() ;
  $is_first_post =  get_the_ID() < $min_post_id + 1 ;

  $oldest_issue_url = 'http://devabo.de/2013/08/01/a-step-in-the-dark/' ;
  $prev_issue_url = get_permalink(get_adjacent_post(false,'',true))  ;
  $next_issue_url = get_permalink(get_adjacent_post(false,'',false)) ;
  $newest_post_url =  get_permalink( $my_latest_post_id);

  $html = '<div id="stripnav" style="width: 100%;text-align:right; margin-bottom:80px;"><ul>';
  $html .= fcp_add_navi_li( ! $is_newest_post, $newest_post_url,  "newest &gt;&gt;");
  $html .= fcp_add_navi_li( ! $is_newest_post, $next_issue_url,   "next&gt;");
  $html .= fcp_add_navi_li( ! $is_first_post,  $prev_issue_url,   "&lt; previous");
  $html .= fcp_add_navi_li( ! $is_first_post,  $oldest_issue_url, "&lt;&lt; first");
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
  $value = get_post_meta( $post->ID, '_fcp_comic_image_url', true ); 
  ?>
    <label for="fcp_field">Comic Image URI </label>
      <input name="fcp_field" id="fcp_field" class="postbox" value="<?php if($value){ echo $value; } ?>" style="width:100%;" />
  <?php
}

add_action( 'save_post', 'fcp_save_postdata' );
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
