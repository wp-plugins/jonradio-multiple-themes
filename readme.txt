=== jonradio Multiple Themes ===
Contributors: jonradio
Donate link: http://jonradio.com/plugins
Tags: themes, theme, sections, style, template, stylesheet, accessibility
Requires at least: 3.4.1
Tested up to: 3.6
Stable tag: 4.0.2
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

**Changing Theme Options (Widgets, Sidebars, Menus, Templates, Background, Header, etc.)?**:  See the FAQ (Frequently-Asked Questions) tab for important information on changing Options on Themes other than the Current Theme.

To select all Pages, Posts, Archives, Categories, etc. that begin with the same partial URL, a "Prefix URL" can be specified by selecting the Prefix checkbox when creating an entry on the Settings page for the jonradio Multiple Themes plugin.  Restriction:  the Prefix URL cannot contain all or part of a Query, which is the portion of a URL that begins with a question mark ("?").  Although the Prefix cannot contain a Query, URLs containing a Query will be matched by the Prefix.

If a Theme is not specified for a Page, Post, Site Home or other WordPress-displayed web page, such as Category or Archive display, the **Current Theme** specified in **Appearance-Themes** on the Admin panels will be used.

When selecting a Theme for Site Home or all or specific Pages, Posts or other non-Admin pages, the Theme must be shown in the list of Available Themes on the Appearance-Themes Admin panel. If necessary, install the Theme.  In a WordPress Network (AKA Multisite), Themes must be **Network Enabled** before they will appear as Available Themes on individual sites' Appearance-Themes panel.

I hesitate to use the term **Theme Switcher** to describe this plugin, because the term has so many meanings.  The jonradio Multiple Themes plugin does **not** alter the standard WordPress options that define what Theme is used on your WordPress site.  Instead, it dynamically (and selectively) overrides that choice.  Technical details aside, what this means is that deactivating or deleting the jonradio Multiple Themes plugin will instantly revert to the WordPress Current Theme that you have defined through the standard WordPress Appearance-Themes Admin panel.

== Installation ==

This section describes how to install the jonradio Multiple Themes plugin and get it working.

1. Use "Add Plugin" within the WordPress Admin panel to download and install this jonradio Multiple Themes plugin from the WordPress.org plugin repository (preferred method).  Or download and unzip this plugin, then upload the `/jonradio-multiple-themes/` directory to your WordPress web site's `/wp-content/plugins/` directory.
1. Activate the jonradio Multiple Themes plugin through the 'Plugins' menu in WordPress.  If you have a WordPress Network ("Multisite"), you can either Network Activate this plugin, or Activate it individually on the sites where you wish to use it.  Activating on individual sites within a Network avoids some of the confusion created by WordPress' hiding of Network Activated plugins on the Plugin menu of individual sites.
1. Be sure that all Themes you plan to use have been installed and are listed under Available Themes on the WordPress Appearance-Themes Admin panel. In a WordPress Network (AKA Multisite), Themes must be **Network Enabled** before they will appear as Available Themes on individual sites' Appearance-Themes panel.
1. Select Themes to be used on the Plugin's "Multiple Themes plugin" page in the WordPress Admin panels, which is found in both the **Appearance** and **Settings** sections.  You can also get to this page by clicking on the **Settings** link for this plugin on the **Installed Plugins** page.
1. If you need to change Theme Options (Widgets, Sidebars, Menus, Templates, Background, Header, etc.) for any Theme other than the Current Theme, see the FAQ (Frequently-Asked Questions) tab for important information.

== Frequently Asked Questions ==

= Will this plugin work with Paid Themes? =

Yes, in most cases.  But we have only tested the few Paid Themes whose authors have provided us with permission to use, without charge, their themes for test purposes.  Elegant, for example, allows us to accept copies of its Themes provided by its customers who require assistance with the jonradio Multiple Themes plugin.  On the other hand, some other Paid Theme authors have simply ignored our requests, despite our stated willingness to sign a non-disclosure agreement.

