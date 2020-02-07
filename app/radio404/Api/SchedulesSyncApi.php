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
				'methods' => ['POST'],
				'callback' => [$this,'schedules_fetch'],
				'permission_callback' => [$this,'permission_is_admin'],
				'args' => [
					'start'=>['required'=>true],
					'end'=>['required'=>true],
				]
			],
			'schedules/import' => [
				'methods' => ['POST'],
				'callback' => [$this,'schedules_import'],
				'permission_callback' => [$this,'permission_is_admin'],
				'args' => [
					'start'=>['required'=>true],
					'end'=>['required'=>true],
				]
			],
		];
	}

	public function schedules_fetch(\WP_REST_Request $request){

		return self::schedules_sync('fetch',$request['start'],$request['end']);
	}

	public function schedules_import(\WP_REST_Request $request){

		return self::schedules_sync('import',$request['start'],$request['end']);
	}

	public function schedules_sync($mode,int $start,int $end){

		try {
			$schedules = RadioKing::radioking_sync_period_planned($mode,$start,$end);
		}catch (\Throwable $exception){
			return new \WP_REST_Response(['message'=>$exception->getMessage()], 500);
		}

		return rest_ensure_response( $schedules );
	}

}