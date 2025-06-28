<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");
    session_start();


// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get current month and year, or override with GET parameters for month navigation
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$currentYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Process form submission to add or edit unavailability
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = mysqli_real_escape_string($conn, $_POST['unavailable_date']);
    $startTime = mysqli_real_escape_string($conn, $_POST['unavailable_start_time']);
    $endTime = mysqli_real_escape_string($conn, $_POST['unavailable_end_time']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $editId = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : null;

    if ($editId) {
        // Update existing unavailability
        $queryUpdate = "UPDATE nurse_schedule SET unavailable_date='$date', unavailable_start_time='$startTime', unavailable_end_time='$endTime', reason='$reason' WHERE Num='$editId'";
        mysqli_query($conn, $queryUpdate);
    } else {
        // Insert new unavailability
        $queryInsert = "INSERT INTO nurse_schedule (unavailable_date, unavailable_start_time, unavailable_end_time, reason) VALUES ('$date', '$startTime', '$endTime', '$reason')";
        mysqli_query($conn, $queryInsert);
    }
}

// Process delete action
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $queryDelete = "DELETE FROM nurse_schedule WHERE Num='$deleteId'";
    mysqli_query($conn, $queryDelete);
    header("Location: sched_calendar.php?month=$currentMonth&year=$currentYear"); // Refresh page after deletion
    exit();
}

// Retrieve unavailable days for the selected month and year
$query = "SELECT Num, unavailable_date, unavailable_start_time, unavailable_end_time, reason FROM nurse_schedule 
          WHERE MONTH(unavailable_date) = '$currentMonth' AND YEAR(unavailable_date) = '$currentYear' ";
$result = mysqli_query($conn, $query);

$unavailability = [];
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

mysqli_close($conn);

// Get the name of the month (e.g., "January", "February")
$monthName = date("F", strtotime("$currentYear-$currentMonth-01"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Calendar</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <style>
/* General body and layout */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
    background-color: #f4f7fc;
    margin: 0;
    padding: 0;
}
body {
    background-image: url(image.jpeg);
    background-repeat: no-repeat;
    background-size: 100% 100%;
    background-attachment: fixed;
    
}

.container {
    width: 100%;
    max-width: 1300px;
    margin: 0 auto;
    padding: 20px;
    background-color: rgba(173, 216, 230, 0.50);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    margin-top: 0px;
}

/* Header Section */
header h1 {
    text-align: center;
    font-size: 2.5em;
    color: #4e73df;
    margin-bottom: -10px;
    margin-top: -13px;
}

/* Navigation for previous and next months */
.navigation {
    margin: 20px 0;
    font-size: 1.2em;
    text-align: center;
}

.navigation a {
    text-decoration: none;
    color: #007bff;
    padding: 10px 18px;
    border-radius: 25px;
    background-color: #ffffff;
    margin: 0 15px;
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
    font-size: 1.8em;
    color: blue;
    margin-bottom: 30px;
}

/* Calendar Layout */
.calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10px;
    padding: 5px;
    background-color: #f9f9f9;
    border-radius: 8px;
    font-size: 15px;
}

.day-header {
    font-weight: bold;
    text-align: center;
    background-color: #007bff;
    color: #fff;
    padding: 10px;
    border-radius: 5px;
}

.day {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 15px;
    border-radius: 5px;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    position: relative;
    transition: background-color 0.3s;
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
    top: -70px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: #fff;
    padding: 5px;
    font-size: 0.9em;
    border-radius: 5px;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
    width: 180px;
    text-align: center;
}

.day.unavailable:hover .tooltip {
    display: block;
    opacity: 1;
}

/* Modal for schedule details */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    max-width: 400px;
    width: 100%;
}

.modal-header h3 {
    margin: 0;
    color: #007bff;
}

.modal-body {
    margin-top: 15px;
    
}

.modal-footer {
    margin-top: 15px;
    text-align: center;
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
#backButton {
            display: inline-block; /* Allows padding and margin */
            background-color: #007bff; /* Blue background */
            color: white; /* White text */
            padding: 10px 15px; /* Padding around the text */
            font-size: 16px; /* Font size */
            border-radius: 5px; /* Rounded corners */
            text-decoration: none; /* Remove underline */
            transition: background-color 0.3s, transform 0.2s; /* Transition effects */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
            margin: 10px; /* Margin for spacing */
            width: 2.6%;
            margin-bottom: -10px;
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

/* Responsive Design */
@media screen and (max-width: 768px) {
    .calendar {
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }
    .day-header, .day {
        font-size: 1em;
        padding: 10px;
    }
    .navigation a {
        font-size: 1em;
    }
}

/* Styling for the delete button */
.delete-btn {
    background-color: #f44336; /* Red background for delete */
    color: white; /* White text color */
    border: none; /* No border */
    padding: 8px 5px; /* Padding inside the button */
    font-size: 14px; /* Font size */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    transition: background-color 0.3s ease; /* Smooth background color transition */
    margin-right: -50px;
    
}

.delete-btn:hover {
    background-color: #d32f2f; /* Darker red when hovering */
}

.delete-btn:focus {
    outline: none; /* Remove focus outline */
}

.delete-btn:active {
    background-color: #b71c1c; /* Even darker red when button is clicked */
}


    </style>
</head>
<body>
<div class="container">
    <div class="main-content">
        <a id="backButton" href="dashboard.php">Back</a>
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
                    $readOnlyClass = $isSundayOrSaturday ? 'read-only' : ''; // Add read-only class for Sunday and Saturday
                    echo "<div class='$dayClass $currentDayClass $readOnlyClass' data-date='$date' ";
                    if (!$isSundayOrSaturday) {
                        echo "onclick='openModal(\"$date\")'"; // Only add onclick for non-Sunday/Saturday
                    }
                    echo ">";
                    echo "<span>$currentDay</span>";
                    if ($tooltip) {
                        echo "<div class='tooltip'>$tooltip</div>";
                    }
                    if ($dayUnavailable) {
                        // Add a Delete button for unavailable days
                        echo "<button class='delete-btn' onclick='deleteUnavailableDate(\"$date\")'>Delete</button>";
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

<script>
    function deleteUnavailableDate(date) {
        if (confirm("Are you sure you want to delete this unavailable date from the schedule?")) {
            // Send an AJAX request to delete the unavailability from the nurse_schedule table
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "delete_unavailability.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    alert("Unavailable date deleted successfully.");
                    location.reload(); // Reload the page to reflect the changes
                } else {
                    alert("An error occurred while deleting the unavailable date.");
                }
            };
            xhr.send("date=" + encodeURIComponent(date)); // Send the date to be deleted
        }
    }
