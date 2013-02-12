<?php


/**
* Snitch_CPT
*
* @since 0.0.1
*/

class Snitch_CPT
{


	/**
	* Plugin options
	*
	* @since   0.0.1
	*/

	protected static $options = array();


	/**
	* Pseudo-Konstruktor der Klasse
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public static function instance()
	{
		new self();
	}


	/**
	* Registrierung der Post Types und Aktionen
	*
	* @since   0.0.1
	* @change  0.0.5
	*/

	public function __construct()
	{
		/* Set plugin options */
		self::$options = Snitch::get_options();

		/* Post Type */
		register_post_type(
			'snitch',
			array(
				'label' => 'Snitch',
				'labels' => array(
					'not_found' => translate('No items found. Future connections will be shown at this place.', 'snitch'),
					'not_found_in_trash' => translate('No items found in trash.', 'snitch')
				),
				'public' => false,
				'show_ui' => true,
				'query_var' => true,
				'hierarchical' => false,
				'menu_position' => 50,
				'capability_type' => 'snitch',
				'publicly_queryable' => false,
				'exclude_from_search' => true
			)
		);


		/* Admin only */
		if ( ! is_admin() ) {
			return;
		}


		/* CSS */
		add_action(
			'admin_print_scripts-edit.php',
			array(
				__CLASS__,
				'add_css'
			)
		);


		/* Bulk action */
		add_action(
			'load-edit.php',
			array(
				__CLASS__,
				'bulk_action'
			)
		);


		/* Admin notice */
		add_action(
			'admin_notices',
			array(
				__CLASS__,
				'updated_notice'
			)
		);
		add_action(
			'updated_notice',
			array(
				__CLASS__,
				'updated_notice'
			)
		);


		/* Hide menu item */
		add_action(
			'admin_menu',
			array(
				__CLASS__,
				'hide_menu'
			)
		);


		/* Filter dropdown */
		add_action(
			'restrict_manage_posts',
			array(
				__CLASS__,
				'filter_dropdown'
			)
		);
		add_filter(
			'parse_query',
			array(
				__CLASS__,
				'perform_filter'
			)
		);


		/* Action dropdown */
		add_filter(
			'bulk_actions-edit-snitch',
			array(
				__CLASS__,
				'bulk_actions'
			)
		);


		/* Custom columns */
		add_filter(
			'manage_snitch_posts_columns',
			array(
				__CLASS__,
				'manage_columns'
			)
		);
		add_filter(
			'manage_edit-snitch_sortable_columns',
			array(
				__CLASS__,
				'sortable_columns'
			)
		);
		add_action(
			'manage_snitch_posts_custom_column',
			array(
				__CLASS__,
				'custom_column'
			),
			10,
			2
		);
		add_filter(
			'request',
			array(
				__CLASS__,
				'column_orderby'
			)
		);


		/* View links */
		add_filter(
			'views_edit-snitch',
			array(
				__CLASS__,
				'views_edit'
			),
			10,
			1
		);
	}


	/**
	* Fügt Stylesheets hinzu
	*
	* @since   0.0.5
	* @change  0.0.5
	*/

	public static function add_css()
	{
		/* Local anesthesia */
		if ( get_current_screen()->id !== 'edit-snitch' ) {
			return;
		}

		/* Register styles */
		wp_register_style(
			'snitch_cpt_css',
			plugins_url(
				'css/cpt.min.css',
				SNITCH_FILE
			)
		);

		/* Add styles */
		wp_enqueue_style('snitch_cpt_css');
	}


	/**
	* Entfernt den Menüeintrag in der Sidebar
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public static function hide_menu()
	{
  		unset($GLOBALS['submenu']['edit.php?post_type=snitch'][10]);
	}


	/**
	* Definition der Filter-Auswahlbox
	*
	* @since   0.0.1
	* @change  0.0.3
	*/

