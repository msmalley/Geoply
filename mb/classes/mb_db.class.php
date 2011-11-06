<?php

class MONGOBASE_DB extends MONGOBASE {

	public $dbh;
	private $mongo;
	public $error;
	private $is_connected = false;

	function __construct($force = false){
		parent::__construct();
		if ($force) $this->is_connected = $this->connect();
	}

	private function connect(){

		if (is_object($this->dbh)) return $this->dbh;
		$options = $this->options();

		try{

			if($options['db_replicas'] === true && $options['db_user'] !=='' ){
				//replica and database need authentication
				$m = new Mongo("mongodb://{$options['db_user']}:{$options['db_pass']}@{$options['db_host']}:{$options['db_port']}", array('replicaSet' => true));
			}elseif($options['db_replicas'] === true){
				//replica set and no auth.
				$m = new Mongo("mongodb://{$options['db_host']}:{$options['db_port']}", array('replicaSet' => true));
			}elseif($options['db_user']!=false){
				////database need authentication
				$m = new Mongo("mongodb://{$options['db_user']}:{$options['db_pass']}@{$options['db_host']}:{$options['db_port']}");
			}else{
				//default without auth and replica
				$m = new Mongo("mongodb://{$options['db_host']}:{$options['db_port']}");
			}

			$this->mongo = $m;
			$this->dbh = $m->$options['db_name'];
			
			return true;

		}catch(MongoConnectionException $e){
			$this->error = $this->__('Error connecting to MongoDB server');
		}catch(MongoException $e) {
			$this->error = $this->__('Error: mongoDB error').$e->getMessage();
		}catch(MongoCursorException $e) {
			$this->error = $this->__('Error: probably username password in config').$e->getMessage();
		}catch(Exception $e) {
			$this->error = $this->__('Error: ').$e->getMessage();
		}
		return false;
	}

	private function command($cmd){
		$results = $this->dbh->command($cmd);
		return $results;
	}

	private function arrayed($these_objs){
		/* takes mongo_db objects and returns them as arrays for php */
		if(is_object($these_objs)){
			$objects = array();
			foreach($these_objs as $this_obj) {
				$this_object = array();
				foreach($this_obj as $key => $value){
					$this_object[$key] = $value;
				} $objects[] = $this_object;
			}
			if(is_array($objects)){
				if(!empty($objects)){
					return $objects;
				}
			}
		}
	}

	public function count($args = false){
		$defaults = array(
			'col'	=> 'mbsert',
			'where' => array()
		);
		$settings = $this->settings($args,$defaults);
		if(!$this->is_connected) $this->connect();
		$dbh = $this->dbh;
		try{

			$collection = $dbh->$settings['col'];
			$results = $collection->find($settings['where'])->count();
			return $results;

		}catch(Exception $e){
			// should be able to do this class wide on the base object
			return $this->__('Error: ').$e->getMessage();
		}
	}

	public function _id($id){
        $mongo_id = '';
        if (isset($id)) {
            if(is_object($id)){
                foreach($id as $key => $value){
                    if($key=='$id'){
                        $mongo_id = $value;
                    }
                }
            }
            return (string)$mongo_id;
        } else {
            return (string)$id;
        }
	}
	
	public function options(){

		if (isset($this->options) && ! empty($this->options)) return $this->options;

		/* ELSE - GATHER CONFIG SETTINGS */
		if(!defined('MONGODB_NAME')) define('MONGODB_NAME', 'mongobase');
		$this->register_configuration_setting('db_name','MONGODB_NAME', MONGODB_NAME);

		if(!defined('MONGODB_HOST')) define('MONGODB_HOST', 'localhost');
		$this->register_configuration_setting('db_host','MONGODB_HOST', MONGODB_HOST);

		if(!defined('MONGODB_USERNAME')) define('MONGODB_USERNAME', false);
		$this->register_configuration_setting('db_user','MONGODB_USERNAME', MONGODB_USERNAME);

		if(!defined('MONGODB_PASSWORD')) define('MONGODB_PASSWORD', false);
		$this->register_configuration_setting('db_pass','MONGODB_PASSWORD', MONGODB_PASSWORD);

		if(!defined('MONGODB_PORT')) define('MONGODB_PORT', '27017');
		$this->register_configuration_setting('db_port','MONGODB_PORT', MONGODB_PORT);

		if(!defined('MONGODB_REPLICAS')) define('MONGODB_REPLICAS', false);
		$this->register_configuration_setting('db_replicas','MONGODB_REPLICAS', MONGODB_REPLICAS);

		return $this->options;
        
    }

