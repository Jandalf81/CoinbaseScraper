# CoinbaseScraper
Gets the currencies from your wallet and save them to SQLite. View them with a web frontend

* Create a new mount point ```sudo mkdir /var/www/html/CoinbaseScraper```
* Mount the HTML folder like so ```sudo mount --bind /home/pi/CoinbaseScraper/HTML /var/www/html/CoinbaseScraper```
* Enable some needed modules with lighthttps ```sudo lighty-enable-mod fastcgi
sudo lighty-enable-mod fastcgi-php ```
* Restart lighthttpd ```sudo systemctl restart lighttpd```