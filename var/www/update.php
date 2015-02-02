<?php
#
#  Raspberry Pi Camera Control Software
#  Copyright (C) 2013-2015 Cansel Software Limited
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

use lib\Camera;

session_start();

spl_autoload_extensions('.php');
spl_autoload_register();

require 'config.php';
require 'lib/password.php';

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
		$firsttime = Camera::firsttime();
		$username = Camera::username();
		$width = Camera::width();
		$height = Camera::height();
		$framerate = Camera::framerate();
		$port = Camera::port();
		$password = Camera::password();
	    // time lapse
		$basefilename = Camera::basefilename();
		$savedir = Camera::savedir();
		$pausetime = Camera::pausetime();
        $basepath = Camera::basepath();
        $webdir = Camera::webdir();

		if(isset($_POST['firsttime']))
			$firsttime = $_POST['firsttime'];
		else
			$firsttime = "1";
		if(isset($_POST['username']))
			$username = $_POST['username'];
		if(isset($_POST['width']))
			$width = $_POST['width'];
		if(isset($_POST['height']))
			$height = $_POST['height'];
		if(isset($_POST['framerate']))
			$framerate = $_POST['framerate'];
		if(isset($_POST['port']))
			$port = $_POST['port'];
		if(isset($_POST['basefilename']))
			$basefilename = $_POST['basefilename'];

		if(isset($_POST['pausetime']))
			$pausetime = $_POST['pausetime'];

		if(isset($_POST['nopass']))
			$nopass = "1"; 
		else
			$nopass = "0";

		if(isset($_POST['timelapse']))
			$timelapse = "1"; 
		else
			$timelapse = "0";

		if(isset($_POST['flipvertical']))
			$flipvertical = "1"; 
		else
			$flipvertical = "0";

		if(isset($_POST['fliphorizontal']))
			$fliphorizontal = "1"; 
		else
			$fliphorizontal = "0";

		if(isset($_POST['sharpness']))
			$sharpness = $_POST['sharpness'];
		else
			$sharpness = "0";

		if(isset($_POST['contrast']))
			$contrast = $_POST['contrast'];
		else
			$contrast ="0";

		if(isset($_POST['brightness']))
			$brightness = $_POST['brightness'];
		else
			$brightness = "50";

		if(isset($_POST['saturation']))
			$saturation = $_POST['saturation'];
		else 
			$saturation ="0";	

		if(isset($_POST['rotation']))
			$rotation = $_POST['rotation'];

		if(!isset($_POST['old_password']) || empty($_POST['old_password'])) {
			$password = Camera::password();
		} else {
			if(isset($_POST['old_password']) && isset($_POST['new_password']) && isset($_POST['new_password1'])) {
				if($password == $_POST['old_password']){
					if ($_POST['new_password'] == $_POST['new_password1']) {
						$password = $_POST['new_password1'];
						if(!Camera::change_pass($_POST['old_password'], $_POST['new_password'])) {
	    	            	$_SESSION['message'] = "Could not update shell password. Save Aborted.";
							header('Location: ' . INDEX. SETTINGS);
							exit();			
						}
					} else {		
    	            	$_SESSION['message'] = "New passwords do not match please try again.";
						header('Location: ' . INDEX. SETTINGS);
						exit();			
					}
				} else {
    	            $_SESSION['message'] = "Old password is incorrect, please try again.";
					header('Location: ' . INDEX. SETTINGS);
					exit();			
				}
			}else{
				$_SESSION['message'] = "All password fields must be filled in to complete password change";
				header('Location: ' . INDEX. SETTINGS);
				exit();			
			}
		}
		
		$save_string = "WIDTH=". $width . "\n" . "HEIGHT=". $height . "\n" . "FRAMERATE=" . $framerate . "\n" . "HTTP_PORT=" . $port . "\n" . "WEB_DIR=" . $webdir . "\n" . "USER=" . $username . "\n" . "PASS=" . $password . "\n" . "BASE_FILENAME=" . $basefilename . "\n" . "SAVEDIR=" . $savedir . "\n" . "PAUSE_TIME=" . $pausetime . "\n" . "TIMELAPSE=" . $timelapse . "\n". "VERTICALFLIP=" . $flipvertical . "\n". "HORIZONTALFLIP=" . $fliphorizontal . "\n" . "ROTATION=" . $rotation . "\n" . "SHARPNESS=" . $sharpness . "\n" . "BRIGHTNESS=" . $brightness . "\n" . "CONTRAST=" . $contrast . "\n" . "SATURATION=" . $saturation . "\n" . "JPG_LIST=stills.txt" . "\n" . "AVI=timelapse.avi" . "\n" . "BASE_PATH=" . $basepath . "\n" . "NOPASS=" . $nopass . "\n" . "FIRSTTIME=" . $firsttime . "\n";


            if (is_writable(CONF_FILE)) {
                $handler = fopen(CONF_FILE, 'w');
                fwrite($handler, $save_string);
                fclose($handler);
				chmod(CONF_FILE, 0740); 

				$db = json_decode(file_get_contents(FILE_PASS));

		        $password = password_hash($password, PASSWORD_BCRYPT);
		        $db->{'password'} = $password;
		        $db->{'user'} = $username;
		        if (is_writable(FILE_PASS)) {
		            $handler = fopen(FILE_PASS, 'w');
		            fwrite($handler, json_encode($db));
		            fclose($handler);
		         } else {
		            $_SESSION['message'] = "Database file \"" . FILE_PASS . "\" is not writable, please check the file owner and rights.";
					header('Location: ' . INDEX. SETTINGS);
					exit();			
            	}
				$_SESSION['success_message'] = "Your Settings have been saved, please restart camera to activated";
            } else {
                $_SESSION['message'] = "File \"" . CONF_FILE . "\" is not writable, please check the file owner and rights.";
            }

}

header('Location: ' . INDEX. SETTINGS);
exit();
?>
