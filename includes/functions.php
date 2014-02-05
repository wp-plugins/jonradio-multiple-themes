<?php
//	Exit if .php file accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( function_exists( 'mb_strtolower' ) ) {
	function jr_mt_strlen( $string ) {
		return mb_strlen( $string );
	}
	function jr_mt_strtolower( $string ) {
		return mb_strtolower( $string );
	}
} else {
	function jr_mt_strlen( $string ) {
		return strlen( $string );
	}
	function jr_mt_strtolower( $string ) {
		return strtolower( $string );
	}
}

/**
 * Return WordPress Current Theme, as defined in Appearance Admin panels
 *
 * Obtains Folder Name of Current Theme, from 'template' option of wp_load_alloptions().
 *
 * @param	string		$option		parameter to select current template or stylesheet
 * @return	string		type		Folder Name of Current Theme
 */
function jr_mt_current_theme( $option ) {
	global $jr_mt_options_cache;
	return $jr_mt_options_cache[$option];
}

/**
 * Given URL, return post or page ID, if possible, and relative path if not
 * 
 * Calls jr_mt_query_keywords.
 *
 * @param	string		$url		full URL of WordPress page, post, admin, etc.
 * @return	array					array with keys of "type", "id" and "page_url":
 *			string		type		"pages", "posts" or "admin"
 *			string		id			Page ID or Post ID or FALSE
 *			string		page_url	relative URL WordPress page, post, admin, etc. or FALSE
 *			string		rel_url		URL relative to WordPress home
 *			bool		home		is URL Site Home?
 */
function jr_mt_url_to_id( $url_orig ) {
	//	Some hosts, likely only IIS, insert an erroneous "/index.php" into the middle of the Permalink in $_SERVER['REQUEST_URI']
	$url = str_replace( '/index.php', '', $url_orig );
	
	$trim = '\ /';	// remove leading and trailing backslashes, blanks and forward slashes

	$is_home = FALSE;
	
	//	get_home_url() returns "https://subdomain.domain.com/wp" - the full URL of the home page of the site
	$home = trim( parse_url( get_home_url(), PHP_URL_PATH ), $trim );	// "wp"
	
	$admin_home = trim( parse_url( admin_url(), PHP_URL_PATH ), $trim );
	$page_url = trim( parse_url( $url, PHP_URL_PATH ), $trim );	// "wp/fruit/apples"
	$is_admin = ( $admin_home == substr( $page_url, 0, strlen( $admin_home ) ) );
	if ( !empty( $home ) ) {	// Only if WordPress is installed in a subfolder, NOT in the Root
		$page_url = trim( substr( $page_url, stripos( $page_url, $home ) + strlen( $home ) ), $trim );	// "fruit/apples"
	}
	$rel_url = $page_url;
	
	$type = FALSE;
	
	$id = jr_mt_query_keywords( parse_url( $url, PHP_URL_QUERY ) );
	if ( $id === NULL ) {
		if ( $is_admin ) {
			$id = FALSE;
			$type = 'admin';
		} else {	
			//	Check for home page (url_to_postid() does not work for home page)
			if ( empty( $page_url ) ) {
				$is_home = TRUE;
				$id = get_option('page_on_front');
				if ( $id == 0 ) {
					//	There is no home Page; posts are displayed instead on the home page
					$page_url = '';
					$id = FALSE;
				} else {
					$type = 'pages';
				}
			} else {
				global $wp_rewrite;
				if ( is_null( $wp_rewrite ) ) {
					$GLOBALS['wp_rewrite'] = new WP_Rewrite();
				}
				global $wp;
				if ( is_null( $wp ) ) {
					$GLOBALS['jr_mt_cache'] = FALSE;
					$wp = (object) array( 'public_query_vars' => array() );
				} else {
					if ( !isset( $wp->public_query_vars ) ) {
						$GLOBALS['jr_mt_cache'] = FALSE;
						$wp->public_query_vars = array();
					}
				}
				$id = url_to_postid( $url );
				if ( $id == 0 ) {
					$page = get_page_by_path( $page_url );
					if ( $page === NULL ) {
						//	get_page_by_path() returns NULL for Posts, Home Page, Admin, etc.
						//	So, check for Posts:
						$post = get_posts( array( 'name' => $page_url ) );
						if ( empty( $post ) ) {
							$id = FALSE;
						} else {
							$id = $post[0]->ID;
							$type = 'posts';
						}
					} else {
						$id = $page->ID;
						$type = 'pages';
					}
				} else {
					$post_obj = get_post( $id );
					if ( $post_obj->post_type == 'page' ) {
						$type = 'pages';
					} else {
						if ( $post_obj->post_type == 'post' ) {
							$type = 'posts';
						}
					}
				}
			}
		}
	} else {
		//	id in query of URL (?keyword=value&keyword=value)
		$type = key( $id );
		$id = $id[$type];
		$page_url = FALSE;
	}
	return array(
		'type' => $type, 
		'id' => $id, 
		'page_url' => $page_url, 
		'rel_url' => $rel_url, 
		'home' => $is_home
	);
}

