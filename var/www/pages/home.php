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

use lib\Uptime;
use lib\Memory;
use lib\CPU;
use lib\Storage;
use lib\Network;
use lib\Rbpi;
use lib\Users;
use lib\Temp;
use lib\DHT22;
use lib\Camera;

$uptime = Uptime::uptime();
$ram = Memory::ram();
$swap = Memory::swap();
$cpu = CPU::cpu();
$cpu_heat = CPU::heat();

$hdd = Storage::hdd();
$hdd_alert = 'success';
for ($i = 0; $i < sizeof($hdd); $i++) {
    if ($hdd[$i]['alert'] == 'warning')
        $hdd_alert = 'warning';
}
$network = Network::connections();
$users = sizeof(Users::connected());
$temp = Temp::temp();

$external_ip = Rbpi::externalIp();
$internal_ip = Rbpi::internalIp();

$version = Camera::version();
$latest_version = Camera::latest_version();
$update_available = Camera::update_available();
$timelapse = Camera::timelapse();

$room_temp = DHT22::temperature();
$room_humidity = DHT22::humidity();

function icon_alert($alert) {
    echo '<i class="glyphicon glyphicon-';
    switch ($alert) {
        case 'success':
            echo 'ok';
            break;
        case 'warning':
            echo 'warning-sign';
            break;
        default:
            echo 'exclamation-sign';
    }
    echo ' pull-right"></i>';
}
?>

<div class="container home">

    <div class="row-fluid infos">
        <div class="col-md-4">
            <span class="glyphicon glyphicon-home"></span> <?php echo Rbpi::hostname(); ?>
        </div>
        <div class="col-md-3">
            <span class="glyphicon glyphicon-home"></span> Ver: <?php echo $version ?>
		</div>
        <div class="col-md-3">
            <span class="glyphicon glyphicon-home"></span> Latest: <?php echo $latest_version ?>
        </div>
        <div class="col-md-4">
            <span class="glyphicon glyphicon-map-marker"></span> <?php echo Rbpi::internalIp(); ?>
            <?php echo ($external_ip != 'Unavailable') ? '<br /><i class="glyphicon glyphicon-globe"></i> ' . $external_ip : ''; ?>
        </div>
		
			<?php if ($timelapse != "1") { echo("<div class='row text-center'> <img class='img-responsive' id='img' src='streamer.php'>"); } ?>
			<?php if ($timelapse == "1") { echo("<div class='row text-center'> <img class='img-responsive' id='img' src='" . Camera::getLatestImage(Camera::savedir()) ."'>"); } ?>


		</div>
		<div class="row text-center">
            <?php if ($timelapse != "1") { echo("<button class='btn btn-primary' id='save'/>Snap shot</button>"); } ?>
            <?php if ($timelapse == "1") { echo("Time Lapse Picture"); } ?>
		</div>
<script>
document.getElementById('save').onclick = function () {

var c = document.createElement('canvas');
var img = document.getElementById('img');
c.width = <?php echo json_encode(Camera::width()); ?>;
c.height = <?php echo json_encode(Camera::height()); ?>;
var ctx = c.getContext('2d');

ctx.drawImage(img, 0, 0);
window.open(c.toDataURL('image/png'),'_blank','menubar=no,toolbar=no,location=no,directories=no,status=no,scrollbars=no,width=640,height=480,left=0,top=0');

};
</script>
   <div class="infos">
        <div>
            <a href="<?php echo DETAILS; ?>#check-uptime"><i class="glyphicon glyphicon-time"></i></a> <?php echo $uptime; ?>		        
        </div>		
    </div>
    <div class="row-fluid">
        <div class="infos">
            <div class="col-md-5">
                <?php flush(); ?>
                <?php if ($room_temp != "") { echo("<i class='glyphicon glyphicon-fire'></i> Room Temperature $room_temp <br/>"); } ?>
            </div>
            <div class="col-md-5">
                <?php if ($room_humidity != "") { echo("<i class='glyphicon glyphicon-tasks'></i> Room Humidity $room_humidity <br/>"); } ?>
            </div>

         <div class="col-md-4">
 <?php if ($update_available == TRUE) { echo("<button data-rootaction='update' class='btn btn-info rootaction' type='submit' value='Upgrade Now' onclick='return validate();'/>Upgrade Now</button>"); } ?>
        </div>
                                                                             
        </div>
    </div>
</div>
</div>
