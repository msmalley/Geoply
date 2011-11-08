<?php

$markers = array();

define('MONGODB_NAME', 'geo');

require_once(dirname(__DIR__).'/mb/classes/mb_base.class.php'); // The one file to rule them all
require_once(dirname(__DIR__).'/mb/classes/mb_db.class.php'); // Required for MongoDB Connections

$db = new MONGOBASE_DB; // Assign the DB

$lat = 3.152864; // These defaults represent KL MUG HQ
$lng = 101.712624; // These defaults represent KL MUG HQ
// More information on KL MUG - http://lauli.ma/klmug

/* LAT / LNG
$tl = array(3.154346,101.708443);
$tr = array(3.155857,101.721784);
$br = array(3.147474,101.723233);
$bl = array(3.146322,101.711817);
*/

/* LNG / LAT */
$tl = array('lng'=>101.708443,'lat'=>3.154346);
$tr = array('lng'=>101.721784,'lat'=>3.155857);
$br = array('lng'=>101.723233,'lat'=>3.147474);
$bl = array('lng'=>101.711817,'lat'=>3.146322);
$near_my_office[] = $tl;
$near_my_office[] = $tr;
$near_my_office[] = $br;
$near_my_office[] = $bl;

$query = array(
	'col'	=> 'geonames',
	'limit'		=> 100,
	'within'	=> $near_my_office
);
$results = $db->find($query);

if((isset($results))&&(is_array($results))){
	foreach($results as $result){
		$marker_info['lat'] = $result['loc']['lat'];
		$marker_info['lng'] = $result['loc']['lng'];
		$marker_info['title'] = $result['name'];
		$marker_info['this_id'] = $db->_id($result['_id']);
		$marker_info['slug'] = '?id='.$db->_id($result['_id']);
		if(count($markers)<1){
			$marker_info['open'] = true;
			$marker_info['content'] = '<p>This example shows the results of a polygon query, where we have currently hard-coded the polygon to be the error surrounding MongoPress HQ. This is querying 7.9+ Million documents and only displaying those within the polygon. We will soon be introducing polygons for each country!</p>';
		}else{
			$marker_info['open'] = false;
			$marker_info['content'] = '<pre>'.print_r($result,true).'</pre>';
		}
		$markers[] = $marker_info;
	}
}

echo json_encode($markers);