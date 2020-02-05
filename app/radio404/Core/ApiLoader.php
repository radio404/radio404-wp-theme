<?php

namespace radio404\Core;

use radio404\Api;

class ApiLoader {

	public function __construct() {
		new Api\TracksSyncApi();
		new Api\TracksHistoryApi();
		new Api\TracksLikeApi();
		new Api\SchedulesSyncApi();
	}
}