<?php
//	Exit if .php file accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Add Link to the plugin's entry on the Admin "Plugins" Page, for easy access
add_filter( 'plugin_action_links_' . jr_mt_plugin_basename(), 'jr_mt_plugin_action_links', 10, 1 );

/**
 * Creates Settings entry right on the Plugins Page entry.
 *
 * Helps the user understand where to go immediately upon Activation of the Plugin
 * by creating entries on the Plugins page, right beside Deactivate and Edit.
 *
 * @param	array	$links	Existing links for our Plugin, supplied by WordPress
 * @param	string	$file	Name of Plugin currently being processed
 * @return	string	$links	Updated set of links for our Plugin
 */
function jr_mt_plugin_action_links( $links ) {
	// The "page=" query string value must be equal to the slug
	// of the Settings admin page.
	array_push( $links, '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=jr_mt_settings' . '">Settings</a>' );
	return $links;
}


//	Admin Page

add_action( 'admin_menu', 'jr_mt_admin_hook' );
//	Runs just before admin_init (below)

/**
 * Add Admin Menu item for plugin
 * 
 * Plugin needs its own Page in the Settings section of the Admin menu.
 *
 */
function jr_mt_admin_hook() {
	//  Add Settings Page for this Plugin
	global $jr_mt_plugin_data;
	add_theme_page( $jr_mt_plugin_data['Name'], 'Multiple Themes plugin', 'switch_themes', 'jr_mt_settings', 'jr_mt_settings_page' );
	add_options_page( $jr_mt_plugin_data['Name'], 'Multiple Themes plugin', 'switch_themes', 'jr_mt_settings', 'jr_mt_settings_page' );
	/*	Cleanup Settings before using them.
		Presently, this just checks for Themes that are no longer installed,
		but are referenced in the Settings.
		Settings referencing such Themes are deleted.
	*/
	require_once( jr_mt_path() . 'includes/settings-cleanup.php' );
}

global $jr_mt_kwvalsep;
/*	Everything is converted to lower-case, so upper-case letter makes a good keyword-value separator
*/
$jr_mt_kwvalsep = 'A';

add_action( 'admin_enqueue_scripts', 'jr_mt_admin_enqueue_scripts' );
function jr_mt_admin_enqueue_scripts() {
	global $jr_mt_plugin_data;
	wp_enqueue_script( 'jr_mt_tabs', plugins_url() . '/' . dirname( jr_mt_plugin_basename() ) . '/js/tabs.js', array(), $jr_mt_plugin_data['Version'] );
}

/**
 * Settings page for plugin
 * 
 * Display and Process Settings page for this plugin.
 *
 */
