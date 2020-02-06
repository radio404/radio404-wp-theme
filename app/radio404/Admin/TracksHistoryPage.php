<?php

namespace radio404\Admin;

class TracksHistoryPage {

	public static function load(){
		wp_enqueue_style('radioking-tracks-history-style', get_template_directory_uri() . '/css/admin/tracks-history.css');
		include (__DIR__.'/Templates/Pages/tracks-history.php');
	}

}