<?php
/**
 * This is an implementation for Fields API for the General Settings form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_General
 */
class WP_Fields_API_Form_Settings_General extends WP_Fields_API_Table_Form {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		$wp_fields->add_section( $this->object_type, $this->id . '-options-general', null, array(
				'title'  => __( 'General Settings' ),
				'form' => $this->id,
				'display_title' => false,
		) );

		/**
		 * Site Name
		 */
		// @todo Caps Check
		$field_args = array(
				'control' => array(
						'type'              => 'text',
						'section'           => $this->id . '-options-general',
						'label'             => __( 'Site Title' ),
						'input_attrs'       => array(
							'class'         => 'regular-text',
							'id'            => 'blogname',
							'placeholder'   => __( 'Local WordPress Dev' ),
							'name'          => 'blogname',
						),
						'internal'          => true,
				),
		);
		$wp_fields->add_field( $this->object_type, 'blogname', null, $field_args );

		/**
		 * Tagline
		 */
		// @todo Caps Check
		$field_args = array(
				'control' => array(
						'type'              => 'text',
						'section'           => $this->id . '-options-general',
						'label'             => __( 'Tagline' ),
						'description'       => __( 'In a few words, explain what this site is about.' ),
						'input_attrs'       => array(
							'class'             => 'regular-text',
							'id'                => 'blogdescription',
							'placeholder'       => __( 'Just another WordPress site.' ),
							'name'              => 'blogdescription',
						),
						'internal'    => true,
				),
		);
		$wp_fields->add_field( $this->object_type, 'blogdescription', null, $field_args );

		/**
		 * WordPress URL
		 */
		// @todo Caps Check
		$field_args = array(
				'control' => array(
						'type'              => 'text',
						'section'           => $this->id . '-options-general',
						'label'             => __( 'WordPress Address (URL)' ),
						'input_attrs'       => array(
								'class'         => 'regular-text code',
								'id'            => 'siteurl',
								'name'          => 'siteurl',
						),
						'internal'    => true,
				),
		);
		$wp_fields->add_field( $this->object_type, 'siteurl', null, $field_args );

		/**
		 * Home URL
		 */
		// @todo Caps Check
		$field_args = array(
				'control' => array(
						'type'              => 'text',
						'section'           => $this->id . '-options-general',
						'label'             => __( 'Site Address (URL)' ),
						'input_attrs'       => array(
								'class'         => 'regular-text code',
								'id'            => 'home',
								'name'          => 'home',
						),
						'description'       => sprintf( __( 'Enter the address here if you <a href="%s">want your site home page to be different from your WordPress installation directory</a>.' ), 'https://codex.wordpress.org/Giving_WordPress_Its_Own_Directory' ),
						'internal'    => true,
				),
		);
		$wp_fields->add_field( $this->object_type, 'home', null, $field_args );

		/**
		 * Admin Email Address
		 */
		// @todo Caps Check
		$field_args = array(
				'control' => array(
						'type'              => 'text',
						'section'           => $this->id . '-options-general',
						'label'             => __( 'Email Address' ),
						'input_attrs'       => array(
								'class'         => 'regular-text ltr',
								'id'            => 'admin_email',
								'name'          => 'admin_email',
						),
						'description'       => __( 'This address is used for admin purposes, like new user notification.' ),
						'internal'    => true,
				),
		);
		$wp_fields->add_field( $this->object_type, 'admin_email', null, $field_args );

		/**
		 * Open Registration
		 */
		// @todo Caps Check
		$field_args = array(
				'control' => array(
						'type'              => 'checkbox',
						'section'           => $this->id . '-options-general',
						'label'             => __( 'Membership' ),
						'input_attrs'       => array(
								'id'            => 'users_can_register',
								'name'          => 'users_can_register',
						),
						'description'       => __( 'Anyone can register' ),
						'internal'    => true,
				),
		);
		$wp_fields->add_field( $this->object_type, 'users_can_register', null, $field_args );


		/**
		 * Default Role
		 */
		// @todo Caps Check
		$field_args = array(
				'control' => array(
					'type'             => 'user-role',
					'section'          => $this->id . '-options-general',
					'label'            => __( 'New User Default Role' ),
					'input_attrs'      => array(
						'id'    => 'default_role',
						'name'  => 'default_role',
					),
					'internal'          => true,
				),
		);
		$wp_fields->add_field( $this->object_type, 'default_role', null, $field_args );

		/**
		 * Date Format
		 */
		// @todo Caps Check
		// @todo custom control type because of nested fields
		$current_time = time();
		$field_args = array(
				'control' => array(
					'type'              => 'radio',
					'section'           => $this->id . '-options-general',
					'label'             => __( 'Date Format' ),
						'input_attrs'   => array(
							'id'    => 'date_format',
							'name'  => 'date_format',
						),
					'internal'          => true,
					'choices'           => array(
						date( 'F j, Y', $current_time ),
						date( 'Y-m-d', $current_time ),
						date( 'm/d/y', $current_time ),
						date( 'd/m/y', $current_time ),
					)
				),
		);
		$wp_fields->add_field( $this->object_type, 'date_format', null, $field_args );

		/**
		 * Time Format
		 */
		// @todo Caps Check
		// @todo custom control type because of nested fields
		$current_time = time();
		$field_args = array(
				'control' => array(
						'type'              => 'radio',
						'section'           => $this->id . '-options-general',
						'label'             => __( 'Time Format' ),
						'description'       => sprintf( '<a href="%s">' . __( 'Documentation on date and time formatting.') . '<a/>', 'https://codex.wordpress.org/Formatting_Date_and_Time' ),
						'input_attrs'   => array(
								'id'    => 'time_format',
								'name'  => 'time_format',
						),
						'internal'          => true,
						'choices'           => array(
								date( 'g:i a', $current_time ),
								date( 'g:i A', $current_time ),
								date( 'H:i', $current_time ),
						)
				),
		);
		$wp_fields->add_field( $this->object_type, 'time_format', null, $field_args );

		/**
		 * Week Starts On
		 */
		// @todo Caps Check
		$field_args = array(
				'control' => array(
						'type'              => 'select',
						'section'           => $this->id . '-options-general',
						'label'             => __( 'Week Starts On' ),
						'internal'          => true,
						'input_attrs'   => array(
								'id'    => 'start_of_week',
								'name'  => 'start_of_week',
						),
						'choices'           => array(
							__( 'Sunday' ),
							__( 'Monday' ),
							__( 'Tuesday' ),
							__( 'Wednesday' ),
							__( 'Thursday' ),
							__( 'Friday' ),
							__( 'Saturday' ),
						)
				),
		);
		$wp_fields->add_field( $this->object_type, 'start_of_week', null, $field_args );

		// @todo implent languages dropdown
	}

}