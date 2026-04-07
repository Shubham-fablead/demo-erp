@extends('layout.app')

@section('title', 'Connected Devices')

@section('content')

    <style>
        .connect-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
            background: linear-gradient(135deg, #eef2ff, #f8fafc);
        }

        .connect-card {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            width: 350px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .qr-box {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
        }

        #qrcode {
            width: 220px !important;
            height: 220px !important;
        }

        #reader {
            width: 250px;
            margin: auto;
        }

        .status {
            margin-top: 20px;
            font-weight: 600;
        }

        .waiting {
            color: orange;
        }

        .connected {
            color: green;
        }

        .btn-disconnect {
            margin-top: 15px;
            padding: 10px 18px;
            background: red;
            color: white;
            border-radius: 8px;
            border: none;
        }

        .mobile-scan-meta {
            margin-top: 10px;
            font-size: 13px;
            color: #475569;
            min-height: 18px;
        }
    </style>

    <div class="connect-wrapper">
        <div class="connect-card">

            <h2>Connect Device</h2>

            <!-- PC QR -->
            <div id="qrSection">
                <div class="qr-box">
                    <canvas id="qrcode"></canvas>
                </div>
                <div id="statusText" class="status waiting">Waiting for device...</div>
            </div>

            <!-- Mobile Scanner -->
            <div id="scannerSection" style="display:none;">
                <div id="reader"></div>
                <div id="mobileStatus" class="status" style="display:none;"></div>
                <div id="mobileScanMeta" class="mobile-scan-meta" style="display:none;"></div>
                <button id="mobileDisconnectBtn" onclick="disconnectMobileDevice()" class="btn-disconnect" style="display:none;">
                    Disconnect
                </button>
            </div>

        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const authToken = localStorage.getItem('authToken');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        function hasApiAuthToken() {
            return !!localStorage.getItem('authToken');
        }

        function mobileConnectUrl() {
            return hasApiAuthToken() ? '/api/connect-device' : '/connect-device';
        }

        function mobileCheckUrl(code) {
            return hasApiAuthToken() ?
                '/api/check-device/' + encodeURIComponent(code) :
                '/check-device/' + encodeURIComponent(code) + '?global=1';
        }

        function mobileDisconnectUrl(code) {
            return hasApiAuthToken() ?
                '/api/disconnect-device/' + encodeURIComponent(code) :
                '/disconnect-device/' + encodeURIComponent(code) + '?global=1';
        }

        function mobileSubmitUrl() {
            return hasApiAuthToken() ? '/api/submit-device-scan' : '/submit-device-scan';
        }

        if (authToken) {
            $.ajaxSetup({
                headers: {
                    'Authorization': 'Bearer ' + authToken,
                    'X-CSRF-TOKEN': csrfToken
                },
                xhrFields: {
                    withCredentials: true
                }
            });
        } else {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                xhrFields: {
                    withCredentials: true
                }
            });
        }

        let deviceCode = null;
        let html5QrCode = null;
        let scannerRunning = false;
        let lastStatus = false;
        let pollTimer = null;

        const MOBILE_DEVICE_KEY = 'connected_mobile_device_code';
        let mobileConnectedCode = null;
        let mobileLastStatus = null;
        let mobilePollTimer = null;
        let audioCtx = null;
        let mobileScannerMode = 'pair';
        let mobileScanInFlight = false;
        let lastScannedBarcode = '';
        let lastScannedAt = 0;
        const PRODUCT_SCAN_COOLDOWN_MS = 900;

        function ensureAudioContext() {
            if (audioCtx) return audioCtx;

            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return null;

            try {
                audioCtx = new AudioContextClass();
            } catch (error) {
                audioCtx = null;
            }

            return audioCtx;
        }

        function unlockAudio() {
            const ctx = ensureAudioContext();
            if (ctx && ctx.state === 'suspended') {
                ctx.resume().catch(function() {});
            }
        }

        document.addEventListener('click', unlockAudio, {
            passive: true
        });
        document.addEventListener('touchstart', unlockAudio, {
            passive: true
        });

        function playBeep(type = 'connect') {
            const ctx = ensureAudioContext();
            if (!ctx) return;

            let tones;

            if (type === 'disconnect') {
                tones = [{
                        at: 0,
                        freq: 850
                    },
                    {
                        at: 0.16,
                        freq: 620
                    }
                ];
            } else if (type === 'scan') {
                tones = [{
                    at: 0,
                    freq: 1380
                }];
            } else {
                tones = [{
                        at: 0,
                        freq: 1200
                    },
                    {
                        at: 0.16,
                        freq: 1600
                    }
                ];
            }

            tones.forEach(function(tone) {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                const startAt = ctx.currentTime + tone.at;
                const endAt = startAt + 0.14;

                osc.type = 'square';
                osc.frequency.setValueAtTime(tone.freq, startAt);

                gain.gain.setValueAtTime(0, startAt);
                gain.gain.linearRampToValueAtTime(0.12, startAt + 0.02);
                gain.gain.linearRampToValueAtTime(0, endAt);

                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(startAt);
                osc.stop(endAt);
            });
        }

        function isMobile() {
            return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        }

        function renderConnected(name) {
            $('#statusText')
                .removeClass('waiting')
                .addClass('connected')
                .html(`
                    Connected: ${name || 'Mobile'}
                    <br>
                    <button onclick="disconnectDevice()" class="btn-disconnect">Disconnect</button>
                `);
        }

        function renderWaiting() {
            $('#statusText')
                .removeClass('connected')
                .addClass('waiting')
                .text('Waiting for device...');
        }

        function renderMobileChecking() {
            $('#reader').hide();
            $('#mobileDisconnectBtn').hide();
            $('#mobileScanMeta').hide().text('');
            $('#mobileStatus')
                .removeClass('connected')
                .addClass('waiting')
                .text('Checking existing connection...')
                .show();
        }

        function renderMobileConnected(name) {
            mobileScannerMode = 'product';
            $('#reader').show();
            $('#mobileStatus')
                .removeClass('waiting')
                .addClass('connected')
                .text('Connected: ' + (name || 'Mobile Scanner') + ' | Scan product barcodes')
                .show();
            $('#mobileScanMeta')
                .show()
                .text('Ready to scan products');
            $('#mobileDisconnectBtn').show();
        }

        function renderMobileScanner() {
            mobileScannerMode = 'pair';
            $('#reader').show();
            $('#mobileDisconnectBtn').hide();
            $('#mobileScanMeta').hide().text('');
            $('#mobileStatus')
                .removeClass('connected')
                .addClass('waiting')
                .text('Scan PC QR to connect')
                .show();
        }

        function updateMobileScanMeta(text, isError = false) {
            $('#mobileScanMeta')
                .show()
                .css('color', isError ? '#dc2626' : '#475569')
                .text(text);
        }

        function setMobileConnectedCode(code) {
            mobileConnectedCode = code || null;

            if (mobileConnectedCode) {
                localStorage.setItem(MOBILE_DEVICE_KEY, mobileConnectedCode);
            } else {
                localStorage.removeItem(MOBILE_DEVICE_KEY);
            }
        }

        function startPolling() {
            if (pollTimer) return;
            pollTimer = setInterval(checkDevice, 1500);
        }

        function generateQR() {
            $.get('/generate-device-code', function(res) {
                deviceCode = res.code;
                QRCode.toCanvas(document.getElementById('qrcode'), deviceCode);

                if (res.connected) {
                    lastStatus = true;
                    renderConnected(res.device_name || 'Mobile');
                } else {
                    lastStatus = false;
                    renderWaiting();
                }

                startPolling();
            }).fail(function() {
                renderWaiting();
            });
        }

        function checkDevice() {
            if (!deviceCode) return;

            $.get('/check-device/' + encodeURIComponent(deviceCode), function(res) {
                if (res.connected && !lastStatus) playBeep('connect');
                if (!res.connected && lastStatus) playBeep('disconnect');

                lastStatus = !!res.connected;

                if (res.connected) {
                    renderConnected(res.device_name);
                } else {
                    renderWaiting();
                }
            });
        }

        function disconnectDevice() {
            if (!deviceCode) return;

            $.get('/disconnect-device/' + encodeURIComponent(deviceCode), function() {
                lastStatus = false;
                playBeep('disconnect');
                Swal.fire({
                    icon: 'warning',
                    title: 'Disconnected',
                    timer: 1500,
                    showConfirmButton: false
                });

                location.reload();
            });
        }

        function stopMobilePolling() {
            if (!mobilePollTimer) return;

            clearInterval(mobilePollTimer);
            mobilePollTimer = null;
        }

        function startMobilePolling() {
            if (mobilePollTimer) return;

            mobilePollTimer = setInterval(function() {
                if (!mobileConnectedCode) {
                    stopMobilePolling();
                    return;
                }

                checkMobileDevice(true);
            }, 1500);
        }

        function checkMobileDevice(playTone = true) {
            if (!mobileConnectedCode) return;

            $.get(mobileCheckUrl(mobileConnectedCode), function(res) {
                const connected = !!res.connected;
                const wasConnected = mobileLastStatus === true;

                if (mobileLastStatus !== null && connected !== mobileLastStatus && playTone) {
                    playBeep(connected ? 'connect' : 'disconnect');
                }

                mobileLastStatus = connected;

                if (connected) {
                    if (!wasConnected) {
                        renderMobileConnected(res.device_name || 'Mobile Scanner');
                    }
                    startScanner();
                    return;
                }

                setMobileConnectedCode(null);
                stopMobilePolling();
                lastScannedBarcode = '';
                lastScannedAt = 0;
                renderMobileScanner();
                startScanner();
            }).fail(function() {
                // Keep scanner active in product mode even if this check fails once.
                if (mobileScannerMode === 'product') {
                    startScanner();
                }
            });
        }

        function extractDeviceCode(decodedText) {
            const text = (decodedText || '').trim();

            if (!text) return null;

            if (/^DEV_[A-Za-z0-9]+$/.test(text)) {
                return text;
            }

            try {
                const url = new URL(text);
                const queryCode = url.searchParams.get('code');
                if (queryCode) return queryCode;

                const parts = url.pathname.split('/').filter(Boolean);
                return parts.length ? parts[parts.length - 1] : null;
            } catch (error) {
                return null;
            }
        }

        function submitProductScan(decodedText) {
            const barcode = (decodedText || '').trim();

            if (!barcode || !mobileConnectedCode) {
                return;
            }

            const now = Date.now();
            if (
                barcode === lastScannedBarcode &&
                now - lastScannedAt < PRODUCT_SCAN_COOLDOWN_MS
            ) {
                return;
            }

            if (mobileScanInFlight) {
                return;
            }

            lastScannedBarcode = barcode;
            lastScannedAt = now;
            mobileScanInFlight = true;

            $.ajax({
                url: mobileSubmitUrl(),
                type: 'POST',
                dataType: 'json',
                data: {
                    code: mobileConnectedCode,
                    barcode: barcode
                },
                success: function(response) {
                    if (!response || response.status !== true) {
                        updateMobileScanMeta('Failed to send barcode', true);
                        return;
                    }

                    playBeep('scan');
                    updateMobileScanMeta('Sent: ' + barcode);
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to send barcode';
                    updateMobileScanMeta(message, true);
                },
                complete: function() {
                    mobileScanInFlight = false;
                }
            });
        }

        function connectByScannedCode(decodedText) {
            const code = extractDeviceCode(decodedText);

            if (!code) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid QR code',
                    text: 'Please scan a valid device QR from your PC.'
                });
                return;
            }

            const headers = {};
            const token = localStorage.getItem('authToken');
            if (token) {
                headers.Authorization = 'Bearer ' + token;
            }

            stopScanner();

            $.ajax({
                url: mobileConnectUrl(),
                type: 'POST',
                dataType: 'json',
                headers: headers,
                data: {
                    code: code,
                    device_name: 'Mobile Scanner'
                },
                success: function(response) {
                    if (!response || response.status !== true) {
                        setMobileConnectedCode(null);
                        mobileLastStatus = null;
                        renderMobileScanner();
                        startScanner();
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection failed',
                            text: response?.message || 'Could not connect this device. Please try again.'
                        });
                        return;
                    }

                    setMobileConnectedCode(code);
                    mobileLastStatus = true;
                    playBeep('connect');
                    renderMobileConnected('Mobile Scanner');
                    startScanner();
                    startMobilePolling();
                    checkMobileDevice(false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Connected',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    setMobileConnectedCode(null);
                    mobileLastStatus = null;
                    renderMobileScanner();
                    startScanner();

                    Swal.fire({
                        icon: 'error',
                        title: 'Connection failed',
                        text: 'Could not connect this device. Please try again.'
                    });
                }
            });
        }

        function onScanSuccess(decodedText) {
            if (mobileScannerMode === 'product') {
                submitProductScan(decodedText);
                return;
            }

            connectByScannedCode(decodedText);
        }

        function startScanner() {
            if (scannerRunning) return;

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode('reader');
            }

            html5QrCode.start({
                    facingMode: 'environment'
                }, {
                    fps: 10,
                    qrbox: 250
                },
                onScanSuccess
            ).then(function() {
                scannerRunning = true;
            }).catch(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Camera error',
                    text: 'Unable to access camera on this phone.'
                });
            });
        }

        function stopScanner() {
            if (!html5QrCode || !scannerRunning) return;

            html5QrCode.stop().then(function() {
                scannerRunning = false;
            }).catch(function() {
                scannerRunning = false;
            });
        }

        function disconnectMobileDevice() {
            if (!mobileConnectedCode) return;

            $.get(mobileDisconnectUrl(mobileConnectedCode), function() {
                playBeep('disconnect');
                mobileLastStatus = false;
                setMobileConnectedCode(null);
                stopMobilePolling();
                mobileScanInFlight = false;
                lastScannedBarcode = '';
                lastScannedAt = 0;
                renderMobileScanner();
                startScanner();

                Swal.fire({
                    icon: 'warning',
                    title: 'Disconnected',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }

        if (isMobile()) {
            $('#qrSection').hide();
            $('#scannerSection').show();
            const savedCode = localStorage.getItem(MOBILE_DEVICE_KEY);

            if (savedCode) {
                setMobileConnectedCode(savedCode);
                renderMobileChecking();
                checkMobileDevice(false);
                startMobilePolling();
            } else {
                renderMobileScanner();
                startScanner();
            }
        } else {
            $.get('/get-session-device', function(res) {
                if (res.connected) {
                    deviceCode = res.device_code;
                    lastStatus = true;
                    renderConnected(res.device_name);
                    startPolling();
                } else {
                    generateQR();
                }
            }).fail(function() {
                generateQR();
            });
        }
    </script>
@endpush
