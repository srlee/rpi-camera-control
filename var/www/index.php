<?php
namespace lib;
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

spl_autoload_extensions('.php');
spl_autoload_register();

session_start();

require 'config.php';

require_once 'lib/phpseclib/Net/SSH2.php';

use lib\Camera;

$ssh = new \Net_SSH2('localhost');


//force redirect to secure page
if(constant(FORCE_SSL)) {
	if($_SERVER['SERVER_PORT'] != '443') { 
		header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); 
		exit(); 
	}
}

// authentification
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
    if (empty($_GET['page']))
        $_GET['page'] = 'home';

    $_GET['page'] = htmlspecialchars($_GET['page']);
    $_GET['page'] = str_replace("\0", '', $_GET['page']);
    $_GET['page'] = str_replace(DIRECTORY_SEPARATOR, '', $_GET['page']);
    $display = true;

    function is_active($page) {
        if ($page == $_GET['page'])
            echo ' class="active"';
    }

} else {
    $_GET['page'] = 'login';
    $display = false;
}

$page = 'pages' . DIRECTORY_SEPARATOR . $_GET['page'] . '.php';
$page = file_exists($page) ? $page : 'pages' . DIRECTORY_SEPARATOR . '404.php';

if ((isset($_GET['action']) && isset($_GET['username']) && isset($_GET['password'])) || (isset($_GET['action']) && Camera::nopass() == 1)) {
    $action = $_GET['action'];
	if(Camera::nopass() == 1 && Camera::sudoers_enabled()){
        if ($action == 'reboot') {
            echo $_GET["action"] . "<br />Successfully perfomed ";           
            shell_exec("sudo reboot");
		}else if ($action == 'update') {
			echo Camera::updater("CheckForUpgrade");
		}else if ($action == 'deleteimages') {
            echo $_GET["action"] . "<br />Successfully perfomed ";
			echo Camera::delete_images();
		}else if ($action == 'makemovie') {
            echo $_GET["action"] . "<br />Successfully perfomed ";
			echo Camera::make_movie();
		}else if ($action == 'deletemovies') {
            echo $_GET["action"] . "<br />Successfully perfomed ";
			echo Camera::delete_videos();
		}else if ($action == 'restartcam') {
			echo Camera::restart();
		}else if ($action == 'systemupdate') {
            echo $_GET["action"];
			echo Camera::updater("Update_Repos");
			echo Camera::updater("Upgrade_OS");
			echo Camera::updater("RPI_Update");
			echo Camera::updater("CheckForUpgrade");
       	}else if ($action == 'shutdown') {
            echo $_GET["action"] . "<br />Successfully perfomed ";
            shell_exec("sudo shutdown -h now");
       	}
	}	
    else if ($ssh->login($_GET['username'], $_GET['password'])) {
        if ($action == 'reboot') {
            echo $_GET["action"] . "<br />Successfully perfomed ";           
            $ssh->exec("sudo reboot");
		}else if ($action == 'update') {
			echo Camera::updater("CheckForUpgrade");
		}else if ($action == 'deleteimages') {
            echo $_GET["action"] . "<br />Successfully perfomed ";
			echo Camera::delete_images();
		}else if ($action == 'makemovie') {
            echo $_GET["action"] . "<br />Successfully perfomed ";
			echo Camera::make_movie();
		}else if ($action == 'deletemovies') {
            echo $_GET["action"] . "<br />Successfully perfomed ";
			echo Camera::delete_videos();
		}else if ($action == 'restartcam') {
            echo $_GET["action"] . "<br />Successfully perfomed ";
			echo Camera::restart();
		}else if ($action == 'systemupdate') {
            echo $_GET["action"];
			echo Camera::updater("Update_Repos");
			echo Camera::updater("Upgrade_OS");
			echo Camera::updater("RPI_Update");
			echo Camera::updater("CheckForUpgrade");
       	}else if ($action == 'shutdown') {
            echo "Action: " . $_GET["action"] . "<br />Successfully perfomed ";
            $ssh->exec("sudo shutdown -h now");
       	}
    }else {
        echo 'Can\'t perform ' . $_GET["action"] . '<br />Error: Login failed';
    }
    exit();
}

