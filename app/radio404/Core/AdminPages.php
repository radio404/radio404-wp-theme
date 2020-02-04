<?php

namespace radio404\Core;


use radio404\Admin\SchedulesSyncPage;
use radio404\Admin\TracksHistoryPage;
use radio404\Admin\TracksSyncPage;

class AdminPages {

	public function __construct() {
		add_action('admin_print_styles', [$this,'admin_print_styles'], 11);
		add_action('admin_menu', [$this,'admin_menu']);
	}

	function radioking_dashboard_page(){
		include (__DIR__.'/../pages/radioking-dashboard.php');
	}
	function initTracksSyncPage(){
		wp_enqueue_script('radioking-tracks-import-script', plugins_url() . '/radioking/js/tracks-import.js', array('jquery'));
		wp_enqueue_style('radioking-tracks-import-style', plugins_url() . '/radioking/css/tracks-import.css');
		include (__DIR__.'/../pages/radioking-tracks-import.php');
	}
	function radioking_schedules_import_page(){
		wp_enqueue_script('radioking-schedules-import-script', plugins_url() . '/radioking/js/schedules-import.js', array('jquery'));
		wp_enqueue_style('radioking-schedules-import-style', plugins_url() . '/radioking/css/schedules-import.css');
		include (__DIR__.'/../pages/radioking-schedules-import.php');
	}
	function radioking_tracks_history_page(){
		global $wpdb;
		wp_enqueue_style('radioking-tracks-history-style', plugins_url() . '/radioking/css/tracks-history.css');
		include (__DIR__.'/../pages/radioking-tracks-history.php');
	}

	function admin_menu() {
		add_menu_page( __( 'Gestion RadioKing', 'radio404' ),
			__( 'radio404', 'radio404' ), 'administrator',
			'radioking-admin', 'radioking_dashboard_page',
			'dashicons-radio404', 80 );


		add_submenu_page( 'radioking-admin',
			__( 'Syncronisation des pistes RadioKing', 'radio404' ),
			__( 'Syncronisation', 'radio404' ),
			'administrator',
			'tracksSync',
			[TracksSyncPage::class,'load'],
			);

		add_submenu_page( 'radioking-admin',
			__( 'Syncronisation du planning RadioKing', 'radio404' ),
			__( 'Planning', 'radio404' ),
			'administrator',
			'radioking-schedules-sync',
			[SchedulesSyncPage::class,'load'],
			);
		add_submenu_page( 'radioking-admin',
			__( 'Historique des pistes RadioKing', 'radio404' ),
			__( 'Historique', 'radio404' ),
			'administrator',
			'radioking-tracks-history',
			[TracksHistoryPage::class,'load'],
			);
	}

	public function admin_print_styles() {
		$admin_handle = 'admin_css';
		$admin_stylesheet = get_template_directory_uri() . '/admin.css';
		wp_enqueue_style($admin_handle, $admin_stylesheet);

		$inter_stylesheet = get_template_directory_uri() . '/fonts/Inter/inter.css';
		wp_enqueue_style('inter_font_css', $inter_stylesheet,['wp-block-editor']);

	}
}