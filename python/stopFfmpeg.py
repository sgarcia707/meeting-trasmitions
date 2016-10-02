import sys
import shlex
import pipes
from subprocess import check_call
from flask import Flask
app = Flask(__name__)

@app.route("/streaming/stop")
def stopStreaming():
	command = 'killall ffmpeg'
	check_call(shlex.split(command))

if __name__ == "__main__":
	app.run(port=5001)  