<?php

namespace Chevron\Requests;

class Redirect {

	/**
	 * build a url, and send a location header to that URL
	 * @param string $url The base URL
	 * @param array $params The query for that URL
	 * @param bool $force_ssl If "host" isset, force an HTTPS request
	 */
	function __construct( $redirect, array $params = array(), $force_ssl = false ){

		if(!($redirect InstanceOf BaseRequest)){
			$redirect = new BaseRequest($redirect, $params);
		}

		if(isset($redirect->scheme) && $force_ssl){
			$redirect->alter_request(array("scheme" => "https"));
		}

		$headers = new ResponseHeaders;
		$headers->setRedirect($redirect->build());
		$headers->eachHeader("header");
		exit(0);

	}

}