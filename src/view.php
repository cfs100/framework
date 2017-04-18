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
		${'@args'} = [];
		foreach (['name', 'params', 'return'] as ${'@arg'}) {
			${'@args'}[${'@arg'}] = ${${'@arg'}};
			unset(${${'@arg'}});
		}

		extract(static::$global);
		extract(${'@args'}['params']);
		if (${'@args'}['return']) {
			ob_start();
		}
		require 'view' . DIRECTORY_SEPARATOR . ${'@args'}['name'] . '.phtml';
		if (${'@args'}['return']) {
			return ob_get_clean();
		}
	}
}