	public static function filter_dropdown() {
		/* Local anesthesia */
		if ( get_current_screen()->id !== 'edit-snitch' OR ( !isset($_GET['snitch_state_filter']) && !_get_list_table('WP_Posts_List_Table')->has_items() ) ) {
			return;
		}

		/* Current value */
		$current = ( ! isset($_GET['snitch_state_filter']) ? '' : (int)$_GET['snitch_state_filter']);

		/* Print dropdown */
		echo sprintf(
			'<select name="snitch_state_filter">%s%s%s</select>',
			'<option value="">' .translate('All states', 'snitch'). '</option>',
			'<option value="' .SNITCH_AUTHORIZED. '" ' .selected($current, SNITCH_AUTHORIZED, false). '>' .translate('Authorized', 'snitch'). '</option>',
			'<option value="' .SNITCH_BLOCKED. '" ' .selected($current, SNITCH_BLOCKED, false). '>' .translate('Blocked', 'snitch'). '</option>'
		);
	}


	/**
	* Führt den Dropdown Filter aus
	*
	* @since   0.0.3
	* @change  0.0.3
	*
	* @param   array  $query  Array mit Abfragewerten
	* @return  array  $query  Array mit modifizierten Abfragewerten
	*/

	public static function perform_filter($query)
	{
		/* Local anesthesia */
		if ( get_current_screen()->id !== 'edit-snitch' OR empty($_GET['snitch_state_filter']) ) {
			return;
		}

		/* Set values */
		$query->query_vars['meta_key'] = 'state';
        $query->query_vars['meta_value'] = (int)$_GET['snitch_state_filter'];
	}


	/**
	* Entfernt Einträge aus der Aktion-Auswahlbox
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @param   array  $actions  Array mit Standard-Aktionen
	* @return  array  $actions  Array mit modifizierten Aktionen
	*/

	public static function bulk_actions($actions) {
		/* Kill me */
		unset($actions['edit']);

		return $actions;
	}


	/**
	* Verwaltung der benutzerdefinierten Spalten
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @hook    array  snitch_manage_columns
	*
	* @return  array  $columns  Array mit Spalten
	*/

	public static function manage_columns() {
		return (array)apply_filters(
			'snitch_manage_columns',
			array(
				'cb'      => '<input type="checkbox" />',
				'url'     => translate('Destination', 'snitch'),
				'file'    => translate('File', 'snitch'),
				'state'   => translate('State', 'snitch'),
				'code'    => translate('Code', 'snitch'),
				'created' => translate('Time', 'snitch')
			)
		);
	}


	/**
	* Verwaltung der sortierbaren Spalten
	*
	* @since   0.0.2
	* @change  0.0.3
	*
	* @hook    array  snitch_manage_columns
	*
	* @return  array  $columns  Array mit Spalten
	*/

	public static function sortable_columns()
	{
		return (array)apply_filters(
			'snitch_manage_columns',
			array(
				'url'     => 'url',
				'file'    => 'file',
				'state'   => 'state',
				'code'    => 'code',
				'created' => 'date'
			)
		);
	}


	/**
	* Führt die Filterung via Dropdown aus
	*
	* @since   0.0.3
	* @change  0.0.3
	*
	* @param   array  $vars  Array mit Abfragewerten
	* @return  array  $vars  Array mit modifizierten Abfragewerten
	*/

	public static function column_orderby($vars)
	{
		/* Check for orderby var */
		if ( empty($vars['orderby']) OR !in_array($vars['orderby'], array('url', 'file', 'state', 'code')) ) {
			return $vars;
		}

		/* Set var */
		$orderby = $vars['orderby'];

		return array_merge(
			$vars,
			array(
            	'meta_key' => $orderby,
            	'orderby' => ( in_array($orderby, array('code', 'state')) ? 'meta_value_num' : 'meta_value' )
        	)
        );
	}


	/**
	* Verwaltung der benutzerdefinierten Spalten
	*
	* @since   0.0.1
	* @change  0.0.3
	*
	* @hook    array    snitch_custom_column
	*
	* @param   string   $column  Aktueller Spaltenname
	* @param   integer  $post_id  Post-ID
	*/

	public static function custom_column($column, $post_id) {
		/* Column types */
		$types = (array)apply_filters(
			'snitch_custom_column',
			array(
				'url'     => array(__CLASS__, '_html_url'),
				'file'    => array(__CLASS__, '_html_file'),
				'state'   => array(__CLASS__, '_html_state'),
				'code'    => array(__CLASS__, '_html_code'),
				'created' => array(__CLASS__, '_html_created'),
			)
		);

		/* If type exists */
		if ( ! empty($types[$column]) ) {
			/* Callback */
			$callback = $types[$column];

			/* Execute */
			if ( is_callable($callback) ) {
				call_user_func(
					$callback,
					$post_id
				);
			}
		}
	}


