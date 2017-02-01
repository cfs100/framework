<?php

namespace framework;

class view
{
	private $path;
	public static $global = [];

	public function __construct($name)
	{
		$this->path = str_replace('\\', DIRECTORY_SEPARATOR, $name);
	}

	public function render($params = [])
	{
		$this->add($this->path, $params);
	}

	public function add($name, $params = [], $return = false)
	{
		extract(static::$global);
		extract($params);
		if ($return) {
			ob_start();
		}
		require 'view' . DIRECTORY_SEPARATOR . $name . '.phtml';
		if ($return) {
			return ob_get_clean();
		}
	}
}
