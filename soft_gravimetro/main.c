#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <bcm2835.h>
#include "ad7706.h"

#define RESET_AD RPI_V2_GPIO_P1_07
#define LED	 RPI_V2_GPIO_P1_15


float r1 = 32.67;
float r2 = 6.7;

unsigned char datoh,datol,temp,rerun;
unsigned int dato,sumatorio;

char msg[128];
char base[256]={"/media/CAM/datalogger/gravimetro"};
char file_configura[256]={"/home/pi/soft_gravimetro/configura.txt"};
FILE *fc;
FILE *fp;
unsigned char lectura[10];
unsigned char regad;
char horas[3],minutos[3],segundos[3],dia[3],mes[3],anio[3],aniog[5];
int hours,minutes,seconds,year,day,month,yearg;
char chan1[4],chan3[4],chan2[4],muest[6];
char name[5],lat[64],lon[64],alt[64];
char check[4],server[64],port[16],folder[64];

int result1,result2,result3;
float volch1,volch2,volch3;

char medida1[6],medida2[6],medida3[6];

char vol1[10],vol2[10],vol3[10];


int main()
{
	fc = fopen(file_configura,"r");
	if (fc == NULL)
	{
		printf("el fichero no existe\n");
		return 1;
	}

	fscanf(fc,"NAME=%s\n",name);
	fscanf(fc,"CHANNEL1=%s\n",chan1);
	fscanf(fc,"CHANNEL2=%s\n",chan2);
	fscanf(fc,"CHANNEL3=%s\n",chan3);
	fscanf(fc,"MUESTREO=%s\n",muest);
	fscanf(fc,"LAT=%s\n",lat);
	fscanf(fc,"LON=%s\n",lon);
	fscanf(fc,"ALT=%s\n",alt);
	fscanf(fc,"SERVERCHECK=%s\n",check);
	fscanf(fc,"SERVER=%s\n",server);
	fscanf(fc,"PORT=%s\n",port);
	fscanf(fc,"FOLDER=%s\n",folder);
	fclose(fc);

	printf("nombre > %s\n",name);
	printf("CANAL 1 > %s\n",chan1);
	printf("CANAL 2 > %s\n",chan2);
	printf("CANAL 3 > %s\n",chan3);
	printf("muestreo > %s\n",muest);
	printf("latitud > %s\n",lat);
	printf("longitud > %s\n",lon);
	printf("altitud > %s\n",alt);
	printf("SERVERCHECK > %s\n",check);
	printf("server > %s\n",server);
	printf("port > %s\n",port);
	printf("folder > %s\n",folder);




	if(!bcm2835_init())
		return 1;

	bcm2835_gpio_fsel(LED,BCM2835_GPIO_FSEL_OUTP);
	bcm2835_gpio_fsel(RESET_AD,BCM2835_GPIO_FSEL_OUTP);

	bcm2835_gpio_set(RESET_AD);

	//se habilita el spi
	bcm2835_spi_begin();
	bcm2835_spi_setDataMode(BCM2835_SPI_MODE0);
	bcm2835_spi_setClockDivider(BCM2835_SPI_CLOCK_DIVIDER_128);
	//se habilita el i2c
//	bcm2835_i2c_begin();
//	bcm2835_i2c_setClockDivider(BCM2835_I2C_CLOCK_DIVIDER_626);
//	bcm2835_i2c_setSlaveAddress(0x50);


	bcm2835_gpio_set(LED);			//COMIENZA LA MEDIDA
	//TOMAMOS LA HORA DE LA MEDIDA//HAY QUE HACER CONTROL DE CALIDAD

//	regad = 0x02;
//	bcm2835_i2c_read_register_rs(&regad,lectura,5);

	fp = popen("date -u +%S","r");
	fgets(segundos,3,fp);
	pclose(fp);
	fp = popen("date -u +%M","r");
	fgets(minutos,3,fp);
	pclose(fp);
	fp = popen("date -u +%H","r");
	fgets(horas,3,fp);
	pclose(fp);
	fp = popen("date -u +%y","r");
	fgets(anio,3,fp);
	pclose(fp);
	fp = popen("date -u +%Y","r");
	fgets(aniog,5,fp);
	pclose(fp);
	fp = popen("date -u +%m","r");
	fgets(mes,3,fp);
	pclose(fp);
	fp = popen("date -u +%d","r");
	fgets(dia,3,fp);
	pclose(fp);
	seconds=atoi(segundos);
	minutes=atoi(minutos);
	hours=atoi(horas);
	month=atoi(mes);
	year=atoi(anio);
	yearg=atoi(aniog);
	day=atoi(dia);

	//HACEMOS LA MEDIDA


	bcm2835_gpio_clr(RESET_AD);
	bcm2835_delay(100);
	bcm2835_gpio_set(RESET_AD);
	bcm2835_delay(500);

	if (strcmp(chan1,"ON")==0)
	{
		bcm2835_spi_transfer(CLOCK_REGISTER|CHANNEL1|_WRITE);
		bcm2835_spi_transfer(CLKDIS_OFF|CLKDIV_OFF|CLK_ON|HZ50);
		bcm2835_spi_transfer(SETUP_REGISTER|CHANNEL1|_WRITE);
		bcm2835_spi_transfer(SELF_CALIBRATION|GAIN_X1|UNIPOLAR_OP|BUFFER_OFF|FSYNC_OFF);

		bcm2835_delay(500);
		sumatorio = 0;
		for (rerun=0;rerun<32;rerun++)
		{
			do{
				temp = (COMMUNICATION_REGISTER|_READ|CHANNEL1);
				bcm2835_spi_transfer(temp);
				temp = bcm2835_spi_transfer(0);
				//printf("DRDY -> %x\n",temp);
			}while((temp&0x80)!=0);
			temp = (DATA_REGISTER|_READ|CHANNEL1);
			bcm2835_spi_transfer(temp);
			datoh = bcm2835_spi_transfer(0);
			datol = bcm2835_spi_transfer(0);
	//		printf("%x %x\n",datoh,datol);
			dato = (unsigned int)datoh * 256 + (unsigned int)datol;
			sumatorio = sumatorio + (unsigned int)dato;
		}
	
		result1 = sumatorio/32;
		volch1 = (result1*(2500./65535.)-1250)*8;
		printf("voltaje ch1 = %.2f\n",volch1);
//		result1 = READ_AD_DATA(DATA_REGISTER|_READ|CHANNEL1);
		sprintf(medida1,"%d",result1);
		sprintf(vol1,"%.2f",volch1);

	}
	else
	{
		strcpy(medida1,"?");
		strcpy(vol1,"?");
	}


	if (strcmp(chan2,"ON")==0)
	{
		bcm2835_spi_transfer(CLOCK_REGISTER|CHANNEL2|_WRITE);
		bcm2835_spi_transfer(CLKDIS_OFF|CLKDIV_OFF|CLK_ON|HZ50);
		bcm2835_spi_transfer(SETUP_REGISTER|CHANNEL2|_WRITE);
		bcm2835_spi_transfer(SELF_CALIBRATION|GAIN_X1|UNIPOLAR_OP|BUFFER_OFF|FSYNC_OFF);

		bcm2835_delay(500);
		sumatorio = 0;
		for (rerun=0;rerun<32;rerun++)
		{
			do{
				temp = (COMMUNICATION_REGISTER|_READ|CHANNEL2);
				bcm2835_spi_transfer(temp);
					temp = bcm2835_spi_transfer(0);
				//printf("DRDY -> %x\n",temp);
			}while((temp&0x80)!=0);
			temp = (DATA_REGISTER|_READ|CHANNEL2);
			bcm2835_spi_transfer(temp);
			datoh = bcm2835_spi_transfer(0);
			datol = bcm2835_spi_transfer(0);
	//		printf("%x %x\n",datoh,datol);
			dato = (unsigned int)datoh * 256 + (unsigned int)datol;
			sumatorio = sumatorio + (unsigned int)dato;
		}
		result2 = sumatorio/32;
		volch2 = result2*(2.5/65535.);
		printf ("voltajte ch2 = %.2f\n",volch2);
	//	result2 = READ_AD_DATA(DATA_REGISTER|_READ|CHANNEL2);
		sprintf(medida2,"%d",result2);
		sprintf(vol2,"%.2f",volch2);

	}
	else
	{
		strcpy(medida2,"?");
		strcpy(vol2,"?");
	}

	if (strcmp(chan3,"ON")==0)
	{
		bcm2835_spi_transfer(CLOCK_REGISTER|CHANNEL3|_WRITE);
		bcm2835_spi_transfer(CLKDIS_OFF|CLKDIV_OFF|CLK_ON|HZ50);
		bcm2835_spi_transfer(SETUP_REGISTER|CHANNEL3|_WRITE);
		bcm2835_spi_transfer(SELF_CALIBRATION|GAIN_X1|UNIPOLAR_OP|BUFFER_OFF|FSYNC_OFF);

		bcm2835_delay(500);
		sumatorio = 0;
		for (rerun=0;rerun<32;rerun++)
		{
			do{
				temp = (COMMUNICATION_REGISTER|_READ|CHANNEL3);
				bcm2835_spi_transfer(temp);
				temp = bcm2835_spi_transfer(0);
				//printf("DRDY -> %x\n",temp);
			}while((temp&0x80)!=0);
			temp = (DATA_REGISTER|_READ|CHANNEL3);
			bcm2835_spi_transfer(temp);
			datoh = bcm2835_spi_transfer(0);
			datol = bcm2835_spi_transfer(0);
	//		printf("%x %x\n",datoh,datol);
			dato = (unsigned int)datoh * 256 + (unsigned int)datol;
			sumatorio = sumatorio + (unsigned int)dato;
		}
		result3 = sumatorio/32;
		volch3 = result3*(2.5/65535.)*((r1+r2)/r2);
		printf("voltaje ch3 = %.2f\n",volch3);
	//	result3 = READ_AD_DATA(DATA_REGISTER|_READ|CHANNEL3);

		sprintf(medida3,"%d",result3);
		sprintf(vol3,"%.2f",volch3);

	}
	else
	{
		strcpy(medida3,"?");
		strcpy(vol3,"?");
		printf("no se que es esto ->%s<-\n",chan3);

	}


/*	temp = (DATA_REGISTER|_READ|CHANNEL1);
	printf("vamos a leer aqui %.2x\n",temp);
	bcm2835_spi_transfer(temp);
	datol = bcm2835_spi_transfer(0);
	datoh = bcm2835_spi_transfer(0);
	result = (unsigned int)datoh * 256 + (unsigned int)datol;
*/
//	printf("el resultado es %d\n",result1);
	bcm2835_gpio_clr(LED);			//COMIENZA LA MEDIDA

	bcm2835_close();

	//La medida hecha hay que guardarla en un fichero de texto
	sprintf(msg,"mkdir %s/%.4d",base,yearg);
	system(msg);
//	printf("%s\n",msg);
	sprintf(msg,"mkdir %s/%.4d/%.2d",base,yearg,month);
	system(msg);
//	printf("%s\n",msg);
//	sprintf(msg,"mkdir %s/original",base);
//	system(msg);
	sprintf(msg,"echo \"%.2d %.2d %.2d  %.2d %.2d %.2d  %s  %s  %s  %s  %s  %s \" >> %s/%.4d/%.2d/%.2d%.2d%.2d.txt",year,month,day,hours,minutes,seconds,medida1,medida2,medida3,vol1,vol2,vol3,base,yearg,month,year,month,day);
	printf("%s\n",msg);
	system(msg);


	return 0;
}

