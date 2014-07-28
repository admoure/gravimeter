#!/bin/bash

gpio mode 4 out
gpio write 4 1
sleep 1s
gpio write 4 0
