<?php
require_once dirname(__FILE__).'/../unifi_api.class.php';
require_once dirname(__FILE__).'/../settings.php';

ini_set('display_errors',0);
set_time_limit(0);

define('REQUEST_ID', uniqid());
define('PROXY_LOG_FILE_NAME', dirname(__FILE__).'/../log/proxy.log');

function show_page($u, $captive = true) {
  proxy_log("Page redirect to: ".$u);
  
	require 'page.php';
}

function proxy_log($message) {
  file_put_contents(PROXY_LOG_FILE_NAME, date("Y-m-d H:i:s")." [".REQUEST_ID."] ".$message.PHP_EOL, FILE_APPEND);
}

function redirect_to($location) {
	proxy_log("Redirect to: ".$location);
	header("Location: ".$location);
}

function is_mac($variable) {
  return isset($variable) && preg_match('/^(?:[0-9A-F]{2}[:]?){5}(?:[0-9A-F]{2}?)$/i', $variable);
}

function karma_url($karma, $kind, $ap_mac, $identity) {
  $result = $karma[$kind];
  
  if (substr($result, -1) != "?") {
    $result = $result."&";
  }
  
  if (isset($identity)) {
    $result = $result."identity=".$identity;
  } else {
    $result = $result."ap_mac=".$ap_mac;
  }
  
  return $result;
}

function karma_session($session_url) {
	$json = file_get_contents($session_url);
	$obj = json_decode($json);
	$access = false;
	
	if($obj) {
		$access = $obj->check->access;
		$minutes = isset($obj->check->internet_expires_in) ? $obj->check->internet_expires_in / 60 : 0;
	} else {
		//Show pretty error message and die();
	}
	
	proxy_log("Result: ".print_r($obj, true));
  
  return $access ? ($minutes > 0 ? $minutes : $time_60) : 0;
}

function karma_authorize($unifi, $mac, $session_url, $redirect_url, $login_url) {
  $minutes = karma_session($session_url);
  
  if($minutes > 0) {
		//$unifi->login();				
		$auth_result = $unifi->authorize_guest($mac, $minutes);
    proxy_log("Authorize: ".print_r($auth_result, true));
	} else {				
    $auth_result = false;
	}
  
	if($auth_result) {
		show_page($redirect_url);
	} else {
	  redirect_to($login_url);
	}
}

function karma_unauthorize($unifi, $mac, $session_url, $redirect_url) {
  $minutes = karma_session($session_url);
  
  if($minutes == 0) {
    //$unifi->login();
		$auth_result = $unifi->unauthorize_guest($mac);
    proxy_log("Unauthorize: ".print_r($auth_result, true));
  }
  
  redirect_to($redirect_url);
}

$path = $_SERVER['REQUEST_URI'];
$paths = parse_url($path);
if(isset($paths['path'])) {
	$site = explode('/', $paths['path']);
}
	
if(isset($site[1]) && $site[1] == 'guest' && isset($site[2]) && $site[2] == 's' && isset($site[3])) {
  $unifi = new unifiAPI($unifi_login, $unifi_password, $unifi_url, $site[3]);
  //$unifi->set_debug(true);
  $time_start = microtime(true);
  
  $mac = $_GET['id'];
  $ap_mac = $_GET['ap'];
  
  proxy_log("Started ".$path);
  proxy_log("Params: ".print_r($_GET, true));
    
  if (is_mac($mac) && is_mac($ap_mac)) {
    $redirect_url = karma_url($karma, 'placeURL',   $ap_mac, $identity);
    $login_url    = karma_url($karma, 'loginURL',   '',      $identity)."&unifi_site=".$site[3]."&".$paths['query'];
    $session_url  = karma_url($karma, 'sessionURL', $ap_mac, $identity)."&mac=".$mac;
    
  	if(isset($site[4]) && ($site[4] == 'login' || $site[4] == 'logout')) {
      if (isset($_GET['url']) && !empty($_GET['url'])) {
        $redirect_url = $_GET['url'];
      }
      
      if($site[4] == 'login') {        
        karma_authorize($unifi, $mac, $session_url, $redirect_url, $login_url);
      } else {
        karma_unauthorize($unifi, $mac, $session_url, $redirect_url);
      }
  	} else {
      if(!isset($_GET['process'])) {
        show_page($path . (isset($paths['query']) ? "&" : "?")."process=true", false);
        die();
      }
      
      karma_authorize($unifi, $mac, $session_url, $redirect_url, $login_url);
  	}
  }
  
  $time_end = microtime(true);
  proxy_log("Complete in: ".round($time_end - $time_start, 3));
}
