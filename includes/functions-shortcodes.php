<?php

/**
 * Functions responsible for shortcodes management.
 *
 * @since        1.0.0
 * @package      UpFront_Shortcodes
 * @subpackage   UpFront_Shortcodes/includes
 */

/**
 * Add a shortcode.
 *
 * @since  1.0.0
 * @param array   $data    New shortcode data.
 * @param boolean $replace Replace existing shortcode or not.
 */
function su_add_shortcode( $data, $replace = true ) {
	return UpFront_Shortcodes_Shortcodes::add( $data, $replace );
}

/**
 * Remove a shortcode.
 *
 * @since  1.0.0
 * @param string  $id Shortcode ID to remove.
 */
function su_remove_shortcode( $id ) {
	return UpFront_Shortcodes_Shortcodes::remove( $id );
}

/**
 * Get all shortcodes.
 *
 * @since  1.0.0
 * @return array The collection of available shortcodes.
 */
function su_get_all_shortcodes() {
	return UpFront_Shortcodes_Shortcodes::get_all();
}

/**
 * Get specific shortcode by ID.
 *
 * @since  1.0.0
 * @param string  $id The ID (without prefix) of shortcode.
 * @return array|boolean   Shortcode data if found, False otherwise.
 */
function su_get_shortcode( $id ) {
	return UpFront_Shortcodes_Shortcodes::get( $id );
}

/**
 * Get shortcode default settings.
 *
 * @since 1.0.0
 * @param  string $id Shortcode ID.
 * @return array      Array with default settings.
 */
function su_get_shortcode_defaults( $id ) {

	$shortcode = su_get_shortcode( $id );
	$defaults  = array();

	if ( ! isset( $shortcode['atts'] ) ) {
		return $defaults;
	}

	foreach ( $shortcode['atts'] as $key => $props ) {
		$defaults[ $key ] = isset( $props['default'] ) ? $props['default'] : '';
	}

	return $defaults;

}

/**
 * Parse shortcode attribute values.
 *
 * @since  1.0.0
 * @param  string $id    Shortcode ID.
 * @param  array  $atts  Input values.
 * @param  array  $extra Additional attributes.
 * @return array         Parsed values.
 */
function su_parse_shortcode_atts( $id, $atts, $extra = array() ) {

	return shortcode_atts(
		array_merge( su_get_shortcode_defaults( $id ), $extra ),
		$atts,
		$id
	);

}

/**
 * Custom do_shortcode function for nested shortcodes
 *
 * @since  5.0.4
 * @param string  $content Shortcode content.
 * @param string  $pre     First shortcode letter.
 * @return string          Formatted content.
 */
function su_do_nested_shortcodes_alt( $content, $pre ) {

	if ( strpos( $content, '[_' ) !== false ) {
		$content = preg_replace( '@(\[_*)_(' . $pre . '|/)@', '$1$2', $content );
	}

	return do_shortcode( $content );

}

/**
 * Remove underscores from nested shortcodes.
 *
 * @since  5.0.4
 * @param string  $content   String with nested shortcodes.
 * @param string  $shortcode Shortcode tag name (without prefix).
 * @return string            Parsed string.
 */
function su_do_nested_shortcodes( $content, $shortcode ) {

	if ( get_option( 'su_option_do_nested_shortcodes_alt' ) ) {
		return su_do_nested_shortcodes_alt( $content, substr( $shortcode, 0, 1 ) );
	}

	$prefix = su_get_shortcode_prefix();

	if ( strpos( $content, '[_' . $prefix . $shortcode ) !== false ) {

		$content = str_replace(
			array( '[_' . $prefix . $shortcode, '[_/' . $prefix . $shortcode ),
			array( '[' . $prefix . $shortcode, '[/' . $prefix . $shortcode ),
			$content
		);

		return do_shortcode( $content );

	}

	return do_shortcode( wptexturize( $content ) );

}

/**
 * Get shortcode prefix.
 *
 * @since  1.0.0
 * @return string Shortcode prefix.
 */
function su_get_shortcode_prefix() {
	return get_option( 'su_option_prefix' );
}

/**
 * Do shortcodes in attributes.
 *
 * Replace braces with square brackets: {shortcode} => [shortcode], applies do_shortcode() filter.
 *
 * @since  1.0.0
 * @param string  $value Attribute value with shortcodes.
 * @return string        Parsed string.
 */
function su_do_attribute( $value ) {

	$value = str_replace( array( '{', '}' ), array( '[', ']' ), $value );
	$value = do_shortcode( $value );

	return $value;

}