	public function mbsert($args = false){

		/* mbsert() allow for intelligent inserting and (or) updating */

		$defaults = array(
			'col'	=> 'mbsert',
			'obj'   => false,
			'id'	=> false
		);
		$settings = $this->settings($args,$defaults);

		if(!$this->is_connected) $this->connect();
		$dbh = $this->dbh;

		try{

			$collection = $dbh->$settings['col'];
			$mongo_id = new MongoID($settings['id']);
			$key = array("_id"=>$mongo_id);
			$data = $settings['obj'];
			$results = $dbh->command( array(
				'findAndModify' => $settings['col'],
				'query' => $key,
				'update' => $data,
				'new' => true,
				'upsert' => true,
				'fields' => array( '_id' => 1 ) // mongoDB returns these values
			) );
			return $this->_id($results['value']['_id']);

		}catch(Exception $e){
			// should be able to do this class wide on the base object
			return $this->__('Error: ').$e->getMessage();
		}
	}

	public function find($args = false){

		$defaults = array(
			'col'		=> 'mbsert',
			'where'		=> array(),
			'regex'		=> false,
			'limit'		=> false,
			'offset'	=> false,
			'order_by'	=> false,
			'order'		=> false,
			'id'		=> false,
			'near'		=> false,
			'distance'	=> 100
		);
		$settings = $this->settings($args,$defaults);

		if($settings['order_by']){
			if ($settings['order']!='desc') $order_value=1; else $order_value=-1;
			$sort_clause = array($order_by=>$order_value);
		}else{
			$sort_clause = array();
		}

		if(!$this->is_connected) $this->connect();
		$dbh = $this->dbh;

		if($settings['regex']!==false) {
			$regex_object = new MongoRegex($settings['regex']);
			$where = $settings['where'];
			$settings['where'] = array(
				$where => $regex_object
			);
		}

		try{

			if(isset($settings['near'])){

				$geo_near_query = array('geoNear'=>$settings['col'],'near'=>$settings['near'],'$spherical'=>true,'$maxDistance'=>$settings['distance']/6378,'num'=>$settings['limit']);
				$geo_results = $dbh->command($geo_near_query);
				foreach($geo_results['results'] as $result){
                    if(is_array($result['obj'])){
                        $temp_geo_results[] = $result['obj'];
                    }
                } $results = $temp_geo_results;
				return $results;

			}elseif(is_array($settings['col'])){

				$combined_array = false;
				$i = 0;

				foreach($settings['col'] as $this_collection){
					$collection = $dbh->$this_collection;
					$results = $this->arrayed($collection->find($settings['where'])->sort($sort_clause)->skip($settings['offset'])->limit($settings['limit']));
					if(isset($results)){
						foreach($results as $result){
							$combined_array[$i] = $result;
							$i++;
						}
					}
				}

				return $combined_array;

			}else{
				
				$collection = $dbh->$settings['col'];
				$results = $this->arrayed($collection->find($settings['where'])->sort($sort_clause)->skip($settings['offset'])->limit($settings['limit']));
				return $results;

			}

		}catch(Exception $e){
			return $this->__('Error: ').$e->getMessage();
		}
	}

	public function delete($args = false) {
		
		$defaults = array(
			'col'		=> 'mbsert',
			'id'		=> false
		);
		$settings = $this->settings($args,$defaults);

		if(!$this->is_connected) $this->connect();
		$dbh = $this->dbh;

		try{

			$collection = $dbh->$settings['col'];
			$criteria = array(
				'_id' => new MongoId($settings['id']),
			);
			$progress = $collection->remove($criteria,array('safe'=>true));
			return $progress['n'];

		}catch(Exception $e){
			return $this->__('Error: ').get_class($e).' : '.$e->getMessage();
		}
	}

	public function distinct($args = false) {

		$defaults = array(
			'col'		=> 'mbsert',
			'where'		=> array(),
			'criteria'	=> false
		);
		$settings = $this->settings($args,$defaults);

		if(!$this->is_connected) $this->connect();
		$dbh = $this->dbh;
		$objects = $dbh->command(array("distinct"=>$settings['col'],'key'=>$settings['criteria'],'query'=>$settings['where']));
		return $objects;

	}

}
