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

$savedir = Camera::savedir();
$basepath = Camera::basepath();

echo' <div class="container images">';
$images = glob($basepath . "/" . $savedir . "/thumbnails" . '/*{.jpg,.png,.jpeg}', GLOB_BRACE);  //all jpegs into an array
if (!empty($images))
{
?>
	<header class="page-header">
		<h1><center>Timelapse Images</center></h1><br>
	</header>
    <div class="row-fluid infos">
	  	<div class="col-md-4">
	  		<button class="btn btn-default rootaction" type="submit" data-rootaction='makemovie' onclick="return makemovie();"/>Create Video</button>
	  	</div>
	  	<div class="col-md-4">
	  		<button class="btn btn-danger rootaction" type="submit" data-rootaction='deleteimages' onclick="return deleteit();"/>Delete Images</button>
	  	</div>
	</div>
<script>
	function deleteit() 
	{
		conf = confirm("Delete all time lapse images?");
		if (! conf){
			var url="./?page=images";
		   	window.open(url, "_self");	
		}
	}

	function makemovie() 
	{
		conf = confirm("Create movie from time lapse images? (This can take some time)");
		if (! conf){
			var url="./?page=images";
		   	window.open(url, "_self");	
		}
	}

</script>
	<div class="row-fluid">
	<div class="col-md-12">
<?php
	$count = 0;
    $fullcount=0;
	foreach ($images as $image)
	{
		if ($fullcount == 100){
			break;
		}
		echo '<div class="col-md-3">'.PHP_EOL;  
		echo '<a href ="'.$savedir . "/" . basename($image) .'"><img class="img-responsive" id="img" src= "'.$savedir. "/thumbnails/" .basename($image).'"></a><center>'.basename($image).'</center>'.PHP_EOL;
		echo '</div>' . PHP_EOL;
		$count = $count + 1;
		$fullcount = $fullcount + 1;
		if ($count == 4)
		{
		  	echo '</div>' . PHP_EOL;
	  		echo '<div class="col-md-12">' . PHP_EOL;  
	  		$count = 0; 
            // send output and give a break to cpu
            flush();
            sleep(1);
 	  	}
	}
	echo '<br>' . PHP_EOL;
	echo '</div></div>' . PHP_EOL;
}else{
	echo '<header class="page-header">';
	echo '<h1><center>No time lapse images to display.</center></h1>';
	echo '</header>';
}
$videos = glob($basepath . "/movies/*{.mp4,.avi}", GLOB_BRACE);
if (!empty($videos))
{
	$count = 0;
    $fullcount=0;
?>
	<header class="page-header">
		<h1><center>Timelapse Videos</center></h1><br>
	</header>
	 <div class="col-md-4">
	  		<button class="btn btn-danger rootaction" type="submit" data-rootaction='deletemovies' onclick="return deleteit();"/>Delete Videos</button>
	 </div>
 <?php
	foreach ($videos as $video)
	{

		echo '<div class="col-md-6">'.PHP_EOL;  
		echo '<video src="'. "/movies" .basename($video).'" controls width = "100%"></video>';
		echo '</div>' . PHP_EOL;
		$count = $count + 1;
		$fullcount = $fullcount + 1;
		if ($count == 2)
		{
		  	echo '</div>' . PHP_EOL;
	  		echo '<div class="col-md-12">' . PHP_EOL;  
	  		$count = 0; 
            // send output and give a break to cpu
            flush();
            sleep(1);
	  	}
	}
	echo '<br>' . PHP_EOL;
	echo '</div></div></div>' . PHP_EOL;

}else{
	echo '<header class="page-header">';
	echo '<h1><center>No time lapse videos to display.</center></h1>';
	echo '</header>';

}
	echo '</div>' . PHP_EOL;
?>



