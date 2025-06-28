<?php
// Start session
session_start();

// Include database connection file
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Get the current year and month name
$currentYear = date("Y");
$currentMonth = date("F"); // Full month name (e.g., "November")

// Check if a year is selected, default to current year
$selectedYear = isset($_GET['years']) ? intval($_GET['years']) : $currentYear;

// Check if a month is selected, default to current month
$selectedMonth = isset($_GET['months']) ? $_GET['months'] : $currentMonth;

// Disable foreign key checks (if needed for renumbering)
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0;");

// Renumber the `Num` column only for records within the selected year and month
$selectQuery = "SELECT Num FROM faculty 
                WHERE YEAR(AppointmentDate) = ? AND MONTHNAME(AppointmentDate) = ? 
                ORDER BY Num ASC";

$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("ss", $selectedYear, $selectedMonth);
$stmt->execute();
$result = $stmt->get_result();

$new_num = 1; // Start renumbering from 1
while ($row = $result->fetch_assoc()) {
    $old_num = $row['Num'];

    // Update `Num` for the selected year and month
    $updateQuery = "UPDATE faculty SET Num = ? WHERE Num = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $new_num, $old_num);
    $updateStmt->execute();

    $new_num++; // Increment for the next row
}

// Close the prepared statement
$stmt->close();
if (isset($updateStmt)) {
    $updateStmt->close();
}

// Re-enable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1;");

// Fetch the updated records based on the selected year and month
$sql = "SELECT Num, IDNumber, Rank, FirstName, LastName, GmailAccount, 
               IF(Department IS NULL OR Department = '', 'NONE', Department) AS Department, 
               Position,  
               IF(Complains IS NULL OR Complains = '', 'No complaints', Complains) AS Complains, 
               IF(Temperature = 0, '', Temperature) AS Temperature, 
               IF(BloodPressure = 0, '', BloodPressure) AS BloodPressure, 
               IF(HeartRate = 0, '', HeartRate) AS HeartRate, 
               IF(RespiratoryRate = 0, '', RespiratoryRate) AS RespiratoryRate,
               IF(Height = 0, '', Height) AS Height,
               IF(Weight = 0, '', Weight) AS Weight,
               IF(AppointmentDate = '0000-00-00 00:00:00' OR AppointmentDate IS NULL, '', AppointmentDate) AS AppointmentDate,
               YEAR(AppointmentDate) AS years, MONTHNAME(AppointmentDate) AS months
        FROM faculty 
        WHERE YEAR(AppointmentDate) = ? AND MONTHNAME(AppointmentDate) = ?
        ORDER BY Num ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $selectedYear, $selectedMonth);
$stmt->execute();
$resultFaculty = $stmt->get_result();

if (!$resultFaculty) {
    die("Error fetching data: " . $conn->error);
}

// Close the statement
$stmt->close();

// Don't close the connection yet, as it might be needed elsewhere
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <title>Personnel Records</title>
    <style>
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
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    text-align: center;
    padding: 20px 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Slight shadow for depth */
    height: 100px; /* Define a fixed height for better vertical alignment */
    border-radius: 8px;
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


        .resizer {
            width: 10px; /* Width of the resizer */
            cursor: ew-resize; /* Cursor style */
            position: fixed; /* Fixed position */
            top: 0;
            left: 195px; /* Initially set to match the sidebar width */
            height: 100vh; /* Full height */
            background-color: transparent; /* Invisible, can change for visibility */
            z-index: 1100; /* Above sidebar */
        }

.main-content { 
    background-color: rgba(173, 216, 230, 0.50); /* Light blue background with 50% transparency */
    border-radius: 8px;
    padding: 0px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 10.5px;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    text-align: center;
    height: auto; /* Set a fixed height */
   
}



h3 {
    margin-bottom: 20px;
    color: green; /* Keeping the green theme */
    font-size: 24px; /* Larger font for headings */
    border-bottom: 2px solid #4CAF50; /* Underline for emphasis */
    padding-bottom: 10px; /* Spacing below the heading */
}

