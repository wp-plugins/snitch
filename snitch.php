<?php
/*
Plugin Name: Snitch
Description: Network monitor for WordPress. Connecting overview for monitoring and controlling of outbound blog traffic.
Text Domain: snitch
Domain Path: /lang
Author: Sergej M&uuml;ller
Author URI: http://wpcoder.de
Plugin URI: http://wordpress.org/extend/plugins/snitch/
Version: 1.0.4
*/


/* Sicherheitsabfrage */
if ( ! class_exists('WP') ) {
	die();
}


/* Konstanten */
define('SNITCH_FILE', __FILE__);
define('SNITCH_BASE', plugin_basename(__FILE__));
define('SNITCH_BLOCKED', 1);
define('SNITCH_AUTHORIZED', -1);


/* Hooks */
add_action(
	'plugins_loaded',
	array(
		'Snitch',
		'instance'
	)
);


/* Install */
register_activation_hook(
	__FILE__,
	array(
		'Snitch',
		'install'
	)
);


/* Uninstall */
register_uninstall_hook(
	__FILE__,
	array(
		'Snitch',
		'uninstall'
	)
);


/* Autoload Init */
spl_autoload_register('snitch_autoload');

/* Autoload Funktion */
function snitch_autoload($class) {
	if ( in_array($class, array('Snitch', 'Snitch_HTTP', 'Snitch_CPT', 'Snitch_Blacklist')) ) {
		require_once(
			sprintf(
				'%s/inc/%s.class.php',
				dirname(__FILE__),
				strtolower($class)
			)
		);
	}
}