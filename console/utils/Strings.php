<?php

namespace GPlate\Utils;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Strings {

	public static function class_to_file_name( $class_name ) {
		return $class_name;
	}

	public static function name_to_namespace( $name ) {
		$parts = array_map(
			function ( $item ) {
				return ucfirst( strtolower( $item ) );
			},
			explode( '-', $name )
		);

		return implode( '_', $parts );
	}

	public static function name_to_label( $name ) {
		$parts = explode( '-', $name );

		return ucfirst( strtolower( implode( ' ', $parts ) ) );
	}

	public static function name_to_constant( $name ) {
		$parts = explode( '-', $name );

		return strtoupper( implode( '_', $parts ) );
	}

	public static function dashes_to_underscore( $name ) {
		return str_replace( '-', '_', $name );
	}

	public static function label_to_class( $label ) {
		$label = str_replace( [ '-', '_' ], ' ', $label );

		$parts = array_map(
			function ( $item ) {
				return ucfirst( strtolower( $item ) );
			},
			explode( ' ', $label )
		);

		return implode( '_', $parts );
	}

	public static function label_to_slug( $label ) {
		$parts = array_map(
			function ( $item ) {
				return strtolower( $item );
			},
			explode( ' ', $label )
		);

		return implode( '-', $parts );
	}

}
