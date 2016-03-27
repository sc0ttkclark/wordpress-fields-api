<?php

class WP_Fields_API_Form_Widget extends WP_Fields_API_Form {

    /**
     * {@inheritdoc}
     */
    public $default_section_type = 'widget';

    /**
     * {@inheritdoc}
     */
    public function register_fields( $wp_fields ) {

        // Add example fields (maybe)
        parent::register_fields( $wp_fields );

    }

    /**
     * {@inheritdoc}
     */
    public function save_fields( $item_id = null, $object_name = null ) {

        // Save additional fields
        return parent::save_fields( $item_id, $object_name );

    }
}