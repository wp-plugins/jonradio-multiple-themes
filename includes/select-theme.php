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
	if ( is_admin() ) {
		//	Admin panel
		//	return P2 theme if p2ajax= is present; current theme otherwise
		parse_str( $_SERVER['QUERY_STRING'], $keywords );
		if ( isset( $keywords['p2ajax'] ) && array_key_exists( 'p2', wp_get_themes() ) ) {
			$theme = 'p2';
		} else {
			$theme = jr_mt_current_theme();
		}
	} else {
		//	Non-Admin page, i.e. - Public Site, etc.
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
				$theme = jr_mt_check_all( $type, $rel_url, $ids );
			}
		} else {
			if ( isset( $ids[$id] ) ) {
				$theme = $ids[$id]['theme'];
			} else {
				$theme = jr_mt_check_all( $type, $rel_url, $ids );
			}
		}
	}
	return $theme;
}

function jr_mt_check_all( $type, $rel_url, $ids ) {
	//	Check Prefix entries first, because we already know there is no specific entry for this URL.
	$theme = '';
	$match_length = 0;
	foreach ( $ids as $key => $array ) {
		if ( $array['type'] == 'prefix' ) {
			$this_length = strlen( $array['rel_url'] );
			if ( $array['rel_url'] == substr( $rel_url, 0, $this_length ) ) {
				//	Need to find longest match if there are multiple prefix matches.
				if ( $this_length > $match_length ) {
					$theme = $array['theme'];
					$match_length = $this_length;
				}
			}
		}
	}
	//	See if a Prefix entry was found
	if ( $match_length == 0 ) {
		if ( $type === FALSE ) {
			$theme = FALSE;
		} else {	
			$settings = get_option( 'jr_mt_settings' );
			if ( isset( $settings["all_$type"] ) ) {
				$theme = $settings["all_$type"];
			} else {
				$theme = '';
			}
			if ( empty( $theme ) ) {
				$theme = FALSE;
			}
		}
	}
	return $theme;
}

?>