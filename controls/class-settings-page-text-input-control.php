<?php

class Settings_Page_Text_Input_Control extends Field_Control {

	/**
	 * @var mixed|string
	 */
	protected $section;
	/**
	 * @var string
	 */
	protected $description;
	/**
	 * @var string
	 */
	protected $page;

	public function __construct( string $id, string $control_id, array $args = [] ) {
		parent::__construct( $id, $control_id, $args );
		$this->section     = (string) $args['section'] ? : '';
	}

	public function get_content() : string {
		return "<input type='text' name='" . esc_attr( $this->get_id() ) . "' id='" . esc_attr( $this->get_id() ) . "' value='" . esc_attr( $this->get_datastore()->get_value() ) . "'>";
	}

	public function render() {
		echo $this->get_content();
	}

	public function get_section() : string {
		return $this->section;
	}

	public static function init( string $id, string $control_id ) {
		add_action( 'admin_init', function () use ( $id, $control_id ) {
			$control   = Fields_Registry::get_instance()->get_control( $id, $control_id );
			$datastore = $control->get_datastore();

			if ( $control instanceof static && $datastore instanceof Option ) {
				add_settings_field(
					$control->get_id(),
					$datastore->get_description(),
					[ $control, 'render' ],
					$datastore->get_group(),
					$control->get_section()
				);
			}
		} );
	}
}