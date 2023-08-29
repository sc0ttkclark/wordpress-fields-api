<?php

abstract class Registrable_Field_Data_Store extends Field_Data_Store {
	/**
	 * @param string $id
	 * @param array  $args
	 *
	 * @return void
	 */
	abstract public static function register(string $id, array $args);
}