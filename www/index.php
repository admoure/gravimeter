<http>
<head>

</head>
<body bgcolor="#F0F0F0">


<form action="index.php" method="post">
	Usuario:<input type="text" name="usuario">
	Pass: <input type="password" name="clave">
	<input type="submit" name="enviao" value="envio">
</form>

<?php
	global $clave_ok;

?>



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
	function lee_config_file()
	{
		global $ch1,$ch2,$ch3,$muest,$names,$gain,$filter,$lat,$lon,$alt;
		global $usar_servidor,$server,$port,$folder;
		
		$BASE_PI = "/home/pi/soft_gravimetro/";
		$cf = fopen($BASE_PI."configura.txt","r");
		fscanf($cf,"NAME=%s",$names);
		fscanf($cf,"CHANNEL1=%s",$ch1);
		fscanf($cf,"CHANNEL2=%s",$ch2);
		fscanf($cf,"CHANNEL3=%s",$ch3);
		fscanf($cf,"MUESTREO=%s",$muest);
		
		fscanf($cf,"LAT=%s",$lat);
		fscanf($cf,"LON=%s",$lon);
		fscanf($cf,"ALT=%s",$alt);

		fscanf($cf,"SERVERCHECK=%s",$usar_servidor);
		fscanf($cf,"SERVER=%s",$server);
		fscanf($cf,"PORT=%s",$port);
		fscanf($cf,"FOLDER=%s",$folder);

		fclose($cf);

	}
?>