h1 {
    margin-bottom: 5px;
    color: white; /* Keeping the green theme */
    font-size: 40px; /* Larger font for headings */
    border-bottom: 0px solid #4CAF50; /* Underline for emphasis */
    padding-bottom: 0px; /* Spacing below the heading */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 8px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    border: 0.1px solid #ddd;
    
}

th {
            background-color: green; /* Green header */
            color: white;
            font-weight: bold; /* Bold text for headers */
            cursor: pointer; /* Change cursor to pointer for clickable headers */
            
        }


tr:hover {
    background-color: lightgreen; /* Light green hover for rows */
}

.btn {
    background-color: blue; /* Blue button for better visibility */
    color: white;
    border: none;
    padding: 10px 5px; /* Increased padding */
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s; /* Added transform for effect */
}

.btn:hover {
    background-color: darkblue; /* Darker blue on hover */
    transform: scale(1.05); /* Slightly enlarges button */
}

.button {
    background-color: red; /* Blue button for better visibility */
    color: white;
    border: none;
    padding: 10px 5px; /* Increased padding */
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s; /* Added transform for effect */
}

.button:hover {
    background-color: darkred; /* Darker blue on hover */
    transform: scale(1.05); /* Slightly enlarges button */
}




        #searchInput {
            width: 50%;
            padding: 10px; /* Increased padding for comfort */
            margin: 10px 0; /* Space between elements */
            border: 1px solid #ccc; /* Border for input fields */
            border-radius: 5px; /* Rounded corners */
            font-size: 14px; /* Consistent font size */
        }

       /* CSS for the search container */

        select {
    padding: 10px;
    border: 1px solid #ccc; /* Light gray border */
    border-radius: 4px; /* Slightly rounded corners */
    font-size: 14px;
    margin-right: 20px; /* Space between dropdown and search input */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
   
}

input[type="text"] {
    padding: 10px;
    border: 1px solid #ccc; /* Light gray border */
    border-radius: 4px; /* Slightly rounded corners */
    font-size: 14px;
    width: 200px; /* Width for the search input */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
}

input[type="text"]:focus, select:focus {
    border-color: #007bff; /* Change border color on focus */
    outline: none; /* Remove default outline */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Light blue shadow on focus */
}

.alert-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
         
            margin-left: 10px;
            margin-top: 3px;
        }

        .alert-btn:hover {
            background-color: blue; /* Darker shade on hover */

    border-color: #1e7e34; /* Change border color on hover */
        }


.btn2 {
            background-color: Blue;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            margin-left: 10px;
}

.btn2:hover {
    background-color: green; /* Darker shade on hover */
    transform: translateY(-2px); /* Lift the button slightly */
    border-color: #1e7e34; /* Change border color on hover */
}

.btn2:active {
    transform: translateY(1px); /* Slightly lower when clicked */
}

.button-container {
    display: flex;
    align-items: center;
    gap: 10px;
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
.menu {
            position: relative;
            display: inline-block;
            text-align: center;
            margin-bottom: 10px; /* Space between menu and other content */
        }

        #searchInput {
            width: 50%;
            padding: 10px; /* Increased padding for comfort */
            margin: 10px 0; /* Space between elements */
            border: 1px solid #ccc; /* Border for input fields */
            border-radius: 5px; /* Rounded corners */
            font-size: 14px; /* Consistent font size */
        }

        #searchContainer {
    display: flex; /* Align items in a row */
    align-items: center; /* Center items vertically */
    justify-content: space-between; /* Space between elements */
    margin: 5px; /* Space outside the container */
    padding: 5px; /* Reduced padding for a more compact look */
    background-color: transparent; /* Light background for contrast */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    width: 50%; /* Set a fixed width */
    margin-left: 280px; /* Adjust if necessary for positioning */
    height: 40px; /* Set a specific height for the container */
    
}

/* Styles for the select dropdown */
#columnSelect {
    padding: 4px 6px; /* Reduced padding inside the dropdown */
    border: 1px solid #ccc; /* Border around the dropdown */
    border-radius: 5px; /* Rounded corners */
    font-size: 15px; /* Font size */
    margin-left: 0px; /* Space between dropdown and input */
    width: 210px; /* Adjusted width for better fit */
    height: 30px; /* Set a fixed height */
    
}

