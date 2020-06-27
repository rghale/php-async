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

	private function process() {
		$content = '';	
        	$level = ob_get_level();
        	for ($idx = 0; $idx < $level; $idx++) {
            		$content .= ob_get_clean();
        	}
    		ob_start();
		
        	// buffer all upcoming output
        	if(!ob_start("ob_gzhandler")){
            		define('NO_GZ_BUFFER', true);
            		ob_start();
        	}
        
        	echo $content;
        
        	//Flush here before getting content length if ob_gzhandler was used.
        	if(!defined('NO_GZ_BUFFER')){
            		ob_end_flush();
        	}
        
        	// get the size of the output
        	$size = ob_get_length();
        
        	// send headers to tell the browser to close the connection
        	header("Content-Length: $size");
        	header('Connection: close');
        
        	// flush all output
        	ob_end_flush();
        	ob_flush();
        	flush();
        	if (session_id())
        		session_write_close();
		}
				
		if ($this->asyncTasks) {
			foreach ($this->asyncTasks as $task) {
				$task->run();
			}
		}
	}
}

runtime::init();
