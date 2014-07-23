<?php

class RequestTest extends PHPUnit_Framework_TestCase {

	public function test_construct(){

		$request = new \Chevron\Requests\BaseRequest;

		$this->assertInstanceOf("Chevron\Requests\BaseRequest", $request, "CurrentRequest::__construct failed to return an object of the proper type");
	}

	public function test_construct_with_relative_url(){
		$url = "/dir/file.html?qry=str&snow=white";

		$request = new \Chevron\Requests\BaseRequest($url);

		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$expected = array(
			"scheme"           => "",
			"host"             => "",
			"port"             => "",
			"path"             => "/dir/file.html",
			"query"            => "qry=str&snow=white",
			"sub_domain"       => "",
			"domain"           => "",
			"top_level_domain" => "",
			"user"             => "",
			"pass"             => "",
			"query_arr"        => array(
				"qry"=>"str",
				"snow"=>"white"
			),
			"dirname"          => "/dir",
			"basename"         => "file.html",
			"extension"        => "html",
			"filename"         => "file",
			"action"           => "GET",
		);

		foreach($expected as $key => $value){
			$this->assertEquals($info[$key], $value, "Request::__construct failed to set the {$key} property");
		}

	}

	public function test_construct_with_absolute_url(){
		$url = "http://local.testing.com/dir/file.html?qry=str&snow=white";

		$request = new \Chevron\Requests\BaseRequest($url);

		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$expected = array(
			"scheme"           => "http",
			"host"             => "local.testing.com",
			"port"             => "",
			"path"             => "/dir/file.html",
			"query"            => "qry=str&snow=white",
			"sub_domain"       => "local",
			"domain"           => "testing",
			"top_level_domain" => "com",
			"user"             => "",
			"pass"             => "",
			"query_arr"        => array(
				"qry"=>"str",
				"snow"=>"white"
			),
			"dirname"          => "/dir",
			"basename"         => "file.html",
			"extension"        => "html",
			"filename"         => "file",
			"action"           => "GET",
		);

		foreach($expected as $key => $value){
			$this->assertEquals($info[$key], $value, "Request::__construct failed to set the {$key} property");
		}

	}

	public function test_construct_with_params(){
		$url = "http://local.testing.com/dir/file.html?qry=str&snow=white";

		$additional = array(
			"seven" => "little people"
		);

		$request = new \Chevron\Requests\BaseRequest($url, $additional);

		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$expected = array(
			"scheme"           => "http",
			"host"             => "local.testing.com",
			"port"             => "",
			"path"             => "/dir/file.html",
			"query"            => "seven=little+people",
			"sub_domain"       => "local",
			"domain"           => "testing",
			"top_level_domain" => "com",
			"user"             => "",
			"pass"             => "",
			"query_arr"        => array("seven" => "little people"),
			"dirname"          => "/dir",
			"basename"         => "file.html",
			"extension"        => "html",
			"filename"         => "file",
			"action"           => "GET",
		);

		foreach($expected as $key => $value){
			$this->assertEquals($info[$key], $value, "Request::__construct failed to set the {$key} property");
		}

	}

	public function test_parse_extended(){
		$request = new \Chevron\Requests\BaseRequest;

		$info = array(
			"host"  => "local.Chevron.com",
			"query" => "mobile=phones",
			"path"  => "/path/to/file.html",
		);

		$result = $request->parse_extended($info);

		$expected = array(
			"host"             => "local.Chevron.com",
			"top_level_domain" => "com",
			"domain"           => "Chevron",
			"sub_domain"       => "local",
			"query"            => "mobile=phones",
			"query_arr"        => array("mobile"=>"phones"),
			"path"             => "/path/to/file.html",
			"dirname"          => "/path/to",
			"basename"         => "file.html",
			"filename"         => "file",
			"extension"        => "html",
			'hash'             => '68527be74e41edaf65030fba85e9011d'
		);

		$this->assertEquals($expected, $result, "Request::parse_extended failed to parse the array correctly");

	}

	public function test_parse_extended_nulls(){
		$request = new \Chevron\Requests\BaseRequest;

		$info = array(
			"host"  => null,
			"query" => "mobile=phones",
			"path"  => "/path/to/file.html",
		);

		$result = $request->parse_extended($info);

		$expected = array(
			"host"             => "",
			"top_level_domain" => "",
			"domain"           => "",
			"sub_domain"       => "",
			"query"            => "mobile=phones",
			"query_arr"        => array("mobile"=>"phones"),
			"path"             => "/path/to/file.html",
			"dirname"          => "/path/to",
			"basename"         => "file.html",
			"filename"         => "file",
			"extension"        => "html",
			'hash'             => '68527be74e41edaf65030fba85e9011d'
		);

		$this->assertEquals($expected, $result, "Request::parse_extended failed to parse the array correctly");

	}

