<?php

class FcpHead {
  public function add_head_content(){
    global $post;
    return fcp_get_header_link_tags( $post );
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

}
