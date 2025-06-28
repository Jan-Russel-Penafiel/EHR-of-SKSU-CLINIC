<?php
session_start(); // Start the session
require 'vendor/autoload.php'; // Make sure Composer autoload is included
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Check if the user is logged in, redirect if not
if (!isset($_SESSION['GmailAccount'])) {
    header("Location: login_faculty.html"); // Redirect to login page if not logged in
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb"); // Modify database credentials as needed

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if there is a success message in the session
if (isset($_SESSION['appointment_success_message'])) {
    // Display the success message
    echo '<div class="alert alert-success">' . $_SESSION['appointment_success_message'] . '</div>';
    // Unset the message so it doesn't appear again on page refresh
    unset($_SESSION['appointment_success_message']);
}

// Retrieve the logged-in Gmail account
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
            $updateProfilePictureQuery = "UPDATE faculty SET ProfilePicture = '$targetFile' WHERE GmailAccount = '$GmailAccount'";
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
// Get the selected month and year from the form (if available)
$selectedMonth = isset($_POST['month']) ? $_POST['month'] : date('m'); // Default to current month
$selectedYear = isset($_POST['year']) ? $_POST['year'] : date('Y'); // Default to current year

// Query to retrieve user information from the faculty table, including the new fields
$queryPersonalInfo = "SELECT ProfilePicture, IDNumber, Rank, FirstName, LastName, GmailAccount, Department, Position, Complains,
                                Temperature, BloodPressure, HeartRate, RespiratoryRate, Height, Weight, AppointmentDate 
                       FROM faculty 
                       WHERE GmailAccount = '$GmailAccount' 
                       AND MONTH(AppointmentDate) = '$selectedMonth' 
                       AND YEAR(AppointmentDate) = '$selectedYear'";

$resultPersonalInfo = mysqli_query($conn, $queryPersonalInfo);

if ($resultPersonalInfo && mysqli_num_rows($resultPersonalInfo) > 0) {
    // Fetch the user's personal information
    $userInfo = mysqli_fetch_assoc($resultPersonalInfo);
    
    // Extract values, leave blank if empty, NULL, or zero
    $ProfilePicture = !empty($userInfo['ProfilePicture']) ? $userInfo['ProfilePicture'] : 'default.jpg'; // Default picture if none
    $IDNumber = ($userInfo['IDNumber'] !== "0" && !is_null($userInfo['IDNumber'])) ? $userInfo['IDNumber'] : ""; 
    $Rank = ($userInfo['Rank'] !== "0" && !is_null($userInfo['Rank'])) ? $userInfo['Rank'] : ""; 
    $FirstName = ($userInfo['FirstName'] !== "0" && !is_null($userInfo['FirstName'])) ? $userInfo['FirstName'] : "";
    $LastName = ($userInfo['LastName'] !== "0" && !is_null($userInfo['LastName'])) ? $userInfo['LastName'] : "";
    $Department = !empty($userInfo['Department']) ? $userInfo['Department'] : "NONE";
    $Position = ($userInfo['Position'] !== "0" && !is_null($userInfo['Position'])) ? $userInfo['Position'] : "";
    $Complains = ($userInfo['Complains'] !== "0" && !is_null($userInfo['Complains'])) ? $userInfo['Complains'] : "";
    $Temperature = ($userInfo['Temperature'] !== "0" && !is_null($userInfo['Temperature'])) ? $userInfo['Temperature'] : "";
    $BloodPressure = ($userInfo['BloodPressure'] !== "0" && !is_null($userInfo['BloodPressure'])) ? $userInfo['BloodPressure'] : "";
    $HeartRate = ($userInfo['HeartRate'] !== "0" && !is_null($userInfo['HeartRate'])) ? $userInfo['HeartRate'] : "";
   
    // New fields: RespiratoryRate, Height, Weight, AppointmentDate
    $RespiratoryRate = ($userInfo['RespiratoryRate'] !== "0" && !is_null($userInfo['RespiratoryRate'])) ? $userInfo['RespiratoryRate'] : "";
    $Height = ($userInfo['Height'] !== "0" && !is_null($userInfo['Height'])) ? $userInfo['Height'] : "";
    $Weight = ($userInfo['Weight'] !== "0" && !is_null($userInfo['Weight'])) ? $userInfo['Weight'] : "";
    $AppointmentDate = !empty($userInfo['AppointmentDate']) ? $userInfo['AppointmentDate'] : "";
} else {
    // Handle case where no user information is found
    $ProfilePicture = 'default.jpg'; // Default picture if none
    $IDNumber = ""; 
    $Rank = ""; 
    $FirstName = "";
    $LastName = "";
    $Department = "NONE";
    $Position = "";
    $Complains = "";
    $Temperature = "";
    $BloodPressure = "";
    $HeartRate = "";
    
    $RespiratoryRate = "";
    $Height = "";
    $Weight = "";
    $AppointmentDate = "";
}

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

// Close the database connection
mysqli_close($conn);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnel Dashboard</title>
    <style>
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            font-size: 18px; /* Adjust to suit the design */
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
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
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
        <div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Select a Feature</h2>
        <ul>
        <a href="contacts2.php" class="link-button">View Contacts</a> 
        <a href="sched_calendar2.php" class="link-button">Book Online Apppointment</a> 
       
<a href="chatbot1.php" class="link-button">Online Consultation</a> 
<a href="health_resources1.php" class="link-button">View Health Resources</a> 
<a href="guide.php" class="link-button">Guidelines for Hypertension and Diabetes</a>

        </ul>
    </div>
</div>
    
<h1>WELCOME TO YOUR DASHBOARD </h1>
        <hr>

     
        <!-- Button to Open the QR Code Modal -->
<button id="openQrModalButton" style="margin: px auto; padding: 10px 20px; background-color: #007bff; color: white; border: none; cursor: pointer;">
    My QR Code
</button>
<form method="post" action="">
    <label for="month"></label>
    <select name="month" id="month">
        <?php 
            for ($m = 1; $m <= 12; $m++) {
                $monthName = date('F', mktime(0, 0, 0, $m, 1));
                $selected = ($m == $selectedMonth) ? 'selected' : '';
                echo "<option value='$m' $selected>$monthName</option>";
            }
        ?>
    </select>

    <label for="year"></label>
<select name="year" id="year">
    <?php 
        // Set the range for years from 2024 to 2100
        for ($y = 2024; $y <= 2100; $y++) {
            // Check if the year is the selected one (from the user's session or input)
            $selected = ($y == $selectedYear) ? 'selected' : '';
            echo "<option value='$y' $selected>$y</option>";
        }
    ?>
</select>

    <input type="submit" value="Filter">
</form>
<!-- QR Code Modal -->
<div id="qrCodeModal" class="modal" style="display: none;">
    <div class="modal-content" style="text-align:center; padding: 20px;">
        <span id="closeQrModalButton" class="close" style="position: absolute; top: 10px; right: 10px; font-size: 30px; cursor: pointer;">&times;</span>
        <h2>My QR Code</h2>
        <img src="<?php echo $qrCodePath; ?>" alt="QR Code" style="width: 280px; height: 300px; margin: 5px auto; border: 2px solid #007bff;">
    </div>
</div>
       
        
        <div class="profile-container">
      
        <div style="text-align:center; margin-top: 10px;">
            <?php if ($ProfilePicture): ?>
                <img src="<?php echo htmlspecialchars($ProfilePicture); ?>" alt="Profile Picture" style="   width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #007bff;
            display: block;
            margin: 0 auto 20px;">
            <?php else: ?>
                <img src="default_profile_picture.png" alt="Default Profile Picture" >
               
            <?php endif; ?>

             <!-- Form for uploading a new profile picture -->
    <form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
        <label for="profilePicture" style="display: block; margin-bottom: 10px; font-size: 14px;">Update Profile Picture:</label>
        <input type="file" name="profilePicture" id="profilePicture" accept="image/*" required style="display: block; margin: 0 auto 5px;">
        <button type="submit" style="padding: 10px 12px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Upload</button>
    </form>
        </div>
        <h1>Hi, <?php echo htmlspecialchars($FirstName) . ' ' . htmlspecialchars($LastName); ?>!</h1>
        <h2 class="list-header">Your Records</h2>
        <div class="profile-details">
    <p><strong>ID Number:</strong> <?php echo htmlspecialchars($IDNumber); ?></p>
    <p><strong>Rank:</strong> <?php echo htmlspecialchars($Rank); ?></p>
    <p><strong>First Name:</strong> <?php echo htmlspecialchars($FirstName); ?></p>
    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($LastName); ?></p>
    <p><strong>Gmail Account:</strong> <?php echo htmlspecialchars($GmailAccount); ?></p>
    <p><strong>Department:</strong> <?php echo htmlspecialchars($Department); ?></p>
    <p><strong>Position:</strong> <?php echo htmlspecialchars($Position); ?></p>
    <p><strong>Complaints:</strong> <?php echo htmlspecialchars($Complains); ?></p>
    <p><strong>Temperature:</strong> <?php echo htmlspecialchars($Temperature); ?></p>
    <p><strong>Blood Pressure:</strong> <?php echo htmlspecialchars($BloodPressure); ?></p>
    <p><strong>Heart Rate:</strong> <?php echo htmlspecialchars($HeartRate); ?></p>
    
    <!-- New fields -->
    <p><strong>Respiratory Rate:</strong> <?php echo htmlspecialchars($RespiratoryRate); ?></p>
    <p><strong>Height:</strong> <?php echo htmlspecialchars($Height); ?></p>
    <p><strong>Weight:</strong> <?php echo htmlspecialchars($Weight); ?></p>
    <p><strong>Appointment Date:</strong> 
    <?php 
        // Assuming $AppointmentDate is stored in a standard MySQL datetime format (e.g., 'YYYY-MM-DD HH:MM:SS')
        $appointmentDateTime = new DateTime($AppointmentDate);
        echo $appointmentDateTime->format('F j, Y g:i A'); // Format: Month Day, Year 12-hour time (AM/PM)
    ?>
</p>

</div>

        <div class="health-message">
        <p>Your health is our priority! If you have any questions, feel free to reach out.</p>
    </div>

        <form action="logout_faculty.php" method="post">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </div>
 

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
    overflow-y: auto; /* Enable vertical scrolling */
    background-color: rgba(0, 0, 0, 0.4);
    transition: opacity 0.3s ease;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
}

