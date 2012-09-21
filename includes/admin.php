<?php
//	Admin Page

add_action( 'admin_menu', 'jr_mt_admin_hook' );

/**
 * Add Admin Menu item for plugin
 * 
 * Plugin needs its own Page in the Settings section of the Admin menu.
 *
 */
function jr_mt_admin_hook() {
	//  Add Settings Page for this Plugin
	add_theme_page( 'jonradio Multiple Themes', 'Multiple Themes plugin', 'manage_options', 'jr_mt_settings', 'jr_mt_settings_page' );
	add_options_page( 'jonradio Multiple Themes', 'Multiple Themes plugin', 'manage_options', 'jr_mt_settings', 'jr_mt_settings_page' );
}

/**
 * Settings page for plugin
 * 
 * Display and Process Settings page for this plugin.
 *
 */
function jr_mt_settings_page() {
	echo '<div class="wrap">';
	screen_icon( 'plugins' );
	echo '<h2>jonradio Multiple Themes</h2>';
	
	//	Required because it is only called automatically for Admin Pages in the Settings section
	settings_errors( 'jr_mt_settings' );
	
	$theme = wp_get_theme()->Name;
	global $jr_mt_options_cache;
	if ( $jr_mt_options_cache['template'] == $jr_mt_options_cache['stylesheet'] ) {
		?>
		<p>This plugin allows you to selectively change the Theme you have selected as your <b>Current Theme</b> in <b>Appearance-Themes</b> on the Admin panels.
		You can choose from any of the <b>Available Themes</b> listed on the Appearance-Themes Admin panel for:
		<ul>
		<li> &raquo; All Pages</li>
		<li> &raquo; All Posts</li>
		<li> &raquo; The Site Home</li>
		<li> &raquo; A Specific Page</li>
		<li> &raquo; A Specific Post</li>
		<li> &raquo; Any other non-Admin page that has its own Permalink; for example, a specific Archive or Category page</li>
		</ul>
		<?php
		if ( function_exists('is_multisite') && is_multisite() ) {
			echo "In a WordPress Network (AKA Multisite), Themes must be <b>Network Enabled</b> before they will appear as Available Themes on individual sites' Appearance-Themes panel.";
		}
		echo '</p>';
		echo '<p>';
		echo "The Current Theme is <b>$theme</b>. You will not normally need to specify it in any of the Settings on this page. The only exception would be if you specify a different Theme for All Pages or All Posts and wish to use the Current Theme for a specific Page, Post or other non-Admin page."; 
		echo '</p>';
		echo '<form action="options.php" method="POST">';
		
		//	Plugin Settings are displayed and entered here:
		settings_fields( 'jr_mt_settings' );
		do_settings_sections( 'jr_mt_settings_page' );
		echo '<p><input name="save" type="submit" value="Save Changes" class="button-primary" /></p></form>';
	} else {
		echo '<p>Please report this problem to the Plugin Author:<br />';
		echo "Stylesheet and Template names do not match for Theme $theme: " . $jr_mt_options_cache['stylesheet'] . ' v.s. ' . $jr_mt_options_cache['template'];
		global $jr_mt_plugin_data;
		echo '</p><p><a href="' . $jr_mt_plugin_data['AuthorURI'] . '"' . ">Click here</a> to get to The Plugin Author's page where you can click Contact Us in the menu bar.</p>";
	}
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
		'For An Individual Page, Post or other non-Admin page', 
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
	any other non-Admin pages that has its own Permalink; for example, a specific Archive or Category page.
	<p>
	</p>
	Then cut and paste the URL of the desired Page, Post or other non-Admin page.
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
	
	$url = trim( $input['add_path_id'] );
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
	$valid['ids'] = $ids;
	return $valid;
}

// Add Link to the plugin's entry on the Admin "Plugins" Page, for easy access
global $jr_mt_plugin_basename;
add_filter( "plugin_action_links_$jr_mt_plugin_basename", 'jr_mt_plugin_action_links', 10, 1 );

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

function jr_mt_themes_field( $field_name, $theme_name, $setting, $excl_current_theme ) {
	echo "<select id='$field_name' name='$setting" . "[$field_name]' size='1'>";
	if ( empty( $theme_name ) ) {
		$selected = 'selected="selected"';
	} else {
		$selected = '';
	}
	echo "<option value='' $selected></option>";
	foreach ( wp_get_themes() as $folder => $theme_obj ) {
		if ( $excl_current_theme ) {
			if ( jr_mt_current_theme() == $folder ) {
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