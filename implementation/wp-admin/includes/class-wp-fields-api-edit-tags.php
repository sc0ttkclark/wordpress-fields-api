<?php
/**
 * This is an implementation for Fields API for the Edit Tags screen in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_User_Profile
 */
class WP_Fields_API_Edit_Tags {

    public function __construct() {

        $this->register_controls();

    }

    /**
     * Register controls for Edit Tags
     *
     * @todo Move out of wp-admin implementation
     */
    public function register_controls() {

        /**
         * @var $wp_fields WP_Fields_API
         */
        global $wp_fields;

        // Register control types
        #$wp_fields->register_control_type( 'user-color-scheme', 'WP_Fields_API_Color_Scheme_Control' );

        // Add Edit Profile screen
        $wp_fields->add_screen( 'term', 'edit-tags' );

        ////////////////
        // Core: Name //
        ////////////////

        $wp_fields->add_section( 'term', 'term-options', 'edit-tags', array(
            'title' => __( 'Name' ),
        ) );

        $field_args = array(
            // @todo Needs validation callback
            'control' => array(
                'type'        => 'text',
                'section'     => 'term-options',
                'label'       => __( 'Name' ),
                'description' => __( 'The name is how it appears on your site.' ),
            ),
        );

        $wp_fields->add_field( 'term', 'tag-name', 'edit-tags', $field_args );

        $field_args = array(
            'control' => array(
                'type'                  => 'text',
                'section'               => 'term-options',
                'label'                 => __( 'Slug' ),
                'description'           => __( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' ),
            ),
        );

        $wp_fields->add_field( 'term', 'tag-slug', 'edit-tags', $field_args );

        $field_args = array(
            'control'               => array(
                'type'                  => 'textarea',
                'section'               => 'term-options',
                'label'                 => __( 'Description' ),
                'description'           => __( 'The description is not prominent by default; however, some themes may show it.' ),
                'input_attrs' => array(
                    'rows' => '5',
                    'cols' => '40',
                ),
            ),
        );

        $wp_fields->add_field( 'term', 'tag-description', 'edit-tags', $field_args );

    }

}