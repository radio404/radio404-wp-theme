<?php
/**
 * radio404
 * Date: 2020-01-31
 */

namespace radio404\Core;


use http\Exception;
use radio404\PostType\Album;
use radio404\PostType\Artist;
use radio404\PostType\Podcast;
use radio404\PostType\Schedule;
use radio404\PostType\Track;

class RadioKing {

	private const _ACCESS_TOKEN_TRANSIENT_ = '_RADIOKING_ACCESS_TOKEN_';
	private const _REFRESH_TOKEN_TRANSIENT_ = '_RADIOKING_REFRESH_TOKEN_';
	private const _BOXES_TRANSIENT_ = '_RADIOKING_BOXES_';
	private const _BOXES_TRANSIENT_EXPIRES_ = 604800; // 1 week expiration

	public static $radio_id;
	private static $access_token;

	public static function get_token() {

		if(isset(RadioKing::$access_token)) return RadioKing::$access_token;

		$access_token = get_transient(self::_ACCESS_TOKEN_TRANSIENT_);
		if($access_token) return $access_token;

		$refresh_token = get_transient(self::_REFRESH_TOKEN_TRANSIENT_);

		$api_oauth_endpoint = get_field('radioking_api_manager_auth_endpoint','options_radio');

		if($refresh_token){
			$api_oauth_endpoint .= '/refresh';
			$params = [
				'refresh_token' => $refresh_token
			];
		}else{
			$api_oauth_endpoint .= '/login';
			$rk_user_id = get_field("radioking_user_id","options_radio");
			$rk_password = get_field("radioking_password","options_radio");
			$params = [
				'login'    => $rk_user_id,
				'password' => $rk_password,
			];
		}

		$response = \Requests::post($api_oauth_endpoint, [], json_encode( $params ) );

		if($response->body) {
			$data = json_decode( $response->body );
			if ( $data && $data->access_token ) {
				RadioKing::$access_token = $access_token  = $data->access_token;
				$refresh_token = $data->refresh_token;
				$expires_in    = $data->expires_in;
				set_transient( self::_REFRESH_TOKEN_TRANSIENT_, $refresh_token );
				set_transient( self::_ACCESS_TOKEN_TRANSIENT_, $access_token, $expires_in );

				return RadioKing::$access_token;
			}
		}
		delete_transient(self::_REFRESH_TOKEN_TRANSIENT_);
		delete_transient(self::_ACCESS_TOKEN_TRANSIENT_);
		throw new \Exception('Unable do get access token');
	}

	public static function get_api_header(){
		$access_token = RadioKing::get_token();
		return $api_headers = [ "authorization"=> "Bearer $access_token"];
	}

	/**
	 * @return string
	 */
	public static function get_radio_id ():string{
		if(!isset(RadioKing::$radio_id)){
			RadioKing::$radio_id = get_field("radioking_radio_id","options_radio");
		}
		return RadioKing::$radio_id;
	}

	/**
	 * @return array
	 * @throws \Exception
	 * return list of RadioKing boxes
	 */
	public static function get_boxes():array{
		$boxes = get_transient(self::_BOXES_TRANSIENT_);
		if($boxes) return $boxes;

		$radio_id = self::get_radio_id();
		$response = \Requests::get("https://www.radioking.com/api/track/box/$radio_id",RadioKing::get_api_header());
		$boxes = json_decode($response->body)->data;
		set_transient(self::_BOXES_TRANSIENT_,$boxes,self::_BOXES_TRANSIENT_EXPIRES_);
		return $boxes;
	}

	public static function prepare_sync_track():array{
		$wp_users = get_users();
		$wp_users_display_name = [];
		foreach ($wp_users as $user){
			if($user->data->display_name === 'robot404'){
				$default_user_id = $user->ID;
			}
			$wp_users_display_name[strtolower($user->data->display_name)] = $user->ID;
		}

		add_filter('upload_dir',[__CLASS__,'cover_upload_dir'],10);
		add_filter('title_save_pre',[__CLASS__,'ignore_amp_filter'],10);
		add_filter('title_save_pre',[__CLASS__,'ignore_amp_filter'],10);

		return [
			'wp_users_display_name' => $wp_users_display_name,
			'default_user_id' => $default_user_id
		];
	}

