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

	public function add($name, $params = [])
	{
		extract(static::$global);
		extract($params);
		require 'view' . DIRECTORY_SEPARATOR . $name . '.phtml';
	}
}
