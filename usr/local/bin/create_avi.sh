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

FULL_PATH=$BASE_PATH"/"$SAVEDIR;
MOVIE_PATH=$BASE_PATH"/movies";
#does directory exist for snapshots
if [ ! -d "$MOVIE_PATH" ]; then
	mkdir $MOVIE_PATH 
    chown root.www-data $MOVIE_PATH
	chmod 775 $MOVIE_PATH 
    chmod g+s $MOVIE_PATH 
fi

rm -rf  $JPG_LIST
ls $FULL_PATH/*.jpg > $JPG_LIST
mencoder -nosound -ovc lavc -lavcopts vcodec=mpeg4:vbitrate=8000000 -vf scale=$WIDTH:$HEIGHT -o $MOVIE_PATH/$AVI -mf type=jpeg:fps=24 mf://@$JPG_LIST
