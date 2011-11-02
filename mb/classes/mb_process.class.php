<?php

class MONGOBASE_PROCESS extends MONGOBASE_MODULE {

	public $DATA = null;
	private $got_db = false;
	private $process_options = false;

	function __construct($name=null,$app=null,$args=null){
		parent::__construct($name,$app);
		$this->process_options = $args;
		if(class_exists('MONGOBASE_DB')) $this->got_db = true;
        return $this->got_db;
	}

	public function process($data){

		if(!$this->got_db) return $this->__('MONGOBASE_DB Class Required');

		$defaults = array(
			'col'		=> 'mbsert',
			'id'		=> false,
			'delete'	=> false
		);
		$settings = $this->settings($this->process_options,$defaults);

		$results = null;
		$data_array = array(
			'col'		=> $settings['col'],
			'obj'		=> $data,
			'id'		=> $settings['id'],
			'action'	=> false
		);

		if(($settings['delete']===true) && ($settings['id']) && ($settings['col'])){
			$results = $this->app->db->delete($data_array);
			$data_array['action'] = $this->__('delete');
		}elseif($settings['col']){
			$results = $this->app->db->mbsert($data_array);
			if($settings['id']) $data_array['action'] = $this->__('edit');
			else {
				$data_array['action'] = $this->__('add');
				$data_array['id'] = $results;
			}
		}else{
			$results = $this->__('Collection Name Required');
			$data_array['action'] = $this->__('error');
		}

		$filtered_results = $this->apply_filters('process_results', $results);

		$this->DATA = $data_array;
		return $filtered_results;
	}

	public function options(){

		if (isset($this->options) && ! empty($this->options)) return $this->options;

		$this->do_action('process_options',$this);

		return $this->options;

	}

}
