<?php

/* THESE ARE FOR WHEN WE NEED BASIC FUNCTIONS - NOT METHODS - MORE EFFICIENT */
/* ALL FUNCTIONS - ALL PREFIXED WITH mbu_ - mongo_base_utils... */

function mbu_encode_mongo_id($id){
	return mbu_to_base(mbu_to_decimal($id, 16)); // base 16 -> dec -> base 62
}

function mbu_decode_mongo_id($id62){
	return mbu_to_base(mbu_to_decimal($id62),16);
}

function mbu_to_base($num, $b=62){
	$base='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$r = $num  % $b ;
	$res = $base[$r];
	$q = floor($num/$b);
	while ($q) {
		$r = $q % $b;
		$q = floor($q/$b);
		$res = $base[$r].$res;
	}
	return $res;
}

function mbu_to_decimal($num, $b=62){
	$base='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$limit = strlen($num);
	$res=strpos($base,$num[0]);
	for($i=1;$i<$limit;$i++) {
		$res = $b * $res + strpos($base,$num[$i]);
	}
	return $res;
}

function mbu_os(){
	utils_dump(php_uname('s'));
}

function mbu_get_os(){
	$myos = strtolower(php_uname('s'));
	if (strstr($myos,'linux')) return 'linux';
	if (strstr($myos,'windows')) return 'windows';
	// extend for supported os's
	return 'unknown';
}

function mbu_version_compare($required,$value,$checking_for_upgrades=false){
	$req = explode('.',$required);
	$got = explode('.',$value);
	$req_len = count($req);
	$req_count = 1;
	foreach ($req as $k => $v) {
		if (!isset($got[$k]) || (int)$got[$k] < (int)$v) return false;
		elseif ($got[$k] > $v) return true;
		if($req_count==$req_len){
			if($checking_for_upgrades){
				if (!isset($got[$k]) || (int)$got[$k] <= (int)$v) return false;
			}else{
				if (!isset($got[$k]) || (int)$got[$k] < (int)$v) return false;
				else return true;
			}
		}
		$req_count++;
	}
	return true;
}


function mbu_caller_func(){
	$trace=debug_backtrace();
	$caller=$trace[2]; // relative to the calling function
	echo 'called by '.$caller['function'];
	if (isset($caller['class'])) echo 'in '.$caller['class'];
}

function mbu_dump($obj,$verbose=true){
	if (isset($GLOBALS['_CLI']) && $GLOBALS['_CLI'] == true) { print_r($obj); return;}
	if (isset($GLOBALS['_mb_is_ajax']) && $GLOBALS['_mb_is_ajax'] === true) $verbose = false;
	$func = mbu_caller();
	if (is_string($obj)) $obj = htmlentities($obj);
	if ($verbose) print "<pre style='text-align:left;border:1px solid #ddd;background:white;padding:5px;font-size:12px;'>";
	if ($verbose) print "mbu_dump <b>$func</b> in file: " . $_SERVER['PHP_SELF'] . "\n\n";
	print_r($obj);
	if ($verbose) print "</pre>";
}

function mbu_array_str_simple($array){
	if (! is_array($array)) return 'null';
	$str = '';
	foreach ($array as $v) {
		if (is_object($v)) $str .= ' object ('.get_class($v).')';
		else $str .= gettype($v) .':'.(string)$v . ', ';
	}
	return substr($str,0, -2);
}


function mbu_caller(){
	$trace = debug_backtrace();
	if (isset($trace[2])) $caller=$trace[2];
	else $caller = $trace[1]; // relative to the calling function
	$str = 'called by '.$caller['function'];
	$str .= '('.mbu_array_str_simple($caller['args']).')';
	if (isset($caller['class'])) $str .= ' in '.$caller['class'];
	$caller = $trace[1]; // where the call to dump was
	$str .= "\n".' file: '.$caller['file'];
	$str .= ' line: '.$caller['line'];
	return $str;
}
