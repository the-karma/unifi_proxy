# UniFi® Controller proxy

Ubiquity UniFi® Controller Proxy for communications between
locally deployed UniFi® Controller and Karma API. It also provides
UniFi®-capatable guest portal interface for smooth
UniFi® Controller integration without edit of UniFi® Controller's
guest portal HTML files.
UniFi® Controller proxy host must be:

1. accessible from guest network via HTTP / HTTPS;
2. accessible from app.karmawifi.ru via HTTP / HTTPS;
3. UniFi® Controller must be accessible from Controller proxy host via HTTPS;
4. SSL certificate on Controller proxy host needs for HTTPS redirection.

## Build and run Controller proxy

      # git clone git@github.com:the-karma/unifi_proxy.git
      # nano settings.php
      # docker build . -t karma-unifi-proxy
      # docker run --rm -p 80:80 karma-unifi-proxy

## Crontab

Periodic job is used to collect APs status and statistic.
Add this to your crontab on Docker host:

      * * * * * docker run --rm karma-unifi-proxy periodic.sh

## Setup UniFi® Controller

* Go to Settings > Guest Control;
* Setup redirection to Controller Proxy:
  * Enable Guest Portal: checked;
  * Authentication: External portal server;
  * Custom Portal: IP Address of Controller Proxy;
  * Setup Redirection:
    * Use Secure Portal: checked if Controller Proxy accessible via HTTPS;
    * Redirect using hostname: hostname of Controller Proxy;
    * Enable HTTPS Redirection: checked if Controller Proxy accessible via HTTPS;
  * Pre-Authorization Access:
    * mc.yandex.ru;
    * app.karmawifi.ru or your custom Karma domain;
    * UniFi® Controller Proxy hostname or IP;
* Setup Wireless Networks:
  * Setup Public Network:
    * Guest Policy: checked;
    * Security: open;
    * UAPSD: checked (for WMM Power save);

## Setup Karma

You need to add each UniFi® Access point by MAC address (for multiple UniFi® Sites proxy option, see `settings.php`).

## Contribute

If you would like to contribute code (improvements), please open an issue and include your code there or else create a pull request.

## Credits

`unifi_api.class.php` is version `1.1.6` of the [Art-of-WiFi/UniFi-API-client](https://github.com/Art-of-WiFi/UniFi-API-client/)