	public static function sync_track($track):array {

		$prepare_sync          = self::prepare_sync_track();
		$wp_users_display_name = $prepare_sync['wp_users_display_name'];
		$default_user_id       = $prepare_sync['default_user_id'];

		return self::sync_single_track( $track, $wp_users_display_name, $default_user_id );;
	}

	public static function sync_tracks($offset=0,$limit=1,$box=1):array {

		$radio_id    = self::get_radio_id();
		$api_headers = RadioKing::get_api_header();
		$url         = "https://www.radioking.com/api/track/tracks/$radio_id/limit/$limit/offset/$offset/order/upload_date/desc?box=$box";
		$response    = \Requests::get( $url, $api_headers );

		$tracks          = json_decode( $response->body )->data;
		$tracks_imported = [];

		$prepare_sync          = self::prepare_sync_track();
		$wp_users_display_name = $prepare_sync['wp_users_display_name'];
		$default_user_id       = $prepare_sync['default_user_id'];

		foreach ( $tracks as $track ) {
			$tracks_imported[] = self::sync_single_track( $track, $wp_users_display_name, $default_user_id );
		}
		return $tracks_imported;
	}

	private static function sync_single_track($track, $wp_users_display_name, $default_user_id = 0){

		$wp_track = Track::get_track_by_id($track->idtrack);

		if($wp_track->ID){
			$track_no_sync = get_field('no_sync',$wp_track->ID);
			if($track_no_sync){
				return [
					'wp_track'=>$wp_track,
					'track'=>$track,
					'track_no_sync'=>$track_no_sync,
				];
			}
		}

		switch($track->idtrackbox){
			case 3:
				$album_post_type = Podcast::POST_TYPE;
				$wp_album = Podcast::get_podcast_by_title("$track->album");
				$wp_cover = Album::get_cover_by_album(false, false, $track->cover);
				break;
			default:
				$album_post_type = Album::POST_TYPE;
				$wp_album = Album::get_album_by_title_and_artist("$track->album","$track->artist");
				$wp_cover = Album::get_cover_by_album("$track->album", "$track->artist", $track->cover);
				break;
		}


		$id_author = $default_user_id;
		$upload_date = new \DateTime($track->upload_date);
		$post_date = $upload_date->format("Y-m-d H:i:s");

		if(!! $wp_track || (intval($wp_track->post_author) <= 0)) {
			// détails des tags
			$response      = \Requests::get( "https://www.radioking.com/api/track/240028/$track->idtrack", $api_headers );
			$track_details = json_decode( $response->body )->data;
			if($track_details->tags){
				foreach($track_details->tags as $index=>$tag){
					$tagname = strtolower($tag->name);
					if(isset($wp_users_display_name[$tagname])){
						$id_author = $wp_users_display_name[$tagname];
					}
				}
				$track->tags = $track_details->tags;
			}
		}else if($wp_track){
			$id_author = $wp_track->post_author;
		}

		$artists_names = array_map('trim',preg_split("/[,;]/","$track->artist"));
		$artist_list = [];

		foreach ($artists_names as $artist_name){
			switch(strtolower($artist_name)){
				case '':
				case 'Inconnu':
				case 'Unknown':
					$artist_name = 'Inconnu';
					break;
			}
			$wp_artist = Artist::get_artist_by_name($artist_name);
			$wp_artist_attr = [
				'post_title' => "$artist_name",
				'post_author'=> $id_author,
				'post_type' => 'artist',
				'post_status' => 'publish',
				'post_date' => $post_date,
			];
			if(!$wp_artist){
				$wp_artist_id = wp_insert_post($wp_artist_attr);
				$wp_artist = get_post($wp_artist_id);
			}else{
				$wp_artist_attr['ID'] = $wp_artist->ID;
				$artist_no_sync = get_field('no_sync',$wp_artist->ID);
				if(!$artist_no_sync){
					wp_update_post($wp_artist_attr);
				}
			}
			$artist_list[] = $wp_artist->ID;
		}

		$wp_album_meta = [
			'artist_literal' => "$track->artist",
			'artist' => $artist_list,
			'release_year' => $track->year,
		];

		$wp_album_attr = [
			'post_title' => "$track->album",
			'post_type' => $album_post_type,
			'post_status' => 'publish',
			'post_author'=> $id_author,
			'post_name' => sanitize_title("$track->artist--$track->album"),
			'post_date' => $post_date,
			'post_date_gmt' => $post_date,
			//'meta_input' => $wp_album_meta,
		];
		$album_no_sync = false;
		if(!$wp_album){
			$wp_album_id = wp_insert_post($wp_album_attr);
			$wp_album = get_post($wp_album_id);

		}else{
			$wp_album_attr['ID'] = $wp_album->ID;
			$album_no_sync = get_field('no_sync',$wp_album->ID);
			if(!$album_no_sync){
				wp_update_post($wp_album_attr);
			}
		}
		if(!$album_no_sync) {
			foreach ( $wp_album_meta as $field_key => $field_value ) {
				update_field( $field_key, $field_value, $wp_album->ID );
			}
		}

		$wp_track_meta = [
			'idtrack' => "$track->idtrack",
			'upload_date' => $track->upload_date,
			'release_year' => $track->year,
			'bpm' => $track->bpm,
			'tracklength_seconds' => $track->tracklength_seconds,
			'tracklength_string' => $track->tracklength_string,
			'playtime_seconds' => $track->playtime_seconds,
			'playtime_string' => $track->playtime_string,
			'artist' => $artist_list,
			'artist_literal' => "$track->artist",
			'album' => $wp_album->ID,
			'album_post_type' => $album_post_type,
			'album_literal' => "$track->album",
		];
		$wp_track_attr = [
			'post_title' => "$track->title",
			'post_type' => 'track',
			'post_status' => 'publish',
			'post_author'=> $id_author,
			'post_name' => sanitize_title("$track->title"),
			'post_date' => $post_date,
			'post_date_gmt' => $post_date,
			//'meta_input' => $wp_track_meta
		];

		if(!$wp_track){
			$wp_track_id = wp_insert_post( $wp_track_attr );
			$wp_track = get_post($wp_track_id);
		}else{
			$wp_track_attr['ID'] = $wp_track->ID;
			$wp_track_id = wp_update_post($wp_track_attr);
		}

		if(!$wp_track_id){
			return [
				'track'=>$track,
				'error'=>'fail to update Track'
			];
		}
		foreach ($wp_track_meta as $field_key => $field_value){
			update_field($field_key, $field_value, $wp_track->ID);
		}

		// on ajoute la track à l'album/podcast
		$track_listing = get_field('track_listing',$wp_album) ?? [];
		$is_in_track_listing = false;
		foreach($track_listing as $item){
			$item_track = $item['track'];
			$is_in_track_listing |= (!!$item_track && ($item_track->ID === $wp_track->ID));
		};
		if(!$is_in_track_listing){
			update_field('track_listing',array_merge($track_listing,[['track'=>$wp_track->ID]]),$wp_album->ID);
		}

		if($track->cover_url) {
			$wp_cover_meta = [
				'is_cover'    => true,
				'idtrack'     => $track->idtrack,
				'id_album'    => $wp_album->ID,
				'album'       => "$track->album",
				'artist'      => $artist_list,
				'artist_literal' => "$track->artist",
				'cover'       => $track->cover,
			];
			if ( ! $wp_cover) {
				$wp_cover_id = Attachment::insert_attachment_from_url(
					$track->cover_url,
					$wp_album->ID,
					"$track->title",
					$wp_cover_meta
				);
				$wp_cover    = get_post( $wp_cover_id );
			}

			if(!$album_no_sync){
				set_post_thumbnail( $wp_album->ID, $wp_cover->ID );
			}
			set_post_thumbnail( $wp_track->ID, $wp_cover->ID );
		}else{
			$wp_cover_id = false;
		}
		return [
			'track'=>$track,
			'wp_track_attr'=>$wp_track_attr,
			'wp_track'=>$wp_track,
			'wp_cover'=>$wp_cover,
			'track_no_sync'=>$track_no_sync ?? false,
			'cover_url'=>get_the_post_thumbnail_url($wp_track->ID,'thumbnail')
		];

	}

