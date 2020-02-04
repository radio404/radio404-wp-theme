<?php
/**
 * radio404
 * Date: 2020-01-31
 */

namespace radio404\Api;


abstract class AbstractApi {

	protected $routes = [];

	public const API_NAMESPACE = 'radio404/v1';

	public function __construct() {
		add_action( 'rest_api_init', [$this,'rest_api_init'] );
	}

	public function permission_is_admin(){
		return current_user_can('manage_options');
	}

	public function rest_api_init(){
		foreach ($this->getRoutes() as $route=>$routeArgs){
			register_rest_route(AbstractApi::API_NAMESPACE , $route, $routeArgs);
		}
	}

	public function getRoutes():array {
		return [];
	}
}