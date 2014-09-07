<?php

/*	Exit if .php file accessed directly
*/
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/*	setup_theme is the earliest Action where
	all functions in jr_mt_convert_ids() work properly.
*/
add_action( 'setup_theme', 'jr_mt_convert_ids', JR_MT_RUN_FIRST );

/**
 * Convert pre-Version 5 ['ids'] Settings to new Version 5 format.
 * 
 * Mainly, it involves converting Post ID to URL.
 *
 */
function jr_mt_convert_ids() {
	$internal_settings = get_option( 'jr_mt_internal_settings' );
	if ( isset( $internal_settings['ids'] ) ) {
		$settings = get_option( 'jr_mt_settings' );
		foreach ( $settings['ids'] as $key => $ids_array ) {
			/*	Be sure that Theme has not been deleted.
			*/
			$jr_mt_all_themes = jr_mt_all_themes();
			if ( isset( $jr_mt_all_themes[ $ids_array['theme'] ] ) ) {
				/*	$key:
						'' - Home entry
				*/
				if ( '' === $key ) {
					if ( '' === $settings['site_home'] ) {
						$settings['site_home'] = $ids_array['theme'];
					}
				} else {
					if ( isset( $ids_array['type'] ) ) {
						switch ( $ids_array['type'] ) {
							case 'admin':
								/*	Ignore as Admin pages are ignored
								*/
								break;
							case 'prefix':
								/*	URL Prefix
								*/
								$url = get_home_url() . "/$key";
								$settings['url_prefix'][] = array(
									'url'   => $url,
									'prep'  => jr_mt_prep_url( $url ),
									'theme' => $ids_array['theme']
								);
								break;
							case '*':
								/*	URL Prefix with Asterisk
								*/
								$url = get_home_url() . "/$key";
								$settings['url_asterisk'][] = array(
									'url'   => $url,
									'prep'  => jr_mt_prep_url( $url ),
									'theme' => $ids_array['theme']
								);
								break;
							case 'cat':
								if ( !is_wp_error( get_the_category_by_ID( $key ) ) ) {
									$url = get_category_link( $key );
									$settings['url'][] = array(
										'url'   => $url,
										'prep'  => jr_mt_prep_url( $url ),
										'theme' => $ids_array['theme']
									);
								}
								/*	Ignore non-existent Categories.
									They were likely deleted.
								*/
								break;
							case 'archive':
								/*	From ?m=yyyymm query originally
								*/
								$yyyymm = $ids_array['id'];
								$year = intval( $yyyymm / 100 );
								$month = $yyyymm % 100;
								$url = get_month_link( $year, $month );
								$settings['url'][] = array(
									'url'   => $url,
									'prep'  => jr_mt_prep_url( $url ),
									'theme' => $ids_array['theme']
								);
								break;
							default:
								if ( FALSE === $ids_array['id'] ) {
									/*	Exact URL
									*/
									$url = get_home_url() . "/$key";
									$settings['url'][] = array(
										'url'   => $url,
										'prep'  => jr_mt_prep_url( $url ),
										'theme' => $ids_array['theme']
									);
								} else {
									/*	Some Post type
									
										get_permalink() can be used as early as Action Hook 'setup_theme',
										but not in 'plugins_loaded' (Fatal Error).
									*/
									if ( FALSE !== ( $url = get_permalink( $key ) ) ) {
										$settings['url'][] = array(
											'url'   => $url,
											'prep'  => jr_mt_prep_url( $url ),
											'theme' => $ids_array['theme']
										);
									}
									/*	Ignore any non-existent IDs, typically deleted.
									*/
								}
						}
					}
				}
			}
		}
		update_option( 'jr_mt_settings', $settings );
		
		unset( $internal_settings['ids'] );
		update_option( 'jr_mt_internal_settings', $internal_settings );
	}
}

?>