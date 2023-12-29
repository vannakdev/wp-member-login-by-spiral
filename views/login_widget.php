<?php

/**
 * Represents the view for the login form.
 *
 * @package   spiral_member_login
 * @author    PIPED BITS Co.,Ltd.
 */

if (isset($_REQUEST["message"])) {
	$error_message = (get_locale()  == "en_US") ? "Log in error" : "ログインエラー";
}

if (empty(get_option('spiral_member_login')["login_id_label_en"])) {
	$login_label_default_text_en = "Email Address";
}
if (empty(get_option('spiral_member_login')["login_id_label_en"])) {
	$login_label_default_text_jp = "ユーザー名";
}
$is_taget_blank = $this->get_option('enable_blank_tab');

?>
<style>
	.sml-login p {
		margin-bottom: 20px;
		text-align: left;
		display: table;
		width: 100%;
	}

	.sml-login p label {
		font-weight: 400;
		width: 30%;
		vertical-align: middle;
		display: table-cell;
		vertical-align: middle;
	}

	.sml-login input[name="login_id"],
	.sml-login input[name="password"] {
		border-radius: 4px;
		margin-left: 10px;
		width: 70%;
		display: table-cell;
	}

	.sml-login input[name="wp-submit"] {
		border: none;
		cursor: pointer;
		outline: none;
		-webkit-box-sizing: border-box;
		-webkit-appearance: button;
		appearance: none;
		width: 200px;
		background: #252f7f;
		background: -moz-linear-gradient(left, #330867, #0092bc);
		background: -webkit-linear-gradient(left, #330867, #0092bc);
		background: linear-gradient(to right, #330867, #0092bc);
		border-radius: 4px;
		box-shadow: 0px 1px 2px rgb(0 0 0 / 20%);
		color: #fff;
		padding: 0.5em 1.5em;
		margin: 0 auto;
		text-align: center;
		display: block;
	}

	.log-in-btn {
		color: #f2f2f2;
		display: flex;
		justify-content: center;
		align-items: center;
		background: linear-gradient(to right, #330867, #0092bc);
		border-style: none;
		border-radius: 5px;
		width: 200px;
		height: 50px;
		margin-top: 20px;
		/* margin-left: 35%;*/
		margin-right: 20%; 
		float: right;
	}

	.log-in-btn:hover {
		color: white;
	}
</style>
<div class="sml-login" id="spiral-member-login<?php $template->the_template_num(); ?>">
	<?php
	if (isset($_REQUEST["message"])) {
		echo '<p class="error sml-login-error-message">' . $error_message . '<br></p>';
	}
	?>
	<form onsubmit="return befor_submit()" name="loginform" id="loginform<?php $template->the_template_num(); ?>" action="<?php $template->the_auth_form_url(); ?>" method="post">
		<p>
			<label for="sml-label-user-login">
				<?php
				if (!empty(get_option('spiral_member_login')["login_id_label_en"])) {
					echo (get_locale() == "en_US") ? get_option('spiral_member_login')["login_id_label_en"] : ((get_locale() == "ja") ? get_option('spiral_member_login')["login_id_label_jp"] : get_option('spiral_member_login')["login_id_label_jp"]);
				} else {
					echo (get_locale() == "en_US") ? $login_label_default_text_en : ((get_locale() == "ja") ? $login_label_default_text_jp : $login_label_default_text_jp);
				}
				?>
			</label>
			<input required type="text" name="login_id" id="user_login<?php $template->the_template_num(); ?>" class="input" value="<?php $template->the_posted_value('login_id'); ?>" size="20" />
		</p>
		<p>
			<label for="sml-label-user-pass"><?php _e('Password'); ?></label>
			<input required type="password" name="password" id="user_pass<?php $template->the_template_num(); ?>" class="input" value="" size="20" />
		</p>
		<p class="submit">
			<input type="hidden" name="wpmls_identification_key" value="<?php echo get_option('spiral_member_login')["wpmls_member_identification_key"]; ?>" />
			<input type="hidden" name="wpmls_area_title" value="<?php echo get_option('spiral_member_login')["wpmls_area_title"]; ?>" />
			<input type="hidden" name="custom_module" value="<?php echo get_option('spiral_member_login')["wpmls_custom_module_path"]; ?>" />
			<button class="sml-login-submit log-in-btn" type="submit" name="wp-submit" id="wp-submit<?php $template->the_template_num(); ?>"><span><?php _e('Log in'); ?></span></button>
			<input type="hidden" name="template_num" value="<?php $template->the_template_num(); ?>" />
			<input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url(); ?>" />
			<input type="hidden" name="action" value="login" />
			<input type="hidden" name="detect" value="判定" />
		</p>
	</form>
</div>
<?php
	if($is_taget_blank){
		$template->the_action_links_widget_target_blank(); 
	}else{
		$template->the_action_links_widget(); 
	}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    var action_url = $('form[name="loginform"]').attr('action').replace(/regist/ig, "area");
    let final_url = action_url;

    // Regexp that match regXXX url
    const regex = /\/\/reg+[0-9]{1,}/i;
    // Get regex word
    const regWord = action_url.match(regex);

    // If found word
    if (regWord) {
        // Replace reg by area
        const replacedArea = regWord.toString().replace(/reg/i, 'area');

        // Replace final regex by replacedArea
        final_url = action_url.replace(regex, replacedArea);
    }

    $('form[name="loginform"]').attr('action', final_url);
</script>