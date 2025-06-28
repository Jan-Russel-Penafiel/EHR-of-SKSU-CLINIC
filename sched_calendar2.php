<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");
    session_start();



// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get current month and year, or override with GET parameters for month navigation
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Retrieve unavailable days for the selected month and year
$query = "SELECT Num, unavailable_date, unavailable_start_time, unavailable_end_time, reason 
          FROM nurse_schedule 
          WHERE MONTH(unavailable_date) = '$currentMonth' 
          AND YEAR(unavailable_date) = '$currentYear'";
$result = mysqli_query($conn, $query);

$unavailability = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $dateOnly = date("Y-m-d", strtotime($row['unavailable_date']));
        $startTimeOnly = date("H:i", strtotime($row['unavailable_start_time']));
        $endTimeOnly = date("H:i", strtotime($row['unavailable_end_time']));
        $unavailability[$dateOnly] = [
            'Num' => $row['Num'], 
            'reason' => $row['reason'], 
            'start_time' => $startTimeOnly, 
            'end_time' => $endTimeOnly
        ];
    }
}

// Get the user's email from the session
$GmailAccount = $_SESSION['GmailAccount'];

// Query to get faculty info for form pre-fill
$queryPersonalInfo = "SELECT IDNumber, Rank, FirstName, LastName, Department, Position FROM faculty WHERE GmailAccount = '$GmailAccount'";
$resultPersonalInfo = mysqli_query($conn, $queryPersonalInfo);

if ($resultPersonalInfo && mysqli_num_rows($resultPersonalInfo) > 0) {
    $userInfo = mysqli_fetch_assoc($resultPersonalInfo);
    $IDNumber = $userInfo['IDNumber'];
    $Rank = $userInfo['Rank'];
    $FirstName = $userInfo['FirstName'];
    $LastName = $userInfo['LastName'];
    $Department = !empty($userInfo['Department']) ? $userInfo['Department'] : "NONE"; // Default to "NONE" if no department value
    $Position = $userInfo['Position'];
} else {
    // If no user info is found, return a proper error message
    die("Error: Unable to fetch personal information. Please contact support.");
}

// Check how many appointments the faculty has made in the current month and year
$queryAppointmentCount = "SELECT COUNT(*) AS appointment_count FROM faculty_appointments 
                          WHERE IDNumber = '$IDNumber' 
                          AND MONTH(Appointment_Date) = '$currentMonth' 
                          AND YEAR(Appointment_Date) = '$currentYear'";
$resultAppointmentCount = mysqli_query($conn, $queryAppointmentCount);

$appointmentCount = 0; // Default value
if ($resultAppointmentCount) {
    $appointmentData = mysqli_fetch_assoc($resultAppointmentCount);
    $appointmentCount = $appointmentData['appointment_count'] ?? 0;
}

// Handle form submission for booking appointments
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $appointmentDate = mysqli_real_escape_string($conn, $_POST['appointment_date'] ?? '');
    $appointmentTime = mysqli_real_escape_string($conn, $_POST['appointment_time'] ?? '');
    $reasonForAppointment = mysqli_real_escape_string($conn, $_POST['reason_for_appointment'] ?? '');

    if (empty($appointmentDate) || empty($appointmentTime) || empty($reasonForAppointment)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    // Check if appointment limit has been reached
    if ($appointmentCount >= 2) {
        echo json_encode(['success' => false, 'message' => 'You have already booked 2 appointments this month.']);
        exit();
    }

    // Insert the appointment data into the faculty_appointments table
    $queryInsertAppointment = "INSERT INTO faculty_appointments (IDNumber, FirstName, LastName, Department, Position, Appointment_Date, Appointment_Time, Reason) 
    VALUES ('$IDNumber', '$FirstName', '$LastName', '$Department', '$Position', '$appointmentDate', '$appointmentTime', '$reasonForAppointment')";

    if (mysqli_query($conn, $queryInsertAppointment)) {
        // Set success message in session
        $_SESSION['appointment_success_message'] = 'Appointment booked successfully!';

        // Redirect to the faculty dashboard after successful appointment booking
        header('Location: faculty_dashboard.php');
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . mysqli_error($conn)]);
        exit();
    }
}


