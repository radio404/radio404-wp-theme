<?php

namespace radio404\Core;

use radio404\Api\SchedulesSyncApi;
use radio404\Api\TracksHistoryApi;
use radio404\Api\TracksSyncApi;

class ApiLoader {

	public function __construct() {
		new TracksSyncApi();
		new TracksHistoryApi();
		new SchedulesSyncApi();
	}
}