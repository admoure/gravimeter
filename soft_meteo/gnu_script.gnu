set terminal png truecolor size 1024,480
set output "/var/www/meteo.png"
set datafile separator ","
set timefmt "%d/%m/%y %H:%M"
set autoscale xy
set xdata time
set format x "%H:%M\n%d/%m/%y"
set grid mxtics mytics xtics ytics
set ylabel "Temperatura (Celsius)"
set xlabel "Se representan los últimos 3 dias"
plot "/home/pi/soft_meteo/last_meteo.dat" using 1:2 title '(Ta, temperatura ambiente)' with lines,"/home/pi/soft_meteo/last_meteo.dat" using 1:6 title '(Ti, temperatura interna)' with lines
set output "/var/www/humedad.png"
set y2range [0:100]
set y2tics out
set tics scale 2
set ylabel "Punto de rocío (Celsius)"
set y2label "Humedad relativa (%)"
set style fill transparent solid 0.2
plot "/home/pi/soft_meteo/last_meteo.dat" using 1:3 title '(Hr, humedad relativa)' with filledcurves y2=0 linecolor rgb 'blue' axes x1y2, "/home/pi/soft_meteo/last_meteo.dat" using 1:4 title '(Pr, punto de rocío)' with lines linecolor rgb 'red'
set output "/var/www/presion.png"
unset y2label
unset y2tics
set tics scale 0.5
set autoscale xy
set ylabel "Presión atmosférica (mbar)"
plot "/home/pi/soft_meteo/last_meteo.dat" using 1:5 title '(P, presión)' with lines
