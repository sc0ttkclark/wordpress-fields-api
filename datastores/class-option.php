<?php

class Option extends Registrable_Field_Data_Store {

	/**
	 * @var false|mixed
	 */
	protected $show_in_rest;
	/**
	 * @var mixed|null
	 */
	protected $default;
	/**
	 * @var mixed|string
	 */
	protected $description;
	/**
	 * @var mixed|string
	 */
	protected $group;

	public function __construct( string $id, array $args ) {
		parent::__construct( $id, $args );
		$this->show_in_rest = $args['show_in_rest'] ?? false;
		$this->default      = $args['default'] ?? false;
		$this->description  = $args['description'] ?? '';
		$this->group        = $args['group'] ?? '';
	}

	public function save( $value ) {
		update_option( $this->id, $value );
	}

	public function get_value() {
		return get_option( $this->id, $this->get_default() );
	}

	public function get_default() {
		return $this->default;
	}

	public function get_show_in_rest() {
		return $this->show_in_rest;
	}

	public function get_group() {
		return $this->group;
	}

	public function get_description() {
		return $this->description;
	}

	public static function register( string $id, array $args ) {
		add_action( 'admin_init', function () use ( $id, $args ) {
			$datastore = Fields_Registry::get_instance()->get_datastore( $id );

			register_setting( $datastore->get_group(), $datastore->get_id(), [
				'type'         => $datastore->get_data_type(),
				'description'  => $datastore->get_description(),
				'show_in_rest' => $datastore->get_show_in_rest(),
				'default'      => $datastore->get_default(),
			] );
		} );
	}
}