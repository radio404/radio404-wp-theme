<?php

namespace radio404\Admin;

use radio404\Core\AdminPages;

class TracksSyncPage {

	public static function load(){
		wp_enqueue_script('radioking-tracks-sync-script', AdminPages::get_js_uri('tracks-sync'), ['jquery','wp-api']);
		wp_enqueue_style('radioking-tracks-sync-style', get_template_directory_uri() . '/css/admin/tracks-sync.css');
		include (__DIR__.'/Templates/Pages/tracks-sync.php');
	}

}