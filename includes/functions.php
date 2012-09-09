<?php
/**
 * Return WordPress Current Theme, as defined in Appearance Admin panels
 *
 * Obtains Folder Name of Current Theme, from 'template' option of wp_load_alloptions().
 *
 * @param	string		$option		optional parameter that is not currently used,
 *									but may in the future select current template or stylesheet
 * @return	string		type		Folder Name of Current Theme
 */
function jr_mt_current_theme( $option='stylesheet' ) {
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
	//	Some hosts, likely IIS, insert an erroneous "/index.php" into the middle of the Permalink in $_SERVER['REQUEST_URI']
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
			//	Check for home page (get_page_by_path() does not work for home page)
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
			}
		}
	} else {
		//	id in query of URL (?keyword=value&keyword=value)
		$type = key( $id );
		$id = $id[$type];
		$page_url = FALSE;
	}
	return array( 'type' => $type, 'id' => $id, 'page_url' => $page_url, 'rel_url' => $rel_url, 'home' => $is_home );
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
					return NULL;
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
?>