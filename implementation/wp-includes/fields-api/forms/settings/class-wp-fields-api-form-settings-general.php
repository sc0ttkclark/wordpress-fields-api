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
		 * Registration
		 */
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

	}

}