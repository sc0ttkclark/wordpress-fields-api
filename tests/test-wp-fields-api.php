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
		$wp_fields->remove_form( true, true );
		$wp_fields->remove_section( true, true );
		$wp_fields->remove_field( true, true );
		$wp_fields->remove_control( true, true );

	}

	/**
	 * Test Fields API is setup
	 *
	 * @covers WP_Fields_API::__construct
	 */
	public function test_api() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$this->assertTrue( is_a( $wp_fields, 'WP_Fields_API' ) );

	}

	/**
	 * Test WP_Fields_API::add_form()
	 *
	 * @param string $object_type
	 * @param string $object_name
	 */
	public function test_add_form( $object_type = 'post', $object_name = null ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$wp_fields->add_form( $object_type, 'my_test_form', $object_name );

	}

	/**
	 * Test WP_Fields_API::add_form()
	 *
	 * @param string $object_type
	 * @param string $object_name
	 */
	public function test_add_form_invalid( $object_type = 'post' ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$wp_fields->add_form( $object_type, null, null, array() );

	}

	/**
	 * Test WP_Fields_API::get_forms()
	 */
	public function test_get_forms() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a form
		$this->test_add_form( 'post', 'my_custom_post_type' );

		// Get forms for object type / name
		$forms = $wp_fields->get_forms( 'post', 'my_custom_post_type' );

		$this->assertEquals( 1, count( $forms ) );

		$this->assertArrayHasKey( 'my_test_form', $forms );

		// Get a form that doesn't exist
		$forms = $wp_fields->get_forms( 'post', 'some_other_post_type' );

		$this->assertEquals( 0, count( $forms ) );

		// Get all forms for object type
		$forms = $wp_fields->get_forms( 'post', true );

		$this->assertEquals( 1, count( $forms ) );

		$form_ids = wp_list_pluck( $forms, 'id' );

		$this->assertContains( 'my_test_form', $form_ids );

		// Get all forms for all object types
		$forms = $wp_fields->get_forms();

		// Each array item is an object type with an array of object names
		$this->assertEquals( 1, count( $forms ) );

		// Array keys are object types
		$this->assertArrayHasKey( 'post', $forms );

	}

	/**
	 * Test WP_Fields_API::get_forms()
	 */
	public function test_get_forms_no_object_name() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a form
		$this->test_add_form( 'post' );

		// Get forms for object type / name
		$forms = $wp_fields->get_forms( 'post' );

		$this->assertEquals( 1, count( $forms ) );

		$this->assertArrayHasKey( 'my_test_form', $forms );

	}

	/**
	 * Test WP_Fields_API::get_form()
	 */
	public function test_get_form() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a form
		$this->test_add_form( 'post', 'my_custom_post_type' );

		// Form exists for this object type / name
		$form = $wp_fields->get_form( 'post', 'my_test_form', 'my_custom_post_type' );

		$this->assertNotEmpty( $form );

		$this->assertEquals( 'my_test_form', $form->id );

		// Form doesn't exist for this object type / name
		$form = $wp_fields->get_form( 'post', 'my_test_form1' );

		$this->assertEmpty( $form );

		// Form doesn't exist for this object type / name
		$form = $wp_fields->get_form( 'post', 'my_test_form2', 'my_custom_post_type' );

		$this->assertEmpty( $form );

	}

	/**
	 * Test WP_Fields_API::remove_form()
	 */
	public function test_remove_form() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a form
		$this->test_add_form( 'post', 'my_custom_post_type' );

		// Form exists for this object type / name
		$form = $wp_fields->get_form( 'post', 'my_test_form', 'my_custom_post_type' );

		$this->assertNotEmpty( $form );

		$this->assertEquals( 'my_test_form', $form->id );

		// Remove form
		$wp_fields->remove_form( 'post', 'my_test_form', 'my_custom_post_type' );

		// Form no longer exists for this object type / name
		$form = $wp_fields->get_form( 'post', 'my_test_form', 'my_custom_post_type' );

		$this->assertEmpty( $form );

	}

	/**
	 * Test WP_Fields_API::remove_form()
	 */
	public function test_remove_form_by_object_type() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a form
		$this->test_add_form( 'post', 'my_custom_post_type' );

		// Remove form
		$wp_fields->remove_form( 'post', null, true );

		// Form no longer exists for this object type / name
		$form = $wp_fields->get_form( 'post', 'my_test_form', 'my_custom_post_type' );

		$this->assertEmpty( $form );

	}

	/**
	 * Test WP_Fields_API::remove_form()
	 */
	public function test_remove_form_default_object() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a form
		$this->test_add_form( 'post' );

		// Remove form
		$wp_fields->remove_form( 'post', 'my_test_form' );

		// Form no longer exists for this object type / name
		$form = $wp_fields->get_form( 'post', 'my_test_form' );

		$this->assertEmpty( $form );

	}

	/**
	 * Test WP_Fields_API::remove_form()
	 */
	public function test_remove_form_by_object_name() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a form
		$this->test_add_form( 'post', 'my_custom_post_type' );

		// Remove form
		$wp_fields->remove_form( 'post', true, 'my_custom_post_type' );

		// Form no longer exists for this object type / name
		$form = $wp_fields->get_form( 'post', 'my_test_form', 'my_custom_post_type' );

		$this->assertEmpty( $form );

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

		// Add a form
		$this->test_add_form( $object_type, $object_name );

		$wp_fields->add_section( $object_type, 'my_test_section', $object_name, array(
			'form' => 'my_test_form',
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

		// Add a form
		$this->test_add_section( 'post', 'my_custom_post_type' );

		// Get sections for object type / name
		$sections = $wp_fields->get_sections( 'post', 'my_custom_post_type' );

		$this->assertEquals( 1, count( $sections ) );

		$this->assertArrayHasKey( 'my_test_section', $sections );

		// Get a section that doesn't exist
		$sections = $wp_fields->get_sections( 'post', 'some_other_post_type' );

		$this->assertEquals( 0, count( $sections ) );

		// Get sections by form
		$sections = $wp_fields->get_sections( 'post', 'my_custom_post_type', 'my_test_form' );

		$this->assertEquals( 1, count( $sections ) );

		$this->assertArrayHasKey( 'my_test_section', $sections );

		$this->assertEquals( 'my_test_form', $sections['my_test_section']->get_parent()->id );

		// Get sections *from* form
		$form = $wp_fields->get_form( 'post', 'my_test_form', 'my_custom_post_type' );

		$sections = $form->get_children( 'section' );

		$this->assertEquals( 1, count( $sections ) );

		$this->assertArrayHasKey( 'my_test_section', $sections );

		$this->assertEquals( 'my_test_form', $sections['my_test_section']->get_parent()->id );

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

		// Add a form
		$this->test_add_section( 'post', 'my_custom_post_type' );

		// Section exists for this object type / name
		$section = $wp_fields->get_section( 'post', 'my_test_section', 'my_custom_post_type' );

		$this->assertNotEmpty( $section );

		$this->assertEquals( 'my_test_section', $section->id );
		$this->assertEquals( 'my_test_form', $section->get_parent()->id );

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

		// Add a form
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
				'id'      => 'my_test_field_control',
				'label'   => 'My Test Field',
				'type'    => 'text',
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
			'field'   => 'my_test_field',
			'label'   => 'My Test Control Field',
			'type'    => 'text',
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

		$this->assertEquals( 'my_test_section', $controls['my_test_control']->get_parent()->id );

		// Get a control that doesn't exist
		$controls = $wp_fields->get_controls( 'post', 'some_other_post_type' );

		$this->assertEquals( 0, count( $controls ) );

		// Get controls by section
		$controls = $wp_fields->get_controls( 'post', 'my_custom_post_type', 'my_test_section' );

		$this->assertEquals( 2, count( $controls ) );

		$this->assertArrayHasKey( 'my_test_control', $controls );
		$this->assertArrayHasKey( 'my_test_field_control', $controls );

		$this->assertEquals( 'my_test_section', $controls['my_test_control']->get_parent()->id );

		// Get sections *from* form
		$section = $wp_fields->get_section( 'post', 'my_test_section', 'my_custom_post_type' );

		$controls = $section->get_children( 'control' );

		$this->assertEquals( 2, count( $controls ) );

		$this->assertArrayHasKey( 'my_test_control', $controls );
		$this->assertArrayHasKey( 'my_test_field_control', $controls );

		$this->assertEquals( 'my_test_section', $controls['my_test_control']->get_parent()->id );

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
		$this->assertNotEmpty( $control->get_field() );
		$this->assertEquals( 'my_test_field', $control->get_field()->id );
		$this->assertEquals( 'my_test_section', $control->get_parent()->id );

		// Control exists for this object type / name
		$control = $wp_fields->get_control( 'post', 'my_test_control', 'my_custom_post_type' );

		$this->assertNotEmpty( $control );

		$this->assertEquals( 'my_test_control', $control->id );
		$this->assertNotEmpty( $control->get_field() );
		$this->assertEquals( 'my_test_field', $control->get_field()->id );
		$this->assertEquals( 'my_test_section', $control->get_parent()->id );

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
		$this->assertEquals( 'my_test_field', $control->get_field()->id );
		$this->assertEquals( 'my_test_section', $control->get_parent()->id );

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