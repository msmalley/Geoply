<?php
/* This module is assumed to be running withing a webserver... well urls wouldn't make sense outside of one? */


class MONGOBASE_URLS extends MONGOBASE_MODULE {
	public $ENV = null;
	private $got_env = false;

	function __construct($name=null, $app=null){
		parent::__construct($name,$app);
		$this->got_env = $this->env();
	}

	public function env($force_refresh = false){

	if(!$force_refresh && $this->ENV!==null) return true;

	$env['DOCUMENT_ROOT'] = dirname($_SERVER['SCRIPT_FILENAME']); // index.php

	if (DIRECTORY_SEPARATOR !== '/') {
		$env['DOCUMENT_ROOT'] = str_replace('\\','/',$env['DOCUMENT_ROOT']);
	}
	$env['MB_CLASSES'] = __DIR__;
	$env['MB_BASE'] = dirname(__DIR__);

	$env['MB_HOME'] = str_replace($_SERVER['DOCUMENT_ROOT'],'',$env['DOCUMENT_ROOT']);
	if (empty($env['MB_HOME'])) $env['MB_HOME'] = '/';
	else $env['MB_HOME'] .= '/';

	if(strstr($_SERVER['SERVER_PROTOCOL'],'HTTPS')) $env['MB_URL'] = 'https://'.$_SERVER['HTTP_HOST'].$env['MB_HOME'];
	$env['MB_URL'] = 'http://'.$_SERVER['HTTP_HOST'].$env['MB_HOME'];

	if($this->is_set($this->options)){
			foreach($this->options as $key => $value){
				$env[$key.'_ROOT'] = $env['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR;
				$env[$key.'_PATH'] = $env['MB_BASE'].DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR;
				$env[$key.'_URL'] = $env['MB_HOME'].$value.'/';
			}
	}
	$env['MB_SLUG'] = $_SERVER['REQUEST_URI'];

	$qs_pos = strpos($env['MB_SLUG'],'?');
	if ($qs_pos !== false) $env['MB_SLUG'] = substr($env['MB_SLUG'],0,$qs_pos);

	$env['MB_SLUG'] = substr($env['MB_SLUG'],strlen($env['MB_HOME']));
		// QS is available in _GET.
		$this->ENV = $env;
		return true;
	}

	public function options(){

		if (isset($this->options) && ! empty($this->options)) return $this->options;
	
		$this->do_action('custom_urls',$this);

		return $this->options;

	}

}
