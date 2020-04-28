<?php
require_once dirname(__FILE__).'/../unifi_api.class.php';
require_once dirname(__FILE__).'/../settings.php';

set_time_limit(0);

function periodic_log($message) {
  echo(date("Y-m-d H:i:s")." :: ".$message.PHP_EOL);
}
  
$unifi = new unifiAPI($unifi_login, $unifi_password, $unifi_url);
//$unifi->set_debug(true);

$result = array();

foreach ($unifi->list_sites() as $site) {  
  periodic_log("Process site ".$site->desc." [".$site->name."]");
  
  if (isset($site_name) && $site->name != $site_name) {
    periodic_log("  Skip");
    
    continue;
  }
  
  $unifi->site = $site->name;
  
  foreach ($unifi->list_devices() as $device) {
    if ($device->type != 'uap') {
      continue;
    }
    
    periodic_log("  Device ".$device->mac.". State: ".$device->state);
    
    if ($device->state == 1) {
      $result[$device->mac] = array('hotspot' => 1, 'users' => 0, 'active_users' => 0);
    }
  }
    
  foreach ($unifi->list_clients() as $client) {
    if (!$client->is_guest) {
      continue;
    }
        
    periodic_log("  Client ".$client->mac." @ ".$client->ap_mac.". Authorized: ".$client->authorized);
    
    if (isset($result[$client->ap_mac])) {
      $result[$client->ap_mac]['users']++;
      
      if ($client->authorized) {
        $result[$client->ap_mac]['active_users']++;
      }
    }
  }
  
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

curl_setopt($ch, CURLOPT_URL, $karma['periodicURL']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('identity' => $identity, 'periodic' => $result)));

if (($content = curl_exec($ch)) === false) {
    periodic_log('cURL error: '.curl_error($ch));
}

periodic_log("Answer: ".$content);

curl_close ($ch);