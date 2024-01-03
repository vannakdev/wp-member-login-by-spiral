<?php

/**
 * WP Member Login by SPIRAL.
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */

if (!class_exists('Spiral_Member_Login')) :
	/**
	 * Plugin class.
	 *
	 * @package Spiral_Member_Login
	 * @author  PIPED BITS Co.,Ltd.
	 */
	class Spiral_Member_Login extends WPMLS_Spiral_Member_Login_Base
	{

		/**
		 * Plugin version
		 *
		 * @since   2.0.0
		 *
		 * @const     string
		 */
		const version = '2.0.0';

		/**
		 * Plugin slug
		 *
		 * @since   2.0.0
		 * @var     string
		 */
		protected $plugin_slug = 'spiral-member-login';

		/**
		 * Holds options key
		 *
		 * @access protected
		 * @var string
		 */
		protected $options_key = 'spiral_member_login';

		/**
		 * Unique identifier for your plugin.
		 *
		 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
		 * match the Text Domain file header in the main plugin file.
		 *
		 * @since    1.0.0
		 *
		 * @const      string
		 */
		const domain = 'spiral-member-login';

		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;

		/**
		 * Slug of the plugin screen.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		protected $plugin_screen_hook_suffix = null;

		/**
		 * Holds errors object
		 *
		 * @access public
		 * @var object
		 */
		public $errors;

		/**
		 * Holds current page being requested
		 *
		 * @access public
		 * @var string
		 */
		public $request_page;

		/**
		 * Holds current action being requested
		 *
		 * @access public
		 * @var string
		 */
		public $request_action;

		/**
		 * Holds current template being requested
		 *
		 * @access public
		 * @var int
		 */
		public $request_template_num;

		/**
		 * Holds loaded template instances
		 *
		 * @access protected
		 * @var array
		 */
		protected $loaded_templates = array();

		/**
		 * WP Session for SML
		 */
		public $session;

		/**
		 * SPIRAL API
		 */
		public $spiral;

		public  $page_option_keys = [
			"wpmls_withdrawal_page_id",
			"wpmls_profile_page_id",
			"wpmls_resetpass_page_id"
		];

		/**
		 * Initialize the plugin by setting localization, filters, and administration functions.
		 *
		 */
		private function __construct()
		{
			$this->load_options();
			$this->load_template();
			$this->load_plugin_textdomain();

			// wp actions
			add_action('init', array(&$this, 'init'));
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'admin_menu'));
			add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_styles'));
			add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_scripts'));
			// style
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_styles'));
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
			add_action('widgets_init', array(&$this, 'widgets_init'));
			add_action('wp', array(&$this, 'wp'));
			add_action('template_redirect', array(&$this, 'template_redirect'));
			add_action('wp_head', array(&$this, 'wp_head'));
			add_action('wp_footer', array(&$this, 'wp_footer'));
			add_action('wp_print_footer_scripts', array(&$this, 'wp_print_footer_scripts'));

			// wp filters
			add_filter('wp_setup_nav_menu_item', array(&$this, 'wp_setup_nav_menu_item'));
			add_filter('wp_list_pages_excludes', array(&$this, 'wp_list_pages_excludes'));
			add_filter('page_link', array(&$this, 'page_link'), 10, 2);

			// wp shortcodes
			add_shortcode('sml-show-template', array(&$this, 'shortcode_show_template'));
			add_shortcode('sml-is-logged-in', array(&$this, 'shortcode_is_logged_in'));
			add_shortcode('sml-is-logged-mypage', array(&$this, 'shortcode_mypage_url'));
			add_shortcode('sml-is-logged-in-hide', array(&$this, 'shortcode_is_logged_in_hide'));
			add_shortcode('sml-user-prop', array(&$this, 'shortcode_user_prop'));
			add_shortcode('sml-is-logged-in-type', array(&$this, 'shortcode_is_logged_in_type'));
			add_shortcode('sml-is-logged-in-rule', array(&$this, 'shortcode_is_logged_in_rule'));
			add_shortcode('sml-link', array(&$this, 'shortcode_user_link'));

			// setup session
			$this->session = new WPMLS_Spiral_Member_Login_Session();

			$api_token_key =  $this->wpfws_dos_soar($this->get_option('wpmls_api_token'), SECURE_AUTH_KEY);
			$api_token_secret_key =  $this->wpfws_dos_soar($this->get_option('wpmls_api_token_secret'), SECURE_AUTH_KEY);

			$this->spiral = new WPMLS_Spiral_Api($api_token_key, $api_token_secret_key);

			$this->translator 			= 	new WPMLS_Translator();

			if ($this->is_settings_imcomplete()){
				return null;
			}else{
				if(!isset($this->get_options()['wpmls_api_token']))
				$this->wpmls_modify_option_keys();
			}
		}

		public function wpmls_modify_option_keys()
        {
            $options = $this->get_options();
            $mapping = [
                "api_token" => "wpmls_api_token",
                "api_token_secret"=> "wpmls_api_token_secret",
                "member_db_title" => "wpmls_member_db_title",
                "member_identification_key" => "wpmls_member_identification_key",
                "area_title" => "wpmls_area_title",
                "custom_module_path" => "wpmls_custom_module_path",
                "auth_form_url" => "wpmls_auth_form_url",
                "member_list_search_title" => "wpmls_member_list_search_title",
                "default_name_key" => "wpmls_default_name_key",
                "login_id_label_jp" => "wpmls_login_id_label_jp",
                "login_id_label_en" => "wpmls_login_id_label_en",
                "register_url" => "wpmls_register_url",
                "lostpassword_url" => "wpmls_lostpassword_url",
                "member_domain_name" => "wpmls_member_domain_name",
                "member_logout_url" => "wpmls_member_logout_url",
                "profile_page_id" => "wpmls_profile_page_id",
                "resetpass_page_id" => "wpmls_resetpass_page_id",
                "withdrawal_page_id" => "wpmls_withdrawal_page_id",
                "related_web" => "wpmls_related_web",
                "version" => "version"
            ];
            
            $new_keys = array_map(function ($key) use ($mapping) {
                return $mapping[$key];
            }, array_keys($options));

            $new_option = array_combine($new_keys, $options);
            update_option($this->options_key,$new_option);
        }

		


		/************************************************************************************************************************
		 * Hooks
		 ************************************************************************************************************************/

		/**
		 * Fired when the plugin is activated.
		 *
		 * @since    1.0.0
		 *
		 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
		 */
		public static function activate($network_wide)
		{
		}

		/**
		 * Fired when the plugin is deactivated.
		 *
		 * @since    1.0.0
		 *
		 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
		 */
		public static function deactivate($network_wide)
		{
		}

		/**
		 * Uninstall hook
		 *
		 * @access public
		 */
		public static function uninstall()
		{
			global $wpdb;

			if (is_multisite()) {
				if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
					$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
					foreach ($blogids as $blog_id) {
						switch_to_blog($blog_id);
						self::_uninstall();
					}
					restore_current_blog();
					return;
				}
			}
			self::_uninstall();
		}


		/************************************************************************************************************************
		 * Actions
		 ************************************************************************************************************************/

		/**
		 * Initilizes the plugin
		 *
		 * @since    1.0.0
		 */
		public function init()
		{
			$this->errors = new WP_Error();
		}
		function wholesomecode_wholesome_plugin_block_categories($categories)
		{
			return array_merge(
				$categories,
				[
					[
						'slug'  => 'spiral-member-login',
						'title' => __('Wholesome Blocks', 'wholesome-boilerplate'),
					],
				]
			);
		}

		/**
		 * Register plugin's setting and Install
		 *
		 * @since    1.0.0
		 */
		public function admin_init()
		{
			register_setting($this->options_key, $this->options_key, array(&$this, 'save_settings'));

			if (version_compare($this->get_option('version', 0), self::version, '<')) {
				$this->install();
			}
		}

		/**
		 * Register and enqueue admin-specific style sheet.
		 *
		 *
		 * @return    null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_styles()
		{
			if (!isset($this->plugin_screen_hook_suffix)) {
				return;
			}

			$screen = get_current_screen();
			if ($screen->id == $this->plugin_screen_hook_suffix) {
				wp_enqueue_style($this->plugin_slug . '-admin-styles', plugins_url('css/admin.css', __DIR__), false, self::version);
			}
		}

		/**
		 * Register and enqueue admin-specific JavaScript.
		 *
		 * @return    null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_scripts()
		{
			if (!isset($this->plugin_screen_hook_suffix)) {
				return;
			}

			$screen = get_current_screen();
			if ($screen->id == $this->plugin_screen_hook_suffix) {
				wp_enqueue_script($this->plugin_slug . '-admin-script', plugins_url('js/admin.js', __DIR__), array('jquery'), self::version);
			}
		}


		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles()
		{
			//wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), false, self::version );
		}

		/**
		 * Register and enqueues public-facing JavaScript files.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts()
		{
			//wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), self::version );
		}

		/**
		 * Register the administration menu for this plugin into the WordPress Dashboard menu.
		 *
		 * @since    1.0.0
		 */
		public function admin_menu()
		{
			$this->plugin_screen_hook_suffix = add_options_page(
				__('WP Member Login by SPIRAL', self::domain),
				__('WP Member Login by SPIRAL', self::domain),
				'read',
				$this->options_key,
				array($this, 'display_plugin_admin_page')
			);
			add_settings_section('api', __($this->translator->sml_translate('api_agen_api_key'), self::domain), '__return_false', $this->options_key);
			add_settings_section('auth', __($this->translator->sml_translate('authentication_setting'), self::domain), '__return_false', $this->options_key);
			add_settings_section('userprop', __($this->translator->sml_translate('member_information_setting'), self::domain), '__return_false', $this->options_key);
			add_settings_section('link', __($this->translator->sml_translate('each_link_setting'), self::domain), '__return_false', $this->options_key);
			add_settings_section('logout', __($this->translator->sml_translate('after_logout_page_url_setting'), self::domain), '__return_false', $this->options_key);
			add_settings_section(
				'web',
				__(
					$this->translator->sml_translate('relate_web_no_ashiato'),
					self::domain
				),
				'__return_false',
				$this->options_key
			);

			// api
			add_settings_field('wpmls_api_token', __($this->translator->sml_translate('api_key'), self::domain), array(&$this, 'settings_field_api_token'), $this->options_key, 'api', ["class" => "basic-config-label api-token"]);
			add_settings_field('wpmls_api_token_secret', __($this->translator->sml_translate('wpmls_api_token_secret'), self::domain), array(&$this, 'settings_field_api_token_secret'), $this->options_key, 'api', ["class" => "basic-config-label"]);

			// auth
			add_settings_field('wpmls_member_db_title', __($this->translator->sml_translate('db_title'), self::domain), array(&$this, 'settings_field_member_db_title'), $this->options_key, 'auth', ["class" => "advance-config hidden"]);
			add_settings_field('wpmls_identification_key', __($this->translator->sml_translate('my_area_identification_key'), self::domain), array(&$this, 'settings_field_identification_key'), $this->options_key, 'auth', ["class" => "advance-config hidden"]);
			add_settings_field('wpmls_area_title', __($this->translator->sml_translate('my_area_title'), self::domain), array(&$this, 'settings_field_area_title'), $this->options_key, 'auth', ["class" => "advance-config hidden"]);
			// 			add_settings_field('api_token_title', __($this->translator->sml_translate('api_token_title'), self::domain), array(&$this, 'settings_field_api_token_title'), $this->options_key, 'auth', ["class" => "advance-config hidden"]);
			add_settings_field('wpmls_custom_module_path', __($this->translator->sml_translate('wpmls_custom_module_path'), self::domain), array(&$this, 'settings_field_custom_module_path'), $this->options_key, 'auth', ["class" => "advance-config hidden"]);
			add_settings_field('wpmls_auth_form_url', __($this->translator->sml_translate('authentication_form_url'), self::domain), array(&$this, 'settings_field_auth_form_url'), $this->options_key, 'auth', ["class" => "basic-config-label"]);

			// userprop
			add_settings_field('wpmls_member_list_search_title', __($this->translator->sml_translate('wpmls_member_list_search_title'), self::domain), array(&$this, 'settings_field_member_list_search_title'), $this->options_key, 'userprop', ["class" => "advance-config hidden"]);
			add_settings_field('wpmls_default_name_key', __($this->translator->sml_translate('wpmls_default_name_key'), self::domain), array(&$this, 'settings_field_default_name_key'), $this->options_key, 'userprop', ["class" => "basic-config-label"]);
			add_settings_field('wpmls_login_id_label', __('ログインフォームのIDラベル', self::domain), array(&$this, 'settings_field_login_id_label'), $this->options_key, 'userprop', ["class" => "basic-config-label"]);

			// link
			add_settings_field('wpmls_register_url', __($this->translator->sml_translate('wpmls_register_url'), self::domain), array(&$this, 'settings_field_register_url'), $this->options_key, 'link', ["class" => "basic-config-label link-setting"]);
			add_settings_field('wpmls_lostpassword_url', __($this->translator->sml_translate('lost_password_url'), self::domain), array(&$this, 'settings_field_lostpassword_url'), $this->options_key, 'link', ["class" => "basic-config-label link-setting"]);
			add_settings_field('wpmls_profile_page_id', __($this->translator->sml_translate('wpmls_profile_page_id'), self::domain), array(&$this, 'settings_field_profile_page_id'), $this->options_key, 'link', ["class" => "basic-config-label link-setting"]);
			add_settings_field('wpmls_resetpass_page_id', __($this->translator->sml_translate('reset_password_page_id'), self::domain), array(&$this, 'settings_field_resetpass_page_id'), $this->options_key, 'link', ["class" => "basic-config-label link-setting"]);
			add_settings_field('wpmls_withdrawal_page_id', __($this->translator->sml_translate('wpmls_withdrawal_page_id'), self::domain), array(&$this, 'settings_field_withdrawal_page_id'), $this->options_key, 'link', ["class" => "basic-config-label link-setting"]);

			// Logout
			add_settings_field('wpmls_logout_url', __($this->translator->sml_translate('url_after_logout'), self::domain), array(&$this, 'settings_field_logout_url'), $this->options_key, 'logout', ["class" => "advance-config hidden"]);
			// Web section


			add_settings_field('is_enable', $this->translator->sml_translate('use_this_function'), array(&$this, 'settings_field_is_enable'), $this->options_key, 'web', ["class" => "advance-config"]);
			add_settings_field('param_name', $this->translator->sml_translate('parameter_name'), array(&$this, 'settings_field_param_name'), $this->options_key, 'web', ["class" => "advance-config"]);
			add_settings_field('filed_name', $this->translator->sml_translate('field_name'), array(&$this, 'settings_field_filed_name'), $this->options_key, 'web', ["class" => "advance-config"]);
		}

		public function settings_field_is_enable()
		{
			$is_enable  = isset(get_option('spiral_member_login')['related_web']) ? get_option('spiral_member_login')['related_web']['is_enable'] : false;
			$is_checked = $is_enable ? 'checked' : '';
?>
			<div>
				<input id="is_enable" type="checkbox" pattern="https?://.+" name="is_enable" type="text" class="sml_url_field sml_member_logout_url_field advance-config" value="<?php echo $is_enable ?>" <?php echo $is_checked; ?> />
			</div>
			<script>
				const checkbox = document.querySelector("#is_enable");

				checkbox.addEventListener("click", function() {
					if (checkbox.checked) {
						checkbox.value = 1;
					} else {
						checkbox.value = 0;
					}
				});
			</script>

		<?php
		}
		public function settings_field_param_name()
		{
			$param_name = isset(get_option('spiral_member_login')['related_web']['atts']) ? get_option('spiral_member_login')['related_web']['atts']['param_name'] : '';
		?>
			<div>
				<div class="" id="web_id">
					<input name="param_name" type="text" class="sml_login_id_label_jp basic_config" value="<?php echo $param_name; ?>" />
				</div>
			</div>

		<?php
		}
		public function settings_field_filed_name()
		{
			$field_name = isset(get_option('spiral_member_login')['related_web']['atts']) ? get_option('spiral_member_login')['related_web']['atts']['field_name'] : '';
		?>
			<div>
				<div class="" id="web_id">
					<input name="field_name" type="text" class="sml_login_id_label_en basic_config" value="<?php echo $field_name; ?>" />
				</div>
			</div>

		<?php
		}


		/**
		 * Registers the widget
		 *
		 * @access public
		 */
		public function widgets_init()
		{
			if (class_exists('WPMLS_Spiral_Member_Login_Widget')) {
				register_widget('WPMLS_Spiral_Member_Login_Widget');
			}
		}

		/**
		 * Used to add/remove filters from login page
		 *
		 * @access public
		 */
		public function wp()
		{
			if (self::is_sml_page()) {
				do_action('login_init');

				remove_action('wp_head', 'feed_links',                       2);
				remove_action('wp_head', 'feed_links_extra',                 3);
				remove_action('wp_head', 'rsd_link');
				remove_action('wp_head', 'wlwmanifest_link');
				remove_action('wp_head', 'parent_post_rel_link',            10);
				remove_action('wp_head', 'start_post_rel_link',             10);
				remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
				remove_action('wp_head', 'rel_canonical');

				// Don't index any of these forms
				add_action('login_head', 'wp_no_robots');

				if (force_ssl_admin() && !is_ssl()) {
					if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {
						wp_redirect(preg_replace('|^http://|', 'https://', $_SERVER['REQUEST_URI']));
						exit;
					} else {
						wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
						exit;
					}
				}
			}
		}


		public function get_wp_redirect_url()
		{
			$logout_redirect_url = get_option("spiral_member_login")["member_logout_url"];
			if (isset($logout_redirect_url) && $logout_redirect_url != null) {
				return $logout_redirect_url;
			}
			global $wp;
			if ('' === get_option('permalink_structure')) return home_url(add_query_arg(array($_GET), $wp->request));
			else return home_url(trailingslashit(add_query_arg(array($_GET), $wp->request)));
		}

		function encrypt($string, $key = 5)
		{
			$result = '';
			for ($i = 0, $k = strlen($string); $i < $k; $i++) {
				$char = substr($string, $i, 1);
				$keychar = substr($key, ($i % strlen($key)) - 1, 1);
				$char = chr(ord($char) + ord($keychar));
				$result .= $char;
			}
			return base64_encode($result);
		}

		function decrypt($string, $key = 5)
		{
			$result = '';
			$string = base64_decode($string);
			for ($i = 0, $k = strlen($string); $i < $k; $i++) {
				$char = substr($string, $i, 1);
				$keychar = substr($key, ($i % strlen($key)) - 1, 1);
				$char = chr(ord($char) - ord($keychar));
				$result .= $char;
			}
			return $result;
		}


		/**
		 * Proccesses the request
		 *
		 * Callback for "template_redirect" hook in template-loader.php
		 *
		 * @access public
		 */
		public function template_redirect()
		{
			$this->request_action = isset($_REQUEST['action']) ? sanitize_key($_REQUEST['action']) : '';
			if (!$this->request_action && self::is_sml_page()) {
				$this->request_action = self::get_page_action(get_the_ID());
			}
			$this->request_template_num = isset($_REQUEST['template_num']) ? sanitize_key($_REQUEST['template_num']) : 0;
			if ($this->is_settings_imcomplete()) {
				if ($this->request_action) {
					wp_redirect(get_home_url('/'));
					exit;
				}
				return;
			}

			do_action_ref_array('sml_request', array(&$this));

			if (has_action('sml_request_' . $this->request_action)) {
				do_action_ref_array('sml_request_' . $this->request_action, array(&$this));
			} else {
				$is_post = ('POST' == $_SERVER['REQUEST_METHOD']);
				switch ($this->request_action) {
					case 'logout':
						$sml_sid = $this->session->get('sml_sid');
						$wpmls_area_title = $this->get_option('wpmls_area_title');

						if ($sml_sid) {
							@setcookie('is_login', false, time() - 1800, COOKIEPATH, COOKIE_DOMAIN, TRUE, TRUE);
							$result = $this->spiral->logout_area($wpmls_area_title, $sml_sid);
							$this->clear_user_options();
						}

						if ($this->get_option('member_logout_url')) {
							$logout_setting_url = $this->get_option('member_logout_url');
							wp_redirect($logout_setting_url);
							exit;
						}
						wp_redirect($_SERVER['HTTP_REFERER']);
						exit;
					case 'register':
						$sml_sid = $this->session->get('sml_sid');
						if (isset($_GET['id'])) {
							$option_name = 'shortcode_mypage_url' . $sml_sid . '_' . $this->request_action . '_page_id_' . $_GET['id'];
							if (get_option($option_name)) {
								$shortcode_mypage_url = get_option($option_name);
								if (isset($_GET['param'])) {
									wp_redirect($this->decrypt_key($shortcode_mypage_url, SECURE_AUTH_KEY) . '&' . $_GET['param']);
									exit;
								}
								wp_redirect($this->decrypt_key($shortcode_mypage_url, SECURE_AUTH_KEY));
								exit;
							} else {
								$wpmls_area_title = $this->get_option('wpmls_area_title');
								$result = $this->spiral->get_area_mypage($wpmls_area_title, $sml_sid, $_GET['id']);

								if (is_null($result)) {
									wp_redirect(get_home_url('/'));
									exit;
								} else {
									update_option($option_name, $this->encrypt_key($result, SECURE_AUTH_KEY));
									if (isset($_GET['param'])) {
										wp_redirect($result . '&' . $_GET['param']);
										exit;
									}
									wp_redirect($result);
									exit;
								}
							}
						}
						if ($wpmls_register_url = $this->get_option('wpmls_register_url')) {
							wp_redirect($wpmls_register_url);
							exit;
						} else {
							wp_redirect(get_home_url('/'));
							exit;
						}
						break;
					case 'lostpassword':
						if ($wpmls_lostpassword_url = $this->get_option('wpmls_lostpassword_url')) {
							wp_redirect($wpmls_lostpassword_url);
							exit;
						} else {
							wp_redirect(get_home_url('/'));
							exit;
						}
						break;
					case 'resetpass':
					case 'withdrawal':

						$page_id = $this->get_option($this->request_action . '_page_id');
						$sml_sid = $this->session->get('sml_sid');

						if ($this->is_logged_in()) {
							if ($page_id) {
								$withdrawal_option_name = 'page_url_' . $sml_sid . '_' . $this->request_action . '_page_id_' . $page_id;
								if (get_option($withdrawal_option_name)) {
									$withdrawal_url = get_option($withdrawal_option_name);
									wp_redirect($this->decrypt_key($withdrawal_url, SECURE_AUTH_KEY));
									exit;
								} else {
									$wpmls_area_title = $this->get_option('wpmls_area_title');
									$result = $this->spiral->get_area_mypage($wpmls_area_title, $sml_sid, $page_id);
									if ($result) {
										update_option($withdrawal_option_name, $this->encrypt_key($result, SECURE_AUTH_KEY));
										wp_redirect($result);
										exit;
									}
								}
							}
							wp_redirect(get_home_url('/'));
							exit;
						} else {
							$this->clear_user_options();
							if ($page_id) {
								wp_redirect(self::get_page_link('login', 'expired=true'));
							} else {
								wp_redirect(get_home_url('/'));
							}
							exit;
						}
						break;
					case 'profile':
						$page_id = $this->get_option($this->request_action . '_page_id');
						$sml_sid = $this->session->get('sml_sid');
						if ($this->is_logged_in()) {
							if ($page_id) {
								$profile_option_name = 'page_url_' . $sml_sid . '_' . $this->request_action . '_page_id_' . $page_id;
								if (get_option($profile_option_name)) {
									var_dump("1");
									die;
									$profile_url = get_option($profile_option_name);
									wp_redirect($this->decrypt_key($profile_url, SECURE_AUTH_KEY));
									exit;
								} else {
									var_dump("2");
									die;
									$wpmls_area_title = $this->get_option('wpmls_area_title');
									$result = $this->spiral->get_area_mypage($wpmls_area_title, $sml_sid, $page_id);
									if ($result) {
										update_option($profile_option_name, $this->encrypt_key($result, SECURE_AUTH_KEY));
										wp_redirect($result);
										exit;
									}
								}
							}
							wp_redirect(get_home_url('/'));
							exit;
						} else {
							if ($page_id) {
								wp_redirect(self::get_page_link('login', 'expired=true'));
							} else {
								wp_redirect(get_home_url('/'));
							}
							exit;
						}
						break;
					case 'login':
					default:
						if ($is_post && isset($_REQUEST['sml-sid'])) {
							$sml_sid    = $_REQUEST['sml-sid'];
							$login_id   = $this->encrypt($_REQUEST['login_id']);
							update_option('wpmls_clear_cached', "unclear");
							if (!isset($_REQUEST['sml-error']) && !isset($_REQUEST['code'])) {
								$this->session->regenerate_id(true);
								$this->session->set('sml_sid', $sml_sid);
								$this->session->set('login_id', $login_id);
							}
							$is_enable  = get_option('spiral_member_login')['related_web']['is_enable'];
							$is_checked = $is_enable ? 'checked' : '';
							$param_name = get_option('spiral_member_login')['related_web']['atts']['param_name'];
							$field_name = get_option('spiral_member_login')['related_web']['atts']['field_name'];

							$user_data = $this->get_user_prop_by_key($field_name);

							$param_exist =  (strpos($_REQUEST['redirect_to'], "?") !== false) ? "&" : "?";
							$param = null;

							if ($is_enable == '1') {
								$param = $param_exist . $param_name . '=' . $user_data;
							}

							$redirect_to = $this->after_login_redirect($_REQUEST['redirect_to']);
							wp_redirect($this->clear_error_message($_REQUEST['redirect_to']) . $param);
							exit;
						}

						if (isset($_REQUEST['sml-error']) && isset($_REQUEST['code'])) {
							$redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
							$error_code = (int)$_REQUEST['code'];
							$param_exist =  (strpos($redirect_to, "?") !== false) ? "&" : "?";

							switch ($error_code) {
								case 10:
									$redirect_to = $this->after_login_redirect($this->clear_error_message($_REQUEST['redirect_to']));
									wp_redirect($redirect_to . $param_exist . "message=error_occurred");
									break;
								case 20:
									$redirect_to = $this->after_login_redirect($this->clear_error_message($_REQUEST['redirect_to']));
									wp_redirect($redirect_to . $param_exist . "message=username_failed");
									break;
								case 21:
									$redirect_to = $this->after_login_redirect($this->clear_error_message($_REQUEST['redirect_to']));
									wp_redirect($redirect_to .  $param_exist . "message=password_failed");
									break;
								case 30:
									$redirect_to = $this->after_login_redirect($this->clear_error_message($_REQUEST['redirect_to']));
									wp_redirect($redirect_to .  $param_exist . "message=auth_failed");
									break;
								case 121:
									$redirect_to = $this->after_login_redirect($this->clear_error_message($_REQUEST['redirect_to']));
									wp_redirect($redirect_to . $param_exist . "message=pass_failed");
									break;
							}
						}

						if (!$this->is_logged_in()) {
							$this->clear_all_user_options();
							if (self::is_member_page(get_the_ID())) {
								// for member page
								$args = array(
									'memberpage' => 'true',
									'redirect_to' => self::get_current_path()
								);
								wp_redirect(self::get_page_link('login', $args));
								exit;
							}
						}

						if (isset($_GET['loggedout']) && true == $_GET['loggedout']) {
							@setcookie('is_login', false, time() - 1800, COOKIEPATH, COOKIE_DOMAIN, TRUE, TRUE);
							$this->clear_user_options();
							$this->errors->add('loggedout', __('You are now logged out.'), 'message');
						} elseif (isset($_GET['expired']) && true == $_GET['expired']) {
							@setcookie('is_login', false, time() - 1800, COOKIEPATH, COOKIE_DOMAIN, TRUE, TRUE);
							$this->clear_user_options();
							$this->errors->add('expired', __('Session expired. Please log in again. You will not move away from this page.'), 'message');
						}
						break;
				} // end switch
			}
		}


		public function number_params($url)
		{

			$parsedUrl = parse_url($url);

			$number_param = 0;

			if (isset($parsedUrl['query'])) {

				parse_str($parsedUrl['query'], $parsedArray);
				$paramCount = count($parsedArray);
				return $paramCount;
			}
			return $number_param;
		}

		public function redirect_params($url)
		{

			$parsedUrl = parse_url($url);

			$param = "";

			if (isset($parsedUrl['query'])) {

				parse_str($parsedUrl['query'], $parsedArray);

				$param = "";
				foreach ($parsedArray as $x => $val) {
					if ($x != 'message') {
						$param .= $x . '=' . $val;
					}
				}
				return $param;
			}
			return $number_param;
		}

		private function clear_error_message($url)
		{

			// Split the URL into its components
			$urlComponents = parse_url($url);
			// Get the query string
			$queryString = $urlComponents['query'];
			$string = $queryString;
			$substring = "message";

			if (!is_null($queryString)) {
				if (strpos($string, $substring) !== false) {
					// Remove the unwanted part of the query string
					$newQueryString = substr($queryString, 0, strpos($queryString, '&message'));

					// Reassemble the URL with the modified query string
					$newUrl = $urlComponents['scheme']  . $urlComponents['host'] . $urlComponents['path'] . '?' . $newQueryString;

					return $newUrl;
				}
			}

			return $url;
		}
		private function after_login_redirect($redirect_path)
		{
			$redirect_to = isset($redirect_path) ? $redirect_path : '';

			if (empty($redirect_to) || strpos($redirect_to, '/') != 0) {
				$redirect_to = get_home_url('/');
			}
			return $redirect_to;
		}

		private function encrypt_key($plaintext, $key, $cipher = "aes-256-gcm")
		{
			if (!in_array($cipher, openssl_get_cipher_methods())) {
				return false;
			}
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
			$tag = null;
			$ciphertext = openssl_encrypt(
				gzcompress($plaintext),
				$cipher,
				base64_decode($key),
				$options = 0,
				$iv,
				$tag,
			);
			return json_encode(
				array(
					"ciphertext" => base64_encode($ciphertext),
					"cipher" => $cipher,
					"iv" => base64_encode($iv),
					"tag" => base64_encode($tag),
				)
			);
		}

		private function decrypt_key($cipherjson, $key)
		{
			try {
				$json = json_decode($cipherjson, true, 2,  JSON_THROW_ON_ERROR);
			} catch (Exception $e) {
				return false;
			}
			return gzuncompress(
				openssl_decrypt(
					base64_decode($json['ciphertext']),
					$json['cipher'],
					base64_decode($key),
					$options = 0,
					base64_decode($json['iv']),
					base64_decode($json['tag'])
				)
			);
		}


		function get_wp_current_url()
		{
			if (get_option("spiral_member_login")["member_logout_url"]) {
				$logout_redirect_url = get_option("spiral_member_login")["member_logout_url"];
				if (isset($logout_redirect_url) && $logout_redirect_url != null) {
					return parse_url($logout_redirect_url);
				}
			}
			global $wp;
			if ('' === get_option('permalink_structure')) return home_url(add_query_arg(array($_GET), $wp->request));
			else return parse_url(home_url(trailingslashit(add_query_arg(array($_GET), $wp->request))));
		}

		/**
		 * GENERATE PAGE TO URL
		 */
		public function generate_save_page_id_to_url($page_names)
		{
			foreach ($page_names as $page_name) {
				$id = get_option($this->options_key)[$page_name];
				if ($this->is_logged_in()) {
					$sml_sid = $this->session->get('sml_sid');
					$wpmls_area_title = $this->get_option('wpmls_area_title');
					if ($id) {
						$page_url = $this->spiral->get_area_mypage($wpmls_area_title, $sml_sid, $id);
						if (isset($page_url)) {
							$page_option_name = 'page_url_' . $sml_sid . '_' . $page_name . '_' . $id;
							add_option($page_option_name, $page_url);
						}
					}
				}
			}
		}

		private function clear_user_options()
		{
			global $wpdb;

			$sml_sid =  $this->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
			if (isset($_COOKIE["sml_wp_session"])) {
				$sesssions = explode('||', $_COOKIE["sml_wp_session"]);
				$session_id  = $sesssions[0];
				delete_option('_wp_session_' . $session_id);
			}

			$page_option_name 							= 'page_url_' . $sml_sid . '%';
			$shortcode_mypage_url_optiona_name 			= 'shortcode_mypage_url' . $sml_sid . '%';
			$extraction_rule_option_name 				= 'extraction_rule' . $sml_sid . '%';
			$shortcode_is_logged_in_rule_option_name 	= 'shortcode_is_logged_in_rule_' . $sml_sid . '%';
			$shortcode_is_logged_in_type_optiona_name   = 'shortcode_is_logged_in_type' . $sml_sid . '%';


			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $shortcode_mypage_url_optiona_name));
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $shortcode_is_logged_in_type_optiona_name));
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $page_option_name));
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $extraction_rule_option_name));
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $shortcode_is_logged_in_rule_option_name));
		}

		private function clear_all_user_options()
		{
			global $wpdb;

			$page_option_name 							= 'page_url_' . '%';
			$shortcode_mypage_url_optiona_name 			= 'shortcode_mypage_url' . '%';
			$extraction_rule_option_name 				= 'extraction_rule' . '%';
			$shortcode_is_logged_in_type_optiona_name 	= 'shortcode_is_logged_in_type' . '%';
			$shortcode_is_logged_in_rule_option_name 	= 'shortcode_is_logged_in_rule_' . '%';

			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $shortcode_mypage_url_optiona_name));
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $shortcode_is_logged_in_type_optiona_name));
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $page_option_name));
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $extraction_rule_option_name));
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $shortcode_is_logged_in_type_optiona_name));
		}

		/**
		 * Calls "login_head" hook on login page
		 *
		 * Callback for "wp_head" hook
		 *
		 * @access public
		 */
		public function wp_head()
		{
			if (self::is_sml_page()) {
				// This is already attached to "wp_head"
				remove_action('login_head', 'wp_print_head_scripts', 9);

				do_action('login_head');
			}
		}

		/**
		 * Calls "login_footer" hook on login page
		 *
		 * Callback for "wp_footer" hook
		 *
		 */
		public function wp_footer()
		{
			if (self::is_sml_page()) {
				// This is already attached to "wp_footer"
				remove_action('login_footer', 'wp_print_footer_scripts', 20);

				do_action('login_footer');
			}
		}

		/**
		 * Prints javascript in the footer
		 *
		 * @access public
		 */
		public function wp_print_footer_scripts()
		{
			if (!self::is_sml_page()) {
				return;
			}
		}


		/************************************************************************************************************************
		 * Filters
		 ************************************************************************************************************************/

		/**
		 * Alters menu item title & link according to whether user is logged in or not
		 *
		 * Callback for "wp_setup_nav_menu_item" hook in wp_setup_nav_menu_item()
		 *
		 * @see wp_setup_nav_menu_item()
		 * @access public
		 *
		 * @param object $menu_item The menu item
		 * @return object The (possibly) modified menu item
		 */
		public function wp_setup_nav_menu_item($menu_item)
		{
			if (is_admin())
				return $menu_item;

			if ('page' == $menu_item->object && self::is_sml_page('login', $menu_item->object_id)) {
				if ($this->is_logged_in()) {
					$menu_item->title = $this->get_template()->get_title('logout');
					$menu_item->url   = self::get_page_link('logout');
				}
			}
			return $menu_item;
		}

		/**
		 * Excludes pages from wp_list_pages
		 *
		 *
		 * @param array $exclude Page IDs to exclude
		 * @return array Page IDs to exclude
		 */
		public function wp_list_pages_excludes($exclude)
		{
			$pages = get_posts(array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'meta_key'       => '_sml_action',
				'posts_per_page' => -1
			));
			$pages = wp_list_pluck($pages, 'ID');

			return array_merge($exclude, $pages);
		}

		/**
		 * Adds nonce to logout link
		 *
		 * @param string $link Page link
		 * @param int $post_id Post ID
		 * @return string Page link
		 */
		public function page_link($link, $post_id)
		{
			if (self::is_sml_page('logout', $post_id))
				$link = add_query_arg('_wpnonce', wp_create_nonce('log-out'), $link);
			return $link;
		}


		/************************************************************************************************************************
		 * Utilities
		 ************************************************************************************************************************/

		/**
		 * Is this plugin with imcomplete settings
		 *
		 * @access public
		 *
		 * @return bool True if settings is imcomplete
		 */
		public function is_settings_imcomplete()
		{
			if (strlen($this->get_option('wpmls_api_token')) != 52) {
				$token =  $this->wpfws_dos_soar($this->get_option('wpmls_api_token'), SECURE_AUTH_KEY);
			} elseif (strlen($this->get_option('wpmls_api_token')) == 52) {
				$token = $this->get_option('wpmls_api_token');
			}

			if (strlen($this->get_option('wpmls_api_token_secret')) != 40) {
				$token_secret =  $this->wpfws_dos_soar($this->get_option('wpmls_api_token_secret'), SECURE_AUTH_KEY);
			} elseif (strlen($this->get_option('wpmls_api_token_secret')) == 40) {
				$token_secret = $this->get_option('wpmls_api_token_secret');
			}
			$wpmls_auth_form_url = $this->get_option('wpmls_auth_form_url');

			return (empty($token) || empty($token_secret) || empty($wpmls_auth_form_url));
		}

		/**
		 * Handler for "sml-show-template" shortcode
		 *
		 * Optional $atts contents:
		 *
		 * - template_num - A unqiue template number for this instance.
		 * - default_action - The action to display. Defaults to "login".
		 * - login_template - The template used for the login form. Defaults to "login-form.php".
		 * - user_template - The templated used for when a user is logged in. Defalts to "user-panel.php".
		 * - show_title - True to display the current title, false to hide. Defaults to true.
		 * - show_reg_link - True to display the register link, false to hide. Defaults to true.
		 * - show_pass_link - True to display the lost password link, false to hide. Defaults to true.
		 * - logged_in_widget - True to display the widget when logged in, false to hide. Defaults to true.
		 * - logged_out_widget - True to display the widget when logged out, false to hide. Defaults to true.
		 *
		 * @access public
		 *
		 * @param string|array $atts Attributes passed from the shortcode
		 * @return string HTML output from WPMLS_Spiral_Member_Login_Template->display()
		 */
		public function shortcode_show_template($atts = '')
		{
			static $did_main_template = false;

			$atts = wp_parse_args($atts);

			if (isset($atts['is_widget']) == false) {
				// Hide title
				if (isset($atts['title']) && $atts['title'] == 'off') {
					$atts['show_title'] = false;
				}
				// Hide all links
				if ((isset($atts['all']) && $atts['all'] == 'off') && (isset($atts['showname']) && $atts['showname'] == 'on')) {
					$atts['show_title'] = false;
					$atts['show_reg_link'] = false;
					$atts['show_pass_link'] = false;
					$atts['show_profile_link'] = false;
					$atts['show_resetpass_link'] = false;
					$atts['show_withdrawal_link'] = false;
				} elseif (isset($atts['all']) && $atts['all'] == 'off') {
					$atts['hide_logout_link'] = true;
					$atts['show_title'] = false;
					$atts['show_reg_link'] = false;
					$atts['show_pass_link'] = false;
					$atts['show_profile_link'] = false;
					$atts['show_resetpass_link'] = false;
					$atts['show_withdrawal_link'] = false;
				}
				// Hide_register & lost_password_link & profile_link & resetpw_link & withdrawal_link
				if (isset($atts['register']) && $atts['register'] == 'off') {
					$atts['show_reg_link'] = false;
				}
				if (isset($atts['lostpw']) && $atts['lostpw'] == 'off') {
					$atts['show_pass_link'] = false;
				}
				if (isset($atts['showname']) && $atts['showname'] == 'off') {
					$atts['name_key'] = false;
				}
				if (isset($atts['profile']) && $atts['profile'] == 'off') {
					$atts['show_profile_link'] = false;
				}
				if (isset($atts['resetpw']) && $atts['resetpw'] == 'off') {
					$atts['show_resetpass_link'] = false;
				}
				if (isset($atts['withdrawal']) && $atts['withdrawal'] == 'off') {
					$atts['show_withdrawal_link'] = false;
				}
				if (isset($atts['logout']) && $atts['logout'] == 'off') {
					$sml_sid = $this->session->get('sml_sid');
					$logout_option_name = 'is_logout_sId';
					add_option($logout_option_name, true);
				} else {
					$logout_option_name = 'is_logout_sId';
					delete_option($logout_option_name);
				}
				if (isset($atts['target']) && $atts['target'] == '_blank') {
					$logout_option_name = 'is_target_blank';
					add_option($logout_option_name, true);
				} else {
					$logout_option_name = 'is_target_blank';
					delete_option($logout_option_name);
				}
			}

			if (!isset($atts['name_key']) && $this->get_option('wpmls_default_name_key')) {
				$atts['name_key'] = $this->get_option('wpmls_default_name_key');
			}
			if (!$this->get_option('wpmls_register_url')) {
				$atts['show_reg_link'] = false;
			}
			if (!$this->get_option('wpmls_lostpassword_url')) {
				$atts['show_pass_link'] = false;
			}
			if (!$this->get_option('wpmls_profile_page_id')) {
				$atts['show_profile_link'] = false;
			}
			if (!$this->get_option('wpmls_resetpass_page_id')) {
				$atts['show_resetpass_link'] = false;
			}
			if (!$this->get_option('wpmls_withdrawal_page_id')) {
				$atts['show_withdrawal_link'] = false;
			}

			if (self::is_sml_page() && in_the_loop() && is_main_query() && !$did_main_template) {
				$template = $this->get_template();

				if (!empty($this->request_template_num))
					$template->set_active(false);

				if (!empty($this->request_action))
					$atts['default_action'] = $this->request_action;

				if (!isset($atts['show_title']))
					$atts['show_title'] = false;

				foreach ($atts as $option => $value) {
					$template->set_option($option, $value);
				}

				$did_main_template = true;
			} else {
				$template = $this->load_template($atts);
			}
			return $template->display($atts);
		}


		public function shortcode_is_logged_in($atts, $content = null)
		{
			if (!$this->is_logged_in()) {
				return null;
			}
			return do_shortcode($content);
		}

		public function shortcode_is_logged_in_hide($atts, $content = null)
		{
			if (!$this->is_logged_in()) {
				return do_shortcode($content);
			}

			return null;
		}

		public function shortcode_user_link($atts)
		{
			$array_key_prop = $this->to_arrray($atts["key"]);
			$target     = isset($atts['target']) ? 'target="_blank"' : '';

			if (isset($array_key_prop)) {
				$param_link = "";
				$param = "";
				// For array props
				if (count($array_key_prop) > 1) {
					foreach ($array_key_prop as $key => $value) {
						$user_key = isset($value) ? $value : null;

						$user_data =  $this->get_user_prop($user_key);

						if (isset($user_data) || empty($user_data)) {
							$param_link .=   $user_key . '=' . $user_data  . '&';
							$param .=    $user_data  . ',';
						}
					}

					$display_text_link = substr_replace($param_link, "", -1);
					$display_text = substr_replace($param, "", -1);
	
					if (isset($atts['link'])) {
						$is_query =  strpos($atts["link"], '?') !== false;
						$link = ($is_query) ? $atts["link"] . '&' : $atts["link"] . '?';
						if (isset($user_key)) {

							$final_display = '<p><a href="' . $link . $display_text_link . '">' . $atts['link_text'] . '</a></p>';
							if (isset($atts['target'])) {
								return '<p><a href="' . $link . $display_text_link . '"' . $target . '>' . $atts['link_text'] . '</a></p>';
							} else {
								return '<p><a href="' . $link . $display_text_link . '">' . $atts['link_text'] . '</a></p>';
							}
						}
					} else {
						return $display_text;
					}
				}

				// For Single Prop

				$user_key = isset($atts["key"]) ? $atts["key"] : null;
				$user_data =  $this->get_user_prop($user_key);

				$param_link =   $user_key . '=' . $user_data;
				$param =    $user_data;

				if (isset($atts['link'])) {
					$is_query =  strpos($atts["link"], '?') !== false;
					$link = ($is_query) ? $atts["link"] . '&' : $atts["link"] . '?';
					if (isset($user_key)) {
						if (isset($atts['target'])) {
							return '<p><a href="' . $link . $param_link . '"' . $target . '>' . $atts['link_text'] . '</a></p>';
						} else {
							return '<p><a href="' . $link . $param_link . '">' . $atts['link_text'] . '</a></p>';
						}
					}
				} else {
					return $param;
				}
			}
		}

		public function shortcode_user_prop($atts)
		{
			if (!$this->is_logged_in())
				return null;
			$array_key_prop = $this->to_arrray($atts["key"]);


			if (isset($array_key_prop)) {

				if (count($array_key_prop) > 1) {
					$display = "";
					foreach ($array_key_prop as $key => $value) {
						$user_key = isset($value) ? $value : null;
						$user_data =  $this->get_user_prop($user_key);

						if ($user_data != "" || $user_data != null)
							$display .= $user_data . ',';
					}
					$final_display = substr($display, 0, -1);
					return  $final_display;
				}

				$user_key = isset($atts['key']) ? $atts['key'] : null;
				$user_data =  $this->get_user_prop($user_key);

				return $user_data;
			}
		}

		protected function to_arrray($str)
		{
			$arr = preg_split("/\,/", $str);
			return $arr;
		}




		public function shortcode_mypage_url($atts)
		{
			if (!$this->is_logged_in() || !isset($atts['id']))
				return null;

			$sml_sid 	= $this->session->get('sml_sid');
			$page_id 	= $atts['id'];
			$page_title = isset($atts['title']) ? $this->encrypt($atts['title']) : '';
			$page_image = isset($atts['image']) ? $this->encrypt($atts['image']) : '';
			$param 		= isset($atts['param']) ? '&param=' . $atts['param'] : '';
			$target     = isset($atts['target']) ? 'target="_blank"' : '';

			$action = 'register';

			if (isset($atts['title'])) {
				return '<div><a ' . $target  . ' href="' . $action . '?id=' . $page_id . $param . '">' . $atts['title'] . '</a></div>';
			}

			if (isset($atts['image'])) {
				return '<div><a ' . $target  . ' href="' . $action . '?id=' . $page_id . $param . '">
				<img src="' . $atts['image'] . '">
				</a></div>';
			}
		}

		protected function get_user_prop_by_key($key)
		{
			$wpmls_identification_key  = $this->get_option('wpmls_member_identification_key');
			$db_title  = $this->get_option('wpmls_member_db_title');
			$user_key = $this->decrypt($this->session->get('login_id'));

			$user_record = $this->spiral->get_user_record($db_title, $wpmls_identification_key, $user_key);

			if (!array_key_exists(strval($key), (array)$user_record) && $key != 'name') {
				return null;
			}

			if ($key == 'name') {
				if (isset($user_record['firstName']) && isset($user_record['lastName'])) {
					return $user_record['lastName'] . ' ' . $user_record['firstName'];
				} elseif (isset($user_record['name'])) {
					return $user_record['name'];
				} else {
					return null;
				}
			}
			return $user_record[$key];
		}

		protected function get_user_prop_by_value($key)
		{
			$wpmls_identification_key  = $this->get_option('wpmls_member_identification_key');
			$db_title  = $this->get_option('wpmls_member_db_title');
			$user_key = $this->decrypt($this->session->get('login_id'));

			$is_selectable_field = $this->spiral->check_selectable_field($db_title, $wpmls_identification_key, $token, $key);

			if (!$is_selectable_field)
				return null;

			$user_record = $this->spiral->get_user_record_value($db_title, $wpmls_identification_key, $user_key);
			if (!array_key_exists(strval($key), (array)$user_record) && $key != 'name') {
				return null;
			}

			if ($key == 'name') {
				if (isset($user_record['firstName']) && isset($user_record['lastName'])) {
					return $user_record['lastName'] . ' ' . $user_record['firstName'];
				} elseif (isset($user_record['name'])) {
					return $user_record['name'];
				} else {
					return null;
				}
			}
			return $user_record[$key];
		}

		function convert_to_number($number)
		{
			return is_numeric($number) ? ($number + 0) : FALSE;
		}

		protected function isFilterTypeNumber($atts)
		{
			if (!array_key_exists('fieldtype', $atts)) {
				return false;
			}
			if (!empty($atts['fieldtype'])) {
				if ($atts['fieldtype'] == 'num' &&  is_integer($this->convert_to_number($atts['value']))) {
					return true;
				}
				return false;
			}
			return false;
		}

		protected function isOperator($atts)
		{
			if (array_key_exists('filter', $atts)) {
				if (empty($atts['filter'])) {
					return 'equal';
				}
				return $atts['filter'];
			}
			return 'equal';
		}

		public function shortcode_is_logged_in_type($atts, $content = null)
		{
			if (!$this->is_logged_in() || !isset($atts['value']) || !isset($atts['key'])) {
				return null;
			}

			$sml_sid 	 = $this->session->get('sml_sid');
			$operator    = isset($atts['filter']) ? $atts['filter'] : '';
			$option_name = 'shortcode_is_logged_in_type_' . $sml_sid . '_' . $atts['key'] . '_' . $atts['value'] . $operator;

			$option_name_encrypted = $this->encrypt($option_name);
			$user_prop_value = null;
			$att_value 			= $atts['value'] == 'null' ? NULL : $atts['value'];

			$user_prop_value 	= $this->get_user_prop_by_value($atts['key']);
			
			// Catch Not existed
			if (!get_option($option_name)) {
				$user_prop_value 	= $this->get_user_prop_by_value($atts['key']);
				if (!is_null($user_prop_value))
					add_option($option_name, $user_prop_value);
			} else {
				$user_prop_value = get_option($option_name);
			}

			switch ($this->isOperator($atts)) {
				case 'equal':
					$arr_value = $this->to_arrray($att_value);
					$user_prop_value_arr = $this->to_arrray($user_prop_value);

					if (count($arr_value) > 1) {
						$is_equal = 0;
						for ($i = 0; $i < count($arr_value); $i++) {
							if (in_array($arr_value[$i], $user_prop_value_arr)) {
								$is_equal++;
							}
						}
						if ($is_equal > 0) {
							return do_shortcode($content);
						}
					} else {
						$user_prop_value_arr = $this->to_arrray($user_prop_value);
						if (in_array($att_value, $user_prop_value_arr)) {
							return do_shortcode($content);
						}
					}
					break;
				case 'unequal':
					$arr_value = $this->to_arrray($att_value);
					if (!in_array($user_prop_value, $arr_value)) {
						return do_shortcode($content);
					}
					break;
				case 'less':
					if ($this->isFilterTypeNumber($atts)) {
						if (intval($user_prop_value) < intval($att_value)) {
							return do_shortcode($content);
						}
						break;
					}
				case 'greater':
					if ($this->isFilterTypeNumber($atts)) {
						if (intval($user_prop_value) > intval($att_value)) {
							return do_shortcode($content);
						}
						break;
					}
					return null;
				case 'lessequal':
					if ($this->isFilterTypeNumber($atts)) {
						if (intval($user_prop_value) <= intval($att_value)) {
							return do_shortcode($content);
						}
						break;
					}
					return null;
				case 'greaterequal':
					if ($this->isFilterTypeNumber($atts)) {
						if (intval($user_prop_value) >= intval($att_value)) {
							return do_shortcode($content);
						}
						break;
					}
					return null;
				default:
					$arr_value = $this->to_arrray($att_value);
					if (in_array($user_prop_value, $arr_value)) {
						return do_shortcode($content);
					}
					return null;
					break;
			}
		}

		public function shortcode_is_logged_in_rule($atts, $content = null)
		{
			if (!$this->is_logged_in()) {
				return null;
			}
			$id = $this->get_user_prop("id");
			$select_name = $atts['rule_name'];
			if ($select_name == "") {
				return null;
			}
			$login_rule_count = $this->get_user_extraction_rule($id, $select_name);

			if ($login_rule_count > 0) {
				return do_shortcode($content);
			} else {
				return null;
			}
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance()
		{

			// If the single instance hasn't been set, set it now.
			if (null == self::$instance) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Returns default options
		 *
		 * @access public
		 *
		 * @return array Default options
		 */
		public static function default_options()
		{
			$domin_name = parse_url(get_site_url())["scheme"] . '://' . parse_url(get_site_url())["host"] . '/';
			return apply_filters('sml_default_options', array(
				'wpmls_api_token' => '',
				'wpmls_api_token_secret' => '',
				'wpmls_member_db_title' => 'wpmls_member',
				'wpmls_member_identification_key' => 'email',
				'wpmls_area_title' => 'wpmls_area',
				"wpmls_custom_module_path"   => "wpmls_module/login_url.php",
				'wpmls_auth_form_url' => '',
				'wpmls_member_list_search_title' => 'wpmls_searchform',
				'wpmls_default_name_key' => 'name',
				'login_id_label_jp' => '',
				'login_id_label_en' => '',
				'wpmls_register_url' => '',
				'wpmls_lostpassword_url' => '',
				'member_domain_name' => $domin_name,
				'member_logout_url' => get_home_url(),
				'wpmls_profile_page_id' => '',
				'wpmls_resetpass_page_id' => '',
				'wpmls_withdrawal_page_id' => '',
				'related_web' => [
					'is_enable' => false,
					'atts' => [
						'param_name' => '',
						'field_name' => ''
					]
				]
			));
		}

		/**
		 * Returns default pages
		 *
		 * @access public
		 *
		 * @return array Default pages
		 */
		public static function default_pages()
		{
			return apply_filters('sml_default_pages', array(
				'login'        => __('Log In'),
				'logout'       => __('Log Out'),
				'profile'      => __('Profile', self::domain),
				'lostpassword' => __('Lost Password', self::domain),
				'resetpass'    => __('Reset Password', self::domain),
				'register'     => __('Register', self::domain),
				'withdrawal'   => __('Withdrawal', self::domain)
			));
		}

		/**
		 * Retrieves active template object
		 *
		 * @access public
		 *
		 * @return object Instance object
		 */
		public function get_active_template()
		{
			return $this->get_template((int) $this->request_template_num);
		}

		/**
		 * Retrieves a loaded template object
		 *
		 * @access public
		 *
		 * @param int $num Instance number
		 * @return object Instance object

		 */
		public function get_template($num = 0)
		{
			if (isset($this->loaded_templates[$num]))
				return $this->loaded_templates[$num];
		}

		/**
		 * Sets an template object
		 *
		 * @access public
		 *
		 * @param object $object Instance object
		 */
		public function set_template($object)
		{
			$this->loaded_templates[] = &$object;
		}

		/**
		 * Instantiates an template
		 *
		 * @access public
		 *
		 * @param array|string $args Array or query string of arguments

		 * @return object Instance object
		 */
		public function load_template($args = '')
		{
			if (!$args && version_compare(phpversion(), '7.1.0', '>=')) {
				$args = array();
			}

			$args['template_num'] = count($this->loaded_templates);

			$template = new WPMLS_Spiral_Member_Login_Template($args);

			if ($args['template_num'] == $this->request_template_num) {
				$template->set_active();
				$template->set_option('default_action', $this->request_action);
			}

			$this->loaded_templates[] = $template;

			return $template;
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain()
		{
			$locale = apply_filters('plugin_locale', get_locale(), self::domain);

			load_textdomain(self::domain, WP_LANG_DIR . '/' . self::domain . '/' . self::domain . '-' . $locale . '.mo');
			load_plugin_textdomain(self::domain, FALSE, dirname(plugin_basename(__DIR__)) . '/lang');
		}

		/**
		 * Save plugin settings
		 *
		 * This is the callback for register_setting()
		 *
		 * @access public
		 *
		 * @param string|array $inputs Settings passed in from filter
		 * @return string|array Sanitized settings
		 */
		public function save_settings($inputs)
		{
			if (isset($_REQUEST['is_save'])) {
				$options                       = $this->get_options();
				$options['wpmls_api_token']          = sanitize_text_field(trim($this->wpfws_jak_soar($_POST[$this->options_key]['wpmls_api_token'], SECURE_AUTH_KEY)));
				$options['wpmls_api_token_secret']   = sanitize_text_field(trim($this->wpfws_jak_soar($_POST[$this->options_key]['wpmls_api_token_secret'], SECURE_AUTH_KEY)));
				$token_pattern = '/^[0-9a-zA-Z_\-]+$/';
				$secret_pattern = '/^[0-9a-zA-Z_\-]+$/';
				$error_messages = array();
				$options['wpmls_member_db_title']              = sanitize_text_field(trim($_POST[$this->options_key]['wpmls_member_db_title']));
				$options['wpmls_member_identification_key']    = sanitize_text_field(trim($_POST[$this->options_key]['wpmls_member_identification_key']));
				$options['wpmls_area_title']                   = sanitize_text_field(trim($_POST[$this->options_key]['wpmls_area_title']));
				$options['wpmls_custom_module_path']           = sanitize_text_field(trim($_POST[$this->options_key]['wpmls_custom_module_path']));
				$options['wpmls_auth_form_url']                = sanitize_text_field(trim($_POST[$this->options_key]['wpmls_auth_form_url']));
				$options['member_logout_url']            = sanitize_text_field(trim($_POST[$this->options_key]['member_logout_url']));
				$options['wpmls_register_url']                 = sanitize_text_field(trim($_POST[$this->options_key]['wpmls_register_url']));
				$options['wpmls_lostpassword_url']             = sanitize_text_field(trim($_POST[$this->options_key]['wpmls_lostpassword_url']));
				$options['wpmls_profile_page_id']              = ($_POST[$this->options_key]['wpmls_profile_page_id'] != '') ? absint(trim($_POST[$this->options_key]['wpmls_profile_page_id'])) : '';
				$options['wpmls_resetpass_page_id']            = ($_POST[$this->options_key]['wpmls_resetpass_page_id'] != '') ? absint(trim($_POST[$this->options_key]['wpmls_resetpass_page_id'])) : '';
				$options['wpmls_withdrawal_page_id']           = ($_POST[$this->options_key]['wpmls_withdrawal_page_id'] != '') ? absint(trim($_POST[$this->options_key]['wpmls_withdrawal_page_id'])) : '';
				$options['wpmls_member_list_search_title']     = sanitize_text_field(trim($_POST[$this->options_key]['wpmls_member_list_search_title']));
				$options['wpmls_default_name_key']             = sanitize_text_field(trim($_POST[$this->options_key]['wpmls_default_name_key']));
				$options['login_id_label_jp']            = sanitize_text_field(trim($_POST[$this->options_key]['login_id_label_jp']));
				$options['login_id_label_en']            = sanitize_text_field(trim($_POST[$this->options_key]['login_id_label_en']));

				if (!isset($options['related_web'])) {
					$options['related_web'] = [
						'is_enable' => false,
						'atts' => [
							'param_name' => '',
							'field_name' => ''
						]
					];
				}

				$options['related_web'] = [
					'is_enable' => $_POST['is_enable'] == NULL ? false : $_POST['is_enable'],
					'atts' => [
						'param_name' => isset($_POST['param_name']) ? $_POST['param_name'] : null,
						'field_name' => isset($_POST['field_name']) ? $_POST['field_name'] : null
					]
				];

				if (!preg_match($token_pattern, $this->wpfws_dos_soar($options['wpmls_api_token'], SECURE_AUTH_KEY))) {
					$options['wpmls_api_token'] = '';
					$error_messages[] = __('Enter a valid API token', self::domain);
				}
				if (!preg_match($secret_pattern, $this->wpfws_dos_soar($options['wpmls_api_token_secret'], SECURE_AUTH_KEY))) {
					$options['wpmls_api_token_secret'] = '';
					$error_messages[] = __('Enter a valid API token secret', self::domain);
				}
				if (!$options['login_id_label_jp']) {
					unset($options['wpmls_login_id_label']);
					$error_messages[] = __('ログインIDラベルを入力してください', self::domain);
				}
				if (!$options['login_id_label_en']) {
					unset($options['wpmls_login_id_label']);
					$error_messages[] = __('Enter Login ID Label', self::domain);
				}
				if (!$options['wpmls_member_list_search_title']) {
					unset($options['wpmls_member_list_search_title']);
					$error_messages[] = __('Enter member list search title', self::domain);
				}
				if (!$options['wpmls_default_name_key']) {
					unset($options['wpmls_default_name_key']);
					$error_messages[] = __('Enter default name key', self::domain);
				}
				if (!$options['wpmls_area_title']) {
					unset($options['wpmls_area_title']);
					$error_messages[] = __('Enter area title', self::domain);
				}
				if (!$options['wpmls_custom_module_path']) {
					unset($options['wpmls_custom_module_path']);
					$error_messages[] = __('Enter Custom module', self::domain);
				}
				if (!$options['wpmls_auth_form_url']) {
					$options['wpmls_auth_form_url'] = '';
					$error_messages[] = __('Enter authentication form url', self::domain);
				}
				if (!empty($error_messages)) {
					$error_message = implode('<br/>', $error_messages);
					add_settings_error($this->options_key, $this->plugin_slug, $error_message);
				}
				return $options;
			}
		}

		/**
		 * Install plugin
		 *
		 * @access public
		 */
		public function install()
		{
			// Current version
			$version = $this->get_option('version', self::version);

			// Setup default pages
			foreach (self::default_pages() as $action => $title) {
				if (!$page_id = self::get_page_id($action)) {
					$page_id = wp_insert_post(array(
						'post_title'     => $title,
						'post_name'      => $action,
						'post_status'    => 'publish',
						'post_type'      => 'page',
						'post_content'   => '[sml-show-template]',
						'comment_status' => 'closed',
						'ping_status'    => 'closed'
					));
					update_post_meta($page_id, '_sml_action', $action);
				}
			}

			$this->set_option('version', self::version);

			if (!get_option($this->options_key))
				$this->save_options();
		}

		/**
		 * Returns current URL
		 *
		 * @access public
		 *
		 * @param string $query Optionally append query to the current URL
		 * @return string URL with optional path appended
		 */
		public static function get_current_url($query = '')
		{
			$url = remove_query_arg(array('template_num', 'action', 'error', 'loggedout', 'redirect_to', 'updated', 'key', '_wpnonce', 'login'));

			if (!empty($_REQUEST['template_num']))
				$url = add_query_arg('template_num', $_REQUEST['template_num']);

			if (!empty($query)) {
				$r = wp_parse_args($query);
				foreach ($r as $k => $v) {
					if (strpos($v, ' ') !== false)
						$r[$k] = rawurlencode($v);
				}
				$url = add_query_arg($r, $url);
			}
			return $url;
		}

		public static function get_current_path($query = '')
		{
			$url = self::get_current_url($query);
			$home_url = get_home_url('/');
			return str_replace($home_url, '', $url);
		}

		/**
		 * Returns link for a login page
		 *
		 * @access public
		 *
		 * @param string $action The action
		 * @param string|array $query Optional. Query arguments to add to link
		 * @return string Login page link with optional $query arguments appended
		 */
		public static function get_page_link($action, $query = '')
		{
			$page_id = self::get_page_id($action);

			if ($page_id) {
				$link = get_permalink($page_id);
			} elseif ($page_id = self::get_page_id('login')) {
				$link = add_query_arg('action', $action, get_permalink($page_id));
			} else {
				$link = get_home_url('/');
			}

			if (!empty($query)) {
				$args = wp_parse_args($query);

				if (isset($args['action']) && $action == $args['action']) {
					unset($args['action']);
				}

				$link = add_query_arg(array_map('rawurlencode', $args), $link);
			}

			// Respect FORCE_SSL_LOGIN
			if ('login' == $action && force_ssl_login()) {
				$link = preg_replace('|^http://|', 'https://', $link);
			}

			return apply_filters('sml_page_link', $link, $action, $query);
		}

		/**
		 * Retrieves a page ID for an action
		 *
		 * @param string $action The action
		 * @return int|bool The page ID if exists, false otherwise
		 */
		public static function get_page_id($action)
		{
			global $wpdb;

			if (!$page_id = wp_cache_get($action, 'sml_page_ids')) {
				$page_id = $wpdb->get_var($wpdb->prepare("SELECT p.ID FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pmeta ON p.ID = pmeta.post_id WHERE p.post_type = 'page' AND pmeta.meta_key = '_sml_action' AND pmeta.meta_value = %s", $action));
				if (!$page_id) {
					return null;
				}
				wp_cache_add($action, $page_id, 'sml_page_ids');
			}
			return $page_id;
		}

		/**
		 * Get the action for a page
		 *
		 * @param int|object Post ID or object
		 * @return string|bool Action name if exists, false otherwise
		 */
		public static function get_page_action($page)
		{
			if (!$page = get_post($page))
				return false;

			return get_post_meta($page->ID, '_sml_action', true);
		}

		/**
		 * Determines if $action is for $page
		 *
		 * @param string $action The action to check
		 * @param int|object Post ID or object
		 * @return bool True if $action is for $page, false otherwise
		 */
		public static function is_sml_page($action = '', $page = '')
		{
			if (!$page = get_post($page))
				return false;

			if ('page' != $page->post_type)
				return false;

			if (!$page_action = self::get_page_action($page->ID))
				return false;

			if (empty($action) || $action == $page_action)
				return true;

			return false;
		}

		public static function is_member_page($page = '')
		{
			if (!$post = get_post($page)) {
				return false;
			}

			if ($post->post_type != 'page') {
				return false;
			}

			return get_post_meta($post->ID, 'sml-member-page', true) == 'true';
		}

		/**
		 * Renders api token settings field
		 *
		 * @access public
		 */
		public function settings_field_api_token()
		{
		?>
			<input name="<?php echo $this->options_key ?>[wpmls_api_token]" type="password" id="spiral_member_login_api_token" class="sml_token_field basic_config" value="<?php echo $this->get_option('wpmls_api_token'); ?>" required />
		<?php
		}

		private function wpfws_jak_soar($lek_somngat, $soar)
		{
			// already jak soar or invalid lek somngat
			if (strlen($lek_somngat) === 152 || strlen($lek_somngat) === 128) {
				return $lek_somngat;
			}

			if (current_user_can('administrator')) {
				$lekjbol = get_user_meta(1, 'soarsomngat', true);
				$lekderm = $lek_somngat;
				$ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
				$iv = random_bytes($ivlen);
				$lek_kae_rouch_raw = openssl_encrypt($lekderm, $cipher, $soar . $lekjbol, $options = OPENSSL_RAW_DATA, $iv);
				$hmac = hash_hmac('sha256', $lek_kae_rouch_raw, $soar . $lekjbol, $as_binary = true);
				$lek_kae_rouch = base64_encode($iv . $hmac . $lek_kae_rouch_raw);
				return $lek_kae_rouch;
			}
		}

		private function wpfws_dos_soar($lek_somngat, $soar)
		{
			// already jak soar or invalid lek somngat
			if (strlen($lek_somngat) !== 152 && strlen($lek_somngat) !== 128) {
				return $lek_somngat;
			}
			$lekjbol = get_user_meta(1, 'soarsomngat', true);
			$c = base64_decode($lek_somngat);
			$ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
			$iv = substr($c, 0, $ivlen);
			$hmac = substr($c, $ivlen, $sha2len = 32);
			$lek_kae_rouch_raw = substr($c, $ivlen + $sha2len);
			$original_lekderm = openssl_decrypt($lek_kae_rouch_raw, $cipher, $soar . $lekjbol, $options = OPENSSL_RAW_DATA, $iv);
			$calcmac = hash_hmac('sha256', $lek_kae_rouch_raw, $soar . $lekjbol, $as_binary = true);
			if ($c) {
				if (hash_equals($hmac, $calcmac)) {
					return $original_lekderm;
				}
			}
		}


		/**
		 * Renders api token secret settings field
		 *
		 * @access public
		 */
		public function settings_field_api_token_secret()
		{
			$lek_somngat_secret = $this->get_option('wpmls_api_token_secret');
			$after_encript_secret = $lek_somngat_secret;
		?>
			<input name="<?php echo $this->options_key ?>[wpmls_api_token_secret]" type="password" class="sml_token_field basic_config" value="<?php esc_attr_e($after_encript_secret); ?>" required />
		<?php
		}

		public function settings_field_register_url()
		{
		?>
			<input name="<?php echo $this->options_key ?>[wpmls_register_url]" type="text" class="sml_token_field" value="<?php esc_attr_e($this->get_option('wpmls_register_url')); ?>" />
		<?php
		}

		public function settings_field_lostpassword_url()
		{
		?>
			<input name="<?php echo $this->options_key ?>[wpmls_lostpassword_url]" type="text" class="sml_token_field" value="<?php esc_attr_e($this->get_option('wpmls_lostpassword_url')); ?>" />
		<?php

		}



		public function settings_field_identification_key()
		{
		?>
			<input placeholder="BP_email" name="<?php echo $this->options_key ?>[wpmls_member_identification_key]" type="text" class="sml_member_identification_key_field advance-config" value="<?php esc_attr_e($this->get_option('wpmls_member_identification_key')); ?>" />
		<?php
		}

		public function settings_field_member_db_title()
		{
		?>
			<input placeholder="mssp_person_DB" name="<?php echo $this->options_key ?>[wpmls_member_db_title]" type="text" class="sml_member_home_url_field advance-config" value="<?php esc_attr_e($this->get_option('wpmls_member_db_title')); ?>" />
		<?php
		}

		public function settings_field_logout_url()
		{
		?>
			<input pattern="https?://.+" name="<?php echo $this->options_key ?>[member_logout_url]" type="text" class="sml_url_field sml_member_logout_url_field" value="<?php esc_attr_e($this->get_option('member_logout_url')); ?>" />
		<?php
		}

		public function settings_field_area_title()
		{
		?>
			<input placeholder="mssp_person_logi" name="<?php echo $this->options_key ?>[wpmls_area_title]" type="text" class="sml_area_title_field advance-config" value="<?php esc_attr_e($this->get_option('wpmls_area_title')); ?>" />
		<?php

		}

		public function settings_field_custom_module_path()
		{
		?>
			<input placeholder="mssp/wpmls_module/login_url.php" name="<?php echo $this->options_key ?>[wpmls_custom_module_path]" type="text" class="sml_area_title_field advance-config" value="<?php esc_attr_e($this->get_option('wpmls_custom_module_path')); ?>" />
		<?php

		}

		public function settings_field_default_name_key()
		{
		?>
			<input name="<?php echo $this->options_key ?>[wpmls_default_name_key]" type="text" class="sml_title_field basic_config" value="<?php esc_attr_e($this->get_option('wpmls_default_name_key')); ?>" />
		<?php
		}

		public function settings_field_login_id_label()
		{
		?>
			<div>
				<label for="">日本語</label>
				<input name="<?php echo $this->options_key ?>[login_id_label_jp]" type="text" class="sml_login_id_label_jp basic_config" value="<?php echo (empty(get_option('spiral_member_login')["login_id_label_jp"])) ? "ユーザー名" :  get_option('spiral_member_login')["login_id_label_jp"] ?>" required />
				<br><br>
				<label for="">English</label>
				<input name="<?php echo $this->options_key ?>[login_id_label_en]" type="text" class="sml_login_id_label_en basic_config" value="<?php echo (empty(get_option('spiral_member_login')["login_id_label_en"])) ? "User Name" :  get_option('spiral_member_login')["login_id_label_en"] ?>" required />
			</div>
		<?php

		}

		public function settings_field_profile_page_id()
		{
		?>
			<input name="<?php echo $this->options_key ?>[wpmls_profile_page_id]" type="text" class="sml_id_field basic_config" value="<?php esc_attr_e($this->get_option('wpmls_profile_page_id')); ?>" />
		<?php

		}

		public function settings_field_resetpass_page_id()
		{
		?>
			<input name="<?php echo $this->options_key ?>[wpmls_resetpass_page_id]" type="text" class="sml_id_field basic_config" value="<?php esc_attr_e($this->get_option('wpmls_resetpass_page_id')); ?>" />
		<?php
		}

		public function settings_field_withdrawal_page_id()
		{
		?>
			<input name="<?php echo $this->options_key ?>[wpmls_withdrawal_page_id]" type="text" class="sml_id_field basic_config" value="<?php esc_attr_e($this->get_option('wpmls_withdrawal_page_id')); ?>" />
		<?php
		}

		public function settings_field_member_list_search_title()
		{
		?>
			<input placeholder="mssp_wp_search" name="<?php echo $this->options_key ?>[wpmls_member_list_search_title]" type="text" class="sml_member_list_search_title_field" value="<?php esc_attr_e($this->get_option('wpmls_member_list_search_title')); ?>" />
		<?php
		}

		public function settings_field_auth_form_url()
		{
		?>
			<input name="<?php echo $this->options_key ?>[wpmls_auth_form_url]" type="text" class="sml_url_field basic_config" value="<?php esc_attr_e($this->get_option('wpmls_auth_form_url')); ?>" />
<?php
		}

		/**
		 * Render the settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_plugin_admin_page()
		{
			include_once(plugin_dir_path(__DIR__) . 'views/admins/admin.php');
		}

		/**
		 * Uninstall the plugin
		 *
		 * @access protected
		 */
		protected static function _uninstall()
		{
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');

			$pages = get_posts(array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'meta_key'       => '_sml_action',
				'posts_per_page' => -1
			));

			// Delete pages
			foreach ($pages as $page) {
				wp_delete_post($page->ID, true);
			}
		}

		public function is_logged_in($area_session_id = null)
		{
			if (get_option('wpmls_clear_cached') == "cleared") {
				$sml_sid = $this->session->get('sml_sid');
				$wpmls_area_title = $this->get_option('wpmls_area_title');
				if ($sml_sid) {
					@setcookie('is_login', false, time() - 1800, COOKIEPATH, COOKIE_DOMAIN, TRUE, TRUE);
					$result = $this->spiral->logout_area($wpmls_area_title, $sml_sid);
					$this->clear_user_options();
				}
				return false;
			}
			if (isset($_COOKIE['is_login'])) {
				return true;
			}
			if ($area_session_id == null) {
				$area_session_id = $this->session->get('sml_sid');
				if ($area_session_id == null) {

					return false;
				}
			}

			$wpmls_area_title = $this->get_option('wpmls_area_title');
			$result = $this->spiral->get_area_status($wpmls_area_title, $area_session_id);

			// when user is not exist in DB clear 
			if (!$result) {
				$this->clear_user_options();
			}
			@setcookie('is_login', true, time() + 1800, COOKIEPATH, COOKIE_DOMAIN, TRUE, TRUE); // 30 Minutes
			return $result === true;
		}

		public function get_user_props($key_prop = null)
		{
			if (!$this->is_logged_in()) {
				return null;
			}

			$wpmls_area_title = $this->get_option('wpmls_area_title');
			$area_session_id = $this->session->get('sml_sid');
			$search_title = $this->get_option('wpmls_member_list_search_title');
			$result = $this->spiral->get_table_data($wpmls_area_title, $area_session_id, $search_title);


			if ($result == null || (int)$result['count'] != 1) {
				return null;
			}

			$header = $result['header'];
			$data = $result['data'][0];

			$user_props = array();
			foreach ($header as $i => $key) {
				$user_props[$key] = $data[$i];
			}
			return $user_props;
		}

		public function get_user_extraction_rule($id, $select_name)
		{
			$wpmls_area_title = $this->get_option('wpmls_area_title');
			$db_title = $this->get_option('wpmls_member_db_title');
			$area_session_id = $this->session->get('sml_sid');
			$result = $this->spiral->get_extraction_rule($wpmls_area_title, $db_title, $area_session_id, $id, $select_name);
			$data = $result;
			return $data;
		}

		public function get_user_prop($key = 'name')
		{
			return $this->get_user_prop_by_key($key);
		}
	}

endif; // Class exists