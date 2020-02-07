<?php

namespace radio404\Admin;

use radio404\Core\AdminPages;

class SchedulesSyncPage {

	public static function load(){
		wp_enqueue_script('radioking-schedule-sync-script', AdminPages::get_js_uri('schedules-sync'), ['jquery','wp-api']);
		wp_enqueue_style('radioking-schedule-sync-style', get_template_directory_uri() . '/css/admin/schedules-sync.css');
		include (__DIR__.'/Templates/Pages/schedules-sync.php');
	}

}