<?php
	//Mira todos los ficheros de un directorio y los mete en un vector

	global $minch1,$minch2,$minch3,$maxch1,$maxch2,$maxch3,$currentch1,$currentch2,$currentch3,$fecha;
	global $cch1,$cch2,$cch3,$currentvch1,$currentvch2,$currentvch3;
	global $anio,$mes,$dia,$hora,$minuto,$segundo;
	global $difch1,$difch2,$difch3;
	global $tambmin,$hrelmin,$procmin,$presmin,$tintmin,$pluvmin,$tambmax,$hrelmax,$procmax,$presmax,$tinmax,$pluvmax;
	global $currenttamb,$currenthrel,$currentproc,$currentpres,$currenttint,$currentpluv;
	global $aniometeo,$mesmeteo,$diameteo,$horameteo,$minutometeo;
	global $label_meteo_dias;
	global $anno,$mese;
	
	//Leemos el directorio donde estan los datos del gravimetro
	
	if (isset($_POST['selecciona']))
	{
		$anno = $_POST['anios'];
		$mese = $_POST['meses'];
		$mese = str_pad($mese,2,"0",STR_PAD_LEFT);
		$aux = fopen("/var/www/auxiliar.txt","w");
		fwrite($aux, "$anno\n");
		fwrite($aux, "$mese\n");
		fclose($aux);
	}
	
	
	$aux = fopen("/var/www/auxiliar.txt","r");
	$anno = trim(fgets($aux));
	$mese = trim(fgets($aux));
	fclose($aux);

	$BASE = "/media/CAM/datalogger/gravimetro/$anno/$mese/";
	$directorio = opendir($BASE);
	while ($archivo = readdir($directorio))
	{
		if (!is_dir($archivo))
		{
			$lista[] = $archivo;
		}
	}
	sort($lista);

	//leemos el directorio donde estan los datos de meteo
	
	if (isset($_POST['seleccionam']))
	{
		$annom = $_POST['aniosm'];
		$mesem = $_POST['mesesm'];
		$mesem = str_pad($mesem,2,"0",STR_PAD_LEFT);
		$aux = fopen("/var/www/auxiliarm.txt","w");
		fwrite($aux, "$annom\n");
		fwrite($aux, "$mesem\n");
		fclose($aux);
	}
	
	$aux = fopen("/var/www/auxiliarm.txt","r");
	$annom = trim(fgets($aux));
	$mesem = trim(fgets($aux));
	fclose($aux);

	$BASE_METEO = "/media/CAM/datalogger/meteo/$annom/$mesem/";
	$direc = opendir($BASE_METEO);
	while ($archivo2 = readdir($direc))
	{
		if (!is_dir($archivo2))
		{
			$lista2[] = $archivo2;
		}
	}
	sort($lista2);

	//leemos dias a representar del gravimetro
	$gd = fopen("/home/pi/soft_gravimetro/config_dias.con","r");

	$gd_line = fgets($gd);

	$tok = strtok($gd_line,"=");
	$tok = strtok("=");
	$label_gravi_dias = $tok;
	fclose($gd);

	//leemos dias a representar de meteo
	$gd = fopen("/home/pi/soft_meteo/config_dias.con","r");

	$gd_line = fgets($gd);

	$tok = strtok($gd_line,"=");
	$tok = strtok("=");
	$label_meteo_dias = $tok;
	fclose($gd);



	//cuando se pulsa el boton de envio se coge el nombre del fichero y se llama a la 
	//funcion de descarga

	if (isset($_POST['gravmenos']))
	{
		$label_gravi_dias = $label_gravi_dias - 1;
		if ($label_gravi_dias < 1)
		{
			$label_gravi_dias = 1;
		}
		$gd = fopen("/home/pi/soft_gravimetro/config_dias.con","w");

		$gd_line = "NUMERO_DIAS=".$label_gravi_dias;
		fwrite($gd,"$gd_line");
		fclose($gd);
		echo $gd_line;
		
		shell_exec("sudo /var/www/gravi_graph_local.sh");
	}

	
	if (isset($_POST['gravmas']))
	{

		$label_gravi_dias=$label_gravi_dias+1;

		$gd = fopen("/home/pi/soft_gravimetro/config_dias.con","w");

		$gd_line = "NUMERO_DIAS=".$label_gravi_dias;
		fwrite($gd,"$gd_line");
		fclose($gd);
		echo $gd_line;
		shell_exec("sudo /var/www/gravi_graph_local.sh");

	}



	if (isset($_POST['meteomenos']))
	{
		$label_meteo_dias = $label_meteo_dias - 1;
		if ($label_meteo_dias < 1)
		{
			$label_meteo_dias = 1;
		}
		$gd = fopen("/home/pi/soft_meteo/config_dias.con","w");

		$gd_line = "NUMERO_DIAS=".$label_meteo_dias;
		fwrite($gd,"$gd_line");
		fclose($gd);
		echo $gd_line;
		shell_exec("sudo /var/www/meteo_graph_local.sh");
	}
	if (isset($_POST['meteomas']))
	{
		$label_meteo_dias = $label_meteo_dias + 1;
		$gd = fopen("/home/pi/soft_meteo/config_dias.con","w");

		$gd_line = "NUMERO_DIAS=".$label_meteo_dias;
		fwrite($gd,"$gd_line");
		fclose($gd);
		echo $gd_line;
		shell_exec("sudo /var/www/meteo_graph_local.sh");
	}




	if (isset($_POST['envio']))
	{
		$name = $_POST['ficheros'];
		$fich=$BASE.$name;
		download_file($fich);
	}

	if (isset($_POST['envio2']))
	{
		$name2 = $_POST['ficheros2'];
		$fich2=$BASE_METEO.$name2;
		download_file($fich2);
	}
	//AHORA LEE EL FICHERO DE CONFIGURACIÓN PARA VER QUE ES LO QUE ESTA CHECKED O NO
	lee_config_file();
	//$label_meteo_dias = 7;


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
		if (!empty($_POST['server_check']))
		{
			$usar_servidor="ON";
		}
		else
		{
			$usar_servidor="OFF";
		}

		//$muest=$_POST['muestreo'];
		$muest=0.5;
		$names=$_POST['nombre'];
		$lat=$_POST['latitud'];
		$lon=$_POST['longitud'];
		$alt=$_POST['altitud'];
		$server=$_POST['server'];
		$port=$_POST['port'];
		$folder=$_POST['folder'];
		fwrite($cf, "NAME=$names\nCHANNEL1=$ch1\nCHANNEL2=$ch2\nCHANNEL3=$ch3\nMUESTREO=$muest\n");
		fwrite($cf, "LAT=$lat\nLON=$lon\nALT=$alt\n");
		fwrite($cf, "SERVERCHECK=$usar_servidor\nSERVER=$server\nPORT=$port\nFOLDER=$folder\n");
		fclose($cf);


	}

	$today = date("ymd").".txt";
	$today_meteo = "meteo".date("ymd").".txt";
	$anio_meteo_grav = date("Y");
	$mes_meteo_grav = date("m");
	$file_curso = fopen("/media/CAM/datalogger/gravimetro/$anio_meteo_grav/$mes_meteo_grav/".$today,"r");
	$file_curso_meteo = fopen("/media/CAM/datalogger/meteo/$anio_meteo_grav/$mes_meteo_grav/".$today_meteo,"r");

	//ESTADISTICA DEL GRAVIMETRO

	$current_line = fgets($file_curso);

	$tok = strtok($current_line," ");
	$anio = $tok;
	$tok = strtok(" ");
	$mes = $tok;
	$tok = strtok(" ");
	$dia = $tok;
	$tok = strtok(" ");
	$hora = $tok;
	$tok = strtok(" ");
	$minuto = $tok;
	$tok = strtok(" ");
	$segundo = $tok;
	$tok = strtok(" ");
	$cch1 = $tok;
	$tok = strtok(" ");
	$cch2 = $tok;
	$tok = strtok(" ");
	$cch3 = $tok;
	$tok = strtok(" ");
	$minch1 = $tok;
	$tok = strtok(" ");
	$minch2 = $tok;
	$tok = strtok(" ");
	$minch3 = $tok;


	$maxch1 = $minch1;
	$maxch2 = $minch2;
	$maxch3 = $minch3;

	while($current_line = fgets($file_curso))
	{
		$tok = strtok($current_line," ");
		$anio = $tok;
		$tok = strtok(" ");
		$mes = $tok;
		$tok = strtok(" ");
		$dia = $tok;
		$tok = strtok(" ");
		$hora = $tok;
		$tok = strtok(" ");
		$minuto = $tok;
		$tok = strtok(" ");
		$segundo = $tok;
		$tok = strtok(" ");
		$currentch1 = $tok;
		$tok = strtok(" ");
		$currentch2 = $tok;
		$tok = strtok(" ");
		$currentch3 = $tok;
	$tok = strtok(" ");
	$currentvch1 = $tok;
	$tok = strtok(" ");
	$currentvch2 = $tok;
	$tok = strtok(" ");
	$currentvch3 = $tok;

		//echo "$equis $ys $temp a las $hora:$minuto:$segundo<br>";
		if($currentvch1 < $minch1)
		{
			$minch1 = $currentvch1;
		}
		if($currentvch1 > $maxch1)
		{
			$maxch1 = $currentvch1;
		}
		if($currentvch2 < $minch2)
		{
			$minch2 = $currentvch2;
		}
		if($currentvch2 > $maxch2)
		{
			$maxch2 = $currentvch2;
		}
		if($currentvch3 < $minch3)
		{
			$minch3 = $currentvch3;
		}
		if($currentvch3 > $maxch3)
		{
			$maxch3 = $currentvch3;
		}



	}
	$dffch1=abs($maxch1-$minch1);
	$dffch2=abs($maxch2-$minch2);
	$dffch3=abs($maxch3-$minch3);

	$difch1=number_format($dffch1,2);
	$difch2=number_format($dffch2,2);
	$difch3=number_format($dffch3,2);

	fclose($file_curso);

	//AHORA LA ESTACION DE METEO

	$current_line = fgets($file_curso_meteo);

	$tok = strtok($current_line,",");
	$fechia = $tok;
	$tambmin = strtok(",");
	$hrelmin = strtok(",");
	$procmin = strtok(",");
	$presmin = strtok(",");
	$tintmin = strtok(",");
	$pluvmin = strtok(",");

	$tambmax = $tambmin;
	$hrelmax = $hrelmin;
	$procmax = $procmin;
	$presmax = $presmin;
	$tintmax = $tintmin;
	$pluvmax = $pluvmin;

	$tok = strtok($fechia," ");
	$deit = $tok;
	$ora = strtok(" ");

	$tok = strtok($deit,"/");
	$diameteo = $tok;
	$mesmeteo = strtok("/");
	$aniometeo = strtok("/");

	$tok = strtok($ora,":");
	$horameteo = $tok;
	$minutometeo = strtok(":");


	while($current_line = fgets($file_curso_meteo))
	{


		$tok = strtok($current_line,",");
		$fechia = $tok;
		$currenttamb = strtok(",");
		$currenthrel = strtok(",");
		$currentproc = strtok(",");
		$currentpres = strtok(",");
		$currenttint = strtok(",");
		$currentpluv = strtok(",");

		$tok = strtok($fechia," ");
		$deit = $tok;
		$ora = strtok(" ");

		$tok = strtok($deit,"/");
		$diameteo = $tok;
		$mesmeteo = strtok("/");
		$aniometeo = strtok("/");

		$tok = strtok($ora,":");
		$horameteo = $tok;
		$minutometeo = strtok(":");


		//echo "$equis $ys $temp a las $hora:$minuto:$segundo<br>";
		if($currenttamb < $tambmin)
		{
			$tambmin = $currenttamb;
		}
		if($currenttamb > $tambmax)
		{
			$tambmax = $currenttamb;
		}
		if($currenthrel < $hrelmin)
		{
			$hrelmin = $currenthrel;
		}
		if($currenthrel > $hrelmax)
		{
			$hrelmax = $currenthrel;
		}

		if($currentproc < $procmin)
		{
			$procmin = $currentproc;
		}
		if($currentproc > $procmax)
		{
			$procmax = $currentproc;
		}

		if($currentpres < $presmin)
		{
			$presmin = $currentpres;
		}
		if($currentpres > $presmax)
		{
			$presmax = $currentpres;
		}

		if($currenttint < $tintmin)
		{
			$tintmin = $currenttint;
		}
		if($currenttint > $tintmax)
		{
			$tintmax = $currenttint;
		}
		if($currentpluv < $pluvmin)
		{
			$pluvmin = $currentpluv;
		}
		if($currentpluv > $pluvmax)
		{
			$pluvmax = $currentpluv;
		}



	}
	$dfftamb=abs($tambmax-$tambmin);
	$dffhrel=abs($hrelmax-$hrelmin);
	$dffproc=abs($procmax-$procmin);
	$dffpres=abs($presmax-$presmin);
	$dfftint=abs($tintmax-$tintmin);
	$dffpluv=abs($pluvmax-$pluvmin);

	$diftamb=number_format($dfftamb,2);
	$difhrel=number_format($dffhrel,2);
	$difproc=number_format($dffproc,2);
	$difpres=number_format($dffpres,2);
	$diftint=number_format($dfftint,2);
	$difpluv=number_format($dffpluv,2);

	fclose($file_curso_meteo);

