<?php // phpcs:ignoreFile -- this is not a core file

if ( ! function_exists( 'd' ) ) {
	function d() {
		if ( func_num_args() > 0 ) {
			for ( $i = 0; $i < func_num_args(); $i ++ ) {
				var_dump( func_get_args( $i ) );
			}
		} else {
			var_dump( 'var dumping nothing' );
		}

	}
}

if ( ! function_exists( 'dd' ) ) {
	function dd() {

		if ( func_num_args() < 1 ) {
			$args = 'died';
		} else {
			$args = func_get_args();
		}

		d( $args );

		exit;
	}
}

if ( ! function_exists( 'ddsql' ) ) {
	function ddsql() {
		add_filter( 'posts_request', 'dump_sql_and_die' );
	}
}

if ( ! function_exists( 'dump_sql_and_die' ) ) {
	function dump_sql_and_die( $input ) {
		dd( $input );
	}
}

if ( ! function_exists( 'printr' ) ) {
	function printr() {
		foreach ( func_get_args() as $arg ) {
			echo '<pre>';
			print_r( $arg );
			echo '</pre>';
		}
	}
}

if ( ! function_exists( 'vardump' ) ) {
	function vardump() {
		foreach ( func_get_args() as $arg ) {
			echo '<pre>';
			var_dump( $arg );
			echo '</pre>';
		}
	}
}
