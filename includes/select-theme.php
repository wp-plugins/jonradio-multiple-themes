<?php
//	Exit if .php file accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'plugins_loaded', 'jr_mt_plugins_loaded' );
function jr_mt_plugins_loaded() {
	/*	Select the relevant Theme
	*/
	add_filter( 'pre_option_stylesheet', 'jr_mt_stylesheet' );
	add_filter( 'pre_option_template', 'jr_mt_template' );
}

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
	$keywords_raw = jr_mt_parse_query( $_SERVER['QUERY_STRING'] );
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
		/*	No, so now check for Asterisk
		*/
		$current_url = str_replace( '\\', '/', $rel_url );
		$current_url_dirs = explode( '/', $current_url );
		$current_url_dirs_count = count( $current_url_dirs );
		foreach ( $ids as $key => $array ) {
			if ( '*' === $array['type'] ) {
				$prefix_url = str_replace( '\\', '/', $array['rel_url'] );
				$prefix_url_dirs = explode( '/', $prefix_url );
				/*	Current URL must have at least as many subdirectory levels
					specified as Prefix Entry being tested, or it cannot match
				*/
				if ( $current_url_dirs_count >= count( $prefix_url_dirs ) ) {
					foreach ( $prefix_url_dirs as $element => $dir ) {
						/*	Anywhere there is an Asterisk in Entry,
							Make the Current URL match at that point (subdirectory level)
						*/
						if ( '*' === $dir ) {
							$current_url_dirs[$element] = '*';
						}
					}
					$this_length = strlen( $prefix_url );
					if ( $prefix_url === substr( implode( '/', $current_url_dirs ), 0, $this_length ) ) {
						//	Need to find longest match if there are multiple prefix matches.
						if ( $this_length > $match_length ) {
							$theme = $array['theme'];
							$match_length = $this_length;
						}
					}
				}
			}
		}
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