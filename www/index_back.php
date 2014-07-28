<http>
<head>

</head>
<body>
<?php
	//funcion para descarga de fichero
	function download_file($archiv)
	{
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($archiv).'"');
		header('Content-Length: ' . filesize($archiv));
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		ob_clean();
		flush();
		readfile($archiv);
		exit;
	}
?>
<?php
	function lee_file()
	{
		global $ch1,$ch2,$ch3,$muest;
		$BASE_PI = "/home/pi/soft_gravimetro/";
		$cf = fopen($BASE_PI."configura.txt","r");
		fscanf($cf,"CHANNEL1=%s",$ch1);
		fscanf($cf,"CHANNEL2=%s",$ch2);
		fscanf($cf,"CHANNEL3=%s",$ch3);
		fscanf($cf,"MUESTREO=%s",$muest);
		fclose($cf);

	}
?>


<?php
	//Mira todos los ficheros de un directorio y los mete en un vector
	$BASE = "/media/CAM/datalogger/gravimetro/original/";
	$directorio = opendir($BASE);
	while ($archivo = readdir($directorio))
	{
		if (!is_dir($archivo))
		{
			$lista[] = $archivo;
		}
	}
	sort($lista);

	//cuando se pulsa el boton de envio se coge el nombre del fichero y se llama a la 
	//funcion de descarga

if (isset($_POST['envio']))
{
	$name = $_POST['ficheros'];
	$fich=$BASE.$name;
	download_file($fich);
}
	//AHORA LEE EL FICHERO DE CONFIGURACIÓN PARA VER QUE ES LO QUE ESTA CHECKED O NO
	lee_file();


if (isset($_POST['config']))
{
	$cf = fopen("/home/pi/soft_gravimetro/configura.txt","w");

	if (!empty($_POST['check1']))
	{
		$ch1="ON";
	}
	else
	{
		$ch1="0FF";
	}
	if (!empty($_POST['check2']))
	{
		$ch2="ON";
	}
	else
	{
		$ch2="OFF";
	}
	if (!empty($_POST['check3']))
	{
		$ch3="ON";
	}
	else
	{
		$ch3="OFF";
	}
	$muest=$_POST['muestreo'];
	fwrite($cf, "CHANNEL1=$ch1\nCHANNEL2=$ch2\nCHANNEL3=$ch3\nMUESTREO=$muest\n");
	fclose($cf);


}
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

	<center>
	<h1>GRAVIMETRO</h1>
	<iframe src="./gravimetro.html" width=1500 height=500 frameborder=0 scrolling=no>
	</iframe>


	<h2>Archivos diarios</h2>
	<select size="1" name="ficheros">

	<?php
		for ($i=0;$i<count($lista);$i++)
		{
			echo "<option>".$lista[$i]."</option><br>";
		}

	?>

	</select>
	<input type="submit" name="envio" value="Descarga">
	</center>
	<h2>Configuraci&oacute;n</h2>
	<div style="border-style:solid; border-color:#CCCCCC; border-width:thin; width:450px;">
	Selecci&oacute;n de canales: <br>
	<?php

	if ($ch1=="ON")
	{
		echo "<input type="."checkbox"." name="."check1"." checked>CANAL 1<br>";
	}
	else
	{
		echo "<input type="."checkbox"." name="."check1"." >CANAL 1<br>";

	}
	if ($ch2=="ON")
	{
		echo "<input type="."checkbox"." name="."check2"." checked>CANAL 2<br>";
	}
	else
	{
		echo "<input type="."checkbox"." name="."check2"." >CANAL 2<br>";

	}
	if ($ch3=="ON")
	{
		echo "<input type="."checkbox"." name="."check3"." checked>CANAL 3<br>";
	}
	else
	{
		echo "<input type="."checkbox"." name="."check3"." >CANAL 3<br>";

	}
	?>
	<br>
	Muestreo (XX) (en minutos, minimo 01):
	<?php
	echo "<input type="."text"." name=muestreo value=$muest><br>"
	?>
	<br>
	<input type="submit" name="config" value="Enviar"><br>

	</div>
</form>
</body>
</head>