We do encourage you to contact us if you run into problems when using the jonradio Multiple Themes plugin with a Paid Theme, as the problem may not be unique to the Paid Theme.

To state the obvious, the cost of purchasing a license for all Paid Themes for testing purposes is prohibitive for an Open Source plugin such as this one.

= How do I change the Theme Options (Widgets, Sidebars, Menus, Background, Header, etc.) used for each Theme? =

**NOTE**:  See the next FAQ for information on Templates.

For the Current Theme, nothing changes when using the jonradio Multiple Themes plugin.  Options for the Current Theme, including Widgets, Sidebars, Menus, Background, Header and other Customizations supported by the Theme, can be modified in the Admin panel using the Appearance menu items on the left sidebar and the Customize link displayed beside the Current Theme on the Manage Themes tab of Appearance-Themes.

It is more difficult to modify Options for Active Themes that are *not* the Current Theme.  We hope to build this functionality into the jonradio Multiple Themes plugin in a future Version, but it is not clear just how practical that is, so the best that can be said is:  Maybe.

For now, there are two approaches.  Except for Widgets, the first approach is the most likely to give you success.  Menus, on the other hand, really work well with the first approach, and are severely restricted with the second method.

**WARNING**:  So far, we have not received any reports of, nor have we tested, using both Method #1 and #2 on the same WordPress site.  Use Caution if you plan to do so, as we cannot predict the results. 

**Method #1:**

Set the Theme Options with Live Preview.

Note:  Widgets cannot be placed using this Method.

1. Go to Appearance-Themes-Live Preview in the Admin panels.
1. Use the right sidebar to modify the Theme Options.  Note that "Navigation" will not appear until a Custom Menu has been defined in Appearance-Menus.  Navigation-Primary Navigation is where you would set the custom menu to be used for the Theme you are currently previewing.
1. Click the Save & Activate button.
1. Go immediately to Appearance-Themes to reactivate the Current Theme.

**Method #2:**

Use the Theme Test Drive plugin.

Note:  this approach only allows Menus to be set for one Theme.  Using this method to assign one or more menus to a Theme will unassign menus for all other Themes.

The jonradio Multiple Themes plugin (i.e. - this plugin) must be Deactivated, and the Theme Test Drive plugin installed and activated, so that each Theme can be selected with the Theme Test Drive plugin, allowing the Theme's Options to be set "as if" it were the Current Theme.

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

= How do I change the Template for a specific Page or Post? =

For the Current Theme, nothing changes when using the jonradio Multiple Themes plugin.  Select an alternate Template from the drop-down list in the Template field of the Page Attributes section of the Add New Page, Edit Page, Add New Post or Edit Post page of the Admin panels.  Or the Template field in Quick Edit.

It is more difficult to change Templates for Pages or Posts defined with the jonradio Multiple Themes plugin to use Active Themes that are *not* the Current Theme.  We hope to build this functionality into the jonradio Multiple Themes plugin in a future Version.

Use the Theme Test Drive plugin.  The jonradio Multiple Themes plugin (i.e. - this plugin) must be Deactivated, and the Theme Test Drive plugin installed and activated, so that each Theme can be selected with the Theme Test Drive plugin, allowing the Theme's Template to be set for each Page or Post using that Theme "as if" it were the Current Theme.

1. Deactivate jonradio Multiple Themes 
1. Install the Theme Test Drive plugin found at http://wordpress.org/extend/plugins/theme-test-drive/
1. Activate the Theme Test Drive plugin
1. Go to Appearance-Theme Test Drive 
1. In the Usage section, select an alternate Theme you will be using with jonradio Multiple Themes 
1. Push the Enable Theme Drive button at the bottom 
1. Go to the WordPress Admin panel's Page or Post menu (left sidebar) 
1. Make your changes to the Template field of each Page and/or Post that has been selected for this alternate Theme AND requires a non-default Template 
1. If you have more than one alternate Theme with Templates you wish to change, repeat Steps 4-8 for each alternate Theme 
1. Deactivate the Theme Test Drive plugin 
1. Activate jonradio Multiple Themes
1. Changes to the Templates for Pages and Posts using the Current Theme can now be made normally, just as you would without either plugin
1. Both the alternate and Current Themes should now display all Templates properly when selected through the jonradio Multiple Themes plugin

