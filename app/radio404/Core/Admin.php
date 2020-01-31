<?php

namespace radio404\Core;


class Admin {

	public function __construct() {
		add_action('admin_print_styles', [$this,'admin_print_styles'], 11);
	}

	public function admin_print_styles() {
		$admin_handle = 'admin_css';
		$admin_stylesheet = get_template_directory_uri() . '/admin.css';
		wp_enqueue_style($admin_handle, $admin_stylesheet);

		$inter_stylesheet = get_template_directory_uri() . '/fonts/Inter/inter.css';
		wp_enqueue_style('inter_font_css', $inter_stylesheet,['wp-block-editor']);

	}

	/**
	 * Insert an attachment from an URL address.
	 *
	 * @param  String $url
	 * @param  Int    $parent_post_id
	 * @param  String $title
	 * @param  Array  $meta
	 * @return Int    Attachment ID
	 */
	public static function insert_attachment_from_url($url, $parent_post_id = null, $title = null, $meta = []) {
		if( !class_exists( 'WP_Http' ) )
			include_once( ABSPATH . WPINC . '/class-http.php' );
		$http = new WP_Http();
		$response = $http->request( $url );
		if( $response['response']['code'] != 200 ) {
			return false;
		}
		$upload = wp_upload_bits( basename($url), null, $response['body'] );
		if( !empty( $upload['error'] ) ) {
			return false;
		}
		$file_path = $upload['file'];
		$file_name = basename( $file_path );
		$file_type = wp_check_filetype( $file_name, null );
		$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
		$wp_upload_dir = wp_upload_dir();
		$target_title = !!$title ? $title : $attachment_title;
		$post_info = array(
			'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
			'post_mime_type' => $file_type['type'],
			'post_title'     => $target_title,
			'post_content'   => '',
			'post_status'    => 'inherit',
			'meta_input'     => $meta
		);
		// Create the attachment
		$attach_id = wp_insert_attachment( $post_info, $file_path, $parent_post_id );
		// Include image.php
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
		// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id,  $attach_data );
		return $attach_id;
	}
}