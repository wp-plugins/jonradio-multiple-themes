<?php
/*	Exit if .php file accessed directly
*/
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'jr_mt_plugins_loaded' );
function jr_mt_plugins_loaded() {
	/*	Select the relevant Theme
	*/
	add_filter( 'pre_option_stylesheet', 'jr_mt_stylesheet' );
	add_filter( 'pre_option_template', 'jr_mt_template' );
}

add_action( 'wp_loaded', 'jr_mt_wp_loaded', 999 );
function jr_mt_wp_loaded() {
	/*	Purpose of this hook is to output any required Cookie before it is too late
		(after the <html> or any other HTML is generated).
		There is no performance impact because this effectively pre-caches values
		for use later.
		This timing is also used to enqueue JavaScript related to the Sticky feature.
	*/
	global $jr_mt_cache;
	if ( $jr_mt_cache === FALSE ) {
		$settings = get_option( 'jr_mt_settings' );
		if ( !empty( $settings['remember']['query'] ) ) {
			jr_mt_template();
		}
	}
	DEFINE( 'JR_MT_TOO_LATE_FOR_COOKIES', TRUE );
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
		if ( !is_admin() ) {
			jr_mt_cookie( 'all', 'clean' );
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
	if ( is_admin() ) {
		//	Admin panel
		//	return P2 theme if p2ajax= is present; current theme otherwise
		$keywords = jr_mt_kw( 'QUERY_STRING' );
		if ( isset( $keywords['p2ajax'] ) && array_key_exists( 'p2', wp_get_themes() ) ) {
			$theme = 'p2';
		} else {
			$theme = FALSE;	// Current Theme
		}
		return $theme;
	}

	/*	Non-Admin page, i.e. - Public Site, etc.
	
		Begin by checking for any Query keywords specified by the Admin in Settings,
		complicated by the fact that Override entries take precedence.
	*/
	$settings = get_option( 'jr_mt_settings' );
	if ( !empty( $settings['query'] ) ) {
		if ( '' !== $_SERVER['QUERY_STRING'] ) {
			/*	$queries - array of [keyword] => array( value, value, ... )
					in the current URL.
			*/
			$queries = jr_mt_query_array(); 
			/*	Check Override entries
			*/
			foreach ( $settings['override']['query'] as $override_keyword => $override_value_array ) {
				if ( isset( $queries[ $override_keyword ] ) ) {
					foreach ( $override_value_array as $override_value => $bool ) {
						if ( in_array( $override_value, $queries[ $override_keyword ] ) ) {
							$override_found[] = array( $override_keyword, $override_value );
						}
					}
				}
			}
			if ( !isset( $overrides_found ) ) {
				/*	Look for both keyword=value settings and keyword=* settings,
					with keyword=value taking precedence (sorted out later).
				*/
				foreach ( $settings['query'] as $query_settings_keyword => $value_array ) {
					if ( isset( $queries[ $query_settings_keyword ] ) ) {
						foreach ( $value_array as $query_settings_value => $theme ) {
							if ( in_array( $query_settings_value, $queries[ $query_settings_keyword ] ) ) {
								$query_found[] = array( $query_settings_keyword, $query_settings_value );
							}
						}
						if ( in_array( '*', $queries[ $query_settings_keyword ] ) ) {
							$keyword_found[] = $query_settings_keyword;
						}
					}
				}
			}
		}
	}
	
	/*	Handle Overrides:
		First, for Override keyword=value query in URL.
		Second, for previous Override detected by PHP cookie.
	*/
	if ( isset( $override_found ) ) {
		/*	If sticky, create JavaScript Sticky Cookie,
			and PHP Sticky Cookie.
			No matter what:
			return Theme from the first Override found.
		*/
		$keyword = $override_found[0][0];
		$value = $override_found[0][1];
		if ( isset( $settings['remember']['query'][ $keyword ][ $value ] ) ) {
			jr_mt_js_sticky_query( $keyword, $value );
			jr_mt_cookie( 'php', 'put', "$keyword=$value" );
		}
		return $settings['query'][ $keyword ][ $value ];
	} else {
		/*	Is there a previous Override Query for this Site Visitor?
			If so, use it, but only if it is still valid.
		*/
		if ( FALSE !== ( $cookie = jr_mt_cookie( 'php', 'get' ) ) ) {
			list( $keyword, $value ) = explode( '=', $cookie );
			if ( isset( $settings['override']['query'][ $keyword ][ $value ] ) ) {
				/*	If sticky, create JavaScript Sticky Cookie,
					and renew PHP Sticky Cookie.
					No matter what:
					Return Theme
				*/
				if ( isset( $settings['remember']['query'][ $keyword ][ $value ] ) ) {
					jr_mt_js_sticky_query( $keyword, $value );
					jr_mt_cookie( 'php', 'put', "$keyword=$value" );
				}
				return $settings['query'][ $keyword ][ $value ];
			}
		}
	}

	/*	Handle Non-Overrides:
		keyword=value query in URL with matching setting entry.
	*/
	if ( isset( $query_found ) ) {
		$query_keyword_found = $query_found[0][0];
		$query_value_found = $query_found[0][1];
		/*	Probably makes sense to give preference to the Sticky ones
		*/
		foreach ( $query_found as $query_kwval_array ) {
			if ( isset( $settings['remember']['query'][ $query_kwval_array[0] ][ $query_kwval_array[1] ] ) ) {
				$query_keyword_found = $query_kwval_array[0];
				$query_value_found = $query_kwval_array[1];
				/*	Create JavaScript Sticky Cookie,
					and PHP Sticky Cookie.
				*/
				jr_mt_js_sticky_query( $query_keyword_found, $query_value_found );
				jr_mt_cookie( 'php', 'put', "$query_keyword_found=$query_value_found" );
				break;
			}
		}
		/*	Return Theme
		*/
		return $settings['query'][ $query_keyword_found ][ $query_value_found ];
	}
	
	/*	Handle Keyword wildcards:
		keyword=* setting entry that matches keyword in URL query.
	*/
	if ( isset( $keyword_found ) ) {
		return $settings['query'][ $keyword_found[0] ]['*'];
	}

	$full_url = parse_url( home_url(), PHP_URL_SCHEME ) . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

	extract( jr_mt_url_to_id( rawurldecode(  $full_url ) ) );	
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

	return $theme;
}

/**	Cookie to JavaScript with Sticky Query and related info.

	Replace Existing or Create New (if no existing) Cookie
	to remember what Stickey Keyword=Value to use on this Browser on this Visitor Computer.
	Cookie is an encoding of this array:
	- keyword=value query to append to URL
	- FALSE if Setting "Append if no question mark ("?") found in URL", or
		TRUE if Setting "Append if no Override keyword=value found in URL"
	- an array of all sticky or override queries (empty array if FALSE)
*/
function jr_mt_js_sticky_query( $keyword, $value ) {
	add_action( 'wp_enqueue_scripts', 'jr_mt_wp_enqueue_scripts' );
	function jr_mt_wp_enqueue_scripts() {
		global $jr_mt_plugin_data;
		wp_enqueue_script( 'jr_mt_sticky', plugins_url() . '/' . dirname( jr_mt_plugin_basename() ) . '/js/sticky.js', array(), $jr_mt_plugin_data['Version'] );
		/*	JavaScript needs some values passed in HTML,
			so add that hook here, too.
		*/
		add_action( 'wp_footer', 'jr_mt_wp_footer' );
	}
	function jr_mt_wp_footer() {
		echo '<div style="display: none;"><div id="jr-mt-home-url" title="'
			. jr_mt_prep_comp_url( get_home_url() )
			. '"></div><div id="jr-mt-site-admin" title="'
			. jr_mt_prep_comp_url( admin_url() )
			. '"></div></div>';
	}
	/**	Prepare URL for JavaScript compares
	
		Remove http[s]//: from beginning
		Convert rest of URL to lower-case
		Remove www. from beginning, if present
		Convert any backslashes to forward slashes
		Remove any trailing slash(es).
	*/
	function jr_mt_prep_comp_url( $url ) {
		$comp_url = strtolower( substr( $url, 3 + strpos( $url, '://' ) ) );
		if ( 'www.' === substr( $comp_url, 0, 4 ) ) {
			$comp_url = substr( $comp_url, 4 );
		}
		return rtrim( str_replace( '\\', '/', $comp_url ), '/' );
	}
			
	$settings = get_option( 'jr_mt_settings' );

	if ( $settings['query_present'] ) {
		foreach ( $settings['override']['query'] as $override_keyword => $override_value_array ) {
			foreach ( $override_value_array as $override_value => $theme ) {
				$override[] = "$override_keyword=$override_value";
			}
		}
	} else {
		$override = array();
	}
	
	jr_mt_cookie( 'js', 'put', strtr( rawurlencode( json_encode(
			array( "$keyword=$value", $settings['query_present'], $override ) ) ), 
		array( '%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')' ) )
	);
}

/*	All Cookie Handling occurs here.
	$action - 'get', 'put', 'del'
*/
function jr_mt_cookie( $lang, $action, $cookie_value = '' ) {
	switch ( $lang ) {
		case 'js':
			$cookie_name = 'jr-mt-remember-query';
			$raw = TRUE;
			$expiry = '+36 hours';
			$function = 'setrawcookie';
			break;
		case 'php':
			$cookie_name = 'jr_mt_php_override_query';
			$raw = FALSE;
			$expiry = '+1 year';
			$function = 'setcookie';
			break;
	}
	if ( 'get' === $action ) {
		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			return $_COOKIE[ $cookie_name ];
		} else {
			return FALSE;
		}
	} else {
		global $jr_mt_cookie_track;
		if ( defined( 'JR_MT_TOO_LATE_FOR_COOKIES' ) ) {
			return FALSE;
		}
		/*	Determine Path off Domain to WordPress Address, not Site Address, for Cookie Path value.
			Using get_home_url().
		*/
		$cookie_path = parse_url( get_home_url(), PHP_URL_PATH ) . '/';
		switch ( $action ) {
			case 'put':
				if ( empty( $cookie_value ) ) {
					return FALSE;
				} else {
					return ( $jr_mt_cookie_track[ $lang ] = $function( $cookie_name, $cookie_value, strtotime( $expiry ), $cookie_path, $_SERVER['SERVER_NAME'] ) );
				}
				break;
			case 'del':
				/*	Don't clutter up output to browser with a Cookie Delete request if a Cookie does not exist.
				*/
				if ( isset( $_COOKIE[ $cookie_name ] ) ) {
					return ( $jr_mt_cookie_track[ $lang ] = setrawcookie( $cookie_name, '', strtotime( '-2 days' ), $cookie_path, $_SERVER['SERVER_NAME'] ) );
				}
				break;
			case 'clean':
				if ( 'all' === $lang ) {
					$clean_langs = array( 'php', 'js' );
				} else {
					$clean_langs = array( $lang );
				}
				foreach ( $clean_langs as $clean_lang ) {
					if ( !isset( $jr_mt_cookie_track[ $clean_lang ] ) ) {
						jr_mt_cookie( $clean_lang, 'del' );
					}
				}
				break;
		}
	}
}
			

						
			
			

			
			
			


function jr_mt_code_to_resolve() {	
		
		if ( empty( $settings['remember']['query'] ) ) {
			/*	Delete any Cookie that might exist
			*/
			jr_mt_cookie( 'js', 'del' );
			if ( isset( $query_entry ) ) {
				return $query_entry;
			}
		} else {
			/*	Check for a Cookie (safe to do here because we exit immediately after cookie creation above)
				If it exists, make sure both a ['remember']['query'] and ['query'] setting still exists for it
				(i.e. - the Keyword=Value specified in the Cookie).
				If so, select the specified Theme;
				if not, delete the Cookie.
			*/
			if ( FALSE !== ( $cookie_value = jr_mt_cookie( 'js', 'get' ) ) ) {
				list( $keyword, $value ) = explode( '=', $cookie_value );
				if ( isset( $settings['remember']['query'][$keyword][$value] ) && isset( $settings['query'][$keyword][$value] ) ) {
					return $settings['query'][$keyword][$value];
				} else {
					jr_mt_cookie( 'js', 'del' );
				}
			}
		}
}
		




/**	Build Query Array

	$array[keyword] = array( value, value, ... )
*/
function jr_mt_query_array() {
	$query = explode( '&', $_SERVER['QUERY_STRING'] );
	$queries = array();
	foreach ( $query as $pair ) {
		$kwval = explode( '=', $pair );
		$keyword = $kwval[0];
		if ( isset( $kwval[1] ) ) {
			$value = $kwval[1];
		} else {
			$value = '';
		}
		if ( '' !== $keyword ) {
			$queries[ $keyword ][] = $value;
		}
	}
	return $queries;
}

/*	Returns Keyword=Value array based on $_SERVER variable requested.
*/
function jr_mt_kw( $server ) {
	$keywords_raw = jr_mt_parse_query( $_SERVER[ $server ] );
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
	return $keywords;
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