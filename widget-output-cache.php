<?php
/*
	Plugin Name: Widget & Menu Output Cache
	Description: Caches widget and menu output in WordPress object cache.
	Version: 0.4.3
	Plugin URI: https://github.com/kasparsd/widget-output-cache
	GitHub URI: https://github.com/kasparsd/widget-output-cache
	Author: Kaspars Dambis
	Author URI: http://kaspars.net
*/


add_filter( 'widget_display_callback', 'maybe_cache_widget_output', 10, 3 );

function maybe_cache_widget_output( $instance, $widget_object, $args ) {
	
	$timer_start = microtime(true);

	$version = get_option( 'cache-widgets-version', 1 );
	$cache_key = 'cache-widget-' . md5( serialize( array( $instance, $args ) ) ) . $version;

	$cached_widget = get_transient( $cache_key );

	if ( empty( $cached_widget ) ) {
		ob_start();
			$widget_object->widget( $args, $instance );
			$cached_widget = ob_get_contents();
		ob_end_clean();

		set_transient( $cache_key, $cached_widget, apply_filters( 'widget_output_cache_ttl', 0, $args ) );
	}

	printf( 
		"%s <!-- From widget cache in %s seconds -->",
		$cached_widget,
		number_format( microtime(true) - $timer_start, 5 ) 
	);

	// We already echoed the widget, so return false
	return false;

}


// World's first plugin to use this new filter added to WordPress core (by me!)
// See: https://core.trac.wordpress.org/ticket/23627
add_filter( 'pre_wp_nav_menu', 'maybe_cache_return_menu_output', 10, 2 );

function maybe_cache_return_menu_output( $nav_menu, $args ) {

	$version = get_option( 'cache-menus-version', 1 );
	$cache_key = 'cache-menu-' . md5( serialize( $args ) ) . $version;
	$cached_menu = get_transient( $cache_key );

	// Return the menu from cache
	if ( ! empty( $cached_menu ) )
		return $cached_menu;

	return $nav_menu;

}


global $wp_version;

// Store menu output in a transient if WP 3.9+
if ( version_compare( $wp_version, '3.9-RC', '>=' ) ) {
	add_filter( 'wp_nav_menu', 'maybe_cache_menu_output', 10, 2 );
}

function maybe_cache_menu_output( $nav_menu, $args ) {

	$version = get_option( 'cache-menus-version', 1 );
	$cache_key = 'cache-menu-' . md5( serialize( $args ) ) . $version;

	// Store menu output in a transient
	set_transient( $cache_key, $nav_menu, apply_filters( 'menu_output_cache_ttl', 0, $args ) );
	
	return $nav_menu;

}


// Cache invalidation for menus
add_action( 'wp_update_nav_menu', 'menu_output_cache_bump' );

function menu_output_cache_bump() {

	update_option( 'cache-menus-version', time() );

}


// Cache invalidation for widgets
add_filter( 'widget_update_callback', 'widget_output_cache_bump' );

function widget_output_cache_bump( $instance ) {

	update_option( 'cache-widgets-version', time() );

	return $instance;

}



