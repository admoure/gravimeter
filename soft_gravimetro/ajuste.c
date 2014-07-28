#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <bcm2835.h>
#include "ad7706.h"

#define RESET_AD RPI_V2_GPIO_P1_07
#define LED	 RPI_V2_GPIO_P1_15

int result1,result2,result3;
char medida1[6],medida2[6],medida3[6];

unsigned char datoh,datol,temp,rerun;
unsigned int dato,sumatorio;

char msg[128];
unsigned char lectura[10];
unsigned char regad;
char chan1[4],chan2[4],chan3[4],muest[6];
char name[5],lat[64],lon[64],alt[64];
float datof1,datof2,datof3;

int main()
{


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

	//HACEMOS LA MEDIDA

	bcm2835_gpio_clr(RESET_AD);
	bcm2835_delay(100);
	bcm2835_gpio_set(RESET_AD);
	bcm2835_delay(500);

	while(1)
	{
		bcm2835_spi_transfer(CLOCK_REGISTER|CHANNEL1|_WRITE);
		bcm2835_spi_transfer(CLKDIS_OFF|CLKDIV_OFF|CLK_ON|HZ50);
		bcm2835_spi_transfer(SETUP_REGISTER|CHANNEL1|_WRITE);
		bcm2835_spi_transfer(SELF_CALIBRATION|GAIN_X1|UNIPOLAR_OP|BUFFER_OFF|FSYNC_OFF);

		bcm2835_delay(500);

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
			datof1 = dato*2500./65536.;
//		result1 = READ_AD_DATA(DATA_REGISTER|_READ|CHANNEL1);
		sprintf(medida1,"%d",dato);

		bcm2835_spi_transfer(CLOCK_REGISTER|CHANNEL2|_WRITE);
		bcm2835_spi_transfer(CLKDIS_OFF|CLKDIV_OFF|CLK_ON|HZ50);
		bcm2835_spi_transfer(SETUP_REGISTER|CHANNEL2|_WRITE);
		bcm2835_spi_transfer(SELF_CALIBRATION|GAIN_X1|UNIPOLAR_OP|BUFFER_OFF|FSYNC_OFF);

		bcm2835_delay(500);

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
			datof2 = dato*2500./65535.;

	//	result2 = READ_AD_DATA(DATA_REGISTER|_READ|CHANNEL2);
		sprintf(medida2,"%d",dato);

		bcm2835_spi_transfer(CLOCK_REGISTER|CHANNEL3|_WRITE);
		bcm2835_spi_transfer(CLKDIS_OFF|CLKDIV_OFF|CLK_ON|HZ50);
		bcm2835_spi_transfer(SETUP_REGISTER|CHANNEL3|_WRITE);
		bcm2835_spi_transfer(SELF_CALIBRATION|GAIN_X1|UNIPOLAR_OP|BUFFER_OFF|FSYNC_OFF);

		bcm2835_delay(500);

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
			datof3 = dato*2500./65535.;

	//	result3 = READ_AD_DATA(DATA_REGISTER|_READ|CHANNEL3);

		sprintf(medida3,"%d",dato);

		printf("canal1 -> %s canal2 -> %s canal3 -> %s      canal1 -> %.4f mV canal2 -> %.4f mV canal3 -> %.4f mv\n",medida1,medida2,medida3,datof1,datof2,datof3);
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


	return 0;
}

