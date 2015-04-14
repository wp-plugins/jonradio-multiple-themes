=== jonradio Multiple Themes ===
Contributors: jonradio
Donate link: http://zatzlabs.com/plugins/
Tags: themes, theme, sections, style, template, stylesheet, accessibility
Requires at least: 3.4.1
Tested up to: 3.5.1
Stable tag: 4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Select different Themes for one or more, or all WordPress Pages, Posts or other non-Admin pages.  Or Site Home.

== Description ==

Allows the Administrator to specify which Theme will be used on specific Pages, Posts, other non-Admin pages (such as Category or Archive pages) or Site Home.  Also allows a Theme to be specified for All Pages or All Posts. In turn, even when a Theme is specified for All Pages or All Posts, a different Theme can still be specified for specific Pages or Posts.

Think what you could do if you could easily use more than one Theme on your WordPress web site or blog:

* Divide your site into Sections, each with its own unique look
* Style individual Pages, Posts, or other elements (Site Home, Category main page, Archive main page) with a different Theme
* Select a unique Theme for all Pages, Posts, Attachments, Category pages or Archive pages
* Make slight variations to a Theme, using Child Themes, for one or more Pages, Posts or other elements (Site Home, Category main page, Archive main page)
* Supports Multiple Stylesheets for Accessibility and other purposes (create one Child Theme for each Stylesheet)
* Test a new Theme on one or more Pages or Posts
* Convert to a new Theme a Page or Post at a time
* Host multiple mini-web sites on your WordPress site, each with a different Theme
* When a larger version of an image attachment is displayed, for example, when clicking on a gallery image thumbnail, use a different theme to display it, for one or all images attached to a Page or Post

**Use with other Plugins**:  **BuddyPress** and Theme Test Drive plugins must both be Deactivated when jonradio Multiple Themes is Activated

**Changing Theme Options (Widgets, Sidebars, Menus, Background, Header, etc.)?**:  See the FAQ (Frequently-Asked Questions) tab for important information on changing Options on Themes other than the Current Theme.

To select all Pages, Posts, Archives, Categories, etc. that begin with the same partial URL, a "Prefix URL" can be specified by selecting the Prefix checkbox when creating an entry on the Settings page for the Plugin.  Restriction:  the Prefix URL cannot contain all or part of a Query, which is the portion of a URL that begins with a question mark ("?").  Although the Prefix cannot contain a Query, URLs containing a Query will be matched by the Prefix.

If a Theme is not specified for a Page, Post, Site Home or other WordPress-displayed web page, such as Category or Archive display, the **Current Theme** specified in **Appearance-Themes** on the Admin panels will be used.

When selecting a Theme for Site Home or all or specific Pages, Posts or other non-Admin pages, the Theme must be shown in the list of Available Themes on the Appearance-Themes Admin panel. If necessary, install the Theme.  In a WordPress Network (AKA Multisite), Themes must be **Network Enabled** before they will appear as Available Themes on individual sites' Appearance-Themes panel.

I hesitate to use the term **Theme Switcher** to describe this plugin, because the term has so many meanings.  This plugin does **not** alter the standard WordPress options that define what Theme is used on your WordPress site.  Instead, it dynamically (and selectively) overrides that choice.  Technical details aside, what this means is that deactivating or deleting the plugin will instantly revert to the WordPress Current Theme that you have defined through the standard WordPress Appearance-Themes Admin panel.

== Installation ==

This section describes how to install the plugin and get it working.

1. Use "Add Plugin" within the WordPress Admin panel to download and install this plugin from the WordPress.org plugin repository (preferred method).  Or download and unzip this plugin, then upload the `/jonradio-multiple-themes/` directory to your WordPress web site's `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.  If you have a WordPress Network ("Multisite"), you can either Network Activate this plugin, or Activate it individually on the sites where you wish to use it.  Activating on individual sites within a Network avoids some of the confusion created by WordPress' hiding of Network Activated plugins on the Plugin menu of individual sites.
1. Be sure that all Themes you plan to use have been installed and are listed under Available Themes on the WordPress Appearance-Themes Admin panel. In a WordPress Network (AKA Multisite), Themes must be **Network Enabled** before they will appear as Available Themes on individual sites' Appearance-Themes panel.
1. Select Themes to be used on the Plugin's "Multiple Themes plugin" page in the WordPress Admin panels, which is found in both the **Appearance** and **Settings** sections.  You can also get to this page by clicking on the **Settings** link for this plugin on the **Installed Plugins** page.
1. If you need to change Theme Options (Widgets, Sidebars, Menus, Background, Header, etc.) for any Theme other than the Current Theme, see the FAQ (Frequently-Asked Questions) tab for important information.

== Frequently Asked Questions ==