	/**
	* HTML-Ausgabe der URL
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @param   integer  $post_id  Post-ID
	*/

	private static function _html_url($post_id)
	{
		/* Init data */
		$url = get_post_meta($post_id, 'url', true);
		$host = get_post_meta($post_id, 'host', true);

		/* Already blacklisted? */
		$blacklisted = in_array( $host, self::$options['hosts'] );

		/* Print output */
		echo sprintf(
			'<div><p class="label blacklisted_%s"></p>%s<div class="row-actions">%s</div></div>',
			$blacklisted,
			str_replace(
				$host,
				'<code>' .$host. '</code>',
				esc_url($url)
			),
			self::_action_link(
				$post_id,
				'host',
				$blacklisted
			)
		);
	}


	/**
	* HTML-Ausgabe der Herkunftsdatei
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @param   integer  $post_id  Post-ID
	*/

	private static function _html_file($post_id)
	{
		/* Init data */
		$file = get_post_meta($post_id, 'file', true);
		$line = get_post_meta($post_id, 'line', true);
		$meta = get_post_meta($post_id, 'meta', true);

		/* Already blacklisted? */
		$blacklisted = in_array( $file, self::$options['files'] );

		/* Print output */
		echo sprintf(
			'<div><p class="label blacklisted_%s"></p>%s: %s<br /><code>/%s:%d</code><div class="row-actions">%s</div></div>',
			$blacklisted,
			$meta['type'],
			$meta['name'],
			$file,
			$line,
			self::_action_link(
				$post_id,
				'file',
				$blacklisted
			)
		);
	}


	/**
	* HTML-Ausgabe des Zustandes
	*
	* @since   0.0.1
	* @change  0.0.3
	*
	* @param   integer  $post_id  Post-ID
	*/

	private static function _html_state($post_id)
	{
		/* Item state */
		$state = get_post_meta($post_id, 'state', true);

		/* State values */
		$states = array(
			SNITCH_BLOCKED    => 'Blocked',
			SNITCH_AUTHORIZED => 'Authorized'
		);

		/* Print the state */
		echo sprintf(
			'<span class="%s">%s</span>',
			strtolower($states[$state]),
			translate($states[$state], 'snitch')
		);

		/* Colorize blocked item */
		if ( $state == SNITCH_BLOCKED ) {
			echo sprintf(
				'<style>#post-%d {background:#f8eae8}</style>',
				$post_id
			);
		}
	}


	/**
	* HTML-Ausgabe des Status-Codes
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @param   integer  $post_id  Post-ID
	*/

	private static function _html_code($post_id)
	{
		echo get_post_meta($post_id, 'code', true);
	}


	/**
	* HTML-Ausgabe des Datums
	*
	* @since   0.0.2
	* @change  0.0.2
	*
	* @param   integer  $post_id  Post-ID
	*/

	private static function _html_created($post_id)
	{
		echo sprintf(
			__( '%s ago' ),
			human_time_diff( get_post_time('G', true, $post_id) )
		);
	}



	/**
	* Generierung der Action-Links
	*
	* @since   0.0.1
	* @change  0.0.5
	*
	* @param   integer  $post_id      Post-ID
	* @param   string   $type         Typ des Links (host|file)
	* @param   boolean  $blacklisted  Bereits in der Blacklist?
	* @return  string                 Zusammengebauter Action-Link
	*/

