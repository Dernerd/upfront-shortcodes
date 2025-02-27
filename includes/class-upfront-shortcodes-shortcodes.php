<?php

/**
 * The class responsible for adding, storing and accessing shortcodes data.
 *
 * @since  1.0.0
 * @package      UpFront_Shortcodes
 * @subpackage   UpFront_Shortcodes/includes
 */
class UpFront_Shortcodes_Shortcodes {

	/**
	 * The collection of available shortcodes.
	 *
	 * @since  1.0.0
	 * @var array
	 */
	private static $shortcodes = array();

	/**
	 * Get all shortcodes.
	 *
	 * @since  1.0.0
	 * @return array The collection of available shortcodes.
	 */
	public static function get_all() {

		$shortcodes = apply_filters( 'su/data/shortcodes', self::$shortcodes );
		$shortcodes = self::add_ids( $shortcodes );

		return $shortcodes;

	}

	/**
	 * Get specific shortcode by ID.
	 *
	 * @since  1.0.0
	 * @param string  $id The ID (without prefix) of shortcode.
	 * @return array|boolean   Shortcode data if found, False otherwise.
	 */
	public static function get( $id ) {

		$shortcodes = self::get_all();

		return isset( $shortcodes[ $id ] )
			? $shortcodes[ $id ]
			: false;

	}

	/**
	 * Add a shortcode.
	 *
	 * @since  1.0.0
	 * @param array   $data    New shortcode data.
	 * @param boolean $replace Replace existing shortcode or not.
	 */
	public static function add( $data = array(), $replace = true ) {

		if ( ! isset( $data['id'], $data['callback'] ) ) {

			trigger_error( 'Shortcode wurde nicht hinzugefügt. Fehlende erforderliche Parameter (ID, Rückruf).' );

			return;

		}

		if ( ! $replace && self::get( $data['id'] ) ) {
			return;
		}

		self::$shortcodes[ $data['id'] ] = $data;

	}

	/**
	 * Remove a shortcode.
	 *
	 * @since  1.0.0
	 * @param string  $id Shortcode ID to remove.
	 */
	public static function remove( $id ) {

		if ( isset( self::$shortcodes[ $id ] ) ) {
			unset( self::$shortcodes[ $id ] );
		}

	}

	/**
	 * Register all available shortcodes.
	 *
	 * @since  1.0.0
	 */
	public static function register() {

		$shortcodes = self::get_all();
		$prefix = su_get_shortcode_prefix();

		foreach ( $shortcodes as $id => $shortcode ) {

			if ( isset( $shortcode['callback'] ) && is_callable( $shortcode['callback'] ) ) {
				$callback = $shortcode['callback'];
			}

			elseif ( isset( $shortcode['function'] ) && is_callable( $shortcode['function'] ) ) {
				$callback = $shortcode['function'];
			}

			else {
				continue;
			}

			add_shortcode( $prefix . $id, $callback );

		}

	}

	public static function add_ids( $shortcodes ) {

		foreach ( $shortcodes as $id => $shortcode ) {
			$shortcodes[ $id ] = array_merge( array( 'id' => $id ), (array) $shortcode );
		}

		return $shortcodes;

	}

}