/* Styles for the search input */
#searchInput {
    padding: 6px; /* Reduced padding inside the input */
    border: 1px solid #ccc; /* Border around the input */
    border-radius: 5px; /* Rounded corners */
    font-size: 13px; /* Font size */
    width: 240px; /* Adjusted width for better fit */
    height: 30px; /* Set a fixed height */
    transition: border-color 0.3s; /* Smooth transition for focus effect */
}

/* Change border color on focus for both elements */
#columnSelect:focus,
#searchInput:focus {
    border-color: #007bff; /* Change border color on focus */
    outline: none; /* Remove default outline */
}


     /* .menu-button1 styles */
.menu-button1 {
    background-color: blue; /* Blue button color */
    color: white; /* Text color */
    padding: 10px 15px; /* Padding */
    text-decoration: none; /* Remove underline */
    border-radius: 5px; /* Rounded corners */
    font-size: 16px; /* Larger font size */
    transition: background-color 0.3s; /* Transition for hover effect */
    cursor: pointer; /* Pointer cursor */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    margin-left: 5px;
    margin-right: -15px;
}

/* Button hover effect */
.menu-button1:hover {
    background-color: darkblue; /* Darker shade on hover */
}

/* Show dropdown content when hovering over the button or the dropdown content */
.menu-button1:hover + .dropdown-content1,
.dropdown-content1:hover {
    display: block; /* Display the dropdown */
}

/* .dropdown-content1 styles */
.dropdown-content1 {
    display: none; /* Hidden by default */
    position: absolute; /* Positioning */
    background-color: white; /* White background for dropdown */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Box shadow */
    z-index: 1; /* Ensure it is above other content */
    min-width: 125px; /* Minimum width for dropdown */
    max-height: 200px; /* Maximum height for dropdown */
    overflow-y: auto; /* Enable scrollbar if needed */
    margin-left: 5px;
    margin-right: 10px;
}

/* .dropdown-content1 link styles */
.dropdown-content1 a {
    color: black; /* Text color */
    padding: 12px 16px; /* Padding for links */
    text-decoration: none; /* Remove underline */
    display: block; /* Make links block elements */
    transition: background-color 0.3s; /* Transition for hover effect */
}

/* Hover effect for dropdown links */
.dropdown-content1 a:hover {
    background-color: #f1f1f1; /* Light gray on hover */
}


/* .menu-button styles */
.menu-button {
    background-color: blue; /* Blue button color */
    color: white; /* Text color */
    padding: 10px 15px; /* Padding */
    text-decoration: none; /* Remove underline */
    border-radius: 5px; /* Rounded corners */
    font-size: 16px; /* Larger font size */
    transition: background-color 0.3s; /* Transition for hover effect */
    cursor: pointer; /* Pointer cursor */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    margin-left: -35px;
}

/* Button hover effect */
.menu-button:hover {
    background-color: darkblue; /* Darker shade on hover */
}

/* Show dropdown content when hovering over the button or the dropdown content */
.menu-button:hover + .dropdown-content,
.dropdown-content:hover {
    display: block; /* Display the dropdown */
}

/* .dropdown-content styles */
.dropdown-content {
    display: none; /* Hidden by default */
    position: absolute; /* Positioning */
    background-color: white; /* White background for dropdown */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Box shadow */
    z-index: 1; /* Ensure it is above other content */
    min-width: 100px; /* Minimum width for dropdown */
    max-height: 200px; /* Maximum height for dropdown */
    overflow-y: auto; /* Enable scrollbar if needed */
    margin-left: -32px;
}

/* .dropdown-content link styles */
.dropdown-content a {
    color: black; /* Text color */
    padding: 12px 16px; /* Padding for links */
    text-decoration: none; /* Remove underline */
    display: block; /* Make links block elements */
    transition: background-color 0.3s; /* Transition for hover effect */
}

/* Hover effect for dropdown links */
.dropdown-content a:hover {
    background-color: #f1f1f1; /* Light gray on hover */
}

      
    .no-records {
            text-align: center;
            margin-top: 20px;
            font-size: 18px; /* Larger font size for message */
            color: #dc3545; /* Red color for no records */
        }

        .btn1 {
            background-color: blue; /* Blue button for better visibility */
            color: white;
    border: none;
    padding: 10px 5px; /* Increased padding */
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s; /* Added transform for effect */
        }