/**
 * Return page_id= or p= (post ID) or page= (admin page) value from a URL
 * 
 * Calls parse_str function in its own variable space because it could create virtually any variable name!
 * Only looks at page_id=, p= and page= now, but could be expanded to other query keywords.
 *
 * @param    string  $url_query  Query portion (after the ?) in a URL
 * @return   var                 array with key of "pages", "posts" or "admin" and value of page_id=, p= or page=, respectively; or NULL if none are present
 */
function jr_mt_query_keywords( $url_query ) {
	if ( $url_query === NULL ) {
		return NULL;
	} else {
		parse_str( $url_query );
		if ( isset( $page_id ) ) {
			return array( 'pages' => $page_id );
		} else {
			if ( isset( $p ) ) {
				return array( 'posts' => $p );
			} else {
				if ( isset( $page ) ) {
					return array( 'admin' => $page );
					} else {
					if ( isset( $cat ) ) {
						return array( 'cat' => $cat );
					} else {
						if ( isset( $m ) ) {
							return array( 'archive' => $m );
						} else {
							if ( isset( $s ) ) {
								return array( 'livesearch' => $s );
							} else {	
								return NULL;
							}
						}
					}
				}
			}
		}
	}
}

/**
 * Is the URL on the current WordPress web site?
 * 
 * Checks if URL begins with Site Home URL.
 *
 * @param    string  $url		URL to be checked to be sure it is "on" the current WordPress web site
 * @return   var                bool TRUE if URL on current WordPress web site; string error message otherwise
 */
function jr_mt_site_url( $url ) {
	$check_url = trim( $url );
	if ( strcasecmp( 'http', substr( $check_url, 0, 4 ) ) != 0 ) {
		return 'URL does not begin with http://';
	}
	$site_home = get_home_url();
	if ( strcasecmp( $site_home, substr( $check_url, 0, strlen( $site_home ) ) ) != 0 ) {
		return "URL specified is not part of current WordPress web site.  URL must begin with '$site_home'";
	}
	return TRUE;
}

function jr_readme() {
	$readme = array( 'read readme' => FALSE );
	$file_array = jr_filesystem_text_read('readme.txt', jr_mt_path());
	if ( ( FALSE !== $file_array ) && !is_wp_error( $file_array ) ) {
		//	Get first non-blank line
		$results = jr_nextline( $file_array, 0 );
		if ( '===' === substr( $results['line'], 0, 3) ) {
			$readme['read readme'] = TRUE;
			$readme['name'] = trim( $results['line'], ' =' );
			do {
				$results = jr_nextline( $file_array, $results['index'] );
				if ( '==' === substr( $results['line'], 0, 2) ) {
					break;
				} else {
					$colon = strpos( $results['line'], ":", 4 );
					if ( $colon !== FALSE ) {
						$key = preg_replace( '/\s+/', ' ', trim( substr( $results['line'], 0, $colon ) ) );
						$readme[$key] = trim( substr( $results['line'], $colon + 1 ) );
					}
				}
			} while ( $results['line'] != "" );
		}
	}
	return $readme;
}
function jr_nextline( $file_array, $index ) {
	$line = "";
	while ( ( $index < count( $file_array ) ) && ( $line === "" ) ) {
		$line = trim( $file_array[$index++] );
	};
	return array( 'line' => $line, 'index' => $index );
	//	['line'] of "" indicates End of File
	//	['index'] is the next line to be read
}

/**
 * Initialize Filesystem object
 *
 * @param str $form_url - URL of the page to display request form
 * @param str $method - connection method
 * @param str $context - destination folder
 * @param array $fields - fileds of $_POST array that should be preserved between screens
 * @return bool/str - false on failure, stored text on success
 **/
function jr_filesystem_init( $form_url, $method, $context, $fields = null ) {
	global $wp_filesystem;
	
	/* first attempt to get credentials */
	if (false === ($creds = request_filesystem_credentials($form_url, $method, false, $context, $fields))) {
	
	/**
	* if we comes here - we don't have credentials
	* so the request for them is displaying
	* no need for further processing
	**/
	return false;
	}
	
	/* now we got some credentials - try to use them*/
	if ( !WP_Filesystem( $creds ) ) {
	
	/* incorrect connection data - ask for credentials again, now with error message */
	request_filesystem_credentials($form_url, $method, true, $context);
	return false;
	}
	
	return true; //filesystem object successfully initiated
}

