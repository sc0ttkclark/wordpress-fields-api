<?php
/**
 * Fields API Field Class
 *
 * Handles saving and sanitizing of fields.
 *
 * @package    WordPress
 * @subpackage Fields_API
 */
class WP_Fields_API_Field extends WP_Fields_API_Component {

	/**
	 * Field type
	 *
	 * @var string
	 */
	public $type = 'text';

	/**
	 * Default value for field
	 *
	 * @var string
	 */
	public $default = '';

	/**
	 * Server-side sanitization callback for the field's value.
	 *
	 * @var callback
	 */
	public $sanitize_callback;
	public $sanitize_js_callback;

	protected $id_data = array();

	/**
	 * register_meta Auth Callback.
	 *
	 * @access public
	 *
	 * @see register_meta
	 *
	 * @var callable
	 */
	public $meta_auth_callback;

	/**
	 * Whether to show field in REST API.
	 *
	 * @access public
	 *
	 * @see register_meta
	 *
	 * @var bool
	 */
	public $show_in_rest = false;

	/**
	 * Whether to render this field in forms.
	 *
	 * @access public
	 * @var boolean
	 */
	public $can_render = false;

	/**
	 * Value Callback.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Field::value()
	 *
	 * @var callable Callback is called with two arguments, the item ID and the instance of
	 *               WP_Fields_API_Field. It returns a string for the value to use.
	 */
	public $value_callback;

	/**
	 * Update Value Callback.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Field::update()
	 *
	 * @var callable Callback is called with three arguments, the value being saved, the item ID, and the instance of
	 *               WP_Fields_API_Field.
	 */
	public $update_value_callback;

	/**
	 * Whether or not a field is
	 *
	 * @access public
	 *
	 * @var bool
	 */
	public $internal = false;

	/**
	 * Check user capabilities and theme supports, and then save
	 * the value of the field.
	 *
	 * @param mixed    $value    The value to save.
	 * @param int|null $item_id  The Item ID.
	 * @param boolean  $sanitize Sanitize value before saving
	 *
	 * @return false|mixed False if cap check fails or value isn't set.
	 */
	public function save( $value, $item_id = null, $sanitize = false ) {

		if ( null === $item_id ) {
			$item_id = $this->get_item_id();
		}

		if ( ! $this->check_capabilities() || false === $value ) {
			return false;
		}

		if ( $sanitize ) {
			$value = $this->sanitize( $value );

			if ( is_wp_error( $value ) ) {
				return $value;
			}
		}

		/**
		 * Fires when the WP_Fields_API_Field::save() method is called.
		 *
		 * The dynamic portion of the hook name, `$this->id_data['base']` refers to
		 * the base slug of the field name.
		 *
		 * @param mixed $value The value being saved.
		 * @param int $item_id The item ID.
		 * @param WP_Fields_API_Field $this {@see WP_Fields_API_Field} instance.
		 *
		 * @return string The value to save
		 */
		$value = apply_filters( 'field_save_' . $this->object_type . '_' . $this->id_data[ 'base' ], $value, $item_id, $this );

		return $this->update( $value, $item_id );

	}

	/**
	 * Sanitize an input.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return mixed Null if an input isn't valid, otherwise the sanitized value.
	 */
	public function sanitize( $value ) {

		$value = wp_unslash( $value );

		if ( is_callable( $this->sanitize_callback ) ) {
			$value = call_user_func( $this->sanitize_callback, $value, $this );
		}

		/**
		 * Filter a Customize field value in un-slashed form.
		 *
		 * @param mixed               $value Value of the field.
		 * @param WP_Fields_API_Field $this  WP_Fields_API_Field instance.
		 */
		return apply_filters( "fields_sanitize_{$this->object_type}_{$this->object_subtype}_{$this->id}", $value, $this );

	}

