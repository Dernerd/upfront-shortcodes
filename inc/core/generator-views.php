<?php
/**
 * Shortcode Generator
 */
class Su_Generator_Views {

	/**
	 * Constructor
	 */
	function __construct() {}

	public static function text( $id, $field ) {
		$field = wp_parse_args( $field, array(
			'default' => ''
		) );
		$return = '<input type="text" name="' . $id . '" value="' . esc_attr( $field['default'] ) . '" id="su-generator-attr-' . $id . '" class="su-generator-attr" />';
		return $return;
	}

	public static function textarea( $id, $field ) {
		$field = wp_parse_args( $field, array(
			'rows'    => 3,
			'default' => ''
		) );
		$return = '<textarea name="' . $id . '" id="su-generator-attr-' . $id . '" rows="' . $field['rows'] . '" class="su-generator-attr">' . esc_textarea( $field['default'] ) . '</textarea>';
		return $return;
	}

	public static function select( $id, $field ) {

		// Multiple selects
		$multiple = isset( $field['multiple'] ) && $field['multiple'] ? ' multiple' : '';
		$return = '<select name="' . $id . '" id="su-generator-attr-' . $id . '" class="su-generator-attr"' . $multiple . '>';
		// Create options
		foreach ( $field['values'] as $option_value => $option_title ) {
			// Is this option selected
			$selected = ( $field['default'] === $option_value ) ? ' selected="selected"' : '';
			// Create option
			$return .= '<option value="' . $option_value . '"' . $selected . '>' . $option_title . '</option>';
		}
		$return .= '</select>';
		return $return;

	}

	public static function post_type( $id, $field ) {

		// Get post types
		$types = get_post_types( array(), 'objects', 'or' );

		// Prepare empty array for values
		$field['values'] = array(
			'any' => _x( 'Beliebiger Beitragstyp', 'upfront-shortcodes' ),
		);

		// Fill the array
		foreach( $types as $type ) {
			$field['values'][$type->name] = $type->label;
		}

		// Create select
		return self::select( $id, $field );

	}

	public static function taxonomy( $id, $field ) {

		// Get taxonomies
		$taxonomies = get_taxonomies( array(), 'objects', 'or' );

		// Prepare empty array for values
		$field['values'] = array();

		// Fill the array
		foreach( $taxonomies as $taxonomy ) {
			$field['values'][$taxonomy->name] = $taxonomy->label;
		}

		// Create select
		return self::select( $id, $field );

	}

	public static function term( $id, $field ) {

		// Get categories
		$field['values'] = Su_Generator::get_terms( 'category' );

		// Create select
		return self::select( $id, $field );

	}

	public static function bool( $id, $field ) {
		$return = '<span class="su-generator-switch su-generator-switch-' . $field['default'] . '"><span class="su-generator-yes">' . __( 'Ja', 'upfront-shortcodes' ) . '</span><span class="su-generator-no">' . __( 'Nein', 'upfront-shortcodes' ) . '</span></span><input type="hidden" name="' . $id . '" value="' . esc_attr( $field['default'] ) . '" id="su-generator-attr-' . $id . '" class="su-generator-attr su-generator-switch-value" />';
		return $return;
	}

	public static function upload( $id, $field ) {
		$return = '<input type="text" name="' . $id . '" value="' . esc_attr( $field['default'] ) . '" id="su-generator-attr-' . $id . '" class="su-generator-attr su-generator-upload-value" /><div class="su-generator-field-actions"><a href="javascript:;" class="button su-generator-upload-button"><img src="' . admin_url( '/images/media-button.png' ) . '" alt="' . __( 'Medien-Manager', 'upfront-shortcodes' ) . '" />' . __( 'Medien-Manager', 'upfront-shortcodes' ) . '</a></div>';
		return $return;
	}

