<?php
session_start(); // Start the session

require 'vendor/autoload.php'; // Make sure Composer autoload is included
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
// Check if the user is logged in, redirect if not
if (!isset($_SESSION['GmailAccount'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb"); // Modify database credentials as needed

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the user's email from the session
$GmailAccount = $_SESSION['GmailAccount'];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profilePicture'])) {
    $targetDir = "downloads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true); // Create directory if it doesn't exist
    }

    $targetFile = $targetDir . basename($_FILES["profilePicture"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validate file type
    if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $targetFile)) {
            // Update the database with the new profile picture path
            $updateProfilePictureQuery = "UPDATE personal_info SET ProfilePicture = '$targetFile' WHERE GmailAccount = '$GmailAccount'";
            if (mysqli_query($conn, $updateProfilePictureQuery)) {
                echo "<p style='text-align: center; color: green;'></p>";
            } else {
                echo "<p style='text-align: center; color: red;'>Error updating profile picture: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p style='text-align: center; color: red;'>Error uploading the file.</p>";
        }
    } else {
        echo "<p style='text-align: center; color: red;'>Only JPG, JPEG, PNG & GIF files are allowed.</p>";
    }
}

// Query to retrieve user information from the personal_info table
$queryPersonalInfo = "SELECT Num, FirstName, LastName, IDNumber, BirthDate, Age, Gender, Course, Yr, Section, ProfilePicture FROM personal_info WHERE GmailAccount = '$GmailAccount'";
$resultPersonalInfo = mysqli_query($conn, $queryPersonalInfo);

if ($resultPersonalInfo && mysqli_num_rows($resultPersonalInfo) > 0) {
    // Fetch the user's personal information
    $userInfo = mysqli_fetch_assoc($resultPersonalInfo);
    $Num = $userInfo['Num']; // Use Num instead of IDNumber
    $FirstName = $userInfo['FirstName'];
    $LastName = $userInfo['LastName'];
    $IDNumber = $userInfo['IDNumber']; // Use Num instead of IDNumber
    $BirthDate = date("F j, Y", strtotime($userInfo['BirthDate'])); // Format BirthDate
    $Age = $userInfo['Age'];
    $Gender = $userInfo['Gender'];
    $Course = $userInfo['Course']; // Fetching Course directly
    $Yr = $userInfo['Yr'];
    $Section = $userInfo['Section'];
    $profilePicture = !empty($userInfo['ProfilePicture']) ? $userInfo['ProfilePicture'] : null; // Check if ProfilePicture exists
} else {
    // Handle case where no user information is found
    $Num = "N/A"; // Change from IDNumber to Num
    $FirstName = "Unknown User";
    $LastName = "";
    $IDNumber = "N/A"; // Change from IDNumber to Num
    $BirthDate = "N/A";
    $Age = "N/A";
    $Gender = "N/A";
    $Course = "N/A"; // Set default value for Course
    $Yr = "N/A";
    $Section = "N/A";
    $profilePicture = null; // Set profilePicture to null if user info not found
}

// Query to retrieve multiple intervention records based on IDNumber
$queryIntv = "SELECT what_you_do, what_is_your_existing_desease, have_you_a_family_history_desease, have_you_a_allergy FROM intv WHERE IDNumber = '$IDNumber'";
$resultIntv = mysqli_query($conn, $queryIntv);

// Initialize arrays to store intervention data
$interventionRecords = [];

if ($resultIntv && mysqli_num_rows($resultIntv) > 0) {
    while ($row = mysqli_fetch_assoc($resultIntv)) {
        $interventionRecords[] = $row; // Store each row of intervention data
    }
}

// Variables to check for high fever and high blood pressure
$highFever = false;
$highBloodPressure = false;

// Query to retrieve multiple illness medication records based on IDNumber
$queryIllmed = "SELECT IllName, MedName,Prescription, Temperature, BloodPressure , Appointment_Date FROM illmed WHERE IDNumber = '$IDNumber'";
$resultIllmed = mysqli_query($conn, $queryIllmed);

// Initialize an array to store illness medication records
$illnessMedicationRecords = [];

if ($resultIllmed && mysqli_num_rows($resultIllmed) > 0) {
    while ($row = mysqli_fetch_assoc($resultIllmed)) {
        $illnessMedicationRecords[] = $row; // Store each row of illness medication data
    }
}

// Re-number records in the personal_info table based on IDNumber
$renumberQuery = "SET @new_num = 0;
                  UPDATE personal_info SET Num = (@new_num := @new_num + 1) ORDER BY IDNumber ASC";
mysqli_multi_query($conn, $renumberQuery);

// Dedicated QR Code Generator Function
function generateQRCode($data, $fileName) {
    // Check if Endroid\QrCode is available
    if (!class_exists('Endroid\QrCode\QrCode')) {
        throw new Exception("Endroid QrCode library not found. Please install it using composer.");
    }

    // Initialize QR code with given data
    $qrCode = new QrCode($data);
    $writer = new PngWriter();
   

    // Directory setup
    $directory = 'qrcodes/';
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true); // Create directory if not exists
    }

    // Define path and save QR code
    $filePath = $directory . $fileName . '.png';
    $qrCodeImage = $writer->write($qrCode);
    $qrCodeImage->saveToFile($filePath);

    return $filePath;
}

