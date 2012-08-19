<?php
//	Ensure call comes from WordPress, not a hacker.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

/*  Remove any tables, options, and such created by this Plugin  */
if ( function_exists('is_multisite') && is_multisite() ) {
	global $wpdb, $site_id;
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = $site_id" );
	foreach ($blogs as $blog_obj) {
		delete_blog_option( $blog_obj->blog_id, 'jr_mt_settings' );
		delete_blog_option( $blog_obj->blog_id, 'jr_mt_internal_settings' );
	}
} else {
	delete_option( 'jr_mt_settings' );
	delete_option( 'jr_mt_internal_settings' );
}
?>