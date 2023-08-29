<?php

abstract class Field_Control {
	/**
	 * @var string
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $control_id;

	public function __construct( string $id, string $control_id, array $args = [] ) {
		$this->id = $id;
		$this->control_id = $control_id;
	}

	public function get_control_id() : string {
		return $this->control_id;
	}

	public function get_id() : string {
		return $this->id;
	}

	protected function get_datastore() : Field_Data_Store {
		return Fields_Registry::get_instance()->get_datastore( $this->id );
	}

	abstract public function get_content() : string;

	public static function init( string $id, string $control_id ) {
	}
}