// Close the database connection
mysqli_close($conn);

// Get the name of the month (e.g., "January", "February")
$monthName = date("F", strtotime("$currentYear-$currentMonth-01"));
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Calendar</title>

</head>
<style>
/* General body and layout */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7fc;
    margin: 0;
    padding: 0;
    height: 100%;
}

.container {
    width: 95%;
    max-width: 1250px;
    margin: 0 auto;
    padding: 20px;
    background-color: rgba(173, 216, 230, 0.50);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    height: 89vh;
}

/* Header Section */
header h1 {
    text-align: center;
    font-size: 4.5em;
    color: #4e73df;
    margin-bottom: 10px;
    font-weight: bolder;
}

.navigation {
    margin: 15px 0;
    font-size: 2em;
    text-align: center;
}

.navigation a {
    text-decoration: none;
    color: #007bff;
    padding: 8px 15px;
    border-radius: 25px;
    background-color: #ffffff;
    margin: 0 10px;
    font-weight: bold;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.navigation a:hover {
    background-color: #0056b3;
    color: white;
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
}

/* Display Current Month Name */
h2 {
    text-align: center;
    font-size: 2.6em;
    color: blue;
    margin-bottom: 20px;
}

/* Calendar Layout */
.calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10px;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 8px;
    font-size: 0.9em;
    overflow-y: auto;
    flex-grow: 1;
    height: 90%;
}

.day-header {
    font-weight: bold;
    text-align: center;
    background-color: #007bff;
    color: #fff;
    padding: 10px;
    border-radius: 5px;
    height: 70%;
    font-size: 25px;
    margin-bottom: -100px;
}

.day {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 12px;
    border-radius: 5px;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    position: relative;
    transition: background-color 0.3s;
    font-size: 40px;
}

.day.unavailable {
    background-color: #f8d7da;
    color: #721c24;
}

.day:hover {
    background-color: #e9ecef;
    transform: scale(1.05);
}
.tooltip {
    position: absolute;
    top: -80px; /* Slightly adjusted for a better position */
    left: 80%;
    transform: translateX(-50%);
    background-color: #4e73df; /* Soft blue color */
    color: #fff;
    padding: 10px 15px; /* Increased padding for better readability */
    font-size: 0.55em; /* Slightly larger font size for better visibility */
    border-radius: 8px; /* Rounded corners for a smoother look */
    display: none;
    opacity: 0;
    transition: opacity 0.4s ease, transform 0.3s ease-in-out; /* Smooth fade and sliding effect */
    width: 200px; /* Slightly wider for more content */
    text-align: center;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); /* Added shadow for depth */
    z-index: 10; /* Ensures the tooltip stays on top of other elements */
}

/* Tooltip display animation */
.day.unavailable:hover .tooltip {
    display: block;
    opacity: 1;
    transform: translateX(-50%) translateY(-10px); /* Tooltip gently slides up */
}

/* Optional: Add a small arrow effect for the tooltip */
.tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 8px solid #4e73df; /* Matches the tooltip background */
}


.day.unavailable:hover .tooltip {
    display: block;
    opacity: 1;
}

.close-btn {
    background-color: #007bff;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
}

/* Background Image */
body {
    background-image: url(image.jpeg);
    background-repeat: no-repeat;
    background-size: cover;
    background-attachment: fixed;
}

/* Back Button Styles */
#backButton {
    display: inline-block;
    background-color: #007bff;
    color: white;
    padding: 30px 30px;
    font-size: 2em;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s, transform 0.2s;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    margin: 10px;
    width: auto;
}

#backButton:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
}

