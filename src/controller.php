<?php

namespace framework;

abstract class controller
{
	public $params = [];
	protected $debugger;

	public function __construct(array $params = [])
	{
		$this->params = $params;

		if (class_exists('\\debugger\\instance')) {
			$this->debugger = new \debugger\instance(static::class);
			$this->debugger->log('Instantiating new controller');
		}

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
		$data = [];
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
