<?php

namespace Chevron\Requests;

/**
 *
 */
class ResponseHeaders {

	const HEADER_STATUS_CODE = 102;

	/**
	 * @var array
	 */
	protected $headers = array();

	protected $status_codes = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		420 => 'Enhance Your Calm',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		449 => 'Retry With',
		450 => 'Blocked by Windows Parental Controls',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended'
	);

	/**
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Method to generate the correct content-type header for the response
	 *
	 * @param string $extension The type to retrieve
	 * @return string
	 */
	public function detectContentTypeByExtension( $extension ) {
		$extension = strtolower($extension);
		$value     = "text/html";

		switch( trim($extension, " .") ) {
			case "json" :
				$value = "application/json";
				break;
			case "xml" :
				$value = "application/xml";
				break;
			case "txt" :
				$value = "text/plain";
				break;
		}

		return $this->setHeader('Content-Type', $value);
	}

	/**
	 * @todo array values, separated with semicolons and comas dependent on their depth
	 *
	 * @param string $key
	 * @param string $value
	 * @return string The Composed Header
	 */
	public function setHeader( $key, $value ) {
		$this->headers[$key] = $value;

		return $this->composeHeader($key, $value);
	}

	/**
	 * @param $key
	 * @param $value
	 * @return string
	 */
	protected function composeHeader( $key, $value ) {
		$key = strval($key);
		if( is_numeric($key) ) {
			return $value;
		}

		return "{$key}: {$value}";
	}

	/**
	 * @param callable $callback
	 * @param bool     $extra
	 */
	public function eachHeader( callable $callback, $extra = false ) {
		foreach( $this->headers as $key => $value ) {
			$header = $this->composeHeader($key, $value);

			if( !$extra ) {
				$callback($header);
			} else {
				$callback($header, $key, $value);
			}
		}
	}

	/**
	 * @param string $url
	 * @param int    $statusCode
	 * @throws \Exception
	 */
	public function setRedirect( $url, $statusCode = 302 ) {
		if( intval($statusCode / 100) != 3 ) {
			throw new \Exception("{$statusCode} is not a valid redirect");
		}

		$this->setStatusCode($statusCode);
		$this->setHeader('Location', $url);
	}

	/**
	 * method to to generate the correct HTTP header for the response
	 *
	 * @param int $statusCode The status code to retrieve
	 * @return string
	 * @throws \Exception
	 */
	public function setStatusCode( $statusCode ) {

		if( !isset($this->status_codes[$statusCode]) ) {
			throw new \Exception("Unknown Status Code {$statusCode}", $statusCode);
		}

		$header = "HTTP/1.1 {$statusCode} " . $this->status_codes[$statusCode];
		$this->setHeader(static::HEADER_STATUS_CODE, $header);

		return $header;
	}

}