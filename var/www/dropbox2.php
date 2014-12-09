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


if ( isset( $_GET['oauth_token'] ) && isset( $_GET['uid'] ) && isset( $_SESSION['myapp'] ) ) {

	$dropboxapikey = Cloud::dropbox_api_key();
	$dropboxapisecret = Cloud::dropbox_api_secret();
	
	$ch = curl_init(); 
	
	$headers = array( 'Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="' . $dropboxapikey . '", oauth_token="'  .$_GET['oauth_token'] . '", oauth_signature="' . $dropboxapisecret . '&' . $_SESSION['myapp']['oauth_request_token_secret'] . '"' );

	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers ); 
	
	curl_setopt( $ch, CURLOPT_URL, "https://api.dropbox.com/1/oauth/access_token" );  
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );  

	$access_token_response = curl_exec( $ch );
	
	parse_str( $access_token_response, $parsed_access_token );


	$json_access = json_decode( $access_token_response );

	if ( isset( $json_access->error ) ) {
		$_SESSION['message'] = 'DropBox FATAL ERROR: ' . $json_access->error;

	} else {
	
		$_SESSION['myapp']['uid'] = $parsed_access_token['uid'];
		$_SESSION['myapp']['oauth_access_token'] = $parsed_access_token['oauth_token'];
		$_SESSION['myapp']['oauth_access_token_secret'] = $parsed_access_token['oauth_token_secret'];
	

		$dropboxapikey = Cloud::dropbox_api_key();
		$dropboxapisecret = Cloud::dropbox_api_secret();
		$dropboxsynctime = Cloud::dropbox_sync_time();

		$save_string = "DROPBOX_API_KEY=". $dropboxapikey . "\n" . "DROPBOX_API_SECRET=". $dropboxapisecret . "\n" . "DROPBOX_ACCESS_TOKEN=" . $_SESSION['myapp']['oauth_access_token'] . "\n" . "DROPBOX_ACCESS_TOKEN_SECRET=" . $_SESSION['myapp']['oauth_access_token_secret'] . "\n". "DROPBOX_SYNC_TIME=" . $dropboxsynctime . "\n";


    	if (is_writable(Cloud::conf_file())) {
    	    $handler = fopen(Cloud::conf_file(), 'w');
    	    fwrite($handler, $save_string);
    	    fclose($handler);
			chmod(Cloud::conf_file(), 0740); 
			$_SESSION['success_message'] = "Your Cloud settings have been saved, you are now connected to your dropbox account.";
    	} else {
    	    $_SESSION['message'] = "File \"" . Cloud::conf_file() . "\" is not writable, please check the file owner and rights.";
    	}
	}
	header( 'Location: ' . INDEX . CLOUD );
	
}
