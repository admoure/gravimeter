set terminal png truecolor size 1024,480
set output "/var/www/gravimetro.png"
set timefmt "%y %m %d %H %M %S"
set autoscale xy
set xdata time
set format x "%H:%M\n%d/%m/%y"
set grid mxtics mytics xtics ytics
set ylabel "Gravimetro (mV)"
set xlabel "Se representan los últimos 2 dias"
plot "/home/pi/soft_gravimetro/gravilast.dat" using 1:10 notitle with lines