	public static function cover_upload_dir($upload){
		$upload['subdir'] = '/cover' . $upload['subdir'];
		$upload['path']       = $upload['basedir'] . $upload['subdir'];
		$upload['url']        = $upload['baseurl'] . $upload['subdir'];
		return $upload;
	}

	public static function ignore_amp_filter($value){
		return str_replace('&amp;','&',$value);
	}

	public static function radioking_sync_week_planned($mode='fetch'){
		$api_headers = self::get_api_header();
		$radio_id = self::get_radio_id();
		$day = date('w');
		$week_start = date('Y-m-d', strtotime('-'.($day).' days'));
		$week_end = date('Y-m-d', strtotime('+'.(7-$day).' days'));
		$response = \Requests::get("https://www.radioking.com/api/radio/$radio_id/schedule/planned/$week_start/to/$week_end",$api_headers);
		$radioking_schedules = json_decode($response->body)->data;
		foreach ($radioking_schedules as &$schedule){
			$schedule->day_playlist = !!preg_match('/^Day #\d/',$schedule->name);
			if($schedule->day_playlist){
				continue;
			}
			$type = 'playlist';
			$wp_schedule = Schedule::get_schedule_by_id($schedule->idschedule);
			$track_listing = get_field('track_listing',$wp_schedule) ?? [];

			if($schedule->idplaylist){
				$response = \Requests::get("https://www.radioking.com/api/playlist/tracks/$radio_id/$schedule->idplaylist?limit=50&offset=0",$api_headers);
				$schedule->playlist = json_decode($response->body)->data;
				foreach ($schedule->playlist->tracks as &$track){
					$wp_track = Track::get_track_by_id($track->idtrack);
					if($track->idtrackbox === 3){
						$type = 'podcast';
						$wp_track->acf = get_fields($wp_track->ID) ?? [];
						$wp_podcast = get_post($wp_track->acf['album']);
						$wp_podcast->acf = get_fields($wp_track->acf['album']);
						$schedule->podcast_id = $wp_track->acf['album'];
						$schedule->podcast = $wp_podcast;
						$track->wp_track = $wp_track;
						$podcast_track_id = $wp_track->ID;
					}
					$is_in_track_listing = false;
					foreach($track_listing as $item){
						if($wp_track) {
							$item_track          = $item['track'];
							$is_in_track_listing |= ( !! $item_track && ( $item_track->ID === $wp_track->ID ) );
						}else{
							$is_in_track_listing |= ( !! $item['title'] && ( $item['title'] === $track->title ) );
						}
					};
					if(!$is_in_track_listing){
						if($wp_track){
							$track_listing = array_merge($track_listing,[['track'=>$wp_track->ID]]);
						}else{
							$track_listing = array_merge($track_listing,[['title'=>$track->title,'tracklength_string'=>$track->tracklength_string,'tr'=>$track]]);
						}
					}
				}
			}

			$schedule_start = new \DateTime($schedule->schedule_start);
			$schedule_end = new \DateTime($schedule->schedule_end);
			$schedule_interval = $schedule_start->diff($schedule_end,true);
			$schedule_interval_hms = $schedule_interval->format('%H:%I:%S');
			$schedule_interval_diff = $schedule_end->getTimestamp() - $schedule_start->getTimestamp();
			$schedule_start = $schedule_start->format("Y-m-d H:i:s");
			$schedule_end = $schedule_end->format("Y-m-d H:i:s");
			$wp_schedule_meta = [
				'idschedule'=>$schedule->idschedule,
				'start'=>$schedule_start,
				'end'=>$schedule_end,
				'type'=>$type,
				'duration_seconds'=>$schedule_interval_diff,
				'duration_literal'=>$schedule_interval_hms,
				'podcast'=>$schedule->podcast_id??'',
				'podcast_track'=>$podcast_track_id??'',
				'track_listing'=>$track_listing,
				'color'=>$schedule->color,
			];

			$schedule->wp_schedule_meta = $wp_schedule_meta;

			$wp_schedule_attr = [
				'post_title' => $schedule->name,
				'post_type' => 'schedule',
				'post_status' => 'publish',
				'post_name' => sanitize_title("$schedule->name"),
				'post_date' => $schedule_start,
				'post_date_gmt' => $schedule_start,
			];

			if($mode === 'import') {
				if ( ! $wp_schedule ) {
					$wp_schedule_id = wp_insert_post( $wp_schedule_attr );
					$wp_schedule    = get_post( $wp_schedule_id );
				} else {
					$wp_schedule_attr['ID'] = $wp_schedule->ID;
					$wp_schedule_id         = wp_update_post( $wp_schedule_attr );
				}
				foreach ( $wp_schedule_meta as $field_key => $field_value ) {
					update_field( $field_key, $field_value, $wp_schedule->ID );
				}
			}
		}
		return $radioking_schedules;
	}

