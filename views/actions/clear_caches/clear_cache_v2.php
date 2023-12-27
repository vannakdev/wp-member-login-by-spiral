<?php

$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

$_SESSION["message"] = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['clear_cache_db'])) {
    $sml_sid =  $this->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
    $this->clear_user_options();
    if ($sml_sid) {
      @setcookie('is_login', false, time() - 1800, COOKIEPATH, COOKIE_DOMAIN, TRUE, TRUE); // DELETE COOKIE
      $result = $this->spiral2->logout($sml_sid);
    }
    if (get_option('clear_cached') == "unclear") {
      update_option('clear_cached', "cleared");
    } else {
      add_option('clear_cached', "cleared");
    }
    $_SESSION["message"] = $this->translator->sml_translate('cache_cleared');
  }
}
