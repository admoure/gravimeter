#include <stdio.h>
#include <stdlib.h>
#include <wiringPi.h>
#include <wiringSerial.h>
#include <string.h>


int fd;
int length;
int flag_while = 1;			//flag del while de la interrupcion que solo se pone a cero cuando recibe un 0x0A

static unsigned char datos[256];
static unsigned char rx_buffer[256];		//buffer de recepcion
static unsigned char buffer[512];		//buffer general en el que se va acumulando rx_buffer hasta que recibe un 0x0A
static unsigned char line[512];		//buffer que guarda la orden que se envia al sistema de escribir en el fichero
unsigned char caracter;			//se usa para la espera por teclado
static unsigned char fecha[32];
static unsigned char horas[32];
int dia,mes,anio,hora,minuto;
static unsigned char comando[512];
static unsigned char base[64]={"/media/CAM/datalogger/meteo"};

//unsigned char dia[15],mes[15],anio[15],hora[15],minuto[15];
int i,j;
int t1=0;
int t2=0;
int t3=0;
int count=0;

#define IN_PIN	1			//pin que hace de interrupcion
//#define RESET_PIN 4			//ES EL PIN DE RESET

void Interrupt (void)
{
	pinMode(IN_PIN, OUTPUT);		//PARA QUE LA INTERRUPCION NO CAPTURE EL SIGUIENTE PULSO SE PONE COMO SALIDA
	j=0;
	while(flag_while)
	{
		length = read(fd,(void*)rx_buffer, 255);			//utilizamos la funcion read generica

		if (length > 0)							//si hay dato disponible lo capturamos
		{

			for (i=0;i<length;i++)
			{
				buffer[j] = rx_buffer[i];			//guardamos el buffer parcial
				j++;

			}
			for (i=0;i<j;i++)
			{
				if (buffer[i] == 0x0D)				//se busca el new line
				{
					buffer[i] = '\0';
					//contamos el numero de comas para saber si lo qu enos llega es una tira de datos o no
					count = 0;
					for (j=0;j<i;j++)
					{
						if (buffer[j] == ',')
						{
							count++;
						}
					}
					if (count != 5 && count != 6)
						break;
					j=0;
					flag_while = 0;				//para salir del bucle while porque ya hemos recibido la linea
					//escaneamos la linea recien capturada para coger los campos
					t1 = sscanf(buffer,"%[^','],%s",fecha,datos);		//Coge todo hasta la primera coma y luego el resto
					//printf("%s\n%s\n",fecha,datos);				//imprime lo capturado anteriormete
					t2 = sscanf(fecha,"%d/%d/%d %d:%d",&dia,&mes,&anio,&hora,&minuto);	//recoge los datos del string de la fecha y hora
					//printf("%.2d%.2d%.2d.txt\n",anio,mes,dia);
					sprintf(comando,"mkdir %s/20%.2d",base,anio);
					system(comando);
					sprintf(comando,"mkdir %s/20%.2d/%.2d",base,anio,mes);
					system(comando);
					sprintf(comando,"echo %s >> %s/20%.2d/%.2d/meteo%.2d%.2d%.2d.txt",buffer,base,anio,mes,anio,mes,dia);
					system(comando);
					//printf("%s\n",comando);
					break;
				}
			}
		}
	}
	flag_while = 1;
	pinMode(IN_PIN, INPUT);		//PARA QUE VUELVA A CAPTURAR LA INTERRUPCION AL SALIR


}

int main()
{

	sprintf(comando,"mkdir %s",base);
	system(comando);
	fd = serialOpen("/dev/ttyAMA0",19200);
	if (fd < 0)
	{
		printf ("error abriendo el puerto serie\n");
		return 1;
	}

	if (wiringPiSetup() < 0)
	{
		printf("no se puede habilitar wiringpi\n");
		return 1;
	}
	pinMode(IN_PIN, INPUT);

	if (wiringPiISR(IN_PIN, INT_EDGE_FALLING, &Interrupt) < 0)
	{
		printf ("No se puede setupear la inetrrupcion\n");
		return 1;
	}

	while(caracter != 'q')
	{
		caracter = getc(stdin);
	}


	return 0;
}
