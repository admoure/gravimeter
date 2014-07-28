#!/bin/bash
NUMERO_DIAS=1
BASE=/home/pi/soft_gravimetro
BASE_USB=/media/CAM/datalogger/gravimetro
BASE_GRAPH=/var/www

OUT=gravilast.dat
OUTC=gravilastv.dat
OUTCC=gravilastvv.dat
OUT_GRAPH=gravimetro.png
OUT_GRAPH2=bateria.png
OUT_GRAPH3=canal2.png


NUMERO_DIAS=$(awk 'BEGIN{FS="="}{print $2}' $BASE/config_dias.con)
#cat $BASE/config_dias.con | awk 'BEGIN{FS="="}{'$NUMERO_DIAS' = $1}'


if [ -z $1 ]
then

firgas="cat "
for ((i=$NUMERO_DIAS;i>-1;i--))
do
	file=`date +%y%02m%02d --date='-'$i' day'`
	yearg=`date +%Y --date='-'$i' day'`
	month=`date +%02m --date='-'$i' day'`
	firgas="$firgas $BASE_USB/$yearg/$month/$file.txt"
done
#file=`date +%y%02m%02d --date='0 day'`
#firgas="$firgas $BASE_USB/original/$file.txt > $BASE/$OUT"
firgas="$firgas > $BASE/$OUT"

echo $firgas | bash
else
	cat $1 > $BASE/$OUT
fi

#CONVERTIMOS DE CUENTAS A VOLTIOS

#cat $BASE/$OUT | awk 'BEGIN{}{$(NF+1)=(($(NF-2)*(2500/65536))-1250)*8;print > "'$BASE'/'$OUTCC'"}'
#cat $BASE/$OUTCC | awk 'BEGIN{}{$(NF+1)=($(NF-1)*(2.5/65536))*((32.67+6.7)/6.7);print > "'$BASE'/'$OUTC'"}'

#cat $BASE/$OUT | awk 'BEGIN{}{$(NF+1)=$(NF-2)*(2.5/65536);print}'

#REPRESENTAMOS LOS DIAS

echo "set terminal png truecolor size 1024,480" > $BASE/gnu_script1.gnu
echo "set output \"$BASE_GRAPH/$OUT_GRAPH\"" >> $BASE/gnu_script1.gnu
echo "set timefmt \"%y %m %d %H %M %S\"" >> $BASE/gnu_script1.gnu
#echo "set yrange [1300:1400]" >> $BASE/gnu_script1.gnu
echo "set autoscale xy" >> $BASE/gnu_script1.gnu
echo "set xdata time" >> $BASE/gnu_script1.gnu
echo "set format x \"%H:%M\\n%d/%m/%y\"" >> $BASE/gnu_script1.gnu
echo "set grid mxtics mytics xtics ytics" >> $BASE/gnu_script1.gnu
echo "set ylabel \"Gravimetro (mV)\"" >> $BASE/gnu_script1.gnu
echo "set xlabel \"Se representan los últimos $NUMERO_DIAS dias\"" >> $BASE/gnu_script1.gnu
echo "plot \"$BASE/$OUT\" using 1:10 notitle with lines" >> $BASE/gnu_script1.gnu

chmod 777 $BASE/gnu_script1.gnu
gnuplot $BASE/gnu_script1.gnu

echo "set terminal png truecolor size 1024,480" > $BASE/gnu_script2.gnu
echo "set output \"$BASE_GRAPH/$OUT_GRAPH2\"" >> $BASE/gnu_script2.gnu
echo "set timefmt \"%y %m %d %H %M %S\"" >> $BASE/gnu_script2.gnu
echo "set yrange [10:15]" >> $BASE/gnu_script2.gnu
#echo "set autoscale xy" >> $BASE/gnu_script2.gnu
echo "set xdata time" >> $BASE/gnu_script2.gnu
echo "set format x \"%H:%M\\n%d/%m/%y\"" >> $BASE/gnu_script2.gnu
echo "set grid mxtics mytics xtics ytics" >> $BASE/gnu_script2.gnu
echo "set ylabel \"Bateria (V)\"" >> $BASE/gnu_script2.gnu
echo "set xlabel \"Se representan los últimos $NUMERO_DIAS dias\"" >> $BASE/gnu_script2.gnu
echo "plot \"$BASE/$OUT\" using 1:12 notitle with lines" >> $BASE/gnu_script2.gnu

chmod 777 $BASE/gnu_script2.gnu
gnuplot $BASE/gnu_script2.gnu

#falta implementar la representacion del canal2 en caso de que se ponga algo
#la salida debe ser $OUT_GRAPH3 cuyo nombre se corresponde con canal2.png
#se puede cambiar arriba.  Sería la misma representación que las anteriores
#pero fijando una escala o haciendola automatica
#habria que crear un html en /var/www cuyo nombre generico debería ser
#canal2.html que recogiera la imagen canal2.png.  La pagina index.php
#ya está preparada para mostrar esa pagina html.





