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

use lib\Cloud;
use lib\Rbpi;

session_start();

require 'config.php';

spl_autoload_extensions('.php');
spl_autoload_register();


if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
	$dropboxapikey = Cloud::dropbox_api_key();
	$dropboxapisecret = Cloud::dropbox_api_secret();
	if(isset($_POST['action'])) {
		if($_POST['action'] == "unlink" || $_POST['action'] == "synctime") {

			if($_POST['action'] == "synctime") {
				$dropboxsynctime = $_POST['synctime'];
				$dropboxaccesstoken = Cloud::dropbox_access_token();
				$dropboxaccesstokensecret = Cloud::dropbox_access_token_secret();
				$save_string = "DROPBOX_API_KEY=". $dropboxapikey . "\n" . "DROPBOX_API_SECRET=". $dropboxapisecret . "\n" . "DROPBOX_ACCESS_TOKEN=" . $dropboxaccesstoken . "\n" . "DROPBOX_ACCESS_TOKEN_SECRET=" . $dropboxaccesstokensecret . "\n" . "DROPBOX_SYNC_TIME=" . $dropboxsynctime . "\n";
			} else {
				$save_string = "DROPBOX_API_KEY=". $dropboxapikey . "\n" . "DROPBOX_API_SECRET=". $dropboxapisecret . "\n" .
"DROPBOX_ACCESS_TOKEN=\n" . "DROPBOX_ACCESS_TOKEN_SECRET=\n" . "DROPBOX_SYNC_TIME=" . $dropboxsynctime . "\n";
			}

    		if (is_writable(Cloud::conf_file())) {
    		    $handler = fopen(Cloud::conf_file(), 'w');
    		    fwrite($handler, $save_string);
    		    fclose($handler);
				chmod(Cloud::conf_file(), 0740); 
				$_SESSION['success_message'] = "Your Cloud settings have been saved, you are now connected to your dropbox account.";
    		} else {
    		    $_SESSION['message'] = "File \"" . Cloud::conf_file() . "\" is not writable, please check the file owner and rights.";
    		}

			header( 'Location: ' . INDEX . CLOUD );
		}
	} else {
		$ch = curl_init(); 

		$headers = array( 'Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="' . 	$dropboxapikey . '", oauth_signature="' . $dropboxapisecret . '&"' );

		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers ); 
		curl_setopt( $ch, CURLOPT_URL, "https://api.dropbox.com/1/oauth/request_token" );  
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );  
		$request_token_response = curl_exec( $ch );

		parse_str( $request_token_response, $parsed_request_token );

		$json_access = json_decode( $request_token_response );

		if ( isset( $json_access->error ) ) {
			$_SESSION['message'] = 'DropBox FATAL ERROR: ' . $json_access->error;
			die();
		}
		$_SESSION['myapp'] = array();
		$_SESSION['myapp']['oauth_request_token'] = $parsed_request_token['oauth_token'];
		$_SESSION['myapp']['oauth_request_token_secret'] = $parsed_request_token['oauth_token_secret'];

		header( 'Location: https://www.dropbox.com/1/oauth/authorize?oauth_token=' . $parsed_request_token['oauth_token'] . '&oauth_callback=http://' . $_SERVER['HTTP_HOST'] . '/dropbox2.php' );
	}
}
