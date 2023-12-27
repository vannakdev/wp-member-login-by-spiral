<?php
 /**
   * Set Version One Action
   */
  if (isset($_POST['show_on_front'])) {
    if (empty($_POST) || !wp_verify_nonce($_POST['spiral_version'], 'spiral_version')) {
      print 'Verification failed. Try again.';
      exit;
    } else {
      $_POST['_wp_http_referer'] = "/wordpress/wp-admin/options-general.php?page=spiral_member_login";
      //Delete Old Setting
      update_option('sml_is_setup', false);
      update_option('sml_version', $_POST['show_on_front']);
      $user_key = $this->session->get('sml_sid');
      add_option('clear_cache_'. $user_key,true);

      if($_POST['show_on_front'] == 1){
        echo '<script type="text/javascript">
        window.location.href = "options-general.php?page=spiral_member_login";
        </script>';
      }
      if($_POST['show_on_front'] == 2){
        echo '<script type="text/javascript">
        window.location.href = "options-general.php?page=spiral_v2_member_login";
        </script>';
      }
    }
  }