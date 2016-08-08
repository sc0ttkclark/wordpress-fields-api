<?php

/**
 * Class WP_Test_Fields_API_Testcase
 *
 * @uses PHPUnit_Framework_TestCase
 */
class WP_Test_Fields_API_Testcase extends WP_UnitTestCase {

	public $object_type = 'post';
	public $object_subtype = 'my_custom_post_type';

	public function setUp() {

		// Do main teardown
		parent::tearDown();

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Reset WP Fields instance for testing purposes
		$wp_fields->remove_forms();

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
	 * @param string $object_subtype
	 */
	public function test_add_form_invalid( $object_type = 'post' ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		return $wp_fields->add_form( $object_type, null, array() );
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
		$wp_fields->add_form( $this->object_type, 'my_test_form' );

		// Form exists
		$form = $wp_fields->get_form( 'my_test_form' );

		$this->assertNotEmpty( $form );

		$this->assertEquals( 'my_test_form', $form->id );

		// Form doesn't exist
		$form = $wp_fields->get_form( 'my_test_form1' );

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
		$wp_fields->add_form( $this->object_type, 'my_test_form' );

		$form = $wp_fields->get_form( 'my_test_form' );

		$this->assertNotEmpty( $form );

		$this->assertEquals( 'my_test_form', $form->id );

		// Remove form
		$wp_fields->remove_form( 'my_test_form' );

		$form = $wp_fields->get_form( 'my_test_form' );

		$this->assertEmpty( $form );

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
		$form = $wp_fields->add_form( $this->object_type, 'my_test_form' );

		$wp_fields->add_section( 'my_test_section', array(
			'form' => $form,
		) );

		// Section exists
		$section = $wp_fields->get_section( 'my_test_section' );

		$this->assertNotEmpty( $section );

		$this->assertEquals( 'my_test_section', $section->id );
		$this->assertEquals( 'my_test_form', $section->parent->id );

		// Section doesn't exist
		$section = $wp_fields->get_section( 'my_test_section1'  );

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
		$form = $wp_fields->add_form( $this->object_type, 'my_test_form' );

		$wp_fields->add_section( 'my_test_section', $this->object_subtype, array(
			'form' => $form,
		) );

		// Section exists
		$section = $wp_fields->get_section( 'my_test_section' );

		$this->assertNotEmpty( $section );

		$this->assertEquals( 'my_test_section', $section->id );

		// Remove section
		$wp_fields->remove_section( 'my_test_section' );

		// Section no longer exists
		$section = $wp_fields->get_section( 'my_test_section' );

		$this->assertEmpty( $section );

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
		$form = $wp_fields->add_form( $this->object_type, 'my_test_form' );

		$section = $wp_fields->add_section( 'my_test_section', array(
			'form' => $form,
		) );

		$wp_fields->add_field( 'my_test_field', array(
			'control' => array(
				'id'      => 'my_test_field_control',
				'label'   => 'My Test Field',
				'type'    => 'text',
				'section' => $section,
			),
		) );

		// Field exists
		$field = $wp_fields->get_field( 'my_test_field' );

		$this->assertNotEmpty( $field );
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
		$form = $wp_fields->add_form( $this->object_type, 'my_test_form' );

		$section = $wp_fields->add_section( 'my_test_section', array(
			'form' => $form,
		) );

		$wp_fields->add_field( 'my_test_field', array(
			'control' => array(
				'id'      => 'my_test_field_control',
				'label'   => 'My Test Field',
				'type'    => 'text',
				'section' => $section,
			),
		) );

		// Field exists for this object type / name
		$field = $wp_fields->get_field( 'my_test_field' );

		$this->assertNotEmpty( $field );

		$this->assertEquals( 'my_test_field', $field->id );

		// Remove field
		$wp_fields->remove_field( 'my_test_field' );

		// Field no longer exists
		$field = $wp_fields->get_field( 'my_test_field' );

		$this->assertEmpty( $field );

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
		$form = $wp_fields->add_form( $this->object_type, 'my_test_form' );

		$section = $wp_fields->add_section( 'my_test_section', array(
			'form' => $form,
		) );

		$field = $wp_fields->add_field( 'my_test_field', array(
			'control' => array(
				'id'      => 'my_test_field_control',
				'label'   => 'My Test Field',
				'type'    => 'text',
				'section' => $section,
			),
		) );

		$wp_fields->add_control( 'my_test_control', array(
			'section' => 'my_test_section',
			'field'   => $field,
			'label'   => 'My Test Control Field',
			'type'    => 'text',
		) );

		// Control exists
		$control = $wp_fields->get_control( 'my_test_field_control' );

		$this->assertNotEmpty( $control );

		$this->assertEquals( 'my_test_field_control', $control->id );
		$this->assertNotEmpty( $control->get_field() );
		$this->assertEquals( 'my_test_field', $control->get_field()->id );
		$this->assertEquals( 'my_test_section', $control->parent->id );

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
		$form = $wp_fields->add_form( $this->object_type, 'my_test_form' );

		$section = $wp_fields->add_section( 'my_test_section', array(
			'form' => $form,
		) );

		$field = $wp_fields->add_field( 'my_test_field', array(
			'control' => array(
				'id'      => 'my_test_field_control',
				'label'   => 'My Test Field',
				'type'    => 'text',
				'section' => $section,
			),
		) );

		$wp_fields->add_control( 'my_test_control', array(
			'section' => 'my_test_section',
			'field'   => $field,
			'label'   => 'My Test Control Field',
			'type'    => 'text',
		) );

		// Control exists
		$control = $wp_fields->get_control( 'my_test_control' );

		$this->assertNotEmpty( $control );

		$this->assertEquals( 'my_test_control', $control->id );
		$this->assertEquals( 'my_test_field', $control->get_field()->id );
		$this->assertEquals( 'my_test_section', $control->parent->id );

		// Remove control
		$wp_fields->remove_control( 'my_test_control' );

		// Control no longer exists
		$control = $wp_fields->get_control( 'my_test_control' );

		$this->assertEmpty( $control );
	}

}