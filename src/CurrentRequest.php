<?php

namespace Capstone\Requests;

class CurrentRequest extends BaseRequest {

	protected $headers = array();

	/**
	 * Create a Request object based on the information in $_SERVER about the
	 * current request
	 *
	 * @param bool $request_authorization Send basic authorization headers
	 * @throws \Exception
	 */
	function __construct( $request_authorization = false ){

		$auth_prefix = "";
		if($request_authorization){
			$auth_prefix = vsprintf("%s:%s@", $this->request_authorization());
		}

		$scheme = "http";
		if( array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] === 'on' ){
			$scheme = "https";
		}

		$params = array("SERVER_NAME", "SERVER_PORT", "REQUEST_URI", "REQUEST_METHOD");
		foreach($params as $param){
			if(!array_key_exists($param, $_SERVER)){
				throw new \Exception("The {$param} is missing from the server array.");
			}
		}

		$url = vsprintf("%s://%s%s:%s%s", array(
			$scheme, $auth_prefix,
			$_SERVER["SERVER_NAME"],
			$_SERVER["SERVER_PORT"],
			$_SERVER["REQUEST_URI"])
		);

		foreach($_SERVER as $key => $val){
			if( strpos($key, "HTTP_") !== false ){
				$this->headers[substr($key, 5)] = $val;
			}
		}

		return $this->parse($url, $_SERVER["REQUEST_METHOD"]);

	}

	/**
	 * send/recieve/parse basic authorization headers
	 *
	 * @return array
	 */
	protected function request_authorization(){
		$username = $password = "";

		switch( true ){
			case( array_key_exists("PHP_AUTH_USER", $_SERVER) ):
				$username = $_SERVER['PHP_AUTH_USER'];
				$password = $_SERVER['PHP_AUTH_PW'];
			break;
			case( array_key_exists("HTTP_AUTHENTICATION", $_SERVER) ):
				if( strpos( strtolower( $_SERVER['HTTP_AUTHENTICATION'] ), 'basic' ) === 0 ) {
					list( $username, $password ) = explode( ':', base64_decode( substr( $_SERVER['HTTP_AUTHORIZATION'], 6 ) ) );
				}
			break;
			default:
				header('WWW-Authenticate: Basic realm="Capstone"');
				header('HTTP/1.0 401 Unauthorized');
				printf("It was a good rain, the kind you wait for ...");
				die();
			break;
		}

		return array( $username, $password );
	}

	/**
	 * build a link for a different file in the same current requested directory
	 *
	 * @param string $file The new file
	 * @param array $params New query params
	 * @param bool $preserve Whether to append of replace the current query
	 * @return string
	 */
	function pwd($file = "", array $params = array(), $preserve = true){
		$request = clone $this;
		$path = rtrim($request->dirname, " /");
		$request->alter_request(array("path" => "{$path}/{$file}", "host" => null));
		return $request->rebuild($params, $preserve);
	}

	/**
	 * @param bool $parsed
	 * @return mixed|string
	 * @deprecated
	 */
	function base_href( $parsed = false ) {
		$_BASE["PROTOCOL"] = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https" : "http";
		// $_BASE["SERVER_ADDR"] = $_SERVER["SERVER_ADDR"];
		$_BASE["HOST"] = trim(strtolower($_SERVER['SERVER_NAME']));
		if( strpos($_BASE["HOST"], ':') !== false ) {
			//IPV6 Support
			$_BASE["HOST"] = "[{$_BASE["HOST"]}]";
		}

		//there's no need to show the port when it's a standard 80|443
		$_PORT = trim(strtolower($_SERVER['SERVER_PORT']));
		if( !in_array($_PORT, array( "80", "443" )) ) {
			$_BASE["PORT"] = $_PORT;
			$_BASE         = vsprintf("%s://%s:%s", $_BASE);
		} else {
			$_BASE = vsprintf("%s://%s", $_BASE);
		}

		$_PATH = dirname(str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
		$_PATH = !empty($_PATH) ? "/{$_PATH}/" : "/";
		$_PATH = rtrim("{$_PATH}", "/") . "/";
		$link  = "{$_BASE}{$_PATH}";

		if( $parsed ) {
			return parse_url($link);
		}

		return $link;
	}

	/**
	 * check if the current request is a POST request
	 * @return bool
	 */
	function is_post(){
		return $this->action === "POST";
	}

	/**
	 * method to get a specific header from the current request. The
	 * retrieval is scoped to only those headers that start with HTTP_
	 * @param string $name The name of the header WITHOUT the HTTP_
	 * @return string
	 */
	function getHeader($name){
		if(array_key_exists($name, $this->headers)){
			return $this->headers[$name];
		}
		return null;
	}

}