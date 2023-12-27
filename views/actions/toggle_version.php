<?php if ( (get_option('sml_is_setup') == true) && !(get_option('spiral_member_login'))) { ?>
  <form method="post" id="choouse_version">
    <table class="form-table" role="presentation">
      <tr>
        <th scope="row">
          <h2>WP Member Login by SPIRAL</h2>
          <p>
            <?php
                echo $this->translator->sml_translate('please_choose_your_spiral_version');
            ?>
          </p>
        </th>
      </tr>
      <tr>
        <td id="front-static-pages">
          <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Your homepage displays'); ?></span></legend>
            <p><label>
                <input name="show_on_front" type="radio" value="1" class="tog" required />
                <?php _e('SPIRAL ver.1'); ?>
              </label>
            </p>
            <p><label>
                <input name="show_on_front" type="radio" value="2" class="tog" required />
                <?php _e('SPIRAL ver.2'); ?>
              </label>
            </p>
            <?php wp_nonce_field('spiral_version', 'spiral_version'); ?>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="            <?php
                echo $this->translator->sml_translate('next');
            ?>"></p>
          </fieldset>
        </td>
      </tr>
    </table>
  </form>
<?php } ?>