?>

<?	if(isset($_POST['enviao']))
	{
		$us = $_POST['usuario'];
		$cl = $_POST['clave'];
		$mius = "ign";
		$miclave = "ignfomento";
		if($us == $mius && $cl == $miclave)
		{
?>





<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

	<center>
	<?php
	echo "<h1>GRAV&Iacute;METRO (".$names.")</h1>";
	?>
	<div style="float:left;width:100%">
		<iframe src="./gravimetro.html" width=1024 height=500 frameborder=0 scrolling=no>
		</iframe>
	</div>
	
	<?php
		if($ch2=="ON")
		{
			echo "<div style=\"float:left;width:100%\">";
			echo "<iframe src=./canal2.a.html width=1024 height=500 frameborder=0 scrolling=no>";
			echo "</iframe>";
			echo "</div>";			
		}
	?>
	
	<?php
		if($ch3=="ON")
		{
			echo "<div style=\"float:left;width:100%\">";
			echo "<iframe src=./bateria.html width=1024 height=500 frameborder=0 scrolling=no>";
			echo "</iframe>";
			echo "</div>";
		}

	?>
	<div style="float:left; width:35%">
		<p>  </p>
	</div>
	<div style="border-style:solid; border-color:#CCCCCC; border-width:thin;float:left;width:30%">

		<div style="float:left;width:100%">
			<?
				echo "<p align=center>".$label_gravi_dias."<br>";
				echo "Dias a representar</p>";
			?>
		</div>

		<div style="float:left;width:100%">
			<div style="float:left;width:50%">
				<p align="right"><input type="submit" name="gravmenos" value="-"></p>
			</div>
			<div style="float:right;width:50%">
				<p align="left"><input type="submit" name="gravmas" value="+"></p>
			</div>

		</div>
	</div>
	<div style="float:left; width:30%">
		<p>   </p>
	</div>

	<div style="float:left;width:100%">

		<h2>Archivos diarios</h2>
	</div>

	<div style="float:left;width:100%">
	
		<select size="1" name="anios">
		
			<?php
				$paranios=date("Y")-1;
				for ($j=0;$j<3;$j++)
				{
					if ($anno==$paranios+$j)
					{
						echo "<option selected>".($paranios+$j)."</option><br>";
					}
					else
					{
						echo "<option>".($paranios+$j)."</option><br>";
					}
				}
			
			?>
		</select>
		
		<select size="1" name="meses">
		<?php
			for($j=1;$j<13;$j++)
			{
				if ($mese == $j)
				{
					echo "<option selected>".$j."</option><br>";
				}
				else
				{
					echo "<option>".$j."</option><br>";
				}
			}

		?>
		</select>	
	
		<input type="submit" name="selecciona" value="Select">

		<br>
	
		<select size="1" name="ficheros">

		<?php
			for ($i=0;$i<count($lista);$i++)
			{
				echo "<option>".$lista[$i]."</option><br>";
			}

		?>

		</select>
		<input type="submit" name="envio" value="Descarga">
	</div>

	<div style="float:left;width:100%">
		<h2>DATOS METEOROL&Oacute;GICOS</h2>
	</div>


	<div style="float:left;width:100%">
	<iframe src="./meteo.html" width=1024 height=500 frameborder=0 scrolling=no>
	</iframe>
	</div>

	<div style="float:left;width:100%">
	<iframe src="./humedad.html" width=1024 height=500 frameborder=0 scrolling=no>
	</iframe>
	</div>

	<div style="float:left;width:100%">
	<iframe src="./presion.html" width=1024 height=500 frameborder=0 scrolling=no>
	</iframe>
	</div>

	<div style="float:left;width:100%">
		<div style="float:left;width:35%">
			<p>   </p>
		</div>
		<div style="border-style:solid; border-color:#CCCCCC; border-width:thin;float:left;width:30%">

			<div style="float:left;width:100%">
				<?
					echo "<p align=center>".$label_meteo_dias."<br>";
					echo "Dias a representar</p>";
				?>
			</div>

			<div style="float:left;width:100%">
				<div style="float:left;width:50%">
					<p align="right"><input type="submit" name="meteomenos" value="-"></p>
				</div>
				<div style="float:right;width:50%">
					<p align="left"><input type="submit" name="meteomas" value="+"></p>
				</div>

			</div>
		</div>
	</div>

	<div style="float:left; width:100%">
		<h2>Archivos diarios</h2>
	</div>

	<div style="float:left;width:100%">
	
		<select size="1" name="aniosm">
		
			<?php
				$paraniosm=date("Y")-1;
				for ($j=0;$j<3;$j++)
				{
					if ($annom==$paraniosm+$j)
					{
						echo "<option selected>".($paraniosm+$j)."</option><br>";
					}
					else
					{
						echo "<option>".($paraniosm+$j)."</option><br>";
					}
				}
			
			?>
		</select>
		
		<select size="1" name="mesesm">
		<?php
			for($j=1;$j<13;$j++)
			{
				if ($mesem == $j)
				{
					echo "<option selected>".$j."</option><br>";
				}
				else
				{
					echo "<option>".$j."</option><br>";
				}
			}

		?>
		</select>	
	
		<input type="submit" name="seleccionam" value="Select">

		<br>
		
	
	
	<select size="1" name="ficheros2">
	<?php
		for ($i=0;$i<count($lista2);$i++)
		{
			echo "<option>".$lista2[$i]."</option><br>";
		}

	?>

	</select>
	<input type="submit" name="envio2" value="Descarga">
	</div>

	</center>





	<div style="float:left;width:100%">

		<div style="float:left;width:50%">
			<div style="float:left; width:100%">
				<h2 align="center">Configuraci&oacute;n</h2>
			</div>
			<div style="float:left; width:10%">
			<p> </p>
			</div>
			<div style="border-style:solid; border-color:#CCCCCC; border-width:thin; width:80%; float:left">
				<div style="float:left; width:100%">
				<h3 align="center">Grav&iacute;metro</h3>
				</div>
				<div style="float:left;width:100%">
					<div style="float:left;width:45%">
						<p align="right">Nombre de la estaci&oacute;n: </p>
					</div>
					<div style="width:45%;float:right">
					<p align="left">
					<?php
						echo "<input type="."text"." size=15 name=nombre value=$names aling=left>"
					?>
					</p>
					</div>
				</div>
				<div style="float:left;width:100%">
					<div style="width:45%;float:left">
						<p align="right">Selecci&oacute;n de canales: </p>
					</div>
					<div style="width:45%;float:right">
						<p align="left">
						<?php

							if($ch1=="ON")
							{
								echo "<input type="."checkbox"." name="."check1"." checked>CANAL 1<br>";
							}
							else
							{
								echo "<input type="."checkbox"." name="."check1"." >CANAL 1<br>";
							}
							if($ch2=="ON")
							{
								echo "<input type="."checkbox"." name="."check2"." checked>CANAL 2<br>";
							}
							else
							{
								echo "<input type="."checkbox"." name="."check2"." >CANAL 2<br>";
							}
							if($ch3=="ON")
							{
								echo "<input type="."checkbox"." name="."check3"." checked>CANAL 3<br>";
							}
							else
							{
								echo "<input type="."checkbox"." name="."check3"." >CANAL 3<br>";
							}
						?>
					</div>

				</div>
				<div style="float:left;width:100%">
					<div style="float:left;width:45%">
						<p align="right">Muestreo (en minutos):</p>
					</div>
					<div style="float:right;width:45%">
						<p align="left">
						<?php
							echo "<input type=text size=15 name=muestreo value=$muest disabled><br>";
						?>
						</p>
					</div>



				</div>


				<div style="float:left; width:100%;height:50px">
					<div style="float:left;width:45%">
						<p align="center"><b>Localizaci&oacute;n</b></p>
					</div>
				</div>
				<div style="float:left; width:100%;height:30px">
					<div style="float:left; width:45%">
						<p align="center">LAT</p>
					</div>
					<div style="float:right;width:45%">
						<p align="left">
						<?php
							echo "<input type=text size=15 name=latitud value=$lat>";
						?>
						</p>
					</div>
				</div>
				<div style="float:left; width:100%;height:30px">
					<div style="float:left; width:45%">
						<p align="center">LON</p>
					</div>
					<div style="float:right;width:45%">
						<p align="left">
						<?php
							echo "<input type=text size=15 name=longitud value=$lon>";
						?>
						</p>
					</div>
				</div>
				<div style="float:left; width:100%">
					<div style="float:left; width:45%">
						<p align="center">ALT</p>
					</div>
					<div style="float:right;width:45%">
						<p align="left">
						<?php
							echo "<input type=text size=15 name=altitud value=$alt>";
						?>
						</p>
					</div>
				</div>

				<div style="float:left;width:100%;height:50px">
				<div style="float:left;width:45%">
					<p align="center"><b>Servidor remoto   </b>
						<?
							if ($usar_servidor=="ON")
							{
								echo "<input type=checkbox name=server_check checked>";
							}
							else	
							{
								echo "<input type=checkbox name=server_check>";
							}
						?>
					</p>
				</div>
				
				</div>
				
				<div style="float:left;width:100%;height:30px">
					<div style="float:left;width:45%">
						<p align="center">Servidor</p>
					</div>
					<div style="float:right;width:45%">
						<p align="left">
						<?php
							echo "<input type=text size=15 name=server value=$server>";
						?>
						</p>
					</div>
				</div>
				<div style="float:left;width:100%;height:30px">

					<div style="float:left;width:45%">
						<p align="center">Puerto</p>
					</div>
					<div style="float:right;width:45%">
						<p align="left">
						<?php
							echo "<input type=text size=15 name=port value=$port>";
						?>
						</p>
					</div>
				</div>
				<div style="float:left;width:100%;height:50px">

					<div style="float:left;width:45%">
						<p align="center">Carpeta</p>
					</div>
					<div style="float:right;width:45%">
						<p align="left">
						<?php
							echo "<input type=text size=15 name=folder value=$folder>";
						?>
						</p>
					</div>
				</div>


				
				<div style="float:left;width:100%">
					<p align="center">
					<input type="submit" name="config" value="Enviar"><br>
					</p>
				</div>




			</div>
		</div>

		<div style="float:left;width:50%">
			<div style="float:right:width:100%">
				<h2 align="center">Informaci&oacute;n</h2>
			</div>

			<div style="float:left; width:10%">
				<p> </p>
			</div>

			<div style="border-style:solid; border-color:#CCCCCC; border-width:thin; width:80%;float:left">
				<div style="width:100%">
					<h2 align="center"><b>Datos actuales</b></h2>
				</div>
				<div style="float:left;width:45%">
				</div>
				<div style="float:right;width:45%">
					<?php
					?>
				</div>
				<div style="width:100%">
					<h3 align="center"><b>Grav&iacute;metro</b></h3>
				</div>
				<div style="float:left;width:45%">
					<p align="center"><b>Fecha</b></p>
					<p align="center"><b>ch1 (mV)</b></p>
					<p align="center"><b>ch2 (mV)</b></p>
					<p align="center"><b>ch3 (V)</b></p>
				</div>
				<div style="float:right;width:45%">
					<?php
					echo "<p align=center style=color:green><b>$dia/$mes/$anio $hora:$minuto:$segundo</p>";
					echo "<p align=center style=color:green>$currentvch1</p>";
					echo "<p align=center style=color:green>$currentvch2</p>";
					echo "<p align=center style=color:green>$currentvch3</b></p>";
					?>
				</div>
				<div style="float:left;width:100%">
					<h3 align="center"><b>Meteo</b></h3>
				</div>

				<div style="float:left;width:45%">
					<p align="center"><b>Fecha</b></p>
					<p align="center"><b>T ambiente (&degC)</b></p>
					<p align="center"><b>T interna (&degC)</b></p>
					<p align="center"><b>H realtiva (%)</b></p>
					<p align="center"><b>P roc&iacute;o (&degC)</b></p>
					<p align="center"><b>Presi&oacute;n (mbar)</b></p>
				</div>

				<div style="float:right;width:45%">
					<?php
					echo "<p align=center style=color:green><b>$diameteo/$mesmeteo/$aniometeo $horameteo:$minutometeo</p>";
					echo "<p align=center style=color:green>$currenttamb</p>";
					echo "<p align=center style=color:green>$currenttint</p>";
					echo "<p align=center style=color:green>$currenthrel</p>";
					echo "<p align=center style=color:green>$currentproc</p>";
					echo "<p align=center style=color:green>$currentpres</p>";
					?>
				</div>



				<div style="float:left;width:100%">
					<h2 align="center"><b>Estad&iacute;sticas</b></h2>
				</div>
				<div style="float:left; width:100%">
					<h3 align="center">Grav&iacutemetro</h3>
				</div>

				<div style="float:left;width:100%">
					<div style="float:left;width:22%;">
						<p align="center"> </p>
					</div>
					<div style="float:left;width:23%;">
						<p align="center"><b>Min</b></p>
					</div>
					<div style="float:left;width:23%;">
						<p align="center"><b>Max</b></p>
					</div>
					<div style="float:left;width:23%;">
						<p align="center"><b>Dif</b></p>
					</div>
				</div>
				<div style="float:left;width:100%">
					<div style="float:left;width:22%;">
					<p align="center"><b>ch1 (mV)</b></p>
					<p align="center"><b>ch2 (mV)</b></p>
					<p align="center"><b>ch3 (V)</b></p>
					</div>
					<div style="float:left;width:23%;">
						<?php
						echo "<p align=center style=color:green><b>$minch1</b></p>";
						echo "<p align=center style=color:green><b>$minch2</b></p>";
						echo "<p align=center style=color:green><b>$minch3</b></p>";
						?>
					</div>
					<div style="float:left;width:23%">
						<?php
						echo "<p align=center style=color:green><b>$maxch1</b></p>";
						echo "<p align=center style=color:green><b>$maxch2</b></p>";
						echo "<p align=center style=color:green><b>$maxch3</b></p>";
						?>
					</div>
					<div style="float:left;width:23%;">
						<?php
						echo "<p align=center style=color:green><b>$difch1</b></p>";
						echo "<p align=center style=color:green><b>$difch2</b></p>";
						echo "<p align=center style=color:green><b>$difch3</b></p>";
						?>
					</div>
				</div>

				<div style="float:left;width:100%">
					<div style="float:left;width:22%;">
						<p align="center"><b>Ta (&degC)</b></p>
						<p align="center"><b>Ti (&degC)</b></p>
						<p align="center"><b>HR (%)</b></p>
						<p align="center"><b>Pr (&degC)</b></p>
						<p align="center"><b>P (mbar)</b></p>
					</div>

					<div style="float:left;width:23%">
						<?php
						echo "<p align=center style=color:green><b>$tambmin</b></p>";
						echo "<p align=center style=color:green><b>$tintmin</b></p>";
						echo "<p align=center style=color:green><b>$hrelmin</b></p>";
						echo "<p align=center style=color:green><b>$procmin</b></p>";
						echo "<p align=center style=color:green><b>$presmin</b></p>";
						?>
					</div>
					<div style="float:left;width:23%">
						<?php
						echo "<p align=center style=color:green><b>$tambmax</b></p>";
						echo "<p align=center style=color:green><b>$tintmax</b></p>";
						echo "<p align=center style=color:green><b>$hrelmax</b></p>";
						echo "<p align=center style=color:green><b>$procmax</b></p>";
						echo "<p align=center style=color:green><b>$presmax</b></p>";
						?>
					</div>

					<div style="float:left;width:23%;">
						<?php
						echo "<p align=center style=color:green><b>$diftamb</b></p>";
						echo "<p align=center style=color:green><b>$diftint</b></p>";
						echo "<p align=center style=color:green><b>$difhrel</b></p>";
						echo "<p align=center style=color:green><b>$difproc</b></p>";
						echo "<p align=center style=color:green><b>$difpres</b></p>";
						?>
					</div>

				</div>

			</div>
		</div>

	</div>



	<div style="width:100%">

	</div>


	<div style="float:left;width:100%;">
		<p align=center>Copyleft IGN 2014 </p>
	</div>
</form>
<?	}else{echo "incorrecto";}}?>

</body>
</head>
