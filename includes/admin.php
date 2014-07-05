<?php
//	Exit if .php file accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


//	Admin Page

require_once( jr_mt_path() . 'includes/admin-other.php' );

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
}

global $jr_mt_kwvalsep;
/*	Everything is converted to lower-case, so upper-case letter makes a good keyword-value separator
*/
$jr_mt_kwvalsep = 'A';

/**
 * Settings page for plugin
 * 
 * Display and Process Settings page for this plugin.
 *
 */
function jr_mt_settings_page() {
	global $jr_mt_plugin_data;
	$jr_mt_plugin_data = array_merge( $jr_mt_plugin_data, jr_readme() );
	global $jr_mt_themes_cache;
	$jr_mt_themes_cache = wp_get_themes();
	global $jr_mt_plugins_cache;
	$jr_mt_plugins_cache = get_plugins();
	add_thickbox();
	echo '<div class="wrap">';
	echo '<h2>' . $jr_mt_plugin_data['Name'] . '</h2>';
	
	//	Required because it is only called automatically for Admin Pages in the Settings section
	settings_errors( 'jr_mt_settings' );
	
	$theme = wp_get_theme()->Name;
	global $jr_mt_options_cache;

	$current_wp_version = get_bloginfo( 'version' );

	if ( $jr_mt_plugin_data['read readme'] && version_compare( $current_wp_version, $jr_mt_plugin_data['Tested up to'], '>' ) ) {
		/*	WordPress version is too new:
			When currently-installed version of Plugin was installed, 
			it did not support currently-installed version of WordPress.  
			So, check if a newer version of plugin is available.		*/
		$current = FALSE;
		//	Check if latest version of the plugin supports this version of WordPress
		if ( !function_exists( 'plugins_api' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		}
		$directory = plugins_api( 'plugin_information', array( 'slug' => $jr_mt_plugin_data['slug'],
			'fields' => array( 'download_link' => TRUE, 
				'tested' => TRUE,
				'version' => TRUE,
				'error_data' => TRUE,
				'tags' => FALSE,
				'compatibility' => FALSE,
				'sections' => FALSE
				)
			) );
		if ( property_exists( $directory, 'errors' ) && ( $directory->error_data->plugins_api_failed == 'N;' ) ) {
			//	Plugin not found in WordPress Directory
			echo '<h3>Warnings</h3><p>Here is the problem:<ul><li> &raquo; This Plugin (' . $jr_mt_plugin_data['Name'] 
				. ') has not been tested with the version of WordPress you are currently running: ' . $current_wp_version
				. '.</li><li> &raquo; This Plugin could not be found in the WordPress Plugin Directory.  '
				. 'If you are sure it should be there, the WordPress Plugin Directory may be currently unavailable or inaccessible from your web server.</li></ul></p>'
				. '<p>The plugin will probably still work with your newer version of WordPress, but you need to be aware of the issue.</p>';
		} else {
			if ( version_compare( $current_wp_version, $directory->tested, '>' ) ) {
				//	Latest version of readme.txt for latest version of Plugin indicates that Plugin has not yet been tested for this version of WordPress
				echo '<h3>Warning</h3><p>Here is the problem:<ul><li> &raquo; This Plugin (' . $jr_mt_plugin_data['Name'] 
					. ') has not been tested with the version of WordPress you are currently running: ' . $current_wp_version
					. '.</li></ul></p>'
					. '<p>The plugin has been tested with Version ' . $directory->tested . ' of WordPress and '
					. 'will probably still work with your newer version of WordPress, but you need to be aware of the issue.</p>';
			} else {
				if ( version_compare( $jr_mt_plugin_data['Version'], $directory->version, '=' ) ) {
					/*	The latest version of the Plugin has been installed, 
						but the readme.txt has been updated in the WordPress Plugin Directory
						to indicate that it now supports the installed version of WordPress.
					
						Latest version of Plugin has already been installed, but readme.txt is out of date,
						so update readme.txt.	...if you can
					*/
					
					$errmsg_before = '<h3>Warning</h3><p>Here is the problem:<ul><li> &raquo; This version (' . $jr_mt_plugin_data['Version']
						. ') of this Plugin (' . $jr_mt_plugin_data['Name']
						. ') has been tested with the version of WordPress you are currently running (' . $current_wp_version
						. '), but</li><li> &raquo; The currently installed readme.txt file for this plugin is out of date,'
						. '</li><li> &raquo; The attempt to update the readme.txt from the WordPress Plugin Repository failed, and'
						. '</li><li> &raquo; The specific error is:  ';
					$errmsg_after = '</li></ul></p>'
						. '<p>Another attempt will be made to update readme.txt each time this Settings page is displayed.'
						. ' Nonetheless, this plugin should work properly even if readme.txt is out of date.</p>';
					
					if ( is_wp_error( $file_name = download_url( $directory->download_link ) ) ) {
						//	Error
						echo $errmsg_before . 'The plugin failed to completely download from the WordPress Repository with 300 seconds' . $errmsg_after;
					} else {
						if ( function_exists( 'zip_open' ) ) {
							if ( is_int( $resource_handle = zip_open( $file_name ) ) ) {
								//	Error
								echo $errmsg_before 
									. "php function zip_open error number $resource_handle while attempting to open the plugin's"
									. 'compressed .zip file successfully downloaded from the WordPress Plugin Repository' 
									. $errmsg_after;
							} else {
								$find_readme = TRUE;
								while ( $find_readme && ( FALSE !== $dir_ent = zip_read( $resource_handle ) ) ) {
									if ( is_int( $dir_ent ) ) {
										//	Error code
										echo $errmsg_before 
											. "php function zip_read error number $dir_ent while attempting to read the plugin's"
											. ' compressed .zip file successfully downloaded from the WordPress Plugin Repository' 
											. $errmsg_after;
										//	Get out of While loop
										$find_readme = FALSE;	
									} else {
										//	Wait until the While loop gets to the readme.txt entry in the Plugin's Zip file
										if ( zip_entry_name( $dir_ent ) == $jr_mt_plugin_data['slug'] . '/readme.txt' ) {
											if ( FALSE === zip_entry_open( $resource_handle, $dir_ent, 'rb' ) ) {
												//	Error
												echo $errmsg_before 
													. 'php function zip_entry_open failed to open readme.txt file compressed within plugin .zip file in WordPress Repository' 
													. $errmsg_after;
											} else {
												$filesize = zip_entry_filesize( $dir_ent );
												if ( !is_int( $filesize ) || ( $filesize < 100 ) ) {
													//	Error
													echo $errmsg_before 
														. 'Size, in bytes, of readme.txt file is being incorrectly reported by php function zip_entry_filesize as '
														. var_export( $filesize, TRUE )
														. $errmsg_after;
												} else {
													$readme_content = zip_entry_read( $dir_ent, $filesize );
													if ( ( $readme_content === FALSE ) || ( $readme_content === '' ) ) {
														//	Error
														echo $errmsg_before 
															. 'php function zip_entry_read failed to read readme.txt file compressed within plugin .zip file in WordPress Repository'
															. $errmsg_after;
													} else {
														if ( FALSE === zip_entry_close( $dir_ent ) ) {
															//	Error
															echo $errmsg_before 
																. 'php function zip_entry_close failed to close readme.txt file compressed within plugin .zip file in WordPress Repository'
																. $errmsg_after;
														} else {
															//	Alternate:  file_put_contents( jr_mt_path() . 'readme.txt', $readme_content );
															$write_return = jr_filesystem_text_write( $readme_content, 'readme.txt', jr_mt_path() );
															if ( is_wp_error( $write_return ) || ( FALSE === $write_return ) ) {
																//	Error
																echo $errmsg_before 
																	. 'WP_filesystem failed to store readme.txt file as part of download/update process from WordPress Repository'
																	. $errmsg_after;
															}
														}
													}
												}
											}
											//	Get out of While loop because we have found and processed readme.txt
											$find_readme = FALSE;
										}
									}
								}
								zip_close( $resource_handle );
							}
						} else {
							echo $errmsg_before 
								. "php zip_open function is not defined, so readme.txt could not be updated from WordPress Plugin Repository"
								. $errmsg_after;
						}
						// Delete temporary download file
						if ( !unlink( $file_name ) ) {
							echo $errmsg_before 
								. "php unlink function failed to delete downloaded readme.txt in temporary download file $file_name"
								. $errmsg_after;
						}
					}
					$current = TRUE;
				} else {
					//	Recommend updating Plugin to latest version which supports the version of WordPress being run, 
					//	but the currently-installed version of the Plugin does not.
					echo '<h3>Warning</h3><p>This plugin is out of date and should be updated for performance and reliability reasons.'
						. '  Plugin updates are shown on the Plugins-Installed Plugins page and the Dashboard-Updates page here in the Admin panels.</p>';
				}
			}
		}
	} else {
		//	Currently-installed version of Plugin supports currently-installed version of WordPress
		$current = TRUE;
	}
	
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
				. ' plugin (not just when using this Settings page, but whenever the ' 
				. $jr_mt_plugin_data['Name'] . ' plugin is activated).</p>';
		}
	}
	
	if ( $compatible ) {
		?>		
		<h3>Overview</h3>
		<p>This Plugin allows you to selectively change the Theme you have selected as your <b>Current Theme</b> in <b>Appearance-Themes</b> on the Admin panels.
		You can choose from any of the <b>Available Themes</b> listed on the Appearance-Themes Admin panel for:
		<ul>
		<li> &raquo; All Pages</li>
		<li> &raquo; All Posts</li>
		<li> &raquo; Everything (Advanced Settings)</li> 
		<li> &raquo; The Site Home</li>
		<li> &raquo; A Specific Page</li>
		<li> &raquo; A Specific Post</li>
		<li> &raquo; Any other non-Admin page that has its own Permalink; for example, a specific Archive or Category page</li>
		<li> &raquo; A Specific Query Keyword, or Keyword/Value pair, in any URL (<code>?keyword=value</code> or <code>&keyword=value</code>)</li>
		<li> &raquo; All non-Admin pages after a Specific Query Keyword/Value pair is specified in any URL (Advanced Settings)</li>
		</ul>
		</p>
		<h3>Important Notes</h3>
		<?php
		if ( function_exists('is_multisite') && is_multisite() ) {
			echo "In a WordPress Network (AKA Multisite), Themes must be <b>Network Enabled</b> before they will appear as Available Themes on individual sites' Appearance-Themes panel.";
		}
		echo '<p>';
		echo "The Current Theme, defined to WordPress in Appearance-Themes admin panel, is <b>$theme</b>.";
		$settings = get_option( 'jr_mt_settings' );
		if ( trim( $settings['current'] ) ) {
			echo " But it is being overridden in Advanced Settings (see below), which set the plugin's default Theme to <b>";
			echo wp_get_theme( $settings['current'] )->Name;
			echo '</b>. You will not normally need to specify this default Theme in any of the other Settings on this page, though you will need to specify the WordPress Current Theme wherever you want it appear. Or, if you specify a different Theme for All Pages, All Posts or Everything, and wish to use the default Theme for one or more specific Pages, Posts or other non-Admin pages.';
		} else {
			echo ' You will not normally need to specify it in any of the Settings on this page. The only exception would be if you specify a different Theme for All Pages, All Posts or Everything, and wish to use the Current Theme for one or more specific Pages, Posts or other non-Admin pages.';
		}
		echo '</p>';
		if ( $jr_mt_plugin_data['read readme'] ) {
			if ( $current ) {
				echo '<p>This Plugin (' . $jr_mt_plugin_data['Name'] . ') has been tested with the version of WordPress you are currently running: ' 
					. $current_wp_version . '</p>';
			}
		} else {
			echo '<p>Compatibility checks could not be done because the plugin was unable to read its readme.txt file, likely a user/permissions hosting issue.</p>';
		}
		if ( jr_mt_plugin_update_available() ) {
			echo '<p>A new version of this Plugin (' . $jr_mt_plugin_data['Name'] . ') is available from the WordPress Repository.'
				. ' We strongly recommend updating ASAP because new versions fix problems that users like yourself have reported to us.'
				. ' <a class="thickbox" title="' . $jr_mt_plugin_data['Name'] . '" href="' . network_admin_url()
				. 'plugin-install.php?tab=plugin-information&plugin=' . $jr_mt_plugin_data['slug']
				. '&section=changelog&TB_iframe=true&width=640&height=768">Click here</a> for more details.</p>';
		}
		echo '<hr /><form action="options.php" method="POST">';
		
		//	Plugin Settings are displayed and entered here:
		settings_fields( 'jr_mt_settings' );
		do_settings_sections( 'jr_mt_settings_page' );
		echo '<p><input name="save" type="submit" value="Save Changes" class="button-primary" /></p></form>';
	}

	echo '<hr /><h3>System Information</h3><p>You are currently running:<ul>';
	echo "<li> &raquo; The {$jr_mt_plugin_data['Name']} plugin Version {$jr_mt_plugin_data['Version']}</li>";
	echo "<li> &nbsp; &raquo;&raquo; The Path to the plugin's directory is " . rtrim( jr_mt_path(), '/' ) . '</li>';
	echo "<li> &nbsp; &raquo;&raquo; The URL to the plugin's directory is " . plugins_url() . "/{$jr_mt_plugin_data['slug']}</li>";
	echo "<li> &raquo; WordPress Version $current_wp_version</li>";
	echo '<li> &nbsp; &raquo;&raquo; WordPress language is set to ' , get_bloginfo( 'language' ) . '</li>';
	echo '<li> &raquo; ' . php_uname( 's' ) . ' operating system, Release/Version ' . php_uname( 'r' ) . ' / ' . php_uname( 'v' ) . '</li>';
	echo '<li> &raquo; ' . php_uname( 'm' ) . ' computer hardware</li>';
	echo '<li> &raquo; Host name ' . php_uname( 'n' ) . '</li>';
	echo '<li> &raquo; php Version ' . phpversion() . '</li>';
	echo '<li> &nbsp; &raquo;&raquo; php memory_limit ' . ini_get('memory_limit') . '</li>';
	echo '<li> &raquo; Zend engine Version ' . zend_version() . '</li>';
	echo '<li> &raquo; Web Server software is ' . getenv( 'SERVER_SOFTWARE' ) . '</li>';
	if ( function_exists( 'apache_get_version' ) && ( FALSE !== $apache = apache_get_version() ) ) {
		echo "<li> &nbsp; &raquo;&raquo; Apache Version $apache</li>";
	}
	global $wpdb;
	echo '<li> &raquo; MySQL Version ' . $wpdb->get_var( 'SELECT VERSION();', 0, 0 ) . '</li>';

	echo '</ul></p>';
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
	if ( !empty( $settings['ids']) || !empty( $settings['query'] ) ) {
		add_settings_section( 'jr_mt_delete_settings_section', 
			'Current Theme Selection Entries', 
			'jr_mt_delete_settings_expl', 
			'jr_mt_settings_page' 
		);
		if ( !empty( $settings['ids'] ) ) {
			add_settings_field( 'del_entry', 'Page/Post/Prefix Entries:', 'jr_mt_echo_delete_entry', 'jr_mt_settings_page', 'jr_mt_delete_settings_section' );
		}
		if ( !empty( $settings['query'] ) ) {
			add_settings_field( 'del_query_entry', 'Query Keyword Entries:', 'jr_mt_echo_delete_query_entry', 'jr_mt_settings_page', 'jr_mt_delete_settings_section' );
		}
	}
	add_settings_section( 'jr_mt_all_settings_section', 
		'<input name="save" type="submit" value="Save Changes" class="button-primary" /></h3><h3>For All Pages, All Posts and/or Site Home', 
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
	add_settings_field( 'site_home', 
		'Select Theme for Site Home<br />(' . get_home_url() . ')', 
		'jr_mt_echo_site_home', 
		'jr_mt_settings_page', 
		'jr_mt_all_settings_section' 
	);
	add_settings_section( 'jr_mt_single_settings_section', 
		'For An Individual Page, Post or other non-Admin page;<br />or a group of pages, specified by URL Prefix, optionally with Asterisk(s)', 
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
	add_settings_field( 'add_query_remember', 'Sticky?', 'jr_mt_echo_add_query_remember', 'jr_mt_settings_page', 'jr_mt_query_section' );
	add_settings_section( 'jr_mt_advanced_settings_section', 
		'Advanced Settings', 
		'jr_mt_advanced_settings_expl', 
		'jr_mt_settings_page' 
	);
	add_settings_field( 'current', 
		'Select Theme for Everything, to Override WordPress Current Theme (<b>' . wp_get_theme()->Name . '</b>)', 
		'jr_mt_echo_current', 
		'jr_mt_settings_page', 
		'jr_mt_advanced_settings_section' 
	);
}

/**
 * Section text for Section2
 * 
 * Display an explanation of this Section
 *
 */
function jr_mt_delete_settings_expl() {
	?>
	<p>
	In this section, all entries are displayed for Themes selected for individual Pages, Posts
	and any other non-Admin pages that have their own Permalink; for example, specific Archive or Category pages.
	Or groups of Pages, Posts or any other non-Admin pages that share the same <b>URL Prefix</b> 
	or <b>Query Keyword</b> (<code>?keyword=value</code> or <code>&keyword=value</code>).
	</p>
	<p>
	You can delete any of these entries by filling in the check box beside each one
	and clicking the <b>Save Changes</b> button.
	To change the Theme for an entry, add the same entry with a different Theme in one of the sections below this one.</p>
	
	<?php
}

function jr_mt_echo_delete_entry() {
	$settings = get_option( 'jr_mt_settings' );
	$first = TRUE;
	foreach ( $settings['ids'] as $path_id => $opt_array ) {
		if ( $first ) {
			$first = FALSE;
		} else {
			echo '<br />';
		}
		echo "Delete <input type='checkbox' id='del_entry' name='jr_mt_settings[del_entry][]' value='$path_id' /> &nbsp; Theme="
			. wp_get_theme( $opt_array['theme'] )->Name . '; ';
		if ( $path_id == '' ) {
			echo 'Site=<a href="' . get_home_url() . '" target="_blank">Home</a>';
		} else {
			switch ( $opt_array['type'] ) {
				case '*':
					echo 'Prefix*=<a href="' . get_home_url() . "/$path_id" . '" target="_blank">' . "$path_id</a>";
					break;
				case 'prefix':
					echo 'Prefix=<a href="' . get_home_url() . "/$path_id" . '" target="_blank">' . "$path_id</a>";
					break;
				case 'cat':
					echo 'Category=<a href="' . get_home_url() . '/?cat=' . $opt_array['id'] . '" target="_blank">' . get_cat_name( $opt_array['id'] ) . '</a>';
					break;
				case 'archive':
					echo 'Archive=<a href="' . get_home_url() . '/?m=' . $opt_array['id'] . '" target="_blank">' . $opt_array['id'] . '</a>';
					break;
				default:
					$p_array = get_posts( array( 'post_type' => 'any', 'include' => array( $path_id ) ) );
					if ( empty( $p_array ) ) {
						if ( $opt_array['type'] == 'admin' ) {
							echo 'Admin=<a href="' . get_home_url() . '/' . $opt_array['rel_url'] . '" target="_blank">' . "$path_id</a>";
						} else {
							echo 'Path=<a href="' . get_home_url() . "/$path_id" . '" target="_blank">' . "$path_id</a>";
						}
					} else {
						echo ucfirst( $p_array[0]->post_type ) . '=<a href="' . get_permalink( $path_id ) . '" target="_blank">' . $p_array[0]->post_title . '</a>';
					}
			}
		}
	}
}

function jr_mt_echo_delete_query_entry() {
	global $jr_mt_kwvalsep;
	$settings = get_option( 'jr_mt_settings' );
	$three_dots = '&#133;';
	$first = TRUE;
	foreach ( $settings['query'] as $keyword => $value_array ) {
		foreach ( $value_array as $value => $theme ) {
			if ( $first ) {
				$first = FALSE;
			} else {
				echo '<br />';
			}
			echo "Delete <input type='checkbox' id='del_query_entry' name='jr_mt_settings[del_query_entry][]' value='$keyword$jr_mt_kwvalsep$value' /> &nbsp; Theme="
				. wp_get_theme( $theme )->Name . '; '
				. 'Query='
				. '<code>'
				. trim( get_home_url(), '\ /' ) 
				. "/</code>$three_dots<code>/?"
				. "<b><input type='text' readonly='readonly' disable='disabled' name='jr_mt_delkw' value='$keyword' size='"
				. jr_mt_strlen( $keyword )
				. "' /></b>"
				. '=';
			if ( '*' === $value ) {	
				echo '</code>' . $three_dots;
			} else {
				echo "<b><input type='text' readonly='readonly' disable='disabled' name='jr_mt_delkwval' value='$value' size='"
				. jr_mt_strlen( $value )
				. "' /></b></code>";
			}
			if ( isset( $settings['remember']['query'][$keyword][$value] ) ) {
				echo ' <b><u>STICKY</u></b> <small>(see <b>Advanced Settings</b> section for explanation)</small>';
			}
		}
	}
}

/**
 * Section text for Section1
 * 
 * Display an explanation of this Section
 *
 */
function jr_mt_all_settings_expl() {
	?>
	<p>
	In this section, you can select a different Theme for All Pages, All Posts and/or Site Home.
	To remove a previously selected Theme, select the blank entry from the drop-down list.
	</p>
	<p>
	In the <i>next</i> section, you will be able to select a Theme, including the Current Theme, to override any choice you make here, for individual Pages, Posts or
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
	</p>
	<?php
}

function jr_mt_echo_all_things( $thing ) {
	$settings = get_option( 'jr_mt_settings' );
	$field = 'all_' . strtolower( $thing['thing'] );
	jr_mt_themes_field( $field, $settings[$field], 'jr_mt_settings', TRUE );
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
	And click the <b>Save Changes</b> button to add the entry.
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
	?>
	<input type="radio" id="add_is_prefix" name="jr_mt_settings[add_is_prefix]" value="false" checked="checked" /> URL<br/>
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
	echo '<code>' . trim( get_home_url(), '\ /' ) . '/</code>';
}

/**
 * Section text for Section5
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
	And click the <b>Save Changes</b> button to add the entry.
	</p>
	<p>
	<b>
	Note
	</b>
	that Query Keyword takes precedence over all other types of Theme selection entries.
	For example, 
	<?php
	echo '<code>' . trim( get_home_url(), '\ /' ) . '?firstname=dorothy</code>'
		. ' would use the Theme specified for the <code>firstname</code> keyword, not the Theme specified for Site Home.</p>';
}
function jr_mt_echo_add_querykw_theme() {
	jr_mt_themes_field( 'add_querykw_theme', '', 'jr_mt_settings', FALSE );
}
function jr_mt_echo_add_querykw_keyword() {
	$three_dots = '&#133;';
	echo '<code>'
		. trim( get_home_url(), '\ /' ) 
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
	And click the <b>Save Changes</b> button to add the entry.
	</p>
	<p>
	<b>
	Note
	</b>
	that Query Keyword=Value takes precedence over all other Theme selection entries,
	including a Query Keyword entry for the same Keyword.
	For example, 
	<?php
	echo '<code>' . trim( get_home_url(), '\ /' ) . '?firstname=dorothy</code>'
		. ' would use the Theme specified for the <code>firstname=dorothy</code> keyword=value pair,'
		. ' not the Theme specified for Site Home nor even the Theme specified for the Keyword <code>firstname</code>.</p>';
}
function jr_mt_echo_add_query_theme() {
	jr_mt_themes_field( 'add_query_theme', '', 'jr_mt_settings', FALSE );
}
function jr_mt_echo_add_query_keyword() {
	$three_dots = '&#133;';
	echo '<code>'
		. trim( get_home_url(), '\ /' ) 
		. "/</code>$three_dots<code>/?"
		. '<input id="add_query_keyword" name="jr_mt_settings[add_query_keyword]" type="text" size="20" maxlength="64" value="" /></code>';
}
function jr_mt_echo_add_query_value() {
	echo '<code>'
		. '='
		. '<input id="add_query_value" name="jr_mt_settings[add_query_value]" type="text" size="20" maxlength="64" value="" /></code>';
}
function jr_mt_echo_add_query_remember() {
	echo '<input type="checkbox" id="add_query_remember" name="jr_mt_settings[add_query_remember]" value="TRUE" />'
		. ' Theme <i>sticks to</i> to all WordPress webpages after Visitor views a URL with this <code>keyword=value</code> (<b>Advanced Setting</b>, please read below)'; 
}

/**
 * Section text for Section6
 * 
 * Display an explanation of this Section
 *
 */
function jr_mt_advanced_settings_expl() {
	?>
	<p>
	<b>Warning:</b>
	As the name of the section implies, Advanced Settings
	may surprise you with unintended consequences,
	so please be careful.
	</p>
	<p>
	<b>Sticky?</b>
	This setting
	(just above)
	allows the associated <code>keyword=value</code> to
	set the Theme not just for the current WordPress non-Admin webpage,
	but be remembered by the Visitor's browser
	and the same Theme to be used for all future WordPress non-Admin webpages
	viewed by the same Visitor
	(same visitor computer/same visitor username on that computer/same browser)
	until another Sticky query <code>keyword=value</code> URL is encountered.
	Note: A Cookie is used for this purpose. If the visitor's browser refuses Cookies,
	this setting will not work and no error messages will be displayed.
	</p>
	<p>
	<b>Theme for Everything</b>
	(just below)
	simplifies the use of a Theme with Admin panel settings that you need to change frequently,
	when the Theme is only going to be used on one or more Pages or Posts.
	The Theme can be set as the WordPress Current Theme through the Appearance-Themes admin panel,
	and set for specific Pages or Posts using this plugin's settings (above),
	with another Theme specified below as the plugin's default theme ("Theme for Everything").
	</p>
	<?php
}

function jr_mt_echo_current() {
	$settings = get_option( 'jr_mt_settings' );
	jr_mt_themes_field( 'current', $settings['current'], 'jr_mt_settings', TRUE );
	echo '<br />(select blank entry for default: WordPress Current Theme defined in Appearance-Themes, currently <b>' . wp_get_theme()->Name . '</b>)';
}

function jr_mt_validate_settings( $input ) {
	$valid = array();
	foreach ( array( 'all_pages', 'all_posts', 'site_home', 'current' ) as $thing ) {
		$valid[$thing] = $input[$thing];
	}
	
	$settings = get_option( 'jr_mt_settings' );
	$ids = $settings['ids'];
	$query = $settings['query'];
	$remember = $settings['remember'];
	if ( isset ( $input['del_entry'] ) ) {
		foreach ( $input['del_entry'] as $del_entry ) {
			unset( $ids[$del_entry] );
		}
	}
	if ( isset ( $input['del_query_entry'] ) ) {
		global $jr_mt_kwvalsep;
		foreach ( $input['del_query_entry'] as $del_entry ) {
			list( $keyword, $value ) = explode( $jr_mt_kwvalsep, $del_entry );
			unset( $query[$keyword][$value] );
			if ( empty( $query[$keyword] ) ) {
				unset( $query[$keyword] );
			}
			/*	unset() does nothing if a variable or array element does not exist.
			*/
			unset( $remember['query'][$keyword][$value] );
			if ( empty( $remember['query'][$keyword] ) ) {
				unset( $remember['query'][$keyword] );
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
			$validate_url = jr_mt_site_url( $url );
			if ( $validate_url === TRUE ) {
				if ( ( '*' !== $input['add_is_prefix'] ) && ( FALSE !== strpos( $url, '*' ) ) ) {
					add_settings_error(
						'jr_mt_settings',
						'jr_mt_queryerror',
						'Asterisk ("*") only allowed when "URL Prefix with Asterisk" selected: <code>' . $url . '</code>',
						'error'
					);
				} else {						
					extract( jr_mt_url_to_id( $url ) );				
					if ( 'false' === $input['add_is_prefix'] ) {
						if ( $home ) {
							add_settings_error(
								'jr_mt_settings',
								'jr_mt_homeerror',
								'Please use "Select Theme for Site Home" field instead of specifying Site Home URL as an individual entry.',
								'error'
							);
						} else {
							if ( $type == 'admin' ) {
								add_settings_error(
									'jr_mt_settings',
									'jr_mt_adminerror',
									'Admin Page URLs are not allowed because no known Themes alter the appearance of Admin pages: <code>' . $url . '</code>',
									'error'
								);
							} else {
								if ( $id === FALSE ) {
									$key = $page_url;
								} else {
									$key = $id;
								}
							}
						}
					} else {
						if ( parse_url( $url, PHP_URL_QUERY ) === NULL ) {
							if ( '*' === $input['add_is_prefix'] ) {
								$asterisk_not_alone = FALSE;
								$no_asterisk = TRUE;
								$rel_url_dirs = explode( '/', str_replace( '\\', '/', $rel_url ) );
								foreach ( $rel_url_dirs as $dir ) {
									if ( $no_asterisk ) {
										if ( FALSE !== strpos( $dir, '*' ) ) {
											$no_asterisk = FALSE;
											if ( '*' !== $dir ) {
												$asterisk_not_alone = TRUE;
											}
										}
									}
								}
								if ( $no_asterisk ) {
									add_settings_error(
										'jr_mt_settings',
										'jr_mt_queryerror',
										'No Asterisk ("*") specified but "URL Prefix with Asterisk" selected: <code>' . $url . '</code>',
										'error'
									);	
								} else {
									if ( $asterisk_not_alone ) {
										add_settings_error(
											'jr_mt_settings',
											'jr_mt_queryerror',
											'An Asterisk ("*") may only replace a full subdirectory name, not just a portion of it: <code>' . $url . '</code>',
											'error'
										);	
									}
								}
							}
						} else {
							add_settings_error(
								'jr_mt_settings',
								'jr_mt_queryerror',
								'?key=val&key=val Queries are not supported in a URL Prefix: <code>' . $url . '</code>',
								'error'
							);
						}
						$type = $input['add_is_prefix'];
						$key = $rel_url;
					}
					$errors = get_settings_errors();
					if ( empty( $errors ) ) {
						$ids[$key] = array(
							'theme' => $input['add_theme'],
							'type' => $type,
							'id' => $id,
							'page_url' => $page_url,
							'rel_url' => $rel_url,
							'url' => $url
							);
					}
					/*
					$errors = get_settings_errors();
					if ( empty( $errors ) ) {
						//	Here is where to check if URL gives a 404, but it doesn't work, always getting 302, and obliterating Settings Saved message
						if ( 404 == $respcode = wp_remote_retrieve_response_code( wp_remote_head( $url ) ) ) {
							add_settings_error(
								'jr_mt_settings',
								'jr_mt_urlerror',
								"Warning: URL specified ('$url') generated error response $respcode",
								'error'
							);
						}
					}
					*/
				}
			} else {
				add_settings_error(
					'jr_mt_settings',
					'jr_mt_urlerror',
					'URL specified for Individual page/post: <code>' . $url . '</code>' . $validate_url,
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
			if ( isset( $input['add_query_remember'] ) ) {
				$remember['query'][$keyword][$value] = TRUE;
			}
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
	
	$errors = get_settings_errors();
	if ( empty( $errors ) ) {
		add_settings_error(
			'jr_mt_settings',
			'jr_mt_saved',
			'Settings Saved',
			'updated'
		);	
	}
	$valid['ids'] = $ids;
	$valid['query'] = $query;
	$valid['remember'] = $remember;
	return $valid;
}

//	$theme_name is the name of the Theme's folder within the Theme directory
function jr_mt_themes_field( $field_name, $theme_name, $setting, $excl_current_theme ) {
	echo "<select id='$field_name' name='$setting" . "[$field_name]' size='1'>";
	if ( empty( $theme_name ) ) {
		$selected = 'selected="selected"';
	} else {
		$selected = '';
	}
	echo "<option value='' $selected></option>";
	global $jr_mt_themes_cache;
	foreach ( $jr_mt_themes_cache as $folder => $theme_obj ) {
		if ( $excl_current_theme ) {
			if ( ( jr_mt_current_theme( 'stylesheet' ) == $theme_obj['stylesheet'] ) && ( jr_mt_current_theme( 'template' ) == $theme_obj['template'] ) ) {
				//	Skip the Current Theme
				continue;
			}
		}
		if ( $theme_name == $folder ) {
			$selected = 'selected="selected"';
		} else {
			$selected = '';
		}
		$name = $theme_obj->Name;
		echo "<option value='$folder' $selected>$name</option>";
	}
	echo '</select>' . PHP_EOL;
}

?>