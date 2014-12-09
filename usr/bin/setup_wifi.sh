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
INTERFACES=/etc/network/interfaces
APINFO="/tmp/apinfo.tmp"
AP_LIST="/tmp/ap_list.txt"
CONNECTED=0

function network_up_test
{

	{
	sudo ifdown wlan0 2> /dev/null
        echo 25
    	sleep 2 
    	sudo ifup wlan0 2> /dev/null
	echo 75
    	sleep 10
	echo 100
        ping -c 2 8.8.8.8 2>&1 | egrep -c "\<unknown\>|\<unreachable\>" > /tmp/status
	} | whiptail --title "WIFI Setup" --gauge "\nStarting Networking, please wait" 10 60 0
        
	status=$(cat /tmp/status)
    sleep 5
    if [ $status -eq 0 ]; then
		whiptail --title "WIFI Setup" --msgbox "Network Connected" 10 50 1
    	CONNECTED=1;
	else
    	CONNECTED=0;
		whiptail --title "WIFI Setup" --msgbox "Could not connect, please try again" 10 50 1
    fi
}

function collect_wifi_info
{
    # removing possible previous temp file
    rm $APINFO 2>/dev/null
    rm /tmp/cell* 2>/dev/null

    COUNTER=0
    until [ $COUNTER -gt 5 ]; do
        let COUNTER+=1 
  		iwlist wlan0 scan 2>/dev/null > $APINFO
        if [ -z "$APINFO" ]; then
             sleep 2
        else
			# put each cell in its own file	
        	awk '/Cell/{n++}{print >"cell" n ".txt" }' $APINFO
    		break 
        fi
    done
}

function wifi_test
{
	  	unset list
 		COUNTER=0
 	{
        while (true)
		do
	        let COUNTER+=1 
	        # scans for wifi connections & isolates wifi AP name
	        iwlist wlan0 scan 2>/dev/null | awk -F":" '/ESSID/{print $2}' > $AP_LIST
		    sleep 1		    
            echo $(($COUNTER * 20))
 		    # tests for number of wifi connections, exits if none
   		    LINES=`wc -l < /tmp/ap_list.txt`
			if [ $LINES == 0 ]; then
	            if [ $COUNTER -gt 5 ]; then
	   	            CONNECTED=0
                    break 
	            fi
	        else
		      break
			fi
		done
        #progress to 100%
		echo 100
		sleep 1
	
	}| whiptail --title "WIFI Setup" --gauge "\nScanning WIFI, please wait" 10 60 0

	LINES=`wc -l < $AP_LIST`
	eval list=(`cat $AP_LIST`)		

	if [ $LINES == 0 ]; then
		whiptail --msgbox "No available WIFI connection" 10 50 1
	else
		echo "whiptail --title \"Choose SSID, or enter manually\" \\" > /tmp/choose_ap.sh
		echo "--radiolist \"Choose SSID\" \\" >> /tmp/choose_ap.sh

		LINES=$((${LINES}+1))

		echo "10 60 ${LINES} \\" >> /tmp/choose_ap.sh
		for LINE in "${list[@]}"
		    do
		  	    echo "$LINE '' off \\" >> /tmp/choose_ap.sh
		    done
		echo "Enter\ manually '' on 2>/tmp/ssid.ans" >>/tmp/choose_ap.sh
	    
		chmod 777 /tmp/choose_ap.sh
		. /tmp/choose_ap.sh
        # user cancelled  
		if [ $? -ne 0 ]; then exit 0; fi
	fi

}
function check_enc_key
{
    # tests for encryption key
    need_key=$(grep key: $APINFO | sed 's/.*key://g')
    need_key=${need_key:0:2}
    echo $need_key
    sleep 5   
    if [ "$need_key" == "on" ]; then
        # we need a key so keep going until they give us one  
        until [ -n "$key" ]; do
	  	    key=$(whiptail --title "WIFI Setup" --inputbox "\nWPA Passphrase for $wifi" 10 60 3>&1 1>&2 2>&3)
            # user cancelled  
  			if [ $? -ne 0 ]; then exit 0; fi
            # remove white space
            key=$(echo $key | sed 's/ *$//g')
	    	if [ -z "$key" ]; then
	    	    whiptail --title "WIFI Setup" --msgbox "Encryption key is required to connect to $wifi" 20 60 1
	    	else
        	    key=$(wpa_passphrase "$wifi" $key)
        	    key=$(echo $key | sed 's/.*psk=//g')
        	    key=$(echo $key | sed 's/ }//g')
        	fi
        done
    fi
 }