	/**
	 * Save the value of the field, using the related API.
	 *
	 * @param mixed $value The value to update.
	 *
	 * @return mixed The result of saving the value.
	 */
	protected function update( $value ) {

		// @todo Support post / term / user / comment object field updates

		$item_id = func_get_arg(1);

		switch ( $this->object_type ) {
			case is_callable( $this->update_value_callback ) :
				return call_user_func( $this->update_value_callback, $value, $item_id, $this );

			case 'customizer' :
				return $this->_update_theme_mod( $value );

			case 'settings' :
				return $this->_update_option( $value );

			case 'post' :
			case 'term' :
			case 'user' :
			case 'comment' :
				return $this->_update_meta( $this->object_type, $value, $item_id );

			default :

				/**
				 * Fires when the {@see WP_Fields_API_Field::update()} method is called for fields
				 * not handled as theme_mods or options.
				 *
				 * The dynamic portion of the hook name, `$this->object_type`, refers to the type of field.
				 *
				 * @param mixed               $value   Value of the field.
				 * @param int                 $item_id Item ID.
				 * @param WP_Fields_API_Field $this    WP_Fields_API_Field instance.
				 */
				do_action( "fields_update_{$this->object_type}", $value, $item_id, $this );
		}

		return null;

	}

	/**
	 * Update the theme mod from the value of the parameter.
	 *
	 * @param mixed $value The value to update.
	 *
	 * @return null
	 */
	protected function _update_theme_mod( $value ) {

		if ( is_null( $value ) ) {
			remove_theme_mod( $this->id_data['base'] );
		}

		// Handle non-array theme mod.
		if ( empty( $this->id_data['keys'] ) ) {
			set_theme_mod( $this->id_data['base'], $value );
		} else {
			// Handle array-based theme mod.
			$mods = get_theme_mod( $this->id_data['base'] );
			$mods = $this->multidimensional_replace( $mods, $this->id_data['keys'], $value );

			if ( isset( $mods ) ) {
				set_theme_mod( $this->id_data['base'], $mods );
			}
		}

		return null;

	}

	/**
	 * Update the option from the value of the field.
	 *
	 * @param mixed $value The value to update.
	 *
	 * @return bool|null The result of saving the value.
	 */
	protected function _update_option( $value ) {

		if ( is_null( $value ) ) {
			delete_option( $this->id_data['base'] );
		}

		// Handle non-array option.
		if ( empty( $this->id_data['keys'] ) ) {
			return update_option( $this->id_data['base'], $value );
		}

		// Handle array-based options.
		$options = get_option( $this->id_data['base'] );
		$options = $this->multidimensional_replace( $options, $this->id_data['keys'], $value );

		if ( isset( $options ) ) {
			return update_option( $this->id_data['base'], $options );
		}

		return null;

	}

	/**
	 * Update the meta from the value of the field.
	 *
	 * @param string $meta_type The meta type.
	 * @param mixed  $value     The value to update.
	 * @param int    $item_id   Item ID.
	 *
	 * @return bool|null The result of saving the value.
	 */
	protected function _update_meta( $meta_type, $value, $item_id = 0 ) {

		if ( is_null( $value ) ) {
			delete_metadata( $meta_type, $item_id, $this->id_data['base'] );
		}

		// Handle non-array option.
		if ( empty( $this->id_data['keys'] ) ) {
			return update_metadata( $meta_type, $item_id, $this->id_data['base'], $value );
		}

		// Handle array-based keys.
		$keys = get_metadata( $meta_type, 0, $this->id_data['base'] );
		$keys = $this->multidimensional_replace( $keys, $this->id_data['keys'], $value );

		if ( isset( $keys ) ) {
			return update_metadata( $meta_type, $item_id, $this->id_data['base'], $keys );
		}

		return null;

	}

