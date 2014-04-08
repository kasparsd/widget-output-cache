<?php
/*
	Plugin Name: Widget & Menu Output Cache
	Description: Caches widget and menu output in WordPress object cache.
	Version: 0.4
	Plugin URI: https://github.com/kasparsd/widget-output-cache
	GitHub URI: https://github.com/kasparsd/widget-output-cache
	Author: Kaspars Dambis
	Author URI: http://kaspars.net
*/

add_filter( 'widget_display_callback', 'maybe_cache_widget_output', 10, 3 );

function maybe_cache_widget_output( $instance, $widget_object, $args ) {
	$timer_start = microtime(true);
	$cache_key = 'cache-widget-' . md5( serialize( array( $instance, $args ) ) );

	$cached_widget = get_transient( $cache_key );

	if ( empty( $cached_widget ) ) {
		ob_start();
			$widget_object->widget( $args, $instance );
			$cached_widget = ob_get_contents();
		ob_end_clean();

		set_transient( $cache_key, $cached_widget, apply_filters( 'widget_output_cache_ttl', 60 * 5, $args ) );
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
	$cache_key = 'cache-menu-' . md5( serialize( $args ) );
	$cached_menu = get_transient( $cache_key );

	// Return the menu from cache
	if ( ! empty( $cached_menu ) )
		return $cached_menu;

	return $nav_menu;
}

// Store menu output in a transient
add_filter( 'wp_nav_menu', 'maybe_cache_menu_output', 10, 2 );

function maybe_cache_menu_output( $nav_menu, $args ) {
	$cache_key = 'cache-menu-' . md5( serialize( $args ) );

	// Store menu output in a transient
	set_transient( $cache_key, $nav_menu, apply_filters( 'menu_output_cache_ttl', 60 * 5, $args ) );

	return $nav_menu;
}