#backButton:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Responsive Design for Phones (16:9 ratio) */
@media screen and (max-width: 768px) {
    .calendar {
        grid-template-columns: repeat(3, 1fr); /* Three columns for mobile phones */
        gap: 5px;
        font-size: 0.8em;
    }

    .day-header, .day {
        font-size: 0.8em;
        padding: 6px;
    }

    .navigation a {
        font-size: 0.9em;
        padding: 6px 10px;
    }

    h2 {
        font-size: 1.4em;
    }
}
.day.read-only {
    background-color: #f0f0f0; /* Lighter color to indicate read-only */
    pointer-events: none; /* Disable click interaction */
}

.day.read-only span {
    color: #ccc; /* Gray out the text */
}



</style>

<body>
<div class="container">
    <div class="main-content">
        <header>
        <h1>SCHOOL NURSE SCHEDULE CALENDAR</h1>
        </header>
        <h2><?php echo $monthName . " " . $currentYear; ?></h2>

        <div class="navigation">
            <a href="?month=<?php echo $currentMonth == 1 ? 12 : $currentMonth - 1; ?>&year=<?php echo $currentMonth == 1 ? $currentYear - 1 : $currentYear; ?>">Previous Month</a>
            <a href="?month=<?php echo $currentMonth == 12 ? 1 : $currentMonth + 1; ?>&year=<?php echo $currentMonth == 12 ? $currentYear + 1 : $currentYear; ?>">Next Month</a>
        </div>

        <div class="calendar">
    <?php
    $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    foreach ($daysOfWeek as $day) {
        echo "<div class='day-header'>$day</div>";
    }

    $firstDayOfMonth = date('w', strtotime("$currentYear-$currentMonth-01"));
    $daysInMonth = date('t', strtotime("$currentYear-$currentMonth-01"));
    $currentDay = 1;
    for ($i = 0; $i < 42; $i++) {
        $isSundayOrSaturday = ($i % 7 == 0 || $i % 7 == 6); // Check if it's a Sunday (0) or Saturday (6)
        if ($i >= $firstDayOfMonth && $currentDay <= $daysInMonth) {
            $date = "$currentYear-$currentMonth-" . str_pad($currentDay, 2, "0", STR_PAD_LEFT);
            $dayUnavailable = isset($unavailability[$date]);
            $tooltip = $dayUnavailable ? "Unavailable Day: " . $unavailability[$date]['reason'] . " from " . $unavailability[$date]['start_time'] . " to " . $unavailability[$date]['end_time'] : '';
            $dayClass = $dayUnavailable ? 'day unavailable' : 'day';
            $currentDayClass = ($date == date("Y-m-d")) ? 'current-day' : '';
            $readOnlyClass = ($isSundayOrSaturday || $dayUnavailable) ? : ''; // Add read-only class for Sunday, Saturday, and unavailable days

            // Check if the day is available to open modal (non-weekend and not unavailable)
            $modalAction = (!$isSundayOrSaturday && !$dayUnavailable) ? "onclick='openModal(\"$date\")'" : "onclick='showUnavailableMessage()'"; // Open modal or show message for unavailable days

            echo "<div class='$dayClass $currentDayClass $readOnlyClass' data-date='$date' $modalAction>";
            echo "<span>$currentDay</span>";
            if ($tooltip) {
                echo "<div class='tooltip'>$tooltip</div>";
            }
            echo "</div>";
            $currentDay++;
        } else {
            echo "<div class='day'></div>";
        }
    }
    ?>
</div>
</div>
</div>

