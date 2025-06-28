<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
require 'vendor/autoload.php'; // Adjust the path as necessary

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getIllnessMedicationAlerts($conn, $searchTerm = '') {
    $queryIllMed = "
        SELECT 
            illmed.*, 
            personal_info.FirstName, 
            personal_info.LastName, 
            personal_info.IDNumber,
            illmed.Appointment_Date,
            personal_info.GmailAccount
        FROM 
            illmed 
        JOIN 
            personal_info ON illmed.IDNumber = personal_info.IDNumber
        WHERE 
            illmed.Temperature > 38 OR illmed.BloodPressure > '140/90'";

    // Add search filter if provided
    if (!empty($searchTerm)) {
        $queryIllMed .= " AND (personal_info.FirstName LIKE '%" . mysqli_real_escape_string($conn, $searchTerm) . "%' 
                            OR personal_info.LastName LIKE '%" . mysqli_real_escape_string($conn, $searchTerm) . "%' 
                            OR personal_info.IDNumber LIKE '%" . mysqli_real_escape_string($conn, $searchTerm) . "%')";
    }

    return mysqli_query($conn, $queryIllMed);
}

function sendAlertEmail($email, $firstName, $lastName, $idNumber, $studentNum, $alertMessage) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sksuisulanschoolclinic@gmail.com';
        $mail->Password = 'ukti coep ddhn tzhh'; // Use environment variables in production
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('sksuisulanschoolclinic@gmail.com', 'School Clinic Alert');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Alert for $firstName $lastName (Student ID: $idNumber)";
        $mail->Body = nl2br($alertMessage);

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    } finally {
        $mail->clearAddresses();
    }
}
$highFeverCount = 0;      // Counter for students with high fever
$highBPCount = 0;         // Counter for students with high blood pressure
$alerts = [];
$resultIllMed = getIllnessMedicationAlerts($conn);

// Get search term from the input (if any)
$searchTerm = isset($_POST['search']) ? trim($_POST['search']) : '';
$resultIllMed = getIllnessMedicationAlerts($conn, $searchTerm);

if (mysqli_num_rows($resultIllMed) > 0) {
    while ($row = mysqli_fetch_assoc($resultIllMed)) {
        $studentNum = $row['Num'];
        $firstName = $row['FirstName'];
        $lastName = $row['LastName'];
        $idNumber = $row['IDNumber'];
        $illnessName = $row['IllName'];
        $medicationName = $row['MedName'];
        $temperature = $row['Temperature'];
        $bloodPressure = $row['BloodPressure'];
        $status = $row['Status'];
        $appointmentDate = $row['Appointment_Date'];
        $email = $row['GmailAccount'];
        $feverAlert = $temperature > 38 ? "High Fever: {$temperature}°C" : "";
        $bloodPressureAlert = $bloodPressure > '140/90' ? "High Blood Pressure: $bloodPressure" : "";

        if ($feverAlert) {
            $highFeverCount++; // Increment high fever count
        }
        if ($bloodPressureAlert) {
            $highBPCount++; // Increment high blood pressure count
        }


        // Check if the alert was already sent for this student
        $alertSentKey = "alert_sent_$studentNum";
        $alertSent = isset($_SESSION[$alertSentKey]) && $_SESSION[$alertSentKey];

        if ($status === 'Okay') {
            $alertMessage = "✅ Condition is stable for <strong>$firstName $lastName</strong> (ID: $idNumber). No immediate action required.";
            $alertMessage .= '<form action="reset_vitals.php" method="POST" style="display:inline;">
                <input type="hidden" name="student_num" value="' . $studentNum . '">
                <input type="submit" value="Reset to Normal" class="btn-mark-okay" onclick="return confirm(\'Are you sure you want to reset vitals to normal?\');">
            </form>';
        } else {
            $feverAlert = $temperature > 38 ? "High Fever: {$temperature}°C" : "";
            $bloodPressureAlert = $bloodPressure > '140/90' ? "High Blood Pressure: $bloodPressure" : "";

            $alertMessage = "🚨 Urgent Alert for <strong>$firstName $lastName</strong> (ID: $idNumber): ";
            $alertMessage .= "Illness: <strong>$illnessName</strong>, Medication: <strong>$medicationName</strong>. ";

            if ($feverAlert && $bloodPressureAlert) {
                $alertMessage .= "<span class='alert-highlight'>$feverAlert</span> ";
                $alertMessage .= "<span class='alert-highlight'>$bloodPressureAlert</span> ";
                $alertMessage .= "Critical condition: Immediate medical attention is required!";
            } else {
                if ($feverAlert) {
                    $alertMessage .= "<span class='alert-highlight'>$feverAlert</span> ";
                }
                if ($bloodPressureAlert) {
                    $alertMessage .= "<span class='alert-highlight'>$bloodPressureAlert</span> ";
                }
            }

            $alertMessage .= "Action Required: Please attend to this student immediately for their health and safety! ";
            $alertMessage .= '<form action="mark_okay.php" method="POST" style="display:inline;">
                <input type="hidden" name="student_num" value="' . $studentNum . '">
                <input type="submit" value="Mark Okay" class="btn-mark-okay">
            </form>';

         // Send the alert email with additional context
$alertEmailMessage = "Hello $firstName $lastName,<br><br>";
$alertEmailMessage .= "We are reaching out to inform you of a critical health alert:<br><br>";
$alertEmailMessage .= "<strong>Health Alert Summary:</strong><br>";
$alertEmailMessage .= "Illness: <strong>$illnessName</strong><br>";
$alertEmailMessage .= "Prescribed Medication: <strong>$medicationName</strong><br>";
$alertEmailMessage .= "Temperature: <strong style='color: red; font-weight: bold;'>{$temperature}°C</strong><br>";
$alertEmailMessage .= "Blood Pressure: <strong style='color: red; font-weight: bold;'>$bloodPressure</strong><br><br>";
$alertEmailMessage .= "We strongly advise you to visit the school clinic at your earliest convenience to discuss these findings with a healthcare professional.<br><br>";
$alertEmailMessage .= "If you have any immediate concerns, please do not hesitate to reach out to our clinic.<br><br>";
$alertEmailMessage .= "Warm regards,<br>School Clinic";


            // Only display the Send Alert button if the alert hasn't been sent
            if (!$alertSent) {
                $alertMessage .= '<form action="send_alert.php" method="POST" onsubmit="return handleFormSubmit(this);" style="display:inline;">
                <input type="hidden" name="email" value="' . $email . '">
                <input type="hidden" name="first_name" value="' . $firstName . '">
                <input type="hidden" name="last_name" value="' . $lastName . '">
                <input type="hidden" name="id_number" value="' . $idNumber . '">
                <input type="hidden" name="student_num" value="' . $studentNum . '">
                <input type="hidden" name="alert_message" value="' . htmlspecialchars($alertEmailMessage, ENT_QUOTES) . '">
                <input type="submit" value="Send Alert" class="btn-mark-okay">
                <span class="success-message" style="display:none;"></span>
            </form>'; 
            } else {
                $alertMessage .= "<span style='color: green;'></span>";
            }
        }

        $alerts[] = $alertMessage;
    }
}

