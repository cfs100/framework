<?php

namespace framework;

class dispatcher
{
	public static $uri;
	public static $routes = [];

	private function __construct()
	{
	}

	public static function run(array $routes = [], $module = null)
	{
		static::$routes = $routes;
		static::$uri = trim(strtok($_SERVER['REQUEST_URI'], '?'), '/');

		$route = static::parseRoute(null, $module);

		$params = array_merge($route['params'], $_GET);

		(new $route['class']($params))->{"{$route['action']}Action"}();
	}

	public static function parseRoute($uri = null, $module = null)
	{
		$route = null;
		$matched = false;
		$matches = [];

		if (is_null($uri)) {
			$uri = static::$uri;
		}

		foreach (static::$routes as $key => $value) {
			$regex = preg_replace('<@(\w+)>', '(?<$1>[\w-]+)', $key);
			if (preg_match("<^$regex$>", $uri, $matches)) {
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
			throw new \InvalidArgumentException("URI Not Found: {$uri}", 404);
		}

		if ($module) {
			$route = "{$module}\\{$route}";
		}

		$aux = explode('->', $route);
		$controller = basename($aux[0]);
		$class = "controller\\{$controller}";
		$action = isset($aux[1]) ? $aux[1] : 'index';

		foreach (array_keys($matches) as $key) {
			if (is_numeric($key)) {
				unset($matches[$key]);
			}
		}

		return [
			'route' => $route,
			'controller' => $controller,
			'class' => $class,
			'action' => $action,
			'params' => $matches,
		];
	}
}
