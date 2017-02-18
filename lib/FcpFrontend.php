<?php

class FcpFrontend {
  private $next_post;
  private $prev_post;

  private $first_id;
  private $prev_id;
  private $id;
  private $next_id;
  private $newest_id;

  private $next_image_url;

  private $first_url;
  private $prev_url;
  private $url;
  private $next_url;
  private $newest_url;

  private $is_first;
  private $is_newest;

  private $file_name_pattern;
  private $content;
  private $title;

  private function init (){
    $this->get_adjacent_posts();
    $this->get_ids();
    $this->get_titles();
    $this->get_image_urls();
    $this->get_urls();

    $this->is_first  = $this->url === $this->first_url ;
    $this->is_newest = $this->url === $this->newest_url ;
    $this->file_name_pattern = get_option( 'fcp_file_name_pattern', 'DevAbode_\d+.png' );
  }

  private function get_urls()
  {
    $this->first_url  = get_permalink( $this->first_id );
    $this->prev_url   = get_permalink( $this->prev_id  )  ;
    $this->url        = get_permalink( $post->id ) ;
    $this->next_url   = get_permalink( $this->next_id ) ;
    $this->newest_url = get_permalink( $this->newest_id);
  }

  private function get_image_urls()
  {
    $this->first_image_url
      = get_post_meta( $this->first_id, '_fcp_comic_image_url', true );
    $this->next_image_url
      = get_post_meta( $this->next_id, '_fcp_comic_image_url', true );
  }

  private function get_titles()
  {
    $this->first_title = get_the_title(get_option($this->first_id));
    $this->title = get_the_title(get_adjacent_post(false,'',false));
    $this->prev_title = get_the_title(get_adjacent_post(false,'',true));
  }

  private function get_ids()
  {
    $this->first_id = get_option('fcp_post_id_of_first_issue', 8) ;
    $this->prev_id = $this->prev_post->ID;
    global $post;
    $this->id = $post->ID;
    $this->next_id = $this->next_post->ID;
    $this->newest_id = wp_get_recent_posts(array('numberposts' => 1, 'post_status' => 'publish'))[0]['ID'];
  }

  private function get_adjacent_posts()
  {
    $this->next_post = get_next_post();
    $this->prev_post = get_adjacent_post(false,'',true);
  }

  public function process_head () {
    $this->init();

    if( $this->next_url != $this->url ){
      return $this->format_head_data(
        $this->get_links_to_next_post()
      );
    }

    return $this->format_head_data('');
  }

  private function get_links_to_next_post (){
    $pref_format = FcpFormats::get_next_and_prefetch_headerlinks();
    return sprintf( $pref_format,
      $this->title, $this->next_url, $this->title , $this->next_url );
  }

  private function format_head_data ( $next_line ){
    $headerlink_format = FcpFormats::headerlinks();
    return sprintf( $headerlink_format,
                      $this->first_title,
                      $this->first_url,
                      $this->prev_title,
                      $this->prev_url,
                      $next_line,
                      $this->js()
    );
  }

  private function js()
  {
    $image_url = $this->get_preload_image_url();
    $js_format = FcpFormats::js();
    $js = sprintf( $js_format, $image_url );
    return $js;
  }

  private function get_preload_image_url () {
    if( empty( $this->next_post )){
      return $this->first_image_url ;
    }
    return $this->next_image_url;
  }

  public function process_body ( $content ) {
    $this->content = $content;
    $this->init($content);
    if (! empty($this->next_post) && empty( $this->next_image_url) ){
      return $content;
    }
    $this->rewrite();
    return $this->content;
  }

  private function rewrite () {
    if( $this->post_contains_comic_image() ){
      $navi= $this->get_navi();
      $img = $this->get_image_html($navi);
      $this->content = $img ;
    }
  }

  private function post_contains_comic_image (){
    $pattern = '/' . $this->file_name_pattern . '/';
    $content = $this->content ;
    return preg_match( $pattern, $content );
  }

  private function get_navi (){
    $format = FcpFormats::get_navi_format();
    $nwst = "newest &gt;&gt;";
    $nxt = "next &gt;";
    $prv = "&lt; previous";
    $frst = "&lt;&lt; first";
    return sprintf( $format,
      $this->navi_li( ! $this->is_newest, $this->newest_url, $nwst),
      $this->navi_li( ! $this->is_newest, $this->next_url, $nxt),
      $this->navi_li( ! $this->is_first,  $this->prev_url, $prv),
      $this->navi_li( ! $this->is_first,  $this->first_url, $frst)
    );
  }

  private function navi_li ( $add_navi_link, $url, $label ){
    $wrapped_url = $this->add_get_p( $url );
    $outer_format = FcpFormats::get_listelem_outer_format();
    $inner_format = FcpFormats::get_listelem_inner_format();
    $inner = '';
    if( $add_navi_link ) {
      $inner = sprintf( $inner_format, $wrapped_url, $label );
    }
    return sprintf( $outer_format, $inner );
  }

  private function add_get_p ( $url ){
    if( isset( $_GET['_s'] )){
      $s = intval($_GET['_s']);
      $s++;
      return $url . '?_s=' . $s;
    }
    return $url . '?_s=0';
  }

  private function get_image_html( $navi ){
    $image_pattern = '/(.*)(<img[^>]+>)(.*)/s';
    preg_match( $image_pattern, $this->content, $match );
    if( $match[2] ){
      $format = FcpFormats::get_comicpage_html();
      $pre    = $match[1];
      $img    = $match[2];
      $post   = $match[3];
      return sprintf( $format,
        $pre,
        $this->add_get_p( $this->next_url),
        $img,
        $navi,
        $post
      );
    }
    return '';
  }
}
