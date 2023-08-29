<?php

final class Fields_Registry {
	protected $datastores = [];

	protected $datastore_types = [];

	protected $control_types = [];

	protected static $instance = null;
	protected        $controls = [];

	/**
	 * @return $this
	 */
	public static function get_instance() : Fields_Registry {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function register_datastore_type( string $type, string $instance ) {
		if ( ! isset( $this->datastore_types[ $type ] ) ) {
			$this->datastore_types[ $type ] = $instance;
		}

		return $this;
	}

	public function register_control_type( string $type, string $instance ) {
		if ( ! isset( $this->control_types[ $type ] ) ) {
			$this->control_types[ $type ] = $instance;
		}

		return $this;
	}

	/**
	 * @param string $type
	 *
	 * @return string|null
	 */
	protected function get_field_control_type( string $type ) {
		return isset( $this->control_types[ $type ] ) && is_string( $this->control_types[ $type ] ) && is_subclass_of( $this->control_types[ $type ], Field_Control::class ) ? $this->control_types[ $type ] : null;
	}

	/**
	 * @param string $type
	 *
	 * @return string|null
	 */
	protected function get_field_datastore_type( string $type ) {
		return isset( $this->datastore_types[ $type ] ) && is_string( $this->datastore_types[ $type ] ) && is_subclass_of( $this->datastore_types[ $type ], Field_Data_Store::class ) ? $this->datastore_types[ $type ] : null;
	}

	public function register_json( string $file ) {
		$fields = json_decode( file_get_contents( $file ), true );

		foreach ( $fields as $field ) {
			if ( isset( $field['datastore'] ) && is_array( $field['datastore'] ) && isset( $field['controls'] ) && is_array( $field['controls'] ) ) {
				$id             = isset( $field['id'] ) && is_string( $field['id'] ) ? $field['id'] : null;
				$datastore      = $this->get_field_datastore_type( $field['datastore']['type'] ?? '' );
				$datastore_args = $field['datastore'];

				if ( $id && $datastore ) {
					$this->register_datastore( $id, $datastore, $datastore_args );
				}

				foreach ( $field['controls'] as $control_id => $control_data ) {
					$control    = $this->get_field_control_type( $control_data['type'] ?? '' );

					if ( $id && $control && $control_id ) {
						$this->register_control( $id, $control_id, $control, $control_data );
					}
				}
			}
		}
	}

	protected function get_field_datastore_instance( string $id ) {
		$type = $this->datastores[ $id ]['type'];

		return $this->datastore_types[ $type ];
	}

	protected function get_field_control_instance( string $id, string $control_id ) : array {
		$control = $this->controls[ $id ][ $control_id ];

		$type = $control['type'];

		return $this->control_types[ $type ];
	}

	/**
	 * @param string $id
	 * @param string $datastore
	 * @param array  $args
	 *
	 * @return $this
	 */
	public function register_datastore( string $id, string $datastore, array $args = [] ) : Fields_Registry {
		if ( ! isset( $this->datastores[ $id ] ) ) {
			$args['datastore']       = $datastore;
			$this->datastores[ $id ] = $args;

			// Some field types require their own registrations independent of the settings.
			if ( is_subclass_of( $datastore, Registrable_Field_Data_Store::class ) ) {
				$datastore::register( $id, $args );
			}
		}

		return $this;
	}

	/**
	 * @param string       $id
	 * @param string       $control_id
	 * @param class-string $control
	 * @param array        $args
	 *
	 * @return $this
	 */
	public function register_control( string $id, string $control_id, string $control, array $args = [] ) {
		if ( empty( $this->controls[ $id ] ) ) {
			$this->controls[ $id ] = [];
		}

		$args['control']         = $control;
		$this->controls[ $id ][$control_id] = $args;

		$control::init( $id, $control_id );

		return $this;
	}

	/**
	 * @param string $id
	 *
	 * @return Field_Data_Store|null
	 */
	public function get_datastore( string $id ) {
		if ( ! isset( $this->datastores[ $id ] ) ) {
			return null;
		}

		if ( is_array( $this->datastores[ $id ] ) ) {
			$instance = $this->get_field_datastore_instance( $id );

			if ( $instance ) {
				$this->datastores[ $id ] = new $instance( $id, $this->datastores[ $id ] ?? [] );
			}
		}

		return $this->datastores[ $id ];
	}

	/**
	 * @param string $id
	 * @param string $control_id
	 *
	 * @return Field_Control|null
	 */
	public function get_control( string $id, string $control_id ) {
		if ( isset( $this->controls[ $id ]) && isset( $this->controls[$id][$control_id] ) ) {
			$control = $this->controls[ $id ][ $control_id ];
			$type = $this->get_field_control_type( $control['type'] );

			return new $type( $id, $control_id, $control );
		}

		return null;
	}

	/**
	 * @param string $id
	 * @param        $value
	 *
	 * @return $this
	 */
	public function save( string $id, $value ) : Fields_Registry {
		if ( $field = $this->get_datastore( $id ) ) {
			$field->save( $value );
		}

		return $this;
	}

}