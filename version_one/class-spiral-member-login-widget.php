<?php
/**
 * Holds the Spiral Member Login widget class
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */

if ( ! class_exists( 'WPMLS_Spiral_Member_Login_Widget' ) ) :
/*
 * Spiral Member Login widget class
 *
 * @since 1.0.0
 */
class WPMLS_Spiral_Member_Login_Widget extends WP_Widget {
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */

	public function __construct() {
		$widget_options = array(
			'classname'   => 'widget_spiral_member_login',
			'description' => __( 'SPIRAL Login widget for your site', Spiral_Member_Login::domain )
		);
		parent::__construct( 'spiral_member_login', __( 'WP Member Login by SPIRAL', Spiral_Member_Login::domain ), $widget_options );
		$this->translator 			= 	new WPMLS_Translator();
		add_option('is_blank_page',0);
	}

	/**
	 * Displays the widget
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	public function widget( $args, $instance ) {

		$spiral_member_login = Spiral_Member_Login::get_instance();

		$instance = wp_parse_args( $instance, array(
			'default_action'       => 'login',
			'logged_in_widget'     => true,
			'logged_out_widget'    => true,
			'show_name'            => true,
			'show_title'           => true,
			'show_reg_link'        => true,
			'show_pass_link'       => true,
			'show_profile_link'    => true,
			'show_resetpass_link'  => true,
			'show_withdrawal_link' => true,
			'enable_blank_tab' 	   => false
		) );

		// Show if logged in?
		if ( $spiral_member_login->is_logged_in() && ! $instance['logged_in_widget'] )
			return;

		// Show if logged out?
		if ( ! $spiral_member_login->is_logged_in() && ! $instance['logged_out_widget'] )
			return;

		if ( Spiral_Member_Login::is_sml_page() && $spiral_member_login->get_page_action( get_the_ID() ) == 'login' ) {
			return;
		}
		$args = array_merge( $args, $instance );
		$args_widget = array_merge($args, ['is_widget' => true]);

		echo $spiral_member_login->shortcode_show_template( $args_widget );
	}

	/**
	* Updates the widget
	*
	* @since 1.0.0
	* @access public
	*/
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['default_action']       = in_array( $new_instance['default_action'], array( 'login', 'register', 'lostpassword' ) ) ? 	   $new_instance['default_action'] : 'login';
		$instance['logged_in_widget']     = ! empty( $new_instance['logged_in_widget'] );
		$instance['logged_out_widget']    = ! empty( $new_instance['logged_out_widget'] );
		$instance['show_name']            = ! empty( $new_instance['show_name'] );
		$instance['show_title']           = ! empty( $new_instance['show_title'] );
		$instance['show_reg_link']        = ! empty( $new_instance['show_reg_link'] );
		$instance['show_pass_link']       = ! empty( $new_instance['show_pass_link'] );
		$instance['show_profile_link']    = ! empty( $new_instance['show_profile_link'] );
		$instance['show_resetpass_link']  = ! empty( $new_instance['show_resetpass_link'] );
		$instance['show_withdrawal_link'] = ! empty( $new_instance['show_withdrawal_link'] );
		$instance['enable_blank_tab']     = ! empty( $new_instance['enable_blank_tab'] );
		update_option('is_blank_page',$instance['enable_blank_tab']);
		return $instance;
	}

	/**
	* Displays the widget admin form
	*
	* @since 1.0.0
	* @access public
	*/
	public function form( $instance ) {
		$defaults = array(
			'default_action'       => 'login',
			'logged_in_widget'     => 1,
			'logged_out_widget'    => 1,
			'show_name'            => 1,
			'show_title'           => 1,
			'show_reg_link'        => 1,
			'show_pass_link'       => 1,
			'show_profile_link'    => 1,
			'show_resetpass_link'  => 1,
			'show_withdrawal_link' => 1,
			'register_widget'      => 1,
			'enable_blank_tab'      => 0,
			'lostpassword_widget'  => 1
		);
		$instance = wp_parse_args( $instance, $defaults );

		$is_checked = ( empty( $instance['logged_in_widget'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'logged_in_widget' ) . '" type="checkbox" id="' . $this->get_field_id( 'logged_in_widget' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'logged_in_widget' ) . '">' . __( $this->translator->sml_translate('show_when_logged_in'), Spiral_Member_Login::domain ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['logged_out_widget'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'logged_out_widget' ) . '" type="checkbox" id="' . $this->get_field_id( 'logged_out_widget' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'logged_out_widget' ) . '">' . __( $this->translator->sml_translate('show_when_logged_out'), Spiral_Member_Login::domain ) . '</label></p>' . "\n";

		echo '<hr/>' . "\n";

		$is_checked = ( empty( $instance['show_title'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_title' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_title' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_title' ) . '">' . __( $this->translator->sml_translate('show_title'), Spiral_Member_Login::domain ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_name'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_name' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_name' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_name' ) . '">' . __( $this->translator->sml_translate('show_name'), Spiral_Member_Login::domain ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_reg_link'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . esc_attr( $this->get_field_name( 'show_reg_link' ) ) . '" type="checkbox" id="' . esc_attr( $this->get_field_id( 'show_reg_link' ) ) . '" value="1" ' . $is_checked . '/> <label for="' . esc_attr( $this->get_field_id( 'show_reg_link' ) ) . '">' . esc_html__( $this->translator->sml_translate('show_register_link'), Spiral_Member_Login::domain ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_pass_link'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_pass_link' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_pass_link' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_pass_link' ) . '">' . __( $this->translator->sml_translate('show_lost_password_link'), Spiral_Member_Login::domain ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_profile_link'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_profile_link' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_profile_link' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_profile_link' ) . '">' . __( $this->translator->sml_translate('show_profile_link'), Spiral_Member_Login::domain ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_resetpass_link'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_resetpass_link' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_resetpass_link' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_resetpass_link' ) . '">' . __( $this->translator->sml_translate('show_reset_password_link'), Spiral_Member_Login::domain ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_withdrawal_link'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_withdrawal_link' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_withdrawal_link' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_withdrawal_link' ) . '">' . __( $this->translator->sml_translate('show_withdrawal_link'), Spiral_Member_Login::domain ) . '</label></p>' . "\n";
		$is_checked = ( empty( $instance['enable_blank_tab'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'enable_blank_tab' ) . '" type="checkbox" id="' . $this->get_field_id( 'enable_blank_tab' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'enable_blank_tab' ) . '">' . __( $this->translator->sml_translate('enable_blank_tab'), Spiral_Member_Login::domain ) . '</label></p>' . "\n";
		
	}
}
endif; // Class exists