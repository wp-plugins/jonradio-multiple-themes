<?php

add_action( 'all_admin_notices', 'jr_mt_all_admin_notice' );
//	Runs after admin_menu hook

function jr_mt_all_admin_notice() {
	if ( 
		in_array(
				get_current_user_id(), 
				get_users( 
					array(
						'role' => 'administrator',
						'fields' => 'ID'
					)
				) 
			) 
		)
	{
		//	Administrators only see this:
		// echo '<div class="error">Should Show on Every Admin Page</div>';
	}
}

// Add Link to the plugin's entry on the Admin "Plugins" Page, for easy access
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
	// The "page=" query string value must be equal to the slug
	// of the Settings admin page.
	array_push( $links, '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=jr_mt_settings' . '">Settings</a>' );
	return $links;
}

?>