	private static function _action_link($post_id, $type, $blacklisted = false)
	{
		/* Link action */
		$action = ( $blacklisted ? 'unblock' : 'block' );

		/* Block link */
		return sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url(
				wp_nonce_url(
					admin_url(
						add_query_arg(
							array(
								'id'	    => $post_id,
								'paged'		=> self::_get_pagenum(),
								'type'		=> $type,
								'action'    => $action,
								'post_type' => 'snitch'
							),
							'edit.php'
						)
					),
					'snitch'
				)
			),
			$action,
			translate(
				sprintf(
					'%s this %s',
					ucfirst($action),
					$type
				),
				'snitch'
			)
		);
	}


	/**
	* Legt einen Custom Post Type Eintrag an
	*
	* @since   0.0.1
	* @change  0.0.3
	*
	* @param   array    $meta     Array mit Post-Metadaten
	* @return  integer  $post_id  Post-ID
	*/

	public static function insert_post($meta)
	{
		/* Empty? */
		if ( empty($meta) ) {
			return;
		}

		/* Create post */
		$post_id = wp_insert_post(
			array(
				'post_status' => 'publish',
				'post_type'   => 'snitch'
			)
		);

		/* Add meta values */
		foreach($meta as $key => $value) {
			add_post_meta(
				$post_id,
				$key,
				$value,
				true
			);
		}

		return $post_id;
	}


	/**
	* Ausführung der Link-Aktionen
	*
	* @since   0.0.1
	* @change  0.0.5
	*/

	public static function bulk_action()
	{
		/* Only Snitch */
		if ( get_current_screen()->id !== 'edit-snitch' ) {
			return;
		}

		/* Check for action and type */
		if ( empty($_GET['action']) OR empty($_GET['type']) ) {
			return;
		}

		/* Set vars */
		$action = $_GET['action'];
		$type = $_GET['type'];

		/* Validate action and type */
		if ( !in_array($action, array('block', 'unblock')) OR !in_array($type, array('host', 'file')) ) {
			return;
		}

		/* Security check */
		check_admin_referer('snitch');

		/* Merge bulk IDs */
		if ( !empty($_REQUEST['id']) ) {
			$ids = (array)(int)$_REQUEST['id'];
		} else if ( !empty($_REQUEST['ids']) ) {
			$ids = (array)$_REQUEST['ids'];
		} else {
			return;
		}

		/* Init */
		$items = array();

		/* Loop post meta */
		foreach ($ids as $post_id) {
			$items[] = get_post_meta($post_id, $type, true);
		}

		/* Handle types */
		call_user_func(
			array(
				'Snitch_Blacklist',
				$action
			),
			array_unique($items),
			$type. 's' /* code is poetry, really */
		);

		/* We're done */
		wp_safe_redirect(
			add_query_arg(
				array(
					'post_type' => 'snitch',
					'updated'   => count($ids) * ( $action === 'unblock' ? -1 : 1 ),
					'paged'     => self::_get_pagenum()
				),
				'edit.php'
			)
		);

		/* Fly */
		exit;
	}


	/**
	* Ausgabe des Administrator-Hinweises
	*
	* @since   0.0.1
	* @change  0.0.2
	*/

	public static function updated_notice()
	{
		/* Skip requests */
		if ( $GLOBALS['pagenow'] !== 'edit.php' OR $GLOBALS['typenow'] !== 'snitch' OR empty($_GET['updated']) ) {
			return;
		}

		/* Print */
		echo sprintf(
			'<div class="updated"><p>%s</p></div>',
			translate(
				( $_GET['updated'] > 0 ? 'New rule added to the Snitch filter. Matches are labeled in orange.' : 'An existing rule removed from the Snitch filter.' ),
				'snitch'
			)
		);
	}


	/**
	* Aktuelle Seitennummer der CPT-Ansicht
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @return  integer  void  Ermittelte Seitennummer
	*/

	private static function _get_pagenum()
	{
		return (empty($GLOBALS['pagenum']) ? _get_list_table('WP_Posts_List_Table')->get_pagenum() : $GLOBALS['pagenum'] );
	}


	/**
	* Erweitert die sekundäre Links-Leiste
	*
	* @since   0.0.4
	* @change  0.0.5
	*
	* @param   array  $views  Array mit verfügbaren Links
	* @return  array  $views  Array mit modifizierten Links
	*/

	public static function views_edit($views)
	{
		/* Kill the published link */
		unset($views['publish']);

		/* Donate links */
		$views['paypal'] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=5RDDW9FEHGLG6" target="_blank">PayPal</a>';
		$views['flattr'] = '<a href="https://flattr.com/donation/give/to/sergej.mueller" target="_blank">Flattr</a>';

		/* German manual */
		if ( get_locale() == 'de_DE' ) {
			$views['manual'] = '<a href="http://playground.ebiene.de/snitch-wordpress-netzwerkmonitor/" target="_blank">Handbuch</a>';
		}

		return $views;
	}
}