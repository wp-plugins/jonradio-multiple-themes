<?php
//	Select the relevant Theme

add_filter( 'pre_option_stylesheet', 'jr_mt_stylesheet' );
add_filter( 'pre_option_template', 'jr_mt_template' );

function jr_mt_stylesheet() {
	return jr_mt_theme( 'stylesheet' );
}

function jr_mt_template() {
	return jr_mt_theme( 'template' );
}

function jr_mt_theme( $option ) {
	/*	The hooks that (indirectly) call this function are called repeatedly by WordPress, 
		so do the checking once and store the values in a global array.
	*/
	global $jr_mt_theme;
	if ( !isset( $jr_mt_theme ) ) {
		$jr_mt_theme = array();
	}
	if ( !isset( $jr_mt_theme[$option] ) ) {	
		$theme = jr_mt_chosen();
		if ( $theme === FALSE ) {
			// $jr_mt_theme[$option] = jr_mt_current_theme( $option );  if stylesheet and template are ever different
			$jr_mt_theme['template'] = jr_mt_current_theme();
			$jr_mt_theme['stylesheet'] = jr_mt_current_theme();
		} else {
			$jr_mt_theme[$option] = $theme;
		}
	}
	return $jr_mt_theme[$option];
}

function jr_mt_chosen() {	
	extract( jr_mt_url_to_id( 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ) );
	$settings = get_option( 'jr_mt_settings' );
	if ( $home ) {
		if ( trim( $settings['site_home'] ) != '' ) {
			return $settings['site_home'];
		}
	}
	$ids = $settings['ids'];
	if ( $id === FALSE ) {
		if ( isset( $ids[$page_url] ) ) {
			$theme = $ids[$page_url]['theme'];
		} else {
			$theme = jr_mt_check_all( $type );
		}
	} else {
		if ( isset( $ids[$id] ) ) {
			$theme = $ids[$id]['theme'];
		} else {
			$theme = jr_mt_check_all( $type );
		}
	}
	return $theme;
}

function jr_mt_check_all( $type ) {
	if ( $type === FALSE ) {
		$theme = FALSE;
	} else {	
		$settings = get_option( 'jr_mt_settings' );
		$theme = $settings["all_$type"];
		if ( empty( $theme ) ) {
			$theme = FALSE;
		}
	}
	return $theme;
}

?>