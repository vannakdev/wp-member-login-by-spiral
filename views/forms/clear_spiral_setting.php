<form id="clear-spiral-setting-form" name="frmProduct" method="post" style="border: 1px solid #303030;background: #fff;width: 60%;padding: 10px 20px;">
    <?php wp_nonce_field('clear_spiral_setting', 'clear_spiral_setting'); ?>
    <h2><?php echo $this->translator->sml_translate('clear_all_spiral_setting') ?></h2>
    <button name="btn_clear_spiral_setting" class="button button-primary clear_spiral_setting" type="submit"><?php echo $this->translator->sml_translate('clear') ?></button>
</form>