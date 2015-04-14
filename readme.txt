=== jonradio Multiple Themes ===
Contributors: jonradio
Donate link: http://zatzlabs.com/plugins/
Tags: themes, theme
Requires at least: 3.4.1
Tested up to: 3.4.2
Stable tag: 2.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Select different Themes for one or more, or all WordPress Pages, Posts or other non-Admin pages.  Or Site Home.

== Description ==

**This is a Beta Version of Version 3.0, but all testing to date indicates that its release now will correct major problems with previous versions, without introducing any new bugs.**

Allows the Administrator to specify which Theme will be used on specific Pages, Posts, other non-Admin pages (such as Category or Archive pages) or Site Home.  Also allows a Theme to be specified for All Pages or All Posts. In turn, even when a Theme is specified for All Pages or All Posts, a different Theme can still be specified for specific Pages or Posts.

If a Theme is not specified for a Page, Post, Site Home or other WordPress-displayed web page, such as Category or Archive display, the **Current Theme** specified in **Appearance-Themes** on the Admin panels will be used.

When selecting a Theme for Site Home or all or specific Pages, Posts or other non-Admin pages, the Theme must be shown in the list of Available Themes on the Appearance-Themes Admin panel. If necessary, install the Theme.  In a WordPress Network (AKA Multisite), Themes must be **Network Enabled** before they will appear as Available Themes on individual sites' Appearance-Themes panel.

I hesitate to use the term **Theme Switcher** to describe this plugin, because the term has so many meanings.  This plugin does **not** alter the standard WordPress options that define what Theme is used on your WordPress site.  Instead, it dynamically (and selectively) overrides that choice.  Technical details aside, what this means is that deactivating or deleting the plugin will instantly revert to the WordPress Current Theme that you have defined through the standard WordPress Appearance-Themes Admin panel.

== Installation ==

This section describes how to install the plugin and get it working.

1. Use "Add Plugin" within the WordPress Admin panel to download and install this plugin from the WordPress.org plugin repository (preferred method).  Or download and unzip this plugin, then upload the `/jonradio-multiple-themes/` directory to your WordPress web site's `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.  If you have a WordPress Network ("Multisite"), you can either Network Activate this plugin, or Activate it individually on the sites where you wish to use it.  Activating on individual sites within a Network avoids some of the confusion created by WordPress' hiding of Network Activated plugins on the Plugin menu of individual sites.
1. Be sure that all Themes you plan to use have been installed and are listed under Available Themes on the WordPress Appearance-Themes Admin panel. In a WordPress Network (AKA Multisite), Themes must be **Network Enabled** before they will appear as Available Themes on individual sites' Appearance-Themes panel.
1. Select Themes to be used on the Plugin's "Multiple Themes plugin" page in the WordPress Admin panels, which is found in both the **Appearance** and **Settings** sections.  You can also get to this page by clicking on the **Settings** link for this plugin on the **Installed Plugins** page.

== Frequently Asked Questions ==

= I added a new entry but why doesn't it appear in the list of entries? =

If you add an entry that already exists, it merely replaces the previous entry.

= How can I change the Theme for an entry? =

Simply add the entry again, with the new Theme.  It will replace the previous entry.

== Screenshots ==

1. Plugin's Admin Page when first installed
2. Plugin's Admin Page with entries added

== Changelog ==

= 2.9 =
* Add Support for IIS which returns incorrect values in $_SERVER['REQUEST_URI']
* Make it easier to select the Theme for the Site Home by providing a new Settings field
* Remove ability to set Theme for Admin pages since no known Theme provides Admin templates, and because the previous implementation sometimes displayed the incorrect Current Theme in Admin;  this feature may be re-added in a future release, and could even be used to change Settings of Themes that are not currently the Current Theme
* Add version upgrade detection to add, remove and update Settings fields
* Move Settings link on Plugins page from beginning to end of links

= 2.0 =
* Address pecularities of wp_make_link_relative() related to root-based WordPress sites using Permalinks

= 1.1 =
* Fix foreach failing on some systems, based on PHP warning level

= 1.0 =
* Make plugin conform to WordPress plugin repository standards.
* Beta testing completed.

== Upgrade Notice ==

= 2.9 =
Improve Settings fields, correct display of wrong Current Theme in Appearance-Themes Admin panel, and add IIS Support.

= 2.0 =
Selecting Individual Pages and Posts on a WordPress site installed in the root and using Permalinks now works correctly.

= 1.1 =
Eliminate possibility of foreach error message if PHP warning level is set at a high level

= 1.0 =
Beta version 0.9 had not been tested when installed from the WordPress Plugin Repository