<?php

namespace radio404\PostType;

/**
 * Class AbstractPostType
 * @package radio404\PostType
 */
abstract class AbstractPostType {

	protected static $custom_columns = false;

	/**
	 * @param $args
	 *
	 * @return \WP_Post|null
	 */
	public static function get_post($args){
		// run query ##
		$query = new WP_Query($args);
		$posts = $query->posts;

		// check results ##
		if ( ! $posts || is_wp_error( $posts ) ) return null;

		// test it ##
		#pr( $posts[0] );

		// kick back results ##
		return $posts[0];
	}
}