.btn1:hover {
    background-color: green; /* Darker green on hover */
    transform: scale(1.05); /* Slightly enlarges button */
}

    </style>
</head>
<body>
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


<div class="resizer"></div>

<div class="container">
    <div class="main-content">
        <header>
            <h1>SCHOOL CLINIC RECORDS</h1>
        </header>
        
        <div id="searchContainer">
            <select id="columnSelect">
                <option value="">All Columns</option>
                <option value="1">ID Number</option>
                <option value="2">Rank</option>
                <option value="3">First Name</option>
                <option value="4">Last Name</option>
                <option value="5">Gmail Account</option>
                <option value="6">Department</option>
                <option value="7">Position</option>
                <option value="8">Complaints</option>
                <option value="9">Temp</option>
                <option value="10">BP</option>
                <option value="11">HR</option>
                <option value="12">RR</option>
                <option value="13">Height</option>
                <option value="14">Weight</option>
                <option value="15">Appointment Date</option>
            </select>
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for records..." />
        </div>
        
        <div class="menu">
    <div class="menu-button" onclick="toggleDropdown()">Select Year</div>
    <div class="dropdown-content" id="yearDropdown">
        <?php
        // Get selected year from the URL, or use null if not selected
        $selectedYear = isset($_GET['years']) ? (int)$_GET['years'] : null;

        // Loop through years
        for ($year = 2024; $year <= 2100; $year++): 
            // Maintain the selected month in the query string
            $queryParams = http_build_query([
                'years' => $year,
                'months' => isset($_GET['months']) ? $_GET['months'] : null
            ]);
        ?>
            <a href="?<?php echo $queryParams; ?>" class="<?php echo ($year == $selectedYear) ? 'active' : ''; ?>">
                <?php echo $year; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>

<?php
// Define months as strings
$months = [
    'January', 'February', 'March', 'April',
    'May', 'June', 'July', 'August',
    'September', 'October', 'November', 'December'
];

// Get selected month from the URL
$selectedMonth = isset($_GET['months']) ? $_GET['months'] : null;
?>

<div class="menu">
    <div class="menu-button1" onclick="toggleMonthDropdown()">Select Month</div>
    <div class="dropdown-content1" id="monthDropdown">
        <!-- Add an option for 'All Months' -->
        <a href="?<?php echo http_build_query(['years' => $selectedYear]); ?>" 
           class="<?php echo ($selectedMonth === null) ? 'active' : ''; ?>">All Months</a>

        <?php foreach ($months as $monthName): 
            // Maintain the selected year in the query string
            $queryParams = http_build_query([
                'years' => $selectedYear,
                'months' => $monthName
            ]);
        ?>
            <a href="?<?php echo $queryParams; ?>" class="<?php echo ($monthName == $selectedMonth) ? 'active' : ''; ?>">
                <?php echo $monthName; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>





        <form action="alert_faculty.php" method="post" style="margin-bottom: 5px;margin-top: -8px;">
        <a href="events_faculty.php" class="btn1"><i class="fas fa-calendar-plus"></i> Create Medical Event</a>

<button type="submit" class="alert-btn"><i class="fas fa-exclamation-circle"></i> View Health Status</button>



        </form>
        <button onclick="printTable()" class="print-button">Print Records</button> <!-- Print Button -->

        <h3>Personnel Records for the School Year <?php echo $selectedYear; ?> - <?php echo $selectedMonth ?: 'All Months'; ?></h3>

