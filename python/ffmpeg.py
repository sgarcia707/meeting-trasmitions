import sys
import shlex
import pipes
from subprocess import check_call
from flask import Flask
app = Flask(__name__)

@app.route("/streaming/<id>")
def streaming(id):
	sistemaop = sys.platform
	if(sistemaop == "linux2"):
		command = 'ffmpeg -f alsa -ac 2 -i hw:0,0 -loop 1 -i cover.png -vcodec libx264 -preset veryfast -maxrate 3000k -bufsize 3000k -vf "scale=240:-1,format=yuv422p10le" -g 60 -c:a aac -b:a 128k -ar 44100 -strict -2 -f flv rtmp://a.rtmp.youtube.com/live2/' + id

	if(sistemaop == "" or sistemaop == ""):
		command = 'ffmpeg -f dshow -i audio="@device_cm_{33D9A762-90C8-11D0-BD43-00A0C911CE86}\wave_{D1890F9D-76B4-4393-B77A-10616C849FED}" -loop 1 -i cover.jpg -vcodec libx264 -preset veryfast -maxrate 3000k -bufsize 3000k -vf "scale=240:-1,format=yuv422p10le" -g 60 -c:a aac -b:a 128k -ar 44100 -strict -2 -f flv rtmp://a.rtmp.youtube.com/live2/' + id

	check_call(shlex.split(command))

if __name__ == "__main__":
	app.run() 