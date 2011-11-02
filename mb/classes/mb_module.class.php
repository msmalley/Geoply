<?php
class MONGOBASE_MODULE extends MONGOBASE {

    /* BASE PLUGIN OBJECT FOR MONGOBASE */

	public $name;
	public $app;

    public function __construct($name = 'default_module', $app = null) {

		// Plugin objects need to register with an app - if not - they are being run standalone
		// as yet - when would a module be run standalone and how it should work is undefined
		$this->name = $name;
				
		if ($app !== null && is_object($app)) {
			$app->register_module($this);
			$this->app = $app;	
		}

        parent::__construct(); // then does the options
    }


	public function do_action($key,$args=array()) {
		// pass-through method
		if (isset($this->app)) $this->app->do_action($key,$args);
		else trigger_error('no application registered',E_USER_WARNING);
	}

	public function apply_filters($key,$args=array()) {
		// pass-through method
		if (isset($this->app)) return $this->app->apply_filters($key,$args);
		else trigger_error('no application registered',E_USER_WARNING);
	}


}



?>
