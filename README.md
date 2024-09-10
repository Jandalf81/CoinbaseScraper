# CoinbaseScraper
Gets the currencies from your wallet and save them to SQLite. View them with a web frontend

This is how to use this with a Pi-Hole installation on a Raspberry Pi

## Copy the files
Copy the files with the following command

```
git clone https://github.com/Jandalf81/CoinbaseScraper.git
```

## Installation
Create a new mount point

```
sudo mkdir /var/www/html/CoinbaseScraper
```

Mount the HTML automatically on each reboot
```
sudo crontab -e
```
then add the following line
```
@reboot sudo mount --bind /home/pi/CoinbaseScraper/HTML /var/www/html/CoinbaseScraper
```

Change permissions of the home folder, so lighthttpd can read it
```
chmod 755 /home/pi
```

Enable some needed modules with lighthttpd

```
sudo lighty-enable-mod fastcgi
sudo lighty-enable-mod fastcgi-php
```

Restart lighthttpd

```
sudo systemctl restart lighttpd
```

## API key generation

Create a new API key here: https://www.coinbase.com/settings/api

It need to have the following permission

```
wallet:accounts:read
wallet:user:read
```

## Configuration

Rename `config.ini.DEFAULT` to `config.ini`

Open the file

```
nano /home/pi/CoinbaseScraper/Python/config.ini
```

Enter your API key, token and desired FIA currency, then save the file

## Creating a cron job
Open Crontab

```
crontab -e
```

Enter the following line to execute the script every 5 minutes

```
*/5 * * * * python /home/pi/CoinbaseScraper/Python/script.py
```

Save and exit the file