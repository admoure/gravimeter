#!/bin/bash
#variables ftp
NUMERO_DIAS=1           #NUMERO DE DIAS A REPRESENTAR
BASE=/home/pi/soft_meteo
BASE_USB=/media/CAM/datalogger/meteo
OUT=last_meteo.dat            #CON EXTENSION DISTINTA DE TXT SIEMPRE
#OUTC=last_meteo.dat
#variable grafico
BASE_GRAPH=/var/www
OUT_GRAPH=meteo.png
OUT_GRAPH2=humedad.png
OUT_GRAPH3=presion.png
YRANGE_MIN=0.3157
YRANGE_MAX=0.323

NUMERO_DIAS=$(awk 'BEGIN{FS="="}{print $2}' $BASE/config_dias.con)


#PARA UN FICHERO CONCRETO
if [ -z $1 ]
then

firgas="cat "
for ((i=NUMERO_DIAS;i>-1;i--))
do
	file=meteo`date +%y%02m%02d --date='-'$i' day'`
	yearg=`date +%Y --date='-'$i' day'`
	month=`date +%m --date='-'$i' day'`
	firgas="$firgas $BASE_USB/$yearg/$month/$file.txt"
done
#file=meteo`date +%y%02m%02d --date='0 day'`

#	firgas="$firgas $BASE_USB/original/$file.txt > $BASE/$OUT"
	firgas="$firgas > $BASE/$OUT"
	echo $firgas | bash
else
	cat $1 > $BASE/$OUT
fi



#EL FICHERO QUE CONTIENE LOS ULTIMOS DIAS SE REPRESENTA 
#SI SE QUIERE ESCALAR EL EJE Y A MANO COMENTAR LA LINEA DE AUTOSCALE Y QUITAR COMENTARIO A LA DE YRANGE

#TEMPERATURAS
echo "set terminal png truecolor size 1024,480" > $BASE/gnu_script.gnu
echo "set output \"$BASE_GRAPH/$OUT_GRAPH\"" >> $BASE/gnu_script.gnu
echo "set datafile separator \",\"" >> $BASE/gnu_script.gnu
echo "set timefmt \"%d/%m/%y %H:%M\"" >> $BASE/gnu_script.gnu
echo "set autoscale xy" >> $BASE/gnu_script.gnu
#echo "set yrange [$YRANGE_MIN:$YRANGE_MAX]" >> $BASE/gnu_script.gnu
echo "set xdata time" >> $BASE/gnu_script.gnu
echo "set format x \"%H:%M\\n%d/%m/%y\"" >> $BASE/gnu_script.gnu
echo "set grid mxtics mytics xtics ytics" >> $BASE/gnu_script.gnu
echo "set ylabel \"Temperatura (Celsius)\"" >> $BASE/gnu_script.gnu
echo "set xlabel \"Se representan los últimos $NUMERO_DIAS dias\"" >> $BASE/gnu_script.gnu
echo "plot \"$BASE/$OUT\" using 1:2 title '(Ta, temperatura ambiente)' with lines,\"$BASE/$OUT\" using 1:6 title '(Ti, temperatura interna)' with lines" >> $BASE/gnu_script.gnu
#HUMEDAD RELATIVA
echo "set output \"$BASE_GRAPH/$OUT_GRAPH2\"" >> $BASE/gnu_script.gnu
echo "set y2range [0:100]" >> $BASE/gnu_script.gnu
echo "set y2tics out" >> $BASE/gnu_script.gnu
echo "set tics scale 2" >> $BASE/gnu_script.gnu
echo "set ylabel \"Punto de rocío (Celsius)\"" >> $BASE/gnu_script.gnu
echo "set y2label \"Humedad relativa (%)\"" >> $BASE/gnu_script.gnu
echo "set style fill transparent solid 0.2" >> $BASE/gnu_script.gnu
echo "plot \"$BASE/$OUT\" using 1:3 title '(Hr, humedad relativa)' with filledcurves y2=0 linecolor rgb 'blue' axes x1y2, \"$BASE/$OUT\" using 1:4 title '(Pr, punto de rocío)' with lines linecolor rgb 'red'" >> $BASE/gnu_script.gnu
#PRESION
echo "set output \"$BASE_GRAPH/$OUT_GRAPH3\"" >> $BASE/gnu_script.gnu
echo "unset y2label" >> $BASE/gnu_script.gnu
echo "unset y2tics" >> $BASE/gnu_script.gnu
echo "set tics scale 0.5" >> $BASE/gnu_script.gnu
echo "set autoscale xy" >> $BASE/gnu_script.gnu
echo "set ylabel \"Presión atmosférica (mbar)\"" >> $BASE/gnu_script.gnu
echo "plot \"$BASE/$OUT\" using 1:5 title '(P, presión)' with lines" >> $BASE/gnu_script.gnu

gnuplot $BASE/gnu_script.gnu






