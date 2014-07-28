#!/bin/bash
ANIO=`date +%02y`
MES=`date +%02m`
IP='88.2.245.55'
BASE='/media/CAM/datalogger'
#la siguiente linea lista todos los txt del directorio y crea un script para el lftp que envia todos los ficheros de texto qeu encuentre
ls -lt $BASE/$ANIO/$MES/*.txt | awk 'BEGIN{printf "debug -o debug.txt\n" > "'$BASE'/ftp_script.ftp"; printf "open '$IP'\n" >> "'$BASE'/ftp_script.ftp"; printf "user ign_grav_izana \"svv-teide\"\n" > "'$BASE'/ftp_script.ftp"}{printf "put %s\n",$NF >> "'$BASE'/ftp_script.ftp" } END{printf "bye\n" >> "'$BASE'/ftp_script.ftp"}' | bash
chmod 777 $BASE/ftp_script.ftp
lftp -f $BASE/ftp_script.ftp
#la siguiente linea lista todos los txt del directorio excepto el que se esta utilizando actualmente y los cambia de carpeta (original)
ls -lt $BASE/$ANIO/$MES/*.txt | awk 'BEGIN { FS="/";variable=0;printf "mkdir '$BASE'/'$ANIO'/'$MES'/original\n" }{if (variable!=0) printf "mv '$BASE'/'$ANIO'/'$MES'/%s '$BASE'/'$ANIO'/'$MES'/original/%s\n",$NF,$NF}{variable++}' | bash



