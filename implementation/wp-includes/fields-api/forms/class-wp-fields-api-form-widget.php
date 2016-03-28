<?php
/**
 * This is an implementation for Fields API for the Widgets Forms in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Widget
 */
class WP_Fields_API_Form_Widget extends WP_Fields_API_Form {

    /**
     * {@inheritdoc}
     */
    public $default_section_type = 'widget';
}