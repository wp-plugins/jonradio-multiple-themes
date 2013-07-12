<?php
//	Exit if .php file accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/*	require_once( jr_mt_path() . 'includes/debug/debug.php' );
jr_dump( 'jr_mt_validate_settings $input', $input );
*/

function jr_dump( $comment, $dump_var ) {
	$file_name = 'jonradio-dump.txt';
	
	$header = '***' . current_time('mysql') . ': ' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	ob_start();;
	echo "$comment: ";
	var_dump( $dump_var );
	$output = ob_get_clean() . jr_dump_env();
	if ( function_exists('is_multisite') && is_multisite() ) {
		global $site_id, $blog_id;
		$file_name = $site_id . '-' . $blog_id . '-' . $file_name;
	}
	$file = fopen( plugin_dir_path( __FILE__ ) . $file_name, 'at' );
	fwrite( $file, $header . PHP_EOL . $output );
	fclose( $file );

	return;
}

function jr_dump_env() {
	global $jr_dump_env_first;
	if ( isset( $jr_dump_env_first ) ) {
		$output = '';
	} else {
		$jr_dump_env_first = FALSE;
		$output = PHP_EOL;
		
	}
	return $output;
}
?>