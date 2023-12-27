<?php
/**
 * Represents the view for the user info.
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
$is_taget_blank = $this->get_option('enable_blank_tab');
?>
<div class="sml-login" id="spiral-v2-member-login<?php $template->the_template_num(); ?>">
	<?php $template->the_user_name(); ?>
	<?php 
		if($is_taget_blank){
			$template->the_user_links_widget_target_blank();
		}else{
			$template->the_user_links_widget(); 
		}
	?>
	<?php do_action( 'sml_user_info' ); ?>
</div>