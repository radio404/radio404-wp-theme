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

}