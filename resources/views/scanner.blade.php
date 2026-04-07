<!DOCTYPE html>
<html>
<head>
    <title>Scan Device</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body { text-align: center; font-family: Arial; }
        #reader { width: 300px; margin: auto; }
    </style>
</head>

<body>

<h3>Scan QR to Connect</h3>

<div id="reader"></div>

<script>
let html5QrCode;

// 🔊 SOUND
function playSound() {
    let audio = new Audio('https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg');
    audio.play();
}

// SCAN SUCCESS
function onScanSuccess(decodedText) {

    playSound();

    $.post('/api/connect-device', {
        code: decodedText,
        device_name: 'Mobile Scanner'
    }, function() {
        alert("Connected ✅");
    });

    html5QrCode.stop();
}

// START SCANNER
function startScanner() {
    html5QrCode = new Html5Qrcode("reader");

    html5QrCode.start(
        { facingMode: "environment" }, // 🔥 back camera
        { fps: 10, qrbox: 250 },
        onScanSuccess
    );
}

startScanner();
</script>

</body>
</html>