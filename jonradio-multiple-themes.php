<?php
/*
Plugin Name: jonradio Multiple Themes
Plugin URI: http://zatzlabs.com/plugins/
Description: Select different Themes for one or more, or all WordPress Pages, Posts or Admin Panels.  Or Site Home.
Version: 1.1
Author: David Gewirtz
Author URI: http://zatzlabs.com/plugins/
License: GPLv2
*/

/*  Copyright 2012  jonradio  (email : info@zatz.com)

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

//	Limitation:  This plugin does not currently support Theme usage that involves the stylesheet and template names not being the same.

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
		'all_admin' => '',
		'ids'       => array()
	);
	//	Nothing happens if Settings already exist
	add_option( 'jr_mt_settings', $settings );
	
	$plugin_data = get_plugin_data( __FILE__ );
	$version = $plugin_data['Version'];
	$internal_settings = array(
		'version' => $version
	);
	//	Nothing happens if Settings already exist
	add_option( 'jr_mt_internal_settings', $internal_settings );
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

require_once( jr_mt_path() . 'includes/select-theme.php' );

if ( is_admin() ) {
	//	Admin panel
	require_once( jr_mt_path() . 'includes/admin.php' );
}

require_once( jr_mt_path() . 'includes/functions.php' );

/*
Research Notes:
	The first time one of these Filter Hooks fires, pre_option_stylesheet and pre_option_template, only the following functions can be used to help determine "where" you are in the site:
	- is_admin()
	- is_user_logged_in()
	- get_option("page_on_front") - ID of home page; zero if Reading Settings NOT set to a Static Page of a WordPress Page
*/

?>