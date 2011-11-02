<?php

class MONGOBASE_APP extends MONGOBASE {

    /* BASE CLASS FOR THE APPLICATIONS USING MONGOBASE */

    public $modules; // various objects
	public $actions = null;
	public $filters = null;
    public $db; // fundamental object


    public function __construct() {
    }

	public function setup_db() {
        // For now - just create a db instance and register it.
        if (! class_exists('MONGOBASE_DB')) {
            // Could do - a require_once - and assume directory structure...
            trigger_error("MONGOBASE_DB Class Required",E_USER_WARNING);
            die();
        }

        $this->db = new MONGOBASE_DB();
    }


	public function log($message) {
		// TODO: - send to error logs or trigger errors or....
		error_log($message);
	}
	
	public function register_module($module) {
		if (isset($this->modules[$module->name]) && is_object($this->modules[$module->name]))
			trigger_error("MONGOBASE_MODULE ALREADY REGISTERED",E_USER_WARNING);


		$this->modules[$module->name] = $module;
	}


	public function apply_filters($key, $args){
		if(isset($this->filters[$key])) $do = $this->filters[$key];
		else {
			$this -> log('Missing filter: ' . $key);
			return $args;
		}

		if(is_array($do)){
			/* TODO: CANNOT GET GLOBALS TO WORK */
			$module= $do[0]; $method = $do[1];
			if(method_exists($this->modules[$module],$method)) return $this->modules[$module]->$method($args);
		}else{
		
			$function = &$do;
		
			if (function_exists($function)) return $function($args);
			else $this->log('Filter function: '.$function.' not defined ' . $key);
		}

	}

	public function add_action($key, $arg1, $arg2 = null){

		if ($this->actions !== null) $this->actions = array();
		
		if ($arg2 !== null) $this->actions[$key] = array($arg1,$arg2);
		else $this->actions[$key] = $arg1;

	}

	public function add_filter($key, $arg1, $arg2 = null){

		if ($this->filters !== null) $this->filters = array();

		if ($arg2 !== null) $this->filters[$key] = array($arg1,$arg2);
		else $this->filters[$key] = $arg1;

	}


	public function do_action($key, $args = array()) {

		// TODO: USE AN OBJECT METHOD WITH THE ARGS

		if(isset($this->actions[$key])) $do = $this->actions[$key];
		else {
			$this -> log('Missing action: ' . $key); // low priority
			return $args;
		}

		if (is_array($do)) {
			$module= $do[0]; $method = $do[1];
			if (method_exists($this->modules[$module],$method)) return $this->modules[$module]->$method($args);
				// or should we use is_callable?
			else print "\n\nMethod $method undefined\n\n";
		} else {
			$function = &$do;
			if (function_exists($function)) return $function($args);
			else $this->log('Filter function: '.$function.' not defined ' . $key);
		}
	}



}
