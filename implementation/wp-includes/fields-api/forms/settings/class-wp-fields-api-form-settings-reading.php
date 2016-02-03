<?php
/**
 * This is an implementation for Fields API for the Reading form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_Reading
 */
class WP_Fields_API_Form_Settings_Reading extends WP_Fields_API_Form_Settings {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		// Sections
		$wp_fields->add_section( $this->object_type, $this->id . '-options-reading', null, array(
			'label'         => __( 'Reading Settings' ),
			'form'          => $this->id,
			'display_label' => false,
		) );

		// Controls
		/**
		 * Default Post Format
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'radio',
				'section'     => $this->id . '-options-reading',
				'label'       => __( 'Front page displays' ),
				'input_attrs' => array(
					'id'    => 'show_on_front',
					'name'  => 'show_on_front',
					'class' => 'tog'
				),
				'choices'     => array(
					'posts' => __( 'Your latest posts' ),
					'pages' => sprintf( __( 'A <a href="%s">static page</a> (select below)' ), get_admin_url( 'edit.php?post_type=page' ) ),
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'show_on_front', null, $field_args );
	}
}