	/**
	 * Fetch the value of the field.
	 *
	 * @return mixed The value.
	 */
	public function value() {

		$item_id = func_get_arg(0);

		switch ( $this->object_type ) {
			case is_callable( $this->value_callback ) :
				$value = call_user_func( $this->value_callback, $item_id, $this );
				$value = $this->multidimensional_get( $value, $this->id_data['keys'], $this->default );

				break;
			case 'post' :
			case 'term' :
			case 'user' :
			case 'comment' :
				$value = $this->get_object_value( $item_id );
				$value = $this->multidimensional_get( $value, $this->id_data['keys'], $this->default );
				break;

			case 'customizer' :
			case 'settings' :
				$value = $this->get_option_value();
				$value = $this->multidimensional_get( $value, $this->id_data['keys'], $this->default );
				break;

			default :
				/**
				 * Filter a field value for a custom object type.
				 *
				 * The dynamic portion of the hook name, `$this->id_date['base']`, refers to
				 * the base slug of the field name.
				 *
				 * For fields handled as theme_mods, options, or object fields, see those corresponding
				 * functions for available hooks.
				 *
				 * @param mixed $default The field default value. Default empty.
				 * @param int   $item_id (optional) The Item ID.
				 */
				$value = apply_filters( 'fields_value_' . $this->object_type . '_' . $this->object_subtype . '_' . $this->id_data['base'], $this->default, $item_id );
				
				/**
				 * Fires when the {@see WP_Fields_API_Field::value()} method is called for fields
				 * not handled as theme_mods or options.
				 *
				 * The dynamic portion of the hook name, `$this->object_type`, refers to the type of field.
				 *
				 * @param mixed               $value   Default value of the field.
				 * @param int                 $item_id Item ID.
				 * @param WP_Fields_API_Field $this    WP_Fields_API_Field instance.
				 */
				$value = apply_filters( "fields_value_{$this->object_type}", $value, $item_id, $this );

				break;
		}

		return $value;

	}

	/**
	 * Get value from meta / object
	 *
	 * @param int $item_id
	 *
	 * @return mixed|null
	 */
	public function get_object_value( $item_id ) {

		$value = null;
		$object = null;

		$field_key = $this->id_data['base'];

		switch ( $this->object_type ) {
			case 'post' :
				$object = get_post( $item_id );
				break;

			case 'term' :
				$object = get_term( $item_id );
				break;

			case 'user' :
				$object = get_userdata( $item_id );
				break;

			case 'comment' :
				$object = get_comment( $item_id );
				break;
		}

		if ( $object && ! is_wp_error( $object ) && isset( $object->{$field_key} ) ) {
			// Get value from object
			$value = $object->{$field_key};
		} else {
			// Get value from meta
			$value = get_metadata( $this->object_type, $item_id, $field_key );

			if ( array() === $value ) {
				$value = $this->default;
			}
		}

		return $value;

	}

	/**
	 * Get value from option / theme_mod
	 *
	 * @return mixed|void
	 */
	public function get_option_value() {

		$function = '';
		$value = null;

		switch ( $this->object_type ) {
			case 'customizer' :
				$function = 'get_theme_mod';
				break;

			case 'settings' :
				$function = 'get_option';
				break;
		}

		if ( is_callable( $function ) ) {
			// Handle non-array value
			if ( empty( $this->id_data['keys'] ) ) {
				return $function( $this->id_data['base'], $this->default );
			}

			// Handle array-based value
			$value = $function( $this->id_data['base'] );
		}

		return $value;

	}

	/**
	 * Sanitize the field's value for use in JavaScript.
	 *
	 * @return mixed The requested escaped value.
	 */
	public function js_value() {

		$value = $this->value();

		if ( is_callable( $this->sanitize_js_callback ) ) {
			$value = call_user_func( $this->sanitize_js_callback, $value, $this );
		}

		/**
		 * Filter a Customize field value for use in JavaScript.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to the field ID.
		 *
		 * @param mixed                $value The field value.
		 * @param WP_Fields_API_Field $this  {@see WP_Fields_API_Field} instance.
		 */
		$value = apply_filters( "fields_sanitize_js_{$this->object_type}_{$this->object_subtype}_{$this->id}", $value, $this );

		if ( is_string( $value ) ) {
			return html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
		}

		return $value;

	}

