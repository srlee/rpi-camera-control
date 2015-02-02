<?php

namespace lib;

require_once 'lib/phpseclib/Net/SSH2.php';

class Camera {

	private static $config_file = "/etc/cansel/mjpg-streamer.cfg";
	private static $ver_file = "/tmp/version.php";
	private static $change_log = "/tmp/CHANGELOG";

	public static function write_file($file, $text) {
		$handler = fopen($file, 'w');
        fwrite($handler, $text);
        fclose($handler);
  }
	// change shell password
	public static function change_pass($oldpass, $pass){
		$ssh = new \Net_SSH2('localhost');
		if($ssh->login("pi", $oldpass)) {
			if($ssh->exec("echo -e '" . $pass . "\n" . $pass . "' | sudo passwd pi"))
				return true;
			else
				return false;
		} else {
			return false;
		}

	}

	public static function sudoers_enabled() {
		if (file_exists('/etc/sudoers.d/reboot')) 
			return true;
		else
			return false;
	}
	public static function remove_sudoers() {
		global $ssh;

		$output = $ssh->exec("sudo rm -rf /etc/sudoers.d/reboot");
		$output = $ssh->exec("sudo rm -rf /etc/sudoers.d/shutdown");
		$output = $ssh->exec("sudo rm -rf /etc/sudoers.d/camera");
		$output = $ssh->exec("sudo rm -rf /etc/sudoers.d/create_avi");
		$output = $ssh->exec("sudo rm -rf /etc/sudoers.d/camera-restart");
		$output = $ssh->exec("sudo rm -rf /etc/sudoers.d/camera-update");
	}

	public static function update_sudoers() {
		global $ssh;

		Camera::write_file("/tmp/reboot","www-data  ALL=(ALL) NOPASSWD:/sbin/reboot\n");
		Camera::write_file("/tmp/shutdown","www-data  ALL=(ALL) NOPASSWD:/sbin/shutdown\n");
		Camera::write_file("/tmp/camera","www-data  ALL=(ALL) NOPASSWD:/usr/local/bin/mjpg_streamer\n");
		Camera::write_file("/tmp/create_avi","www-data  ALL=(ALL) NOPASSWD:/usr/local/bin/create_avi.sh\n");
		Camera::write_file("/tmp/camera-restart","www-data  ALL=(ALL) NOPASSWD:/etc/init.d/cansel-camera\n");
		Camera::write_file("/tmp/camera-update","www-data  ALL=(ALL) NOPASSWD:/usr/local/bin/camera-update.sh\n");
				
		$output = $ssh->exec("sudo cp /tmp/reboot /etc/sudoers.d");
		$output = $ssh->exec("sudo cp /tmp/shutdown /etc/sudoers.d");
		$output = $ssh->exec("sudo cp /tmp/camera /etc/sudoers.d");
		$output = $ssh->exec("sudo cp /tmp/create_avi /etc/sudoers.d");
		$output = $ssh->exec("sudo cp /tmp/camera-restart /etc/sudoers.d");
		$output = $ssh->exec("sudo cp /tmp/camera-update /etc/sudoers.d");

		$output = $ssh->exec("sudo chmod 440");
		
	} 


	// Get package Version
	public static function version() {
		return (exec("dpkg --status canselcamera | grep '^Version' | cut -d: -f2"));
	}

	public static function latest_version() {
		if(file_exists(Camera::$ver_file)) {
			if (filemtime(Camera::$ver_file) < time() - 3600) {
			// remove file if an hour or more 
				unlink(Camera::$ver_file);
			}	
		}

	    if(!file_exists(Camera::$ver_file))
		{
			$fh = fopen("http://downloads.canselsoftware.com/canselcamera/version.php", "r");
			if (!$fh) {
				$_SESSION['info_message'] = "Unable to open remote version file. Please check your network and DNS settings";
			}else {
				$string = fgets($fh, 10);
				fclose($fh);  
		
				// write a copy to temp and use it for an hour or so
	        	$handler = fopen(Camera::$ver_file, 'w');
	        	fwrite($handler, $string);
	        	fclose($handler);
			}
		} else {
        	$fh = fopen(Camera::$ver_file, 'r');
			$string = fgets($fh, 10);
			fclose($fh);  
		}	
		return $string;
    }