</script>


        <!-- Modal structure -->
        <div id="modal" class="modal">
            <div class="modal-content">
                <span id="closeModalBtn" class="close-btn">&times;</span>
                <h2>ADD UNAVAILABILITY</h2>
                <form action="sched_calendar.php?month=<?php echo $currentMonth; ?>&year=<?php echo $currentYear; ?>" method="POST" id="unavailabilityForm">
                    <div class="form-group">
                        <label for="unavailable_date"> Date:</label>
                        <input type="date" name="unavailable_date" id="unavailable_date" required>
                    </div>

                    <div class="form-group">
                <label for="unavailable_start_time">Start Time:</label>
                <select name="unavailable_start_time" id="unavailable_start_time" required>
                    <option value="08:00">08:00 AM</option>
                    <option value="08:30">08:30 AM</option>
                    <option value="09:00">09:00 AM</option>
                    <option value="09:30">09:30 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="10:30">10:30 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="11:30">11:30 AM</option>
                    <option value="01:00">01:00 PM</option>
                    <option value="01:30">01:30 PM</option>
                    <option value="02:00">02:00 PM</option>
                    <option value="02:30">02:30 PM</option>
                    <option value="03:00">03:00 PM</option>
                    <option value="03:30">03:30 PM</option>
                    <option value="04:00">04:00 PM</option>
                </select>
            </div>

            <div class="form-group">
                <label for="unavailable_end_time">End Time:</label>
                <select name="unavailable_end_time" id="unavailable_end_time" required>
                    <option value="08:00">08:00 AM</option>
                    <option value="08:30">08:30 AM</option>
                    <option value="09:00">09:00 AM</option>
                    <option value="09:30">09:30 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="10:30">10:30 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="11:30">11:30 AM</option>
                    <option value="01:00">01:00 PM</option>
                    <option value="01:30">01:30 PM</option>
                    <option value="02:00">02:00 PM</option>
                    <option value="02:30">02:30 PM</option>
                    <option value="03:00">03:00 PM</option>
                    <option value="03:30">03:30 PM</option>
                    <option value="04:00">04:00 PM</option>
                </select>
            </div>


                    <div class="form-group">
                        <label for="reason">Reason:</label>
                        <textarea name="reason" id="reason" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="submit-btn">Save Unavailability</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to handle modal -->
<script>
    function openModal(date) {
        const modal = document.getElementById('modal');
        const dateInput = document.getElementById('unavailable_date');
        modal.style.display = 'block'; // Show modal
        dateInput.value = date; // Pre-fill the date input field with the clicked date
    }

    // Close modal when 'X' is clicked
    document.getElementById('closeModalBtn').addEventListener('click', () => {
        document.getElementById('modal').style.display = 'none';
    });

    // Close modal when clicking outside the modal content
    window.addEventListener('click', (event) => {
        const modal = document.getElementById('modal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
</script>



<!-- Modal styles -->
<style>
    /* Modal styles */
    .modal {
        display: none; /* Hidden by default */
        position: fixed;
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%;
        height: 120%;
        background-color: rgba(0, 0, 0, 0.6); /* Black with opacity */
        padding-top: 0px;
        transition: opacity 0.3s ease; /* Fade in/out transition */
        margin-top:-60px ;
    }

    .modal-content {
        background-color: lightblue;
        margin: 9% auto;
        padding: 10px;
        border-radius: 10px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transform: translateY(-30px);
        transition: transform 0.3s ease; /* Slide in effect */
    }

    .close-btn {
      
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        position: absolute; right: 10px; top: 10px;
    }

    .close-btn:hover,
    .close-btn:focus {
        color: black;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 15px;
    }

    label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    input, select, textarea {
        width: 96%;
        padding: 10px;
        margin-top: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 16px;
    }
select{
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 16px;
    }
    .submit-btn {
        padding: 12px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .submit-btn:hover {
        background-color: blue;
    }

    /* Button to open the modal */
    .open-modal-btn {
        padding: 10px 15px;
        background-color: #008CBA;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        margin-left: 530px;
    }

    .open-modal-btn:hover {
        background-color: green;
    }

    /* Responsive design */
    @media (max-width: 600px) {
        .modal-content {
            width: 95%;
        }

        .open-modal-btn {
            font-size: 14px;
            padding: 8px 12px;
        }

        .submit-btn {
            font-size: 14px;
            padding: 10px 15px;
        }
    }
</style>

<!-- Modal script -->



</body>
</html>