// Example usage:
// Encode only the IDNumber as a JSON string
// Assign the ID number directly as an integer or string without encoding
$userData = $IDNumber;


// Generate QR code with user data and IDNumber as the filename
$qrCodePath = generateQRCode($userData, $IDNumber);




echo "</div>";




// Close the database connection properly at the end of the script
mysqli_close($conn);
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Roboto', 'Open Sans', sans-serif;
            font-size: 20px; /* Adjust to suit the design */
            background-color: lightblue; /* Soft background color */
            display: flex; 
            justify-content: center; 
            align-items: center; 
            flex-direction: column; 
            margin: 0; 
            padding: 20px;
            
        }
        body {
            background-image: url(image.jpeg);
            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-attachment: fixed;  
        }
        .dashboard-container { 
            background-color: lightblue; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
            max-width: 500px; 
            width: 95%; 
            transition: box-shadow 0.3s ease; 
        }
        .dashboard-container:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }
        h1 {
            text-align: center;
            color: #007bff; 
            font-size: 26px; 
            margin-bottom: 10px;
        }
        h2 {
            color: #343a40; 
            font-size: 20px;
          
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */

            
        }
        .info-item {
            margin: 10px 0;
            font-size: 16px;
            line-height: 1.5;
        }
        .info-item strong {
            color: #007bff;
        }
        .list-header {
            font-size: 20px;
            margin-top: 20px;
            color: #343a40;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        .record-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }
        .record-list li {
            padding: 12px;
            background: lightblue;
            margin: 5px 0;
            border-radius: 5px;
            transition: background 0.3s ease;
            font-size: 17px;
        }
        .record-list li:hover {
            background: #d1d8db;
        }
        .logout-button {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: linear-gradient(90deg, #dc3545, #c82333); 
            color: white; 
            border: none; 
            border-radius: 25px; 
            cursor: pointer; 
            font-size: 16px; 
            transition: background 0.3s ease; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
        }
        .logout-button:hover { 
            background: linear-gradient(90deg, #c82333, #bd2130); 
        }

        .link-button {
            display: block;
            width: 75%;
            padding: 12px;
            margin-top: 20px;
            text-align: center;
            background: linear-gradient(90deg, #28a745, #218838); 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px; 
            text-decoration: none; 
            transition: background 0.3s ease; 
        }

        .link-button:hover { 
            background: linear-gradient(90deg, #218838, #1e7e34); 
            
            
        }

        .health-message {
            background-color: #d1ecf1;
            color: #0c5460;
            
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
            font-size: 16px;
        }
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 300%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
            margin-top: -30px;
             font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
            border-radius: 5px;
            
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        #featureButton {
    background-color: #4CAF50; /* Green background */
    color: white; /* White text */
    padding: 12px 20px; /* Padding around the text */
    font-size: 16px; /* Font size */
    border: none; /* No border */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    transition: background-color 0.3s, transform 0.2s; /* Transition effects */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
    
}

#featureButton:hover {
    background-color: #45a049; /* Darker green on hover */
    transform: translateY(-2px); /* Slight lift effect */
}

#featureButton:active {
    transform: translateY(0); /* Reset lift effect on click */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Reduce shadow on click */
}



        @media (max-width: 600px) {
            .dashboard-container { 
                padding: 20px; 
            }
            h1 {
                font-size: 22px; 
            }
            h2 {
                font-size: 18px; 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            }
            .info-item, .logout-button {
                font-size: 14px; 
            }
        }
    </style>
   

</head>
<body>
    <div class="dashboard-container">
   
        <button id="featureButton">Other Features</button>
        <!-- Feature Modal -->
        <div id="myModal" class="modal feature-modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Select a Feature</h2>
                <ul>
                <a href="contacts1.php" class="link-button">View Contacts</a> 
                    <a href="sched_calendar1.php" class="link-button">Book Online Apppointment</a> 
                 
                   
                    <a href="chatbot.php" class="link-button">Online Consultation</a> 
                    <a href="health_resources.php" class="link-button">View Health Resources</a> 
                    <a href="student_guide.html" class="link-button">Health Guidelines</a> 

                    <a href="symptom_checker.php" class="link-button">Symptom Checker</a>
                    <a href="video.php" class="link-button">Telemed Consultation</a>
                    <a href="about.php" class="link-button">About</a>
                </ul>
            </div>
        </div>

        <h1>WELCOME TO YOUR DASHBOARD </h1>
<hr>


<!-- Button to Open the QR Code Modal -->
<button id="openQrModalButton" style="margin: px auto; padding: 10px 20px; background-color: #007bff; color: white; border: none; cursor: pointer;">
    My QR Code
</button>

<!-- QR Code Modal -->
<div id="qrCodeModal" class="modal" style="display: none;">
    <div class="modal-content" style="text-align:center; padding: 20px;">
        <span id="closeQrModalButton" class="close" style="position: absolute; top: 10px; right: 10px; font-size: 30px; cursor: pointer;">&times;</span>
        <h2>My QR Code</h2>
        <img src="<?php echo $qrCodePath; ?>" alt="QR Code" style="width: 280px; height: 300px; margin: 5px auto; border: 2px solid #007bff;">
    </div>
</div>

<div style="text-align:center; margin-top: 10px;">
    <?php if ($profilePicture): ?>
        <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 4px solid #007bff; display: block; margin: 0 auto 20px;">
    <?php else: ?>
        <img src="default_profile_picture.png" alt="Default Profile Picture" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 4px solid #007bff; display: block; margin: 0 auto 20px;">
    <?php endif; ?>
    
    <!-- Form for uploading a new profile picture -->
    <form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
        <label for="profilePicture" style="display: block; margin-bottom: 10px; font-size: 14px;">Update Profile Picture:</label>
        <input type="file" name="profilePicture" id="profilePicture" accept="image/*" required style="display: block; margin: 0 auto 5px;">
        <button type="submit" style="padding: 10px 12px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Upload</button>
    </form>
</div>


<h1>Hello, <?php echo htmlspecialchars($FirstName) . ' ' . htmlspecialchars($LastName); ?>!</h1>
<h2 class="list-header">Your Records</h2>

<div class="info-item"><strong>Your Student ID Number:</strong> <?php echo htmlspecialchars($IDNumber); ?></div>
<div class="info-item"><strong>Birth Date:</strong> <?php echo htmlspecialchars($BirthDate); ?></div>
<div class="info-item"><strong>Age:</strong> <?php echo htmlspecialchars($Age); ?></div>
<div class="info-item"><strong>Gender:</strong> <?php echo htmlspecialchars($Gender); ?></div>
<div class="info-item"><strong>Course:</strong> <?php echo htmlspecialchars($Course); ?></div>
<div class="info-item"><strong>Year:</strong> <?php echo htmlspecialchars($Yr); ?></div>
<div class="info-item"><strong>Section:</strong> <?php echo htmlspecialchars($Section); ?></div>

<!-- Display intervention records -->
<h2 class="list-header">Consultation Records</h2>
<ul class="record-list">
    <?php if (!empty($interventionRecords)): ?>
        <?php foreach ($interventionRecords as $rowIntv): ?>
            <li>
                <strong>Activity:</strong> <?php echo htmlspecialchars($rowIntv['what_you_do']); ?><br>
                <strong>Existing Disease:</strong> <?php echo htmlspecialchars($rowIntv['what_is_your_existing_desease']); ?><br>
                <strong>Family History:</strong> <?php echo htmlspecialchars($rowIntv['have_you_a_family_history_desease']); ?><br>
                <strong>Allergy:</strong> <?php echo htmlspecialchars($rowIntv['have_you_a_allergy']); ?>
            </li>
            <hr>
        <?php endforeach; ?>
    <?php else: ?>
        <li>No Consultation records found.</li>
    <?php endif; ?>
</ul>

<!-- Display illness medication records -->
<h2 class="list-header">Treatment Records</h2>
<ul class="record-list">
    <?php if (!empty($illnessMedicationRecords)): ?>
        <?php foreach ($illnessMedicationRecords as $rowIllmed): ?>
            <li>
                <strong>Illness Name:</strong> <?php echo htmlspecialchars($rowIllmed['IllName']); ?><br>
                <strong>Medication Name:</strong> <?php echo htmlspecialchars($rowIllmed['MedName']); ?><br>
                <strong>Prescription:</strong> <?php echo htmlspecialchars($rowIllmed['Prescription']); ?><br>
                <strong>Temperature:</strong> <?php echo htmlspecialchars($rowIllmed['Temperature']); ?><br>
                <strong>Blood Pressure:</strong> <?php echo htmlspecialchars($rowIllmed['BloodPressure']); ?><br>
                <strong>Appointment Date:</strong> <?php echo date("h:i A, F j, Y", strtotime($rowIllmed['Appointment_Date'])); ?> <!-- Format appointment date here -->
            </li>
            <hr>
        <?php endforeach; ?>
    <?php else: ?>
        <li>No Treatment records found.</li>
    <?php endif; ?>
</ul>

<div class="health-message">
    <p>Your health is our priority! If you have any questions, feel free to reach out.</p>
</div>

<form action="logout.php" method="post">
    <button type="submit" class="logout-button">Logout</button>
</form>

<!-- Medicine Reminder Modal -->
<div id="medicineReminderModal" class="modal medicine-reminder-modal" style="display: none;">
    <div class="modal-content">
        <h1>REMINDER!!!</h1>
        <p>Please don't forget to take your <strong>PRESCRIBED MEDICINE</strong> for your illness. The School Clinic Team is worried about your health condition.</p>
        <button id="closeReminderModal">Close</button>
    </div>
</div>

<script>
  // Check if the browser supports notifications
  if ("Notification" in window) {

    // Request notification permission if not already granted
    if (Notification.permission !== "granted") {
      Notification.requestPermission();
    }

    // Function to show a browser notification
    function showBrowserNotification() {
      if (Notification.permission === "granted") {
        // Create and display the notification
        const notification = new Notification("Medicine Reminder", {
          body: "Please don't forget to take your PRESCRIBED MEDICINE for your illness. The School Clinic Team is worried about your health condition.",
          icon: 'sksu-logo.png' // Optional: Replace with the actual icon URL or path
        });

        // Optional: Add event listener to the notification click
        notification.onclick = function () {
          window.focus(); // Bring the page into focus if notification is clicked
        };
      }
    }

    // Function to show the medicine reminder modal on the page
    function showMedicineReminderModal() {
      document.getElementById("medicineReminderModal").style.display = "block";
      showBrowserNotification(); // Show the browser notification when modal shows
    }

    // Close the medicine reminder modal when user clicks "Close"
    document.getElementById("closeReminderModal").addEventListener("click", function () {
      document.getElementById("medicineReminderModal").style.display = "none";
    });

    // Initialize reminder count from localStorage
    let reminderCount = parseInt(localStorage.getItem('reminderCount')) || 0; // Default to 0 if not found in localStorage
    const reminderLimit = 3; // Maximum reminders allowed

    // Function to control the medicine reminder modal
    function toggleMedicineReminderModal(show) {
      const modal = document.getElementById('medicineReminderModal');
      if (show) {
        if (reminderCount < reminderLimit) {
          modal.style.display = 'block';
          reminderCount++; // Increment counter each time the modal is shown
          localStorage.setItem('reminderCount', reminderCount); // Save updated count in localStorage
          console.log(`Reminder shown: ${reminderCount}/${reminderLimit}`);
        } else {
          console.log("Reminder limit reached.");
        }
      } else {
        modal.style.display = 'none';
      }
    }

    // Add event listener to close the modal
    document.getElementById('closeReminderModal').addEventListener('click', () => {
      toggleMedicineReminderModal(false);

      // Logic to display the modal again based on the reminder count
      if (reminderCount < reminderLimit) {
        setTimeout(() => {
          toggleMedicineReminderModal(true);
        }, 60000); // Delayed re-trigger of the modal after 30 seconds
      }
    });

    // Initial trigger to start the reminder sequence
    toggleMedicineReminderModal(true);
  }
  else {
    alert("Your browser does not support notifications.");
  }
</script>




<script>
openQrModalButton.onclick = function() {
    qrCodeModal.style.display = "block";
    document.body.classList.add('modal-open');  // Add modal-open class to body
}

closeQrModalButton.onclick = function() {
    qrCodeModal.style.display = "none";
    document.body.classList.remove('modal-open');  // Remove modal-open class from body
}

window.onclick = function(event) {
    if (event.target == qrCodeModal) {
        qrCodeModal.style.display = "none";
        document.body.classList.remove('modal-open');  // Remove modal-open class from body
    }
}

</script>
<style>
/* Modal Base Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: auto;
    background-color: rgba(0, 0, 0, 0.6);
    overflow: hidden;
    transition: all 0.3s ease-in-out;
}

/* Medicine Reminder Modal */
.medicine-reminder-modal .modal-content {
    background-color: #f8d7da;
  margin: 55% auto;
    padding: 15px;
    border-radius: 8px;
    width: 50%;
    max-width: 300px;
    height: auto;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    overflow-y: hidden;
}

.medicine-reminder-modal .modal-content h1 {
    font-size: 32px;
    color: #721c24;
}

.medicine-reminder-modal .modal-content p {
    font-size: 18px;
    color: #721c24;
}

/* Prevent scrolling when modal is open */
body.modal-open {
    overflow: hidden;
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .feature-modal .modal-content,
    .medicine-reminder-modal .modal-content {
        width: 95%;
        padding: 20px;
    }
}

</style>


<script>
// Feature Modal
var featureModal = document.getElementById("myModal");
var featureBtn = document.getElementById("featureButton");
var featureSpan = document.getElementsByClassName("close")[0];

featureBtn.onclick = function() {
    featureModal.style.display = "block";
}

featureSpan.onclick = function() {
    featureModal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == featureModal) {
        featureModal.style.display = "none";
    }
}


window.onclick = function(event) {
    if (event.target == medicineModal) {
        medicineModal.style.display = "none";
    }
}

</script>


</body>
<style>
/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
    transition: opacity 0.3s ease;
}

