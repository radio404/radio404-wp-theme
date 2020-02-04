<?php
/**
 * radio404
 * Date: 2020-01-31
 */

namespace radio404\Api;


use radio404\Core\RadioKing;

class TracksSyncApi extends AbstractApi {

	public function getRoutes(): array {

		return [
			'tracks/sync' => [
				'methods' => 'POST',
				'callback' => [$this,'tracks_sync'],
				'permission_callback' => [$this,'permission_is_admin']
			],
		];
	}

	public function tracks_sync() {

		$offset = intval($_POST['offset']);
		$idtrackbox = intval($_POST['idtrackbox']) ?? 1;
		$limit = intval($_POST['limit']);

		try {
			$tracks_imported = RadioKing::sync_tracks( $offset, $limit, $idtrackbox );
		}catch (Throwable $exception){
			return new WP_REST_Response(['message'=>$exception->getMessage()], 500);
		}

		return rest_ensure_response( $tracks_imported );
	}
}