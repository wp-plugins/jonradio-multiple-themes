<?php
/*	Exit if .php file accessed directly
*/
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/*	Select the relevant Theme
	These hooks must be available immediately
	as some Themes check them very early.
	Also must be available in Admin for p2.
*/
add_filter( 'pre_option_stylesheet', 'jr_mt_stylesheet' );
add_filter( 'pre_option_template', 'jr_mt_template' );

if ( !is_admin() ) {	
	/*	Hooks below shown in order of execution */
	add_action( 'wp_loaded', 'jr_mt_wp_loaded', JR_MT_RUN_LAST );
	function jr_mt_wp_loaded() {
		/*	Purpose of this hook is to output any required Cookie before it is too late
			(after the <html> or any other HTML is generated).
			There is no performance impact because this effectively pre-caches values
			for use later.
			This timing is also used to enqueue JavaScript related to the Sticky feature.
		*/
		global $jr_mt_theme;
		if ( !isset( $jr_mt_theme ) ) {
			$settings = get_option( 'jr_mt_settings' );
			if ( !empty( $settings['remember']['query'] ) ) {
				jr_mt_template();
			}
		}

		DEFINE( 'JR_MT_TOO_LATE_FOR_COOKIES', TRUE );
	}
	
	/*	'parse_query' is the earliest Action that I could find where is_page()
		is valid.
		Unfortunately, it can run several times.
	*/
	add_action( 'parse_query', 'jr_mt_page_conditional', JR_MT_RUN_FIRST );
	function jr_mt_page_conditional() {
		/*	Only run it once
		*/
		remove_action( 'parse_query', 'jr_mt_page_conditional', JR_MT_RUN_FIRST );
		
		/*	In case any requests for Theme came before this hook,
			make sure that Theme Selection is repeated the next time
			it is needed.
			Because is_page() and is_single don't work until now.
			
			Note:  in PHP, you cannot directly unset a global variable,
			hence the cryptic code below.
		*/
		unset( $GLOBALS['jr_mt_theme'] );
		DEFINE( 'JR_MT_PAGE_CONDITIONAL', TRUE );
	}
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
	global $jr_mt_theme;
	if ( !isset( $jr_mt_theme ) ) {
		$jr_mt_theme = array();
	}
	if ( !isset( $jr_mt_theme[$option] ) ) {
		$theme = jr_mt_chosen();
		$jr_mt_all_themes = jr_mt_all_themes();
		/*	Check to be sure that Theme is still installed.
			If not, do a massive cleanup to remove all Settings entries that
			reference Themes that are no longer installed.
		*/
		if ( ( FALSE !== $theme ) && ( !isset( $jr_mt_all_themes[ $theme ] ) ) ) {
			require_once( jr_mt_path() . 'includes/settings-cleanup.php' );
			$theme = jr_mt_chosen();
		}
		if ( FALSE === $theme ) {
			//	Get both at once, to save a repeat of this logic later:
			$jr_mt_theme['stylesheet'] = jr_mt_current_theme( 'stylesheet' );
			$jr_mt_theme['template'] = jr_mt_current_theme( 'template' );
		} else {
			$jr_mt_theme['stylesheet'] = $jr_mt_all_themes[ $theme ]->stylesheet;
			$jr_mt_theme['template'] = $jr_mt_all_themes[ $theme ]->template;
		}
		if ( !is_admin() ) {
			jr_mt_cookie( 'all', 'clean' );
		}
	}
	$theme = $jr_mt_theme[$option];
	return $theme;
}

