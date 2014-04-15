# Widget & Menu Output Cache

Contributors: kasparsd   
Tags: cache, caching, widget, widgets, output, transient, object cache, memcache, apc, menu, performance   
Requires at least: 3.0.1   
Tested up to: 3.9   
Stable tag: trunk   
License: GPLv2 or later   

Improve website performance by caching widget and menu output in WordPress transients.


## Description

Uses the `pre_wp_nav_menu` filter (introduced in WordPress 3.9) to short-circuit menu rendering and PHP output buffering to extract widget output and store it into WordPress transients for later retrieval.

In theory, it should save up to six database queries per menu on each page load!

It is a quick fix for bad behaving plugins that parse RSS feeds or call remote URLs on every page load.


## Installation

Install it from the official WordPress repository or use the plugin search in your WordPress dashboard.


## Frequently Asked Questions 

None.


## Screenshots

None.


## Changelog

### 0.4.4
* Honor widgets being hidden using the `widget_display_callback` filter.

### 0.4.3
* Add cache versioning for simple invalidation.

### 0.4.2
* Store menu cache only if WordPress supports `pre_wp_nav_menu`.

### 0.4
* Add support for menu output caching.

### 0.1
* Initial release.


### Upgrade Notice
