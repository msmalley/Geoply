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
//echo json_encode(count($this_db['values'])); exit;
echo json_encode($this_db); exit;

if(isset($_POST['lat'])) $lat = (float)$_POST['lat'];
else $lat = 3.152864; // These defaults represent KL MUG HQ
if(isset($_POST['lng'])) $lng = (float)$_POST['lng'];
else $lng = 101.712624; // These defaults represent KL MUG HQ
// More information on KL MUG - http://lauli.ma/klmug

$query = array(
	'col'	=> 'geonames',
	'limit'	=> 500,
	'near'	=> array( $lng, $lat )
);
$results = $db->find($query);

if((isset($results))&&(is_array($results))){
	foreach($results as $result){
		$marker_info['lat'] = $result['loc']['lat'];
		$marker_info['lng'] = $result['loc']['lng'];
		$marker_info['title'] = $result['name'];
		$marker_info['content'] = '<pre>'.print_r($result,true).'</pre>';
		$marker_info['this_id'] = $db->_id($result['_id']);
		$marker_info['slug'] = '?id='.$db->_id($result['_id']);
		if(count($markers)<1) $marker_info['open'] = true;
		else $marker_info['open'] = false;
		$markers[] = $marker_info;
	}
}

echo json_encode($markers);