<!-- Faculty Table -->
<table id="recordsTable">
    <?php if (mysqli_num_rows($resultFaculty) > 0): ?>
        <thead>
            <tr>
                <th data-order="asc">#</th>
                <th data-order="asc">ID Number</th>
                <th data-order="asc">Rank</th>
                <th data-order="asc">First Name</th>
                <th data-order="asc">Last Name</th>
                <th data-order="asc">Gmail Account</th>
                <th data-order="asc">Department</th>
                <th data-order="asc">Position</th>
                <th data-order="asc">Complaints</th>
                <th data-order="asc">Temp</th>
                <th data-order="asc">BP</th>
                <th data-order="asc">HR</th>
                <th data-order="asc">RR</th>
                <th data-order="asc">Height</th>
                <th data-order="asc">Weight</th>
                <th data-order="asc">Appointment Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($resultFaculty)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Num']); ?></td>
                    <td><?php echo htmlspecialchars($row['IDNumber']); ?></td>
                    <td><?php echo htmlspecialchars($row['Rank']); ?></td>
                    <td><?php echo htmlspecialchars($row['FirstName']); ?></td>
                    <td><?php echo htmlspecialchars($row['LastName']); ?></td>
                    <td><?php echo htmlspecialchars($row['GmailAccount']); ?></td>
                    <td><?php echo htmlspecialchars($row['Department']); ?></td>
                    <td><?php echo htmlspecialchars($row['Position']); ?></td>
                    <td><?php echo htmlspecialchars($row['Complains']); ?></td>
                    <td><?php echo htmlspecialchars($row['Temperature']); ?></td>
                    <td><?php echo htmlspecialchars($row['BloodPressure']); ?></td>
                    <td><?php echo htmlspecialchars($row['HeartRate']); ?></td>
                    <td><?php echo htmlspecialchars($row['RespiratoryRate']); ?></td>
                    <td><?php echo htmlspecialchars($row['Height']); ?></td>
                    <td><?php echo htmlspecialchars($row['Weight']); ?></td>
                    <td><?= date("F j, Y, h:i A", strtotime($row['AppointmentDate'])) ?></td>
                    <td>
                        <div class="button-container">
                            <button class="btn1" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                            <button class="button" onclick="deleteRecord(<?php echo $row['Num']; ?>)">Delete</button>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <?php else: ?>
            <p class="no-records">No personnel records found for the selected year and month.</p>
        <?php endif; ?>
</table>
<script>
   function printTable() {
    // Get the content of the table to be printed
    var table = document.getElementById('recordsTable');
    
    // Remove the "Action" column (assuming it's the last column)
    var rows = table.rows;
    for (var i = 0; i < rows.length; i++) {
        rows[i].deleteCell(rows[i].cells.length - 1); // Remove the last cell (Action)
    }

    // Get the selected year from PHP
    var selectedYear = "<?php echo $selectedYear; ?>";

    // Build the report header dynamically
    var headerText = `<h1>Personnel Records</h1>`;
    headerText += `<h2>Year: ${selectedYear}</h2>`;

    // Get the table content without the "Action" column
    var printContent = table.outerHTML;

    // Create a new window for printing
    var printWindow = window.open('', '', 'width=1000,height=1000');

    // Add CSS styles for the printed report
    printWindow.document.write(`
        <html>
        <head>
            <title>Personnel Records</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    margin: 20px;
                }
                h1, h2 {
                    color: #333;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 1px;
                    text-align: center;
                    font-size: 16px;
                }
                th {
                    background-color: #f2f2f2;
                }
            </style>
        </head>
        <body>
            ${headerText} <!-- Insert the dynamic header text -->
            ${printContent} <!-- Insert the table content without the "Action" column -->
        </body>
        </html>
    `);

    // Close the document and initiate the printing
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
}
</script>
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Personnel Record</h2>
        <form id="editForm">
    <input type="hidden" id="editNum" name="Num">
    
    <label for="editIDNumber">ID Number:</label>
    <input type="text" id="editIDNumber" name="IDNumber" readonly>

    <label for="editFirstName">First Name:</label>
    <input type="text" id="editFirstName" name="FirstName">
    
    <label for="editLastName">Last Name:</label>
    <input type="text" id="editLastName" name="LastName">
    
    <label for="editGmailAccount">Gmail Account:</label>
    <input type="text" id="editGmailAccount" name="GmailAccount" readonly>
    
  <!-- CSS for modal  <label for="editRank">Academic Rank:</label>
    <select id="editRank" name="Rank" required onchange="handleRankAndDepartmentChange()">
        <option value="">Select Rank</option>
        <option value="NONE">NONE</option>
        <optgroup label="Instructor">
            <option value="Instructor I">Instructor I</option>
            <option value="Instructor II">Instructor II</option>
            <option value="Instructor III">Instructor III</option>
        </optgroup>
        <optgroup label="Assistant Professor">
            <option value="Assistant Professor I">Assistant Professor I</option>
            <option value="Assistant Professor II">Assistant Professor II</option>
            <option value="Assistant Professor III">Assistant Professor III</option>
            <option value="Assistant Professor IV">Assistant Professor IV</option>
        </optgroup>
        <optgroup label="Associate Professor">
            <option value="Associate Professor I">Associate Professor I</option>
            <option value="Associate Professor II">Associate Professor II</option>
            <option value="Associate Professor III">Associate Professor III</option>
            <option value="Associate Professor IV">Associate Professor IV</option>
            <option value="Associate Professor V">Associate Professor V</option>
        </optgroup>
        <optgroup label="Professor">
            <option value="Professor I">Professor I</option>
            <option value="Professor II">Professor II</option>
            <option value="Professor III">Professor III</option>
            <option value="Professor IV">Professor IV</option>
            <option value="Professor V">Professor V</option>
            <option value="Professor VI">Professor VI</option>
        </optgroup>
        <optgroup label="College/University Professor">
            <option value="College/University Professor">College/University Professor</option>
        </optgroup>
    </select>
    
   <!-- CSS for modal <label for="editDepartment">Department:</label>
    <select id="editDepartment" name="Department" required onchange="handleRankAndDepartmentChange()">
        <option value="">Select Department</option>
        <option value="NONE">NONE</option>
        <option value="CSS">CSS</option>
        <option value="ESO">ESO</option>
        <option value="NABA">NABA</option>
    </select>
    
   <!-- CSS for modal <label for="editPosition">Position:</label>
    <select id="editPosition" name="Position" required>
        <option value="">Select Position</option>
        <option value="Faculty">Faculty</option>
        <option value="Staff">Staff</option>
    </select>
    -->
   
    
    <button type="button1" onclick="submitEditForm()">Save Changes</button>
