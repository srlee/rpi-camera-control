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

$dropboxapikey = Cloud::dropbox_api_key();
$dropboxapisecret = Cloud::dropbox_api_secret();
$dropboxaccesstoken = Cloud::dropbox_access_token();
$dropboxaccesstokensecret = Cloud::dropbox_access_token_secret();
$dropboxsynctime = Cloud::dropbox_sync_time();

 
?>

    <div class="container settings">
        <div>
			<form action="dropbox1.php" method="post">
				<table class="table table-hover">
 	 			<caption><i class="glyphicon glyphicon-cog"></i> Dropbox Settings</caption>

				<?php if ($dropboxaccesstoken == "" && $dropboxaccesstoken == "") { 
					echo("<tr>");
					echo ("<td> Press the connect button below to authorize RPI Camera Control Center to connect to your dropbox account and begin syncing time lapse pictures and movies. </td>");
					echo("</tr>");

				}else{
					echo("<tr>");
					echo ("<td> You are connected to dropbox and any time lapse pictures and movies will be synced.<br> This can take considerable data if left running for a long period of time.<br>Press the button below to disconnect from your dropbox account. </td>");
					echo("</tr>");
        			echo("<input type='hidden' name='action' value='unlink'/>");

				}?>

				</table>
				<hr>
				<?php if ($dropboxaccesstoken == "" && $dropboxaccesstoken == "") { 
					echo ("<input class='btn btn-primary' type='submit' value='Connect to DropBox'/>"); 
				} else {
					echo ("<input class='btn btn-primary' type='submit' value='Disconnect DropBox'/>"); 
				} ?>
			</form>
				<?php if (!$dropboxaccesstoken == "" && !$dropboxaccesstoken == "") { 
					echo("<hr>");
					echo("<form action='dropbox1.php' method='post'>");
					echo("<table class='table table-hover'>");
					echo("<td>Sync every (minutes):</td>");
					echo("<td>");
					echo("<select name='synctime' class='form-control'>");

				    echo("<option value='5'" . ((htmlspecialchars($dropboxsynctime) == '5') ? "selected='selected'":'') . ">5</option>");
				    echo("<option value='10'" . ((htmlspecialchars($dropboxsynctime) == '10') ? "selected='selected'":'') . ">10</option>");
				    echo("<option value='15'" . ((htmlspecialchars($dropboxsynctime) == '15') ? "selected='selected'":'') . ">15</option>");
				    echo("<option value='30'" . ((htmlspecialchars($dropboxsynctime) == '30') ? "selected='selected'":'') . ">30</option>");
					echo("</select>");
					echo("</td>");
					echo("</tr>");
        			echo("<input type='hidden' name='action' value='synctime'/>");
					echo("</table>");
					echo("<hr>");
					echo ("<input class='btn btn-primary' type='submit' value='Save'/>"); 
				}?>

			</form>
        </div>		
		<hr>
   </div>

