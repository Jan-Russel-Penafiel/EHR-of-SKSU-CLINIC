<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");
    session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$errorMessage = "";
$successMessage = "";
$GmailAccount = $_SESSION['GmailAccount'] ?? null; // Safely handle if GmailAccount is not set

if (!$GmailAccount) {
    // Set an error message instead of terminating the script
    $errorMessage = "";
}

// Only fetch user info if GmailAccount is available
if ($GmailAccount) {
    // Get user info (either student or faculty)
    $queryUser = "SELECT * FROM (
        SELECT IDNumber, FirstName, LastName, Gender, Course, Yr AS Year, Section, NULL AS Department, NULL AS Position, GmailAccount FROM personal_info 
        UNION ALL 
        SELECT IDNumber, FirstName, LastName, NULL AS Gender, NULL AS Course, NULL AS Year, NULL AS Section, Department, Position, GmailAccount FROM faculty
    ) AS accounts WHERE GmailAccount = '$GmailAccount'";

    $userInfo = mysqli_query($conn, $queryUser);
    $user = mysqli_fetch_assoc($userInfo);

 
}

// Handle appointment insertion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_appointment'])) {
    if ($GmailAccount && $user) {
        $date = mysqli_real_escape_string($conn, $_POST['appointment_date']);
        $time = mysqli_real_escape_string($conn, $_POST['appointment_time']);
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        
        $existsQuery = "SELECT * FROM appointments WHERE IDNumber='{$user['IDNumber']}' AND Appointment_Date='$date' AND Appointment_Time='$time'";
        if (mysqli_num_rows(mysqli_query($conn, $existsQuery)) > 0) {
            $errorMessage = "Appointment already exists.";
        } else {
            $insertQuery = "INSERT INTO appointments (IDNumber, FirstName, LastName, Appointment_Date, Appointment_Time, Reason) 
                            VALUES ('{$user['IDNumber']}', '{$user['FirstName']}', '{$user['LastName']}', '$date', '$time', '$reason')";
            if (mysqli_query($conn, $insertQuery)) {
                $_SESSION['successMessage'] = "Appointment successfully booked!";
                header("Location: display_online_appointment.php");
                exit();
            } else {
                $errorMessage = "Error: " . mysqli_error($conn);
            }
        }
    } else {
        $errorMessage = "You must be logged in to book an appointment.";
    }
}

