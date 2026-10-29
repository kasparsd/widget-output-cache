# Widget Output Cache

Contributors: kasparsd   
Tags: cache, caching, widget, widgets, output, transient, object cache, memcache, apc, performance   
Requires at least: 3.0.1   
Tested up to: 7.1.4    
License: GPLv2 or later   

Improve website performance by caching widget output in WordPress transients.


## Description

Use PHP output buffering to extract widget output and store it into WordPress transients for faster retrieval. It also adds a checkbox to widget controls to exclude it from being cached.

It is a quick fix for bad behaving plugins that parse RSS feeds or call remote URLs on every page load.

Note that the plugin supports only **Classic Widgets** -- the caching does not apply to widgets rendered in the block-based editor.


## Installation

Install it from the official WordPress repository or use the plugin search in your WordPress dashboard.


## Frequently Asked Questions

None.


## Contributing

Pull requests are welcome. Please include a changeset with your PR so that any code changes are versioned and changelogged by `@wpelevator/wp-release`

Create a changeset by running `npx changeset` and passing the "widget-output-cache" package, or add a `.changeset/*.md` file manually:

```
---
"widget-output-cache": patch
---

Short description of the change.
```


## Screenshots

None.


