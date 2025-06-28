<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner - Search Records</title>
    <style>
    /* Prevent scrolling */
    html, body {
        height: 100%;
        margin: 0;
        overflow: hidden;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        min-height: 100vh;
        margin: 0;
        background-color: #f5f5f5;
    }
    h1 {
        margin-bottom: 20px;
        color: #333;
        font-size: 28px;
        text-align: center;
    }
    .scanner-container {
        width: 100%;
        max-width: 720px; /* Increased max-width for a larger desktop-friendly view */
        height: 80vh; /* Adjusted height to maintain aspect ratio */
        margin-top: 0px;
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        background-color: rgba(0, 0, 0, 0.5);
    }
    #preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 12px;
    }
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        border-radius: 12px;
    }
    .form-container {
        width: 100%;
    max-width: 150px; /* Reduced width for smaller size */
 
    background-color: transparent;
    padding: 10px; /* Reduced padding for smaller element */
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.5s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    input[type="text"], button {
        width: 100%;
        padding: 12px 15px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    input[type="text"]:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
    }
    button {
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    button:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
    }
    button:active {
        transform: translateY(0);
    }
    .scanned-id {
        margin-top: 10px;
        font-weight: bold;
        color: #007bff;
        font-size: 18px;
        text-align: center;
    }
    .error-message {
        color: red;
        font-weight: bold;
        margin-top: 10px;
    }

    .action-button {
        
            padding: 10px 20px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
            margin-left: 5px;
           
            text-decoration: none;
        }

        .action-button:hover {
            background: linear-gradient(90deg, #0056b3, #004085);
        }
    @media (max-width: 1024px) {
        .scanner-container {
            height: 50vh; /* For slightly smaller screen sizes, such as tablets */
        }
        h1 {
            font-size: 24px;
        }
    }
</style>


    <!-- Instascan library for QR code scanning -->
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
</head>
<body>

<h1>QR CODE SCANNER</h1>

<div class="scanner-container">
    <video id="preview" autoplay></video>
    <div class="overlay"></div>
</div>

<div class="form-container">
    <form id="searchForm">
        <!-- Autocomplete IDNumber input field, no need to manually fill this -->
        <input type="hidden" name="IDNumber" id="IDNumber" placeholder="ID Number" required readonly>
        <div class="scanned-id" id="scanned-id"></div>
        <div id="error-message" class="error-message" style="display: none;">Error: Camera access not available.</div>
      
    </form>
  
</div>
<a href="search1.php" class="action-button">Back to Search</a>
<script>
    // Initialize Instascan scanner
    let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });

    scanner.addListener('scan', function(content) {
        // When QR code is scanned, set value to IDNumber input and display scanned ID
        document.getElementById('IDNumber').value = content;
        document.getElementById('scanned-id').innerText = content;
        
        // Automatically redirect to the search page after scanning
        redirectToSearch();
    });

    Instascan.Camera.getCameras().then(function(cameras) {
        if (cameras.length > 0) {
            // Always prefer the front camera if available
            let frontCamera = cameras.find(camera => camera.name.toLowerCase().includes('front'));

            if (frontCamera) {
                // If front camera is available, start the scanner with the front camera
                scanner.start(frontCamera).catch(function(e) {
                    console.error("Error starting camera: ", e);
                    alert("Error starting camera: " + e);
                });
            } else {
                // If no front camera, use the first available camera (typically rear)
                scanner.start(cameras[0]).catch(function(e) {
                    console.error("Error starting camera: ", e);
                    alert("Error starting camera: " + e);
                });
            }
        } else {
            document.getElementById('error-message').style.display = 'block';
        }
    }).catch(function(e) {
        console.error(e);
        alert("Error: " + e);
    });

    // Redirect to search.php with IDNumber as a URL parameter
    function redirectToSearch() {
        const idnumber = document.getElementById('IDNumber').value;
        if (idnumber) {
            window.location.href = `search.php?IDNumber=${encodeURIComponent(idnumber)}`;
        } else {
            alert("Please scan a valid QR code.");
        }
    }
</script>

</body>
</html>
