<?php

namespace Financerecruitment_Millnet\Utils;

/**
 * Class String_Utils
 */
class String_Utils {

	/**
	 * Check if a string starts with another string
	 *
	 * @param string $string
	 * @param string $start_string
	 * @return boolean
	 */
	public static function starts_with( string $string, string $start_string ) {
		$len = self::length( $start_string );

		return self::substr( $string, 0, $len ) === $start_string;
	}

	/**
	 * Check if a string ends with another string
	 *
	 * @param string $string
	 * @param string $end_string
	 * @return boolean
	 */
	public static function ends_with( string $string, string $end_string ) {
		$len = self::length( $end_string );

		if ( $len === 0 ) {
			return true;
		}

		return self::substr( $string, -$len ) === $end_string;
	}

	/**
	 * Substring a string with multibyte support
	 *
	 * @param string $string
	 * @param int $start
	 * @param int|null $length
	 * @return string
	 */
	public static function substr( string $string, int $start, $length = null ) {
		if ( function_exists( 'mb_substr' ) ) {
			if ( $length !== null ) {
				return mb_substr( $string, $start, $length );
			} else {
				return mb_substr( $string, $start );
			}
		} else {
			if ( $length !== null ) {
				return substr( $string, $start, $length );
			} else {
				return substr( $string, $start );
			}
		}
	}

	/**
	 * Check length of string with multibyte support
	 *
	 * @param string $string
	 * @return int
	 */
	public static function length( string $string ) {
		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $string );
		} else {
			return strlen( $string );
		}
	}

	/**
	 * Create tel-link from phone number
	 *
	 * @param string $phone_number
	 * @return string
	 */
	public static function make_phone_link( string $phone_number ) {
		// Changes + -signs to 00
		$telephone_link = preg_replace( '/\+/', '00', $phone_number );

		// Only allow digits
		$telephone_link = preg_replace( '/[^\d]+/', '', $telephone_link );

		// Replace 07 with 0046
		$telephone_link = preg_replace( '/^07/', '00467', $telephone_link );

		return 'tel:' . $telephone_link;
	}

	/**
	 * Separates string in two pieces and returns first part as first name and last part as lastname
	 *
	 * @param string $name
	 * @return array
	 */
	public static function separate_full_name( string $name ) {
		return [
			'first_name' => strpos( $name, ' ' ) ? explode( ' ', $name )[0] : $name,
			'last_name'  => strpos( $name, ' ' ) ? explode( ' ', $name )[1] : $name,
		];
	}

	/**
	 * Convert strings into different cases
	 * Supports snake_case, camelCase, PascalCase and kebab-case
	 *
	 * @param string $str
	 * @param string $case   e.g. 'kebab', 'camel', 'snake'
	 * @return string
	 */
	public static function convert_case( string $str, string $case ) {
		if ( $case === 'snake' ) {
			return self::to_snake_case( $str );
		}

		if ( $case === 'camel' ) {
			return self::to_camel_case( $str );
		}

		if ( $case === 'pascal' ) {
			return self::to_pascal_case( $str );
		}

		if ( $case === 'kebab' ) {
			return self::to_kebab_case( $str );
		}
	}

	/**
	 * Convert strings into camelCase
	 *
	 * @param string $str
	 * @return string
	 */
	public static function to_camel_case( string $str ) {
		$str = preg_replace_callback(
			'/[A-Z]{3,}/',
			function ( $m ) {
				return ucfirst( strtolower( $m[0] ) );
			},
			$str
		);

		$str = preg_replace_callback(
			'/([^A-Za-z0-9]+([A-Za-z]))/',
			function ( $m ) {
				return ucfirst( $m[2] );
			},
			$str
		);

		return lcfirst( $str );
	}

	/**
	 * Convert strings into PascalCase
	 *
	 * @param string $str
	 * @return string
	 */
	public static function to_pascal_case( string $str ) {
		return ucfirst( self::to_camel_case( $str ) );
	}

	/**
	 * Convert strings into kebab-case
	 *
	 * @param string $str
	 * @return string
	 */
	public static function to_kebab_case( string $str ) {
		$str = preg_replace_callback(
			'/([^A-Za-z0-9]+([A-Za-z0-9]))|([a-z]([A-Z]))/',
			function ( $m ) {
				if ( ! empty( $m[2] ) ) {
					return '-' . $m[2];
				}

				if ( ! empty( $m[4] ) ) {
					return $m[3][0] . '-' . $m[4];
				}
			},
			$str
		);

		return preg_replace( '/^[^A-Za-z0-9]+/', '', strtolower( $str ) );
	}

	/**
	 * Convert strings into snake_case
	 *
	 * @param string $str
	 * @return string
	 */
	public static function to_snake_case( string $str ) {
		return str_replace( '-', '_', self::to_kebab_case( $str ) );
	}

	/**
	 * Truncate string if it exceeds the specified length
	 *
	 * @param string $str
	 * @param int $length
	 * @return string
	 */
	public static function truncate( string $str, int $length = 100 ) {
		$str_length = function_exists( 'mb_strlen' ) ? mb_strlen( $str ) : strlen( $str );

		if ( $str_length <= $length ) {
			return $str;
		}

		return ( function_exists( 'mb_substr' ) ? mb_substr( $str, 0, $length - 3 ) : substr( $str, 0, $length - 3 ) ) . '...';
	}
}
