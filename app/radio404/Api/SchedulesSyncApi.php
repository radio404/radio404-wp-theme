<?php
/**
 * wordpress
 * Date: 2020-02-04
 */

namespace radio404\Api;


use radio404\Core\RadioKing;

class SchedulesSyncApi extends AbstractApi {

	public function getRoutes(): array {

		return [
			'schedules/fetch' => [
				'methods' => 'POST',
				'callback' => [$this,'schedules_fetch'],
				'permission_callback' => [$this,'permission_is_admin']
			],
			'schedules/import' => [
				'methods' => 'POST',
				'callback' => [$this,'schedules_import'],
				'permission_callback' => [$this,'permission_is_admin']
			],
		];
	}

	public function schedules_fetch(){

		return self::schedules_sync('fetch');
	}

	public function schedules_import(){

		return self::schedules_sync('import');
	}

	public function schedules_sync($mode){

		try {
			$schedules = RadioKing::radioking_sync_week_planned($mode);
		}catch (\Throwable $exception){
			return new \WP_REST_Response(['message'=>$exception->getMessage()], 500);
		}

		return rest_ensure_response( $schedules );
	}

}