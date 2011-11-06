<?php

$markers = array();

define('MONGODB_NAME', 'geo');

require_once(dirname(__DIR__).'/mb/classes/mb_base.class.php'); // The one file to rule them all
require_once(dirname(__DIR__).'/mb/classes/mb_db.class.php'); // Required for MongoDB Connections

$db = new MONGOBASE_DB; // Assign the DB

$query = array(
	'col'		=> 'geonames',
	'where'		=> array(
		'country_code'	=> 'MY'
	),
	'criteria'	=> 'feature_code'
);

$this_db = $db->distinct($query);
$distinct_codes = $this_db['values'];
$available_codes = array();

foreach($distinct_codes as $code){
	//echo dirname(__DIR__).'/img/'.$code.'.png'; exit;
	if(file_exists(dirname(__DIR__).'/img/'.$code.'.png')){
		$available_codes[$code] = $code.'.png';
	}
}

if(isset($_POST['lat'])) $lat = (float)$_POST['lat'];
else $lat = 3.152864; // These defaults represent KL MUG HQ
if(isset($_POST['lng'])) $lng = (float)$_POST['lng'];
else $lng = 101.712624; // These defaults represent KL MUG HQ
// More information on KL MUG - http://lauli.ma/klmug

$i = 0;

foreach($available_codes as $code => $icon){

	$query = array(
		'col'	=> 'geonames',
		'where'	=> array(
			'feature_code'	=> $code
		),
		'limit'	=> 100,
		'near'	=> array( $lng, $lat )
	);
	$results = $db->find($query);

	if((isset($results))&&(is_array($results))){
		$this_count = 0;
		foreach($results as $result){
			if($this_count===0){
				if($result['feature_code']==$code){
					$marker_info['lat'] = $result['loc']['lat'];
					$marker_info['lng'] = $result['loc']['lng'];
					$marker_info['title'] = $result['name'];
					$marker_info['this_id'] = $db->_id($result['_id']);
					$marker_info['slug'] = '?id='.$db->_id($result['_id']);
					if(count($markers)<1){
						$marker_info['open'] = true;
						$marker_info['content'] = '<p>This example shows the results of a more complex query, where it will add a marker to the map for one of each of the available icons. More icons derived from the feature_codes will be available soon, but in the meantime, this merely aims to be a proof of concept that you can customise as required.</p>';
					}else{
						$marker_info['open'] = false;
						$marker_info['content'] = '<pre>'.print_r($result,true).'</pre>';
					}
					$marker_info['icon'] = $icon;
					$markers[$i] = $marker_info;
					$this_count++;
				}
			}
		}
	}

	$i++;

}

echo json_encode($markers);