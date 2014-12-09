#!/bin/bash

#
#  Raspberry Pi Camera Control Software
#  Copyright (C) 2014 Cansel Software Limited
#
#   This program is free software: you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation, either version 3 of the License, or
#    any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#
#
CONFIG_FILE=/etc/cansel/mjpg-streamer.cfg
CONFIG_FILE_SECURED=/tmp/mjpg-streamer.cfg

# check if the file contains something we don't want
if egrep -q -v '^#|^[^ ]*=[^;]*' "$CONFIG_FILE"; then
  echo "Config file is unclean, cleaning it..." >&2
  # filter the original to a new file
  egrep '^#|^[^ ]*=[^;&]*'  "$CONFIG_FILE" > "$CONFIG_FILE_SECURED"
  CONFIG_FILE="$CONFIG_FILE_SECURED"
fi

source "$CONFIG_FILE"

THUMBNAILSIZE=100
DISK_FULL=75
FULL_PATH=$BASE_PATH"/"$SAVEDIR;

check_dirs(){
	#does directory exist for snapshots
	if [ ! -d "$FULL_PATH" ]; then
		mkdir $FULL_PATH
    	chown root.www-data $FULL_PATH
		chmod 775 $FULL_PATH
    	chmod g+s $FULL_PATH

	fi

	if [ ! -d "$FULL_PATH/thumbnails" ]; then
    	mkdir "$FULL_PATH/thumbnails"  
    	chown root.www-data $FULL_PATH/thumbnails
		chmod 775 $FULL_PATH/thumbnails
    	chmod g+s $FULL_PATH/thumbnails

	fi
}

#take pics till we terminate
while [ true ]; 
	do
        diskfree=$(df -kh /tmp | tail -1 | awk '{print $5}'| cut -c 1-2)
        diskfree=$(echo "$diskfree" | sed -e 's/\(%\)*$//g')
		if [ "$diskfree" -gt "$DISK_FULL" ]; then
			file_to_delete=$(find $FULL_PATH -type f -printf '%p\n' | sort | head -1)
            rm $file_to_delete
		fi

		filename=$BASE_FILENAME-$(date +"%d%m%Y_%H%M-%S").jpg
		raspistill -o $FULL_PATH/$filename
		check_dirs;
        convert -thumbnail $THUMBNAILSIZE $FULL_PATH/$filename $FULL_PATH/thumbnails/$filename
  
		if [ "$?" -ne "0" ]; then
			exit 1;
		fi
		sleep "$PAUSE_TIME";
		check_dirs;
	done;
