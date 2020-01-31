<?php

namespace radio404\Core;

/**
 * Class AcfFilters
 * @package radio404\Core
 */
class Acf{

	/**
	 * AcfFilters constructor.
	 */
	public function __construct() {
		add_filter('acf/format_value',[$this,'acf_nullify_empty'],10,3);
		$this->add_options_radio_page();
	}

	/**
	 * @param $value
	 * @param $post_id
	 * @param $field
	 *
	 * @return null
	 * see https://www.gatsbyjs.org/packages/gatsby-source-wordpress/#graphql-error---unknown-field-on-acf
	 */
	public function acf_nullify_empty($value, $post_id, $field){
		if (empty($value)) {
			return null;
		}
		return $value;
	}

	/**
	 * @return array|bool|string
	 */
	public function add_options_radio_page(){
		$page_title = 'Radio';
		$page_slug = 'options_radio';
		return acf_add_options_page([
			'page_title'	=> $page_title,
			'menu_title'	=> $page_title,
			'menu_slug'     => $page_slug,
			'slug'          => $page_slug,
			'post_id'       => $page_slug,
			'icon_url' => 'dashicons-radio',
		]);
	}

}