	public static function get_tracks_history(){
		global $wpdb;
		$utc_timezone = new \DateTimeZone('UTC');
		$paris_timezone = new \DateTimeZone('Europe/Paris');
		$now           = new \DateTime( 'now', $utc_timezone);
		$today         = date_format( $now, 'Y-m-d H:i:sP' );
		$yesterday      = date_format( $now->modify( '-1 day' ), 'Y-m-d  H:i:sP' );
		$history_sql = "SELECT * FROM `wp_track_log` WHERE `started_at` > '$yesterday' ORDER BY `id_track_log` DESC";
		$history       = $wpdb->get_results( $history_sql );
		$wp_tracks_ids = array_column( $history, 'wp_track_id' );
		$wp_tracks     = get_posts( [
			'numberposts' => - 1,
			'post_type'   => 'track',
			'post__in'    => $wp_tracks_ids,
		] );

		foreach ( $history as &$line ) {
			$wp_track_key = array_search( $line->wp_track_id, array_column( $wp_tracks, 'ID' ) );
			$date         = new \DateTime( $line->started_at, $utc_timezone );
			$date->setTimezone( $paris_timezone );

			if ( $wp_track_key ) {
				$wp_post               = $wp_tracks[ $wp_track_key ];
				$line->wp_post = $wp_post;

			}
		}

		return $history;
	}