function jr_mt_settings_page() {
	global $jr_mt_plugin_data, $jr_mt_plugins_cache;
	$jr_mt_plugins_cache = get_plugins();
	add_thickbox();
	echo '<div class="wrap">';
	echo '<h2>' . $jr_mt_plugin_data['Name'] . '</h2>';
	
	//	Required because it is only called automatically for Admin Pages in the Settings section
	settings_errors( 'jr_mt_settings' );
	
	$theme_obj = wp_get_theme();
	$theme = $theme_obj->Name;
	$theme_version = $theme_obj->Version;
	global $jr_mt_options_cache;

	$current_wp_version = get_bloginfo( 'version' );
	
	global $jr_mt_plugins_cache;
	
	$compatible = TRUE;
	
	//	Check for incompatible plugins that have been activated:  BuddyPress and Theme Test Drive
	global $jr_mt_incompat_plugins;
	foreach ( $jr_mt_plugins_cache as $rel_path => $plugin_data ) {
		if ( in_array( $plugin_data['Name'], $jr_mt_incompat_plugins ) && is_plugin_active( $rel_path ) ) {
			if ( $compatible ) {
				echo '<h3>Plugin Conflict Error Detected</h3>';
				$compatible = FALSE;
			}
			echo '<p>This Plugin (' . $jr_mt_plugin_data['Name'] . ') cannot be used when the <b>' . $plugin_data['Name'] 
				. '</b> plugin is Activated.  If you wish to use the ' . $jr_mt_plugin_data['Name'] 
				. ' plugin, please deactivate the '  . $plugin_data['Name'] 
				. ' plugin (not just when viewing this Settings page, but whenever the ' 
				. $jr_mt_plugin_data['Name'] . ' plugin is activated).</p>';
		}
	}
	
	if ( $compatible ) {
		$settings = get_option( 'jr_mt_settings' );
		$internal_settings = get_option( 'jr_mt_internal_settings' );
		?>
		<h2 class="nav-tab-wrapper">
		<a href="#" class="nav-tab nav-tab-active" id="jr-mt-tabs1"
		onClick="jrMtTabs( 1, 5 );">Settings</a><a href="#" class="nav-tab" id="jr-mt-tabs2"
		onClick="jrMtTabs( 2, 5 );">Advanced Settings</a><a href="#" class="nav-tab" id="jr-mt-tabs3"
		onClick="jrMtTabs( 3, 5 );">Theme Options</a><a href="#" class="nav-tab" id="jr-mt-tabs4"
		onClick="jrMtTabs( 4, 5 );">System Information</a><a href="#" class="nav-tab" id="jr-mt-tabs5"
		onClick="jrMtTabs( 5, 5 );">New in V5</a>
		</h2>
		<div id="jr-mt-settings1">
		<h3>Settings</h3>
		<p>
		This is the main Settings tab.
		Additional Settings are available on the
		<a href="#" onClick="jrMtTabs( 2, 5 );">Advanced Settings tab</a>,
		but they can cause problems,
		so should be used with care.
		</p>
		<h3>Overview</h3>
		<p>This Plugin allows you to selectively display Themes on your web site
		other than the Theme shown as
		<b>
		Active
		</b>
		on
		<b>
		Appearance-Themes
		</b>
		in the WordPress Admin panels.
		</p>
		<p>
		Below,
		Theme Selection entries can be created
		where each Entry specifies which of the installed themes shown on the Appearance-Themes Admin panel will be applied to:
		<ul>
		<li> &raquo; The Site Home</li>
		<li> &raquo; An exact URL of any non-Admin page on this WordPress Site</li>
		<li> &raquo; One or more URLs that begin with the partial URL you specify ("URL Prefix")</li>
		<li> &raquo; One or more URLs that begin with the wildcard URL you specify ("URL Prefix*")</li>
		<li> &raquo; Any URL containing a Specific Query Keyword (<code>?keyword</code> or <code>&keyword</code>)</li>
		<li> &raquo; Any URL containing a Specific Query Keyword/Value pair (<code>?keyword=value</code> or <code>&keyword=value</code>)</li>
		<li> &raquo; For the same site visitor, all non-Admin pages after a <b>Sticky</b> Query Keyword/Value pair is specified in any URL (Advanced Settings tab)</li>
		<li> &raquo; All Pages (Advanced Settings tab)</li>
		<li> &raquo; All Posts (Advanced Settings tab)</li>
		<li> &raquo; Everything else, except what is specified above (Advanced Settings tab)</li>
		</ul>
		</p>
		<h3>Important Notes</h3>
		<form action="options.php" method="POST">
		<?php
		$permalink = get_option( 'permalink_structure' );
		if ( isset( $internal_settings['permalink'] ) ) {
			if ( $internal_settings['permalink'] !== $permalink ) {
				/*	Permalink Structure has been changed.
				*/
				if ( empty( $settings['url'] ) && empty( $settings['url_prefix'] ) && empty( $settings['url_asterisk'] ) ) {
					$update = TRUE;
				} else {
					?>
					<p>
					Permalink Structure has been changed.
					In the
					<b>
					Current Theme Selection Entries
					</b>
					Section just below,
					please review all
					URL=,
					URL Prefix=
					and
					URL Prefix*=
					entries,
					as they may need to be changed to reflect the new Permalink Structure.
					<br />
					<input type="checkbox" id="permalink" name="jr_mt_settings[permalink]" value="true" />
					Dismiss Warning
					</p>
					<?php
					$update = FALSE;
				}
			} else {
				$update = FALSE;
			}
		} else {
			/*	Permalink Internal Setting for Plugin not set,
				so initialize it to current Permalink Structure.
			*/
			$update = TRUE;
		}
		if ( $update ) {
			$internal_settings['permalink'] = $permalink;
			update_option( 'jr_mt_internal_settings', $internal_settings );
		}
		if ( function_exists('is_multisite') && is_multisite() ) {
			echo "In a WordPress Network (AKA Multisite), Themes must be <b>Network Enabled</b> before they will appear as Available Themes on individual sites' Appearance-Themes panel.";
		}
		echo '<p>';
		echo "The Active Theme, defined to WordPress in the Appearance-Themes admin panel, is <b>$theme</b>.";
		if ( trim( $settings['current'] ) ) {
			echo " But it is being overridden by the Theme for Everything setting (see Advanced Settings tab), which set the plugin's default Theme to <b>";
			echo wp_get_theme( $settings['current'] )->Name;
			echo '</b>. You will not normally need to specify this default Theme in any of the other Settings on this page, though you will need to specify the WordPress Active Theme wherever you want it to appear. Or, if you specify, on the Advanced Settings tab, a different Theme for All Pages, All Posts or Everything, and wish to use the default Theme for one or more specific Pages, Posts or other non-Admin pages.';
		} else {
			echo ' You will not normally need to specify it in any of the Settings on this page. The only exception would be if you specify, on the Advanced Settings tab, a different Theme for All Pages, All Posts or Everything, and wish to use the Active Theme for one or more specific Pages, Posts or other non-Admin pages.';
		}
		echo '</p>';

		if ( jr_mt_plugin_update_available() ) {
			echo '<p>A new version of this Plugin (' . $jr_mt_plugin_data['Name'] . ') is available from the WordPress Repository.'
				. ' Updating as quickly as possible is strongly recommend because new versions fix problems that users like you have already reported.'
				. ' <a class="thickbox" title="' . $jr_mt_plugin_data['Name'] . '" href="' . network_admin_url()
				. 'plugin-install.php?tab=plugin-information&plugin=' . $jr_mt_plugin_data['slug']
				. '&section=changelog&TB_iframe=true&width=640&height=768">Click here</a> for more details.</p>';
		}
		?>
		<p>
		If a newly-added Theme Selection does not seem to be working, 
		especially if the associated web page does not display properly, 
		try deactivating any plugins that provide Caching. 
		You may find that you have to flush the plugin's Cache whenever you add or change a Theme Selection setting. 
		Also note that some Caching plugins only cache for visitors who are not logged in, 
		so be sure to check your site after logging out.
		</p>
		<p>
		Need more help?
		Please scroll to the bottom of this Settings page for more information.
		</p>
		<hr />
		<?php
		
		//	Plugin Settings are displayed and entered here:
		settings_fields( 'jr_mt_settings' );
		do_settings_sections( 'jr_mt_settings_page' );
		echo '<p><input name="save" type="submit" value="Save All Changes" class="button-primary" /></p></form>';
	}

	?>
	</div>
	<div id="jr-mt-settings3" style="display: none;">
	<h3>
	Theme Options and Template Selection
	</h3>
	<p>
	This tab provides information on changing Theme Options
	(Widgets, Sidebars, Menus, Background, Header, etc.) 
	for all the different Themes used on a WordPress site.
	</p>
	<p>	
	Information on changing the Template for each Page or Post
	is found near the bottom of this tab.
	</p>
	<h3>
	Changing Theme Options
	</h3>
	<p>
	For the Active Theme, nothing changes when using the jonradio Multiple Themes plugin.
	Options for the Active Theme, 
	including Widgets, Sidebars, Menus, Background, Header and other Customizations supported by the Theme, 
	can be modified in the Admin panel using the Appearance menu items on the left sidebar.
	Some Themes also provide their own menu items in the left sidebar of the Admin panel,
	and these will still appear for the Active Theme when using this plugin.
	</p>
	<p>	
	It is more difficult to modify Options for installed Themes that are not the WordPress Active Theme.
	Building this functionality into this plugin is in the plans for a future Version, 
	but it is not clear just how practical that is, so the best that can be said is:
	<i>
	Maybe</i>.
	</p>
	<p>	
	For now, there are three approaches that can be used to change Options for an installed Theme that is not the Active Theme.
	The first works best if only one Theme has a lot of Options that need to be changed frequently:
	</p>
	<ol>
	<li>
	Make that Theme the Active Theme defined in the Appearance-Themes WordPress admin panel;
	</li>
	<li>
	If that meant changing the Active Theme,
	the previous Active Theme can be selected on the plugin's
	<b>
	Advanced Settings
	</b>
	tab
	in the
	<b>
	Select Theme for Everything
	</b>
	field 
	and it will be used everywhere except where you have specified
	another Theme in the Theme Selection entries for this plugin.
	</li>
	</ol>
	<p>
	For other situations,
	two multi-step Methods are available,
	and are described in the two Sections below.
	Both Methods work for most Theme Options,
	with the following exceptions:
	</p>
	<ol>
	<li>
	Menus really work well with Method #1, 
	but are severely restricted with Method #2;
	</li>
	<li>
	Widgets normally only work with Method #2;
	</li>
	<li>
	Using both Methods may cause conflicts;
	</li>
	<li>
	No matter which Method you choose,
	you may lose previously-set Theme Options.
	A Backup and Recovery of your WordPress Database
	would be required to avoid such a loss.
	</li>
	</ol>
	<h4>
	<u>
	Method #1</u>:
	Set the Theme Options with Live Preview.
	</h4>
	<p>
	Note: Widgets cannot be placed using this Method.
	</p>
	<ol>
	<li>
    Go to Appearance-Themes in the WordPress Admin panels.
	</li>
	<li>
	Mouse over the Theme that you wish to change
	and click the Live Preview button that appears.
	</li>
	<li>
    Use the left sidebar to modify the Theme Options. 
	Note that
	<b>
	Navigation
	</b>
	will not appear in the Live Preview sidebar until a Menu has been defined in Appearance-Menus. 
	Navigation is where you would set the custom menu(s) to be used for the Theme you are currently previewing.
	</li>
	<li>
    Click the Save & Activate button.
	</li>
	<li>
    Go immediately to Appearance-Themes in the WordPress Admin panels.
	</li>
	<li>
	Mouse over the Theme that had previously been the Active Theme
	and click the Activate button that appears
	to reactivate the Active Theme.
	</li>
	</ol>
	<h4>
	<u>
	Method #2</u>:
	Use the Theme Test Drive plugin.
	</h4>
	<p>
	Note: this approach only allows Menus to be set for one Theme. Using this method to assign one or more menus to a Theme will unassign menus for all other Themes.
	</p>
	<p>
	The jonradio Multiple Themes plugin (i.e. - this plugin) must be Deactivated, 
	and the Theme Test Drive plugin installed and activated.
	This enables each Theme to be selected with the Theme Test Drive plugin, 
	allowing the Theme's Options to be set 
	<i>
	as if
	</i>
	it were the Active Theme.
	</p>
	<ol>
	<li>
    Deactivate the jonradio Multiple Themes plugin.
	</li>
    <li>
	Install the Theme Test Drive plugin found at
	<a target="_blank" href="http://wordpress.org/plugins/theme-test-drive/">http://wordpress.org/plugins/theme-test-drive/</a>.
	</li>
	<li>
    Activate the Theme Test Drive plugin.
	</li>
	<li>
    Go to 
	<b>
	Appearance-Theme Test Drive
	</b>
	in the WordPress Admin panels.
	</li>
	<li>
    In the Usage section, select a Theme whose Options you wish to change.
	</li>
	<li>
    Push the Enable Theme Drive button at the bottom of the Admin panel.
	</li>
	<li>
	Make your changes to the Theme Options, including Widgets, Sidebars, Menus (see note above about Menus), Background, Header and other Customizations for this alternate Theme
	using the Appearance submenu
	in the WordPress Admin panels,
	just as you would for the Active Theme.
	</li>
	<li>
    If more than one Theme has Options that need changing, repeat Steps 4-8 for each Theme
	(except the Active Theme,
	which should be only changed
	<i>
	without
	</i>
	the Theme Test Drive plugin activated). 
	</li>
	<li>
    Deactivate the Theme Test Drive plugin.
	</li>
	<li>
    Activate this plugin (jonradio Multiple Themes).
	</li>
	<li>
    Changes to the Options for the Active Theme can now be made normally, just as you would without either plugin.
	</li>
	<li>
    Both the alternate and Active Themes should now display all Theme options properly when selected through the jonradio Multiple Themes plugin.
	</li>
	</ol>
	<h3>
	Changing Templates
	</h3>	
	<p>
	Many Themes provide more than one Template.
	For each Page or Post, you can select the Template you want to use for that Page or Post.
	</p>
	<p>	
	For the Active Theme, nothing changes when using the jonradio Multiple Themes plugin.
	Select an alternate Template from the drop-down list in the Template field of the Page Attributes section of the Add New Page, Edit Page, Add New Post or Edit Post page of the Admin panels.
	Or the Template field in Quick Edit.
	</p>
	<p>
	It is more difficult to change Templates for Pages or Posts defined with the jonradio Multiple Themes plugin to use Installed Themes that are not the Active Theme.
	Building this functionality into this plugin is in the plans for a future Version.
	</p>
	<p>
	Use the Theme Test Drive plugin. 
	The jonradio Multiple Themes plugin (i.e. - this plugin) must be Deactivated, and the Theme Test Drive plugin installed and activated, 
	so that each Theme can be selected with the Theme Test Drive plugin, 
	allowing the Theme's Template to be set for each Page or Post using that Theme 
	<i>
	as if
	</i>
	it were the Active Theme.
	</p>
	<ol>
	<li>
    Deactivate the jonradio Multiple Themes plugin.
	</li>
    <li>
	Install the Theme Test Drive plugin found at
	<a target="_blank" href="http://wordpress.org/plugins/theme-test-drive/">http://wordpress.org/plugins/theme-test-drive/</a>.
	</li>
	<li>
    Activate the Theme Test Drive plugin.
	</li>
	<li>
    Go to 
	<b>
	Appearance-Theme Test Drive
	</b>
	in the WordPress Admin panels.
	</li>
	<li>
    In the Usage section, select a Theme whose Templates need to be changed for a Post or Page.
	</li>
	<li>
    Push the Enable Theme Drive button at the bottom of the Admin panel.
	</li>
	<li>
	Go to Posts-All Posts or Pages-All Pages in the WordPress Admin panels.
	</li>
	<li>
	For each Page or Post where a Template needs to be changed for this Theme,
	mouse over the Page or Post title and click on Quick Edit.
	</li>
	<li>
	Change the Template field.
	</li>
	<li>
	Click the Update button.
	</li>
	<li>
	Repeat Steps 8-10 for each Page or Post that requires a change to Template for this Theme.
	</li>
	<li>
    If more than one Theme has Pages or Posts with Templates that need to be changed,
	repeat Steps 4-11 for each Theme
	(except the Active Theme,
	where Template changes should only be made
	<i>
	without
	</i>
	the Theme Test Drive plugin activated). 
	</li>
	<li>
    Deactivate the Theme Test Drive plugin.
	</li>
	<li>
    Activate this plugin (jonradio Multiple Themes).
	</li>
	<li>
    Changing Templates for the Active Theme can now be made normally, just as you would without either plugin.
	</li>
	<li>
    Both the alternate and Active Themes should now display the correct Template when selected through the jonradio Multiple Themes plugin.
	</li>
	</ol>
	</div>
	<div id="jr-mt-settings4" style="display: none;">
	<h3>
	System Information
	</h3>
	<?php
	$posix = function_exists( 'posix_uname' );
	echo '<p>You are currently running:<ul>';
	echo "<li> &raquo; The {$jr_mt_plugin_data['Name']} plugin Version {$jr_mt_plugin_data['Version']}</li>";
	echo "<li> &nbsp; &raquo;&raquo; The Path to the plugin's directory is <code>" . rtrim( jr_mt_path(), '/' ) . '</code></li>';
	echo "<li> &nbsp; &raquo;&raquo; The URL to the plugin's directory is <code>" . plugins_url() . "/{$jr_mt_plugin_data['slug']}</code></li>";
	echo "<li> &raquo; The Active Theme is $theme Version $theme_version</li>";
	echo "<li> &nbsp; &raquo;&raquo; The Path to the Active Theme's stylesheet directory is <code>" . get_stylesheet_directory() . '</code></li>';
	echo "<li> &nbsp; &raquo;&raquo; The Path to the Active Theme's template directory is <code>" . get_template_directory() . '</code></li>';
	$permalink = get_option( 'permalink_structure' );
	if ( empty( $permalink ) ) {
		$permalink = 'Default (Query <code>/?p=123</code>)';
	} else {
		$permalink = "<code>$permalink</code>";
	}
	echo "<li> &raquo; The current Permalink Structure is $permalink";
	echo "<li> &raquo; WordPress Version $current_wp_version</li>";
	echo '<li> &nbsp; &raquo;&raquo; WordPress language is set to ' , get_bloginfo( 'language' ) . '</li>';
	echo '<li> &raquo; ' . php_uname( 's' ) . ' operating system, Release/Version ' . php_uname( 'r' ) . ' / ' . php_uname( 'v' ) . '</li>';
	if ( $posix ) {
		$array = posix_getpwuid( posix_getuid() );
		$user = $array['name'];
		echo "<li> &raquo; Real operating system User ID that runs WordPress is $user</li>";
		$array = posix_getpwuid( posix_geteuid() );
		$user = $array['name'];
		echo "<li> &raquo; Effective operating system User ID that runs WordPress is $user</li>";
	}
	echo '<li> &raquo; ' . php_uname( 'm' ) . ' computer hardware</li>';
	echo '<li> &raquo; Host name ' . php_uname( 'n' ) . '</li>';
	echo '<li> &raquo; php Version ' . phpversion() . '</li>';
	echo '<li> &nbsp; &raquo;&raquo; php memory_limit ' . ini_get('memory_limit') . '</li>';
	if ( !$posix ) {
		echo '<li> &nbsp; &raquo;&raquo; POSIX functions are not available</li>';
	}
	echo '<li> &raquo; Zend engine Version ' . zend_version() . '</li>';
	echo '<li> &raquo; Web Server software is ' . getenv( 'SERVER_SOFTWARE' ) . '</li>';
	if ( function_exists( 'apache_get_version' ) && ( FALSE !== $apache = apache_get_version() ) ) {
		echo "<li> &nbsp; &raquo;&raquo; Apache Version $apache</li>";
	}
	global $wpdb;
	echo '<li> &raquo; MySQL Version ' . $wpdb->get_var( 'SELECT VERSION();', 0, 0 ) . '</li>';

	echo '</ul></p>';
	
	$paths = array(
		'/..',
		'/',
		'/wp-content/',
		'/wp-content/plugins/',
		'/wp-content/plugins/' . dirname( jr_mt_plugin_basename() ),
		'/wp-content/plugins/' . dirname( jr_mt_plugin_basename() ) . '/readme.txt'
	);
	echo '<h3>File Permissions</h3><p>All of the Paths shown below are relative to the WordPress Site Path <code>'
		. ABSPATH
		. '</code><br />The first is the Parent Directory <code>'
		. dirname( ABSPATH )
		. '/</code> and the second is the WordPress Site Path itself.</p><table class="widefat"><thead><tr><th>Path</th><th>Type</th><th>Read</th><th>Write</th>';
	if ( $posix ) {
		echo '<th>Owner</th><th>Group</th>';
	}
	echo '</tr></thead><tbody>';
	foreach ( $paths as $path ) {
		$full_path = ABSPATH . substr( $path, 1 );
		if ( is_dir( $full_path ) ) {
			$type = 'Directory';
		} else {
			$type = 'File';
		}
		if ( is_readable( $full_path ) ) {
			$read = 'Yes';
		} else {
			$read = 'No';
		}
		if ( is_writeable( $full_path ) ) {
			$write = 'Yes';
		} else {
			$write = 'No';
		}
		if ( $posix ) {
			if ( FALSE === ( $uid = fileowner( $full_path ) ) ) {
				$user = '-';
				$group = '-';
			} else {
				$array = posix_getpwuid( $uid );
				$user = $array['name'];
				$array = posix_getgrgid( filegroup( $full_path ) );
				$group = $array['name'];
			}
		}
		echo "<tr><td>$path</td><td>$type</td><td>$read</td><td>$write</td>";
		if ( $posix ) {
			echo "<td>$user</td><td>$group</td>";
		}
		echo '<tr>';
	}
	echo '</tbody></table>';
	?>
	</div>
	<div id="jr-mt-settings5" style="display: none;">
	<p>
	It is possible to downgrade from Version 5 to Version 4.12,
	which is included with this plugin,
	in <code>/wp-content/plugins/jonradio-multiple-themes/downgrade/jonradio-multiple-themes.4.12.zip</code>.
	</p>
	<h3>
	What's New in Version 5?
	</h3>
	<p>
	Version 5.0 of the jonradio Multiple Themes plugin
	saw a major rewrite of the Theme Selection logic,
	the first since the plugin was originally written,
	and the addition of Tabs to the Settings page,
	for easier navigation.
	</p>
	<p>
	Major improvements include:
	</p>
	<ol>
	<li>
	Greatly expanded compatibility with other Plugins and Themes;
	</li>
	<li>
	Allow <code>?keyword=value&keyword=value</code> Queries
	in URL, URL Prefix and URL Prefix with Asterisk ("*")
	Theme Selection entries;
	</li>
	<li>
	Accurate Theme Selection even for plugins and themes that request
	Stylesheet or Template information before WordPress is fully loaded;
	</li>
	<li>
	Tabs for the Setting page written in JavaScript
	for instant switching between tabs
	and preservation of input data,
	e.g. - switch between Settings and Advanced Settings tabs
	without having to retype your changes when you switch back;
	</li>
	<li>
	Plugin's Theme Selection entries, if any,
	displayed in the order in which they are processed,
	to clarify which Theme will be displayed for any given URL;
	</li>
	<li>
	Complete How-To details on
	Theme Option and Template selection
	right on the Settings page,
	replacing the FAQs
	in the WordPress Plugin Directory;
	</li>
	<li>
	Automatic deletion of Theme Selection entries
	for Themes that have been deleted;
	</li>
	<li>
	Enhanced performance with tighter code,
	and less of it,
	on the public side of your WordPress site.
	</li>	
	</ol>
	<p>
	The only disadvantages of the new Theme Selection methods used in Version 5 are:
	</p>
	<ol>
	<li>
	Changing Permalinks invalidates more Theme Selection settings than in previous versions;
	</li>
	<li>
	Changing the Theme for an Entry
	requires Deletion of the old Entry
	before or after
	adding the same Entry
	with the new Theme specified;
	</li>
	<li>
	The new Theme Selection logic,
	based on URL Matching rather than Page, Post, Attachment, Category and Archive IDs,
	requires a conversion of some Settings from prior versions of the plugin.
	This conversion to the new format occurs automatically
	the first time that Version 5 runs.
	Old format settings are retained,
	transparently without being displayed on the Settings page,
	to allow downgrading to Version 4 from Version 5.
	</li>
	</ol>
	</div>
	<hr />
	<h3>
	Need Help?
	</h3>
	<p>
	Need help with this plugin?
	Check the
	<a href="#" onClick="jrMtTabs( 3, 5 );">Theme Options</a>
	and
	<a href="#" onClick="jrMtTabs( 5, 5 );">New in V5</a>
	tabs above,
	and the
	<a target="_blank" href="http://wordpress.org/plugins/jonradio-multiple-themes/">Description</a>, 
	<a target="_blank" href="http://wordpress.org/plugins/jonradio-multiple-themes/faq/">FAQ</a>, 
	<a target="_blank" href="http://wordpress.org/plugins/jonradio-multiple-themes/installation/">Installation</a>
	and 
	<a target="_blank" href="http://wordpress.org/support/plugin/jonradio-multiple-themes">Support</a>
	tabs
	in the <a target="_blank" href="http://wordpress.org/plugins/jonradio-multiple-themes/">WordPress Directory entry for this plugin</a>.
	All of this information is based on the many support questions jonradio has answered both on-line and via e-mail since this plugin was first released in 2012.
	</p>
	<p>
	Please be sure to check them all out if you have any unanswered questions.
	If you cannot find the answers to all of your questions there,
	simply post your question in the
	<a target="_blank" href="http://wordpress.org/support/plugin/jonradio-multiple-themes">Support Forum</a>
	or
	<a target="_blank" href="http://jonradio.com/contact-us/">contact jonradio directly</a>
	(be sure to mention which plugin your question pertains to).
	</p>
	<p>
	For information on other jonradio plugins,
	including Contact and Donation information,
	<a target="_blank" href="http://jonradio.com/plugins/">click here</a>.
	</p>
	<h3>
	Want to Help?
	</h3>
	<p>
	As well as <a target="_blank" href="http://jonradio.com/plugins/">Donations</a>,
	you can also help by 
	<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/jonradio-multiple-themes">Reviewing this plugin</a> 
	for the WordPress Plugin Directory,
	and telling other people that it works for your particular combination of Plugin version and WordPress version
	in the Compability section of the
	<a target="_blank" href="http://wordpress.org/plugins/jonradio-multiple-themes/">WordPress Directory entry for this plugin</a>.
	</p>
	<?php
}

