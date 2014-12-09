<?php
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
namespace lib;

class Cloud {

	private static $config_file = "/etc/cansel/cloud.cfg";
	
	private static function getConfigVar($var){
		$f = fopen(Cloud::$config_file, "r");
		while ( $line = fgets($f, 1000) ) {
			if( substr($line,0,strlen($var)) == $var){
				$arr = explode("=",$line);
				return rtrim($arr[1]);
			}
		}
	}

	public static function conf_file(){
		return Cloud::$config_file;
	}

	// Get Dropbox api key
	public static function dropbox_api_key(){
		return Cloud::getConfigVar("DROPBOX_API_KEY");
	}

	public static function dropbox_api_secret(){
		return Cloud::getConfigVar("DROPBOX_API_SECRET");
	}

	public static function dropbox_access_token(){
		return Cloud::getConfigVar("DROPBOX_ACCESS_TOKEN");
	}

	public static function dropbox_access_token_secret(){
		return Cloud::getConfigVar("DROPBOX_ACCESS_TOKEN_SECRET");
	}

	public static function dropbox_sync_time(){
		$time = Cloud::getConfigVar("DROPBOX_SYNC_TIME");
		if($time == "" || $time == "0")
			$time = "15";
		return $time;
	}

}

?>




