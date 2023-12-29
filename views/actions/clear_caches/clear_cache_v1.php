<?php

$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

$_SESSION["message"] = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['clear_cache_db'])) {

    $this->clear_user_options();
    $sml_sid = $this->session->get('sml_sid');
    $wpmls_area_title = $this->get_option('wpmls_area_title');

    if ($sml_sid) {
      @setcookie('is_login', false, time() - 1800, COOKIEPATH, COOKIE_DOMAIN, TRUE, TRUE);
      $result = $this->spiral->logout_area($wpmls_area_title, $sml_sid);
    }

    if (get_option('wpmls_clear_cached') == "unclear") {
      update_option('wpmls_clear_cached', "cleared");
    } else {
      add_option('wpmls_clear_cached', "cleared");
    }
    $_SESSION["message"] = $this->translator->sml_translate('cache_cleared');
  }
}
