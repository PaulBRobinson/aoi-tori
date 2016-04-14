<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TitanFrameworkOptionLicense extends TitanFrameworkOption {

	public $defaultSecondarySettings = array(
		'plugin_name' => '',
		'product_permalink' => false,
		'placeholder' => 'XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX',
		'is_password' => false,
	);

	public function display() {
		if ( !empty( $this->owner->postID ) ) {
			return;
		}

		/* Get the license */
		$license = esc_attr( sanitize_key( $this->getValue() ) );

	    $this->echoOptionHeader();

	    printf( '<input class="regular-text" name="%s" placeholder="%s" id="%s" type="%s" value="%s" />',
					$this->getID(),
					$this->settings['placeholder'],
					$this->getID(),
					$this->settings['is_password'] ? 'password' : 'text',
				$license );

	    if(strlen($license) > 0) {

	    	/* Get the license activation status */
			$status = get_transient( "{$this->settings['plugin_name']}_license_status" );

			/* For debug only, forces transient to be false so it will always check the license key. */
			$status = false;

			/* If no transient is found or it is expired to check the license again. */
			if ( false == $status ) {
				$status = $this->check( $license );
			}

			switch ( $status ) {

				case 'valid':
					?><p class="description"><?php esc_html_e( 'Your license is valid.', 'aoitori' ); ?></p><?php
				break;

				case 'valid_unlimited':
					?><p class="description"><?php esc_html_e( 'Your license is valid and can be used on any number of domains.', 'aoitori' ); ?></p><?php
				break;

				case 'invalid':
					?><p class="description"><?php esc_html_e( 'Your license is invalid.', 'aoitori' ); ?></p><?php
				break;

				case 'no_response':
					?><p class="description"><?php esc_html_e( 'The remote server did not return a valid response. You can retry by hitting the &laquo;Save&raquo; button again.', 'aoitori' ); ?></p><?php
				break;

				case 'invalid_refund':
					?><p class="description"><?php esc_html_e( 'You have requested a refund or have initiated a chargeback on your purchase. Your license has been revoked because of this and you are no longer entitled to updates. Please contact support@return-true.com if you believe this to be in error. If it is not in error and you continue to use this plugin you are commiting an offence, please discontinue use immediately.', 'aoitori'); ?></p><?php
				break;

				case 'invalid_domain':
					?><p class="description"><?php esc_html_e( 'Your license does not allow updates to this domain. You are only allowed updates to the domain entered at the checkout when purchasing this plugin. If you believe you purchased an unlimited domain license or you are using the plugin on the domain you entered at purchase please get in touch via support@return-true.com so we can look into the problem.', 'aoitori'); ?></p><?php
				break;

				case 'unknown_error':
					?><p class="description"><?php esc_html_e( 'There was an unknown error. You should never see this error, if you are seeing it something has gone terribly wrong. Please get in touch via support@return-true.com so we can help you as soon as possible.', 'aoitori'); ?></p><?php
				break;

			}
		} else {
			?><p class="description"><?php esc_html_e( 'Entering your license key is mandatory to receive product updates.', 'aoitori' ); ?></p><?php
		}

	    $this->echoOptionFooter();
	}

	public function check($license, $action = 'check_license') {

		if ( empty( $license ) || !$this->settings['product_permalink'] ) {
			return false;
		}

		/* Sanitize the key. */
		$license = trim( sanitize_key( $license ) );

		/* Set license validity time, after this it will be checked again */
		$check_time = YEAR_IN_SECONDS;

		$api_params = array(
			'product_permalink' => $this->settings['product_permalink'],
			'license_key' => $license,
			'increment_uses_count' => (($action == 'use_license') ? 'true' : 'false'),
		);

		$response = wp_remote_post( add_query_arg( $api_params, $this->settings['server'] ), array( 'timeout' => 15 ) );

		/* Check for request error. */
		if ( is_wp_error( $response ) ) {
			return false;
		}

		/* Decode license data. */
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		/* If the remote server didn't return a valid response we just return an error and don't set any transients so that activation will be tried again next time the option is saved */
		if ( ! is_object( $license_data ) || empty( $license_data ) || ! isset( $license_data->success ) ) {
			return 'no_response';
		}

		/* If there success is false or there is no purchase data the license is invalid. */
		if( $license_data->success === false || !isset($license_data->purchase) ) {
			set_transient( "{$this->settings['plugin_name']}_license_status", 'invalid', $check_time );
			return 'invalid';
		}

		if( $license_data->purchase->chargebacked || $license_data->purchase->refunded ) {
			set_transient( "{$this->settings['plugin_name']}_license_status", 'invalid_refund', $check_time );
			return 'invalid_refund';
		}

		/* Is this license allowed to be used on ulimited domains? If so, bypass everything else (at this point we know the license is valid) and return valid */
		if( isset( $license_data->purchase->variants) && strcasecmp($license_data->purchase->variants, '(unlimited domains)') === 0) {
			set_transient( "{$this->settings['plugin_name']}_license_status", 'valid_unlimited', $check_time );
			delete_site_transient( 'update_plugins' ); //Delete the update_plugins transient to force an update check as the user can now update
			return 'valid_unlimited';
		}

		/* If there is domain data they are only licensed to update the plugin while it is installed on a specific domain. Check the domain to make sure it matches. */
		if( isset( $license_data->purchase->custom_fields[0] ) ) {

			//We don't know if the URL has www. or not & parse_url requires http:// so let's format the domain a little
			$licenseDomain = str_ireplace(array('domain: ', 'http://', 'https://'), '', trim($license_data->purchase->custom_fields[0]));
			$licenseDomain = preg_replace("/(www\.)/is", "", $licenseDomain);
			$licenseDomain = "http://" . $licenseDomain;

			$licenseDomain = parse_url($licenseDomain, PHP_URL_HOST);

			$thisDomain = parse_url(get_site_url(), PHP_URL_HOST);

			//Compare the domain & the expected domain. If they are not the same (binary safe) the license is not for this domain.
			if(strcasecmp($licenseDomain, $thisDomain) !== 0) {
				set_transient( "{$this->settings['plugin_name']}_license_status", 'invalid_domain', $check_time );
				return 'invalid_domain';
			} else {
				set_transient( "{$this->settings['plugin_name']}_license_status", 'valid', $check_time );
				delete_site_transient( 'update_plugins' ); //Delete the update_plugins transient to force an update check as the user can now update
				return 'valid';
			}

		}



		return 'unknown_error';

	}
}
