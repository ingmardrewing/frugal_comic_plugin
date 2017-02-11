<?php

class AdminMenu {
  private $opt_name = 'fcp_post_id_of_first_issue';
  private $opt2_name = 'fcp_file_name_pattern';
  private $hidden_field_name = 'fcp_submit_hidden';
  private $data_field_name = 'fcp_post_id_of_first_issue';
  private $data2_field_name = 'fcp_file_name_pattern';

  private $headline ;
  private $post_id_label ;
  private $image_file_label ;
  private $save_label ;

  private $opt_val;
  private $opt2_val;

  private $saved_msg;

  public function read_labels () {
    $this->headline =
      __( 'Frugal Comic Pugin Settings', 'fcp-admin-menu' );
    $this->post_id_label =
      __("Post-ID of first issue (defaults to '8'):", 'fcp-admin-menu' );
    $this->image_file_label =
      __("Image file name pattern (defaults to 'DevAbode_\d+.png' ):", 'fcp-admin-menu' );
    $this->save_label =
      esc_attr(__('Save Changes'));
  }

  public function handle_request (){
    $this->check_user_rights();
    $this->process_request();
    $this->display_settings();
  }

  private function check_user_rights () {
    if (!current_user_can('manage_options')) {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
  }

  private function process_request (){
    // check if user has submitted data
    if( $this->form_was_submitted() ) {
      $this->get_user_input();
      $this->write_values_to_db();
      $this->prepare_saved_msg();
    }
    else{
      $this->read_values_form_db();
    }
  }

  private function form_was_submitted () {
    // If submitted, this hidden field will be set to 'Y'
    return isset($_POST[ $hidden_field_name ])
        && $_POST[ $hidden_field_name ] == 'Y' ;
  }

  private function read_values_form_db () {
    // Read existing option value from database
    $this->opt_val = get_option( $this->opt_name );
    $this->opt2_val = get_option( $this->opt2_name );
  }

  private function get_user_input (){
    // Read their posted value
    $this->opt_val = $_POST[ $this->data_field_name ];
    $this->opt2_val = $_POST[ $this->data2_field_name ];
  }

  private function write_values_to_db(){
    // Save the posted value in the database
    update_option( $this->opt_name, $this->opt_val );
    update_option( $this->opt2_name, $this->opt2_val );
 }

  private function prepare_saved_msg () {
    // Put a "settings saved" message on the screen
    $msg = __('settings saved.', 'fcp-admin-menu' );
    $sformat = FcpFormats::get_settings_editing_html();
    $this->saved_msg = sprintf( $sformat, $msg );
  }

  private function display_settings (){
    $this->read_labels();
    $format = FcpFormats::get_settings_editing_html();
    echo sprintf( $format,
      $this->saved_msg,
      $this->headline,
      $this->hidden_field_name ,
      $this->data_field_name,
      $this->post_id_label,
      $this->data_field_name ,
      $this->opt_val ,
      $this->data2_field_name,
      $this->image_file_label,
      $this->data2_field_name ,
      $this->opt2_val ,
      $this->save_label
    );
  }
}
