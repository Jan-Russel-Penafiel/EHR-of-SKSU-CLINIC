<?php
// Include database connection function
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
$conn = checkConnection(null); // Ensure connection is open before querying

// Get the month and year from the URL or set to the current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Get appointments for students
$studentQuery = "SELECT Appointment_Date, Appointment_Time, IDNumber, FirstName, LastName FROM appointments";
$studentAppointments = mysqli_query($conn, $studentQuery);

// Check for errors in the student query
if (!$studentAppointments) {
    die("Query failed: " . mysqli_error($conn));
}

// Get appointments for faculty
$facultyQuery = "SELECT Appointment_Date, Appointment_Time, IDNumber, FirstName, LastName FROM faculty_appointments";
$facultyAppointments = mysqli_query($conn, $facultyQuery);

// Check for errors in the faculty query
if (!$facultyAppointments) {
    die("Query failed: " . mysqli_error($conn));
}

// Store appointments in an array
$appointments = [];

// Process student appointments
while ($appointment = mysqli_fetch_assoc($studentAppointments)) {
    $date = $appointment['Appointment_Date'];
    $time = $appointment['Appointment_Time'];

    // Convert time to 12-hour format with AM/PM
    $formattedTime = date("h:i A", strtotime($time));

    // Add student appointment to appointments array with a precise link
    $appointments[$date]['students'][] = '<a href="display_online_appointment.php?type=student&name=' . urlencode($appointment['FirstName'] . ' ' . $appointment['LastName']) . '&id=' . $appointment['IDNumber'] . '&date=' . urlencode($date) . '&time=' . urlencode($formattedTime) . '&from=calendar">ID: ' . $appointment['IDNumber'] . ' - ' . htmlspecialchars($appointment['FirstName'] . ' ' . $appointment['LastName']) . ' (Student)</a> at ' . $formattedTime;
}

// Process faculty appointments
while ($appointment = mysqli_fetch_assoc($facultyAppointments)) {
    $date = $appointment['Appointment_Date'];
    $time = $appointment['Appointment_Time'];

    // Convert time to 12-hour format with AM/PM
    $formattedTime = date("h:i A", strtotime($time));

    // Add faculty appointment to appointments array with a precise link
    $appointments[$date]['faculty'][] = '<a href="display_online_appointment.php?type=faculty&name=' . urlencode($appointment['FirstName'] . ' ' . $appointment['LastName']) . '&id=' . $appointment['IDNumber'] . '&date=' . urlencode($date) . '&time=' . urlencode($formattedTime) . '&from=calendar">ID: ' . $appointment['IDNumber'] . ' - ' . htmlspecialchars($appointment['FirstName'] . ' ' . $appointment['LastName']) . ' (Personnel)</a> at ' . $formattedTime;
}

// Function to generate calendar
function generateCalendar($appointments, $month, $year) {
    // Get the first day of the month
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $totalDays = date('t', $firstDay);
    $dayOfWeek = date('w', $firstDay);

    // Create the calendar header
    $calendar = '<h2 class="text-center mb-4">' . date('F Y', $firstDay) . '</h2>';
    $calendar .= '<table class="table table-bordered table-striped text-center"><tr>';

    // Days of the week
    $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    foreach ($daysOfWeek as $day) {
        $calendar .= '<th class="bg-primary text-white">' . $day . '</th>';
    }
    $calendar .= '</tr><tr>';

    // Fill in the days
    for ($i = 0; $i < $dayOfWeek; $i++) {
        $calendar .= '<td></td>'; // Empty cells before the first day
    }

    for ($day = 1; $day <= $totalDays; $day++) {
        $currentDate = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
        $studentAppointmentsList = isset($appointments[$currentDate]['students']) ? implode('<br>', $appointments[$currentDate]['students']) : '';
        $facultyAppointmentsList = isset($appointments[$currentDate]['faculty']) ? implode('<br>', $appointments[$currentDate]['faculty']) : '';
        
        // Combine student and faculty appointments
        $appointmentsList = $studentAppointmentsList . ($studentAppointmentsList && $facultyAppointmentsList ? '<br>' : '') . $facultyAppointmentsList;

        if (($day + $dayOfWeek - 1) % 7 == 0) {
            $calendar .= '</tr><tr>'; // Start a new row for each week
        }

        // Highlight the cell if there are appointments
        $cellContent = $day . '<br>' . $appointmentsList;
        $calendar .= '<td class="' . (empty($appointmentsList) ? '' : 'bg-success text-white') . '">' . $cellContent . '</td>'; // Add day and appointments
    }

    $calendar .= '</tr></table>'; // Close the table
    return $calendar;
}

