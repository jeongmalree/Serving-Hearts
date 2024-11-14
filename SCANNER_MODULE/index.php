<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <script src="http://localhost/Serving%20Hearts/SCANNER_MODULE/html5-qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f9;
            text-align: center;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2.5em;
        }

        #reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 2px dashed #007bff;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .scanner-container {
            margin-top: 20px;
        }

        #scanned-result-display {
            margin-top: 20px;
            font-size: 1.2em;
            color: #555;
        }

        #message-container {
            margin-top: 20px;
        }

        .message {
            padding: 15px;
            margin: 0 auto;
            width: 80%;
            border-radius: 8px;
            font-size: 1.1em;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-out;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .button-container {
            margin-top: 20px;
        }

        .button-container button {
            padding: 10px 20px;
            font-size: 1.1em;
            border: none;
            border-radius: 8px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
        }

        .button-container button:hover {
            background-color: #0056b3;
        }

        #reader__dashboard_section_csr {
            display: block;
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #reader__dashboard_section_csr div {
            margin-bottom: 20px;
        }

        #html5-qrcode-input-range-zoom {
            width: 70%;
            height: 8px;
            background: #007bff;
            border-radius: 4px;
            outline: none;
            opacity: 0.8;
            transition: background-color 0.3s ease;
        }

        #html5-qrcode-input-range-zoom:hover {
            background-color: #0056b3;
        }

        #zoom-level {
            margin-left: 10px;
            font-size: 1.1em;
            color: #333;
        }

        #html5-qrcode-select-camera {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            background-color: #fff;
            font-size: 1.1em;
            color: #333;
            transition: border-color 0.3s ease;
        }

        #html5-qrcode-select-camera:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            padding: 10px 20px;
            font-size: 1.1em;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin: 0 10px;
        }

        #html5-qrcode-button-camera-start {
            margin-top: 10px;
            background-color: #28a745;
        }

        #html5-qrcode-button-camera-start:hover {
            background-color: #218838;
        }

        #html5-qrcode-button-camera-stop {
            margin-top: 10px;
            background-color: #dc3545;
        }

        #html5-qrcode-button-camera-stop:hover {
            background-color: #c82333;
        }

        /* Style for Scan an Image File link */
        #html5-qrcode-anchor-scan-type-change {
            display: inline-block;
            font-size: 1.1em;
            color: #007bff;
            text-decoration: none;
            margin: 10px;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
            transition: color 0.3s ease, background-color 0.3s ease;
        }

        #html5-qrcode-anchor-scan-type-change:hover {
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
        }

        #html5-qrcode-button-file-selection {
            padding: 10px 20px;
            font-size: 1.1em;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            text-align: center;
        }

        #html5-qrcode-button-file-selection:hover {
            background-color: #0056b3;
            transform: scale(1.05); /* Slightly enlarge the button on hover */
        }

        #html5-qrcode-button-file-selection:disabled {
            background-color: #d6d6d6;
            cursor: not-allowed;
        }

        #html5-qrcode-button-file-selection span {
            display: block;
            font-size: 1em;
        }

        #html5-qrcode-button-file-selection .image-selected {
            color: #28a745; /* Green text when image is selected */
        }

        #html5-qrcode-button-file-selection .no-image {
            color: #dc3545; /* Red text when no image is selected */
        }

        #html5-qrcode-anchor-scan-type-change {
            text-decoration: none !important;
        }

        #html5-qrcode-button-file-selection {
            display: block;
            margin: 0 auto; /* Center horizontally */
            margin-bottom: 5px;
            padding: 10px 20px;
            font-size: 1em;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #html5-qrcode-button-file-selection:hover {
            background-color: #0056b3;
        }

        /* If you want to center the button within a parent container */
        .parent-container { 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* This makes the container fill the whole viewport */
        }
    </style>
</head>
<body>
    <h1>QR Code Scanner</h1>
    <div class="scanner-container">
        <div id="reader"></div>
    </div>

    <div class="scanner-container">
        <h3>Scanned Result:</h3>
        <p id="scanned-result-display">No QR code scanned yet.</p>
    </div>

    <div id="message-container"></div>

    <form id="qr-result-form" method="POST" style="display:none;">
        <input type="hidden" name="scanned_text" id="scanned_text" />
    </form>

    <div class="button-container">
        <button id="restart-scan-button" style="display: none;">Scan Again</button>
    </div>

    <script>
        let html5QrcodeScanner;

    function onScanSuccess(decodedText, decodedResult) {
        document.getElementById("scanned-result-display").textContent = decodedText;
        document.getElementById("scanned_text").value = decodedText;

        // Call function to submit the form via AJAX
        submitQRCode();

        // Disable the scanner instead of clearing it
        stopScanning();
        
        // Show the scan again button
        document.getElementById("restart-scan-button").style.display = 'inline-block';
    }

    function onScanFailure(error) {
        console.warn(`QR error = ${error}`);
    }

    // Function to start scanning
    function startScanning() {
        // Hide the scan again button
        document.getElementById("restart-scan-button").style.display = 'none';
        
        // Initialize the QR Code scanner
        html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    }

    // Function to stop scanning and disable the scanner
    function stopScanning() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear(); // Clear the scanning
            html5QrcodeScanner = null; // Prevent future scans
        }
    }

    // Function to submit the QR code and display the message without redirecting
function submitQRCode() {
    const formData = new FormData(document.getElementById("qr-result-form"));

    // AJAX request to process.php
    fetch('process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Get message and display it in message-container
        const messageContainer = document.getElementById("message-container");
        const messageType = data.status === 'success' ? 'success' : 'error';
        messageContainer.innerHTML = `<div class="message ${messageType}">${data.message}</div>`;
    })
    .catch(error => console.error('Error:', error));
}

// Event listener for the scan again button
document.getElementById("restart-scan-button").addEventListener("click", () => {
    // Clear scanned result and message before starting scan again
    document.getElementById("scanned-result-display").textContent = "No QR code scanned yet.";
    document.getElementById("message-container").innerHTML = ""; // Clear messages

    // Restart the scanning process
    startScanning();
});


    // Start the initial scanning
    startScanning();
    </script>
</body>
</html>
