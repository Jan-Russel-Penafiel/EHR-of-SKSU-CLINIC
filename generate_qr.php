<?php
// Include the necessary libraries
require 'vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Check if QR data was sent via POST
if (isset($_POST['qrData'])) {
    $qrData = $_POST['qrData'];

    // Ensure the 'qr_codes' directory exists
    $dir = 'qr_code';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);  // Create the directory if it doesn't exist
    }

    // Generate the QR code
    $qrCode = new QrCode($qrData);
    $writer = new PngWriter();
    
    // Create a unique file name for the QR code
    $imagePath = $dir . '/' . uniqid('qr_') . '.png';
    
    // Save the generated QR code to the file
    $result = $writer->write($qrCode);
    $result->saveToFile($imagePath);

    // Return the path of the generated QR code image
    echo $imagePath;
} else {
    // If no QR data is sent, return an error
    echo 'No data received.';
}
?>
