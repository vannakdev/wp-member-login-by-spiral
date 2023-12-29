<?php

/**
 * SPIRAL Platform API class
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */

if (!class_exists('SpiralPlatform_Api')) :

  /**
   * =======================================
   * SpiralPlatform_Api
   * =======================================
   */
  class SpiralPlatform_Api
  {
    private $token = null;
    /**
     * For production
     */
    // 		private $api_url = 'https://api.spiral-platform.com/v1';
    /**
     * For Beta
     */
    private $api_url = 'https://api.beta.spiral-platform.net/v1';

    private $x_spiral_api_version = '1.1';

    public $app_id        = null;
    public $db_id         = null;
    public $wpmls_site_id       = null;
    public $wpmls_authentication_id   = null;
    private $user_data     = null;
    private $area_status     = null;

    public function __construct($token)
    {
      $this->token         = $token;
    }

    public function set_options($app_id, $db_id, $wpmls_site_id, $wpmls_authentication_id)
    {
      $this->app_id        = $app_id;
      $this->db_id         = $db_id;
      $this->wpmls_site_id        = $wpmls_site_id;
      $this->wpmls_authentication_id   = $wpmls_authentication_id;
    }

    public function request_spiral_api($method, $api_path, $params = null)
    {
      if ($api_path === null || $api_path === '') {
        return null;
      }

      $api_headers = array(
        "Authorization" => "Bearer $this->token",
        'Content-Type' => 'application/json',
      );

      if ($this->x_spiral_api_version !== null) {
        $api_headers['X-Spiral-Api-Version'] = $this->x_spiral_api_version;
      }

      $args = array(
        'headers' => $api_headers
      );

      $response = null;
      $full_path = $this->get_api_url($api_path);
      // for console checkc only
      // d("Request ... $method $full_path");
      switch (strtoupper($method)) {
        case 'GET':
          if ($params != null) {
            $full_path .= $this->generate_paramers($params);
          }

          $response = wp_remote_get($full_path, $args);
          break;
        case 'POST':
          if ($params != null) {
            $args['body'] = json_encode($params);
          }
          $response = wp_remote_post($full_path, $args);
          break;
        default:
          break;
      }

      if ($response == null || is_wp_error($response)) {
        return null;
      }

      $result = json_decode($response["body"], true);
      return $result;
    }

    public function get_api_url($path = '')
    {
      return  $this->api_url . $path;
    }

    public function login_area($wpmls_site_id, $wpmls_authentication_id, $id = null, $password = null)
    {
      $parameters = array();

      if ($id) {
        $parameters["id"] = $id;
      }
      if ($password) {
        $parameters["password"] = $password;
      }

      $result = $this->request_spiral_api('POST', "/sites/$wpmls_site_id/authentications/$wpmls_authentication_id/login", $parameters);
      return $result;
    }

    public function logout($token = null)
    {
      $parameters = array();
      if ($token) {
        $parameters["token"] = $token;
      }
      $wpmls_site_id = $this->wpmls_site_id;
      $wpmls_authentication_id = $this->wpmls_authentication_id;

      $this->request_spiral_api('POST', "/sites/$wpmls_site_id/authentications/$wpmls_authentication_id/logout", $parameters);
      $this->area_status = null;
    }

    public function get_area_status($wpmls_site_id, $wpmls_authentication_id, $session_token = null)
    {
      // To prevent on many request
      if ($this->area_status !== null) {
        return $this->area_status;
      }
      $parameters = array();
      if ($session_token) {
        $parameters["token"] = $session_token;
      }

      $result = $this->request_spiral_api('POST', "/sites/$wpmls_site_id/authentications/$wpmls_authentication_id/status", $parameters);

      if ($result !== null && isset($result['status'])) {
        $this->area_status = $result['status'];
      }
      return $this->area_status;
    }

    public function get_user_action_url($token, $path)
    {
      $parameters = array();
      if ($token) {
        $parameters["token"] = $token;
      }
      if ($token) {
        $parameters["path"] = $path;
      }

      $wpmls_site_id = $this->wpmls_site_id;
      $wpmls_authentication_id = $this->wpmls_authentication_id;

      $result = $this->request_spiral_api('POST', "/sites/$wpmls_site_id/authentications/$wpmls_authentication_id/oneTimeLogin", $parameters);

      if (isset($result['url'])) {
        return $result['url'];
      }
      return null;
    }

    /**
     * @return null||array(fields=>Array(), items=>Array(), options=>Array(), prevOffset=>null,nextOffset=>null, totalCount=>null)
     */
    public function get_user_data($appid, $dbid, $wpmls_identification_key, $user_key)
    {
      $parameters = array();
      $fields = $this->get_db_columns($appid, $dbid);
      $parameters["where"] = "@$wpmls_identification_key='$user_key'";


      $result = $this->request_spiral_api('get', "/apps/$appid/dbs/$dbid/records", $parameters);

      if ($result !== null && isset($result['items'][0])) {
        $result_items = $result["items"][0];

        foreach ($result_items as $key => $value) {
          if (!$value == null && !is_array($value)) {
            $result_items[$key] = $value;
          }
        }
        $result["items"][0] = $result_items;
        return $result;
      } else {
        return null;
      }
    }


    public function get_user($appid, $dbid, $record_id)
    {
      // If already has user data
      if ($this->user_data != null) {
        return $this->user_data;
      }
      if (!isset($dbid))
        return null;
      if (!isset($dbid))
        return null;
      if (!isset($record_id))
        return null;

      $result = $this->request_spiral_api('get', "/apps/$appid/dbs/$dbid/records/$record_id");

      if ($result !== null) {
        $this->user_data = $result;
        return $result;
      } else {
        return null;
      }
    }

    public function get_db_columns($appid, $dbid)
    {
      $result = $this->request_spiral_api('get', "/apps/$appid/dbs/$dbid");

      if ($result !== null && isset($result["fields"])) {
        $fields = $result["fields"];
        $count_fields = count($result["fields"]);
        $columns = [];
        for ($i = 0; $i < $count_fields; $i++) {
          $columns[] = $fields[$i]["name"];
        }
        return $columns;
      } else {
        return null;
      }
    }

    public function get_extraction_rule($wpmls_area_title, $db_title, $session_id, $id, $select_name)
    {
    }

    public function get_table_data($wpmls_area_title, $session_id, $search_title, $options = null)
    {
    }

    /**
     * Generate parameters to string path
     * @return string
     */
    private function generate_paramers($parameters)
    {
      if ($parameters == null || $parameters == '' || !isset($parameters)) {
        return '';
      }
      $parameter_search_str = '';
      $args['body'] = json_encode($parameters);
      foreach ($parameters as $key => $value) {
        $parameter_search_str .= $parameter_search_str === '' ? '?' : '&';
        $parameter_search_str .= "$key=$value";
      }
      return $parameter_search_str;
    }


    public function check_selectable_field($appid, $dbid, $select_field)
    {
      $allow_select_fields = ["select", "multiselect", "integer"];

      $result = $this->request_spiral_api('get', "/apps/$appid/dbs/$dbid");

      if ($result !== null && isset($result["fields"])) {
        $fields = $result["fields"];
        $count_fields = count($result["fields"]);
        $field_name = [];
        $field_type = [];
        for ($i = 0; $i < $count_fields; $i++) {
          $field_name[] = $fields[$i]["name"];
          $field_type[] = $fields[$i]["type"];
        }
        $fields =  array_combine($field_name, $field_type);

        if (isset($fields[$select_field])) {

          $field_value =  $fields[$select_field];
          if (in_array($field_value, $allow_select_fields)) {
            return true;
          } else {
            return false;
          }
        } else {
          return null;
        }
      } else {
        return null;
      }
    }
  }

endif; // Class exists