<?php

namespace WPGO_Plugins\Simple_Sitemap;

/**
 * Plugin utility functions.
 */
class Utility {

	/**
	 * Common root paths/directories.
	 *
	 * @var $module_roots
	 */
	protected $module_roots;

	/**
	 * Custom plugin data.
	 *
	 * @var array
	 */
	protected $custom_plugin_data;
	
	/**
	 * Main class constructor.
	 *
	 * @param array $module_roots Root plugin path/dir.
	 * @param array $custom_plugin_data Plugin data.
	 */
	public function __construct( $module_roots, $custom_plugin_data ) {
		$this->module_roots       = $module_roots;
		$this->custom_plugin_data = $custom_plugin_data;
	}

	/**
	 * Build attributes for the element
	 *
	 * @param array  $class_attribute Array of strings of classes names to assign this element.
	 * @param array  $style_attribute Array of style attributes for inline styling of this element.
	 * @param string $title_attribute Title for this element.
	 * @return string $el_attributes Attributes for an HTML element.
	 */
	public static function build_el_attributes( $class_attribute, $style_attribute, $title_attribute ) {
		$el_attributes = '';
		if ( ! empty( $class_attribute ) ) {
			$el_attributes .= ' class="';
			foreach ( $class_attribute as $key => $value ) {
				$el_attributes .= $value;
			}
			$el_attributes .= '"';
		}
		if ( ! empty( $style_attribute ) ) {
			$el_attributes .= ' style="';
			foreach ( $style_attribute as $key => $value ) {
				$el_attributes .= $value;
			}
			$el_attributes .= '"';
		}
		if ( ! empty( $title_attribute ) ) {
			$el_attributes .= ' title="' . $title_attribute . '"';
		}

		return $el_attributes;
	}

	/**
	 * Decode and return the JSON encoded string in the form of Object.
	 *
	 * @todo Add to framework plugin.
	 *
	 * @param string $data JSON  encoded string.
	 * @return array $new_features List of premium new available features.
	 */
	public static function filter_and_decode_json( $data ) {
		$new_features = json_decode( $data );

		if ( ss_fs()->can_use_premium_code() ) {
			// Remove all entries that are 'free-only'.
			foreach ( $new_features as $key => $new_feature ) {
				if ( 'free' === $new_feature->license ) {
					unset( $new_features[ $key ] );
				}
			}
			$new_features = array_values( $new_features ); // reindex array.
		}

		return $new_features;
	}

	/**
	 * Utilized for converting the custom styled block's border object to CSS string.
	 *
	 * @param object  $json_obj JSON encoded object.
	 * @param boolean $border_bottom Parameter for border bottom state.
	 * @param boolean $border_bottom_only Parameter for border bottom state.
	 * @param boolean $border_top_only Parameter for border top state.
	 * @param array   $args Additional arguments passed as an array.
	 * @return string CSS property and value formatted as valid CSS statement.
	 */
	public static function build_css_from_border_object( $json_obj, $border_bottom = true, $border_bottom_only = false, $border_top_only = false, $args = array() ) {
		$css_obj = json_decode( $json_obj );

		// If for any reason the parsed JSON string doesn't evaluate to an object (it should) then return no CSS.
		if ( ! is_object( $css_obj ) ) {
			return '';
		}

		// If JSON border style is in shorthand and the styles haven't been customised then output the literal value the first value is always assumed to be the border width.
		if ( property_exists( $css_obj, 'literal' ) ) {
			$literal_arr = explode( ' ', $css_obj->literal );

			if ( true === $border_bottom_only ) {
				return true === $border_bottom ? 'border-bottom: ' . $literal_arr[0] . 'px ' . $literal_arr[1] . ' ' . $literal_arr[2] . ';' : '';
			}

			if ( true === $border_top_only ) {
				return 'border-top: ' . $literal_arr[0] . 'px ' . $literal_arr[1] . ' ' . $literal_arr[2] . ';';
			}

			return 'border: ' . $literal_arr[0] . 'px ' . $literal_arr[1] . ' ' . $literal_arr[2] . ';';
		} else {
			$border_top    = 'border-top: ' . $css_obj->top->width . 'px ' . $css_obj->top->color . ' ' . $css_obj->top->style . ';';
			$border_right  = 'border-right: ' . $css_obj->right->width . 'px ' . $css_obj->right->color . ' ' . $css_obj->right->style . ';';
			$border_bottom = true === $border_bottom ? 'border-bottom: ' . $css_obj->bottom->width . 'px ' . $css_obj->bottom->color . ' ' . $css_obj->bottom->style . ';' : '';
			$border_left   = 'border-left: ' . $css_obj->left->width . 'px ' . $css_obj->left->color . ' ' . $css_obj->left->style . ';';
		}

		if ( true === $border_bottom_only ) {
			return $border_bottom;
		}

		if ( true === $border_top_only ) {
			return $border_top;
		}

		if ( '1' === $css_obj->mode ) {
			return 'border: ' . $css_obj->top->width . 'px ' . $css_obj->top->color . ' ' . $css_obj->top->style;
		}
		if ( '4' === $css_obj->mode ) {

			return $border_top . ' ' . $border_right . ' ' . $border_bottom . ' ' . $border_left;
		}
	}

	/**
	 * Gets the bool value.
	 *
	 * @param  mixed $val value to filter.
	 * @return bool
	 */
	public static function filter_boolean( $val ) {
		return filter_var( $val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	}

} /* End class definition */
