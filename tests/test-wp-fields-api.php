<?php
/**
 * Class WP_Test_Fields_API_Testcase
 *
 * @uses PHPUnit_Framework_TestCase
 */
class WP_Test_Fields_API_Testcase extends WP_UnitTestCase {

	public function tearDown() {

		// Do main teardown
		parent::tearDown();

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Reset WP Fields instance for testing purposes
		$wp_fields->remove_screen( true, true );
		$wp_fields->remove_section( true, true );
		$wp_fields->remove_field( true, true );
		$wp_fields->remove_control( true, true );

	}

	/**
	 * Test WP_Fields_API::get_containers()
	 */
	public function test_get_containers() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a section / screen
		$this->test_add_section( 'post', 'my_custom_post_type' );

		// Get containers for object type / name
		$containers = $wp_fields->get_containers( 'post', 'my_custom_post_type' );

		// There are two containers, the screen and the section
		$this->assertEquals( 2, count( $containers ) );

		$this->assertArrayHasKey( 'my_test_screen', $containers );
		$this->assertArrayHasKey( 'my_test_section', $containers );

		// Get a containers that doesn't exist
		$containers = $wp_fields->get_containers( 'post' );

		$this->assertEquals( 0, count( $containers ) );

		// Get all containers for object type
		$containers = $wp_fields->get_containers( 'post', true );

		$this->assertEquals( 2, count( $containers ) );

		$container_ids = wp_list_pluck( $containers, 'id' );

		$this->assertContains( 'my_test_screen', $container_ids );
		$this->assertContains( 'my_test_section', $container_ids );

		// Get all containers for all object types
		$containers = $wp_fields->get_containers();

		// Each array item is an object type with an array of object names
		$this->assertEquals( 1, count( $containers ) );

