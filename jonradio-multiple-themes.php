<?php
/*
Plugin Name: jonradio Multiple Themes
Plugin URI: http://jonradio.com/plugins/jonradio-multiple-themes
Description: Select different Themes for one or more WordPress Pages, Posts or other non-Admin pages.  Or Site Home.
Version: 5.0.1
Author: jonradio
Author URI: http://jonradio.com/plugins
License: GPLv2
*/

/*  Copyright 2014  jonradio  (email : info@jonradio.com)

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

/*	For Hooks, when it needs to run first or last.
*/
DEFINE( 'JR_MT_RUN_FIRST', 1 );
DEFINE( 'JR_MT_RUN_SECOND', JR_MT_RUN_FIRST + 1 );
DEFINE( 'JR_MT_RUN_LAST', 999 );

DEFINE( 'JR_MT_WP_GET_THEMES_ACTION', 'plugins_loaded' );

DEFINE( 'JR_MT_FILE', __FILE__ );

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
				['url'], ['url_prefix'] and ['url_asterisk']
					['url'] => URL
					['prep'] => URL array created by jr_mt_prep_url()
					['theme'] => folder in Themes directory containing theme to use
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
	
	/*	p2 runs in Admin, so must also execute this code in Admin, too.
	*/
	require_once( jr_mt_path() . 'includes/select-theme.php' );
	
	if ( is_admin() ) {
		require_once( jr_mt_path() . 'includes/admin-functions.php' );
		//	Admin panel
		require_once( jr_mt_path() . 'includes/admin.php' );
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