<!-- Appointment Modal -->
<div id="appointmentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h1 class="modal-title">Book Online Appointment</h1>

        <?php if (isset($errorMessage)): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Appointment Form -->
        <form id="appointmentForm" method="POST" action="online_appointment1.php" onsubmit="return validateForm()">
            <!-- Pre-filled User Info -->
            <div class="form-group">
                <label class="bold-label">Full Name:</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($FirstName . ' ' . $LastName); ?>" readonly>
            </div>
            <div class="form-group">
                <label class="bold-label">Rank:</label>
                <input type="text" name="Rank" value="<?php echo htmlspecialchars($Rank); ?>" readonly>
            </div>
            <div class="form-group">
                <label class="bold-label">Department:</label>
                <input type="text" name="Department" value="<?php echo htmlspecialchars($Department); ?>" readonly>
            </div>
            <div class="form-group">
                <label class="bold-label">Position:</label>
                <input type="text" name="Position" value="<?php echo htmlspecialchars($Position); ?>" readonly>
            </div>

            <!-- Appointment Date -->
            <div class="form-group">
                <label for="appointment_date_modal" class="bold-label">Appointment Date:</label>
                <input type="date" id="appointment_date_modal" name="appointment_date" required>
            </div>

            <script>
                // Set today as the default date and restrict to future dates only
                const today = new Date().toISOString().split('T')[0]; // Format to YYYY-MM-DD
                document.getElementById("appointment_date_modal").value = today; // Set default date
                document.getElementById("appointment_date_modal").setAttribute("min", today); // Restrict to future dates only
            </script>

            <!-- Appointment Time -->
            <div class="form-group">
                <label for="appointment_time" class="bold-label">Appointment Time:</label>
                <select id="appointment_time" name="appointment_time" required>
                    <option value="" disabled selected>--Select Time--</option>
                    <?php
                    // Define time slots for appointments in 12-hour format with AM/PM
                    $timeSlots = [
                        '08:00 AM', '08:30 AM', '09:00 AM', '09:30 AM',
                        '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
                        '01:00 PM', '01:30 PM',
                        '02:00 PM', '02:30 PM', '03:00 PM'
                    ];

                    foreach ($timeSlots as $time) {
                        echo "<option value=\"" . date('H:i', strtotime($time)) . "\">$time</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Reason for Appointment -->
            <div class="form-group">
                <label for="reason_for_appointment" class="bold-label">Reason:</label>
                <textarea id="reason_for_appointment" name="reason_for_appointment" rows="4" placeholder="Reason for Appointment" required></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Book Appointment</button>
        </form>
    </div>
</div>

<script>
    // Show message when clicking an unavailable day
    function showUnavailableMessage() {
        alert("This day is unavailable for booking. Please select another day.");
    }
</script>




<script>
    // Function to show a success message
    function showSuccessMessage() {
        alert("<?php echo isset($successMessage) ? $successMessage : ''; ?>");
        // Redirect to home.php after showing the success message
        setTimeout(function () {
            window.location.href = "faculty_dashboard.php";
        }, 1000); // Redirect after 1 second
    }

    // Validate date and time on the client side
    function validateForm() {
        const appointmentDate = new Date(document.querySelector('input[name="appointment_date"]').value);
        const appointmentTime = document.querySelector('select[name="appointment_time"]').value;
        const reason = document.querySelector('textarea[name="reason_for_appointment"]').value;

        // Check if appointment time is selected
        if (!appointmentTime) {
            alert("Please select a valid appointment time.");
            return false;
        }

        const selectedHour = parseInt(appointmentTime.split(':')[0]);
        const selectedMinutes = parseInt(appointmentTime.split(':')[1]);

        // Validate that the selected time is between 8:00 AM and 3:00 PM
        if (selectedHour < 8 || (selectedHour === 15 && selectedMinutes > 0) || selectedHour > 15) {
            alert("Please select a time between 8:00 AM and 3:00 PM.");
            return false;
        }

        // Validate that the selected date is a weekday (Monday to Friday)
        const dayOfWeek = appointmentDate.getDay(); // 0 is Sunday, 6 is Saturday
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            alert("Please select a weekday (Monday to Friday).");
            return false;
        }

        // Check for minimum length of the reason
        if (reason.length < 10) {
            alert("Please provide a more detailed reason (at least 10 characters).");
            return false;
        }

        return true; // Form is valid
    }

    // Automatically show success message if it exists
    window.onload = function () {
        if ("<?php echo isset($successMessage) ? $successMessage : ''; ?>") {
            showSuccessMessage();
        }
    };
</script>