add_action( 'admin_init', 'jr_mt_admin_init' );

/**
 * Register and define the settings
 * 
 * Everything to be stored and/or can be set by the user
 *
 */
function jr_mt_admin_init() {
	register_setting( 'jr_mt_settings', 'jr_mt_settings', 'jr_mt_validate_settings' );
	$settings = get_option( 'jr_mt_settings' );
	foreach ( array( 'query', 'url', 'url_prefix', 'url_asterisk' ) as $key ) {
		if ( !empty( $settings[ $key ] ) ) {
			$found = TRUE;
		}
	}
	if ( isset( $found ) ) {
		add_settings_section(
			'jr_mt_delete_settings_section', 
			'Current Theme Selection Entries', 
			'jr_mt_delete_settings_expl', 
			'jr_mt_settings_page' 
		);
		add_settings_field(
			'del_entry', 
			'Theme Selection Entries:', 
			'jr_mt_echo_delete_entry', 
			'jr_mt_settings_page', 
			'jr_mt_delete_settings_section'
		);
	}
	add_settings_section( 
		'jr_mt_site_home_section',
		'Site Home',
		'jr_mt_site_home_expl',
		'jr_mt_settings_page' 
	);
	add_settings_field( 
		'site_home', 
		'Select Theme for Site Home<br /><code>' . home_url() . '</code>', 
		'jr_mt_echo_site_home', 
		'jr_mt_settings_page', 
		'jr_mt_site_home_section' 
	);
	add_settings_section(
		'jr_mt_single_settings_section', 
		'<input name="save" type="submit" value="Save All Changes" class="button-primary" /></h3><h3>For An Individual Page, Post or other non-Admin page;<br />or a group of pages, specified by URL Prefix, optionally with Asterisk(s)', 
		'jr_mt_single_settings_expl', 
		'jr_mt_settings_page'
	);
	add_settings_field( 'add_is_prefix', 'Select here if URL is a Prefix', 'jr_mt_echo_add_is_prefix', 'jr_mt_settings_page', 'jr_mt_single_settings_section' );
	add_settings_field( 'add_theme', 'Theme', 'jr_mt_echo_add_theme', 'jr_mt_settings_page', 'jr_mt_single_settings_section' );
	add_settings_field( 'add_path_id', 'URL of Page, Post, Prefix or other', 'jr_mt_echo_add_path_id', 'jr_mt_settings_page', 'jr_mt_single_settings_section' );
	add_settings_section( 'jr_mt_querykw_section', 
		'For A Query Keyword on any Page, Post or other non-Admin page', 
		'jr_mt_querykw_expl', 
		'jr_mt_settings_page' 
	);
	add_settings_field( 'add_querykw_theme', 'Theme', 'jr_mt_echo_add_querykw_theme', 'jr_mt_settings_page', 'jr_mt_querykw_section' );
	add_settings_field( 'add_querykw_keyword', 'Query Keyword', 'jr_mt_echo_add_querykw_keyword', 'jr_mt_settings_page', 'jr_mt_querykw_section' );
	add_settings_section( 'jr_mt_query_section', 
		'For A Query Keyword=Value on any Page, Post or other non-Admin page', 
		'jr_mt_query_expl', 
		'jr_mt_settings_page'
	);
	add_settings_field( 'add_query_theme', 'Theme', 'jr_mt_echo_add_query_theme', 'jr_mt_settings_page', 'jr_mt_query_section' );
	add_settings_field( 'add_query_keyword', 'Query Keyword', 'jr_mt_echo_add_query_keyword', 'jr_mt_settings_page', 'jr_mt_query_section' );
	add_settings_field( 'add_query_value', 'Query Value', 'jr_mt_echo_add_query_value', 'jr_mt_settings_page', 'jr_mt_query_section' );
	add_settings_section( 'jr_mt_sticky_section', 
		'<input name="save" type="submit" value="Save All Changes" class="button-primary" /></h3></div><div id="jr-mt-settings2" style="display: none;"><h3>Advanced Settings</h3><p><b>Warning:</b> As the name of this section implies, Advanced Settings should be fully understood or they may surprise you with unintended consequences, so please be careful.</p><h3>Sticky and Override', 
		'jr_mt_sticky_expl', 
		'jr_mt_settings_page' 
	);
	add_settings_field( 'query_present', 'When to add Sticky Query to a URL', 'jr_mt_echo_query_present', 'jr_mt_settings_page', 'jr_mt_sticky_section' );
	add_settings_field( 'sticky_query', 'Keyword=Value Entries:', 'jr_mt_echo_sticky_query_entry', 'jr_mt_settings_page', 'jr_mt_sticky_section' );
	add_settings_section( 'jr_mt_everything_section',
		'Theme for Everything',
		'jr_mt_everything_expl', 
		'jr_mt_settings_page'
	);
	add_settings_field( 'current', 
		'Select Theme for Everything, to Override WordPress Current Theme (<b>' . wp_get_theme()->Name . '</b>)', 
		'jr_mt_echo_current', 
		'jr_mt_settings_page', 
		'jr_mt_everything_section' 
	);
	add_settings_section( 'jr_mt_all_settings_section', 
		'For All Pages and/or All Posts', 
		'jr_mt_all_settings_expl', 
		'jr_mt_settings_page' 
	);
	$suffix = array(
		'Pages' => '<br />(Pages created with Add Page)',
		'Posts' => ''
	);
	foreach ( array( 'Pages', 'Posts' ) as $thing ) {
		add_settings_field( 'all_' . strtolower( $thing ), "Select Theme for All $thing" . $suffix[$thing], 'jr_mt_echo_all_things', 'jr_mt_settings_page', 'jr_mt_all_settings_section', 
			array( 'thing' => $thing ) );
	}
}

