# Widget Output Cache

Contributors: kasparsd   
Tags: cache, caching, widget, widgets, output, transient, object cache, memcache, apc, performance   
Requires at least: 3.0.1   
Tested up to: 4.5.2    
Stable tag: trunk   
License: GPLv2 or later   

Improve website performance by caching widget output in WordPress transients.


## Description

Use PHP output buffering to extract widget output and store it into WordPress transients for faster retrieval. It also adds a checkbox to widget controls to exclude it from being cached.

It is a quick fix for bad behaving plugins that parse RSS feeds or call remote URLs on every page load.


## Installation

Install it from the official WordPress repository or use the plugin search in your WordPress dashboard.


## Frequently Asked Questions

None.


## Screenshots

None.


## Changelog

### 0.5.2
* Tested with WordPress 4.5.2.

### 0.5.1
* Fixed transient name generation issue.

### 0.5
* Remove menu cache due to "active" menu item bug.
* Allow individual widgets to be excluded from being cached.

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