	public static function icon( $id, $field ) {
		$return = '<input type="text" name="' . $id . '" value="' . esc_attr( $field['default'] ) . '" id="su-generator-attr-' . $id . '" class="su-generator-attr su-generator-icon-picker-value" /><div class="su-generator-field-actions"><a href="javascript:;" class="button su-generator-upload-button su-generator-field-action"><img src="' . admin_url( '/images/media-button.png' ) . '" alt="' . __( 'Medien-Manager', 'upfront-shortcodes' ) . '" />' . __( 'Medien-Manager', 'upfront-shortcodes' ) . '</a> <a href="javascript:;" class="button su-generator-icon-picker-button su-generator-field-action"><img src="' . admin_url( '/images/media-button-other.gif' ) . '" alt="' . __( 'Symbol Picker', 'upfront-shortcodes' ) . '" />' . __( 'Symbol Picker', 'upfront-shortcodes' ) . '</a></div><div class="su-generator-icon-picker su-generator-clearfix"><input type="text" class="widefat" placeholder="' . __( 'Filter Symbole', 'upfront-shortcodes' ) . '" /></div>';
		return $return;
	}

	public static function color( $id, $field ) {
		$return = '<span class="su-generator-select-color"><span class="su-generator-select-color-wheel"></span><input type="text" name="' . $id . '" value="' . $field['default'] . '" id="su-generator-attr-' . $id . '" class="su-generator-attr su-generator-select-color-value" /></span>';
		return $return;
	}

	public static function number( $id, $field ) {
		$return = '<input type="number" name="' . $id . '" value="' . esc_attr( $field['default'] ) . '" id="su-generator-attr-' . $id . '" min="' . $field['min'] . '" max="' . $field['max'] . '" step="' . $field['step'] . '" class="su-generator-attr" />';
		return $return;
	}

	public static function slider( $id, $field ) {
		$return = '<div class="su-generator-range-picker su-generator-clearfix"><input type="number" name="' . $id . '" value="' . esc_attr( $field['default'] ) . '" id="su-generator-attr-' . $id . '" min="' . $field['min'] . '" max="' . $field['max'] . '" step="' . $field['step'] . '" class="su-generator-attr" /></div>';
		return $return;
	}

	public static function shadow( $id, $field ) {
		$defaults = ( $field['default'] === 'none' ) ? array ( '0', '0', '0', '#000000' ) : explode( ' ', str_replace( 'px', '', $field['default'] ) );
		$return = '<div class="su-generator-shadow-picker"><span class="su-generator-shadow-picker-field"><input type="number" min="-1000" max="1000" step="1" value="' . $defaults[0] . '" class="su-generator-sp-hoff" /><small>' . __( 'Horizontal offset', 'upfront-shortcodes' ) . ' (px)</small></span><span class="su-generator-shadow-picker-field"><input type="number" min="-1000" max="1000" step="1" value="' . $defaults[1] . '" class="su-generator-sp-voff" /><small>' . __( 'Vertikaler Versatz', 'upfront-shortcodes' ) . ' (px)</small></span><span class="su-generator-shadow-picker-field"><input type="number" min="-1000" max="1000" step="1" value="' . $defaults[2] . '" class="su-generator-sp-blur" /><small>' . __( 'Blur', 'upfront-shortcodes' ) . ' (px)</small></span><span class="su-generator-shadow-picker-field su-generator-shadow-picker-color"><span class="su-generator-shadow-picker-color-wheel"></span><input type="text" value="' . $defaults[3] . '" class="su-generator-shadow-picker-color-value" /><small>' . __( 'Farbe', 'upfront-shortcodes' ) . '</small></span><input type="hidden" name="' . $id . '" value="' . esc_attr( $field['default'] ) . '" id="su-generator-attr-' . $id . '" class="su-generator-attr" /></div>';
		return $return;
	}

