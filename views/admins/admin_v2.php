<?php
/**
 * Represents the view for the administration dashboard.
 *
 * @package   Spiral_v2_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
//Get the active tab from the $_GET param

include_once(plugin_dir_path(__DIR__) . 'actions/clear_caches/clear_cache_v2.php');
include_once(plugin_dir_path(__DIR__) . 'actions/switch_version.php');
include_once(plugin_dir_path(__DIR__) . 'actions/toggle_version.php');
include_once(plugin_dir_path(__DIR__) . 'actions/clear-spiral-setting.php');
?>
<?php if ( ((get_option('sml_version') == 2) &&  (get_option('sml_is_setup') == false)) || get_option('spiral_v2_member_login') ) { ?>
<div class="wrap">
<h2><?php echo esc_html(get_admin_page_title()); ?></h2>
<p>
<?php 
 echo $this->translator->sml_translate('version_of_spiral_setting');
?>	
</p>
  <?php
  if ($_SESSION["message"]  != null) {
    echo '
      <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">
      <p><strong>Caches Cleared.</strong></p><button type="button" class="notice-dismiss clear_cache_button"><span class="screen-reader-text">Dismiss this notice.</span></button>
      </div>
      ';
  }
  ?>
  <nav class="nav-tab-wrapper">
    <a tab="1" class="nav-tab <?php if ($tab === null) : ?>nav-tab-active<?php endif; ?>">
      <?php 
        echo $this->translator->sml_translate('basic_setting');
      ?>
    </a>
  </nav>


  <div class="tab-content">
    <?php 
        echo '<form id="setting-form" name="frmSetting" method="post" action="options.php?tap=advance-settings">';
        echo '<input type="hidden" name="is_save" value="1">';
        echo '<input type="hidden" name="tab" value="2">';
        settings_fields($this->options_key);
        do_settings_sections($this->options_key);
        submit_button();
        echo '</form>';
    ?>
  </div>
  <div class="mb-5">
      <?php
      include_once(plugin_dir_path(__DIR__) . 'forms/clear_cache.php');
      ?>
    </div>
    <div>
      <?php
      include_once(plugin_dir_path(__DIR__) . 'forms/clear_spiral_setting.php');
      ?>
    </div>
</div>
<?php 
  include_once(plugin_dir_path(__DIR__) . 'scripts/confirm_modal.php');
  include_once(plugin_dir_path(__DIR__) . 'scripts/alert_script.php');
  include_once(plugin_dir_path(__DIR__) . 'scripts/confirm_script.php');
?>
<?php } ?>