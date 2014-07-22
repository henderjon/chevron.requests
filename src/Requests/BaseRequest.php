<?php

namespace Capstone\HTTP\Requests;

class BaseRequest {
	protected $url;
	protected $info = array(
		"scheme"           => "",
		"host"             => "",
		"port"             => "",
		"path"             => "",
		"query"            => "",
		"sub_domain"       => "",
		"domain"           => "",
		"top_level_domain" => "",
		"user"             => "",
		"pass"             => "",
		"query_arr"        => array(),
		"dirname"          => "",
		"basename"         => "",
		"extension"        => "",
		"filename"         => "",
		"action"           => "",
		"hash"             => "",
		"status_code"      => "200",
		"content_type"     => "",
	);

	function __construct( $url = "", array $query = array() ){
		if(empty($url)) return;

		if($query){
			if( false != ($pos = strpos($url, "?"))){
				$url = substr($url, 0, $pos);
			}
			$query = http_build_query($query, "no_", "&");
			$url   = "{$url}?{$query}";
		}
		$this->parse($url);
	}

	/**
	 * parse a url into its component parts
	 * @param string $url The URL to parse
	 * @param string $action The action of the request
	 * @return \Capstone\HTTP\Requests\BaseRequest
	 * @throws \Exception
	 */
	function parse( $url = "", $action = "GET" ){
		if(!$url){
			throw new \Exception("A valid url must be supplied to parse");
		}

		$info = parse_url( $url );

		$info = $this->parse_extended($info);

		$info["action"] = $action;

		foreach($info as $name => $value){
			if(array_key_exists($name, $this->info)){
				$this->info[$name] = $value;
			}
		}
	}

	/**
	 * parse the query, host, and path params into a more extended format
	 * @param array $info An array to parse
	 * @return array
	 */
	function parse_extended(array $info){
		if(array_key_exists("query", $info)){
			$info["query_arr"] = array();
			parse_str($info["query"], $info["query_arr"]);
		}

		if(array_key_exists("host", $info)){
			$domain = explode(".", $info['host']);
			$info["top_level_domain"] = array_pop($domain) ?: "";
			$info["domain"]           = array_pop($domain) ?: "";
			$info["sub_domain"]       = implode(".", $domain) ?: "";
		}

		if(array_key_exists("path", $info)){
			$info["hash"] = hash("md5", $info["path"]);
			$parts = pathinfo($info["path"]);
			foreach($parts as $name => $value){
				$info[$name] = $value;
			}
		}
		return $info;
	}

	/**
	 * build the current request object
	 * @return string
	 */
	function build(){
		$url = static::build_url($this);
		return $url;
	}

	/**
	 * change parts of the request object
	 * @param type array $info
	 */
	function alter_request(array $info){
		foreach($info as $part => $value){
			if(array_key_exists($part, $this->info)){
				switch(true){
					case $part == "host" :
						$temp = $this->parse_extended(array($part => $value));
						$this->info["host"]             = $value;
						$this->info["top_level_domain"] = $temp["top_level_domain"];
						$this->info["domain"]           = $temp["domain"];
						$this->info["sub_domain"]       = $temp["sub_domain"];
					break;
					case $part == "query"     : break;
					case $part == "query_arr" : break;
					case $part == "path"      :
						$temp = $this->parse_extended(array($part => $value));
						$this->info["path"]      = $value;
						$this->info["dirname"]   = $temp["dirname"];
						$this->info["basename"]  = $temp["basename"];
						$this->info["extension"] = $temp["extension"];
						$this->info["filename"]  = $temp["filename"];
					break;
					default :
						$this->info[$part] = $value;
					break;
				}
			}
		}
	}

	/**
	 * change parts of the current request object
	 * @param array $params The params to add
	 * @param bool $preserve Whether to preserve the requests current query
	 */
	function alter_query(array $params, $preserve = true){
		if($preserve){
			$params = array_merge($this->info["query_arr"], $params);
		}
		$this->info["query"]     = http_build_query($params, "no_", "&");;
		$this->info["query_arr"] = $params;
	}

	/**
	 * shortcut to changing and then building the current request object
	 * @param array $params The new query
	 * @param bool $preserve Whether to preserve the current query
	 * @return string
	 */
	function rebuild( array $params = array(), $preserve = true ) {
		$this->alter_query($params, $preserve);

		return $this->build();
	}

	/**
	 * magic getter method
	 * @param string $name The property to get
	 * @return mixed
	 */
	function __get($name){
		if(array_key_exists($name, $this->info)){
			return $this->info[$name];
		}
		return null;
	}

	/**
	 * magic isset method, will return if the property is empty
	 * @param string $name The property to check
	 * @return bool
	 */
	function __isset($name){
		if(array_key_exists($name, $this->info)){
			return !empty($this->info[$name]);
		}
		return false;
	}

	/**
	 * method to take a Request object and reconstitute it to a full URL. if the
	 * "host" param is empty, the URL will be relative
	 * @param \Capstone\HTTP\Requests\BaseRequest $request The object to reconstitute
	 * @return string
	 */
	static function build_url(\Capstone\HTTP\Requests\BaseRequest $request){

		$absolute = "";
		if(!empty($request->info["host"])){

			$scheme = "http";
			if(!empty($request->info["scheme"])){
				$scheme = $request->info["scheme"];
			}

			$auth_prefix = "";
			if(!empty($request->info["user"])){
				$auth_prefix = sprintf("%s:%s@", $request->info["user"], $request->info["pass"]);
			}

			$port = "";
			if(!empty($request->info["port"])){
				switch(true){
					case $request->info["port"] == 80  : break;
					case $request->info["port"] == 443 :
						$scheme = "https";
					break;
					default :
						$port = ":{$request->info["port"]}";
					break;
				}
			}

			$host = $request->info["host"];
			$absolute = sprintf("%s://%s%s%s", $scheme, $auth_prefix, $host, $port);
		}

		$path = "";
		if(!empty($request->info["path"])){
			$path = ltrim($request->info["path"], "/");
		}

		$query = "";
		if(!empty($request->info["query_arr"])){
			$query = http_build_query($request->info["query_arr"], "no_", "&");
			$query = "?{$query}";
		}

		$url = sprintf("%s/%s%s", $absolute, $path, $query);
		return $url;

	}

}