	public static function update_available(){
		if(trim( self::version()) < trim( self::latest_version())){
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function should_stop() {
        // stop video feed (gotta be a better way)
		if (file_exists('/tmp/stop')) 
			return true;
		else
			return false;
	}

	public static function make_stop() {
		return touch('/tmp/stop');
	}
	
	public static function stop() {
		global $ssh;
		$cmd = "/etc/init.d/cansel-camera";
        $stop = $cmd . " stop" ; 
		if(Camera:: nopass() == 1)
			$output = exec("sudo " . $stop);
		else
			$output = $ssh->exec("sudo " . $stop);
		sleep(3);
		return $output;
	}

	public static function start() {
		global $ssh;
        // start video feed
		if (file_exists('/tmp/stop')) 
 			unlink ('/tmp/stop');
		$cmd = "/etc/init.d/cansel-camera";
        $start = $cmd . " start" ; 
		if(Camera:: nopass() == 1)
			$output = exec("sudo " . $start);
		else
			$output = $ssh->exec("sudo " . $start);
		sleep(3);
		return $output;
	}

	public static function restart() {
		global $ssh;
		if(Camera::nopass() == 1 && !Camera::sudoers_enabled())
			Camera::update_sudoers();
		else if (Camera::nopass() == 0 && Camera::sudoers_enabled())
			Camera::remove_sudoers();

		$cmd = "/etc/init.d/cansel-camera";
        $restart = $cmd . " restart" ; 
		if(Camera::nopass() == 1) {
            //really need to fix this
			//if (Camera::timelapse() == 1) {
			//	$output = shell_exec("sudo " . $restart . '> /dev/null 2>&1 &');
			//	$output = "Camera Started";
			//} else {
				$output = exec("sudo " . $restart);
			//}
		} else {
			$output = $ssh->exec("sudo " . $restart);
		}
		return $output;
	}

	public static function updater($func) {
		global $ssh;
		$cmd = "/usr/local/bin/camera-update.sh";
        $cmdout = $cmd . " " . $func; 
		if(Camera:: nopass() == 1)
			$output = exec("sudo " . $cmdout);
		else
			$output = $ssh->exec("sudo " . $cmdout);

		// add change log to output
		$string = Camera::getchangelog();
		
		return $output . $string;
	}

	public static function getchangelog(){
		$lines = @file(Camera::$change_log);
        $string = "Displaying last 25 Lines of ";
		for ($i = 0; $i < 26; $i++) 
			$string .= $lines[$i];
		return $string;
	}

    public static function delete_videos(){
    	global $ssh;

		if(Camera:: nopass() == 1) 
        	exec("rm -rf " . Camera::basepath() . "/movies");
		else
            $ssh->exec("sudo  rm -rf " . Camera::basepath() . "/movies");
    }

    public static function delete_images(){
            global $ssh;

			if(Camera:: nopass() == 1) 
            	exec("rm -rf " . Camera::basepath() . "/" . Camera::savedir());
			else
                $ssh->exec("sudo  rm -rf " . Camera::basepath() . "/" . Camera::savedir());

    }

	public static function make_movie(){
		global $ssh;
		$cmd = "/usr/local/bin/create_avi.sh";
		if(Camera:: nopass() == 1)
			$output = exec("sudo " . $cmd);
		else
			$output = $ssh->exec("sudo " . $cmd);

	}

	public static function getLatestImage($folderName) {
	    $newest_mtime = 0;
	    $base_url = $folderName;      
	    $file_ending = 'jpg';
		$show_file ='';

	    if ($handle = opendir(Camera::basepath() . "/" . $base_url)) {
	        while (false !== ($latestFile = readdir($handle))) {
	            if (($latestFile != '.') && ($latestFile != '..') && ($latestFile != '.htaccess') && (strpos($latestFile, $file_ending))) {
	             $mtime = filemtime(Camera::basepath() . "/" . $base_url . "/" . $latestFile);
	                if ($mtime > $newest_mtime) {
	                    $newest_mtime = $mtime;
	                    $show_file = "$base_url/$latestFile";
						break;
	                }
	            }
	        }
	    }
   		return $show_file;
	}

	public static function is_usb_move() {
		if (file_exists('/tmp/usb_move')) 
			return true;
		else
			return false;
	}
	
	private static function getConfigVar($var){
		$f = fopen(Camera::$config_file, "r");
		while ( $line = fgets($f, 1000) ) {
			if( substr($line,0,strlen($var)) == $var){
				$arr = explode("=",$line);
				return rtrim($arr[1]);
			}
		}
	}

	public static function webdir(){
		return Camera::getConfigVar("WEB_DIR");
	}
	
	public static function username(){
		return Camera::getConfigVar("USER");
	}

	public static function port(){
		return Camera::getConfigVar("HTTP_PORT");	
	}

	public static function framerate(){
		return Camera::getConfigVar("FRAMERATE");	
	}

	public static function width(){
		return Camera::getConfigVar("WIDTH");	
	}
	public static function height(){
		return Camera::getConfigVar("HEIGHT");	
	}

	public static function password(){
		return Camera::getConfigVar("PASS");		
	}

	public static function basefilename(){
		return Camera::getConfigVar("BASE_FILENAME");		
	}
	public static function savedir(){
		return Camera::getConfigVar("SAVEDIR");		
	}
	public static function basepath(){
		return Camera::getConfigVar("BASE_PATH");		
	}
	public static function pausetime(){
		return Camera::getConfigVar("PAUSE_TIME");		
	}
	public static function timelapse(){
		return Camera::getConfigVar("TIMELAPSE");		
	}
	public static function flipvertical(){
		return Camera::getConfigVar("VERTICALFLIP");		
	}
	public static function fliphorizontal(){
		return Camera::getConfigVar("HORIZONTALFLIP");		
	}
	public static function rotation(){
		return Camera::getConfigVar("ROTATION");		
	}
	public static function sharpness(){
		return Camera::getConfigVar("SHARPNESS");		
	}
	public static function contrast(){
		return Camera::getConfigVar("CONTRAST");		
	}
	public static function brightness(){
		return Camera::getConfigVar("BRIGHTNESS");		
	}
	public static function saturation(){
		return Camera::getConfigVar("SATURATION");		
	}
	public static function nopass(){
		return Camera::getConfigVar("NOPASS");		
	}
	public static function firsttime(){
		return Camera::getConfigVar("FIRSTTIME");			
	}
}

?>

