<?php
/**
 * radio404
 * Date: 2020-01-31
 */

namespace radio404\Api;


use radio404\Core\RadioKing;

class TracksHistoryApi extends AbstractApi {

	public function getRoutes(): array {

		return [
			'tracks/log' => [
				'methods' => 'POST',
				'callback' => [$this,'tracks_log'],
				'permission_callback' => [$this,'check_passphrase']
			],
		];
	}

	public function check_passphrase($request_data){
		$payload = json_decode( $request_data->get_body() );
		return !defined('WP_RK_LOG_TRACK_PASSPHRASE') || $payload->passphrase === WP_RK_LOG_TRACK_PASSPHRASE;
	}

	public function tracks_log($request_data) {
		$success = ['success'=>false];
		try {
			$payload = json_decode( $request_data->get_body() );
			$success = RadioKing::log_track($payload);
		}catch (\Throwable $err){
			$success['error'] = $err->getMessage();
		}

		return rest_ensure_response( $success );
	}
}