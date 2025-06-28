<?php
session_start(); // Start the session

// Check if user session is set
if (!isset($_SESSION['GmailAccount'])) {
    echo "Session expired or user not logged in.";
    exit;
}

$GmailAccount = $_SESSION['GmailAccount']; // Get user identifier from the session

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the file was uploaded without errors
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'downloads/'; // Define upload directory
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; // Allowed MIME types

        $fileTmpPath = $_FILES['profilePicture']['tmp_name'];
        $fileName = basename($_FILES['profilePicture']['name']);
        $fileSize = $_FILES['profilePicture']['size'];
        $fileType = mime_content_type($fileTmpPath);

        // Validate file type
        if (in_array($fileType, $allowedTypes)) {
            // Generate a unique file name to avoid collisions
            $newFileName = uniqid() . '_' . $fileName;
            $destination = $uploadDir . $newFileName;

            // Ensure the upload directory exists
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move uploaded file to destination
            if (move_uploaded_file($fileTmpPath, $destination)) {
                // Include database connection
                require 'database_connection.php';

                // Ensure the database connection is valid
                if (!$conn) {
                    echo "Database connection failed.";
                    exit;
                }

                // Update the user's profile picture in the database
                $query = "UPDATE personal_info SET ProfilePicture = ? WHERE GmailAccount = ?";
                $stmt = $conn->prepare($query);

                if ($stmt) {
                    $stmt->bind_param('si', $destination, $Num);

                    if ($stmt->execute()) {
                        $_SESSION['ProfilePicture'] = $destination; // Update session variable
                        header("Location: home.php?upload_success=1"); // Redirect on success
                        exit;
                    } else {
                        echo "Failed to update profile picture in the database.";
                    }
                } else {
                    echo "Failed to prepare the database query.";
                }
            } else {
                echo "Failed to move the uploaded file.";
            }
        } else {
            echo "Invalid file type. Only JPEG, PNG, and GIF files are allowed.";
        }
    } else {
        echo "File upload error. Please try again.";
    }
} else {
    echo "Invalid request method.";
}
?>
