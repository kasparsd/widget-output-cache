<?php
/*
	Plugin Name: Partial Cache
	Description: Caches widget and menu output using the WordPress object cache.
	Version: 1.0
	Plugin URI: https://wordpress.org/plugins/widget-output-cache/
	GitHub URI: https://github.com/kasparsd/widget-output-cache
	Author: kasparsd, khromov
	Author URI: http://kaspars.net
*/


$PartialCachePlugin = PartialCache::instance();

class PartialCache {

	private static $instance;

	// Text domain
	private static $td = 'partial-cache';

	// Store IDs of widgets to exclude from cache
	private $excluded_ids = array();

	protected function __construct() {

		global $wp_version;

		/* Menu cache if WP >=3.9 */
		if ( version_compare( $wp_version, '3.9-RC', '>=' ) ) {

			add_filter( 'wp_nav_menu', array($this, 'menu_cache'), 10, 2 );
			add_filter( 'pre_wp_nav_menu', array( $this, 'menu_output' ), 10, 2 );
			add_action( 'wp_update_nav_menu', array( $this, 'menu_cache_bump' ) );
		}

		/* Widgets cache */

		// Enable localization
		add_action( 'plugins_loaded', array( $this, 'init_l10n' ) );

		// Overwrite widget callback to cache the output
		add_filter( 'widget_display_callback', array( $this, 'widget_callback' ), 10, 3 );

		// Cache invalidation for widgets
		add_filter( 'widget_update_callback', array( $this, 'cache_bump' ) );

		// Allow widgets to be excluded from the cache
		add_action( 'in_widget_form', array( $this, 'widget_controls' ), 10, 3 );

		// Load widget cache exclude settings
		add_action( 'init', array( $this, 'init' ), 10 );

		// Save widget cache settings
		add_action( 'sidebar_admin_setup', array( $this, 'save_widget_controls' ) );

	}

	/**
	 * Overrides the menu output with cached version
	 * if one is available.
	 *
	 * @param $nav_menu
	 * @param $args
	 * @return mixed
	 */
	function menu_output( $nav_menu, $args )
	{
		$cache_key = sprintf(
			'pc-m-%s', // m for menus
			md5(  md5( serialize( $args ) . '-' . get_option( 'cache-menus-version', 1 ) ) )
		);

		$cached_menu = get_transient( $cache_key );
		var_dump($cache_key);

		// Return the menu from cache
		if ( ! empty( $cached_menu ) )
			return $cached_menu;

		return $nav_menu;
	}

	/**
	 * Cache menu output
	 *
	 * @param $nav_menu
	 * @param $args
	 * @return mixed
	 */
	function menu_cache( $nav_menu, $args ) {

		$cache_key = sprintf(
			'pc-m-%s', // m for menus
			md5(  md5( serialize( $args ) . '-' . get_option( 'cache-menus-version', 1 ) ) )
		);

		// Store menu output in a transient
		set_transient( $cache_key, $nav_menu, apply_filters( 'menu_output_cache_ttl', 60 * 12, $args ) );
		return $nav_menu;
	}

	function menu_cache_bump() {
		update_option( 'cache-menus-version', time() );
	}

	/**
	 * Returns the correct
	 *
	 * @return PartialCache
	 */
	public static function instance() {

		// http://stackoverflow.com/a/9159235
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;

	}


	/**
	 * Initialize plugin translation
	 */
	function init_l10n() {

		load_plugin_textdomain( self::$td, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}


	function init() {

		$this->excluded_ids = (array) get_option( 'cache-widgets-excluded', array() );

	}


	/**
	 * Handles displaying cached widgets on the frontend.
	 *
	 * @param $instance
	 * @param $widget_object
	 * @param $args
	 * @return bool
	 */
	function widget_callback( $instance, $widget_object, $args ) {
		
		// Don't return the widget
		if ( false === $instance || ! is_subclass_of( $widget_object, 'WP_Widget' ) )
			return $instance;

		if ( in_array( $widget_object->id, $this->excluded_ids ) )
			return $instance;

		$cache_key = sprintf(
				'pc-w-%s', //w for widgets
				md5( $widget_object->id . '-' . get_option( 'cache-widgets-version', 1 ) )
			);

		$cached_widget = get_transient( $cache_key );

		if ( empty( $cached_widget ) ) {

			ob_start();
				$widget_object->widget( $args, $instance );
				$cached_widget = ob_get_contents();
			ob_end_clean();

			set_transient(
				$cache_key,
				$cached_widget,
				apply_filters( 'widget_output_cache_ttl', 60 * 12, $args )
			);

			printf('%s', $cached_widget);
		}
		else
		{
			printf(
				'%s <!-- From widget cache via transient key: %s -->',
				$cached_widget,
				$cache_key
			);
		}

		// We already echoed the widget, so return false
		return false;
	}

	/**
	 * Bumps cache.
	 *
	 * TODO: It may be better if we deleted the transients instead of bumping the cache name.
	 *
	 * This is because if transients are stored in DB, they will not get cleared this way unless
	 * you run a dedicated plugin such as this:
	 * https://wordpress.org/plugins/delete-expired-transients/
	 *
	 * @param $instance
	 * @return mixed
	 */
	function cache_bump( $instance ) {

		update_option( 'cache-widgets-version', time() );

		return $instance;

	}


	function widget_controls( $object, $return, $instance ) {

		include('templates/admin-widget.php');

	}


	function save_widget_controls() {

		// current_user_can( 'edit_theme_options' ) is already being checked in widgets.php
		if ( empty( $_POST ) || ! isset( $_POST['widget-id'] ) )
			return;

		$widget_id = $_POST['widget-id'];
		$is_excluded = isset( $_POST['widget-cache-exclude'] );

		if ( ! isset($_POST['delete_widget']) && $is_excluded ) {

			// Wiget is being saved and it is being excluded too
			$this->excluded_ids[] = $widget_id;

		} elseif ( in_array( $widget_id, $this->excluded_ids ) ) {

			// Widget is being removed, remove it from exclusions too
			$exclude_pos_key = array_search( $widget_id, $this->excluded_ids );
			unset( $this->excluded_ids[ $exclude_pos_key ] );

		}

		$this->excluded_ids = array_unique( $this->excluded_ids );

		update_option( 'cache-widgets-excluded', $this->excluded_ids );

	}


}