/**
 * Section text for Section1
 * 
 * Display an explanation of this Section
 *
 */
function jr_mt_delete_settings_expl() {
	?>
	<p>
	All Theme Selection entries are displayed below,
	in the exact order in which they will be processed.
	For example,
	if a match is made with the first Entry,
	the first Entry's Theme will be used,
	no matter what Theme the Second and subsequent Entries specify.
	</p>
	<p>
	You can delete any of these entries by filling in the check box beside the entry
	and clicking any of the <b>Save All Changes</b> buttons.
	To change the Theme for an entry,
	you will need to delete the entry
	and add the same entry with a different Theme in the relevant section
	on this or the Advanced Settings tab.
	</p>
	<p>
	To add or remove (or to learn about) the Sticky or Override setting for a Query,
	see the Advanced Settings tab.
	</p>
	<?php
}

function jr_mt_echo_delete_entry() {
	echo 'In order of Selection:<ol>';
	$settings = get_option( 'jr_mt_settings' );
	/*	Display any Override entries first,
		because they have the highest priority.
	*/
	foreach ( $settings['override']['query'] as $override_keyword => $override_value_array ) {
		foreach ( $override_value_array as $override_value => $bool ) {
			jr_mt_theme_entry( 
				'Query',
				wp_get_theme( $settings['query'][ $override_keyword ][ $override_value ] )->Name,
				$override_keyword,
				$override_value
			);
		}
	}
	/*	Display Non-Overrides:
		first, keyword=value query in URL with matching setting entry.
	*/
	foreach ( $settings['query'] as $keyword => $value_array ) {
		foreach ( $value_array as $value => $theme ) {
			/*	Wildcard Keyword=* entries come later
			*/
			if ( '*' !== $value ) {
				if ( !isset( $settings['override']['query'][ $keyword ][ $value ] ) ) {
					jr_mt_theme_entry(
						'Query',
						wp_get_theme( $theme )->Name,
						$keyword,
						$value
					);
				}
			}
		}
	}
	/*	Display Non-Overrides:
		second, wildcard keyword=* query in URL with matching setting entry.
	*/
	foreach ( $settings['query'] as $keyword => $value_array ) {
		foreach ( $value_array as $value => $theme ) {
			/*	Wildcard Keyword=* entries
				Overrides are not allowed, so no need to check.
			*/
			if ( '*' === $value ) {
				jr_mt_theme_entry(
					'Query',
					wp_get_theme( $theme )->Name,
					$keyword,
					'*'
				);
			}
		}
	}
	/*	Display URL entries:
		first, exact match URL entries;
		second, prefix URL entries;
		then, prefix URL entries with asterisk wildcards.
	*/
	foreach ( array(
		'url' => 'URL',
		'url_prefix' => 'URL Prefix',
		'url_asterisk' => 'URL Prefix*'
		) as $key => $description ) {
		foreach ( $settings[ $key ] as $settings_array ) {
			jr_mt_theme_entry(
				$key,
				wp_get_theme( $settings_array['theme'] )->Name,
				$settings_array['url'],
				$description
			);
		}
	}
	/*	Home Entry, then All Posts and Pages, and Everything Else
	*/
	foreach ( array(
		'site_home' => 'Home',
		'all_posts' => 'All Posts',
		'all_pages' => 'All Pages',
		'current'   => 'Everything Else'
		) as $key => $description ) {
		if ( '' !== $settings[ $key ] ) {
			jr_mt_theme_entry(
				$key,
				wp_get_theme( $settings[ $key ] )->Name,
				$description
			);
		}
	}
	if ( '' === $settings['current'] ) {
		jr_mt_theme_entry(
			'wordpress'
		);
	}
	echo '</ol>';
}

