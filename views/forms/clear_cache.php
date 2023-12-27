<form id="setting-form" name="frmSetting" method="post" style="border: 1px solid #303030;background: #fff;width: 60%;padding: 10px 20px;">
    <?php wp_nonce_field('clear_cache_db', 'clear_cache_db'); ?>
    <h2><?php echo $this->translator->sml_translate('clear_login_and_api_cache') ?></h2>
    <button name="clear_cache_db" class="button button-primary clear_cache" type="submit"><?php echo $this->translator->sml_translate('clear') ?></button>
</form>