<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['clear_spiral_setting'])) {
        if (empty($_POST) || !wp_verify_nonce($_POST['clear_spiral_setting'], 'clear_spiral_setting')) {
            print 'Verification failed. Try again.';
            exit;
        } else {
            update_option('sml_is_setup', true);
            update_option('sml_version', 1);
            update_option('spiral_v2_member_login',null);
            update_option('spiral_member_login',null);

            global $wpdb;

            $user_key                                 = $this->session->get('sml_sid');
            $shortcode_mypage_url_optiona_name        = 'shortcode_' . $user_key . '%';
            $shortcode_is_logged_in_type_optiona_name = 'shortcode_is_logged_in_type' . $user_key . '%';
      
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $shortcode_mypage_url_optiona_name));
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $shortcode_is_logged_in_type_optiona_name));

            echo '<script type="text/javascript">
            window.location.href = "options-general.php?page=spiral_member_login";
            </script>';
        }
    }
}
