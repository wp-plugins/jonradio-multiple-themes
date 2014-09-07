<?php

/*	Exit if .php file accessed directly
*/
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

function jr_mt_theme_entry( $type, $theme = '', $display1 = NULL, $display2 = NULL ) {
	$three_dots = '&#133;';
	$before = '<li>Delete <input type="checkbox" id="del_entry" name="jr_mt_settings[del_entry][]" value="';
	$after = '" /> &nbsp; ';
	$theme_equals = 'Theme=' . wp_get_theme( $theme )->Name . '; ';
	switch ( $type ) {
		case 'Query':
			echo $before
				. 'query'
				. '='
				. $display1
				. '='
				. $display2
				. $after
				. $theme_equals;
			if ( '*' !== $display2 ) {
				$settings = get_option( 'jr_mt_settings' );
				$sticky = isset( $settings['remember']['query'][ $display1 ][ $display2 ] );
				$override = isset( $settings['override']['query'][ $display1 ][ $display2 ] );
				if ( $sticky ) {
					if ( $override ) {
						echo 'Sticky/Override ';
					} else {
						echo 'Sticky ';
					}
				} else {
					if ( $override ) {
						echo 'Override ';
					}
				}
			}
			echo 'Query='
				. '<code>'
				. home_url() 
				. "/</code>$three_dots<code>/?"
				. '<b><input type="text" readonly="readonly" disable="disabled" name="jr_mt_delkw" value="'
				. $display1
				. '" size="'
				. jr_mt_strlen( $display1 )
				. '" /></b>'
				. '=';
			if ( '*' === $display2 ) {	
				echo '</code>' . $three_dots;
			} else {
				echo '<b><input type="text" readonly="readonly" disable="disabled" name="jr_mt_delkwval" value="'
					. $display2
					. '" size="'
					. jr_mt_strlen( $display2 )
					. '" /></b></code>';
			}
			break;
		case 'url':
		case 'url_prefix':
		case 'url_asterisk':
			echo $before
				. $type
				. '='
				. 'url'
				. '='
				. $display1
				. $after
				. $theme_equals
				. $display2
				. '=<code>' . $display1 . '</code>';
			break;
		case 'wordpress':
			echo '<li><a href="'
				. get_admin_url()
				. 'themes.php" class="button-primary">Change</a> &nbsp; '
				. 'Theme='
				. wp_get_theme()->Name
				. ', the Theme chosen as Active from Appearance-Themes in the WordPress Admin panels';
			break;
		default:
			echo $before
				. $type
				. $after
				. $theme_equals
				. $display1;
			if ( 'site_home' === $type ) {
				echo ' (<code>' . home_url() . '</code>)';
			}
			echo ' setting (see Advanced Settings tab)';
			break;
	}
	echo '</li>';
}

//	$theme_name is the name of the Theme's folder within the Theme directory
function jr_mt_themes_field( $field_name, $theme_name, $setting, $excl_current_theme ) {
	echo "<select id='$field_name' name='$setting" . "[$field_name]' size='1'>";
	if ( empty( $theme_name ) ) {
		$selected = 'selected="selected"';
	} else {
		$selected = '';
	}
	echo "<option value='' $selected></option>";
	foreach ( jr_mt_all_themes() as $folder => $theme_obj ) {
		if ( $excl_current_theme ) {
			if ( ( jr_mt_current_theme( 'stylesheet' ) == $theme_obj['stylesheet'] ) && ( jr_mt_current_theme( 'template' ) == $theme_obj['template'] ) ) {
				//	Skip the Current Theme
				continue;
			}
		}
		if ( $theme_name === $folder ) {
			$selected = 'selected="selected"';
		} else {
			$selected = '';
		}
		$name = $theme_obj->Name;
		echo "<option value='$folder' $selected>$name</option>";
	}
	echo '</select>' . PHP_EOL;
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

?>