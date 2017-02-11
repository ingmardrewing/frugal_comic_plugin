<?php

class FcpCustomBox {

  public function add_box(){
    function fcp_inner_custom_box ( $post ) {
      $id = $post->ID;
      $value = get_post_meta( $post->ID, '_fcp_comic_image_url', true );
      $value = $value ? $value : '';
      $format = FcpFormats::get_inner_custom_box_format();
      echo sprintf( $format, $id, $value );
    }

    $screens = array( 'post', 'my_cpt' );
    foreach ( $screens as $screen ) {
        add_meta_box(
            'fcp_box_id',            // Unique ID
            'Frugal Comic Plugin',   // Box title
            'fcp_inner_custom_box',  // Content callback
             $screen                 // post type
        );
    }
  }


}