/**
 * Section text for Section2
 * 
 * Display an explanation of this Section
 *
 */
function jr_mt_site_home_expl() {
	?>
	<p>
	In this section, you can select a different Theme for Site Home.
	To remove a previously selected Theme, select the blank entry from the drop-down list.
	</p>
	<p>
	In the <i>next</i> section, you will be able to select a Theme, including the Current Theme, for individual Pages, Posts or
	any other non-Admin pages that have their own Permalink; for example, specific Archive or Category pages.
	Or groups of Pages, Posts or any other non-Admin pages that share the same URL Prefix.
	</p>
	<p>	
	There is also a Query Keyword section 
	farther down this Settings page
	that allows
	you to select a Theme to use whenever a specified 
	Query Keyword (<code>?keyword=value</code> or <code>&keyword=value</code>)
	appears in the URL of any Page, Post or other non-Admin page.
	Query entries will even override the Site Home entry,
	if the Query Keyword follows the Site Home URL.
	</p>
	<?php	
}

function jr_mt_echo_site_home() {
	$settings = get_option( 'jr_mt_settings' );
	jr_mt_themes_field( 'site_home', $settings['site_home'], 'jr_mt_settings', FALSE );
}

/**
 * Section text for Section3
 * 
 * Display an explanation of this Section
 *
 */