function check_enc_algorithm
{
    # checks encryption algorithm
    IE=$(grep 'IE: WPA' $APINFO | sed 's/.*IE: //g')
    IE=${IE:0:13}
    echo $IE
    sleep 5
    if [ "$IE" == "WPA Version 1" ]; then
        PROTO="WPA"
    fi

    IE=$(grep 'IE: IEEE' $APINFO | sed 's/.*IE: //g')
    IE=${IE:0:27}
    if [ "$IE" == "IEEE 802.11i/WPA2 Version 1" ]; then
        PROTO="WPA2"
    fi
}

function backup_iface_file
{
    # backup interfaces file by date
    #echo "Reconfiguring interfaces..."
    BACKUP_FILE=$INTERFACES.backup.$(date | sed 's/ /_/g')
    sudo cp $INTERFACES $BACKUP_FILE
}

function check_mode
{
    # test for mode, if mode = master, sets MODE variable to managed
    mode=$(grep Mode $APINFO | sed 's/.*Mode://g')
    mode=${mode:0:6}
    echo $mode
    #sleep 5
    if [ "$mode" == "Master" ]; then
        mode="Managed"
        UNSUPPORTED=0
    else
        UNSUPPORTED=1
        whiptail --title "WIFI Setup" --msgbox "Unsupported mode. please try again" 20 60 1
    fi
}
function check_channel
{
    # sets channel as value for CHANNEL variable
    channel=$(grep Channel: $APINFO | sed 's/.*Channel://g')
    channel=$(echo $channel | sed 's/)//g')
	#echo $channel

}
function get_essid
{
    wifi=$(grep ESSID: $APINFO | sed 's/ESSID://g')
    wifi=$(echo $wifi | sed 's/\"//g')
}

function write_wifi_with_key_and_static
{
    # add new wpa-settings with encryption
    # set to dhcp
    echo "# The loopback network interface
auto lo
iface lo inet loopback

# The primary network interface
auto eth0
iface eth0 inet dhcp

auto wlan0
iface wlan0 inet static
address $ip_choice
netmask $netmask_choice
broadcast $broadcast_choice
gateway $gateway_choice
wpa-ap-scan 1
wpa-driver wext
wpa-passphrase $key 
wpa-key-mgmt WPA-PSK
wpa-proto $PROTO 
wpa-ssid $wifi" > $INTERFACES
}

function write_wifi_with_key
{
    # add new wpa-settings with encryption
    # set to dhcp
    echo "# The loopback network interface
auto lo
iface lo inet loopback

# The primary network interface
auto eth0
iface eth0 inet dhcp

auto wlan0
iface wlan0 inet dhcp
wpa-ap-scan 1
wpa-driver wext
wpa-passphrase $key 
wpa-key-mgmt WPA-PSK
wpa-proto $PROTO 
wpa-ssid $wifi" > $INTERFACES
}

function write_wifi_without_key
{

    # no encryption key
    # sets the wireless configuration for non WPA: essid, channel and mode
    echo "# The loopback network interface        
auto lo
iface lo inet loopback

# The primary network interface
auto eth0
iface eth0 inet dhcp

auto wlan0

iface wlan0 inet dhcp
wpa-key-mgmt NONE
wpa-ssid $wifi" > $INTERFACES
}



