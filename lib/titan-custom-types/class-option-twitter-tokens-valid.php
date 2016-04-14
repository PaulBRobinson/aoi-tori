<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TitanFrameworkOptionTwitterTokensValid extends TitanFrameworkOption {

	public $defaultSecondarySettings = array();

	public function display() {
		if ( !empty( $this->owner->postID ) ) {
			return;
		}

    $this->echoOptionHeader();

    if(!get_option('aoitori_need_check_token_valid', false)) {
      ?>
      <p>No check is currently needed.</p>
      <?php
    } else {
      ?>
      <p id="tokens_valid">Check in progress, please wait.</p>
      <?php
    }

    $this->echoOptionFooter();
	}
}
