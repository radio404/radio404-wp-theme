<?php

namespace radio404\Admin;

class TracksSyncPage {

	public static function load(){
		wp_enqueue_script('radioking-tracks-sync-script', get_template_directory_uri() . '/js/admin/tracks-sync.js', ['jquery','wp-api']);
		wp_enqueue_style('radioking-tracks-sync-style', get_template_directory_uri() . '/css/admin/tracks-sync.css');
		include (__DIR__.'/Templates/Pages/tracks-sync.php');
	}

}