<?php

namespace radio404\Core;

/**
 * Class Security
 * @package radio404\Core
 */
class Security {

	public function __construct() {
		add_action('http_api_curl', [$this,'ignore_internal_ssl_verify_host'], 10);
	}

	protected function ignore_internal_ssl_verify_host($handle){
		//Don't verify SSL certs for internal call
		$curlinfo = curl_getinfo($handle);
		$url = $curlinfo['url'];
		$site_url = get_site_url();
		$site_url_pattern = "~^$site_url.*~";
		if(preg_match_all($site_url_pattern,$url)){
			//curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
		}
	}

	public static function get_the_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}


}