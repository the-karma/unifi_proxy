<?php
  $time_60 = 1200; // default value for session
  
  $karma_host  = "https://app.karmawifi.ru";
  $karma_login = "example@the-karma.com";
  $karma_token = "get_it_from_support";
  
  $karma = array('sessionURL'  => $karma_host."/api/auth/session/check.json?token=".$karma_token."&email=".$karma_login,
                 'periodicURL' => $karma_host."/api/office/access_points/periodic.json?token=".$karma_token."&email=".$karma_login,
                 'loginURL'    => $karma_host."/hotspot/login?",
                 'placeURL'    => $karma_host."/places/router?");

  $unifi_login = "unifi";
  $unifi_password = "unifi";
  $unifi_url = "https://unifi.controller:8443";
  
  $identity = null; // For multiple UniFi Sites
  //$identity = 'karma_router_identity'; // For single UniFi Site
  //$site_name = 'your-site' // For correct periodic on single UniFi Site
?>
