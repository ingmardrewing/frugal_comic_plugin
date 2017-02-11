<?php

class FcpHead {
  private $start_url ;
  private $start_title ;
  private $prev_url ;
  private $prev_title ;
  private $next_url ;
  private $this_url ;
  private $title;
  private $first_id;
  private $first_image_url;
  private $next_image_url;

  private function init(){
    global $post;
    $this->start_url 
      = get_permalink(get_option('fcp_post_id_of_first_issue'))  ;
    $this->start_title 
      = get_the_title(get_option('fcp_post_id_of_first_issue'));
    $this->prev_url = get_permalink(get_adjacent_post(false,'',true))  ;
    $this->prev_title = get_the_title(get_adjacent_post(false,'',true));
    $this->next_url = get_permalink(get_adjacent_post(false,'',false));
    $this->url = get_permalink($post);
    $this->title = get_the_title(get_adjacent_post(false,'',false));
    $this->first_id = get_option('fcp_post_id_of_first_issue', 8) ;
    $this->first_image_url
      = get_post_meta( $this->first_id, '_fcp_comic_image_url', true );
    $this->next_image_url
      = get_post_meta( $this->next_id, '_fcp_comic_image_url', true );
  }

  public function add_head_content(){
    $this->init();

    if( $this->next_url != $this->url ){
      return $this->format_data(
        $this->get_links_to_next_post()
      );
    }

    return $this->format_data('');
  }

  private function get_links_to_next_post (){
    $pref_format = FcpFormats::get_next_and_prefetch_headerlinks();
    $next_line = sprintf( $pref_format,
      $this->title, $this->next_url, $this->title , $this->next_url );
  }


  private function format_data ( $next_line ){
    $headerlink_format = FcpFormats::get_headerlinks();
    return sprintf( $headerlink_format,
                      $this->start_title,
                      $this->start_url,
                      $this->prev_title,
                      $this->prev_url,
                      $next_line,
                      $this->js
    );
  }

  private function js() {
    $image_url = $this->get_preload_image_url();
    $format = FcpFormats::get_preload_js();
    return sprintf( $format, $next_img_url );
  }

  private function get_preload_image_url () {
    if( empty( $this->next_post )){
      return $this->first_image_url ;
    }
    return $this->next_image_url;
  }
}