mysqli_close($conn);

// After sending an alert, mark it as sent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_num'])) {
    $studentNum = $_POST['student_num'];
    $alertSentKey = "alert_sent_$studentNum";
    $_SESSION[$alertSentKey] = true; // Set the session variable to indicate the alert was sent
}
?>

<script>
function handleFormSubmit(form) {
    // Disable the submit button to prevent multiple submissions
    const submitButton = form.querySelector('input[type="submit"]');
    submitButton.disabled = true; // Disable the button
    submitButton.value = 'Sending alert please wait...'; // Change button text

    // Delay the success message display by 5 seconds (5000ms)
    setTimeout(() => {
        const successMessage = form.querySelector('.success-message');
        successMessage.style.display = 'inline'; // Show the success message
        successMessage.innerText = 'Alert sent successfully!'; // Set success message text
    }, 4000); // 5000 milliseconds = 5 seconds

    // Allow the form to submit
    return true;
}
</script>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts - School Clinic DBMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>

.success-message {
            color: green;
            font-weight: bold;
        }
     * {
    margin: 0;
    padding: 0;
    box-sizing: border-box; /* Ensures padding and border are included in element's total width and height */
}

/* Additional Improvements */
html, body {
    height: 100%; /* Ensures the body takes the full height of the viewport */
    font-size: 16px; /* Base font size for better scaling */
    line-height: 1.5; /* Improved line height for readability */
}

/* A smoother scroll experience */
html {
    scroll-behavior: smooth; /* Smooth scrolling for anchor links */
}

/* Prevent overflow on small screens */
body {
    overflow-x: hidden; /* Prevents horizontal overflow */
}

/* Ensure all elements have consistent transition properties */
*,
*:before,
*:after {
    transition: all 0.3s ease; /* Smooth transition for all properties */
}

/* Target specific elements for consistent styling */
h1, h2, h3, h4, h5, h6 {
    margin-bottom: 10px; /* Consistent margin for headings */
}

p {
    margin-bottom: 15px; /* Consistent margin for paragraphs */
}

a {
    text-decoration: none; /* Remove underline from links */
    color: inherit; /* Inherit color for links */
    transition: color 0.3s; /* Smooth color transition for links */
}

a:hover {
    color: #1abc9c; /* Change link color on hover */
}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */

    font-size: 18px; /* Adjust to suit the design */
    background-color: lightblue; /* Soft background color */
    color: black; /* Darker text for better readability */
  
   
}
body {
    background-image: url(image.jpeg);
    background-repeat: no-repeat;
    background-size: 100% 100%;
    background-attachment: fixed;
    
}

