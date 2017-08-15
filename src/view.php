<?php

namespace framework;

class view
{
	private $path;
	public static $global = [];
	protected $debugger;

	public function __construct($name)
	{
		$this->path = str_replace('\\', DIRECTORY_SEPARATOR, $name);

		if (class_exists('\\debugger\\instance')) {
			$this->debugger = new \debugger\instance(static::class);
		}
	}

	public function render($params = [])
	{
		$this->add($this->path, $params);
	}

	public function add($name, $params = [], $return = false)
	{
		if ($this->debugger) {
			$this->debugger->log("Rendering view \"{$name}\" to " . ($return ? 'return' : 'output immediately'));
		}

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
