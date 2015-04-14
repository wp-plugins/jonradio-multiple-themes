<?php
/*
Plugin Name: jonradio Multiple Themes
Plugin URI: http://zatzlabs.com/plugins/
Description: Select different Themes for one or more, or all WordPress Pages, Posts or other non-Admin pages.  Or Site Home.
Version: 4.2
Author: David Gewirtz
Author URI: http://zatzlabs.com/plugins/
License: GPLv2
*/

/*  Copyright 2013  jonradio  (email : info@zatz.com)

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

//	Exit if .php file accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


global $jr_mt_incompat_plugins;
$jr_mt_incompat_plugins = array( 'Theme Test Drive', 'BuddyPress' );

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
	
register_activation_hook( __FILE__, 'jr_mt_activate' );
register_deactivation_hook( __FILE__, 'jr_mt_deactivate' );

function jr_mt_activate( $network_wide ) {
	if ( $network_wide ) {
		global $wpdb, $site_id;
		$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = $site_id" );
		foreach ( $blogs as $blog_obj ) {
			if ( switch_to_blog( $blog_obj->blog_id ) ) {
				//	We know the Site actually exists
				jr_mt_activate1();
			}
		}
		restore_current_blog();
	} else {
		jr_mt_activate1();
	}
}

function jr_mt_activate1() {
	$settings = array(
		'all_pages' => '',
		'all_posts' => '',
		'site_home' => '',
		'current'   => '',
		'ids'       => array()
	);
	//	Nothing happens if Settings already exist
	add_option( 'jr_mt_settings', $settings );
	
	global $jr_mt_plugin_data;
	$internal_settings = array(
		'version' => $jr_mt_plugin_data['Version']
	);	// Only records you version when plugin installed, not current version
	add_option( 'jr_mt_internal_settings', $internal_settings );	//	Nothing happens if Settings already exist
}

add_action( 'wpmu_new_blog', 'jr_mt_new_site', 10, 6 );

function jr_mt_new_site( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
		switch_to_blog( $blog_id );
		jr_mt_activate1();
		restore_current_blog();
	}
}

function jr_mt_deactivate() {
	//	Nothing (yet)
}

jr_mt_version_check();

function jr_mt_version_check() {
	//	Check for Plugin Version update (Deactivate and Activate Hooks not fired)
	$internal_settings = get_option( 'jr_mt_internal_settings' );
	if ( $internal_settings ) {	//	Just in case Activation has not occurred yet
		global $jr_mt_plugin_data;
		if ( version_compare( $internal_settings['version'], $jr_mt_plugin_data['Version'], '<' ) ) {
			$settings = get_option( 'jr_mt_settings' );
			if ( isset( $settings['ids'] ) ) {
				$ids = $settings['ids'];
			} else {
				$ids = array();
			}
			if ( version_compare( $internal_settings['version'], '2.1', '<' ) ) {
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
			if ( version_compare( $internal_settings['version'], '3.0', '<' ) ) {
				foreach ( $ids as $key => $arr ) {
					if ( strcasecmp( 'http', substr( $arr['rel_url'], 0, 4 ) ) == 0 ) {
						unset( $ids[$key] );
					}
				}
			}
			if ( version_compare( $internal_settings['version'], '4.1', '<' ) ) {
				//	Replace %hex with real character to support languages like Chinese
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
			if ( version_compare( $internal_settings['version'], '4.1.2', '<' ) ) {
				//	Add new Current Theme override option
				$settings['current'] = '';
			}
			$settings['ids'] = $ids;
			update_option( 'jr_mt_settings', $settings );
			$internal_settings['version'] = $jr_mt_plugin_data['Version'];
			update_option( 'jr_mt_internal_settings', $internal_settings );
		}
	}
}

require_once( jr_mt_path() . 'includes/functions.php' );
require_once( jr_mt_path() . 'includes/select-theme.php' );

if ( is_admin() ) {
	//	Admin panel
	require_once( jr_mt_path() . 'includes/admin.php' );
} else {
	$settings = get_option( 'jr_mt_settings' );
	//	Setting of Blank uses WordPress Current Theme value
	if ( trim( $settings['current'] ) ) {
		$jr_mt_options_cache['stylesheet'] = $settings['current'];
		$jr_mt_options_cache['template'] = $settings['current'];
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