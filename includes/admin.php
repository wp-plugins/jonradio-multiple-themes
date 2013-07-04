<?php

//	Admin Page

require_once( jr_mt_path() . 'includes/admin-other.php' );

add_action( 'admin_menu', 'jr_mt_admin_hook' );
//	Runs just after admin_init (below)

/**
 * Add Admin Menu item for plugin
 * 
 * Plugin needs its own Page in the Settings section of the Admin menu.
 *
 */
function jr_mt_admin_hook() {
	//  Add Settings Page for this Plugin
	global $jr_mt_plugin_data;
	$jr_mt_plugin_data = array_merge( $jr_mt_plugin_data, jr_readme() );
	add_theme_page( $jr_mt_plugin_data['Name'], 'Multiple Themes plugin', 'manage_options', 'jr_mt_settings', 'jr_mt_settings_page' );
	add_options_page( $jr_mt_plugin_data['Name'], 'Multiple Themes plugin', 'manage_options', 'jr_mt_settings', 'jr_mt_settings_page' );
}

/**
 * Settings page for plugin
 * 
 * Display and Process Settings page for this plugin.
 *
 */
function jr_mt_settings_page() {
	global $jr_mt_plugin_data;
	global $jr_mt_themes_cache;
	$jr_mt_themes_cache = wp_get_themes();
	global $jr_mt_plugins_cache;
	$jr_mt_plugins_cache = get_plugins();
	add_thickbox();
	echo '<div class="wrap">';
	screen_icon( 'plugins' );
	echo '<h2>' . $jr_mt_plugin_data['Name'] . '</h2>';
	
	//	Required because it is only called automatically for Admin Pages in the Settings section
	settings_errors( 'jr_mt_settings' );
	
	$theme = wp_get_theme()->Name;
	global $jr_mt_options_cache;

	$current_wp_version = get_bloginfo( 'version' );
	if ( version_compare( $current_wp_version, $jr_mt_plugin_data['Requires at least'], '<' ) ) {
		//	Plugin requires newer version of WordPress
		echo '<h3>Error</h3><p>Here is the problem:<ul><li> &raquo; This Plugin (' . $jr_mt_plugin_data['Name'] 
			. ') does not support versions of WordPress before WordPress Version '
			. $jr_mt_plugin_data['Requires at least'] . '.</li><li> &raquo; You are running WordPress Version ' . $current_wp_version 
			. '.</li><li> &raquo; This Plugin uses the wp_get_themes() function which became available in Version '
			. '3.4.0 of WordPress.</li></ul></p>';
	} else {
		if ( version_compare( $current_wp_version, $jr_mt_plugin_data['Tested up to'], '>' ) ) {
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
						. '<p>The plugin will probably still work with your newer version of WordPress, but you need to be aware of the issue.</p>';
				} else {
					if ( version_compare( $jr_mt_plugin_data['Version'], $directory->version, '=' ) ) {
						/*	The latest version of the Plugin has been installed, 
							but the readme.txt has been updated in the WordPress Plugin Directory
							to indicate that it now supports the installed version of WordPress.
						
							Latest version of Plugin has already been installed, but readme.txt is out of date,
							so update readme.txt.																*/
						
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
			<li> &raquo; The Site Home</li>
			<li> &raquo; A Specific Page</li>
			<li> &raquo; A Specific Post</li>
			<li> &raquo; Any other non-Admin page that has its own Permalink; for example, a specific Archive or Category page</li>
			</ul>
			</p>
			<h3>Important Notes</h3>
			<?php
			if ( function_exists('is_multisite') && is_multisite() ) {
				echo "In a WordPress Network (AKA Multisite), Themes must be <b>Network Enabled</b> before they will appear as Available Themes on individual sites' Appearance-Themes panel.";
			}
			echo '<p>';
			echo "The Current Theme is <b>$theme</b>. You will not normally need to specify it in any of the Settings on this page. The only exception would be if you specify a different Theme for All Pages or All Posts and wish to use the Current Theme for a specific Page, Post or other non-Admin page."; 
			echo '</p>';
			if ( $current ) {
				echo '<p>This Plugin (' . $jr_mt_plugin_data['Name'] . ') has been tested with the version of WordPress you are currently running: ' 
					. $current_wp_version . '</p>';
			}
			if ( jr_mt_plugin_update_available() ) {
				echo '<p>A new version of this Plugin (' . $jr_mt_plugin_data['Name'] . ') is available from the WordPress Repository.'
					. ' We strongly recommend updating ASAP because new versions fix problems that users like yourself have reported to us.'
					. ' <a class="thickbox" title="' . $jr_mt_plugin_data['Name'] . '" href="' . network_admin_url()
					. 'plugin-install.php?tab=plugin-information&plugin=' . $jr_mt_plugin_data['slug']
					. '&section=changelog&TB_iframe=true&width=640&height=768">Click here</a> for more details.</p>';
			}
			echo '<form action="options.php" method="POST">';
			
			//	Plugin Settings are displayed and entered here:
			settings_fields( 'jr_mt_settings' );
			do_settings_sections( 'jr_mt_settings_page' );
			echo '<p><input name="save" type="submit" value="Save Changes" class="button-primary" /></p></form>';
		}
	}
	echo '<hr /><h3>System Information</h3><p>You are currently running:<ul>';
	echo "<li> &raquo; The {$jr_mt_plugin_data['Name']} plugin Version {$jr_mt_plugin_data['Version']}</li>";
	echo "<li> &nbsp; &raquo;&raquo; The Path to the plugin's directory is " . rtrim( jr_mt_path(), '/' ) . '<li>';
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
	add_settings_section( 'jr_mt_all_settings_section', 
		'For All Pages, All Posts and/or Site Home', 
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
	$settings = get_option( 'jr_mt_settings' );
	$ids = $settings['ids'];
	if ( !empty( $ids) ) {
		add_settings_section( 'jr_mt_delete_settings_section', 
			'To Display or Delete Theme Selections for Individual Pages or Posts', 
			'jr_mt_delete_settings_expl', 
			'jr_mt_settings_page' 
		);
		add_settings_field( 'del_entry', 'Entries:', 'jr_mt_echo_delete_entry', 'jr_mt_settings_page', 'jr_mt_delete_settings_section' );
	}
	add_settings_section( 'jr_mt_single_settings_section', 
		'For An Individual Page, Post or other non-Admin page;<br />or a group of pages, specified by URL Prefix', 
		'jr_mt_single_settings_expl', 
		'jr_mt_settings_page' 
	);
	add_settings_field( 'add_theme', 'Theme', 'jr_mt_echo_add_theme', 'jr_mt_settings_page', 'jr_mt_single_settings_section' );
	add_settings_field( 'add_path_id', 'URL of Page, Post, Prefix or other', 'jr_mt_echo_add_path_id', 'jr_mt_settings_page', 'jr_mt_single_settings_section' );
	add_settings_field( 'add_is_prefix', 'Select here if URL is a Prefix', 'jr_mt_echo_add_is_prefix', 'jr_mt_settings_page', 'jr_mt_single_settings_section' );
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
	</p>
	<p>
	You can delete any of these entries by filling in the check box beside each one.
	To change the Theme for an entry, add the same entry with a different Theme in the section below this one.</p>
	<?php
}

function jr_mt_echo_delete_entry() {
	$entry_num = 0;
	$settings = get_option( 'jr_mt_settings' );
	foreach ( $settings['ids'] as $path_id => $opt_array ) {
		++$entry_num;
		echo "Delete <input type='checkbox' id='del_entry' name='jr_mt_settings[del_entry][]' value='$path_id' /> &nbsp; Theme="
			. wp_get_theme( $opt_array['theme'] )->Name . '; ';
		if ( $path_id == '' ) {
			echo 'Site=<a href="' . get_home_url() . '" target="_blank">Home</a>';
		} else {
			if ( $opt_array['type'] == 'prefix' ) {
				echo 'Prefix=<a href="' . get_home_url() . "/$path_id" . '" target="_blank">' . "$path_id</a>";
			} else {
				if ( $opt_array['type'] == 'cat' ) {
					echo 'Category=<a href="' . get_home_url() . '/?cat=' . $opt_array['id'] . '" target="_blank">' . get_cat_name( $opt_array['id'] ) . '</a>';
				} else {
					if ( $opt_array['type'] == 'archive' ) {
						echo 'Archive=<a href="' . get_home_url() . '/?m=' . $opt_array['id'] . '" target="_blank">' . $opt_array['id'] . '</a>';
					} else {
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
		echo '<br />';
	}
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
	Or for a group of pages which have URLs that all begin with the same characters ("Prefix").
	</p>
	<p>
	Then cut and paste the URL of the desired Page, Post, Prefix or other non-Admin page.
	And click the <b>Save Changes</b> button to add the entry.
	</p>
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
	echo trim( get_home_url(), '\ /' ) . '/';
}

function jr_mt_echo_add_is_prefix() {
	?>
	<input type="checkbox" id="add_is_prefix" name="jr_mt_settings[add_is_prefix]" value="true" /> Anything that begins with this URL will use this Theme
	<?php
}

function jr_mt_validate_settings( $input ) {
	$valid = array();
	foreach ( array( 'all_pages', 'all_posts', 'site_home' ) as $thing ) {
		$valid[$thing] = $input[$thing];
	}
	
	$settings = get_option( 'jr_mt_settings' );
	$ids = $settings['ids'];
	if ( isset ( $input['del_entry'] ) ) {
		foreach ( $input['del_entry'] as $del_entry ) {
			unset( $ids[$del_entry] );
		}
	}
	
	$url = rawurldecode( trim( $input['add_path_id'] ) );
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
				extract( jr_mt_url_to_id( $url ) );
				if ( isset ( $input['add_is_prefix'] ) && ( $input['add_is_prefix'] == "true" ) ) {
					if ( parse_url( $url, PHP_URL_QUERY ) === NULL ) {
						$ids[$rel_url] = array(
							'theme' => $input['add_theme'],
							'type' => 'prefix',
							'id' => $id,
							'page_url' => $page_url,
							'rel_url' => $rel_url,
							'url' => $url
							);
					} else {
						add_settings_error(
							'jr_mt_settings',
							'jr_mt_queryerror',
							'?key=val&key=val Queries are not supported in a URL Prefix',
							'error'
						);		
					
					}
				} else {
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
								'Admin Page URLs are not allowed because no known Themes alter the appearance of Admin pages.',
								'error'
							);
						} else {
							if ( $id === FALSE ) {
								$key = $page_url;
							} else {
								$key = $id;
							}
							$ids[$key] = array(
								'theme' => $input['add_theme'],
								'type' => $type,
								'id' => $id,
								'page_url' => $page_url,
								'rel_url' => $rel_url,
								'url' => $url
								);
						}
					}
				}
			} else {
				add_settings_error(
					'jr_mt_settings',
					'jr_mt_urlerror',
					"Invalid URL specified for Individual page/post: '$url'. $validate_url",
					'error'
				);			
			}
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