	public function test_parse_absolute_url(){
		$url = "http://local.testing.com/dir/file.html?qry=str&snow=white";

		$request = new \Chevron\Requests\BaseRequest;
		$result  = $request->parse($url);

		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$expected = array(
			"scheme"           => "http",
			"host"             => "local.testing.com",
			"port"             => "",
			"path"             => "/dir/file.html",
			"query"            => "qry=str&snow=white",
			"sub_domain"       => "local",
			"domain"           => "testing",
			"top_level_domain" => "com",
			"user"             => "",
			"pass"             => "",
			"query_arr"        => array(
				"qry"=>"str",
				"snow"=>"white"
			),
			"dirname"          => "/dir",
			"basename"         => "file.html",
			"extension"        => "html",
			"filename"         => "file",
			"action"           => "GET",
		);

		foreach($expected as $key => $value){
			$this->assertEquals($info[$key], $value, "Request::parse failed to set the {$key} property");
		}

	}

	public function test_parse_absolute_url_empty_query(){
		$url = "http://local.testing.com/dir/file.html";

		$request = new \Chevron\Requests\BaseRequest;
		$result  = $request->parse($url);

		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$expected = array(
			"scheme"           => "http",
			"host"             => "local.testing.com",
			"port"             => "",
			"path"             => "/dir/file.html",
			"query"            => "",
			"sub_domain"       => "local",
			"domain"           => "testing",
			"top_level_domain" => "com",
			"user"             => "",
			"pass"             => "",
			"query_arr"        => array(),
			"dirname"          => "/dir",
			"basename"         => "file.html",
			"extension"        => "html",
			"filename"         => "file",
			"action"           => "GET",
		);

		foreach($expected as $key => $value){
			$this->assertEquals($info[$key], $value, "Request::parse failed to set the {$key} property");
		}

	}

	public function test_parse_relative_url_empty_query(){
		$url = "/dir/file.html";

		$request = new \Chevron\Requests\BaseRequest;
		$result  = $request->parse($url);

		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$expected = array(
			"scheme"           => "",
			"host"             => "",
			"port"             => "",
			"path"             => "/dir/file.html",
			"query"            => "",
			"sub_domain"       => "",
			"domain"           => "",
			"top_level_domain" => "",
			"user"             => "",
			"pass"             => "",
			"query_arr"        => array(),
			"dirname"          => "/dir",
			"basename"         => "file.html",
			"extension"        => "html",
			"filename"         => "file",
			"action"           => "GET",
		);

		foreach($expected as $key => $value){
			$this->assertEquals($info[$key], $value, "Request::parse failed to set the {$key} property");
		}

	}

	public function test_parse_relative_url(){
		$url = "/dir/file.html?q=s&t=f";

		$request = new \Chevron\Requests\BaseRequest;
		$result  = $request->parse($url);

		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$expected = array(
			"scheme"           => "",
			"host"             => "",
			"port"             => "",
			"path"             => "/dir/file.html",
			"query"            => "q=s&t=f",
			"sub_domain"       => "",
			"domain"           => "",
			"top_level_domain" => "",
			"user"             => "",
			"pass"             => "",
			"query_arr"        => array(
				"q" => "s",
				"t" => "f",
			),
			"dirname"          => "/dir",
			"basename"         => "file.html",
			"extension"        => "html",
			"filename"         => "file",
			"action"           => "GET",
		);

		foreach($expected as $key => $value){
			$this->assertEquals($info[$key], $value, "Request::parse failed to set the {$key} property");
		}

	}

	public function get_seed_request_absolute(){
		$url = "http://local.testing.com/dir/file.html?qry=str&snow=white";

		$request = new \Chevron\Requests\BaseRequest;
		$result  = $request->parse($url);
		return $request;

	}

	public function get_seed_request_relative(){
		$url = "/dir/file.html?qry=str&snow=white";

		$request = new \Chevron\Requests\BaseRequest;
		$result  = $request->parse($url);
		return $request;

	}

	/**
	 * @depends test_parse_absolute_url
	 */
	public function test_build_absolute(){
		$request = $this->get_seed_request_absolute();

		$result = $request->build();
		$url = "http://local.testing.com/dir/file.html?qry=str&snow=white";

		$this->assertEquals($url, $result, "Request::build failed to build the currect request");

	}

	/**
	 * @depends test_parse_relative_url
	 */
	public function test_bulid_relative(){
		$request = $this->get_seed_request_relative();

		$result = $request->build();
		$url = "/dir/file.html?qry=str&snow=white";

		$this->assertEquals($url, $result, "Request::build failed to build the currect request");

	}

	/**
	 * @depends test_parse_relative_url
	 */
	public function test_alter_request(){
		$request = $this->get_seed_request_relative();

		$changes = array(
			"host" => "Chevron.com",
			"port" => "8080",
		);

		$result = $request->alter_request($changes);
		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$expected = array(
			"scheme"           => "",
			"host"             => "Chevron.com",
			"port"             => "8080",
			"path"             => "/dir/file.html",
			"query"            => "qry=str&snow=white",
			"sub_domain"       => "",
			"domain"           => "Chevron",
			"top_level_domain" => "com",
			"user"             => "",
			"pass"             => "",
			"query_arr"        => array(
				"qry" => "str",
				"snow" => "white",
			),
			"dirname"          => "/dir",
			"basename"         => "file.html",
			"extension"        => "html",
			"filename"         => "file",
			"action"           => "GET",
		);

		foreach($expected as $key => $value){
			$this->assertEquals($info[$key], $value, "Request::parse failed to set the {$key} property");
		}
	}

