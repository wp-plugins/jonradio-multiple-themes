<?php
/*
Plugin Name: jonradio Multiple Themes
Plugin URI: http://zatzlabs.com/plugins/
Description: Select different Themes for one or more WordPress Pages, Posts or other non-Admin pages.  Or Site Home.
Version: 6.0.1
Author: David Gewirtz
Author URI: http://zatzlabs.com/plugins/
License: GPLv2
*/

/*  Copyright 2014  jonradio  (email : info@zatz.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*	Exit if .php file accessed directly
*/
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

DEFINE( 'JR_MT_HOME_URL', home_url() );
DEFINE( 'JR_MT_FILE', __FILE__ );

/*	For Hooks, when it needs to run first or last.
*/
DEFINE( 'JR_MT_RUN_FIRST', 1 );
DEFINE( 'JR_MT_RUN_SECOND', JR_MT_RUN_FIRST + 1 );
DEFINE( 'JR_MT_RUN_LAST', 999 );

DEFINE( 'JR_MT_WP_GET_THEMES_ACTION', 'plugins_loaded' );

/*	Catch old unsupported version of WordPress before any damage can be done.
*/
if ( version_compare( get_bloginfo( 'version' ), '3.4', '<' ) ) {
	require_once( plugin_dir_path( JR_MT_FILE ) . 'includes/old-wp.php' );
} else {
	/*	Use $plugin_data['Name'] for the array of incompatible plugins
	*/
	global $jr_mt_incompat_plugins;
	$jr_mt_incompat_plugins = array( 'Theme Test Drive' );  // removed for V5: 'BuddyPress', 'Polylang'
	
	require_once( plugin_dir_path( JR_MT_FILE ) . 'includes/functions.php' );
	
	if ( is_admin() ) {
		/* 	Add Link to the plugin's entry on the Admin "Plugins" Page, for easy access
			
			Placed here to avoid the confusion of not displaying it during a Version conversion of Settings
		*/
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
			/*	The "page=" query string value must be equal to the slug
				of the Settings admin page.
			*/
			array_unshift( $links, '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=jr_mt_settings' . '">Settings</a>' );
			return $links;
		}
		
		/*	Store $wp->public_query_vars for when they are needed before 'setup_theme' Action
		*/
		add_action( 'setup_theme', 'jr_mt_wp_query_vars', JR_MT_RUN_FIRST );
		function jr_mt_wp_query_vars() {
			if ( FALSE !== ( $internal_settings = get_option( 'jr_mt_internal_settings' ) ) ) {
				global $wp;
				if ( ( !isset( $internal_settings['query_vars'] ) )
					|| ( $internal_settings['query_vars'] !== $wp->public_query_vars ) ) {
					/*	Only do an expensive Database Write when you have to,
						i.e. - when value has changed.
					*/
					$internal_settings['query_vars'] = $wp->public_query_vars;
					update_option( 'jr_mt_internal_settings', $internal_settings );
				}
			}
		}
	}

	
	/*	Check for missing Settings and set them to defaults.
		On first use, this means initializing all Settings to their defaults.
	*/
	jr_mt_missing_settings( 'jr_mt_settings',
		array(
			/*	Settings structure:
				code - get_option( 'jr_mt_settings' )
				['all_pages'] => zero length string or folder in Themes directory containing theme to use for All Pages
				['all_posts'] => zero length string or folder in Themes directory containing theme to use for All Posts
				['site_home'] => zero length string or folder in Themes directory containing theme to use for Home Page
				['current'] => zero length string or folder in Themes directory containing theme to override WordPress Current Theme
				['query']
					[keyword]
						[value] or ['*'] => folder in Themes directory containing theme to use
				['remember']
					['query']
						[keyword]
							[value] => TRUE
				['override']
					['query']
						[keyword]
							[value] => TRUE
				['query_present'] => TRUE or FALSE
				['url'], ['url_prefix'] and ['url_asterisk'] - array with each entry:
					['url'] => URL
					['prep'][] => array of URL arrays created by jr_mt_prep_url(), with array index matching the array index of ['aliases']
					['rel_url'] => Relative URL based on Site Address (URL) that admin entered the URL
					['id'] => Post ID (Page, Post or Attachment), if known and if relevant
					['id_kw'] => 'page_id', 'p' or 'attachment_id'
					['theme'] => folder in Themes directory containing theme to use
				
				Added in Version 6.0:
				['aliases'][] - array of Alias URLs that could replace 'home' in URL of this site,
						with each entry:
					['url'] => URL
					['prep'] => URL array created by jr_mt_prep_url()
					['home'] => TRUE if this is Site Address (URL) field value from WordPress General Settings,
						which is stored here to determine when the WordPress General Setting is changed				
				
				Prior to Version 5.0:
				['ids']
					[id] - zero length string or WordPress ID of Page, Post, etc.
						['type'] => 'page' or 'post' or 'admin' or 'cat' or 'archive' or 'prefix' or other
						['theme'] => folder in Themes directory containing theme to use
						['id'] => FALSE or WordPress ID of Page, Post, etc.
						['page_url'] => relative URL WordPress page, post, admin, etc. or FALSE
						['rel_url'] => URL relative to WordPress home
						['url'] => original full URL, from Settings page entry by user	
			*/
			'aliases'       => jr_mt_init_aliases(),
			'all_pages'     => '',
			'all_posts'     => '',
			'site_home'     => '',
			'current'       => '',
			'query'         => array(),
			'remember'      => array( 'query' => array() ),
			'override'      => array( 'query' => array() ),
			'query_present' => FALSE,
			'url'           => array(),
			'url_prefix'    => array(),
			'url_asterisk'  => array()
		)
	);
	
	/*	Detect initial activation or a change in plugin's Version number

		Sometimes special processing is required when the plugin is updated to a new version of the plugin.
		Also used in place of standard activation and new site creation exits provided by WordPress.
		Once that is complete, update the Version number in the plugin's Network-wide settings.
	*/
	if ( FALSE === ( $internal_settings = get_option( 'jr_mt_internal_settings' ) ) ) {
		/*	New install or Plugin was deleted previously, erasing all its Settings
		*/
		$old_version = $jr_mt_plugin_data['Version'];
		$version_change = TRUE;
		$update_version_setting = FALSE;
	} else {
		if ( empty( $internal_settings['version'] ) ) {
			/*	Internal Settings are corrupt, or extremely old.
			*/
			$old_version = '0';
			$version_change = TRUE;
			$update_version_setting = TRUE;
		} else {
			$old_version = $internal_settings['version'];
			$version_change = version_compare( $old_version, $jr_mt_plugin_data['Version'], '!=' );
			$update_version_setting = $version_change;
		}
	}
	/*	Create and initialize any or all internal settings that do not exist.
	*/
	jr_mt_missing_settings( 'jr_mt_internal_settings',
		array(
			'version'   => $jr_mt_plugin_data['Version'],
			'permalink' => get_option( 'permalink_structure' )
		)
	);
	
	if ( $version_change ) {		
		/*	Handle all Settings changes made in old plugin versions
		*/
		if ( version_compare( $old_version, '5.0', '<' ) ) {
			$settings = get_option( 'jr_mt_settings' );
			if ( !empty( $settings['ids'] ) ) {
				/*	Convert 'ids' array to 'urls' array in Settings jr_mt_settings
				
					Signal that a conversion is required.
				*/
				$internal_settings = get_option( 'jr_mt_internal_settings' );
				$internal_settings['ids'] = TRUE;
				update_option( 'jr_mt_internal_settings', $internal_settings );				
			}
		}
		if ( version_compare( $old_version, '6.0', '<' ) ) {
			/*	Check if conversion is needed:
				- see if [url*] are all empty arrays - no conversion required
				- look for any [url*]['rel_url'] - already converted from pre-V6
			*/
			$settings = get_option( 'jr_mt_settings' );
			if ( is_array( $settings ) ) {
				foreach ( $settings as $key => $array ) {
					if ( 'url' === substr( $key, 0, 3 ) ) {
						if ( !empty( $array ) ) {
							/*	Convert 'url'* settings to arrays, one for each Site Alias,
								in Settings jr_mt_settings.
							
								Signal that a conversion is required.
							*/
							$internal_settings = get_option( 'jr_mt_internal_settings' );
							$internal_settings['v6conv'] = TRUE;
							update_option( 'jr_mt_internal_settings', $internal_settings );
							break;
						}
					}
				}
			}
		}
	}

	/*	Only Update if I have to.
	*/
	if ( $update_version_setting ) {
		$internal_settings = get_option( 'jr_mt_internal_settings' );
		$internal_settings['version'] = $jr_mt_plugin_data['Version'];
		update_option( 'jr_mt_internal_settings', $internal_settings );
	}

	/*	Do the Version 5.0 Upgrade, if required.
	*/
	$internal_settings = get_option( 'jr_mt_internal_settings' );
	if ( isset( $internal_settings['ids'] ) ) {
		require_once( jr_mt_path() . 'includes/upgradev5.php' );
	}

	/*	Do the Version 6.0 Upgrade, if required.
	*/
	if ( isset( $internal_settings['v6conv'] ) ) {
		require_once( jr_mt_path() . 'includes/upgradev6.php' );
	/*	Upgrade doesn't occur until late in the WordPress "cycle",
		so best not to risk failure on old settings.
	*/
	} else {
	
		/*	p2 runs in Admin, so must also execute this code in Admin, too.
		*/
		require_once( jr_mt_path() . 'includes/select-theme.php' );
		
		if ( is_admin() ) {
			require_once( jr_mt_path() . 'includes/admin-functions.php' );
			//	Admin panel
			require_once( jr_mt_path() . 'includes/admin.php' );
		}
	}
}					
									
/*
Research Notes:
	The first time one of these Filter Hooks fires, pre_option_stylesheet and pre_option_template, only the following functions can be used to help determine "where" you are in the site:
	- is_admin()
	- is_user_logged_in()
	- get_option("page_on_front") - ID of home page; zero if Reading Settings NOT set to a Static Page of a WordPress Page
*/

?>