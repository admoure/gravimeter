#!/bin/bash

FILE="/home/pi/soft_gravimetro/configura.txt" #esta en la configuracion del gravimetro
MIRROR="/media/CAM/datalogger/meteo/" #el directorio que se quiere copiar

check=$(cat $FILE | awk 'BEGIN{FS="="}{

	if ($1 == "SERVERCHECK")
	{
		if ($2 == "ON")
		{
			print "true";
		}
		else
		{
			print "false";
		}
	}
}END{}')

server=$(cat $FILE | awk 'BEGIN{FS="="}{

	if ($1 == "SERVER" && "'$check'" == "true")
	{
		print $2;
	}
}END{}')

port=$(cat $FILE | awk 'BEGIN{FS="="}{

	if ($1 == "PORT" && "'$check'" == "true")
	{
		print $2;
	}
}END{}')

folder=$(cat $FILE | awk 'BEGIN{FS="="}{

	if ($1 == "FOLDER" && "'$check'" == "true")
	{
		print $2;
	}
}END{}')


#echo "rsync --update -razv 'ssh -p '$port'' $MIRROR svvcog@$server:$folder"
rsync --update --timeout=30 -razvP -e 'ssh -p '$port'' $MIRROR svvcog@$server:$folder

