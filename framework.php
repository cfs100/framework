<?php

namespace framework;

class dispatcher
{
	public static $uri;

	private function __construct()
	{
	}

	public static function run(array $routes = array())
	{
		static::$uri = trim(strtok($_SERVER['REQUEST_URI'], '?'), '/');
		$route = null;
		$matched = false;
		$matches = array();

		foreach ($routes as $key => $value) {
			$regex = preg_replace('<@(\w+)>', '(?<$1>[\w-]+)', $key);
			if (preg_match("<^$regex$>", static::$uri, $matches)) {
				$matched = true;
				break;
			}
		}

		if ($matched) {
			$route = preg_replace_callback('|@([\w\d-]+)|',
				function ($keys) use (&$matches) {
					return isset($matches[$keys[1]]) ? $matches[$keys[1]] : false;
				},
				$value
			);
		} else {
			$route = 'error->_404';
		}

		$aux = explode('->', $route);
		$class = 'controller\\' . basename($aux[0]);
		$action = isset($aux[1]) ? $aux[1] : 'index';

		foreach (array_keys($matches) as $key) {
			if (is_numeric($key)) {
				unset($matches[$key]);
			}
		}
		$params = array_merge($matches, $_GET);

		(new $class($params))->{"{$action}Action"}();
	}
}

abstract class controller
{
	public $params = array();

	public function __construct(array $params = array())
	{
		$this->params = $params;

		if (method_exists($this, 'initialize')) {
			$this->initialize();
		}
	}

	public function render($name, array $data = array(), $layout = true)
	{
		if ($layout) {
			ob_start();
		}

		$view = new view(preg_replace('<^controller\\\\>', '', get_class($this)) . "\\$name");
		$view->render($data);

		if ($layout) {
			$this->layout(ob_get_clean(), is_string($layout) ? $layout : null);
		}
	}

	private function layout($view, $name = null)
	{
		$data = array();
		if (method_exists($this, 'layoutData')) {
			$aux = $this->layoutData();
			if (is_array($aux)) {
				$data = $aux;
			}
		}

		$data['view'] = $view;

		$parts = explode(DIRECTORY_SEPARATOR, $name);
		$name = array_pop($parts);
		$path = implode(DIRECTORY_SEPARATOR, $parts);

		$layout = new view(($path ? $path . DIRECTORY_SEPARATOR : null) . 'layout' . ($name ? ".$name" : null));
		$layout->render($data);
	}
}

class view
{
	private $path;
	public static $global = array();

	public function __construct($name)
	{
		$this->path = str_replace('\\', DIRECTORY_SEPARATOR, $name);
	}

	public function render($params = array())
	{
		$this->add($this->path, $params);
	}

	public function add($name, $params = array())
	{
		extract(static::$global);
		extract($params);
		require 'view' . DIRECTORY_SEPARATOR . $name . '.phtml';
	}
}

define('BASE_PATH', $base = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..'));
set_include_path(get_include_path() . ":.:$base/application:$base/library");

spl_autoload_register(function($class) {
	$filename = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
	require $filename;
});
