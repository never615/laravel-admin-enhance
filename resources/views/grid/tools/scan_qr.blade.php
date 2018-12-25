<style>
    .qr-scan-container {
        position: fixed;
        top: 50%;
        left: 55%;
        transform: translate(-50%, -50%);
        -webkit-transform: translate(-50%, -50%);
        border: 2px solid #3c8dbc;
        z-index: 2;
        background: white;
        display: none;
    }

    .qr-scan-container-cancel {
        display: inline-block;
        text-align: center;
        /*width: 40%;*/
        position: absolute;
        right: 0;
        z-index: 1;
        margin-top: -15px;
        margin-right: -15px;
    }

    #loadingMessage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        -webkit-transform: translate(-50%, -50%);
    }

    #canvas {
        position: relative;
        top: 0;
        left: 0;
    }

    #output {
        position: absolute;
        left: 0;
        bottom: 0;
    }


</style>


<div class="btn-group pull-right" style="margin-right: 10px">
    <button class="btn btn-sm btn-twitter table-scan">
        <i class="fa fa-qrcode"></i>&nbsp;&nbsp;扫码
    </button>
</div>
&nbsp;&nbsp;

<div class="qr-scan-container" style="z-index:1000">
    <span class="glyphicon glyphicon-remove qr-scan-container-cancel" aria-hidden="true"></span>
    <div id="loadingMessage" hidden="">⌛ Loading video...</div>
    <canvas id="canvas"></canvas>
    <div id="output">
        <div id="outputMessage">No QR code detected.</div>
        <div hidden=""><b>Data:</b> <span id="outputData"></span></div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var alert = $('.qr-scan-container');
        var cancel = $('.qr-scan-container-cancel');
        var video;
        var canvas;

        var streamGlobal;

        $('.table-scan').click(function (event) {
            event.stopPropagation();//阻止冒泡
            alert.fadeIn();
            startScan();
        });

        cancel.on('click', function () {
            endScan();
        });

        function endScan() {
            console.log("end scan");

            alert.fadeOut();
            if (streamGlobal) {
                console.log(streamGlobal);
                streamGlobal.getTracks().forEach(function (track) {
                    track.stop();
                });
                video = null;
            }
        }


        function startScan() {
            video = document.createElement("video");
            var canvasElement = document.getElementById("canvas");
            var canvas = canvasElement.getContext("2d");
            var loadingMessage = document.getElementById("loadingMessage");
            var outputContainer = document.getElementById("output");
            var outputMessage = document.getElementById("outputMessage");
            var outputData = document.getElementById("outputData");


            function drawLine(begin, end, color) {
                canvas.beginPath();
                canvas.moveTo(begin.x, begin.y);
                canvas.lineTo(end.x, end.y);
                canvas.lineWidth = 4;
                canvas.strokeStyle = color;
                canvas.stroke();
            }

            var mediaOpts = {audio: false, video: {facingMode: "environment"}};

            // Use facingMode: environment to attemt to get the front camera on phones
            navigator.mediaDevices.getUserMedia(mediaOpts)
                .then(function (stream) {
                    streamGlobal = stream;
                    video.srcObject = stream;
                    video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
                    video.onloadedmetadata = function (e) {
                        video.play();
                    };

                    requestAnimationFrame(tick);
                }).catch(err => {
                console.log(err.name + ": " + err.message);
            });

            function tick() {
                loadingMessage.innerText = "⌛ 加载视频...";
                if (!video) {
                    return;
                }
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    loadingMessage.hidden = true;
                    canvasElement.hidden = false;
                    outputContainer.hidden = false;

                    canvasElement.height = video.videoHeight;
                    canvasElement.width = video.videoWidth;
                    canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                    var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                    var code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert",
                    });
                    if (code) {
                        drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
                        drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
                        drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
                        drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
                        outputMessage.hidden = true;
                        outputData.parentElement.hidden = false;
                        outputData.innerText = code.data;
                        //todo 拿到了数据
                        console.log("二维码数据:" + code.data);
                    } else {
                        outputMessage.hidden = false;
                        outputData.parentElement.hidden = true;
                    }
                }

                requestAnimationFrame(tick);
            }
        }


    });


</script>