<?php

/*
0) Used techniques from http://php.net/manual/en/splfileobject.fgetcsv.php
1) Download geonames from http://download.geonames.org/export/dump/allCountries.zip
2) Edit function options at line 411 and 420
3) Importing allCountries.txt will only insert 3.8 million rows if using 32bit mongodb version or exceed 2G of data
4) However, it works perfectly on mongodb 64bit 1GB ram
*/

set_time_limit(0);

class geonames {
	private $conn = null;
	private $_test = false;
	private $dbname = null;
	private $table = null;

	public function __construct($database = null, $test = false) {
		$this->_test = $test;
		if (is_array($database) == true) {
			if (!empty($database['user'])) {
				$this->conn = new Mongo("mongodb://{$database['user']}:{$database['pass']}@{$database['host']}"); // connect
			} else {
				$this->conn = new Mongo(); // connect
			}
			$db = $this->conn->selectDB($database['dbname']);
			$this->dbname = $database['dbname'];
		} else {
			print "No database connection";
		}
	}

	/* import function will read the text file */
	public function import($path, $type) {
		$file = new SplFileObject($path);

		/* set file flags */
		$file->setFlags(SplFileObject::DROP_NEW_LINE);

		/* is in test mode. this only for me to debugging */
		if ($this->_test == true) {
			$test = 0;
		}

		while ($file->valid()) {

			/* is in test mood ? */
			if ($this->_test == true) {
				if ($test++ == 100) {
					break;
				};
			}

			/* get line from file */
			$line = $file->fgets();

			/* split line value */
			$line = explode("\t", $line);

			/*
			insert to database by type
			do we need all file from geonames website??
			http://download.geonames.org/export/dump/
			*/
			switch ($type) {
				case 'admin1_codes':
					if (count($line) == 2) {
						list(
							$admin1_code,
							$admin1_name
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("admin1_codes", array(
							"admin1_code" => $admin1_code,
							"admin1_name" => $admin1_name
						), true, false);
					}
					break;

				case 'admin1_codes_ascii':
					if (count($line) == 4) {
						list(
							$admin1_code,
							$admin1_name,
							$admin1_name_ascii,
							$geonameid
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("admin1_codes_ascii", array(
							"admin1_code" => $admin1_code,
							"admin1_name" => $admin1_name,
							"admin1_name_ascii" => $admin1_name_ascii,
							"geonameid" => $geonameid
						), true, false);
					}
					break;

				case 'admin2_codes':
					if (count($line) == 4) {
						list(
							$admin2_code,
							$admin2_name,
							$admin2_name_ascii,
							$geonameid
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("admin2_codes", array(
							"admin2_code" => $admin2_code,
							"admin2_name" => $admin2_name,
							"admin2_name_ascii" => $admin2_name_ascii,
							"geonameid" => $geonameid
						), true, false);
					}
					break;

				case 'alternate_names':
					if (count($line) == 6) {
						list(
							$alternatenameid,
							$geonameid,
							$isolanguage,
							$alternate_name,
							$ispreferredname,
							$isshortname
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("alternate_names", array(
							"alternatenameid" => $alternatenameid,
							"geonameid" => $geonameid,
							"isolanguage" => $isolanguage,
							"alternate_name" => $alternate_name,
							"ispreferredname" => $ispreferredname,
							"isshortname" => $isshortname
						), true, false);
					}
					break;

				case 'country_info':
					if (count($line) == 19) {
						list(
							$iso,
							$iso3,
							$iso_numeric,
							$fips,
							$country,
							$capital,
							$area_in_sqkm,
							$population,
							$continent,
							$tld,
							$currencycode,
							$currencyname,
							$phone,
							$postal_code_format,
							$postal_code_regex,
							$languages,
							$geonameid,
							$neighbours,
							$equivalentfipscode
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("country_info", array(
							"iso" => $iso,
							"iso3" => $iso3,
							"iso_numeric" => $iso_numeric,
							"fips" => $fips,
							"country" => $country,
							"capital" => $capital,
							"area_in_sqkm" => $area_in_sqkm,
							"population" => $population,
							"continent" => $continent,
							"tld" => $tld,
							"currencycode" => $currencycode,
							"currencyname" => $currencyname,
							"phone" => $phone,
							"postal_code_format" => $postal_code_format,
							"postal_code_regex" => $postal_code_regex,
							"languages" => $languages,
							"geonameid" => $geonameid,
							"neighbours" => $neighbours,
							"equivalentfipscode" => $equivalentfipscode
						), true, false);
					}
					break;

				case 'feature_codes':
					if (count($line) == 3) {
						list(
							$feature_code,
							$feature_name,
							$feature_desc
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("feature_codes", array(
							"feature_code" => $feature_code,
							"feature_name" => $feature_name,
							"feature_desc" => $feature_desc
						), true, false);
					}
					break;

				case 'geonames':
					if (count($line) == 19) {
						list(
							$geonameid,
							$name,
							$asciiname,
							$alternatenames,
							$latitude,
							$longitude,
							$feature_class,
							$feature_code,
							$country_code,
							$cc2,
							$admin1_code,
							$admin2_code,
							$admin3_code,
							$admin4_code,
							$population,
							$elevation,
							$gtopo30,
							$timezone,
							$modification_date
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("geonames", array(
							"geonameid" => $geonameid,
							"name" => $name,
							"asciiname" => $asciiname,
							"alternatenames" => $alternatenames,
							"latitude" => $latitude,
							"longitude" => $longitude,
							"loc" => array('lng' => floatval($longitude), 'lat' => floatval($latitude)), //use for geo spatial indexing
							"feature_class" => $feature_class,
							"feature_code" => $feature_code,
							"country_code" => $country_code,
							"cc2" => $cc2,
							"admin1_code" => $admin1_code,
							"admin2_code" => $admin2_code,
							"admin3_code" => $admin3_code,
							"admin4_code" => $admin4_code ,
							"population" => $population,
							"elevation" => $elevation,
							"gtopo30" => $gtopo30,
							"timezone" => $timezone,
							"modification_date" => $modification_date
						), true, false);
						$this->table = "geonames"; //use only when doing geo-spatial indexing
					}
					break;

				case 'hierarchy':
					switch (count($line)) {
						case 2:
							list(
								$parentid,
								$childid
							) = $line;

							/* prepare generate statement and execute */
							$this->mongo_insert("hierarchy", array(
								"parentid", $parentid,
								"childid", $childid
							), true, false);
							break;

						case 3:
							list(
								$parentid,
								$childid,
								$type
							) = $line;

							/* prepare generate statement and execute */
							$this->mongo_insert("hierarchy", array(
								"parentid" => $parentid,
								"childid" => $childid,
								"type" => $type
							), true, false);
							break;
					}
					break;

				case 'iso_languages':
					if (count($line) == 4) {
						list(
							$iso_639_3,
							$iso_639_2,
							$iso_639_1,
							$language_name
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("iso_languages", array(
							"iso_639_3" => $iso_639_3,
							"iso_639_2" => $iso_639_2,
							"iso_639_1" => $iso_639_1,
							"language_name" => $language_name
						), true, false);
					}
					break;

				case 'postal_codes':
					if (count($line) == 12) {
						list(
							$country_code,
							$postal_code,
							$place_name,
							$admin_name1,
							$admin_code1,
							$admin_name2,
							$admin_code2,
							$admin_name3,
							$admin_code3,
							$latitude,
							$longitude,
							$accuracy
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("postal_codes", array(
							"country_code" => $country_code,
							"postal_code" => $postal_code,
							"place_name" => $place_name,
							"admin_name1" => $admin_name1,
							"admin_code1" => $admin_code1,
							"admin_name2" => $admin_name2,
							"admin_code2" => $admin_code2,
							"admin_name3" => $admin_name3,
							"admin_code3" => $admin_code3,
							"latitude" => $latitude,
							"longitude" => $longitude,
							"accuracy" => $accuracy
						), true, false);
					}
					break;

				case 'timezones':
					if (count($line) == 3) {
						list(
							$timezoneid,
							$gmt_offset,
							$dst_offset
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("timezones", array(
							"timezoneid" => $timezoneid,
							"gmt_offset" => $gmt_offset,
							"dst_offset" => $dst_offset
						), true, false);
					}
					break;

				case 'usertags':
					if (count($line) == 2) {
						list(
							$geonameid,
							$tag
						) = $line;

						/* prepare generate statement and execute */
						$this->mongo_insert("usertags", array(
							"geonameid" => $geonameid,
							"tag" => $tag
						), true, false);
					}
					break;
			}
		} //end while loop

		//run geo-spatial index here
		if ($type == 'geonames') {
			$collection = $this->conn->selectDB($this->dbname)->selectCollection($this->table);
			$collection->ensureIndex(array("loc" => "2d"), array("min" => floatval(-180), "max" => floatval(181))); //fixed error point not in interval of [-180, 180]
		}
	} //end function import

	public function modify($type, $file) {
		//we need update on future?
	}

	public function delete($file) {
		//we need to delete?
	}

	private function mongo_insert($table, $columns = null, $execute = false, $return = false) {
		$collection = $this->conn->selectDB($this->dbname)->selectCollection($table);
		//var_dump($columns);exit;

		//insert data
		if (!is_null($columns)) {
			$collection->insert($columns);
		} else {
			trigger_error("No column is defined in arguments");
		}
	}

} //end class


//start import script
$geonames = new geonames(array(
	'host' => '127.0.0.1',
	'port' => '27017',
	'dbname' => 'geo',
	'user' => '',
	'pass' => ''
), false);

/* Import */
$geonames->import("allCountries.txt", "geonames");

/* example for other geonames txt files
$geonames->import("admin1Codes.txt", "admin1_codes");
$geonames->import("admin1CodesASCII.txt", "admin1_codes_ascii");
$geonames->import("admin2Codes.txt", "admin2_codes");
$geonames->import("alternateNames.txt", "alternate_names");
$geonames->import("countryInfo.txt", "country_info");
$geonames->import("featureCodes_en.txt", "feature_codes");
$geonames->import("geonames.txt", "geonames");
$geonames->import("geonames-no-country.txt", "geonames");
$geonames->import("hierarchy.txt", "hierarchy");
$geonames->import("iso-languagecodes.txt", "iso_languages");
$geonames->import("postalCodes.txt", "postal_codes");
$geonames->import("timeZones.txt", "timezones");
$geonames->import("userTags.txt", "usertags");
*/
?>