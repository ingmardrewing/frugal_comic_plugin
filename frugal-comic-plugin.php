<?php
/*
Plugin Name: Frugal Comic Plugin
Version: 20170211
*/
include 'lib/FcpFormats.php';

include 'lib/FcpFrontend.php';
add_action( 'wp_head', 'fcp_add_html_header_elements');
function fcp_add_html_header_elements () {
  $ff = new FcpFrontend();
  echo $ff->process_head();
}
add_filter( 'the_content', 'fcp_modify_content');
function fcp_modify_content ( $content ) {
  $ff = new FcpFrontend();
  return $ff->process_body( $content );
}

include 'lib/FcpAdminMenu.php';
add_action( 'admin_menu', 'fcp_plugin_menu' );
function fcp_plugin_menu() {
	add_options_page( 'Frugal Comic Plugin Options', 'Frugal Comic Plugin', 'manage_options', 'fcp-admin-menu', 'fcp_plugin_options' );
}
function fcp_plugin_options() {
  $admin_menu = new AdminMenu();
  $admin_menu->handle_request();
}

include 'lib/FcpCustomBox.php';
add_action( 'add_meta_boxes', 'fcp_add_custom_box' );
function fcp_add_custom_box() {
  $fcb = new FcpCustomBox();
  $fcb->add_box();
}

include 'lib/FcpSavePost.php';
add_action( 'save_post', 'fcp_save_postdata' );
function fcp_save_postdata( $post_id ) {
  $fsp = new FcpSavePost();
  $fsp->save();
}