function jr_mt_single_settings_expl() {
	?>
	<p>
	Select a Theme for an individual Page, Post	or
	any other non-Admin page that has its own Permalink; for example, a specific Archive or Category page.
	Or for a group of pages which have URLs that all begin with the same characters ("Prefix"),
	optionally specifying an Asterisk ("*") to match all subdirectories at specific levels.
	</p>
	<p>
	Then cut and paste the URL of the desired Page, Post, Prefix or other non-Admin page.
	And click any of the <b>Save All Changes</b> buttons to add the entry.
	</p>
	There are three types of Entries that you can specify here:
	<ol>
	<li>
	<b>URL</b> - if Visitor URL matches this URL, use this Theme
	</li>
	<li>
	<b>URL Prefix</b> - any Visitor URL that begins with this URL Prefix will use this Theme
	</li>
	<li>
	<b>URL Prefix with Asterisk(s)</b> - URL Prefix that matches any subdirectory where Asterisk ("*") is specified
	</li>
	</ol>
	For the third type, an Asterisk can only be specified to match the entire subdirectory name, not parts of the name:
	<blockquote>
	For example, using a Permalink structure that uses dates,
	where a typical Post might be at URL
	<code>http://example.com/wp/2014/04/13/daily-thoughts/</code>,
	a URL Prefix with Asterisk entry of
	<code>http://example.com/wp/*/04/*/d</code>
	would match all April Posts with Titles that begin with the letter "d", no matter what year they were posted.
	</blockquote>
	</p>
	<?php	
}

function jr_mt_echo_add_is_prefix() {
	echo '<input type="radio" id="add_is_prefix" name="jr_mt_settings[add_is_prefix]" value="false" checked="checked" /> URL';
	?>
	<br/>
	<input type="radio" id="add_is_prefix" name="jr_mt_settings[add_is_prefix]" value="prefix" /> URL Prefix<br/>
	<input type="radio" id="add_is_prefix" name="jr_mt_settings[add_is_prefix]" value="*" /> URL Prefix with Asterisk ("*")
	<?php
}

function jr_mt_echo_add_theme() {
	jr_mt_themes_field( 'add_theme', '', 'jr_mt_settings', FALSE );
}

function jr_mt_echo_add_path_id() {
	?>
	<input id="add_path_id" name="jr_mt_settings[add_path_id]" type="text" size="100" maxlength="256" value="" />
	<br />
	(cut and paste URL here of Page, Post, Prefix or other)
	<br />
	URL must begin with
	<?php
	echo '<code>' . home_url() . '/</code>.';
	echo '<br />Beginning with Version 5.0, <code>keyword=value</code> Queries are now supported in all URLs.';
}

/**
 * Section text for Section4
 * 
 * Display an explanation of this Section
 *
 */
function jr_mt_querykw_expl() {
	?>
	<p>
	Select a Theme to use 
	whenever the specified Query Keyword (<code>?keyword=</code> or <code>&keyword=</code>)
	is found in the URL of
	any Page, Post or
	any other non-Admin page.
	And click any of the <b>Save All Changes</b> buttons to add the entry.
	</p>
	<p>
	<b>
	Note
	</b>
	that Query Keyword takes precedence over all other types of Theme selection entries.
	For example, 
	<?php
	echo '<code>' . home_url() . '?firstname=dorothy</code>'
		. ' would use the Theme specified for the <code>firstname</code> keyword, not the Theme specified for Site Home.'
		. ' Query matching is case-insensitive, so all Keywords entered are stored in lower-case.</p>';
}
function jr_mt_echo_add_querykw_theme() {
	jr_mt_themes_field( 'add_querykw_theme', '', 'jr_mt_settings', FALSE );
}
function jr_mt_echo_add_querykw_keyword() {
	$three_dots = '&#133;';
	echo '<code>'
		. home_url() 
		. "/</code>$three_dots<code>/?"
		. '<input id="add_querykw_keyword" name="jr_mt_settings[add_querykw_keyword]" type="text" size="20" maxlength="64" value="" />=</code>'
		. $three_dots;
}

/**
 * Section text for Section5
 * 
 * Display an explanation of this Section
 *
 */
function jr_mt_query_expl() {
	?>
	<p>
	Select a Theme to use 
	whenever the specified Query Keyword <b>and</b> Value (<code>?keyword=value</code> or <code>&keyword=value</code>)
	are found in the URL of
	any Page, Post or
	any other non-Admin page.
	And click any of the <b>Save All Changes</b> buttons to add the entry.
	</p>
	<p>
	<b>
	Note
	</b>
	that Query Keyword=Value takes precedence over all other Theme selection entries,
	including a Query Keyword entry for the same Keyword.
	For example, 
	<?php
	echo '<code>' . home_url() . '?firstname=dorothy</code>'
		. ' would use the Theme specified for the <code>firstname=dorothy</code> keyword=value pair,'
		. ' not the Theme specified for Site Home nor even the Theme specified for the Keyword <code>firstname</code>.'
		. ' Query matching is case-insensitive, so all Keywords and Values entered are stored in lower-case.</p>';
}
function jr_mt_echo_add_query_theme() {
	jr_mt_themes_field( 'add_query_theme', '', 'jr_mt_settings', FALSE );
}
function jr_mt_echo_add_query_keyword() {
	$three_dots = '&#133;';
	echo '<code>'
		. home_url() 
		. "/</code>$three_dots<code>/?"
		. '<input id="add_query_keyword" name="jr_mt_settings[add_query_keyword]" type="text" size="20" maxlength="64" value="" /></code>';
}
function jr_mt_echo_add_query_value() {
	echo '<code>'
		. '='
		. '<input id="add_query_value" name="jr_mt_settings[add_query_value]" type="text" size="20" maxlength="64" value="" /></code>';
}

/**
 * Section text for Section6
 * 
 * Display an explanation of this Section
 *
 */
function jr_mt_sticky_expl() {
	/* "Membership System V2" is a paid plugin that blocks (our sticky) Cookies
	*/
	global $jr_mt_plugins_cache;
	foreach ( $jr_mt_plugins_cache as $rel_path => $plugin_data ) {
		if ( 0 === strncasecmp( 'memberium', $rel_path, 9 ) ) {
			echo '<b><u>IMPORTANT</u></b>: The Sticky feature of this plugin does not work with the <b>Membership System V2</b> plugin, which blocks the required Cookies.  At least one plugin from memberium.com appears to have been installed: '
				. $plugin_data['Name'];
			break;
		}
	}
	?>
	<p>
	If one of the
	<b>
	Keyword=Value Entries
	</b>
	shown below
	(if any)
	is present in the URL of a WordPress non-Admin webpage on the current WordPress Site
	and that Entry is:
	<ol>
	<li>
	<b>Sticky</b>,
	then the specified Theme will continue to be displayed for subsequent
	WordPress non-Admin webpages
	viewed by the same Visitor
	until an Override entry is encountered by the same Visitor.
	</li>
	<li>
	<b>Override</b>,
	then the specified Theme will be displayed,
	effectively ending any previous Sticky Theme that was being displayed
	for the same Visitor.
	</li>
	</ol>
	<b>
	Note
	</b>
	that,
	as explained in the
	Query Keyword=Value
	section on the Settings tab,
	Query Keyword=Value already takes precedence over all other Theme selection entries,
	even without the Override checkbox selected.
	Override is only intended to cancel a Sticky entry
	and display the specified Theme on the current WordPress non-Admin webpage.
	</p>
	<p>
	Implementation Notes:
	<ol>
	<li>
	The term "Same Visitor",
	used above,
	refers to a single combination of
	computer, browser and possibly computer user name,
	if the visitor's computer has multiple accounts or user names.
	A computer could be a smartphone, tablet, laptop, desktop or other Internet access device used by the Visitor.
	</li>
	<li>
	When Sticky is active for a given Visitor,
	the associated Query Keyword=Value is added to the
	URL of links displayed on the current WordPress non-Admin webpage.
	With the following exceptions:
	<ul>
	<li>
	a)
	Only links pointing to non-Admin webpages of the current WordPress Site are altered.
	</li>
	<li>
	b)
	The 
	"When to add Sticky Query to a URL"
	setting below also controls when a Sticky Keyword=Value is added to a URL.
	</li>
	</ul>
	<li>
	Cookies are used for Sticky entries. If the visitor's browser refuses Cookies,
	or another Plugin blocks cookies,
	this setting will not work and no error messages will be displayed.
	</li>
	</ol>
	</p>
	<p>
	<b>
	Important:
	</b>
	the Sticky feature cannot be made to work in all WordPress environments.
	Timing, Cookie and other issues may be caused by other plugins, themes and visitor browser settings,
	so please test carefully and realize that the solution to some problems will involve a choice between not using the Sticky feature and not using a particular plugin or theme.
	</p>
	<?php
}