/**
 * Read text from file
 *
 * @param str $form_url - URL of the page where request form will be displayed
 * @return bool/str - false on failure, stored text on success
 **/
function jr_filesystem_text_read($file_name, $context){
  global $wp_filesystem;
  
  $text = '';

  $form_url = wp_nonce_url('themes.php?page=jr_mt_settings', 'filesystem_dummy_screen');
  $method = ''; //leave this empty to perform test for 'direct' writing 

  if(!jr_filesystem_init($form_url, $method, $context))
    return false; //stop further processing when request forms displaying

  /*
   * now $wp_filesystem could be used
   * get correct target file first
   **/
  $target_dir = $wp_filesystem->find_folder($context);
  $target_file = trailingslashit($target_dir).$file_name;

  /* read the file */
  if($wp_filesystem->exists($target_file)){ //check for existence

    $text = $wp_filesystem->get_contents_array($target_file);
    if(!$text)
      return new WP_Error('reading_error', 'Error when reading file'); //return error object      

  }  

  return $text;
}

/**
 * Perform writing into file
 *
 * @param str $form_url - URL of the page to display request form
 * @return bool/str - false on failure, stored text on success
 **/
function jr_filesystem_text_write( $content, $file_name, $context ) {
  global $wp_filesystem;

  $method = ''; //leave this empty to perform test for 'direct' writing
  $form_url = wp_nonce_url('themes.php?page=jr_mt_settings', 'filesystem_dummy_screen');

  if(!jr_filesystem_init($form_url, $method, $context))
    return false; //stop further processing when request form is displaying

  /*
   * now $wp_filesystem could be used
   * get correct target file first
   **/
  $target_dir = $wp_filesystem->find_folder($context);
  $target_file = trailingslashit($target_dir) . $file_name;

  /* write into file */
  if(!$wp_filesystem->put_contents($target_file, $content, FS_CHMOD_FILE))
    return new WP_Error('writing_error', 'Error when writing file'); //return error object

  return $content;
}

/**
 * Update available for Plugin?
 *
 * @return bool - TRUE if an update is available in the WordPress Repository,
 *	FALSE if no update is available or if the update_plugins transient is not available
 *	(which also results in an error message). 
 **/
function jr_mt_plugin_update_available() {
	global $jr_mt_update_plugins;
	if ( !isset( $jr_mt_update_plugins ) ) {
		$transient = get_site_transient( 'update_plugins' );
		if ( FALSE === $transient ) {
			//	Error
			return FALSE;
		} else {
			$jr_mt_update_plugins = $transient;
		}
	}
	if ( empty( $jr_mt_update_plugins->response ) ) {
		return FALSE;
	}
	return array_key_exists( jr_mt_plugin_basename(), $jr_mt_update_plugins->response );
}

/**
 * What Themes are defined to Plugin?
 *
 * @return arr - a list of Themes (folder names) defined in Settings of Plugin 
 **/
function jr_mt_themes_defined() {
	$themes = array();
	$settings = get_option( 'jr_mt_settings' );
	foreach ( $settings as $key => $value ) {
		if ( 'ids' == $key ) {
			foreach ( $value as $id => $arr ) {
				$themes[] = $arr['theme'];
			}
		} else {
			if ( !empty( $value ) ) {
				$themes[] = $value;
			}
		}
	}
	return array_unique( $themes );
}

/**
 * Prepare URL Query Value
 * 
 * Sanitize and standardize a URL Query Value for storage in a database.
 * Does not support ?keyword[]=value, i.e. - $value cannot be an Array. 
 *
 * @param    string  $value		URL Query Value to be sanitized and standardized; will fail if array 
 * @return   string             URL Query Value after being sanitized and standardized
 */
function jr_mt_prep_query_value( $value ) {
	return str_ireplace( '%e2%80%8e', '', jr_mt_strtolower( trim( $value ) ) );
}
function jr_mt_prep_query_keyword( $keyword ) {
	return jr_mt_prep_query_value( $keyword );
}

function jr_mt_parse_query( $query ) {
	/*	Written to replace parse_str which converts a dot to an underscore
	*/
	$marker = 'jr_mt_dot';
	parse_str( str_replace( '.', $marker, $query ), $marker_array );
	$query_array = array();
	foreach ( $marker_array as $key => $val ) {
		$query_array[str_replace( $marker, '.', $key )] = str_replace( $marker, '.', $val );
	}	
	return $query_array;
}

?>