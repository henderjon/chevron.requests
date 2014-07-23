<?php

class CurrentRequestTest extends PHPUnit_Framework_TestCase {

	public function test_current_request_type(){

		// CurrentRequest is dependent on the $_SERVER array set in the phpunit XML manifest
		$request = new \Chevron\Requests\CurrentRequest(false);

		$this->assertInstanceOf("Chevron\Requests\BaseRequest", $request, "CurrentRequest::__construct failed to return an object of the proper type");

	}

	/**
	 * @depends test_current_request_type
	 */
	public function test_current_request_structure(){

		// CurrentRequest is dependent on the $_SERVER array set in the phpunit XML manifest
		$request = new \Chevron\Requests\CurrentRequest(false);
		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("info");
		$property->setAccessible(true);
		$info = $property->getValue($request);

		$expected = array(
			"scheme"           => "http",
			"host"             => "local.chevron.com",
			"port"             => "80",
			"path"             => "/local/file/index.html",
			"query"            => "a=b&c=d",
			"sub_domain"       => "local",
			"domain"           => "chevron",
			"top_level_domain" => "com",
			"user"             => "",
			"pass"             => "",
			"query_arr"        => array(
				"a" => "b",
				"c" => "d",
			),
			"dirname"          => "/local/file",
			"basename"         => "index.html",
			"extension"        => "html",
			"filename"         => "index",
			"action"           => "GET",
		);

		foreach($expected as $key => $value){
			$this->assertEquals($info[$key], $value, "CurrentRequest::__construct failed to set the {$key} property");
		}

	}

	public function test_pwd(){
		$request = new \Chevron\Requests\CurrentRequest(false);

		$original = $request->build();

		$result = $request->pwd("new_file.html");

		$url = "/local/file/new_file.html?a=b&c=d";

		$this->assertEquals($result, $url, "CurrentRequest::pwd failed to rebuild the altered request");
		$this->assertNotEquals($result, $original, "CurrentRequest::pwd failed to change the original request");
	}

	public function test_is_post(){
		$request = new \Chevron\Requests\CurrentRequest(false);

		$result = $request->is_post();

		$this->assertFalse($result, "CurrentRequest::is_post failed to report the correct action");
	}

}