</form>

    </div>
</div>


<!-- CSS for modal -->
<style>

    /* Select field styling */
select {
    width: calc(100% - 20px); /* Full width minus padding */
    padding: 10px; /* Padding for select fields */
    border: 1px solid #ccc; /* Border for select fields */
    border-radius: 4px; /* Rounded corners for select fields */
    background-color: white; /* White background */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
    font-size: 14px; /* Font size for select options */
    color: #333; /* Text color */
    cursor: pointer; /* Pointer cursor on hover */
    transition: border-color 0.3s ease; /* Smooth transition for border color */
    margin-left: 10px;
}

/* Focus state for select field */
select:focus {
    border-color: #007bff; /* Blue border on focus */
    outline: none; /* Remove default focus outline */
}

/* Option styling for select dropdown */
select option {
    padding: 10px; /* Padding inside option elements */
    background-color: white; /* White background for options */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font for options */
}

/* Option hover effect */
select option:hover {
    background-color: #f2f2f2; /* Light gray background on hover */
}

.modal {
    position: fixed;
    z-index: 1000; /* Increased z-index for better stacking */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background-color: rgba(0, 0, 0, 0.7); /* Darker background for better focus */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
}

/* Modal content */
.modal-content {
    background-color: lightblue; /* Dark
    border-radius: 8px; /* Added border radius for rounded corners */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
    margin: 8% auto;
    padding: 20px;
    width: 90%; /* Wider modal for larger screens */
    max-width: 500px;
    transition: transform 0.3s ease; /* Smooth opening animation */
    transform: translateY(-30px); /* Start position for animation */
    border-radius: 10px;
    

}

/* Modal open animation */
.modal.show .modal-content {
    transform: translateY(0); /* End position for animation */
}

/* Close button styling */
.close {
    color: #888; /* Slightly darker for better visibility */
    float: right;
    font-size: 28px;
    font-weight: bold;
    transition: color 0.2s; /* Smooth transition on hover */
}

