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
class WP_Fields_API_Form_Settings_General extends WP_Fields_API_Form_Settings {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		$wp_fields->add_section( $this->object_type, $this->id . '-options-general', null, array(
			'label'         => __( 'General Settings' ),
			'form'          => $this->id,
			'display_label' => false,
		) );

		/**
		 * Site Name
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'text',
				'section'     => $this->id . '-options-general',
				'label'       => __( 'Site Title' ),
				'input_attrs' => array(
					'class' => 'regular-text',
					'id'    => 'blogname',
					'name'  => 'blogname',
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'blogname', null, $field_args );

		/**
		 * Tagline
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'text',
				'section'     => $this->id . '-options-general',
				'label'       => __( 'Tagline' ),
				'description' => __( 'In a few words, explain what this site is about.' ),
				'input_attrs' => array(
					'class' => 'regular-text',
					'id'    => 'blogdescription',
					'name'  => 'blogdescription',
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'blogdescription', null, $field_args );

		/**
		 * WordPress URL
		 */
		$field_args = array(
			'control' => array(
				'type'                  => 'text',
				'section'               => $this->id . '-options-general',
				'label'                 => __( 'WordPress Address (URL)' ),
				'input_attrs'           => array(
					'class' => 'regular-text code',
					'id'    => 'siteurl',
					'name'  => 'siteurl',
				),
				'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
				'internal'              => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'siteurl', null, $field_args );

		/**
		 * Home URL
		 */
		$field_args = array(
			'control' => array(
				'type'                  => 'text',
				'section'               => $this->id . '-options-general',
				'label'                 => __( 'Site Address (URL)' ),
				'input_attrs'           => array(
					'class' => 'regular-text code',
					'id'    => 'home',
					'name'  => 'home',
				),
				'description'           => sprintf( __( 'Enter the address here if you <a href="%s">want your site home page to be different from your WordPress installation directory</a>.' ), 'https://codex.wordpress.org/Giving_WordPress_Its_Own_Directory' ),
				'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
				'internal'              => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'home', null, $field_args );

		/**
		 * Admin Email Address
		 */
		$field_args = array(
			'control' => array(
				'type'                  => 'text',
				'section'               => $this->id . '-options-general',
				'label'                 => __( 'Email Address' ),
				'input_attrs'           => array(
					'class' => 'regular-text ltr',
					'id'    => 'admin_email',
					'name'  => 'admin_email',
				),
				'description'           => __( 'This address is used for admin purposes, like new user notification.' ),
				'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
				'internal'              => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'admin_email', null, $field_args );

		// @todo Use new_admin_email if is_multisite()

		/**
		 * Open Registration
		 */
		$field_args = array(
			'control' => array(
				'type'                  => 'checkbox',
				'section'               => $this->id . '-options-general',
				'label'                 => __( 'Membership' ),
				'input_attrs'           => array(
					'id'   => 'users_can_register',
					'name' => 'users_can_register',
				),
				'checkbox_label'        => __( 'Anyone can register' ),
				'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
				'internal'              => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'users_can_register', null, $field_args );

		/**
		 * Default Role
		 */
		$field_args = array(
			'control' => array(
				'type'                  => 'user-role',
				'section'               => $this->id . '-options-general',
				'label'                 => __( 'New User Default Role' ),
				'input_attrs'           => array(
					'id'   => 'default_role',
					'name' => 'default_role',
				),
				'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
				'internal'              => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'default_role', null, $field_args );

		// @todo: Custom control for Timezones

		/**
		 * Date Format
		 */
		// @todo custom control type because of nested fields
		$current_time = time();
		$field_args   = array(
			'control' => array(
				'type'        => 'radio',
				'section'     => $this->id . '-options-general',
				'label'       => __( 'Date Format' ),
				'input_attrs' => array(
					'id'   => 'date_format',
					'name' => 'date_format',
				),
				'internal'    => true,
				'choices'     => array(
					date_i18n( 'F j, Y', $current_time ),
					date_i18n( 'Y-m-d', $current_time ),
					date_i18n( 'm/d/y', $current_time ),
					date_i18n( 'd/m/y', $current_time ),
				)
			),
		);
		$wp_fields->add_field( $this->object_type, 'date_format', null, $field_args );

		/**
		 * Time Format
		 */
		// @todo custom control type because of nested fields
		$current_time = time();
		$field_args   = array(
			'control' => array(
				'type'        => 'radio',
				'section'     => $this->id . '-options-general',
				'label'       => __( 'Time Format' ),
				'description' => sprintf( '<a href="%s">' . __( 'Documentation on date and time formatting.' ) . '</a>', 'https://codex.wordpress.org/Formatting_Date_and_Time' ),
				'input_attrs' => array(
					'id'   => 'time_format',
					'name' => 'time_format',
				),
				'internal'    => true,
				'choices'     => array(
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
		$field_args = array(
			'control' => array(
				'type'        => 'select',
				'section'     => $this->id . '-options-general',
				'label'       => __( 'Week Starts On' ),
				'internal'    => true,
				'input_attrs' => array(
					'id'   => 'start_of_week',
					'name' => 'start_of_week',
				),
				'choices'     => array()
			),
		);

		/**
		 * @global WP_Locale $wp_locale
		 */
		global $wp_locale;

		// @todo Maybe move into choices callback
		for ( $day_index = 0; $day_index <= 6; $day_index++ ) {
			$field_args['control']['choices'][ (string) $day_index ] = $wp_locale->get_weekday( $day_index );
		}

		$wp_fields->add_field( $this->object_type, 'start_of_week', null, $field_args );

		// @todo implement languages dropdown

		// Add example fields (maybe)
		parent::register_fields( $wp_fields );
	}

	/**
	 * Control only visible if WordPress is not multisite.
	 *
	 * @param WP_Fields_API_Control $control Control object
	 *
	 * @return bool
	 */
	public function capability_is_not_multisite( $control ) {

		return ! is_multisite();

	}

}
