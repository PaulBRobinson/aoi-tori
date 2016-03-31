<?php

if(!class_exists('ReturnTruePluginUpdater')) {

	class ReturnTruePluginUpdater {

		private $api_endpoint;

		private $plugin_id;

		private $plugin_name;

		private $plugin_file;

		public function __construct($plugin_id, $plugin_name, $text_domain, $api_url, $plugin_file = '') {
			
			//Setup private vars
			$this->plugin_id = $plugin_id;
	        $this->plugin_name = $plugin_name;
	        $this->text_domain = $text_domain;
	        $this->api_endpoint = $api_url;
	        $this->plugin_file = $plugin_file;

	        add_action('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
	        add_action('plugins_api', array($this, 'plugins_api_handler'), 10, 3);
	        //Add message if no license is currently active
	        add_action('in_plugin_update_message-' . plugin_basename( $this->plugin_file ), array($this, 'in_plugin_update_message'), 10, 2);
	        //Add API url to external host list
	        add_filter('http_request_host_is_external', array($this, 'allow_custom_update_url'), 10, 3);
		}

		private function is_license_valid() {
			if( get_transient( plugin_basename( basename($this->plugin_file, '.php') ) . '_license_status' ) == 'valid' ||
				get_transient( plugin_basename( basename($this->plugin_file, '.php') ) . '_license_status' ) == 'valid_unlimited' ) {
				return true;
			}

			return false;
		} 

		public function in_plugin_update_message( $plugin_data, $r ) {
		
			// validate
			if($this->is_license_valid())
				return;

			$m = __('To enable updates, please enter your license key on the <a href="%s">Options</a> page.', 'acf');
			
			echo '<br />' . sprintf( $m, admin_url('options-general.php?page=options-general.php-aoi-tori-options'));
		
		}

		public function allow_custom_update_url($allow, $host, $url) {
			
			if($host == 'return-true.com')
				$allow = true;

			//For debug & local dev purposes only !!! DO NOT UNCOMMENT IN PRODUCTION !!!
			//No. Really... DO NOT DO IT !!!
			//if($host == 'wp.dev')
			//	$allow = true;

			return $allow;
		}

		public function plugins_api_handler($res, $action, $args) {
			if($action == 'plugin_information') {
				if(isset($args->slug) && $args->slug == plugin_basename( basename($this->plugin_file, '.php') )) {
					$info = $this->get_plugin_info();

					$res = (object) array(
						'name' => isset( $info->name ) ? $info->name : '',
		                'version' => $info->version,
		                'slug' => $args->slug,
		                'download_link' => $info->package_url,
		 
		                'tested' => isset( $info->tested ) ? $info->tested : '',
		                'requires' => isset( $info->requires ) ? $info->requires : '',
		                'last_updated' => isset( $info->last_updated ) ? $info->last_updated : '',
		                'homepage' => isset( $info->description_url ) ? $info->description_url : '',
		 
		                'sections' => array(
		                    'description' => $info->description,
		                ),
		 
		                'banners' => array(
		                    'low' => isset( $info->banner_low ) ? $info->banner_low : '',
		                    'high' => isset( $info->banner_high ) ? $info->banner_high : ''
		                ),
		 
		                'external' => true
					);
				}

				if(isset($info->changelog)) {
					$res['sections']['changelog'] = $info->changelog;
				}

				return $res;
			}

			return false;
		}

		public function check_for_update($transient) {
			if(empty($transient->checked))
				return $transient;

			if($this->is_update_available()) {
				$info = $this->get_plugin_info();

				$plugin_slug = plugin_basename( $this->plugin_file );

				$transient->response[$plugin_slug] = (object) array(
					'new_version' => $info->version,
					'package' => $info->package_url,
					'slug' => basename($plugin_slug, '.php'),
					'plugin' => $plugin_slug
				);

				if(!$this->is_license_valid())
					$transient->response[$plugin_slug]->package = '';

				//Register the update row with WordPress as this was ran on admin-init, too early for our updater to have already ran
				add_action( "after_plugin_row_$plugin_slug", 'wp_plugin_update_row', 10, 2 );
			}

			return $transient;
		}

		private function is_api_error($reponse) {
			if($response === false)
				return true;

			if(!is_object($reponse))
				return true;

			if(isset($response->error))
				return true;

			return false;
		}

		private function call_api($action, $params) {
			$url = $this->api_endpoint . '/' . $action;

			$url .= '?' . http_build_query($params);

			$response = wp_remote_get($url);
			if(is_wp_error($reponse))
				return false;

			$response_body = wp_remote_retrieve_body($response);
			$result = json_decode($response_body);

			return $result;
		}

		public function get_plugin_info() {
			$info = $this->call_api(
				'info',
				array(
					'pid' => $this->plugin_id,
					'l' => $this->get_license(),
				)
			);

			return $info;
		}

		private function get_license() {
			//Get manually because Titan may not be available, unserialize again due to how options are stored by Titan
			$options = unserialize(get_option($this->plugin_name . '_options'));

			return isset($options['plugin_license']) ? $options['plugin_license'] : '';
		}

		public function is_update_available() {
			$plugin_info = $this->get_plugin_info();

			if($this->is_api_error($plugin_info))
				return false;

			if(version_compare($plugin_info->version, $this->get_local_version(), '>'))
				return $plugin_info;

			return false;
		}

		private function get_local_version() {
			$plugin_data = get_plugin_data($this->plugin_file, false);
			return $plugin_data['Version'];
		}
	}

}