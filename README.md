Install Stuff
```
sudo apt-get install nginx mysql-server php5-curl php5-fpm php5-mysql
```

Init DB
```
CREATE USER 'yourusername'@'localhost' IDENTIFIED BY 'yourpassword';

CREATE DATABASE evedump;
GRANT ALL PRIVILEGES ON evedump.* TO 'yourusername'@'localhost';

CREATE DATABASE eve;
GRANT ALL PRIVILEGES ON eve.* TO 'yourusername'@'localhost';

FLUSH PRIVILEGES;
```

Get EVE Dump
```
https://www.fuzzwork.co.uk/dump/

tar xvjf filename.tar.bz2
mysql -u yourusername -p yourpassword evedump < file.sql
```
