<?php
//	Exit if .php file accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


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
		$jt_mt_theme['stylesheet'] - Stylesheet Name of Theme chosen
		$jt_mt_theme['template'] - Template Name of Theme chosen
		
		Very important note:
			- get_option( 'jr_mt_settings' ) ['ids']['theme'] is the Theme Subdirectory Name,
			as opposed to the Template or Stylesheet Name for the Theme.
			- likewise, the variable local variable $theme
		These three different values for each Theme must be clearly separated, as all three usually
		match, but do not have to, e.g. - Child Themes.
	*/
	$GLOBALS['jr_mt_cache'] = TRUE;
	global $jr_mt_theme;
	if ( !isset( $jr_mt_theme ) ) {
		$jr_mt_theme = array();
	}
	if ( !isset( $jr_mt_theme[$option] ) ) {
		$theme = jr_mt_chosen();
		if ( $theme === FALSE ) {
			//	Get both at once, to save a repeat of this logic later:
			$jr_mt_theme['stylesheet'] = jr_mt_current_theme( 'stylesheet' );
			$jr_mt_theme['template'] = jr_mt_current_theme( 'template' );
		} else {
			$themes = wp_get_themes();
			$jr_mt_theme['stylesheet'] = $themes[$theme]->stylesheet;
			$jr_mt_theme['template'] = $themes[$theme]->template;
		}
	}
	$theme = $jr_mt_theme[$option];
	global $jr_mt_cache;
	if ( $jr_mt_cache === FALSE ) {
		unset( $jr_mt_theme[$option] );
	}
	return $theme;
}

//	Returns FALSE for Current Theme
function jr_mt_chosen() {	
	parse_str( $_SERVER['QUERY_STRING'], $keywords_raw );
	$keywords = array();
	foreach ( $keywords_raw as $keyword => $value ) {
		if ( is_array( $value ) ) {
			$kw_prepped = jr_mt_prep_query_keyword( $keyword );
			foreach ( $value as $arr_key => $arr_value ) {
				$keywords[$kw_prepped][jr_mt_prep_query_value( $arr_key )] = jr_mt_prep_query_value( $arr_value );
			}
		} else {
			$keywords[jr_mt_prep_query_keyword( $keyword )] = jr_mt_prep_query_value( $value );
		}
	}
	if ( is_admin() ) {
		//	Admin panel
		//	return P2 theme if p2ajax= is present; current theme otherwise
		if ( isset( $keywords['p2ajax'] ) && array_key_exists( 'p2', wp_get_themes() ) ) {
			$theme = 'p2';
		} else {
			$theme = FALSE;	// Current Theme
		}
	} else {
		/*	Non-Admin page, i.e. - Public Site, etc.
		
			Begin by checking for any Query keywords specified by the Admin in Settings
		*/
		$settings = get_option( 'jr_mt_settings' );
		$settings_query = $settings['query'];
		foreach ( $keywords as $keyword => $value ) {
			if ( isset( $settings_query[$keyword] ) ) {
				if ( isset( $settings_query[$keyword][$value] ) ) {
					return $settings_query[$keyword][$value];
				} else {
					if ( isset( $settings_query[$keyword]['*'] ) ) {
						return $settings_query[$keyword]['*'];
					}
				}
			}
		}
		
		extract( jr_mt_url_to_id( rawurldecode(  parse_url( home_url(), PHP_URL_SCHEME ) . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ) ) );	
		if ( ( 'livesearch' === $type ) && ( FALSE !== $livesearch_theme = jr_mt_livesearch_theme() ) ) {
			return $livesearch_theme;
		}
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

//	Returns FALSE for Current Theme
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
			$theme = FALSE;	// Current Theme
		} else {	
			$settings = get_option( 'jr_mt_settings' );
			if ( isset( $settings["all_$type"] ) ) {
				$theme = $settings["all_$type"];
			} else {
				$theme = '';
			}
			if ( empty( $theme ) ) {
				$theme = FALSE;	// Current Theme
			}
		}
	}
	return $theme;
}

function jr_mt_livesearch_theme() {
	$livesearch_themes = array( 'knowhow' );
	if ( in_array( jr_mt_current_theme( 'stylesheet' ), $livesearch_themes ) ) {
		return jr_mt_current_theme( 'stylesheet' );
	} else {
		if ( in_array( jr_mt_current_theme( 'template' ), $livesearch_themes ) ) {
			return jr_mt_current_theme( 'template' );
		} else {
			//	Go through all the Themes defined in the Plugin's settings
			foreach ( jr_mt_themes_defined() as $theme ) {
				if ( in_array( $theme, $livesearch_themes ) ) {
					return $theme;
				}
			}
		}
	}
	return FALSE;
}

?>