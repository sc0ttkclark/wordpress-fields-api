<?php
/**
 * WordPress Fields API Meta Box Section class
 *
 * @package    WordPress
 * @subpackage Fields API
 */

/**
 * Fields API Meta Box Table Section class. Ultimately renders controls in a table
 *
 * @see WP_Fields_API_Table_Section
 */
class WP_Fields_API_Meta_Box_Table_Section extends WP_Fields_API_Table_Section {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'meta-box-table';

	/**
	 * Meta box context
	 *
	 * @var string
	 */
	public $mb_context = 'advanced';

}