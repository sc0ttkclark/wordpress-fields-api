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
		$wp_fields->add_form( $this->object_type, 'my_test_form' );

		// Form exists
		$form = $wp_fields->get_form( 'my_test_form' );

		$form->add_section( 'my_test_section' );

		// Section exists
		$section = $form->get_section( 'my_test_section' );

		$this->assertNotEmpty( $section );

		$this->assertEquals( 'my_test_section', $section->id );
		$this->assertEquals( 'my_test_form', $section->parent->id );

		// Section doesn't exist
		$section = $form->get_section( 'my_test_section1'  );

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
		$wp_fields->add_form( $this->object_type, 'my_test_form' );

		// Form exists
		$form = $wp_fields->get_form( 'my_test_form' );

		$form->add_section( 'my_test_section', array(
			'object_subtype' => $this->object_subtype,
		) );

		// Section exists
		$section = $form->get_section( 'my_test_section' );

		$this->assertNotEmpty( $section );

		$this->assertEquals( 'my_test_section', $section->id );

		// Remove section
		$form->remove_section( 'my_test_section' );

		// Section no longer exists
		$section = $form->get_section( 'my_test_section' );

		$this->assertEmpty( $section );

	}

	/**
	 * Test WP_Fields_API::get_control()
	 */
	public function test_get_control() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Add a field
		$wp_fields->add_form( $this->object_type, 'my_test_form' );

		// Form exists
		$form = $wp_fields->get_form( 'my_test_form' );

		$section = $form->add_section( 'my_test_section' );

		$section->add_control( 'my_test_control', array(
			'label' => 'My Test Control Field',
			'type'  => 'text',
		) );

		// Control exists
		$control = $section->get_control( 'my_test_control' );

		$this->assertNotEmpty( $control );

		$this->assertEquals( 'my_test_control', $control->id );
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

		// Add a field
		$wp_fields->add_form( $this->object_type, 'my_test_form' );

		// Form exists
		$form = $wp_fields->get_form( 'my_test_form' );

		$section = $form->add_section( 'my_test_section' );

		$section->add_control( 'my_test_control', array(
			'label' => 'My Test Control Field',
			'type'  => 'text',
		) );

		// Remove control
		$section->remove_control( 'my_test_control' );

		// Control no longer exists
		$control = $section->get_control( 'my_test_control' );

		$this->assertEmpty( $control );
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
		$wp_fields->add_form( $this->object_type, 'my_test_form' );

		// Form exists
		$form = $wp_fields->get_form( 'my_test_form' );

		$section = $form->add_section( 'my_test_section' );

		$section->add_control( 'my_test_control', array(
			'field' => array(
				'id' => 'my_test_control_field',
			),
			'label' => 'My Test Control Field',
			'type'  => 'text',
		) );

		$wp_fields->add_field( $this->object_type, 'my_test_field' );

		// Control exists
		$control = $section->get_control( 'my_test_control' );

		// Control Field exists
		$control_field = $control->get_field();

		$this->assertNotEmpty( $control_field );

		// Field exists
		$field = $wp_fields->get_field( $this->object_type, 'my_test_field' );

		$this->assertNotEmpty( $field );

		$this->assertEquals( 'my_test_control_field', $control->id );
		$this->assertEquals( 'my_test_control', $control->parent->id );
		$this->assertEquals( 'my_test_section', $control->parent->parent->id );
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
		$wp_fields->add_form( $this->object_type, 'my_test_form' );

		// Form exists
		$form = $wp_fields->get_form( 'my_test_form' );

		$section = $form->add_section( 'my_test_section' );

		$section->add_control( 'my_test_control', array(
			'field' => array(
				'id' => 'my_test_control_field',
			),
			'label' => 'My Test Control Field',
			'type'  => 'text',
		) );

		$wp_fields->add_field( $this->object_type, 'my_test_field' );

		// Control exists
		$control = $section->get_control( 'my_test_control' );

		// Remove Control Field
		$control->remove_field();

		// Remove Field
		$wp_fields->remove_field( $this->object_type, 'my_test_field' );

		// Control Field no longer exists
		$control_field = $control->get_field();

		$this->assertEmpty( $control_field );

		// Field no longer exists
		$field = $wp_fields->get_field( $this->object_type, 'my_test_field' );

		$this->assertEmpty( $field );

	}

}