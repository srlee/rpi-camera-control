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

spl_autoload_extensions('.php');
spl_autoload_register();

session_start();

use lib\Camera;


/*<img src="streamer.php"> */ 


if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
	session_write_close();
	$server = "localhost"; 
	$port = Camera::port();
	$url = "/?action=stream"; 
	set_time_limit(0);  
	$username = Camera::username();
	$password = Camera::password();

	$fp = fsockopen($server, $port, $errno, $errstr, 30); 
	if (!$fp) { 
        $_SESSION['message'] = "streamer.php:" . $errstr ."(".$errno.")";
	} else { 

    	$credentials = $username . ":" . $password;
    	$base64EncodedCredentials = base64_encode($credentials);  
    
		fputs ($fp, "GET ".$url." HTTP/1.0\r\n"); 
		fputs($fp,"Authorization: Basic " . $base64EncodedCredentials . "\r\n\r\n");
		while ($str = trim(fgets($fp, 4096))) 
		header($str);
		while (!feof($fp)) {
			if (Camera::should_stop()) 
				break;
			else
				print fread($fp, 4096);
		}
		fclose($fp);
	} 
	exit;
}
?> 
