# WebTSS
An open-source web alternative to tsssaver.

## Installation 
1. Drag and drop this projects files into a web directory.
2. Allow write access to the "tss" folder.
3. Configure "cron.php" to run on a schedule. (On linux you can use crontab -e)
4. Import "webtss.sql" into a mysql server.
5. Configure database information in config.php.
6. Verify that the owner and group of the project files are www-data or whatever your web server belongs to.

## Requirements
- PHP 5+
- MySQL server
- PHP MySQLi extension
- Access to a cron/trigger for cron.php
- Preferrably linux

## Features
- Open source under MIT.
- Add devices and find already added devices.
- Fully automated.
- New ECIDs get blobs instantly.
- 17 different bootstrap themes.
- Easily configurable.
- Recaptcha supported.
- Verifies ECID structure somewhat.

## Preview
![Image preview](http://i.imgur.com/ssHPknd.png)