/* Modal content */
.modal-content {
    background-color: #fff;
    margin: 15px auto; /* Reduced top margin for mobile */
    padding: 20px;
    border: 1px solid #888;
    width: 90%; /* Wider width for mobile */
    max-width: 320px; /* Increased max-width */
    border-radius: 8px;
    text-align: center;
    animation: fadeIn 0.5s;
    position: relative;
}

/* Close button (X) */
.close {
    color: #aaa;
    font-size: 32px; /* Larger for touch targets */
    font-weight: bold;
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
    padding: 8px 12px; /* Larger touch area */
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

/* Styles for the QR code image */
.modal-content img {
    width: 100%; /* Responsive width */
    max-width: 300px;
    height: auto;
    border: 2px solid #007bff;
    margin: 20px auto;
    display: block;
}

/* Button styles */
button {
    padding: 12px 24px; /* Larger touch target */
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px; /* Larger text for mobile */
    width: 100%; /* Full width on mobile */
    max-width: 150px;
    margin: 10px auto;
}

button:hover {
    background-color: #0056b3;
}

/* Prevent scrolling when modal is open */
body.modal-open {
    overflow: hidden;
    position: fixed;
    width: 100%;
}

/* Media queries for responsive design */
@media screen and (max-width: 480px) {
    .modal-content {
        margin: 10px auto;
        padding: 15px;
        width: 95%;
    }
    
    button {
        padding: 15px 20px; /* Even larger touch targets on mobile */
    }
}
</style>
 


</body>
</html>
    <script>
    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the button that opens the modal
    var btn = document.getElementById("featureButton");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
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
// Medicine Reminder Modal
var medicineModal = document.getElementById("medicineReminderModal");

// Function to reset the medicine modal count, e.g., on login
function resetModalCountOnLogin() {
    localStorage.removeItem('medicineModalCount');
}

// Example login logic (for demonstration purposes, replace with actual login logic)
function login() {
    // Your login logic goes here...
    
    // Reset the counter after login
    resetModalCountOnLogin();
}

// Check the number of times the modal has been shown
var showCount = localStorage.getItem('medicineModalCount') || 0;

window.onload = function() {
    // Show the modal only if the count is less than 5
    if (showCount < 5) {
        medicineModal.style.display = "block";
        showCount++;
        localStorage.setItem('medicineModalCount', showCount);  // Store the updated count
    }
}

window.onclick = function(event) {
    if (event.target == medicineModal) {
        medicineModal.style.display = "none";
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
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    overflow: auto;
    transition: all 0.3s ease-in-out;
}

/* Medicine Reminder Modal */
.medicine-reminder-modal .modal-content {
    background-color: #f8d7da;
    margin: 50% auto;
    padding: 25px;
    border-radius: 8px;
    width: 50%;
    max-width: 300px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.medicine-reminder-modal .modal-content h1 {
    font-size: 32px;
    color: #721c24;
}

.medicine-reminder-modal .modal-content p {
    font-size: 18px;
    color: #721c24;
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

</body>
</html>
