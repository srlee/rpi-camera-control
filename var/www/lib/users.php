<?php

namespace lib;

class Users {

    public static function connected() {
        global $ssh;

        $result = array();

        $dataRaw = $ssh->shell_exec_noauth("who --ips");
        $dataRawDNS = $ssh->shell_exec_noauth("who --lookup");

        //patch for arch linux - the "who" binary doesnt support the --ips flag
        if (empty($dataRaw))
            $dataRaw = $ssh->shell_exec_noauth("who");

        foreach (explode("\n", $dataRawDNS) as $line) {
            $line = preg_replace("/ +/", " ", $line);

            if (strlen($line) > 0) {
                $line = explode(" ", $line);
                if (count($line) == 4)
	                $temp[] = $line[5];
				else 
					$temp[] = "";
            }
        }

        $i = 0;
        foreach (explode("\n", $dataRaw) as $line) {
            $line = preg_replace("/ +/", " ", $line);

            if (strlen($line) > 0) {
                $line = explode(" ", $line);
                if (count($line) == 4)
	                $ip = $line[5];
				else 
					$ip = "";

                $result[] = array(
                    'user' => $line[0],
                    'ip' => $ip,
                    'dns' => $temp[$i],
                    'date' => $line[2] . ' ' . $line[3],
                    'hour' => $line[4]
                );
            }
            $i++;
        }

        return $result;
    }

}

?>