= How do I change the Theme Options (Widgets, Sidebars, Menus, Background, Header, etc.) used for each Theme? =

Options for all Themes, including Widgets, Sidebars, Menus, Background, Header and other Customizations supported by the Theme, can be modified in the Admin panel using the Appearance menu items on the left sidebar and the Customize link displayed beside the Current Theme on the Manage Themes tab of Appearance-Themes.

For the Current Theme, there are no issues, as WordPress provides this functionality without any intervention by plugins. 

However, to modify Options for Active Themes that are *not* the Current Theme, the jonradio Multiple Themes plugin (i.e. - this plugin) must be Deactivated, and the Theme Test Drive plugin installed and activated, so that each Theme can be selected with the Theme Test Drive plugin, allowing the Theme's Options to be set "as if" it were the Current Theme.

**MENUS - this approach only allows Menus to be set for one Theme.  Using this method to assign one or more menus to a Theme will unassign menus for all other Themes.**

Design work is currently underway to fully support Theme Options, including Menus, for each Theme used with the jonradio Multiple Themes plugin.  Until that work is completed, in a future version of this plugin, the following Workaround using the Theme Test Drive plugin is being provided:

1. Deactivate jonradio Multiple Themes 
1. Install the Theme Test Drive plugin found at http://wordpress.org/extend/plugins/theme-test-drive/
1. Activate the Theme Test Drive plugin
1. Go to Appearance-Theme Test Drive 
1. In the Usage section, select an alternate Theme you will be using with jonradio Multiple Themes 
1. Push the Enable Theme Drive button at the bottom 
1. Go to the WordPress Admin panel's Appearance menu (left sidebar) 
1. Make your changes to the Theme Options, including Widgets, Sidebars, Menus (see note above about Menus), Background, Header and other Customizations for this alternate Theme (which will appear as if it were the Current Theme)
1. If you have more than one alternate Theme with Options you wish to change, repeat Steps 4-8 for each alternate Theme 
1. Deactivate the Theme Test Drive plugin 
1. Activate jonradio Multiple Themes
1. Changes to the Options for the Current Theme can now be made normally, just as you would without either plugin
1. Both the alternate and Current Themes should now display all Theme options properly when selected through the jonradio Multiple Themes plugin

= What happens when I change Permalinks? =

For entries you have created for individual Pages and Posts with this plugin, you can subsequently change your Permalink structure or you can change the Permalink of a Page or Post, without having to change the plugin's entry.  However, for other pages, such as Categories or Archives, you will have to delete your old entries and create new ones with this plugin.

= I added a new entry but why doesn't it appear in the list of entries? =

If you add an entry that already exists, it merely replaces the previous entry.

= How can I change the Theme for an entry? =

Simply add the entry again, with the new Theme.  It will replace the previous entry.

== Screenshots ==

1. Plugin's Admin Page when first installed
2. Plugin's Admin Page, adding entries

== Changelog ==

= 4 =
* Discovered url_to_postid() function, to address situations where Slug differed from Permalink, such as Posts with Year/Month folders

= 3.3.1 =
* Fix White Screen of Death on a Page selected by plugin

= 3.3 =
* Support Child Themes and any other situation where stylesheet and template names are not the same

= 3.2 =
* Correct Problem with P2 Theme, and its logged on verification at wp-admin/admin-ajax.php?p2ajax=true&action=logged_in_out&_loggedin={nonce}
* Add "Settings Saved" message to Admin page
* Tested with WordPress Version 3.5 beta

= 3.1 =
* Add Support for Prefixes, where all URLs beginning with the specified characters ("Prefix") can be assigned to a specified Theme

= 3.0 =
* Add Support for Categories and Archives when no Permalinks exist (support already existed Categories and Archives with Permalinks)
* Resolve several minor bugs

= 2.9 =
* Rewrite much of the Settings page and Plugin Directory documentation
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

= 4 =
Fix Posts not working in some Permalink setups, most notably Year/Month

= 3.3.1 =
Fix White Screen of Death on a Page, Post or other element selected by plugin

= 3.3 =
Remove Restriction that Stylesheet Name must match Template Name, which it does not with Child Themes 

= 3.2 =
Add Support for P2 Theme and provide "Settings Saved" message

= 3.1 =
Allow Prefix URLs to be used to specify where a Theme will be displayed

= 3.0 =
Improve support for Categories and Archives, and eliminate all known bugs.

= 2.9 =
Improve Settings fields, correct display of wrong Current Theme in Appearance-Themes Admin panel, and add IIS Support.

= 2.0 =
Selecting Individual Pages and Posts on a WordPress site installed in the root and using Permalinks now works correctly.

= 1.1 =
Eliminate possibility of foreach error message if PHP warning level is set at a high level

= 1.0 =
Beta version 0.9 had not been tested when installed from the WordPress Plugin Repository