function jr_mt_echo_query_present() {
	$settings = get_option( 'jr_mt_settings' );
	/*
		FALSE if Setting "Append if no question mark ("?") found in URL", or
		TRUE if Setting "Append if no Override keyword=value found in URL"
	*/
	echo '<input type="radio" id="query_present" name="jr_mt_settings[query_present]" value="false" ';
	checked( $settings['query_present'], FALSE );
	echo ' /> Append if no question mark ("?") found in URL<br/><input type="radio" id="query_present" name="jr_mt_settings[query_present]" value="true" ';
	checked( $settings['query_present'] );
	echo ' /> Append if no Override <code>keyword=value</code> found in URL';
}

function jr_mt_echo_sticky_query_entry() {
	global $jr_mt_kwvalsep;
	$settings = get_option( 'jr_mt_settings' );
	$three_dots = '&#133;';
	$first = TRUE;
	if ( !empty( $settings['query'] ) ) {
		foreach ( $settings['query'] as $keyword => $value_array ) {
			foreach ( $value_array as $value => $theme ) {
				if ( '*' !== $value ) {
					if ( $first ) {
						$first = FALSE;
					} else {
						echo '<br />';
					}
					echo 'Sticky <input type="checkbox" id="sticky_query_entry" name="jr_mt_settings[sticky_query_entry][]" value="'
						. "$keyword$jr_mt_kwvalsep$value"
						. '" ';
					checked( isset( $settings['remember']['query'][$keyword][$value] ) );
					echo ' /> &nbsp; Override <input type="checkbox" id="override_query_entry" name="jr_mt_settings[override_query_entry][]" value="'
						. "$keyword$jr_mt_kwvalsep$value"
						. '" ';
					checked( isset( $settings['override']['query'][$keyword][$value] ) );
					echo ' /> &nbsp; Theme='
						. wp_get_theme( $theme )->Name . '; '
						. 'Query='
						. '<code>'
						. home_url() 
						. "/</code>$three_dots<code>/?"
						. "<b><input type='text' readonly='readonly' disable='disabled' name='jr_mt_stkw' value='$keyword' size='"
						. jr_mt_strlen( $keyword )
						. "' /></b>"
						. '='
						. "<b><input type='text' readonly='readonly' disable='disabled' name='jr_mt_stkwval' value='$value' size='"
						. jr_mt_strlen( $value )
						. "' /></b></code>";
				}
			}
		}
	}
	if ( $first ) {
		echo 'None';
	}
}

function jr_mt_everything_expl() {
	?>
	<p>
	<b>Theme for Everything</b>
	simplifies the use of a Theme with Theme Settings that you need to change frequently,
	when the Theme is only going to be used on one or more Pages or Posts.
	The Theme can be set as the WordPress Active Theme through the Appearance-Themes admin panel,
	and set for specific Pages or Posts using this plugin's settings (on Settings tab),
	with another Theme specified below as the plugin's default theme ("Theme for Everything").
	</p>
	<?php
}

function jr_mt_echo_current() {
	$settings = get_option( 'jr_mt_settings' );
	jr_mt_themes_field( 'current', $settings['current'], 'jr_mt_settings', TRUE );
	echo '<br />(select blank entry for default: WordPress Active Theme defined in Appearance-Themes, currently <b>' . wp_get_theme()->Name . '</b>)';
}

function jr_mt_all_settings_expl() {
	?>
	<p>
	These are
	<b>
	Advanced Setting
	</b>
	because they may not work with every other plugin, theme or permalinks setting.
	This plugin is only able to determine whether what is about to be displayed at the current URL
	is a Page or Post
	after all other Plugins have been loaded;
	the one exception to this is the Default setting for Permalinks,
	when <code>?p=</code> and <code>?page_id=</code> are used.
	</p>
	<p>
	Some other plugins and themes request the name of the current Theme
	<i>
	too early,
	</i>
	while they are being loaded,
	which is before this plugin is able to determine if it is on a Page or Post.
	For this reason,
	using either of these settings may not work properly for all other plugins and themes.
	As a result,
	if you choose to use either or both of these two settings,
	careful testing is advised immediately
	<u>and</u>
	whenever you change the Permalink setting, activate a plugin or start using a different theme.
	</p>
	<p>
	In this section, you can select a different Theme for All Pages and/or All Posts.
	To remove a previously selected Theme, select the blank entry from the drop-down list.
	</p>
	<p>
	On the Settings tab, you were able to select a Theme, including WordPress' Active Theme, to override any choice you make here, for individual Pages, Posts or
	any other non-Admin pages that have their own Permalink; for example, specific Archive or Category pages.
	Or groups of Pages, Posts or any other non-Admin pages that share the same URL Prefix.
	</p>
	<p>	
	The Settings tab also has a Query Keyword section 
	that allows
	you to select a Theme to use whenever a specified 
	Query Keyword (<code>?keyword=value</code> or <code>&keyword=value</code>)
	appears in the URL of any Page, Post or other non-Admin page.
	</p>
	<?php
}

function jr_mt_echo_all_things( $thing ) {
	$settings = get_option( 'jr_mt_settings' );
	$field = 'all_' . strtolower( $thing['thing'] );
	jr_mt_themes_field( $field, $settings[$field], 'jr_mt_settings', TRUE );
}

