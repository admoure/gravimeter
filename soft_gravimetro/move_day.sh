#!/bin/bash

BASE_USB="/media/CAM/datalogger/gravimetro"
BASE_PI="/home/pi/soft_gravimetro"


ls -lr $BASE_USB/encurso | awk 'BEGIN{counter=0}
			{if (counter > 1){printf ("%s\n",$NF) > "'$BASE_PI'/filestomove.txt"}counter++}'

cat $BASE_PI/filestomove.txt | awk 'BEGIN{}
				{printf("mv '$BASE_USB'/encurso/%s '$BASE_USB'/original/",$NF)}' | bash