/* Modal content */
.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 90%; /* Increased width for mobile */
    max-width: 320px; /* Increased max-width */
    border-radius: 8px;
    text-align: center;
    animation: fadeIn 0.5s;
    position: relative;
    margin-top: 5px;
}

/* Close button (X) */
.close {
    color: #aaa;
    font-size: 32px; /* Larger font size for touch targets */
    font-weight: bold;
    position: absolute;
    top: 10px;
    right: 15px; /* Increased touch area */
    cursor: pointer;
    padding: 5px 10px; /* Added padding for larger touch target */
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
}

/* Modal animation */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* QR code image */
.modal-content img {
    width: 100%; /* Make image responsive */
    max-width: 300px; /* Increased max size */
    height: auto; /* Maintain aspect ratio */
    border: 2px solid #007bff;
    margin: 20px auto; /* Center image */
    display: block;
}

/* Button styles */
button {
    padding: 12px 24px; /* Larger padding for touch */
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px; /* Larger text */
    width: 100%; /* Full width on mobile */
    max-width: 150px;
    margin: 10px auto;
}

button:hover {
    background-color: #0056b3;
}

/* Prevent scrolling when modal open */
body.modal-open {
    overflow: hidden;
    position: fixed;
    width: 100%;
}

/* Mobile-specific adjustments */
@media screen and (max-width: 480px) {
    .modal-content {
        margin: 10% auto;
        padding: 15px;
        width: 95%;
    }
    
    button {
        padding: 15px 20px; /* Even larger touch target on mobile */
    }
    
    .close {
        font-size: 36px;
        padding: 8px 12px;
    }
}

</style>