	public static function border( $id, $field ) {
		$defaults = ( $field['default'] === 'none' ) ? array ( '0', 'solid', '#000000' ) : explode( ' ', str_replace( 'px', '', $field['default'] ) );
		$borders = su_html_dropdown( array(
				'options' => su_get_config( 'borders' ),
				'class' => 'su-generator-bp-style',
				'selected' => $defaults[1]
			) );
		$return = '<div class="su-generator-border-picker"><span class="su-generator-border-picker-field"><input type="number" min="-1000" max="1000" step="1" value="' . $defaults[0] . '" class="su-generator-bp-width" /><small>' . __( 'Rahmenbreite', 'upfront-shortcodes' ) . ' (px)</small></span><span class="su-generator-border-picker-field">' . $borders . '<small>' . __( 'Rahmenstil', 'upfront-shortcodes' ) . '</small></span><span class="su-generator-border-picker-field su-generator-border-picker-color"><span class="su-generator-border-picker-color-wheel"></span><input type="text" value="' . $defaults[2] . '" class="su-generator-border-picker-color-value" /><small>' . __( 'Rahmenfarbe', 'upfront-shortcodes' ) . '</small></span><input type="hidden" name="' . $id . '" value="' . esc_attr( $field['default'] ) . '" id="su-generator-attr-' . $id . '" class="su-generator-attr" /></div>';
		return $return;
	}

	public static function image_source( $id, $field ) {
		$field = wp_parse_args( $field, array(
				'default' => 'none'
			) );

		if ( ! isset( $field['media_sources'] ) ) {
			$field['media_sources'] = array(
				'media'         => __( 'Medienbibliothek', 'upfront-shortcodes' ),
				'posts: recent' => __( 'Kürzliche Beiträge', 'upfront-shortcodes' ),
				'taxonomy'      => __( 'Taxonomie', 'upfront-shortcodes' ),
			);
		}

		$sources = su_html_dropdown( array(
				'options'  => $field['media_sources'],
				'selected' => '0',
				'none'     => __( 'Wähle die Bildquelle aus', 'upfront-shortcodes' ) . '&hellip;',
				'class'    => 'su-generator-isp-sources'
			) );
		$categories = su_html_dropdown( array(
				'options'  => Su_Generator::get_terms( 'category' ),
				'multiple' => true,
				'size'     => 10,
				'class'    => 'su-generator-isp-categories'
			) );
		$taxonomies = su_html_dropdown( array(
				'options'  => Su_Generator::get_taxonomies(),
				'none'     => __( 'Wähle Taxonomie', 'upfront-shortcodes' ) . '&hellip;',
				'selected' => '0',
				'class'    => 'su-generator-isp-taxonomies'
			) );
		$terms = su_html_dropdown( array(
				'class'    => 'su-generator-isp-terms',
				'multiple' => true,
				'size'     => 10,
				'disabled' => true,
				'style'    => 'display:none'
			) );
		$return = '<div class="su-generator-isp">' . $sources . '<div class="su-generator-isp-source su-generator-isp-source-media"><div class="su-generator-clearfix"><a href="javascript:;" class="button button-primary su-generator-isp-add-media"><i class="sui sui-plus"></i>&nbsp;&nbsp;' . __( 'Füge Bilder hinzu', 'upfront-shortcodes' ) . '</a></div><div class="su-generator-isp-images su-generator-clearfix"><em class="description">' . __( 'Klicke auf die Schaltfläche oben und wähle Bilder aus.<br>Du kannst mehrere Bilder mit der Strg-Taste (Cmd) auswählen', 'upfront-shortcodes' ) . '</em></div></div><div class="su-generator-isp-source su-generator-isp-source-category"><em class="description">' . __( 'Wähle Kategorien aus, aus denen Beiträge abgerufen werden sollen. <br>Mit der Strg-Taste (Cmd) kannst Du mehrere Kategorien auswählen', 'upfront-shortcodes' ) . '</em>' . $categories . '</div><div class="su-generator-isp-source su-generator-isp-source-taxonomy"><em class="description">' . __( 'Wähle die Taxonomie und ihre Begriffe aus.<br>Du kannst mehrere Begriffe mit der Strg-Taste (Cmd) auswählen', 'upfront-shortcodes' ) . '</em>' . $taxonomies . $terms . '</div><input type="hidden" name="' . $id . '" value="' . $field['default'] . '" id="su-generator-attr-' . $id . '" class="su-generator-attr" /></div>';
		return $return;
	}

	public static function extra_css_class( $id, $field ) {
		$field = wp_parse_args( $field, array(
			'default' => ''
		) );
		$return = '<input type="text" name="' . $id . '" value="' . esc_attr( $field['default'] ) . '" id="su-generator-attr-' . $id . '" class="su-generator-attr" />';
		return $return;
	}

}