	public static function get_last_track_logged(){
		global $wpdb;
		$track_log_table = $wpdb->prefix.'track_log';
		$sql = "SELECT * from $track_log_table ORDER BY `id_track_log` DESC LIMIT 1";
		return $wpdb->get_results($sql, OBJECT)[0];
	}

	public static function log_track($track){
		global $wpdb;

		$rk_track_id = $track->id;
		$last_track_logged = self::get_last_track_logged();
		if(!!$last_track_logged && ($last_track_logged->rk_track_id == $rk_track_id)){
			return [
				'success'=>false,
				'error'=>"track $rk_track_id already tracked"
			];
		}

		$track_log_table = $wpdb->prefix.'track_log';

		$started_at = \DateTime::createFromFormat(DATE_ISO8601, $track->started_at);
		$end_at = \DateTime::createFromFormat(DATE_ISO8601,$track->end_at);
		$wp_track = Track::get_track_by_id($rk_track_id);
		if(!$wp_track){
			$sync = RadioKing::sync_track($track);
			$wp_track = $sync['wp_track'];
		}
		$wp_track_id = $wp_track->ID ?? 0;
		$insert_args = [
			'rk_track_id'=>$rk_track_id,
			'wp_track_id'=>$wp_track_id,
			'started_at'=>$started_at->format('Y-m-d H:i:s'),
			'end_at'=>$end_at->format('Y-m-d H:i:s'),
			'title'=>"$track->title",
			'album'=>"$track->album",
			'artist'=>"$track->artist",
		];
		$success = $wpdb->insert($track_log_table,$insert_args,['%d','%d','%s','%s','%s','%s','%s']);

		return ['success'=>!!$success,'arguments'=>$insert_args,'last_track_logged'=>$last_track_logged];
	}