function jr_mt_validate_settings( $input ) {
	global $jr_mt_kwvalsep;
	$valid = array();
	
	if ( isset( $input['permalink'] ) ) {
		$internal_settings = get_option( 'jr_mt_internal_settings' );
		$internal_settings['permalink'] = get_option( 'permalink_structure' );
		update_option( 'jr_mt_internal_settings', $internal_setting );
	}
	
	foreach ( array( 'all_pages', 'all_posts', 'site_home', 'current' ) as $thing ) {
		$valid[$thing] = $input[$thing];
	}
	
	$settings = get_option( 'jr_mt_settings' );
	$prefix_types = array(
		'false'  => 'url',
		'prefix' => 'url_prefix',
		'*'      => 'url_asterisk'
	);
	foreach ( $prefix_types as $key => $thing ) {
		$valid[$thing] = $settings[$thing];
	}
	$query = $settings['query'];
	$remember = array( 'query' => array() );
	if ( isset( $input['sticky_query_entry'] ) ) {
		foreach	( $input['sticky_query_entry'] as $query_entry ) {
			list( $keyword, $value ) = explode( $jr_mt_kwvalsep, $query_entry );
			/*	Data Sanitization not required as
				Keyword and Value are not entered by a human,
				but extracted from previously-generated HTML.
			*/
			$remember['query'][$keyword][$value] = TRUE;
		}
	}

	$override = array( 'query' => array() );
	if ( isset( $input['override_query_entry'] ) ) {
		foreach	( $input['override_query_entry'] as $query_entry ) {
			list( $keyword, $value ) = explode( $jr_mt_kwvalsep, $query_entry );
			/*	Data Sanitization not required as
				Keyword and Value are not entered by a human,
				but extracted from previously-generated HTML.
			*/
			$override['query'][$keyword][$value] = TRUE;
		}
	}
	
	if ( isset ( $input['del_entry'] ) ) {
		foreach ( $input['del_entry'] as $del_entry ) {
			$del_array = explode( '=', $del_entry, 3 );
			if ( 'query' === $del_array[0] ) {
				unset( $query[ $del_array[1] ][ $del_array[2] ] );
				if ( empty( $query[ $del_array[1] ] ) ) {
					unset( $query[ $del_array[1] ] );
				}
				/*	unset() does nothing if a variable or array element does not exist.
				*/
				unset( $remember['query'][ $del_array[1] ][ $del_array[2] ] );
				if ( empty( $remember['query'][ $del_array[1] ] ) ) {
					unset( $remember['query'][ $del_array[1] ] );
				}
				unset( $override['query'][ $del_array[1] ][ $del_array[2] ] );
				if ( empty( $override['query'][ $del_array[1] ] ) ) {
					unset( $override['query'][ $del_array[1] ] );
				}
			} else {
				/*	Check for a URL entry
				*/
				if ( 'url' === substr( $del_array[0], 0, 3 ) ) {
					foreach ( $valid[ $del_array[0] ] as $i => $entry_array ) {
						if ( $entry_array['url'] === $del_array[2] ) {
							/*	Cannot unset $entry_array, even if prefixed by & in foreach
							*/
							unset( $valid[ $del_array[0] ][ $i ] );
							break;
						}
					}
				} else {
					/*	Must be Home, All Pages or Posts, or Everything
					*/
					$valid[ $del_array[0] ] = '';
				}
			}
		}
	}
	
	/*	Handle troublesome %E2%80%8E UTF Left-to-right Mark (LRM) suffix first.
	*/
	if ( FALSE === stripos( $input['add_path_id'], '%E2%80%8E' ) ) {
		if ( FALSE === stripos( rawurlencode( $input['add_path_id'] ), '%E2%80%8E' ) ) {
			$url = $input['add_path_id'];
		} else {
			$url = rawurldecode( str_ireplace( '%E2%80%8E', '', rawurlencode( $input['add_path_id'] ) ) );
		}
	} else {
		$url = str_ireplace( '%E2%80%8E', '', $input['add_path_id'] );
	}
	$url = rawurldecode( trim( $url ) );
	
	if ( ( empty( $input['add_theme'] ) && !empty( $url ) ) || ( !empty( $input['add_theme'] ) && empty( $url ) ) ) {
		add_settings_error(
			'jr_mt_settings',
			'jr_mt_emptyerror',
			'Both URL and Theme must be specified to add an Individual entry',
			'error'
		);		
	} else {
		if ( !empty( $url ) ) {
			if ( jr_mt_same_prefix_url( home_url(), $url ) ) {
				if ( ( '*' !== $input['add_is_prefix'] ) && ( FALSE !== strpos( $url, '*' ) ) ) {
					add_settings_error(
						'jr_mt_settings',
						'jr_mt_queryerror',
						'Asterisk ("*") only allowed when "URL Prefix with Asterisk" selected: <code>' . $url . '</code>',
						'error'
					);
				} else {									
					$prep_url = jr_mt_prep_url( $url );
					if ( 'false' === $input['add_is_prefix'] ) {
						if ( jr_mt_same_url( $prep_url, home_url() ) ) {
							add_settings_error(
								'jr_mt_settings',
								'jr_mt_homeerror',
								'Please use "Select Theme for Site Home" field instead of specifying Site Home URL as an individual entry.',
								'error'
							);
						} else {
							if ( jr_mt_same_prefix_url( $prep_url, admin_url() ) ) {
								add_settings_error(
									'jr_mt_settings',
									'jr_mt_adminerror',
									'Admin Page URLs are not allowed because no known Themes alter the appearance of Admin pages: <code>' . $url . '</code>',
									'error'
								);
							}
						}
					} else {
						if ( '*' === $input['add_is_prefix'] ) {
							$url_dirs = explode( '/', str_replace( '\\', '/', $url ) );
							foreach ( $url_dirs as $dir ) {
								if ( FALSE !== strpos( $dir, '*' ) ) {
									$asterisk_found = TRUE;
									if ( '*' !== $dir ) {
										$asterisk_not_alone = TRUE;
									}
									break;
								}
							}
							if ( isset( $asterisk_found ) ) {
								if ( isset( $asterisk_not_alone ) ) {
									add_settings_error(
										'jr_mt_settings',
										'jr_mt_queryerror',
										'An Asterisk ("*") may only replace a full subdirectory name, not just a portion of it: <code>' . $url . '</code>',
										'error'
									);	
								}
							} else {
								add_settings_error(
									'jr_mt_settings',
									'jr_mt_queryerror',
									'No Asterisk ("*") specified but "URL Prefix with Asterisk" selected: <code>' . $url . '</code>',
									'error'
								);	
							}
						}
					}

					function jr_mt_settings_errors() {
						$errors = get_settings_errors();
						if ( !empty( $errors ) ) {
							foreach ( $errors as $error_array ) {
								if ( 'error' === $error_array['type'] ) {
									return TRUE;
								}
							}
						}
						return FALSE;
					}

					if ( !jr_mt_settings_errors() ) {
						/*	['url'], ['url_prefix'] or ['url_asterisk']
						*/
						$key = $prefix_types[ $input['add_is_prefix'] ];
						$valid[ $key ][] = array(
							'url'   => $url,
							'prep'  => $prep_url,
							'theme' => $input['add_theme']
						);
					}
				}
			} else {
				add_settings_error(
					'jr_mt_settings',
					'jr_mt_urlerror',
					' URL specified is not part of current WordPress web site: <code>'
						. $url
						. '</code>.  URL must begin with <code>'
						. home_url()
						. '</code>.',
					'updated'
				);			
			}
		}
	}
	
	/*	Make sure reserved characters are not used
		in URL Query keyword or value fields on Settings page.
	*/
	function jr_mt_query_chars( $element, $where ) {
		foreach (
			array(
				'='	 => 'Equals Sign'   ,
				'?'	 => 'Question Mark' ,
				'&'	 => 'Ampersand'     ,
				' '	 => 'Blank'         ,
				'#'	 => 'Number Sign'   ,
				'/'	 => 'Slash'         ,
				'\\' => 'Backslash'     ,
				'['	 => 'Square Bracket',
				']'	 => 'Square Bracket',
			) as $char => $name ) {
			if ( FALSE !== strpos( $element, $char ) ) {
				add_settings_error(
					'jr_mt_settings',
					'jr_mt_queryerror',
					'Illegal character used in '
					. $where
					. ': '
					. $name
					. ' ("' . $char . '") in "'
					. $element
					. '"',
					'error'
				);
				return FALSE;
			}
		}
		return TRUE;
	}
	/*	Data Sanitization needed here
	*/
	$keyword = jr_mt_prep_query_keyword( $input['add_querykw_keyword'] );
	if ( !empty( $input['add_querykw_theme'] ) && !empty( $keyword ) ) {
		if ( jr_mt_query_chars( $keyword, 'Query Keyword' ) ) {
			/*	If there is an existing entry for the Keyword,
				then replace it.
				Otherwise, create a new entry.
			*/
			$query[$keyword]['*'] = $input['add_querykw_theme'];
		}
	} else {
		if ( !( empty( $input['add_querykw_theme'] ) && empty( $keyword ) ) ) {
			add_settings_error(
				'jr_mt_settings',
				'jr_mt_emptyerror',
				'Both Query Keyword and Theme must be specified to add an Individual Query Keyword entry',
				'error'
			);
		}
	}
	
	/*	Data Sanitization needed here
	*/
	$keyword = jr_mt_prep_query_keyword( $input['add_query_keyword'] );
	$value = jr_mt_prep_query_value( $input['add_query_value'] );
	if ( !empty( $input['add_query_theme'] ) && !empty( $keyword ) && !empty( $value ) ) {
		if ( jr_mt_query_chars( $keyword, 'Query Keyword' ) && jr_mt_query_chars( $value, 'Query Value' ) ) {
			/*	If there is an existing entry for the Keyword and Value pair,
				then replace it.
				Otherwise, create a new entry.
			*/
			$query[$keyword][$value] = $input['add_query_theme'];
		}
	} else {
		if ( !( empty( $input['add_query_theme'] ) && empty( $keyword ) && empty( $value ) ) ) {
			add_settings_error(
				'jr_mt_settings',
				'jr_mt_emptyerror',
				'Query Keyword, Value and Theme must all be specified to add an Individual Query entry',
				'error'
			);
		}
	}
	
	if ( 'true' === $input['query_present'] ) {
		$valid['query_present'] = TRUE;
	} else {
		if ( 'false' === $input['query_present'] ) {
			$valid['query_present'] = FALSE;
		}
	}
	
	$errors = get_settings_errors();
	if ( empty( $errors ) ) {
		add_settings_error(
			'jr_mt_settings',
			'jr_mt_saved',
			'Settings Saved',
			'updated'
		);	
	}
	$valid['query'] = $query;
	$valid['remember'] = $remember;
	$valid['override'] = $override;
	return $valid;
}

?>