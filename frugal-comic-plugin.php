<?php
/*
Plugin Name: Frugal Comic Plugin
Version: 20170211
*/
include 'FcpFormats.php';

include 'FcpContent.php';
add_filter( 'the_content', 'fcp_modify_content');
function fcp_modify_content( $content ){
  $c = new FcpContent();
  return $c->process_content( $content );
}

include 'FcpAdminMenu.php';
add_action( 'admin_menu', 'fcp_plugin_menu' );
function fcp_plugin_menu() {
	add_options_page( 'Frugal Comic Plugin Options', 'Frugal Comic Plugin', 'manage_options', 'fcp-admin-menu', 'fcp_plugin_options' );
}
function fcp_plugin_options() {
  $admin_menu = new AdminMenu();
  $admin_menu->handle_request();
}

include 'FcpCustomBox.php';
add_action( 'add_meta_boxes', 'fcp_add_custom_box' );
function fcp_add_custom_box() {
  $fcb = new FcpCustomBox();
  $fcb->add_box();
}

include 'FcpSavePost.php';
add_action( 'save_post', 'fcp_save_postdata' );
function fcp_save_postdata( $post_id ) {
  $fsp = new FcpSavePost();
  $fsp->save();
}

include 'FcpHead.php';
add_action( 'wp_head', 'fcp_add_html_header_elements');
function fcp_add_html_header_elements (){
  $fh = new FcpHead();
  echo $fh->add_head_content();
}
