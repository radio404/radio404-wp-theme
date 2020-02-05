<?php
/**
 * radio404
 * Date: 2020-01-31
 */

namespace radio404\Api;


use radio404\Core\RadioKing;

class TracksLikeApi extends AbstractApi {

	public function getRoutes(): array {

		return [
			'tracks/like' => [
				'methods' => 'POST',
				'callback' => [$this,'tracks_like'],
			],
		];
	}

	public function tracks_like(\WP_REST_Request $request) {

		$success = ['success'=>false];
		try {
			$payload       = json_decode( $request->get_body() );
			if ( $payload ) {
				$success = RadioKing::like_track($payload);
			}
		}catch (Throwable $err){
			$success['error'] = $err->getMessage();
		}

		return rest_ensure_response( $success );
	}
}