	/**
	 * Validate user capabilities whether the theme supports the field.
	 *
	 * @return bool False if theme doesn't support the section or user can't change section, otherwise true.
	 */
	public function check_capabilities() {

		if ( $this->capability && ! call_user_func_array( 'current_user_can', (array) $this->capability ) ) {
			return false;
		}

		if ( $this->theme_supports && ! call_user_func_array( 'current_theme_supports', (array) $this->theme_supports ) ) {
			return false;
		}

		$access = true;

		if ( is_callable( $this->capabilities_callback ) ) {
			$access = call_user_func( $this->capabilities_callback, $this );
		}

		return $access;

	}

	/**
	 * Multidimensional helper function.
	 *
	 * @param      $root
	 * @param      $keys
	 * @param bool $create Default is false.
	 *
	 * @return null|array Keys are 'root', 'node', and 'key'.
	 */
	final protected function multidimensional( &$root, $keys, $create = false ) {

		if ( $create && empty( $root ) ) {
			$root = array();
		}

		if ( ! isset( $root ) || empty( $keys ) ) {
			return null;
		}

		$last = array_pop( $keys );
		$node = &$root;

		foreach ( $keys as $key ) {
			if ( $create && ! isset( $node[ $key ] ) ) {
				$node[ $key ] = array();
			}

			if ( ! is_array( $node ) || ! isset( $node[ $key ] ) ) {
				return null;
			}

			$node = &$node[ $key ];
		}

		if ( $create ) {
			if ( ! is_array( $node ) ) {
				// account for an array overriding a string or object value
				$node = array();
			}
			if ( ! isset( $node[ $last ] ) ) {
				$node[ $last ] = array();
			}
		}

		if ( ! isset( $node[ $last ] ) ) {
			return null;
		}

		return array(
			'root' => &$root,
			'node' => &$node,
			'key'  => $last,
		);

	}

	/**
	 * Will attempt to replace a specific value in a multidimensional array.
	 *
	 * @param string $root
	 * @param array  $keys
	 * @param mixed  $value The value to update.
	 *
	 * @return string
	 */
	final protected function multidimensional_replace( $root, $keys, $value ) {

		if ( ! isset( $value ) ) {
			return $root;
		} elseif ( empty( $keys ) ) {
			// If there are no keys, we're replacing the root.
			return $value;
		}

		$result = $this->multidimensional( $root, $keys, true );

		if ( isset( $result ) ) {
			$result['node'][ $result['key'] ] = $value;
		}

		return $root;

	}

	/**
	 * Will attempt to fetch a specific value from a multidimensional array.
	 *
	 * @param       $root
	 * @param       $keys
	 * @param mixed $default A default value which is used as a fallback. Default is null.
	 *
	 * @return mixed The requested value or the default value.
	 */
	final protected function multidimensional_get( $root, $keys, $default = null ) {

		// If there are no keys, test the root.
		if ( empty( $keys ) ) {
			if ( isset( $root ) ) {
				return $root;
			}
		} else {
			$result = $this->multidimensional( $root, $keys );

			if ( isset( $result ) ) {
				return $result['node'][ $result['key'] ];
			}
		}

		return $default;

	}

	/**
	 * Will attempt to check if a specific value in a multidimensional array is set.
	 *
	 * @param $root
	 * @param $keys
	 *
	 * @return bool True if value is set, false if not.
	 */
	final protected function multidimensional_isset( $root, $keys ) {

		$result = $this->multidimensional_get( $root, $keys );

		return isset( $result );

	}
}

/**
 * A field that is used to filter a value, but will not save the results.
 *
 * Results should be properly handled using another field or callback.
 *
 * @package    WordPress
 * @subpackage Fields_API
 */
class WP_Fields_API_Filter_Field extends WP_Fields_API_Field {

	/**
	 * Save the value of the field, using the related API.
	 *
	 * @param mixed $value The value to update.
	 *
	 * @return mixed The result of saving the value.
	 */
	public function update( $value ) {

		return null;

	}
}