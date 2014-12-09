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

use lib\Rbpi;
use lib\Camera;

$version = Camera::version();
$latest_version = Camera::latest_version();

$username = Camera::username();
$width = Camera::width();
$height = Camera::height();
$framerate = Camera::framerate();
$port = Camera::port();
// time lapse stuff
$basefilename = Camera::basefilename();
$savedir = Camera::savedir();
$pausetime = Camera::pausetime();
$timelapse = Camera::timelapse();
$flipvertical = Camera::flipvertical();
$fliphorizontal = Camera::fliphorizontal();
$rotation = Camera::rotation();
$nopass = Camera::nopass();

$sharpness = Camera::sharpness();
$contrast = Camera::contrast();
$brightness = Camera::brightness();
$saturation = Camera::saturation();

$external_ip = Rbpi::externalIp();
$internal_ip = Rbpi::internalIp();


?>

    <div class="container settings">
        <div>
			<form action="update.php" method="post">
				<table class="table table-hover">
 	 			<caption><i class="glyphicon glyphicon-cog"></i> Camera Settings</caption>
				<tr>
					<td>Width:</td>
					<td>
						<select name="width" class="form-control">
						    <option value="320" <?php if ( htmlspecialchars($width) == '320') echo ' selected="selected"'; ?>>320</option>
						    <option value="640" <?php if ( htmlspecialchars($width) == '640') echo ' selected="selected"'; ?>>640</option>
						    <option value="1280" <?php if ( htmlspecialchars($width) == '1280') echo ' selected="selected"'; ?>>1280</option>
						    <option value="1920" <?php if ( htmlspecialchars($width) == '1920') echo ' selected="selected"'; ?>>1920</option>
						</select>
					<td>Framerate:</td>
					<td><input type="text" class="form-control" name="framerate" value="<?php echo htmlspecialchars($framerate); ?>"></td>

					</td>

				</tr>
				<tr>
					<td>Height:</td>
					<td>
						<select name="height" class="form-control">
						    <option value="240" <?php if ( htmlspecialchars($height) == '240') echo ' selected="selected"'; ?>>240</option>
						    <option value="480" <?php if ( htmlspecialchars($height) == '480') echo ' selected="selected"'; ?>>480</option>
						    <option value="720" <?php if ( htmlspecialchars($height) == '720') echo ' selected="selected"'; ?>>720</option>
						    <option value="1080" <?php if ( htmlspecialchars($height) == '1080') echo ' selected="selected"'; ?>>1080</option>
						</select>
					</td>
					<td>Port:</td>
					<td><input type="text" class="form-control" name="port" value="<?php echo htmlspecialchars($port); ?>"></td>

				</tr>
				<tr>
					<td>Rotation:</td>
					<td>
						<select name="rotation" class="form-control">
						    <option value="0" <?php if ( htmlspecialchars($rotation) == '0') echo ' selected="selected"'; ?>>0</option>
						    <option value="90" <?php if ( htmlspecialchars($rotation) == '90') echo ' selected="selected"'; ?>>90</option>
						    <option value="180" <?php if ( htmlspecialchars($rotation) == '180') echo ' selected="selected"'; ?>>180</option>
						    <option value="270" <?php if ( htmlspecialchars($rotation) == '270') echo ' selected="selected"'; ?>>270</option>
						</select>
					</td>
					<td>User:</td>
					<td><input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($username); ?>"></td>

				</tr>
				<tr>
					<td>Flip Vertical</td>
					<td><input type="checkbox" class="form-control" name="fliphorizontal" <?php echo ($fliphorizontal == 1 ? 'checked' : '');?>></td>
				</tr>
				<tr>
					<td>Flip Horizontal</td>
					<td><input type="checkbox" class="form-control" name="flipvertical" <?php echo ($flipvertical == 1 ? 'checked' : '');?>></td>
				</tr>
                </table>
				<table class="table table-hover">   
				<caption><i class="glyphicon glyphicon-time"></i> Time Lapse</caption>
       
				<tr>
					<td>Use Time lapse:</td>
					<td><input type="checkbox" class="form-control" name="timelapse" <?php echo ($timelapse == 1 ? 'checked' : '');?>></td>
				</tr>

				<tr>
					<td>Base Filename:</td>
					<td><input type="text" class="form-control" name="basefilename" value="<?php echo htmlspecialchars($basefilename); ?>"></td>
				</tr>
				<tr>
					<td>Pause time (in seconds):</td>
					<td><input type="text" class="form-control" name="pausetime" value="<?php echo htmlspecialchars($pausetime); ?>"></td>
				</tr>

				</table>
				<div id="advanced" class="collapse">		
					<table class="table table-hover">   
							<caption><i class="glyphicon glyphicon-cog"></i> Advanced Options</caption>
						<tr>
							<td>Sharpness</td>
							<td><input type="text" class="form-control" name="sharpness" value="<?php echo htmlspecialchars($sharpness); ?>"></td>
							<td>Contrast</td>
							<td><input type="text" class="form-control" name="contrast" value="<?php echo htmlspecialchars($contrast); ?>"></td>
						</tr>
						<tr>
							<td>Brightness</td>
							<td><input type="text" class="form-control" name="brightness" value="<?php echo htmlspecialchars($brightness); ?>"></td>
							<td>Saturation</td>
							<td><input type="text" class="form-control" name="saturation" value="<?php echo htmlspecialchars($saturation); ?>"></td>
						</tr>
						<tr>
							<td>Don't ask for root login (security risk)</td>
							<td><input type="checkbox" class="form-control" name="nopass" <?php echo ($nopass == 1 ? 'checked' : '');?>></td>

						</tr>
					</table>
				</div>
				<hr>
				<input class="btn btn-primary" type="submit" value="Save Settings"/>
			</form>
        </div>		
				<hr>
				<div>
	         		<button class="btn btn-info" data-toggle="collapse" data-target="#advanced">Advanced Options</button>
				</div>
			
 
		<hr>
		<div> 
		<button class="btn btn-info" data-toggle="collapse" data-target="#passwordchange">Change Password</button>
		</div>
		<div id="passwordchange" class="collapse">
			<form action="update.php" method="post">
				<label for="old_password">Old Password</label>
				<input type="password" class="form-control" name="old_password" >
				<label for="new_password">New Password</label>
				<input type="password" class="form-control" name="new_password" >
				<label for="new_password1">New Password</label>
				<input type="password" class="form-control" name="new_password1">
				<input class="btn" type="submit" value="Save Password"/>
			</form>
		</div>
   </div>