= How do I Edit a Theme? =

WordPress includes a built-in Theme Editor.  Select Editor in the Admin panel's Appearance menu items on the left sidebar.

By default, the style.css file of the Current Theme is displayed.  You can edit other Themes by selecting them in the "Select theme to edit" field and clicking the Select button.

Alternatively, you can edit any Theme on your own computer.  If your computer runs Windows, NotePad++ and FileZilla run very well together, using FileZilla's View/Edit feature to provide a Theme Editor with syntax highlighting and other advanced features.

If one or more of the Active Themes have their own Theme Editor or other type of Theme Options panels, such as Elegant's epanel, please read the next FAQ.

= How do I use Elegant's epanel? =

Nothing changes for the Current Theme.  epanel can be accessed just as it would be without the jonradio Multiple Themes plugin, simply by selecting the WordPress Admin panel's Appearance submenu item titled Theme Options preceded by the name of your Elegant Theme.

To make changes to other Active Themes that you will be specifying with the jonradio Multiple Themes plugin:

1. Deactivate jonradio Multiple Themes 
1. Install the Theme Test Drive plugin found at http://wordpress.org/extend/plugins/theme-test-drive/
1. Activate the Theme Test Drive plugin
1. Go to Appearance-Theme Test Drive 
1. In the Usage section, select an alternate Theme you will be using with jonradio Multiple Themes 
1. Push the Enable Theme Drive button at the bottom 
1. Click on the Appearance menu item on the left sidebar of the WordPress Admin panel to refresh the submenu
1. Click on the submenu item titled with your Elegant theme's name followed by "Theme Options"
1. Elegant's epanel will now appear
1. Make all the changes for this Theme, being sure to push the Save button
1. If you have more than one alternate Theme with Options you wish to change, repeat Steps 4-10 for each alternate Theme 
1. Deactivate the Theme Test Drive plugin 
1. Activate jonradio Multiple Themes
1. Changes to the Options for the Current Theme can now be made normally, just as you would without either plugin
1. Both the alternate and Current Themes should now display all Theme options properly when selected through the jonradio Multiple Themes plugin

Thanks to Elegant for allowing us to test copies of any of their Themes provided by their customers.

= What happens when I change Permalinks? =

For entries you have created for individual Pages and Posts with the jonradio Multiple Themes plugin, you can subsequently change your Permalink structure or you can change the Permalink of a Page or Post, without having to change the plugin's entry.  However, for other pages, such as Categories or Archives, you will have to delete your old entries and create new ones with the jonradio Multiple Themes plugin.

= I added a new entry but why doesn't it appear in the list of entries? =

If you add an entry that already exists, it merely replaces the previous entry.

= How can I change the Theme for an entry? =

Simply add the entry again, with the new Theme.  It will replace the previous entry.

== Screenshots ==

1. Plugin's Admin Page when first installed
2. Plugin's Admin Page, adding entries

== Changelog ==

= 4.0.2 =
* Prevent Warning and Notice by initializing global $wp

= 4.0.1 =
* Prevent Fatal Error by initializing global $wp_rewrite

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

= 4.0.2 =
Fix "Warning: in_array() expects parameter 2 to be array, null given in domain.com/wp-includes/rewrite.php on line 364"

= 4.0.1 =
Fix "Fatal error: Call to a member function wp_rewrite_rules() on a non-object in domain.com/wp-includes/rewrite.php on line 294"

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

