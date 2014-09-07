<?php
/*	Exit if .php file accessed directly
*/
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'JR_MT_WP_GET_THEMES_WORKS' ) ) {
	jr_mt_settings_cleanup();
} else {
	add_action( JR_MT_WP_GET_THEMES_ACTION, 'jr_mt_settings_cleanup', JR_MT_RUN_SECOND ); 
}

function jr_mt_settings_cleanup() {
	/*	Go through all Settings and eliminate any entries where Theme
		"does not exist", i.e. - has been deleted since Setting was originally created.
	*/
	$settings = get_option( 'jr_mt_settings' );
	$update = FALSE;
	foreach ( array( 'all_pages', 'all_posts', 'site_home', 'current' ) as $key ) {
		$theme = $settings[ $key ];
		$jr_mt_all_themes = jr_mt_all_themes();
		if ( ( '' !== $theme ) && ( !isset( $jr_mt_all_themes[ $theme ] ) ) ) {
			/*	Theme is not installed, so delete entry.
			*/
			$settings[ $key ] = '';
			$update = TRUE;
		}
	}
	foreach ( $settings['query'] as $keyword => $values ) {
		foreach ( $values as $value => $theme ) {
			if ( !isset( $jr_mt_all_themes[ $theme ] ) ) {
				unset( $settings['query'][ $keyword ][ $value ] );
				if ( empty( $settings['query'][ $keyword ] ) ) {
					unset( $settings['query'][ $keyword ] );
				}
				$update = TRUE;
			}
		}
	}
	foreach ( array( 'url', 'url_prefix', 'url_asterisk' ) as $index => $url_type ) {
		foreach ( $settings[ $url_type ] as $entry_array ) {
			if ( !isset( $jr_mt_all_themes[ $entry_array['theme'] ] ) ) {
				unset( $settings[ $url_type ][ $index ] );
				$update = TRUE;
			}
		}
	}
	$queries = $settings['query'];
	foreach ( array( 'remember', 'override' ) as $query_type ) {
		foreach ( $settings[ $query_type ]['query'] as $keyword => $values ) {
			foreach ( $values as $value => $bool ) {
				if ( !isset( $queries[ $keyword ][ $value ] ) ) {
					unset( $settings[ $query_type ]['query'][ $keyword ][ $value ] );
					$update = TRUE;
					if ( empty( $settings[ $query_type ]['query'][ $keyword ] ) ) {
						unset( $settings[ $query_type ]['query'][ $keyword ] );
					}
				}
			}
		}
	}
	if ( $update ) {
		update_option( 'jr_mt_settings', $settings );
	}
}

?>