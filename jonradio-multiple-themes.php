<?php
/*
Plugin Name: jonradio Multiple Themes
Plugin URI: http://jonradio.com/plugins/jonradio-multiple-themes
Description: Select different Themes for one or more, or all WordPress Pages, Posts or other non-Admin pages.  Or Site Home.
Version: 3.3
Author: jonradio
Author URI: http://jonradio.com/plugins
License: GPLv2
*/

/*  Copyright 2012  jonradio  (email : info@jonradio.com)

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

global $jr_mt_plugin_folder;
$jr_mt_plugin_folder = basename( dirname( __FILE__ ) );

function jr_mt_plugin_folder() {
	global $jr_mt_plugin_folder;
	return $jr_mt_plugin_folder;
}

global $jr_mt_path;
$jr_mt_path = plugin_dir_path( __FILE__ );
function jr_mt_path() {
	global $jr_mt_path;
	return $jr_mt_path;
}

global $jr_mt_plugin_basename;
$jr_mt_plugin_basename = plugin_basename( __FILE__ );

if ( !function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

global $jr_mt_plugin_data;
$jr_mt_plugin_data = get_plugin_data( __FILE__ );

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
			$settings['ids'] = $ids;
			update_option( 'jr_mt_settings', $settings );
			$internal_settings['version'] = $jr_mt_plugin_data['Version'];
			update_option( 'jr_mt_internal_settings', $internal_settings );
		}
	}
}

require_once( jr_mt_path() . 'includes/functions.php' );

//	Do not try and select a Theme for Admin Pages
//	Check that template and stylesheet have the same value for the Current Theme, as Plugin expects this to be true.
global $jr_mt_options_cache;
if ( $jr_mt_options_cache['template'] == $jr_mt_options_cache['stylesheet'] ) {
	require_once( jr_mt_path() . 'includes/select-theme.php' );
}

if ( is_admin() ) {
	//	Admin panel
	require_once( jr_mt_path() . 'includes/admin.php' );
}

/*
Research Notes:
	The first time one of these Filter Hooks fires, pre_option_stylesheet and pre_option_template, only the following functions can be used to help determine "where" you are in the site:
	- is_admin()
	- is_user_logged_in()
	- get_option("page_on_front") - ID of home page; zero if Reading Settings NOT set to a Static Page of a WordPress Page
*/

?>