// Handle appointment deletion
function handleAppointmentDeletion($conn, $ids, $table, $GmailAccount, $FirstName, $LastName) {
    if ($ids) {
        $deleteQuery = "DELETE FROM $table WHERE Num IN ($ids)";
        if (mysqli_query($conn, $deleteQuery)) {
            sendApologyEmail($GmailAccount, $FirstName, $LastName);
            $_SESSION['successMessage'] = "Selected appointments deleted successfully.";
            header("Location: display_online_appointment.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

// Email sending function
function sendApologyEmail($toEmail, $firstName, $lastName) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'penafielliezl1122@gmail.com';
        $mail->Password = 'jryi njov tqmd ogct';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('penafielliezl1122@gmail.com', 'School Clinic');
        $mail->addAddress($toEmail, "$firstName $lastName");
        $mail->isHTML(true);
        $mail->Subject = 'Apology for Appointment Cancellation';
        $mail->Body = 'We apologize for the cancellation of your appointment. Please check back tomorrow for availability.';
        $mail->send();
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}

// Handle deletion requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($GmailAccount && $user) {
        if (isset($_POST['delete_selected'])) {
            handleAppointmentDeletion($conn, implode(',', $_POST['appointment_ids']), 'appointments', $GmailAccount, $user['FirstName'], $user['LastName']);
        }
        if (isset($_POST['delete_faculty_selected'])) {
            handleAppointmentDeletion($conn, implode(',', $_POST['faculty_appointment_ids']), 'faculty_appointments', $GmailAccount, $user['FirstName'], $user['LastName']);
        }
        if (isset($_POST['send_apology'])) {
            sendApologyEmail($GmailAccount, $user['FirstName'], $user['LastName']);
        }
    } else {
        $errorMessage = "You must be logged in to delete appointments.";
    }
}

// Display any success or error messages
if ($successMessage) {
    echo "<p class='success'>$successMessage</p>";
}
if ($errorMessage) {
    echo "<p class='error'>$errorMessage</p>";
}

mysqli_close($conn);
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Online Appointments</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="notification.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <style>

hr {
            border: none; /* Remove default border */
            height: 2px; /* Height of the horizontal line */
            background-color: #007bff; /* Blue color for the horizontal line */
          
        }
        body {
            background-image: url(image.jpeg);
            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
        }
        .sidebar {
    background-color: #2c3e50;
    width: 200px; /* Reduced width */
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding-top: 10px;
    overflow-y: auto;
    z-index: 1000;
    transition: width 0.3s;
}

.sidebar a {
    display: flex;
    align-items: center;
    padding: 8px; /* Reduced padding */
    text-decoration: none;
    color: white;
    font-size: 12px; /* Reduced font size */
}

.sidebar a:hover {
    background-color: green;
    border-radius: 5px;
}

.container {
    margin-left: 200px; /* Adjusted for new sidebar width */
    padding: 15px; /* Reduced padding */
    transition: margin-left 0.3s;
}

.main-content {
    background-color: rgba(173, 216, 230, 0.50);
    border-radius: 8px;
    padding: 15px; /* Reduced padding */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 14px; /* Reduced font size */
    text-align: center;
}

.icon-container {
    width: 30px; /* Smaller width */
    height: 21.3px; /* Smaller height */
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: lightblue;
    border-radius: 50%;
    margin-right: 3px; /* Smaller margin */
    font-size: 16px; /* Smaller font size */
}

.logout { background-color: #ff4c4c; } /* Red */
.dashboard { background-color: #4caf50; } /* Green */
.students { background-color: #2196f3; } /* Blue */
.faculty { background-color: #9c27b0; } /* Purple */
.consultations { background-color: #ff9800; } /* Orange */
.treatments { background-color: #f44336; } /* Dark Red */
.medical { background-color: #009688; } /* Teal */
.inventory { background-color: #673ab7; } /* Deep Purple */
.online-appointment { background-color: #3f51b5; } /* Indigo */
.search { background-color: #00bcd4; } /* Cyan */
.alert { background-color: #ffeb3b; } /* Yellow */
.events { background-color: #ff5722; } /* Deep Orange */
.account { background-color: #607d8b; } /* Blue Grey */
.reports { background-color: #795548; } /* Brown */

.icon-container i {
    color: #fff; /* White icon color */
}

.logo-container {
    text-align: center; /* Center the logo */
    margin-bottom: 20px; /* Space below the logo */
    margin-left: 55px; /* Automatically adds space on the left */
    margin-right: 0; /* Optional: Ensure no right margin */
    width: 76px; /* Reduced width for a more compact look */
    height: 76px; /* Reduced height for a more compact look */
    border-radius: 50%; /* Makes the container circular */
    overflow: hidden; /* Hides overflow */
    display: flex; /* Center the image within the circle */
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    padding-left: 0; /* Remove left padding for perfect centering */
    background-color: #fff; /* Optional: background color for visibility */
    border: 2px solid green; /* Optional: border for better visibility */
}

.sksu-logo {
    width: 100%; /* Makes the logo responsive to container size */
    height: auto; /* Maintain aspect ratio */
}


        .appointment-container {
            display: flex; /* Use flexbox to align containers */
            justify-content: space-between; /* Space between the containers */
            max-width: 1025px; /* Optional: Set a maximum width */
            width: 100%;
            margin-bottom: 20px; /* Space between containers */
            padding-left: 205px;
            margin-top: -5px;
        }

        .appointment-list-container {
            background-color: lightblue;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            flex: 1; /* Allow both containers to grow equally */
            margin: 0 10px; /* Space between containers */
            transition: box-shadow 0.3s ease;
            
        }

        .appointment-list-container:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        h1 {
            text-align: center;
            color: #007bff;
            font-size: 26px;
            margin-bottom: 20px;
        }

        .appointment-item {
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
            margin-bottom: 10px;
            transition: background 0.3s ease;
        }

        .appointment-item:hover {
            background-color: lightgreen;
        }

        .appointment-detail {
            font-size: 16px;
            margin: 5px 0;
        }

        .appointment-detail strong {
            color: #007bff;
        }

        .no-appointments {
            text-align: center;
            font-size: 18px;
            color: red;
            margin: 20px 0;
        }

        /* Delete button styles */
        .delete-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            font-size: 0.9em; /* Font size */
        }

        .delete-button:hover {
            background-color: #c0392b;
            transform: scale(1.05);
        }

        .send-apology-button {
    background-color: #4CAF50; /* Green background */
    color: white; /* White text */
    border: none; /* No border */
    border-radius: 5px; /* Rounded corners */
    padding: 10px 20px; /* Vertical and horizontal padding */
    font-size: 0.9em; /* Font size */
    cursor: pointer; /* Pointer cursor on hover */
    transition: background-color 0.3s ease, transform 0.2s; /* Smooth transition for hover effect */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
    margin-top: 5px;
}

.send-apology-button:hover {
    background-color: #45a049; /* Darker green on hover */
    transform: translateY(-2px); /* Slight upward movement on hover */
}

.send-apology-button:active {
    background-color: #388e3c; /* Even darker green when button is pressed */
    transform: translateY(0); /* Reset the position when active */
}
/* Switch styling */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  border-radius: 50%;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:checked + .slider:before {
  transform: translateX(26px);
}

.calendar-button-link {
    display: inline-block;
    padding: 5px 5px;
    margin-left: -20px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.calendar-button-link:hover {
    background-color: green;
}

    </style>
</head>
<body>
<audio id="ringtone" src="notification.mp3" preload="auto"></audio>
<div class="sidebar">
<div class="logo-container">
        <img src="sksu-logo.png" alt="SKSU Logo" class="sksu-logo">
        </div> 
        <a href="logout_admin.php"><span class="icon-container logout"><i class="fas fa-sign-out-alt"></i></span> Logout</a>
<a href="dashboard.php"><span class="icon-container dashboard"><i class="fas fa-tachometer-alt"></i></span> Dashboard</a>
<a href="display_faculty.php"><span class="icon-container faculty"><i class="fas fa-chalkboard-teacher"></i></span> Personnel Records</a>
<a href="student_information.php"><span class="icon-container students"><i class="fas fa-user-graduate"></i></span> Students Records</a>
<a href="display_intv.php"><span class="icon-container consultations"><i class="fas fa-comments"></i></span> Consultations Records</a>
<a href="display_illmed.php"><span class="icon-container treatments"><i class="fas fa-stethoscope"></i></span> Treatments Records</a>
<a href="display_medical.php"><span class="icon-container medical"><i class="fas fa-file-medical"></i></span> Medical History</a>
<a href="display_inventory.php"><span class="icon-container inventory"><i class="fas fa-capsules"></i></span> Medicine Inventory</a>
<a href="display_online_appointment.php"><span class="icon-container online-appointment"><i class="fas fa-laptop"></i></span> Online Appointments</a>
<a href="search1.php"><span class="icon-container search"><i class="fas fa-search"></i></span> Search Engine</a>
<a href="events.php"><span class="icon-container events"><i class="fas fa-calendar-alt"></i></span> Create Events</a>
<a href="alert.php"><span class="icon-container alert"><i class="fas fa-exclamation-triangle"></i></span> View Alerts</a>
<a href="account.php"><span class="icon-container account"><i class="fas fa-user-circle"></i></span> View Accounts</a>
<a href="reports.php"><span class="icon-container reports"><i class="fas fa-file-alt"></i></span> View Reports</a>

</div>


<?php
// Function to check the connection
function checkConnection($conn) {
    // If the connection is closed, attempt to reconnect
    if ($conn === null || !mysqli_ping($conn)) {
        // Re-establish the connection
        $conn = mysqli_connect('localhost', 'root', '', 'ehrdb');

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
    }
    return $conn;
}

// Initialize connection variable
$conn = null;

// Check and re-establish connection for student appointments
$conn = checkConnection($conn); // Ensure connection is open before querying

// Handle form submission for sending apology and deleting appointments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Send apology email
    if (isset($_POST['send_apology'])) {
        $recipient_email = htmlspecialchars($_POST['recipient_email']);
        
        // Send the apology email (assuming a sendEmail function exists)
        // Example: sendEmail($recipient_email, 'Apology Subject', 'Apology Message');
        
        // Optionally, redirect or display a success message
        // header('Location: success.php');
        // exit;
    }
}
?>
  
  <div class="appointment-container">
    <!-- Students E-Appointments Section -->
    <div class="appointment-list-container">
        <div class="calendar-button" style="margin-top: -20px; margin-bottom: -8px;">
            <?php
            $defaultDate = date('Y-m-d'); // Today's date
            ?>
            <a href="calendar.php?date=<?php echo htmlspecialchars($defaultDate); ?>" class="calendar-button-link">View Calendar</a>
        </div>

        <h1>Students E-Appointments</h1>
        <hr>

        <!-- Form for Deleting or Sending Apology for Selected Appointments -->
        <form method="POST" action="send_apology.php">
            <?php 
            // Query to fetch unique appointments for students
            $query = "SELECT DISTINCT IDNumber, Appointment_Date, Appointment_Time, Reason, Num, FirstName, LastName, Course, Yr, Section FROM appointments";
            $resultAppointments = mysqli_query($conn, $query);
            $highlightID = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null; // Get the highlighted ID from the URL

            if ($resultAppointments && mysqli_num_rows($resultAppointments) > 0): 
                while ($appointment = mysqli_fetch_assoc($resultAppointments)): ?>
                    <div class="appointment-item <?php echo ($highlightID && $highlightID == htmlspecialchars($appointment['IDNumber'])) ? 'highlight' : ''; ?>"
                         id="appointment-<?php echo htmlspecialchars($appointment['IDNumber']); ?>">
                        <label class="switch">
                            <input type="checkbox" name="selected_appointments[]" value="<?php echo htmlspecialchars($appointment['Num']); ?>">
                            <span class="slider round"></span>
                        </label>
                        <div class="appointment-detail"><strong>ID Number:</strong> <?php echo htmlspecialchars($appointment['IDNumber']); ?></div>
                        <div class="appointment-detail"><strong>Name:</strong> <?php echo htmlspecialchars($appointment['FirstName']) . ' ' . htmlspecialchars($appointment['LastName']); ?></div>
                        <div class="appointment-detail"><strong>Course:</strong> <?php echo htmlspecialchars($appointment['Course']); ?></div>
                        <div class="appointment-detail"><strong>Year:</strong> <?php echo htmlspecialchars($appointment['Yr']); ?></div>
                        <div class="appointment-detail"><strong>Section:</strong> <?php echo htmlspecialchars($appointment['Section']); ?></div>
                        <div class="appointment-detail"><strong>Date:</strong> 
                            <?php 
                                $date = DateTime::createFromFormat('Y-m-d', $appointment['Appointment_Date']);
                                echo htmlspecialchars($date->format('F j, Y'));
                            ?>
                        </div>
                        <div class="appointment-detail"><strong>Time:</strong> 
                            <?php 
                                $time = DateTime::createFromFormat('H:i:s', $appointment['Appointment_Time']);
                                echo htmlspecialchars($time->format('g:i A'));
                            ?>
                        </div>
                        <div class="appointment-detail"><strong>Reason:</strong> <?php echo htmlspecialchars($appointment['Reason']); ?></div>
                    </div>
                <?php endwhile; ?>
                <!-- Buttons for deleting or sending apology for selected appointments -->
                <button type="submit" name="delete_selected" formaction="delete_online_appointment.php" class="delete-button">Delete for Selected Appointment</button>
                <button type="submit" name="send_apology" class="send-apology-button">Send Apology for Selected Appointment</button>
            <?php else: ?>
                <div class="no-appointments">No appointments found.</div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Faculty Appointments Section -->
    <div class="appointment-list-container">
        <h1>Personnels E-Appointments</h1>
        <hr>
        <form method="POST" action="send_apology.php">
            <?php 
            // Check if connection is valid before executing the query
            $conn = checkConnection($conn); // Ensure connection is valid
            // Query to fetch unique faculty appointments
            $queryFaculty = "SELECT DISTINCT IDNumber, Appointment_Date, Appointment_Time, Reason, Num, FirstName, LastName, Department, Position FROM faculty_appointments"; 
            $resultFacultyAppointments = mysqli_query($conn, $queryFaculty);
            $highlightFacultyID = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null; // Get the highlighted ID from the URL

            if ($resultFacultyAppointments && mysqli_num_rows($resultFacultyAppointments) > 0): 
                while ($facultyAppointment = mysqli_fetch_assoc($resultFacultyAppointments)): ?>
                    <div class="appointment-item <?php echo ($highlightFacultyID && $highlightFacultyID == htmlspecialchars($facultyAppointment['IDNumber'])) ? 'highlight' : ''; ?>"
                         id="faculty-appointment-<?php echo htmlspecialchars($facultyAppointment['IDNumber']); ?>">
                        <label class="switch">
                            <input type="checkbox" name="selected_faculty_appointments[]" value="<?php echo htmlspecialchars($facultyAppointment['Num']); ?>">
                            <span class="slider round"></span>
                        </label>
                        <div class="appointment-detail"><strong>ID Number:</strong> <?php echo htmlspecialchars($facultyAppointment['IDNumber']); ?></div>
                        <div class="appointment-detail"><strong>Name:</strong> <?php echo htmlspecialchars($facultyAppointment['FirstName']) . ' ' . htmlspecialchars($facultyAppointment['LastName']); ?></div>
                        <div class="appointment-detail"><strong>Department:</strong> <?php echo htmlspecialchars($facultyAppointment['Department']); ?></div>
                        <div class="appointment-detail"><strong>Position:</strong> <?php echo htmlspecialchars($facultyAppointment['Position']); ?></div>
                        <div class="appointment-detail"><strong>Date:</strong> 
                            <?php 
                                $date = DateTime::createFromFormat('Y-m-d', $facultyAppointment['Appointment_Date']);
                                echo htmlspecialchars($date->format('F j, Y'));
                            ?>
                        </div>
                        <div class="appointment-detail"><strong>Time:</strong> 
                            <?php 
                                $time = DateTime::createFromFormat('H:i:s', $facultyAppointment['Appointment_Time']);
                                echo htmlspecialchars($time->format('g:i A'));
                            ?>
                        </div>
                        <div class="appointment-detail"><strong>Reason:</strong> <?php echo htmlspecialchars($facultyAppointment['Reason']); ?></div>
                    </div>
                <?php endwhile; ?>
                <!-- Buttons for deleting or sending apology for selected faculty appointments -->
                <button type="submit" name="delete_faculty_selected" formaction="delete_online_appointment.php" class="delete-button">Delete for Selected Appointment</button>
                <button type="submit" name="send_apology" class="send-apology-button">Send Apology for Selected Appointment</button>
            <?php else: ?>
                <div class="no-appointments">No faculty appointments found.</div>
            <?php endif; ?>
        </form>
    </div>
</div>


<style>
    .highlight {
        background-color: yellow; /* Highlight color */
        transition: background-color 0.5s ease; /* Smooth transition */
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Get the highlighted appointment ID from the URL
        const highlightID = new URLSearchParams(window.location.search).get('id');
        if (highlightID) {
            // Scroll to the highlighted appointment
            const appointmentElement = document.getElementById('appointment-' + highlightID) || document.getElementById('faculty-appointment-' + highlightID);
            if (appointmentElement) {
                appointmentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>