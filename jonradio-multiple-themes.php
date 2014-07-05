<?php
/*
Plugin Name: jonradio Multiple Themes
Plugin URI: http://jonradio.com/plugins/jonradio-multiple-themes
Description: Select different Themes for one or more, or all WordPress Pages, Posts or other non-Admin pages.  Or Site Home.
Version: 4.10.1
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
if ( !defined( 'ABSPATH' ) ) exit;

global $jr_mt_file;
$jr_mt_file = __FILE__;

/*	Catch old unsupported version of WordPress before any damage can be done.
*/
if ( version_compare( get_bloginfo( 'version' ), '3.4', '<' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'includes/old-wp.php' );
} else {
	/*	Use $plugin_data['Name'] for the array of incompatible plugins
	*/
	global $jr_mt_incompat_plugins;
	$jr_mt_incompat_plugins = array( 'Theme Test Drive', 'BuddyPress', 'Polylang' );
	
	global $jr_mt_path;
	$jr_mt_path = plugin_dir_path( __FILE__ );
	/**
	* Return Plugin's full directory path with trailing slash
	* 
	* Local XAMPP install might return:
	*	C:\xampp\htdocs\wpbeta\wp-content\plugins\jonradio-multiple-themes/
	*
	*/
	function jr_mt_path() {
		global $jr_mt_path;
		return $jr_mt_path;
	}
	
	global $jr_mt_plugin_basename;
	$jr_mt_plugin_basename = plugin_basename( __FILE__ );
	/**
	* Return Plugin's Basename
	* 
	* For this plugin, it would be:
	*	jonradio-multiple-themes/jonradio-multiple-themes.php
	*
	*/
	function jr_mt_plugin_basename() {
		global $jr_mt_plugin_basename;
		return $jr_mt_plugin_basename;
	}
	
	if ( !function_exists( 'get_plugin_data' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	global $jr_mt_plugin_data;
	$jr_mt_plugin_data = get_plugin_data( __FILE__ );
	$jr_mt_plugin_data['slug'] = basename( dirname( __FILE__ ) );
	
	global $jr_mt_options_cache;
	$all_options = wp_load_alloptions();
	$jr_mt_options_cache['stylesheet'] = $all_options['stylesheet'];
	$jr_mt_options_cache['template'] = $all_options['template'];
	
	/*	Detect initial activation or a change in plugin's Version number

		Sometimes special processing is required when the plugin is updated to a new version of the plugin.
		Also used in place of standard activation and new site creation exits provided by WordPress.
		Once that is complete, update the Version number in the plugin's Network-wide settings.
	*/

	if ( ( FALSE === ( $internal_settings = get_option( 'jr_mt_internal_settings' ) ) ) 
		|| empty( $internal_settings['version'] ) )
		{
		/*	Plugin is either:
			- updated from a version so old that Version was not yet stored in the plugin's settings, or
			- first use after install:
				- first time ever installed, or
				- installed previously and properly uninstalled (data deleted)
		*/

		$old_version = '0.1';
	} else {
		$old_version = $internal_settings['version'];
	}
	
	$settings = get_option( 'jr_mt_settings' );
	if ( empty( $settings ) ) {
		$settings = array(
			'all_pages' => '',
			'all_posts' => '',
			'site_home' => '',
			'current'   => '',
			'query'     => array(),
			'remember'  => array( 'query' => array() ),
			'ids'       => array()
		);
		/*	Add if Settings don't exist, re-initialize if they were empty.
		*/
		update_option( 'jr_mt_settings', $settings );
		/*	New install on this site, very old version or corrupt settings
		*/
		$old_version = $jr_mt_plugin_data['Version'];
	}
	
	require_once( jr_mt_path() . 'includes/functions.php' );
	
	if ( version_compare( $old_version, $jr_mt_plugin_data['Version'], '!=' ) ) {
		if ( !isset( $settings['remember'] ) ) {
			$settings['remember'] = array( 'query' => array() );
		}
		
		if ( isset( $settings['ids'] ) && is_array( $settings['ids'] ) ) {
			$ids = $settings['ids'];
		} else {
			$ids = array();
		}
		
		/*	Create, if internal settings do not exist; update if they do exist
		*/
		$internal_settings['version'] = $jr_mt_plugin_data['Version'];
		update_option( 'jr_mt_internal_settings', $internal_settings );

		/*	Handle all Settings changes made in old plugin versions
		*/
		if ( version_compare( $old_version, '2.1', '<' ) ) {
			unset( $settings['all_admin'] );
			//	Check for Site Home entry, remove it and set Site Home field
			//	And remove all Admin entries (no longer supported)
			if ( isset( $ids[''] ) ) {
				$settings['site_home'] = $ids['']['theme'];
				unset( $ids[''] );
			} else {
				$settings['site_home'] = '';
			}
			foreach ( $ids as $key => $arr ) {
				if ( $arr['type'] == 'admin' ) {
					unset( $ids[$key] );
				}
			}
		}
		if ( version_compare( $old_version, '3.0', '<' ) ) {
			foreach ( $ids as $key => $arr ) {
				if ( strcasecmp( 'http', substr( $arr['rel_url'], 0, 4 ) ) == 0 ) {
					unset( $ids[$key] );
				}
			}
		}
		if ( version_compare( $old_version, '4.1', '<' ) ) {
			/*	Replace %hex with real character to support languages like Chinese
			*/
			foreach ( $ids as $key => $arr ) {
				$newkey = rawurldecode( $key );
				$newarr = $arr;
				unset( $ids[$key] );
				$newarr['page_url'] = rawurldecode( $newarr['page_url'] );
				$newarr['rel_url'] = rawurldecode( $newarr['rel_url'] );
				$newarr['url'] = rawurldecode( $newarr['url'] );
				$ids[$newkey] = $newarr;
			}
		}
		if ( version_compare( $old_version, '4.1.2', '<' ) ) {
			//	Add new Current Theme override option
			$settings['current'] = '';
		}
		if ( version_compare( $old_version, '4.6', '<' ) ) {
			//	Add new Query Keyword override option
			$settings['query'] = array();
		}
		if ( version_compare( $old_version, '4.7', '<' ) ) {
			/*	Change the format of the Query array
			*/
			$query = array();
			foreach ( $settings['query'] as $number => $keyword_array ) {
				foreach ( $keyword_array as $keyword => $theme ) {
					$query[ jr_mt_prep_query_keyword( $keyword ) ]['*'] = $theme;
				}
			}
			$settings['query'] = $query;
		}
		
		$settings['ids'] = $ids;
		update_option( 'jr_mt_settings', $settings );
	}
	
	if ( is_admin() ) {
		//	Admin panel
		require_once( jr_mt_path() . 'includes/admin.php' );
	} else {
		//	Setting of Blank uses WordPress Current Theme value
		if ( trim( $settings['current'] ) ) {
			$jr_mt_options_cache['stylesheet'] = $settings['current'];
			$jr_mt_options_cache['template'] = $settings['current'];
		}
		require_once( jr_mt_path() . 'includes/select-theme.php' );
	}
}

/*	Settings structure:
	code - get_option( 'jr_mt_settings' )
	['all_pages'] => zero length string or folder in Themes directory containing theme to use for All Pages
	['all_posts'] => zero length string or folder in Themes directory containing theme to use for All Posts
	['site_home'] => zero length string or folder in Themes directory containing theme to use for Home Page
	['current'] => zero length string or folder in Themes directory containing theme to override WordPress Current Theme
	['ids']
		[id] - zero length string or WordPress ID of Page, Post, etc.
			['type'] => 'page' or 'post' or 'admin' or 'cat' or 'archive' or 'prefix' or other
			['theme'] => folder in Themes directory containing theme to use
			['id'] => FALSE or WordPress ID of Page, Post, etc.
			['page_url'] => relative URL WordPress page, post, admin, etc. or FALSE
			['rel_url'] => URL relative to WordPress home
			['url'] => original full URL, from Settings page entry by user	
*/

/*
Research Notes:
	The first time one of these Filter Hooks fires, pre_option_stylesheet and pre_option_template, only the following functions can be used to help determine "where" you are in the site:
	- is_admin()
	- is_user_logged_in()
	- get_option("page_on_front") - ID of home page; zero if Reading Settings NOT set to a Static Page of a WordPress Page
*/

?>