	/**
	 * @depends test_parse_relative_url
	 */
	public function test_build_altered_request(){
		$request = $this->get_seed_request_relative();

		$changes = array(
			"host" => "Chevron.com",
			"port" => "8080",
		);

		$result = $request->alter_request($changes);
		$result = $request->build();

		$url = "http://Chevron.com:8080/dir/file.html?qry=str&snow=white";

		$this->assertEquals($url, $result, "Request::build failed to build the altered request");

	}

	/**
	 * @depends test_parse_relative_url
	 */
	public function test_build_altered_request_with_auth(){
		$request = $this->get_seed_request_relative();

		$changes = array(
			"host"   => "Chevron.com",
			"port"   => "8080",
			"user"   => "goose",
			"pass"   => "dog",
		);

		$result = $request->alter_request($changes);
		$result = $request->build();

		$url = "http://goose:dog@Chevron.com:8080/dir/file.html?qry=str&snow=white";

		$this->assertEquals($url, $result, "Request::build failed to build the altered request with authorization");

	}

	/**
	 * @depends test_build_altered_request
	 */
	public function test_alter_query_preserve(){
		$request = $this->get_seed_request_relative();

		$changes = array(
			"host"   => "Chevron.com",
			"port"   => "8080",
			"user"   => "goose",
			"pass"   => "dog",
			"spaces" => "goose is a dog",
		);

		$result = $request->alter_query($changes);

		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$query       = "qry=str&snow=white&host=Chevron.com&port=8080&user=goose&pass=dog&spaces=goose+is+a+dog";
		$query_arr = array(
			"qry"    => "str",
			"snow"   => "white",
			"host"   => "Chevron.com",
			"port"   => "8080",
			"user"   => "goose",
			"pass"   => "dog",
			"spaces" => "goose is a dog",
		);

		$this->assertEquals($query, $info["query"], "Request::alter_query failed to alter and preserve the query string");
		$this->assertEquals($query_arr, $info["query_arr"], "Request::alter_query failed to alter and preserve the query array");

	}

	/**
	 * @depends test_build_altered_request
	 */
	public function test_alter_query_no_preserve(){
		$request = $this->get_seed_request_relative();

		$changes = array(
			"host"   => "Chevron.com",
			"port"   => "8080",
			"user"   => "goose",
			"pass"   => "dog",
			"spaces" => "goose is a dog",
		);

		$result = $request->alter_query($changes, false);

		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$query       = "host=Chevron.com&port=8080&user=goose&pass=dog&spaces=goose+is+a+dog";
		$query_arr = array(
			"host"   => "Chevron.com",
			"port"   => "8080",
			"user"   => "goose",
			"pass"   => "dog",
			"spaces" => "goose is a dog",
		);

		$this->assertEquals($query, $info["query"], "Request::alter_query failed to alter and not perserve the query string");
		$this->assertEquals($query_arr, $info["query_arr"], "Request::alter_query failed to alter and not preserve the query array");

	}

	/**
	 * @depends test_alter_query_no_preserve
	 */
	public function test_rebuild_absolute(){
		$request = $this->get_seed_request_absolute();

		$changes = array(
			"host"   => "Chevron.com",
			"port"   => "8080",
			"user"   => "goose",
			"pass"   => "dog",
			"spaces" => "goose is a dog",
		);

		$result = $request->rebuild($changes, false);

		$url = "http://local.testing.com/dir/file.html?host=Chevron.com&port=8080&user=goose&pass=dog&spaces=goose+is+a+dog";

		$this->assertEquals($url, $result, "Request::rebuild failed to rebuild the altered request");
	}

	public function test_magic_get(){
		$request = $this->get_seed_request_absolute();

		$scheme  = $request->scheme;
		$domain  = $request->domain;
		$host    = $request->host;
		$dirname = $request->dirname;

		$this->assertEquals("http",              $scheme,  "Request::__get failed to get the scheme property");
		$this->assertEquals("testing",           $domain,  "Request::__get failed to get the domain property");
		$this->assertEquals("local.testing.com", $host,    "Request::__get failed to get the host property");
		$this->assertEquals("/dir",              $dirname, "Request::__get failed to get the dirname property");

	}

	public function test_magic_isset(){
		$request = $this->get_seed_request_absolute();

		$user    = isset($request->user);
		$pass    = isset($request->pass);
		$host    = isset($request->host);
		$dirname = isset($request->dirname);

		$this->assertFalse($user,   "Request::__isset failed to return the correct value");
		$this->assertFalse($pass,   "Request::__isset failed to return the correct value");
		$this->assertTrue($host,    "Request::__isset failed to return the correct value");
		$this->assertTrue($dirname, "Request::__isset failed to return the correct value");

	}

}

