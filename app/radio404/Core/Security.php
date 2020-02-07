<?php

namespace radio404\Core;

/**
 * Class Security
 * @package radio404\Core
 */
class Security {

	static $proxy_list = null;
	static $proxy_list_index = null;
	private const _TRANSIENT_PROXY_LIST_ = 'PROXY_LIST';
	private const _TRANSIENT_PROXY_LIST_INDEX_ = 'PROXY_LIST_INDEX';

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

	public static function get_proxy_list($type='socks5'){
		$proxy_list_api_url = "https://www.proxy-list.download/api/v1/get?type=$type";
		if(is_null(self::$proxy_list)){
			self::$proxy_list = get_transient(self::_TRANSIENT_PROXY_LIST_.$proxy_list_api_url);
			self::$proxy_list_index = get_transient(self::_TRANSIENT_PROXY_LIST_INDEX_);
		}
		if(!self::$proxy_list){
			$response           = \Requests::get( $proxy_list_api_url );
			self::$proxy_list         = explode( "\r\n", trim($response->body) );
			self::$proxy_list_index   = 0;
			set_transient(self::_TRANSIENT_PROXY_LIST_.$proxy_list_api_url,self::$proxy_list,300);
			set_transient(self::_TRANSIENT_PROXY_LIST_INDEX_,self::$proxy_list_index,300);
		}
		return self::$proxy_list;
	}

	public static function get_proxy_list_index(){
		if(is_null(self::$proxy_list_index)){
			self::$proxy_list_index = get_transient(self::_TRANSIENT_PROXY_LIST_INDEX_);
		}
		if(is_null(self::$proxy_list_index)){
			self::$proxy_list_index = 0;
		}else if(is_numeric(self::$proxy_list_index)){
			self::$proxy_list_index++;
			self::$proxy_list = self::$proxy_list % count(self::$proxy_list);
		}
		set_transient(self::_TRANSIENT_PROXY_LIST_INDEX_,self::$proxy_list_index,300);
		return self::$proxy_list_index;

	}

	public static function get_fresh_public_proxy(){
		$proxy_list = self::get_proxy_list();
		$proxy_index = self::get_proxy_list_index();
		return $proxy_list[$proxy_index];
	}


}