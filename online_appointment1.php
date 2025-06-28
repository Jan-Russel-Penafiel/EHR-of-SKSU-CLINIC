<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");
    session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['GmailAccount'])) {
    header('Location: login_faculty.html'); // Redirect to login page if not logged in
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "0922", "ehrdb");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the user's email from the session
$GmailAccount = $_SESSION['GmailAccount'];

// Query to get faculty info for form pre-fill
$queryPersonalInfo = "SELECT IDNumber, FirstName, LastName, Department, Position FROM faculty WHERE GmailAccount = '$GmailAccount'";
$resultPersonalInfo = mysqli_query($conn, $queryPersonalInfo);

if ($resultPersonalInfo && mysqli_num_rows($resultPersonalInfo) > 0) {
    $userInfo = mysqli_fetch_assoc($resultPersonalInfo);
    $IDNumber = $userInfo['IDNumber'];
    $FirstName = $userInfo['FirstName'];
    $LastName = $userInfo['LastName'];
    $Department = !empty($userInfo['Department']) ? $userInfo['Department'] : "NONE"; // Default to "NONE" if no department value
    $Position = $userInfo['Position'];
} else {
    // Handle the case where no faculty information is found
    $IDNumber = "N/A";
    $FirstName = "Unknown";
    $LastName = "";
    $Department = "NONE";
    $Position = "N/A";
}

// Get the current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Check how many appointments the faculty has made in the current month and year
$queryAppointmentCount = "SELECT COUNT(*) AS appointment_count FROM faculty_appointments 
                          WHERE IDNumber = '$IDNumber' 
                          AND MONTH(Appointment_Date) = '$currentMonth' 
                          AND YEAR(Appointment_Date) = '$currentYear'";
$resultAppointmentCount = mysqli_query($conn, $queryAppointmentCount);
$appointmentData = mysqli_fetch_assoc($resultAppointmentCount);
$appointmentCount = $appointmentData['appointment_count'];

// Limit the faculty to 2 appointments per month
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($appointmentCount >= 2) {
        $errorMessage = "You have already booked 2 appointments this month. Please try again next month.";
    } else {
        // Capture form data
        $appointmentDate = mysqli_real_escape_string($conn, $_POST['appointment_date']);
        $appointmentTime = mysqli_real_escape_string($conn, $_POST['appointment_time']);
        $reasonForAppointment = mysqli_real_escape_string($conn, $_POST['reason_for_appointment']);

        // Validate that the appointment time is between 8:00 AM and 3:00 PM
        $appointmentHour = (int)date('H', strtotime($appointmentTime));
        if ($appointmentHour < 8 || $appointmentHour > 15) {
            $errorMessage = "Please select a time between 8:00 AM and 3:00 PM.";
        } else {
            // Validate that the appointment date is a weekday (Monday to Friday)
            $dayOfWeek = date('N', strtotime($appointmentDate)); // 1 (for Monday) through 7 (for Sunday)
            if ($dayOfWeek >= 6) {
                $errorMessage = "Please select a weekday (Monday to Friday).";
            } else {
                // Insert the appointment data into the faculty_appointments table
                $queryInsertAppointment = "INSERT INTO faculty_appointments (IDNumber, FirstName, LastName, Department, Position, Appointment_Date, Appointment_Time, Reason) 
                                            VALUES ('$IDNumber', '$FirstName', '$LastName', '$Department', '$Position', '$appointmentDate', '$appointmentTime', '$reasonForAppointment')";

                if (mysqli_query($conn, $queryInsertAppointment)) {
                    // Set a success message to show in JavaScript
                    $successMessage = "Appointment booked successfully!";
                } else {
                    $errorMessage = "Error: Could not book your appointment. Please try again later.";
                }
            }
        }
    }
}

// Close connection
mysqli_close($conn);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Online Appointment</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            display: flex; 
            justify-content: center; 
            align-items: center; 
            flex-direction: column; 
            margin: 0; 
            padding: 20px;
            background-color: lightblue;
            color: black;
        }
        
        body {
            background-image: url(sksu-logo.png);            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-attachment: fixed;
        }

        .appointment-container { 
            background-color: lightblue;  
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
            width: 90%; /* Make the form container responsive */
            max-width: 400px;
            transition: box-shadow 0.3s ease; 
        }

        .appointment-container:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        h1 {
            text-align: center;
            color: #007bff;
            font-size: 26px; 
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        input, textarea {
            width: 95%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            font-size: 16px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
        }

        textarea {
            resize: none;
        }

        .submit-button {
            background-color: #28a745;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .submit-button:hover {
            background-color: #218838;
        }

        .message {
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
            color: green;
        }

        
        #backButton {
            display: inline-block; /* Allows padding and margin */
            background-color: #007bff; /* Blue background */
            color: white; /* White text */
            padding: 10px 15px; /* Padding around the text */
            font-size: 12px; /* Font size */
            border-radius: 5px; /* Rounded corners */
            text-decoration: none; /* Remove underline */
            transition: background-color 0.3s, transform 0.2s; /* Transition effects */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
            margin: 10px; /* Margin for spacing */
            width: 9%;
            margin-bottom: -50px;
            margin-top: -30px;
            margin-left: -10px;
        }

        #backButton:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        #backButton:active {
            transform: translateY(0); /* Reset lift effect on click */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Reduce shadow on click */
        }
    </style>

    <script>
        // Function to show success message
        function showSuccessMessage() {
            alert("<?php echo isset($successMessage) ? $successMessage : ''; ?>");
            // Redirect to faculty_dashboard.php after showing the success message
            setTimeout(function() {
                window.location.href = "faculty_dashboard.php";
            }, 1000);
        }

        // Validate time (8 AM - 3 PM) and date (Weekdays only)
        function validateForm() {
            const appointmentDate = new Date(document.querySelector('input[name="appointment_date"]').value);
            const appointmentTime = document.querySelector('input[name="appointment_time"]').value;
            const reason = document.querySelector('textarea[name="reason_for_appointment"]').value;

            const selectedHour = parseInt(appointmentTime.split(':')[0]);

            // Validate that the selected time is between 8:00 AM and 3:00 PM
            if (selectedHour < 8 || selectedHour >= 15) {
                alert("Please select a time between 8:00 AM and 3:00 PM.");
                return false;
            }

            // Validate that the selected date is a weekday (Monday to Friday)
            const dayOfWeek = appointmentDate.getUTCDay(); // 0 is Sunday, 6 is Saturday
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                alert("Please select a weekday (Monday to Friday).");
                return false;
            }

            if (reason.length < 10) {
                alert("Please enter a more detailed reason.");
                return false;
            }

            return true;
        }

        window.onload = function() {
            if ("<?php echo isset($successMessage) ? $successMessage : ''; ?>") {
                showSuccessMessage();
            }
        };
    </script>
</head>
<body>




    <style>
    .bold-label {
        font-weight: bold; /* Makes the text bold */
        color: blue;
        text-transform: uppercase;
    }
</style>

</body>
</html>