	public static function like_track($payload){
		$radio_id = self::get_radio_id();
		// fetch now playing data track
		$response = \Requests::get( "https://www.radioking.com/widgets/api/v1/radio/$radio_id/track/current" );
		$current = false;
		if($response->body){
			$current = json_decode($response->body);
		}
		$vote = intval($payload->vote) <= 0 ? -1 : 1;
		$is_current_track = $current && $current->id == $payload->id;
		$is_radio_king_like = $is_current_track ? self::radioking_like_track($vote) : false;
		$is_wp_like = self::wp_like_track($vote,$payload->emoji,$payload->id,$payload->wp_track_id);
		return [
			'success'=> true,
			'is_current_track'=>$is_current_track,
			'is_radio_king_like'=>$is_radio_king_like,
			'is_wp_like'=>!!$is_wp_like,
			'vote'=>$vote,
		];
	}

	private static function radioking_like_track($vote=1, $with_proxy=false, $try_count=0, $max_try=8){

		global $proxy_list;
		$ch = curl_init();
		$url = "https://www.radioking.com/api/radio/240028/track/vote";
		$headers = [];
		$ip = Security::get_the_user_ip();

		curl_setopt($ch, CURLOPT_POST,true);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_TIMEOUT,6);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"vote\":$vote}");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			"REMOTE_ADDR: $ip",
			"X_FORWARDED_FOR: $ip"
		]);

		$curl_body = curl_exec($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);

		$json_body = json_decode($curl_body);
		if($curl_error) {
			return ['status'=>'error','message'=>$curl_error,'ip'=>$ip];
		}
		if($json_body->status === 'success'){
			return $json_body;
		}else if($json_body->status === 'error'){
			return array_merge((array) $json_body,['ip'=>$ip]);
		}else{
			return ['status'=>'error','message'=>'fatal error','ip'=>$ip];
		}


	}

	private static function wp_like_track(int $vote, $emoji='❤️', $rk_track_id = 0, $wp_track_id = 0){
		global $wpdb;
		if(!$wp_track_id){
			$wp_track_id = Track::get_track_by_id($rk_track_id);
		}
		// emoji regexp
		$unicodeRegexp = '([*#0-9](?>\\xEF\\xB8\\x8F)?\\xE2\\x83\\xA3|\\xC2[\\xA9\\xAE]|\\xE2..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?(?>\\xEF\\xB8\\x8F)?|\\xE3(?>\\x80[\\xB0\\xBD]|\\x8A[\\x97\\x99])(?>\\xEF\\xB8\\x8F)?|\\xF0\\x9F(?>[\\x80-\\x86].(?>\\xEF\\xB8\\x8F)?|\\x87.\\xF0\\x9F\\x87.|..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?|(((?<zwj>\\xE2\\x80\\x8D)\\xE2\\x9D\\xA4\\xEF\\xB8\\x8F\k<zwj>\\xF0\\x9F..(\k<zwj>\\xF0\\x9F\\x91.)?|(\\xE2\\x80\\x8D\\xF0\\x9F\\x91.){2,3}))?))';
		preg_match( $unicodeRegexp, $emoji, $matches_emo );
		if(count($matches_emo) !== 1){
			// ignore non-emoji chars
			$emoji='❤️';
		}

		if(!$rk_track_id && !!$wp_track_id){
			$rk_track_id = intval(get_post_meta($wp_track_id,'idtrack'));
		}
		$success = $wpdb->insert($wpdb->prefix.'track_like',[
			'rk_track_id'=>$rk_track_id,
			'wp_track_id'=>$wp_track_id,
			'like_offset'=>$vote,
			'like_emoji'=>$emoji,
		]);

		return $success;
	}


}