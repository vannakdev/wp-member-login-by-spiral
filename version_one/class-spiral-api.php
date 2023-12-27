<?php

/**
 * SPIRAL API class
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */

if (!class_exists('Spiral_Api')) :
	/**
	 *
	 * SPIRAL API class.
	 *
	 * @package Spiral_Member_Login
	 * @author  PIPED BITS Co.,Ltd.
	 */
	class Spiral_Api extends Spiral_Member_Login_Base
	{

		private $token 			= null;
		private $token_secret 	= null;
		private $api_url 		= null;
		private $user_data 		= null;
		private $area_status 	= null;

		public function __construct($token, $token_secret)
		{
			$this->token = $token;
			$this->token_secret = $token_secret;
		}

		public function request_spiral_api($api_url, $api_path, $params)
		{
			if ($api_url === null) {
				return null;
			}

			$api_headers = array(
				"X-SPIRAL-API" => "${api_path}/request",
				"Content-Type" => "application/json; charset=UTF-8"
			);

			$args = array(
				'headers' => $api_headers,
				'body' => json_encode($params)
			);
			
			$response = wp_remote_post($api_url, $args);

			if (is_wp_error($response)) {
				return null;
			}

			$result = json_decode($response["body"], true);
			return $result;
		}

		protected function _sign_params(&$params)
		{
			$params["spiral_api_token"] = $this->token;
			$params["passkey"] = time();
			$key = $params["spiral_api_token"] . "&" . $params["passkey"];
			$params["signature"] = hash_hmac('sha1', $key, $this->token_secret, false);
		}

		public function get_api_url()
		{
			if ($this->api_url === null) {
				$locator_url = "https://www.pi-pe.co.jp/api/locator";

				$params = array();
				$params["spiral_api_token"] = $this->token;

				$result = $this->request_spiral_api($locator_url, "locator/apiserver", $params);

				if (!$result or $result["code"] != "0") {
					return null;
				}
				$this->api_url = $result["location"];
			}

			return $this->api_url;
		}

		public function login_area($area_title, $id = null, $key = null, $password = null)
		{
			$parameters = array();
			$parameters["my_area_title"] = $area_title;
			$parameters["url_type"] = 2;

			if ($id) {
				$parameters["id"] = $id;
			}
			if ($key) {
				$parameters["key"] = $key;
			}
			if ($password) {
				$parameters["password"] = $password;
			}

			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "area/login", $parameters);
			return $result;
		}

		public function logout_area($area_title, $session_id)
		{
			$parameters = array();
			$parameters["my_area_title"] = $area_title;
			$parameters["jsessionid"] = $session_id;

			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "area/logout", $parameters);
			$this->area_status = null;
			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				return $result['url'];
			} else {
				return null;
			}
		}

		public function get_area_status($area_title, $session_id)
		{
			// To prevent on many request
			if ($this->area_status !== null) {
				return $this->area_status;
			}
			$parameters = array();
			$parameters["my_area_title"] = $area_title;
			$parameters["jsessionid"] = $session_id;

			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "area/status", $parameters);
			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				$this->area_status = true;
				return (int)$result['status'] === 1;
			} else {
				$this->area_status = false;
				return null;
			}
		}

		public function get_area_mypage($area_title, $session_id, $mypage_id)
		{
			$parameters = array();
			$parameters["my_area_title"] = $area_title;
			$parameters["jsessionid"] = $session_id;
			$parameters["my_page_id"] = $mypage_id;
			$parameters["url_type"] = 2;


			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "area/mypage", $parameters);

			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				return $result['url'];
			} else {
				return null;
			}
		}

		public function checkIfIndexInStringAndConvertToArray($array, $string)
		{

			$stringArray = explode(',', $string);
			$convertedArray = [];
			foreach ($stringArray as $value) {
				if (array_key_exists($value, $array)) {
					$convertedArray[] = $array[$value];
				}
			}
			$convertedArray = implode(',', $convertedArray);
			return $convertedArray;
		}

		public function get_user_record_value($db_title, $identification_key, $user_key)
		{
			$parameters = array();
			$parameters["search_condition"] = array(array("name" => $identification_key, "value" => $user_key, "operator" => "="));
			$parameters["db_title"] = $db_title;
			$columns = $this->get_db_columns($db_title);
			$parameters["select_columns"] = $columns;
			$this->_sign_params($parameters);
			$result = $this->request_spiral_api($this->get_api_url(), "database/select", $parameters);

			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {

				if (count($result["data"]) == 0) {
					@setcookie('is_login', false, time() - 1800, COOKIEPATH, COOKIE_DOMAIN, TRUE, TRUE); // DELETE COOKIE
					return null;
				}

				$arr_user_data = $result["data"][0];

				$arr_user_data = array_map(function ($value) {
					return $value;
				}, $arr_user_data);

				$arr_user_fiels = array_map(function ($value) {
					return $value;
				}, $columns);

				if (!is_null($arr_user_data)) {
					$user_data = array_combine($arr_user_fiels, $arr_user_data);
				} else {
					return null;
				}

				return $user_data;
			} else {
				return null;
			}
		}


		public function get_user_record($db_title, $identification_key, $user_key)
		{
			$parameters = array();
			$parameters["search_condition"] = array(array("name" => $identification_key, "value" => $user_key, "operator" => "="));
			$parameters["db_title"] = $db_title;
			$columns = $this->get_db_columns($db_title);
			$columnTypes = $this->get_db_columns_types($db_title);

			$parameters["select_columns"] = $columns;
			$this->_sign_params($parameters);
			$result = $this->request_spiral_api($this->get_api_url(), "database/select", $parameters);

			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {

				if (count($result["data"]) == 0) {
					@setcookie('is_login', false, time() - 1800, COOKIEPATH, COOKIE_DOMAIN, TRUE, TRUE); // DELETE COOKIE
					return null;
				}

				$arr_user_data = $result["data"][0];

				$arr_user_data = array_map(function ($value) {
					return $value;
				}, $arr_user_data);

				$arr_user_fiels = array_map(function ($value) {
					return $value;
				}, $columns);

				foreach ($columnTypes as $key => $val) {
					if ($val == "mm_multiple" || $val == "mm_alternative") {
						$convertedArray = $this->checkIfIndexInStringAndConvertToArray($result['label'][$key - 1], $arr_user_data[$key]);
						$arr_user_data[$key] = $convertedArray;
					}
				}
				if (!is_null($arr_user_data)) {
					$user_data = array_combine($arr_user_fiels, $arr_user_data);
				} else {
					return null;
				}
				return $user_data;
			} else {
				return null;
			}
		}

		public function check_selectable_field($db_title, $identification_key, $user_key, $field_name)
		{
			$allow_select_fields = ["mm_alternative", "mm_multiple", "mm_multiple128","mm_integer"];

			$parameters = array();
			$parameters["db_title"] = $db_title;
			$this->_sign_params($parameters);
			$result = $this->request_spiral_api($this->get_api_url(), "database/get", $parameters);
			
			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				$columns = [];
				$types = [];

				for ($i = 0; $i < count($result["schema"]["fieldList"]); $i++) {
					$column = $result["schema"]["fieldList"][$i];
					$columns[] = $column["title"];
					$types[$column["title"]] = $column["type"];
				}

				if (array_key_exists($field_name, $types)) {

					$field_value = $types[$field_name];

					if (in_array($field_value, $allow_select_fields)) {
						return true;
					} else {
						return false;
					}
				}
				return false;
			} else {
				return false;
			}
		}

		public function get_db_columns($db_title)
		{
			$parameters = array();
			$parameters["db_title"] = $db_title;
			$this->_sign_params($parameters);
			$result = $this->request_spiral_api($this->get_api_url(), "database/get", $parameters);

			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				$columns = ['id'];
				for ($i = 0; $i < count($result["schema"]["fieldList"]); $i++) {
					$column = $result["schema"]["fieldList"][$i];
					$columns[] = $column["title"];
				}
				return $columns;
			} else {
				return null;
			}
		}

		public function get_db_columns_types($db_title)
		{
			$parameters = array();
			$parameters["db_title"] = $db_title;
			$this->_sign_params($parameters);
			$result = $this->request_spiral_api($this->get_api_url(), "database/get", $parameters);
			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				$columnsTypes = ['id'];
				for ($i = 0; $i < count($result["schema"]["fieldList"]); $i++) {
					$columnTypes = $result["schema"]["fieldList"][$i];
					$columnsTypes[] = $columnTypes["type"];
				}
				return $columnsTypes;
			} else {
				return null;
			}
		}

		public function get_extraction_rule($area_title, $db_title, $session_id, $id, $select_name)
		{
			$parameters = array();
			$parameters["search_condition"] = array(array("name" => "id", "value" => $id, "operator" => "="));
			$parameters["jsessionid"] = $session_id;
			$parameters["select_name"] = $select_name;
			$parameters["db_title"] = $db_title;

			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "database/select", $parameters);
			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				return $result['count'];
			} else {
				return null;
			}
		}

		public function get_table_data($area_title, $session_id, $search_title, $options = null)
		{
			// If already has user data
			if ($this->user_data != null) {
				return $this->user_data;
			}
			$parameters = array();
			if ($options && is_array($options)) {
				$parameters = $options;
			}
			$parameters["my_area_title"] = $area_title;
			$parameters["jsessionid"] = $session_id;
			$parameters["search_title"] = $search_title;

			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "table/data", $parameters);

			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				$this->user_data = $result;
				return $result;
			} else {
				return null;
			}
		}
	}

endif; // Class exists