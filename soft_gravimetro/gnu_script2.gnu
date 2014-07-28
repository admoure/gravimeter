set terminal png truecolor size 1024,480
set output "/var/www/bateria.png"
set timefmt "%y %m %d %H %M %S"
set yrange [10:15]
set xdata time
set format x "%H:%M\n%d/%m/%y"
set grid mxtics mytics xtics ytics
set ylabel "Bateria (V)"
set xlabel "Se representan los últimos 2 dias"
plot "/home/pi/soft_gravimetro/gravilast.dat" using 1:12 notitle with lines