header {
    background-color: green; /* Green for a fresh look */
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 20px 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Slight shadow for depth */
    height: 100px; /* Define a fixed height for better vertical alignment */
    border-radius: 8px;
    margin-top: -5px;
    font-size: 20px;
    margin-right: -6px;
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
    font-size: 12px; /* Reduced font size */
    text-align: center;
}


        .resizer {
            width: 10px; /* Width of the resizer */
            cursor: ew-resize; /* Cursor style */
            position: fixed; /* Fixed position */
            top: 0;
            left: 220px; /* Initially set to match the sidebar width */
            height: 100vh; /* Full height */
            background-color: transparent; /* Invisible, can change for visibility */
            z-index: 1100; /* Above sidebar */
        }

.alert-container {
            margin-left: 195px; /* Adjusted to fit sidebar */
            padding: 20px;
        }
        .alert-message {
            border: 1px solid #dc3545;
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin: 50px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            animation: fadeIn 0.5s; /* Fade-in animation */
            margin-top: -45px;
            font-size: 17px;
        }
        .alert-highlight {
            font-weight: bold;
            color: #dc3545; /* Red color for alerts */
        }
        .btn-mark-okay {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            transition: background-color 0.3s;
        }
        .btn-delete-alert {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            transition: background-color 0.3s;
        }
        .btn-mark-okay:hover {
            background-color: #218838;
        }
        .no-alerts {
            text-align: center;
            font-size: 18px;
            color: red; /* Neutral color for "No alerts" message */
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
    width: 80px; /* Reduced width for a more compact look */
    height: 80px; /* Reduced height for a more compact look */
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

.alert-container form {
    display: flex; /* Use flexbox for layout */
    justify-content: center; /* Center the items horizontally */
    margin-bottom: 30px; /* Space below the form */
    
}

/* Style for the search input */
.alert-container input[type="text"] {
    padding: 10px 8px; /* Padding inside the input */
    border: 1px solid #ccc; /* Light border */
    border-radius: 4px; /* Rounded corners */
    width: 70%; /* Set width for the input */
    max-width: 315px; /* Limit the maximum width */
    font-size: 14px; /* Increase font size */
    margin-right: 10px; /* Space between input and button */
    margin: 8px;
    margin-top: -50px;
    margin-bottom: 50px;
    margin-left: 90px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
}


/* Style for the search button */
.alert-container button {
    padding: 0px 15px; /* Padding inside the button */
    border: none; /* Remove default border */
    border-radius: 4px; /* Rounded corners */
    background-color: #007bff; /* Primary color for the button */
    color: white; /* White text color */
    font-size: 14px; /* Increase font size */
    cursor: pointer; /* Change cursor to pointer on hover */
    transition: background-color 0.3s; /* Transition effect for hover */
   
    margin-top: -50px;
    margin-bottom: 50px;
    
}

/* Change background color on hover */
.alert-container button:hover {
    background-color: #0056b3; /* Darker shade on hover */
}
/* CSS for the counters display */
.counter-container {
    background-color: transparent; /* Light background for contrast */
    border: 1px solid #ddd; /* Light border for definition */
    border-radius: 8px; /* Rounded corners */
    padding: 5px; /* Space inside the container */
    margin: 5px; /* Space outside the container */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
    width: 25%;
    margin-left: -0.2px;

}

.counter-container p {
    font-size: 12px; /* Larger font for readability */
    margin: 0px 0; /* Space between paragraphs */
    color: #333; /* Darker text color for better contrast */
}

.counter-container strong {
    color: black; /* Blue color for strong text */
}

    </style>
    <script>
        // Hide the Send Alert button after alert is sent
        function hideSendButton() {
            document.getElementById("sendAlertButton").style.display = "none";
        }
    </script>
</head>
<body>
<div>
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



<div class="alert-container">
    <header>
        <h1>ALERT RECORDS</h1>
    </header>
    
    <!-- Display the counters -->
    <div class="counter-container">
        <p><strong>Total Students with High Fever:</strong> <?= $highFeverCount ?></p>
        <p><strong>Total Students with High Blood Pressure:</strong> <?= $highBPCount ?></p>
    </div>

    <!-- Search Form -->
    <form method="POST" action="alert.php">
        <input type="text" name="search" placeholder="Search for records..." value="<?php echo isset($searchTerm) ? htmlspecialchars($searchTerm) : ''; ?>">
        <button type="submit">Search</button>
    </form>

    <?php
    // Initialize the search term
    $searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

    // Filter alerts based on search term if provided
    if (!empty($searchTerm)) {
        $alerts = array_filter($alerts, function($alert) use ($searchTerm) {
            return stripos($alert, $searchTerm) !== false;
        });
    }
    ?>

    <?php if (empty($alerts)): ?>
        <p class="no-alerts">No alerts at this time.</p>
    <?php else: ?>
        <?php foreach ($alerts as $alert): ?>
            <div class="alert-message">
                <span><i class="fas fa-exclamation-triangle"></i> <?php echo $alert; ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>



</body>
</html>