<style>
  /* General Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6); /* Darker overlay for better focus */
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background-color: lightblue;
    margin: 20% auto; /* Reduced margin for a bigger modal */
    padding: 60px; /* Increased padding for more space */
    border-radius: 10px;
    width: 70%; /* Reduced width to make the modal even bigger */
    max-width: 1200px; /* Increased max-width */
 
    overflow-y: auto; /* Allow vertical scrolling if content overflows */
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.5); /* Stronger shadow for emphasis */
    font-family: Arial, sans-serif;
    animation: slideIn 0.3s ease;
}

.close {
    float: right;
    font-size: 5em; /* Increased close button font size */
    font-weight: bold;
    cursor: pointer;
    color: #555;
    transition: color 0.3s ease;
}

.close:hover {
    color: #000;
}

.modal-title {
    font-size: 5rem; /* Increased font size */
    font-weight: bold;
    margin-bottom: 30px; /* Increased margin for more spacing */
    color: #333;
    text-align: center;
}

.form-group {
    margin-bottom: 20px; /* Increased margin for form spacing */
    font-size: 3rem;
}

.bold-label {
    font-weight: bold;
    font-size: 1.5rem; /* Increased label font size */
    color: #444;
    display: block;
    margin-bottom: 10px; /* More space below the label */
}

input, select, textarea {
    width: 100%;
    padding: 14px; /* Increased padding */
    font-size: 2rem; /* Increased font size for inputs */
    border: 1px solid #ccc;
    border-radius: 5px;
    font-family: Arial, sans-serif;
    box-sizing: border-box;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

input:focus, select:focus, textarea:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
    outline: none;
}

textarea {
    resize: none;
    min-height: 150px; /* Increased height for textarea */
}

.submit-button {
    background-color: #4CAF50;
    color: white;
    padding: 16px 24px; /* Increased padding */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 2rem; /* Increased font size */
    font-weight: bold;
    width: 100%;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.submit-button:hover {
    background-color: #45a049;
    transform: scale(1.03);
}

.submit-button:active {
    transform: scale(0.98);
}

.message.error {
    color: red;
    font-weight: bold;
    margin-bottom: 20px; /* Increased space for error message */
    font-size: 1.5rem; /* Increased error message font size */
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        transform: translateY(-30px); /* Increased slide-in effect */
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

</style>



<style>
    .bold-label {
        font-weight: bold; /* Makes the text bold */
        color: blue;
        text-transform: uppercase;
    }
</style>
<style>
    /* Style the label */
    .bold-label {
        font-weight: bold;
        font-size: 1rem;
        color: #333;
        display: block;
        margin-bottom: 5px;
        font-family: Arial, sans-serif;
    }

    /* Style the select dropdown */
    #appointment_time {
        width: 100%;
        max-width: 300px; /* Max width for larger screens */
        padding: 10px;
        font-size: 1rem;
        color: #333;
      
    
        border-radius: 5px;
        appearance: none; /* Remove default browser styling */
        outline: none;
        font-family: Arial, sans-serif;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15); /* Subtle shadow */
        cursor: pointer;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    /* Change border and shadow on focus */
 

    /* Style the options within the dropdown */
    #appointment_time option {
        padding: 10px;
        font-size: 1rem;
        color: #333;
        background-color: #fff;
    }

    /* Style the placeholder option */
    #appointment_time option[disabled] {
        color: #888; /* Lighter color for disabled option */
    }
</style>

<script>
    function openModal(date) {
        document.getElementById('appointment_date_modal').value = date; // Set the selected date
        document.getElementById('appointmentModal').style.display = 'block'; // Show modal
    }

    function closeModal() {
        document.getElementById('appointmentModal').style.display = 'none'; // Hide modal
    }

    function submitAppointment(event) {
        event.preventDefault(); // Prevent form from reloading the page

        // Get form data
        const formData = new FormData(document.getElementById('appointmentForm'));
        
        // Submit via AJAX
        fetch('online_appointment1.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Show success/error message
            if (data.success) {
                closeModal();
                window.location.reload(); // Refresh calendar to reflect changes
            }
        })
        .catch(err => {
            alert("Error: Unable to book the appointment.");
        });
        
    }

    // Close modal on clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('appointmentModal');
        if (event.target === modal) {
            closeModal();
        }
    };
</script>

</body>