static_ip(){

	#Retreive Informations from Actual Network Configuration
	#NOTE :If you install French Locales addr becomes adr and Mask becomes Masque 
	ipaddress=$(ifconfig wlan0 |tail -n7|head -n1|awk '{print $2}'|sed -e 's/'addr:'//'|sed -e 's/'adr:'//') 
	netmask=$(ifconfig wlan0 |tail -n7|head -n1|awk '{print $4}'|sed -e 's/'Mask:'//'|sed -e 's/'Masque:'//')
	broadcast=$(ifconfig wlan0 |tail -n7|head -n1|awk '{print $3}'|sed -e 's/'Bcast:'//')
	gateway=$(ip -4 route list 0/0 |awk '{ print $3 }')

	ip_choice=$(whiptail --inputbox "Enter your new ip address - Your current IP Address is :"$ipaddress 0 0 $ipaddress 3>&1 1>&2 2>&3)
	netmask_choice=$(whiptail --inputbox "Enter your netmask - Your current netmask is :"$netmask 0 0 $netmask 3>&1 1>&2 2>&3)
	gateway_choice=$(whiptail --inputbox "Enter your gateway - Your current gateway is :"$gateway 0 0 $gateway 3>&1 1>&2 2>&3)
	broadcast_choice=$(whiptail --inputbox "Enter your broadcast address - current broadcast is :"$broadcast 0 0 $broadcast 3>&1 1>&2 2>&3)

}

function write_iface_file
{
    if [ "$IE" == "WPA Version 1" ] || [ "$IE" == "IEEE 802.11i/WPA2 Version 1" ]; then
		if [ -z "$ip_choice" ]; then
			write_wifi_with_key	
		else
			write_wifi_with_key_and_static
		fi
    else
		write_wifi_without_key
	fi
}

function pick_wifi
{
    while [ "$CONNECTED" -eq 0 ]; do
		unset ip_choice
        unset key
        unset item
        unset wifi
        wifi_test
 	    # sets essid as value for WIFI variable and displays information about the AP
 		wifi=(`cat /tmp/ssid.ans`)

        while (true)
		do
			count=$[$count +1]
			echo $count
			if [ -f /tmp/cell${count}.txt ]; then
                # remove quotes 
 			    sed -i 's/\"//g' /tmp/cell${count}.txt
       	    	sed -i '/IE: Unknown/d' /tmp/cell${count}.txt
                # find SSID
    			ap=$(grep ESSID /tmp/cell${count}.txt | sed 's/.*ESSID://g')
                #echo $ap
                #echo $wifi   
                #sleep 5  
				if [ "$ap" == "$wifi" ]; then
                    APINFO="/tmp/cell${count}.txt" 
                    FOUND=true
                    #echo ${APINFO}
                    #sleep 5  
                    break;
                fi
  			else
				break
			fi

		done
        if $FOUND ; then
		    check_channel
		    check_mode
    	    if [ "$UNSUPPORTED" -eq 0 ]; then
		        check_enc_key
		        check_enc_algorithm
				whiptail --yesno "Do you want to configure a static ip?" 0 0
				if [ "$?" = "0" ]; then
					static_ip "wlan0";
				fi
		        backup_iface_file
		        write_iface_file
    	        network_up_test
    	    else 
    	        CONNECTED=0
    	    fi
		else
    	   CONNECTED=0
		fi
        break
   done
}

################################
############### Work starts here
################################

#turn down debugging, could not find how to turn off extra broadcom messages
dmesg -n3

#remove old files
rm -rf $APINFO
rm -rf $AP_LIST

whiptail --yesno "WARNING - This will replace your current network configuration \n Do you want to continue?" 0 0
#Warning

if [ "$?" = "0" ]; then
	collect_wifi_info
	pick_wifi
	dmesg -n6
	if [ "$CONNECTED" -eq 1 ]; then
		exit 0
	else 
	    exit 1
	fi
fi

