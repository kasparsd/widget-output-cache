<?php
/*
	Plugin Name: Widget Output Cache
	Description: Caches widget output in WordPress object cache.
	Version: 0.5.1
	Plugin URI: https://wordpress.org/plugins/widget-output-cache/
	GitHub URI: https://github.com/kasparsd/widget-output-cache
	Author: Kaspars Dambis
	Author URI: http://kaspars.net
*/


WidgetOutputCache::instance();


class WidgetOutputCache {

	// Store IDs of widgets to exclude from cache
	private $excluded_ids = array();


	protected function __construct() {

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


	public static function instance() {

		static $instance;

		if ( ! $instance )
			$instance = new self();

		return $instance;

	}


	function init_l10n() {

		load_plugin_textdomain( 'widget-output-cache', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}


	function init() {

		$this->excluded_ids = (array) get_option( 'cache-widgets-excluded', array() );

	}


	function widget_callback( $instance, $widget_object, $args ) {
		
		// Don't return the widget
		if ( false === $instance || ! is_subclass_of( $widget_object, 'WP_Widget' ) )
			return $instance;

		if ( in_array( $widget_object->id, $this->excluded_ids ) )
			return $instance;

		$cache_key = sprintf(
				'cwdgt-%s',
				md5( $widget_object->id . get_option( 'cache-widgets-version', 1 ) )
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

