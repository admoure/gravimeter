#!/bin/bash

#####################################
###########instalador################
#####################################

mkdir /home/pi/soft_gravimetro/
mkdir /home/pi/soft_meteo/
cp ./soft_gravimetro/*.* /home/pi/soft_gravimetro
cp ./soft_meteo/*.* /home/pi/soft_meteo
cd /home/pi/soft_gravimetro
chmod 777 /home/pi/soft_gravimetro/*.*
gcc -o main main.c ad7706.c -l bcm2835
cd -
cd /home/pi/soft_meteo
chmod 777 /home/pi/soft_meteo/*.*
gcc -o meteo meteo.c -l wiringPi
cd -
cp ./www/*.* /var/www/
chmod 777 /var/www/*.*
echo "www-data ALL=(root) NOPASSWD: /var/www/gravi_graph_local.sh" > ./gravi
echo "www-data ALL=(root) NOPASSWD: /var/www/meteo_graph_local.sh" >> ./gravi
chmod 0440 gravi
mv gravi /etc/sudoers.d
crontab ./varios/cron.txt


