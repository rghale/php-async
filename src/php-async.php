<?php
namespace saman\core;

class runtimeTask {
	var $funcs;

	function __construct(callable $func) {
		$this->funcs = [];
		$this->funcs['task'] = $func;
	}

	function done(callable $func) {
		$this->funcs['done'] = $func;
		return $this;
	}

	function exception(callable $func) {
		$this->funcs['exception'] = $func;
		return $this;
	}

	function run() {
		try {
			ob_start();
			$this->funcs['task']();
			$content = ob_get_flush();
			if ($this->funcs['done']) {
				$this->funcs['done']($content);
			}
		}
		catch (\Exception $ex) {
			if ($this->funcs['exception']) {
				$this->funcs['exception']($ex);
			}
		}
	}
}

class runtime {
	public static $obj;
	private $asyncTasks;
	private $finalTasks;

	public static function init() {
		self::$obj = new runtime();
		register_shutdown_function(function() {
			runtime::$obj->process();
		});
	}

	public static function callAsync(callable $f) {
		$task = new runtimeTask($f);
		runtime::$obj->asyncTasks[] = &$task;
		return $task;
	}

	public static function callFinal(callable $f) {
		$task = new runtimeTask($f);
		runtime::$obj->finalTasks[] = &$task;
		return $task;
	}

	private function process() {
		if (is_array($this->finalTasks)) {
			foreach ($this->finalTasks as $task) {
				$task->run();
			}
		}

		$setContentType = true;
		$setContentEncoding = true;
		$setContentLength = true;
		foreach (headers_list() as $header) {
			if (stripos($header, 'content-type') !== false) {
				$setContentType = false;
			}
			if (stripos($header, 'content-encoding') !== false) {
				$setContentEncoding = false;
			}
			if (stripos($header, 'content-length') !== false) {
				$setContentLength = false;
			}
		}
		if ($setContentEncoding) {
			header("Content-Encoding: none");
		}
		if ($setContentType) {
			header('Content-Type: text/html');
		}
		if ($setContentLength) {
			header("Content-Length: " . ob_get_length());
		}
		$level = ob_get_level();
		for ($idx = 0; $idx < $level; $idx++) {
			ob_end_flush();
		}
		ob_flush();
		flush();
		session_write_close();

		if ($this->asyncTasks) {
			foreach ($this->asyncTasks as $task) {
				$task->run();
			}
		}
	}
}

runtime::init();