		$this->assertArrayHasKey( 'post', $containers );

	}

	/**
	 * Test WP_Fields_API::add_screen()
	 *
	 * @param string $object_type
	 * @param string $object_name
	 */
	public function test_add_screen( $object_type = 'post', $object_name = null ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$wp_fields->add_screen( $object_type, 'my_test_screen', $object_name );

	}

	/**
	 * Test WP_Fields_API::get_screens()
	 */
	public function test_get_screens() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a screen
		$this->test_add_screen( 'post', 'my_custom_post_type' );

		// Get screens for object type / name
		$screens = $wp_fields->get_screens( 'post', 'my_custom_post_type' );

		$this->assertEquals( 1, count( $screens ) );

		$this->assertArrayHasKey( 'my_test_screen', $screens );

		// Get a screen that doesn't exist
		$screens = $wp_fields->get_screens( 'post', 'some_other_post_type' );

		$this->assertEquals( 0, count( $screens ) );

		// Get all screens for object type
		$screens = $wp_fields->get_screens( 'post', true );

		$this->assertEquals( 1, count( $screens ) );

		$screen_ids = wp_list_pluck( $screens, 'id' );

		$this->assertContains( 'my_test_screen', $screen_ids );

		// Get all screens for all object types
		$screens = $wp_fields->get_screens();

		// Each array item is an object type with an array of object names
		$this->assertEquals( 1, count( $screens ) );

		// Array keys are object types
		$this->assertArrayHasKey( 'post', $screens );

	}

	/**
	 * Test WP_Fields_API::get_screen()
	 */
	public function test_get_screen() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a screen
		$this->test_add_screen( 'post', 'my_custom_post_type' );

		// Screen exists for this object type / name
		$screen = $wp_fields->get_screen( 'post', 'my_test_screen', 'my_custom_post_type' );

		$this->assertNotEmpty( $screen );

		$this->assertEquals( 'my_test_screen', $screen->id );

		// Screen doesn't exist for this object type / name
		$screen = $wp_fields->get_screen( 'post', 'my_test_screen1' );

		$this->assertEmpty( $screen );

		// Screen doesn't exist for this object type / name
		$screen = $wp_fields->get_screen( 'post', 'my_test_screen2', 'my_custom_post_type' );

		$this->assertEmpty( $screen );

	}

	/**
	 * Test WP_Fields_API::remove_screen()
	 */
	public function test_remove_screen() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a screen
		$this->test_add_screen( 'post', 'my_custom_post_type' );

		// Screen exists for this object type / name
		$screen = $wp_fields->get_screen( 'post', 'my_test_screen', 'my_custom_post_type' );

		$this->assertNotEmpty( $screen );

		$this->assertEquals( 'my_test_screen', $screen->id );

		// Remove screen
		$wp_fields->remove_screen( 'post', 'my_test_screen', 'my_custom_post_type' );

		// Screen no longer exists for this object type / name
		$screen = $wp_fields->get_screen( 'post', 'my_test_screen', 'my_custom_post_type' );

		$this->assertEmpty( $screen );

	}

	/**
	 * Test WP_Fields_API::add_section()
	 *
	 * @param string $object_type
	 * @param string $object_name
	 */
	public function test_add_section( $object_type = 'post', $object_name = null ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a screen
		$this->test_add_screen( $object_type, $object_name );

		$wp_fields->add_section( $object_type, 'my_test_section', $object_name, array(
			'screen' => 'my_test_screen',
		) );

	}

	/**
	 * Test WP_Fields_API::get_sections()
	 */
	public function test_get_sections() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a screen
		$this->test_add_section( 'post', 'my_custom_post_type' );

		// Get sections for object type / name
		$sections = $wp_fields->get_sections( 'post', 'my_custom_post_type' );

		$this->assertEquals( 1, count( $sections ) );

		$this->assertArrayHasKey( 'my_test_section', $sections );

		// Get a section that doesn't exist
		$sections = $wp_fields->get_sections( 'post', 'some_other_post_type' );

		$this->assertEquals( 0, count( $sections ) );

		// Get sections by screen
		$sections = $wp_fields->get_sections( 'post', 'my_custom_post_type', 'my_test_screen' );

		$this->assertEquals( 1, count( $sections ) );

		$this->assertArrayHasKey( 'my_test_section', $sections );

		// Get all sections for object type
		$sections = $wp_fields->get_sections( 'post', true );

		$this->assertEquals( 1, count( $sections ) );

		$section_ids = wp_list_pluck( $sections, 'id' );

		$this->assertContains( 'my_test_section', $section_ids );

		// Get all sections for all object types
		$sections = $wp_fields->get_sections();

		// Each array item is an object type with an array of object names
		$this->assertEquals( 1, count( $sections ) );

		$this->assertArrayHasKey( 'post', $sections );

	}

	/**
	 * Test WP_Fields_API::get_section()
	 */
	public function test_get_section() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a screen
		$this->test_add_section( 'post', 'my_custom_post_type' );

		// Section exists for this object type / name
		$section = $wp_fields->get_section( 'post', 'my_test_section', 'my_custom_post_type' );

		$this->assertNotEmpty( $section );

		$this->assertEquals( 'my_test_section', $section->id );

		// Section doesn't exist for this object type / name
		$section = $wp_fields->get_section( 'post', 'my_test_section', 'some_other_post_type' );

		$this->assertEmpty( $section );

		// Section doesn't exist for this object type / name
		$section = $wp_fields->get_section( 'post', 'my_test_section2', 'my_custom_post_type' );

		$this->assertEmpty( $section );

	}

	/**
	 * Test WP_Fields_API::remove_section()
	 */
	public function test_remove_section() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a screen
		$this->test_add_section( 'post', 'my_custom_post_type' );

		// Section exists for this object type / name
		$section = $wp_fields->get_section( 'post', 'my_test_section', 'my_custom_post_type' );

		$this->assertNotEmpty( $section );

		$this->assertEquals( 'my_test_section', $section->id );

		// Remove section
		$wp_fields->remove_section( 'post', 'my_test_section', 'my_custom_post_type' );

		// Section no longer exists for this object type / name
		$section = $wp_fields->get_section( 'post', 'my_test_section', 'my_custom_post_type' );

		$this->assertEmpty( $section );

	}

	/**
	 * Test WP_Fields_API::add_field()
	 *
	 * @param string $object_type
	 * @param string $object_name
	 */
	public function test_add_field( $object_type = 'post', $object_name = null ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a section for the control
		$this->test_add_section( $object_type, $object_name );

		$wp_fields->add_field( $object_type, 'my_test_field', $object_name, array(
			'control' => array(
				'id' => 'my_test_field_control',
				'label' => 'My Test Field',
				'type' => 'text',
				'section' => 'my_test_section',
			),
		) );

	}

	/**
	 * Test WP_Fields_API::get_fields()
	 */
	public function test_get_fields() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a field
		$this->test_add_field( 'post', 'my_custom_post_type' );

		// Get fields for object type / name
		$fields = $wp_fields->get_fields( 'post', 'my_custom_post_type' );

		$this->assertEquals( 1, count( $fields ) );

		$this->assertArrayHasKey( 'my_test_field', $fields );

		// Get a field that doesn't exist
		$fields = $wp_fields->get_fields( 'post', 'some_other_post_type' );

		$this->assertEquals( 0, count( $fields ) );

		// Get fields by section
		$fields = $wp_fields->get_fields( 'post', 'my_custom_post_type', 'my_test_section' );

		$this->assertEquals( 1, count( $fields ) );

		$this->assertArrayHasKey( 'my_test_field', $fields );

	}

	/**
	 * Test WP_Fields_API::get_field()
	 */
	public function test_get_field() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a field
		$this->test_add_field( 'post', 'my_custom_post_type' );

		// Field exists for this object type / name
		$field = $wp_fields->get_field( 'post', 'my_test_field', 'my_custom_post_type' );

		$this->assertNotEmpty( $field );

		$this->assertEquals( 'my_test_field', $field->id );

		// Field doesn't exist for this object type / name
		$field = $wp_fields->get_field( 'post', 'my_test_field', 'some_other_post_type' );

		$this->assertEmpty( $field );

		// Field doesn't exist for this object type / name
		$field = $wp_fields->get_field( 'post', 'my_test_field2', 'my_custom_post_type' );

		$this->assertEmpty( $field );

	}

	/**
	 * Test WP_Fields_API::remove_field()
	 */
	public function test_remove_field() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a field
		$this->test_add_field( 'post', 'my_custom_post_type' );

		// Field exists for this object type / name
		$field = $wp_fields->get_field( 'post', 'my_test_field', 'my_custom_post_type' );

		$this->assertNotEmpty( $field );

		$this->assertEquals( 'my_test_field', $field->id );

		// Remove field
		$wp_fields->remove_field( 'post', 'my_test_field', 'my_custom_post_type' );

		// Field no longer exists for this object type / name
		$field = $wp_fields->get_field( 'post', 'my_test_field', 'my_custom_post_type' );

		$this->assertEmpty( $field );

	}

	/**
	 * Test WP_Fields_API::add_control()
	 *
	 * @param string $object_type
	 * @param string $object_name
	 */
	public function test_add_control( $object_type = 'post', $object_name = null ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a field for the control
		$this->test_add_field( $object_type, $object_name );

		$wp_fields->add_control( $object_type, 'my_test_control', $object_name, array(
			'section' => 'my_test_section',
			'fields' => 'my_test_field',
			'label' => 'My Test Control Field',
			'type' => 'text',
		) );

	}

	/**
	 * Test WP_Fields_API::get_controls()
	 */
	public function test_get_controls() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a control / field / section
		$this->test_add_control( 'post', 'my_custom_post_type' );

		// Get controls for object type / name
		$controls = $wp_fields->get_controls( 'post', 'my_custom_post_type' );

		// There are two controls, the default one with the main field and this control
		$this->assertEquals( 2, count( $controls ) );

		$this->assertArrayHasKey( 'my_test_control', $controls );
		$this->assertArrayHasKey( 'my_test_field_control', $controls );

		$this->assertEquals( 'my_test_section', $controls['my_test_control']->section );

		// Get a control that doesn't exist
		$controls = $wp_fields->get_controls( 'post', 'some_other_post_type' );

		$this->assertEquals( 0, count( $controls ) );

		// Get controls by section
		$controls = $wp_fields->get_controls( 'post', 'my_custom_post_type', 'my_test_section' );

		$this->assertEquals( 2, count( $controls ) );

		$this->assertArrayHasKey( 'my_test_control', $controls );
		$this->assertArrayHasKey( 'my_test_field_control', $controls );

		$this->assertEquals( 'my_test_section', $controls['my_test_control']->section );

	}

	/**
	 * Test WP_Fields_API::get_control()
	 */
	public function test_get_control() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a control / field / section
		$this->test_add_control( 'post', 'my_custom_post_type' );

		// Control exists for this object type / name
		$control = $wp_fields->get_control( 'post', 'my_test_field_control', 'my_custom_post_type' );

		$this->assertNotEmpty( $control );

		$this->assertEquals( 'my_test_field_control', $control->id );
		$this->assertNotEmpty( $control->field );
		$this->assertEquals( 'my_test_field', $control->field->id );
		$this->assertEquals( 'my_test_section', $control->section );

		// Control exists for this object type / name
		$control = $wp_fields->get_control( 'post', 'my_test_control', 'my_custom_post_type' );

		$this->assertNotEmpty( $control );

		$this->assertEquals( 'my_test_control', $control->id );
		$this->assertNotEmpty( $control->field );
		$this->assertEquals( 'my_test_field', $control->field->id );
		$this->assertEquals( 'my_test_section', $control->section );

		// Control doesn't exist for this object type / name
		$control = $wp_fields->get_control( 'post', 'my_test_control', 'some_other_post_type' );

		$this->assertEmpty( $control );

		// Control doesn't exist for this object type / name
		$control = $wp_fields->get_control( 'post', 'my_test_control2', 'my_custom_post_type' );

		$this->assertEmpty( $control );

	}

	/**
	 * Test WP_Fields_API::remove_control()
	 */
	public function test_remove_control() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a control / field / section
		$this->test_add_control( 'post', 'my_custom_post_type' );

		// Control exists for this object type / name
		$control = $wp_fields->get_control( 'post', 'my_test_control', 'my_custom_post_type' );

		$this->assertNotEmpty( $control );

		$this->assertEquals( 'my_test_control', $control->id );
		$this->assertEquals( 'my_test_field', $control->field->id );
		$this->assertEquals( 'my_test_section', $control->section );

		// Remove control
		$wp_fields->remove_control( 'post', 'my_test_control', 'my_custom_post_type' );

		// Control no longer exists for this object type / name
		$control = $wp_fields->get_control( 'post', 'my_test_control', 'my_custom_post_type' );

		$this->assertEmpty( $control );

		// Remove field's control
		$wp_fields->remove_control( 'post', 'my_test_field_control', 'my_custom_post_type' );

		// Control no longer exists for this object type / name
		$control = $wp_fields->get_control( 'post', 'my_test_field_control', 'my_custom_post_type' );

		$this->assertEmpty( $control );

	}

}