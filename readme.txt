=== jonradio Multiple Themes ===
Contributors: jonradio
Donate link: http://jonradio.com/plugins
Tags: themes, theme
Requires at least: 3.4.1
Tested up to: 3.4.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Select different Themes for one or more, or all WordPress Pages, Posts or Admin Panels.  Or Site Home.

== Description ==

Allows the Administrator to specify which Theme will be used on specific Pages, Posts, Admin Panels or Site Home.  Also allows a Theme to be specified for All Pages, All Posts or All Admin Panel Pages. In turn, even when a Theme is specified for All Pages, a different Theme can still be specified for specific Pages.

If a Theme is not specified for a Page, Post, Admin Panel, Site Home or other WordPress-displayed web page (e.g. - Category display), the **Current Theme** specified in **Appearance-Themes** on the Admin panels will be used.

When selecting a Theme for Site Home or all or specific Pages, Posts or Admin Panels, the Theme must be shown in the list of Available Themes on the Appearance-Themes Admin panel. If necessary, install the Theme.  In a WordPress Network (AKA Multisite), Themes must be **Network Enabled** before they will appear as Available Themes on individual sites' Appearance-Themes panel.

I hesitate to use the term **Theme Switcher** to describe this plugin, because the term has so many meanings.  This plugin does **not** alter the standard WordPress options that define what Theme is used on your WordPress site.  Instead, it dynamically (and selectively) overrides that choice.  Technical details aside, what this means is that deactivating or deleting the plugin will instantly revert to the WordPress Theme that you have defined through the standard WordPress Appearance-Themes Admin panel.

**Limitation**: This plugin does not currently support Theme usage that involves the stylesheet and template names not being the same.

**Note**: Few, if any, Themes alter the appearance of the Admin Panel Pages.  This Plugin hopes to change that.  For those who cannot wait, there are Plugins, most of which call themselves Themes but are listed in the WordPress Plugin Directory, that change the appearance of Admin Panel Pages.

== Installation ==

This section describes how to install the plugin and get it working.

1. Use "Add Plugin" within the WordPress Admin panel to download and install this plugin from the WordPress.org plugin repository (preferred method).  Or download and unzip this plugin, then upload the `/jonradio-multiple-themes/` directory to your WordPress web site's `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.  If you have a WordPress Network ("Multisite"), you can either Network Activate this plugin, or Activate it individually on the sites where you wish to use it.  Activating on individual sites within a Network avoids some of the confusion created by WordPress' hiding of Network Activated plugins on the Plugin menu of individual sites.
1. Select Themes to be used on the Plugin's "Multiple Themes plugin" page in the WordPress Admin panels, which is found in both the **Appearance** and **Settings** sections.  You can also get to this page by clicking on the **Settings** link for this plugin on the **Installed Plugins** page.

== Frequently Asked Questions ==

= I specified a Theme for an Admin Page, but I don't see any change.  What happened? =

The Theme you specified leaves the Admin panels unaltered.  Most Themes do not change the appearance of Admin panels.

= I added a new entry but why doesn't it appear in the list of entries? =

If you add an entry that already exists, it merely replaces the previous entry.

= How can I change the Theme for an entry? =

Simply add the entry again, with the new Theme.  It will replace the previous entry.

== Screenshots ==

1. Plugin's Admin Page when first installed
2. Plugin's Admin Page with entries added

== Changelog ==

= 1.1 =
* Fix foreach failing on some systems, based on PHP warning level

= 1.0 =
* Make plugin conform to WordPress plugin repository standards.
* Beta testing completed.

== Upgrade Notice ==

= 1.1 =
Eliminate possibility of foreach error message if PHP warning level is set at a high level

= 1.0 =
Beta version 0.9 had not been tested when installed from the WordPress Plugin Repository