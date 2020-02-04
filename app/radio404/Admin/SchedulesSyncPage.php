<?php

namespace radio404\Admin;

class SchedulesSyncPage {

	public static function load(){
		wp_enqueue_script('radioking-schedule-sync-script', get_template_directory_uri() . '/js/admin/schedules-sync.js', ['jquery','wp-api']);
		wp_enqueue_style('radioking-schedule-sync-style', get_template_directory_uri() . '/css/admin/schedules-sync.css');
		include (__DIR__.'/Templates/Pages/schedules-sync.php');
	}

}