//	Returns FALSE for Current Theme
function jr_mt_chosen() {
	$settings = get_option( 'jr_mt_settings' );
	
	/*	$queries - array of [keyword] => array( value, value, ... )
			in the current URL.
	*/
	$queries = jr_mt_query_array(); 

	/*	P2 free Theme special processing:
		for both Admin and Public site,
		check for P2 keyword p2ajax=,
		and select P2 theme, if present.
	*/
	if ( isset( $queries['p2ajax'] ) && array_key_exists( 'p2', jr_mt_all_themes() ) ) {
		return 'p2';
	}
	/*	Otherwise, Admin gets current ("Active") WordPress Theme
	*/
	if ( is_admin() ) {
		return FALSE;
	}
	
	/*	KnowHow ThemeForest Paid Theme special processing:
		if s= is present, and 'knowhow' is either the active WordPress Theme
		or is specified in any Settings, then automatically select the KnowHow theme.
	*/
	if ( isset( $queries['s'] ) && in_array( 'knowhow', jr_mt_themes_defined() ) ) {
		return 'knowhow';
	}

	/*	Non-Admin page, i.e. - Public Site, etc.
	
		Begin by checking for any Query keywords specified by the Admin in Settings,
		complicated by the fact that Override entries take precedence.
	*/
	if ( !empty( $settings['query'] ) ) {
		if ( '' !== $_SERVER['QUERY_STRING'] ) {
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
						if ( isset( $value_array['*'] ) ) {
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
	
	/*	Now look at URL entries: $settings['url'] and ['url_prefix']
	*/
	
	$home_url = home_url();
	$prep_url = jr_mt_prep_url( parse_url( $home_url, PHP_URL_SCHEME ) . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] );
	foreach ( $settings['url'] as $settings_array ) {
		if ( jr_mt_same_url( $settings_array['prep'], $prep_url ) ) {
			return $settings_array['theme'];
		}
	}
	foreach ( $settings['url_prefix'] as $settings_array ) {
		if ( jr_mt_same_prefix_url( $settings_array['prep'], $prep_url ) ) {
			return $settings_array['theme'];
		}
	}
	foreach ( $settings['url_asterisk'] as $settings_array ) {
		if ( jr_mt_same_prefix_url_asterisk( $settings_array['prep'], $prep_url ) ) {
			return $settings_array['theme'];
		}
	}

	/*	Must check for Home near the end as queries override
	*/
	$prep_url_no_query = $prep_url;
	$prep_url_no_query['query'] = array();
	if ( '' !== $settings['site_home'] ) {
		/*	Check for Home Page,
			with or without Query.
		*/
		$prep_url_no_query = $prep_url;
		$prep_url_no_query['query'] = array();
		if ( jr_mt_same_url( $home_url, $prep_url_no_query ) ) {
			return $settings['site_home'];
		}
	}
	/*	All Pages and All Posts settings are checked second to last, 
		just before Everything Else.
		
		is_single() and is_page() only work after JR_MT_PAGE_CONDITIONAL is set.
		But alternate means can be used with default Permalinks.
	*/
	if ( defined( 'JR_MT_PAGE_CONDITIONAL' ) ) {
		if ( '' !== $settings['all_posts'] ) {
			if ( is_single() ) {
				return $settings['all_posts'];
			} else {
				if ( '' !== $settings['all_pages'] ) {
					if ( is_page() ) {
						return $settings['all_pages'];
					}
				}
			}
		}
	} else {
		$permalink = get_option( 'permalink_structure' );
		if ( empty( $permalink ) ) {
			if ( '' !== $settings['all_posts'] ) {
				if ( isset( $queries['p'] ) ) {
					return $settings['all_posts'];
				} else {
					if ( '' !== $settings['all_pages'] ) {
						if ( isset( $queries['page_id'] ) ) {
							return $settings['all_pages'];
						}
					}
				}
			}
		}
	}
	/*	This is the Theme for Everything Advanced Setting.
		A Setting of Blank uses WordPress Current Theme value,
		i.e. - the Setting is not set.
	*/
	if ( '' === $settings['current'] ) {
		return FALSE;
	} else {
		return $settings['current'];
	}
}

/**	Cookie to JavaScript with Sticky Query and related info.

	Replace Existing or Create New (if no existing) Cookie
	to remember what Sticky Keyword=Value to use on this Browser on this Visitor Computer.
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
			. jr_mt_prep_comp_url( home_url() )
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
			Using home_url().
		*/
		$cookie_path = parse_url( home_url(), PHP_URL_PATH ) . '/';
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

/**	Build Query Array

	$array[keyword] = array( value, value, ... )
	Sets both keyword and value to lower-case as
	that is how they are stored in Settings.
	
	Supports only & separator, not proposed semi-colon separator.
	
	Handles duplicate keywords in all four of these forms:
	kw=val1&kw=val2 kw[]=val1&kw[]=val2 kw=val1&kw=val1 kw[]=val1&kw[]=val1
	but nothing else, e.g. - kw=val1,val2 is not valid;
	it returns "val1,val2" as the Value.
	Also handles kw1&kw2
	
	Tests of parse_str() in PHP 5.5.9 proved that semi-colon and comma
	are not supported.  But, neither is kw=val1,kw=val2 which is why
	this function is written without the use of parse_str.
*/
function jr_mt_query_array() {
	/*	Remove array entry indicators ("[]") as we properly handle duplicate keywords,
		and covert to lower-case for comparison purposes.
	*/
	$queries = array();
	if ( !empty( $_SERVER['QUERY_STRING'] ) ) {
		$query = explode( '&', jr_mt_strtolower( str_replace( '[]', '', $_SERVER['QUERY_STRING'] ) ) );
		foreach ( $query as $kwval ) {
			$query_entry = explode( '=', $kwval );
			if ( !isset( $query_entry[1] ) ) {
				$query_entry[1] = '';
			}
			$queries[ $query_entry[0] ][] = $query_entry[1];
		}
	}
	return $queries;
}

?>