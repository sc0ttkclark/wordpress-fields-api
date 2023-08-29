<?php

abstract class Field_Data_Store {
	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var mixed|string
	 */
	protected $name;
	/**
	 * @var mixed|null
	 */
	protected $type;
	/**
	 * @var mixed|null
	 */
	protected $data_type;

	public function __construct( string $id, array $args ) {
		$this->id        = $id;
		$this->name      = $args['name'] ?? '';
		$this->data_type = $args['data_type'] ?? null;
		$this->type      = $args['type'] ?? null;
	}

	public function get_name() : string {
		return $this->name;
	}

	public function get_data_type() : string {
		return $this->data_type;
	}

	public function get_type() : string {
		return $this->type;
	}

	public function get_id() : string {
		return $this->id;
	}

	abstract public function save( $value );

	abstract public function get_value();
}