.close:hover,
.close:focus {
    color: #ff0000; /* Change to red on hover for better visibility */
    text-decoration: none;
    cursor: pointer;
}

/* Form group styling */
.form-group {
    margin-bottom: 3px; /* Space between form fields */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
}

label {
    display: block; /* Labels on their own line */
    margin-bottom: 5px; /* Space below labels */
    font-size: 14.5px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
    font-weight: 600; 
}

/* Input field styling */
input[type="text"], input[type="datetime-local"] {
    width: calc(100% - 20px); /* Full width minus padding */
    padding: 10px; /* Padding for input fields */
    border: 1px solid #ccc; /* Border for inputs */
    border-radius: 4px; /* Rounded corners for inputs */
     font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
     margin-bottom: 15px;
}

/* Button styling */
button {
    background-color: #28a745; /* Green background for the button */
    color: white; /* White text */
    padding: 10px 15px; /* Padding around text */
    border: none; /* Remove border */
    border-radius: 4px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
}

button:hover {
    background-color: blue; /* Darker green on hover */
}

/* Modal Header Styling */
.modal-content h2 {
    width: 50%;
    margin: 0 auto;
    padding: 5px;
    text-align: center;
    background-color: #f2f2f2;
    color: #333;
    border-radius: 5px;
    font-size: 1.4em;
    font-weight: bold;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    margin-top: -5px;
    margin-bottom: 10px;
    
}

/* Responsive design adjustments */
@media (max-width: 600px) {
    .modal-content {
        width: 95%; /* Adjust width for smaller screens */
    }

    .close {
        font-size: 24px; /* Slightly smaller close button on small screens */
    }
}


</style>

<!-- JavaScript for modal -->
<script>
function openEditModal(rowData) {
    document.getElementById("editNum").value = rowData.Num;
    document.getElementById("editIDNumber").value = rowData.IDNumber;  // Added for ID Number
   // Added for Rank document.getElementById("editRank").value = rowData.Rank;  // Added for Rank
    document.getElementById("editFirstName").value = rowData.FirstName;  // Added for First Name
    document.getElementById("editLastName").value = rowData.LastName;  // Added for Last Name
    document.getElementById("editGmailAccount").value = rowData.GmailAccount;  // Added for Gmail Account
   // Added for Rank document.getElementById("editDepartment").value = rowData.Department;  // Added for Department
  // Added for Rank  document.getElementById("editPosition").value = rowData.Position;  // Added for Position

    document.getElementById("editModal").style.display = "block";
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

// AJAX function to submit the edited data
function submitEditForm() {
    const formData = new FormData(document.getElementById('editForm'));

    fetch('update_faculty.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Record updated successfully!');
            location.reload();
        } else {
            alert('Failed to update record: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>


<script>

function handleRankAndDepartmentChange() {
        const rankSelect = document.getElementById("Rank");
        const departmentSelect = document.getElementById("Department");
        const positionSelect = document.getElementById("Position");
        const restrictedPositions = ["Faculty", "Dean", "Program Chair"];

        // Handle Rank change - set Department to NONE if Rank is NONE
        if (rankSelect.value === "NONE") {
            departmentSelect.value = "NONE";
            departmentSelect.disabled = true;
        } else {
            departmentSelect.disabled = false;
        }

        // Handle Department change - disable certain positions if Department is NONE
        if (departmentSelect.value === "NONE") {
            restrictedPositions.forEach(positionValue => {
                const option = [...positionSelect.options].find(opt => opt.value === positionValue);
                if (option) option.disabled = true;
            });
        } else {
            restrictedPositions.forEach(positionValue => {
                const option = [...positionSelect.options].find(opt => opt.value === positionValue);
                if (option) option.disabled = false;
            });
        }
    }
    function toggleDropdown() {
    document.getElementById('yearDropdown').classList.toggle('show');
}

function toggleMonthDropdown() {
    document.getElementById('monthDropdown').classList.toggle('show');
}

// Close the dropdown if clicked outside
window.onclick = function(event) {
    if (!event.target.matches('.menu-button')) {
        var dropdowns = document.getElementsByClassName('dropdown-content');
        for (let i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].classList.contains('show')) {
                dropdowns[i].classList.remove('show');
            }
        }
    }

    if (!event.target.matches('.menu-button1')) {
        var dropdowns = document.getElementsByClassName('dropdown-content1');
        for (let i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].classList.contains('show')) {
                dropdowns[i].classList.remove('show');
            }
        }
    }
};



