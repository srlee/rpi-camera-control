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
InitVars()
{
    echo -n "* Initializing";
    PRODUCT="canselcamera" 	
    UPDATEMIRRORS="http://downloads.canselsoftware.com/${PRODUCT}/";
    DOWNLOADDIR="/tmp/";
    VERSION_URL="http://downloads.canselsoftware.com/${PRODUCT}/version.php"
    echo "...OK";
}

Update_Repos()
{
	echo -n "* Updating repositories";
	sudo apt-get update 2>/dev/null
    echo "...OK";

}

Upgrade_OS()
{
	echo -n "* Checking for OS Updates";
	sudo apt-get -y upgrade 2>/dev/null
	echo "...OK";
}

RPI_Update()
{
	echo -n "* Running rpi-update";
	sudo rpi-update 2>/dev/null
    echo "...OK";
}




warnRoot()
{
	currentuser=`whoami`;
	if [ "$currentuser" != "root" ]
	then
		echo 
		echo "  This installation script needs to be run as"
		echo "  user \"root\".  You are currenly running ";
		echo "  as $currentuser.  "
		echo 
		echo " Exiting...";
		echo
		exit 1;
	fi
}

getFile()
{
  echo -n "* Downloading Camera Package";
  wget $1 --output-document $2 >/dev/null 2>&1
  if [ "$?" != "0" ] 
  then 
    echo "...Failed to get $1 !";
    exit 1;	
  else
    echo "...OK";
  fi

}

Warning()
{
    echo "";
    echo " NOTICE:  Your Camera  may go offline during this upgrade process!";
    echo "";
    sleep 3;

}

GetLocalVersion()
{
	ver="";
	ver=$(dpkg --status ${PRODUCT} | grep ^Version | cut -d: -f3) >/dev/null 2>/dev/null
}

Upgrade()
{
   echo  -n "* Upgrading"
   Update_Repos;
   dpkg -i --force-confold ${DOWNLOADDIR}${PRODUCT}_${latest}.deb >/dev/null 2>&1
   apt-get -yf install 2>/dev/null
   if [ "$?" != "0" ]
   then
   		echo "... FAILED"
   else
        echo "... Done!"
   fi   
}

CheckForUpgrade()
{
	echo -n "* Checking latest version ";
	latest=`wget -O - ${VERSION_URL} 2>/dev/null`
	#latest=`wget -O - ${VERSION_URL}`
	if [ "$latest" != "" ]
	then
		echo $latest;
		
		if [ "$latest" != "$ver" ]
		then
			blDownloaded=0;
			for url in $UPDATEMIRRORS
			do
				if [ "$blDownloaded" = "0" ] 
				then
					u="${url}${PRODUCT}_${latest}.deb";
					lf="${DOWNLOADDIR}${PRODUCT}_${latest}.deb"
					getFile $u "$lf";	
					if [ -f "$lf" ]
					then
						Upgrade;
					fi
				fi
			done	
		else
			echo "";
			echo " Your $PRODUCT server is up to date";
			echo "";
		fi
	else
		echo "Unable to determine latest version.";
	fi
}

if [ $# -eq 0 ]
then
	clear;
	echo " * Upgrading $PRODUCT"
	Warning;
	warnRoot;
	InitVars;
	Update_Repos;
	Upgrade_OS;
	RPI_Update;
	GetLocalVersion;
	CheckForUpgrade;
	echo "About to reboot...";
	sleep 5
	reboot
	echo "Done!"
else
	InitVars;
	$1 $2 $3 $4
fi


