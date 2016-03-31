<?php

if(!defined( 'WP_UNINSTALL_PLUGIN' )) exit();

delete_option('aoitori_version');
delete_option('aoitori_need_check_token_valid');

//Titan removes it's own options so no need to do that