function deleteRecord(id) {
            if (confirm("Are you sure you want to delete this record?")) {
                window.location.href = 'delete_faculty.php?id=' + id; // Redirect to delete script
            }
        }

        function searchTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('recordsTable');
        const tr = table.getElementsByTagName('tr');
        const columnSelect = document.getElementById('columnSelect');
        const selectedColumn = columnSelect.value; // Get the selected column index

        for (let i = 1; i < tr.length; i++) { // Skip the header row
            let rowVisible = false;
            const td = tr[i].getElementsByTagName('td');
            
            // If a specific column is selected
            if (selectedColumn) {
                const tdIndex = parseInt(selectedColumn); // Convert selected column to number
                const textValue = td[tdIndex] ? (td[tdIndex].textContent || td[tdIndex].innerText) : '';
                if (textValue.toLowerCase().indexOf(filter) > -1) {
                    rowVisible = true;
                }
            } else {
                // Search across all columns if no specific column is selected
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const textValue = td[j].textContent || td[j].innerText;
                        if (textValue.toLowerCase().indexOf(filter) > -1) {
                            rowVisible = true;
                            break; // Stop further column checks if one column has a match
                        }
                    }
                }
            }
            
            tr[i].style.display = rowVisible ? '' : 'none'; // Show or hide row
        }
    }

            function sortTable(columnIndex) {
    const table = document.getElementById("recordsTable");
    const rows = Array.from(table.rows).slice(1); // Get all rows except the header
    const headerCell = table.getElementsByTagName("th")[columnIndex];
    const isAscending = headerCell.getAttribute("data-order") === "asc";

    // Sort rows based on the column index
    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].innerText.trim();
        const bText = b.cells[columnIndex].innerText.trim();
        
        // Determine data type and compare accordingly
        let comparison = 0;
        if (!isNaN(aText) && !isNaN(bText)) {
            // If the text is numeric, compare as numbers
            comparison = parseFloat(aText) - parseFloat(bText);
        } else if (Date.parse(aText) && Date.parse(bText)) {
            // If the text is a date, compare as dates
            comparison = new Date(aText) - new Date(bText);
        } else {
            // Otherwise, compare as strings
            comparison = aText.localeCompare(bText);
        }

        return isAscending ? comparison : -comparison; // Toggle for sort order
    });

    // Clear existing rows and re-append sorted rows
    while (table.rows.length > 1) {
        table.deleteRow(1);
    }
    rows.forEach(row => table.appendChild(row));

    // Toggle sort order for next click
    const newOrder = isAscending ? "desc" : "asc";
    headerCell.setAttribute("data-order", newOrder);

    // Remove sort indicators from all headers
    Array.from(table.getElementsByTagName("th")).forEach(th => {
        th.classList.remove("sort-asc", "sort-desc");
    });

    // Add the appropriate class to the header for visual indication
    headerCell.classList.add(isAscending ? "sort-desc" : "sort-asc");
}

// Set up event listeners for the header cells
document.querySelectorAll("#recordsTable th").forEach((header, index) => {
    header.setAttribute("data-order", "asc"); // Set initial sort order
    header.addEventListener("click", () => sortTable(index));
});


        </script>

<script>
    const resizer = document.querySelector('.resizer');
    const sidebar = document.querySelector('.sidebar');
    const container = document.querySelector('.container');

    let isResizing = false;

    resizer.addEventListener('mousedown', (event) => {
        isResizing = true;
    });

    window.addEventListener('mousemove', (event) => {
        if (!isResizing) return;
        const newWidth = event.clientX;
        sidebar.style.width = `${newWidth}px`;
        container.style.marginLeft = `${newWidth}px`;
        resizer.style.left = `${newWidth}px`; // Adjust the position of the resizer
    });

    window.addEventListener('mouseup', () => {
        isResizing = false;
    });
</script>
    </div>
</div>

</body>
</html>