if (Camera::is_usb_move()) {
	$_SESSION['info_message'] = "Moving to USB Storage please wait ... (try refreshing in a few minutes)";
}

?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>RPi Camera Control Center</title>
        <meta name="author" content="Cansel Software Limited" />
        <meta name="robots" content="noindex, nofollow, noarchive" />
        <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
        <link rel="icon" type="image/png" href="img/favicon.ico" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="css/bootstrap.min.css" rel="stylesheet" media="screen" />
        <link href="css/camera.css" rel="stylesheet" media="screen" />
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />    

    </head>

    <body>
        <header class="page-header">
        	<h1><a href="<?php echo INDEX; ?>">RPi Camera</a>
			<small>Control Center</small></h1>
        </header>

	<div id="login-form" class="modal fade in">
		<div class="modal-dialog">
			<div class="modal-content">
	        	<div class="modal-header">
	        	      <a class="close" data-dismiss="modal">×</a>
	        	      <h3>Please login</h3>
	        	</div>
			<div>

			<form class="login">
			<fieldset>
		         <div class="modal-body">
			            <p class="validateTips">All fields are required.</p>
                       <label for="username">Username</label>
                        <input type="text" name="username" id="username" value="" class="form-control" />    
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" value="" class="form-control" />
 		        </div>
			</fieldset>
			</form>
		</div>
	     <div class="modal-footer">
	         <button class="btn btn-primary btn-large" id="login">Login</button>

  		</div>
    </div>
	</div>
	</div>

	<div id="passchange" class="modal fade in">
		<div class="modal-dialog">
			<div class="modal-content">
        		<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">×</button>
					<h4 class="modal-title">Change Password</h4>
				</div>
					<form action="update.php" method="post">
						<fieldset>
							<div class="modal-body">
								<p>For security purposes we ask that you please change the default password. This will also change the shell password for the 'pi' user.</p>
								<div class="form-group">
				    	    	    <label for="old_password">Old Password</label>
									<input type="password" class="form-control" value="" name="old_password" >
								</div>
		
								<div class="form-group">
						            <label for="new_password">New Password</label>
									<input type="password" class="form-control" name="new_password" >
								</div>
		
								<div class="form-group">
						            <label for="new_password1">New Password</label>
									<input type="password" class="form-control" name="new_password1">
								</div>
							</div>
						</fieldset>
					<div class="modal-footer">
						<input class="btn btn-primary" type="submit" value="Update Password"/>
					</div>
				</form>
			</div>
			
		</div>
	</div>

		<div id="messages" class="modal fade in">
		    <div class="modal-dialog">
		        <div class="modal-content">
        		    <div class="modal-header">
        		        <h4 id="title" name="title" class="modal-title">Title</h4>
        		    </div>
        		    <div class="modal-body">
        		        <p name ="message" id="message"> Please wait .....</p>
        		    </div>
        		 </div>
    		</div>
		</div>

		<div id="command-output" class="modal fade in">
		    <div class="modal-dialog">
		        <div class="modal-content">
        		    <div class="modal-header">
        		        <h4 id="title" name="title" class="modal-title">Title</h4>
        		    </div>
        		    <div class="modal-body">
        		        <p name ="message" id="message"> Please wait .....</p>
        		    </div>
			     <div class="modal-footer">
        			<button type="button" class="btn btn-default" data-dismiss="modal" id="btn-close" >Close</button>
		  		</div>
        		 </div>
    		</div>
		</div>

	<?php if ($display && $_GET['page'] != 'update') : ?>

        <nav class="navbar navbar-static-top navbar-inverse" role="navigation">
        <div class="container">
				   <div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapser">
					         <span class="sr-only">Toggle navigation</span>

                    	    <span class="icon-bar"></span>
                    	    <span class="icon-bar"></span>
                    	    <span class="icon-bar"></span>
                    	</button>

			   		</div>

                    <div class="collapse navbar-collapse" id="navbar-collapser">
                        <ul class="nav navbar-nav">
                            <li<?php is_active('home'); ?>><a href="<?php echo INDEX; ?>"><span class="glyphicon glyphicon-home"> </span>Home</a></li>
                            <li<?php is_active('details'); ?>><a href="<?php echo DETAILS; ?>"><span class="glyphicon glyphicon-search"></span> Details</a></li>
                            <li<?php is_active('settings'); ?>><a href="<?php echo SETTINGS; ?>"><span class="glyphicon glyphicon-cog"></span> Settings</a></li>
                            <li<?php is_active('images'); ?>><a href="<?php echo IMAGES; ?>"><span class="glyphicon glyphicon-picture"></span> Images</a></li>
                            <li<?php is_active('cloud'); ?>><a href="<?php echo CLOUD; ?>"><span class="glyphicon glyphicon-cloud"></span> Cloud</a></li>
                            <li><a data-rootaction="restartcam" class="rootaction" href="#"><i class="glyphicon glyphicon-repeat"></i> Restart Camera</a></li>
						</ul>

      					<ul class="nav navbar-nav navbar-right">
							<li class="dropdown">
					          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <span class="caret"></span></a>
					          <ul class="dropdown-menu" role="menu">
                    	        <li><a data-rootaction="systemupdate" class="rootaction" href="#"><i class="glyphicon glyphicon-cog"></i> Upgrade System</a></li>
					            <li class="divider"></li>
                    	        <li><a data-rootaction="reboot" class="rootaction" href="#"><i class="glyphicon glyphicon-repeat"></i> Reboot</a></li>
                    	        <li><a data-rootaction="shutdown" class="rootaction" href="#"><i class="glyphicon glyphicon-stop"></i> Shutdown</a></li>   
					            <li class="divider"></li>
                    	        <li><a href="<?php echo LOGOUT; ?>"><span class="glyphicon glyphicon-off"> Logout</span></a></li>    
          					  </ul>
        					</li>
    
                      </ul>
                </div>
            </div>
        </nav>

    <?php endif; ?>
	<div id="content">
			<?php if (isset($_SESSION['message'])) { ?>
            <div class="alert alert-danger" id="alert_danger">
			  	<a class="close" data-dismiss="alert">×</a>  
				<strong>Error! </strong><?php echo $_SESSION['message']; ?>
			</div>  
            <?php unset($_SESSION['message']);
	        } ?>

			<?php if (isset($_SESSION['info_message'])) { ?>
            <div class="alert alert-info" id="alert_info">
			  	<a class="close" data-dismiss="alert">×</a>  
				<?php echo $_SESSION['info_message']; ?>
			</div>  
            <?php unset($_SESSION['info_message']);
	        } ?>


          <?php if (isset($_SESSION['success_message'])) { ?>
			<div class="alert alert-success" id="alert_sucess">  
			  	<a class="close" data-dismiss="alert">×</a>  
				<strong>Success! </strong><?php echo $_SESSION['success_message']; ?>
			</div>  
			 <?php unset($_SESSION['success_message']);
	        } ?>


        <?php
        include $page;
        ?>

    </div> <!-- /content -->

    <footer>
        <div class="container">
            <p><center><a href="http://canselsoftware.com">Copyright Cansel Software Limited 1987-2015</a></center></p>
        </div>
    </footer>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="http://code.jquery.com/ui/1.11.0/jquery-ui.js"></script>

    <?php
    // load specific scripts
    if ('details' === $_GET['page']) {
        echo '   <script src="js/details.js"></script>';
    }
    ?>
	<?php
		if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
			if(Camera::firsttime() != 1) {

	?>
		    <script>
				$("#passchange").modal('show');
			</script>
	<?php  
		} 
	} ?>
	

    <!-- General scripts -->
    <script>
		function validate()
		{
			conf = confirm("New Version available do you want to update now?");
			if (conf){
				return true;
			} else {
				var url="./?page=home";
	   			window.open(url, "_self");
				return false;
			}
		}

            var username = $("#username"),
                    password = $("#password"),
                    allFields = $([]).add(name).add(password),
                    tips = $(".validateTips");

            function updateTips(t) {
                tips
                        .text(t)
                        .addClass("ui-state-highlight");
                setTimeout(function() {
                    tips.removeClass("ui-state-highlight", 1500);
                }, 500);
            }

            function checkLength(o, n, min, max) {
                if (o.val().length > max || o.val().length < min) {
                    o.addClass("ui-state-error");
                    updateTips("Length of " + n + " must be between " +
                            min + " and " + max + ".");
                    return false;
                } else {
                    return true;
                }
            }



       $(function() {
			$("button#login").click(function(){
                    var lValid = true;
                    allFields.removeClass("ui-state-error");
                    var needpass = $("#login-form").data('rootpass');
                    var sudoers = $("#login-form").data('rootsudoers');
					if (needpass == 1 && sudoers)
					{	
						lValid == true;
					} else {
                    	lValid = lValid && checkLength(username, "username", 1, 50);
                   		lValid = lValid && checkLength(password, "password", 1, 50);
					}
					
                    var action = $("#login-form").data('rootaction');
       				if (lValid) {
                        var Url;

                        if (action == 'reboot' || action == 'shutdown' || action == 'update' || action == 'restartcam' 
							|| action == 'deleteimages' || action == 'makemovie' 
							|| action == 'deletemovies' || action == 'systemupdate')
                            Url = "?action=" + action + "&username=" + username.val() + "&password=" + password.val();

	                        $.ajax({
	                       	    url: Url,
	                       	    type: "GET",
								cache: false,
 						        beforeSend: function(){
									if (action == 'update') {
										    $("#messages #title").html('Upgrading');
										    $("#messages #message").html('Please wait..');
											$('#messages #btn-close').hide();
 							          		$("#messages").modal('show');
									}
									if (action == 'systemupdate')
									{
										    $("#messages #title").html('Upgrading');
										    $("#messages #message").html('Please wait.. this process can take up to an hour.');
											$('#messages #btn-close').hide();
 							          		$("#messages").modal('show');
									}
									if (action == 'restartcam'){
										    $("#messages #title").html('Restarting Camera');
											$('#messages #btn-close').hide();
 							          		$("#messages").modal('show');
									}
									if (action == 'makemovie'){
										    $("#messages #title").html('Rendering Movie');
											$('#messages #btn-close').hide();
 							          		$("#messages").modal('show');
									}
									if (action == 'deleteimages'){
										    $("#messages #title").html('Deleting Images');
											$('#messages #btn-close').hide();
 							          		$("#messages").modal('show');
									}
 								    },

	                       	    success: function(result) {
									if (action == 'update' || action == 'restartcam' || action == 'makemovie' 
										|| action == 'deleteimages' || action == 'systemupdate')
 						          		$("#messages").modal('hide');
									$("#command-output #title").html('RPi Camera');
               						$("#command-output").data('rootaction', action);
									$("#command-output #message").html(result.replace(/(?:\r\n|\r|\n)/g, '<br />'));
									$('#command-output #btn-close').show();
 							        $("#command-output").modal('show');
									

                                }
	                       	});
		                    $("#login-form").modal('hide');
                  	} 
            });
		
			$("#command-output").on( "hide.bs.modal", function( event, ui ) {
                var action = $("#command-output").data('rootaction');
				if (action == 'update' || action == 'restartcam' || action == 'deleteimages' 
                    || action == 'deletemovies' || action == 'makemovie'){
	            	location.reload(true);
				}
			});

            $(".rootaction")
                    .click(function() {
		        var nopass = <?php echo(json_encode(Camera::nopass())); ?>;
		        var sudoers = <?php echo(json_encode(Camera::sudoers_enabled())); ?>;

	            $("#login-form").data('rootaction', $(this).attr("data-rootaction"));
		        $("#login-form").data('rootpass', nopass);
		        $("#login-form").data('rootsudoers', sudoers);
				if (nopass == "1" && sudoers) {
					$("button#login").click();
				} else {
    	            $("#login-form").modal('show');
				}
	        });
       });

   </script>

</body>
</html>