// Generate the calendar HTML
$calendarHtml = generateCalendar($appointments, $month, $year);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f0f4f8; /* Light background color */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            color: #333; /* Darker text for better readability */
        }

        body {
            background-image: url(image.jpeg);
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
        }

        h1 {
            margin-bottom: 20px;
            font-weight: bold;
            color: #007bff;
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: auto; /* Center the table */
        }
        th, td {
            padding: 15px;
            border: 1px solid #dee2e6;
        }
        .bg-success {
            background-color: green !important; /* Blue for appointments */
            padding: 5px; /* Reduce padding for smaller container */
            font-size: 0.7em; /* Slightly smaller font size */
            width: 155px; /* Set a specific width */
        }

        .text-white {
            color: white !important;
        }

        /* Style for links */
        a {
            color: #007bff; /* Bootstrap primary color */
            text-decoration: none; /* Remove underline */
            font-weight: bold; /* Make the text bold */
        }
        /* Change color on hover */
        a:hover {
            color: #0056b3; /* Darker shade for hover effect */
            text-decoration: underline; /* Underline on hover for better visibility */
        }
        /* Style for successful appointments */
        .bg-success a {
            color: white; /* White text for links in success background */
        }
        .bg-success a:hover {
            color: #d4d4d4; /* Light color on hover for better contrast */
        }
        /* Form styling */
        .form-control {
            border-radius: 30px; /* Rounded corners */
            border: 1px solid #007bff; /* Custom border color */
        }
        .btn-primary {
            border-radius: 30px; /* Rounded corners for buttons */
            padding: 10px 20px; /* More padding for buttons */
            transition: background-color 0.3s; /* Smooth transition for hover */
        }
        .btn-primary:hover {
            background-color: #0056b3; /* Darker blue on hover */
            border-color: #004085; /* Darker border on hover */
        }

        /* Custom styles for the Back button */
        .btn-secondary {
            background-color: green; /* Grey background color */
            color: white; /* Text color */
            border-radius: 30px; /* Rounded corners */
            padding: 10px 20px; /* Padding for a larger click area */
            text-decoration: none; /* Remove underline */
            transition: background-color 0.3s; /* Smooth transition */
        }

        /* Hover effect for the Back button */
        .btn-secondary:hover {
            background-color: darkblue; /* Darker grey on hover */
            color: #f8f9fa; /* Slightly lighter text color */
        }

        /* Responsive styling */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column; /* Stack the form controls vertically on small screens */
            }
            .form-group {
                width: 100%; /* Full width for form groups */
                margin-bottom: 10px; /* Space between form groups */
            }
        }

        /* New style for light blue background */
        .bg-light-blue {
            background-color: rgba(173, 216, 230, 0.50); /* Light blue background */
            border-radius: 10px; /* Optional: rounded corners */
            padding: 20px; /* Optional: padding for spacing */
        }
    </style>
    <title>Appointments Calendar</title>
</head>
<body>
    <div class="container mt-1 bg-light-blue"> <!-- Added custom class -->
        <h1>APPOINTMENTS CALENDAR</h1>
        
        <!-- Month and Year Navigation Form -->
        <form method="GET" class="mb-0 text-center">
    <div class="form-row justify-content-center">
    <div class="form-group col-auto">
    <a href="display_online_appointment.php" class="btn btn-secondary btn-back">Back</a>
</div>

        <div class="form-group col-auto">
            <select name="month" class="form-control" required>
                <?php 
                for ($m = 1; $m <= 12; $m++) {
                    $selected = ($m == $month) ? 'selected' : '';
                    echo "<option value='$m' $selected>" . date('F', mktime(0, 0, 0, $m, 1)) . "</option>";
                }
                ?>
            </select>
        </div>
       
        <div class="form-group col-auto">
            <select name="year" class="form-control" required>
                <?php 
                $currentYear = date('Y');
                for ($y = $currentYear - 5; $y <= $currentYear + 5; $y++) {
                    $selected = ($y == $year) ? 'selected' : '';
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group col-auto">
            <button type="submit" class="btn btn-primary">View</button>
        </div>
    </div>
</form>


        <!-- Bootstrap Card for the Calendar -->
        <div class="card">
            <div class="card-body">
              
                <?= $calendarHtml ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
</body>
</html>

