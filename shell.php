<?php
	if(isset($_GET['status'])){	
		$status = $_GET['status'];
		switch($status){
			case 'process':
			var_dump(exec("ffmpeg -f alsa -ac 2 -i hw:0,0 -loop 1 -i cover.png -vcodec libx264 -preset veryfast -maxrate 3000k -bufsize 3000k -vf \"scale=240:-1,format=yuv422p10le\" -g 60 -c:a aac -b:a 128k -ar 44100 -strict -2 -f flv rtmp://a.rtmp.youtube.com/live2/vmcu-11qg-xze8-117p"));
			break;

			case 'stop':
			echo("entro por aca");
			exit(0);
			break;
		}
	}

?>