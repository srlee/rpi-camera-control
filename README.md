rpi-camera-control
==================

Raspberry Pi Camera Control Software is a collection of scripts and php that allow you to access  most settings of the raspberry pi camera through a web interface. 

Giving you the ability to control video capture for time lapsed photography that converts directly to video files with a single command. Move storage to USB just by plugging in any USB storage device. Run streaming video with user selectable frame rates or just take snap shots like a still camera.

RPI Camera – Feature List
Camera Settings
- Adjust width and height of video
- Video frame rate
- Flip video horizontal
- Flip video vertical
- Rotate video 0, 90, 180, 270
- Edit port streamer connects on
- Sharpness
- Contrast
- Brightness
- Saturation

Time Lapse Settings
- Modify file name of pictures
- Pause between pictures (default 4 minutes)
- Create video from time lapse pictures

Storage
- Move storage to USB by simply plugging in a USB storage device
- Dropbox cloud storage integration
- Delete time lapse pictures and videos from web interface

Support for DHT-22 humidity and Temperature sensor
- Commandline utility for the sensor
- displays temperature and humidity for the room the camera is in.

Web Interface Utilities
- Reboot Raspberry Pi
- Shut Down Raspberry Pi
- Restart Camera software
- Upgrade OS
- Change and sync password for streaming video and web access
- Upgrade Camera software

Display OS details
- OS Version
- uptime of the camera
- Amount of ram being used
- CPU Load
- CPU Temperature
- storage space available and used
- Network information including data usage
- Current logged in users
- Current IP address both public and private

Other Features
- Complete WIFI command line setup utility – searches for access point etc.
- Interface is designed for desktop, tablets and mobile devices
- IPV6 support
- Console welcome screen displays current IP and information on accessing Camera
- Based on the latest Raspbian build (default OS for raspberry Pi)
- Available as a complete OS solution for your current Raspberry pi (unzip to SD card) or as a debian package.
- Upgrade Camera software, OS and firmware with one command from command line.

Spec
- Using mjpg_streamer and input_raspicam (other plugins available in software)
- Latest libwiringPi included
