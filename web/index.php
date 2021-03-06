<?php

class Bootstrap {

	/**
	 * $class
	 * @var string
	 */
	protected $class;

	/**
	 * $method
	 * @var string
	 */
	protected $method;

	/**
	 * $params
	 * @var array
	 */
	protected $params = array();

	/**
	 * Parse current Url Extract Class, Method AND Parameters
	 * @return array Returns array containg class name, method name and set of parameters
	 */
	protected function parseUrl() {
		if (isset($_GET['url'])) {
			return $url = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
		}
	}

  /**
   * Return Not found Json Response in case if class or methos wasn't found in application
   * @return Json Not found Json Response
   */
  protected function accessNotAllowed() {
    header('Content-Type: application/json');
    echo json_encode(array('code' => 403, 'success' => false, 'msg' => "No direct script allowed.", 'data' => array()));
    die;
  }

	/**
	 * Return Not found Json Response in case if class or methos wasn't found in application
	 * @return Json Not found Json Response
	 */
	protected function invalidRequest() {
		header('Content-Type: application/json');
		echo json_encode(array('code' => 404, 'success' => false, 'msg' => 'Invalid Url', 'data' => array()));
		die;
	}

	public function __construct() {

		require __DIR__ . '/../vendor/autoload.php';
		$config = config('app');
		if (@$config['debug'] === false) {
			ini_set('display_errors', 0);
			error_reporting(0);
		}

		if (@$config['log_requests'] === true) {
			app_log($_REQUEST, 'REQUEST');
		}

		$url = $this->parseUrl();
		$classFile = __DIR__ . '/../app/' . ucfirst($url[0]) . '.php';
		if (file_exists($classFile)) {
			$this->class = ucfirst($url[0]);
			unset($url[0]);
		} else {
			return $this->invalidRequest();
		}

    if ($this->class == "Async") {
      if($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR'] || $_SERVER['HTTP_USER_AGENT'] != 'Rest-API')
      {
        return $this->accessNotAllowed();
        die;
      }
    }

		$className = "\\App\\" . $this->class;

		$this->class = new $className;

		if (isset($url[1])) {
			if (method_exists($this->class, $url[1])) {
				$this->method = $url[1];
				unset($url[1]);
			} else {
				return $this->invalidRequest();
			}
		} else {
			return $this->invalidRequest();
		}

		$this->params = $url ? array_values($url) : array();

		call_user_func_array(array(
			$this->class,
			$this->method,
		), $this->params);

	}
}

new Bootstrap;
