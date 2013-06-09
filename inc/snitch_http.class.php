<?php


/* Quit */
defined('ABSPATH') OR exit;


/**
* Snitch_HTTP
*
* @since 0.0.1
*/

class Snitch_HTTP
{


	/**
	* Prüft den ausgehenden Request
	*
	* @since   0.0.1
	* @change  0.0.5
	*
	* @hook    array    snitch_inspect_request_hosts
	* @hook    array    snitch_inspect_request_files
	* @hook    array    snitch_inspect_request_insert_post
	*
	* @param   boolean  $pre   FALSE
	* @param   array    $args  Argumente der Anfrage
	* @param   string   $url   URL der Anfrage
	* @return  mixed           FALSE im Erfolgsfall
	*/

	public static function inspect_request($pre, $args, $url)
	{
		/* Empty url */
		if ( empty($url) ) {
			return false;
		}

		/* Invalid host */
		if ( ! $host = parse_url($url, PHP_URL_HOST) ) {
			return false;
		}

		/* Snitch options */
		$options = Snitch::get_options();

		/* Blacklisted items */
		$blacklist = array(
			'hosts' => (array)apply_filters(
				'snitch_inspect_request_hosts',
				$options['hosts']
			),
			'files' => (array)apply_filters(
				'snitch_inspect_request_files',
				$options['files']
			)
		);

		/* Backtrace data */
		$backtrace = self::_debug_backtrace();

		/* No reference file found */
		if ( empty($backtrace['file']) ) {
			return false;
		}

		/* Show your face, file */
		$meta = self::_face_detect($backtrace['file']);

		/* Init data */
		$file = str_replace(ABSPATH, '', $backtrace['file']);
		$line = (int)$backtrace['line'];

		/* Blocked item? */
		if ( in_array($host, $blacklist['hosts']) OR in_array($file, $blacklist['files']) ) {
			return Snitch_CPT::insert_post(
				(array)apply_filters(
					'snitch_inspect_request_insert_post',
					array(
						'url'   => esc_url_raw($url),
						'code'  => NULL,
						'host'  => $host,
						'file'  => $file,
						'line'  => $line,
						'meta'  => $meta,
						'state' => SNITCH_BLOCKED
					)
				)
			);
		}

		return false;
	}


	/**
	* Protokolliert den Request
	*
	* @since   0.0.1
	* @change  0.0.5
	*
	* @hook   array   snitch_log_response_insert_post
	*
	* @param  object  $response  Response-Object
	* @param  string  $type      Typ der API
	* @param  string  $class     Klasse der API
	* @param  array   $args      Argumente der API
	* @param  string  $url       URL der API
	*/

	public static function log_response($response, $type, $class, $args, $url)
	{
		/* Log only response */
		if ( $type !== 'response' ) {
			return false;
		}

		/* Empty url */
		if ( empty($url) ) {
			return false;
		}

		/* Invalid host */
		if ( ! $host = parse_url($url, PHP_URL_HOST) ) {
			return false;
		}

		/* Backtrace data */
		$backtrace = self::_debug_backtrace();

		/* No reference file found */
		if ( empty($backtrace['file']) ) {
			return false;
		}

		/* Show your face, file */
		$meta = self::_face_detect($backtrace['file']);

		/* Init data */
		$file = str_replace(ABSPATH, '', $backtrace['file']);
		$line = (int)$backtrace['line'];

		/* Insert post */
		Snitch_CPT::insert_post(
			(array)apply_filters(
				'snitch_log_response_insert_post',
				array(
					'url'   => esc_url_raw($url),
					'code'  => wp_remote_retrieve_response_code($response),
					'host'  => $host,
					'file'  => $file,
					'line'  => $line,
					'meta'  => $meta,
					'state' => SNITCH_AUTHORIZED
				)
			)
		);
	}


	/**
	* Ermittelt die Ursprungsdatei des Requests
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @return  array   $item   Information zu Herkunft
	*/

	private static function _debug_backtrace() {
		/* Reverse items */
		$trace = array_reverse(debug_backtrace());

		/* Loop items */
    	foreach( $trace as $index => $item ) {
    		if ( ! empty($item['function']) && strpos($item['function'], 'wp_remote_') !== false ) {
    			/* Use prev item */
    			if ( empty($item['file']) ) {
    				$item = $trace[-- $index];
    			}

    			/* Get file and line */
    			if ( ! empty($item['file']) && ! empty($item['line']) ) {
    				return $item;
    			}
    		}
    	}
	}


	/**
	* Versuch die Datei anhand des Pfades zuzuordnen
	*
	* @since   0.0.1
	* @change  0.0.5
	*
	* @param   string  $path  Pfad der Datei
	* @return  array   $meta  Array mit Informationen
	*/

	private static function _face_detect($path)
	{
		/* Default */
		$meta = array(
			'type' => 'WordPress',
			'name' => 'Core'
		);

		/* Empty path */
		if ( empty($path) ) {
			return $meta;
		}

		/* Search for plugin */
		if ( $data = self::_localize_plugin($path) ) {
			return array(
				'type' => 'Plugin',
				'name' => $data['Name']
			);

		/* Search for theme */
		} else if ( $data = self::_localize_theme($path) ) {
			return array(
				'type' => 'Theme',
				'name' => $data->get('Name')
			);
		}

		return $meta;
	}


	/**
	* Suche nach einem Plugin anhand des Pfades
	*
	* @since   0.0.1
	* @change  0.0.5
	*
	* @param   string  $path  Pfad einer Datei aus dem Plugin-Ordner
	* @return  array   void   Array mit Plugin-Daten
	*/

	private static function _localize_plugin($path)
	{
		/* Check path */
		if ( strpos($path, WP_PLUGIN_DIR) === false ) {
			return false;
		}

		/* Reduce path */
		$path = ltrim(
			str_replace(WP_PLUGIN_DIR, '', $path),
			DIRECTORY_SEPARATOR
		);

		/* Get plugin folder */
		$folder = substr(
			$path,
			0,
			strpos($path, DIRECTORY_SEPARATOR)
		);

		/* Frontend */
		if ( ! function_exists('get_plugins')) {
			require_once(ABSPATH. 'wp-admin/includes/plugin.php');
		}

		/* All active plugins */
		$plugins = get_plugins();

		/* Loop plugins */
		foreach( $plugins as $path => $plugin ) {
			if ( strpos($path, $folder. '/') === 0 ) {
				return $plugin;
			}
		}
	}


	/**
	* Suche nach einem Theme anhand des Pfades
	*
	* @since   0.0.1
	* @change  0.0.5
	*
	* @param   string  $path  Pfad einer Datei aus dem Theme-Ordner
	* @return  object  void   Objekt mit Theme-Daten
	*/

	private static function _localize_theme($path)
	{
		/* Check path */
		if ( strpos($path, get_theme_root()) === false ) {
			return false;
		}

		/* Reduce path */
		$path = ltrim(
			str_replace(get_theme_root(), '', $path),
			DIRECTORY_SEPARATOR
		);

		/* Get theme folder */
		$folder = substr(
			$path,
			0,
			strpos($path, DIRECTORY_SEPARATOR)
		);

		/* Get theme */
		$theme = wp_get_theme($folder);

		/* Check & return theme */
		if ( $theme->exists() ) {
			return $theme;
		}

		return false;
	}
}