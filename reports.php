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

function renumberTable($table, $year, $month = null) {
    global $conn;

    // Step 1: Set the variable to 0
    $setVariableQuery = "SET @new_num = 0;";
    if ($conn->query($setVariableQuery) === false) {
        die("Error setting variable: " . $conn->error);
    }

    // Step 2: Update the records with the new number
    $renumberQuery = "
        UPDATE $table
        SET Num = (@new_num := @new_num + 1)
        WHERE years = ?";
    
    $params = array($year);
    $types = "s";

    if ($month !== null) {
        $renumberQuery .= " AND months = ?";
        $params[] = $month;
        $types .= "s";
    }

    $renumberQuery .= " ORDER BY Num ASC";

    $stmt = $conn->prepare($renumberQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();
}

// Update how tables are renumbered
$tables = ['personal_info', 'illmed', 'intv', 'faculty', 'inventory'];
foreach ($tables as $table) {
    renumberTable($table, $selectedYear, $selectedMonth);
}

// Modify the getReportData function to handle "All Months" case
function getReportData($reportType, $year, $month = null) {
    global $conn;

    $baseQuery = "";
    $filterConditions = "WHERE years = ?";
    $params = array($year);
    $types = "s";

    // Only add month condition if a specific month is selected
    if ($month !== null) {
        $filterConditions .= " AND months = ?";
        $params[] = $month;
        $types .= "s";
    }

    $fields = "";
    $customLabels = [];

    switch ($reportType) {
        case 'students_report':
            $fields = "Num, FirstName, LastName, IDNumber, GmailAccount, 
                       DATE_FORMAT(BirthDate, '%M %d, %Y') AS BirthDate, Age, Gender, Course, Yr, Section";
            $baseQuery = "SELECT $fields FROM personal_info";
            $customLabels = [
                'Num' => '#',
                'FirstName' => 'First Name',
                'LastName' => 'Last Name',
                'IDNumber' => 'ID Number',
                'GmailAccount' => 'Gmail Account',
                'BirthDate' => 'Birth Date',
                'Age' => 'Age',
                'Gender' => 'Gender',
                'Course' => 'Course',
                'Yr' => 'Year',
                'Section' => 'Section'
            ];
            break;

        case 'treatments_report':
            $fields = "Num, IDNumber, IllName, MedName, Temperature, BloodPressure, Status, 
                       DATE_FORMAT(Appointment_Date, '%M %d, %Y %h:%i %p') AS Appointment_Date, alert_sent";
            $baseQuery = "SELECT $fields FROM illmed";
            $customLabels = [
                'Num' => '#',
                'IDNumber' => 'ID Number',
                'IllName' => 'Illness Name',
                'MedName' => 'Medication Name',
                'Temperature' => 'Temperature',
                'BloodPressure' => 'Blood Pressure',
                'Status' => 'Status',
                'Appointment_Date' => 'Appointment Date',
                'alert_sent' => 'Alert Sent'
            ];
            break;

        case 'consultations_report':
            $fields = "Num, IDNumber, what_you_do, what_is_your_existing_desease, have_you_a_family_history_desease, have_you_a_allergy";
            $baseQuery = "SELECT $fields FROM intv";
            $customLabels = [
                'Num' => '#',
                'IDNumber' => 'ID Number',
                'what_you_do' => 'Activity',
                'what_is_your_existing_desease' => 'Existing Disease',
                'have_you_a_family_history_desease' => 'Family History of Disease',
                'have_you_a_allergy' => 'Allergies'
            ];
            break;

        case 'personnels_report':
            $fields = "Num, IDNumber, Rank, FirstName, LastName, GmailAccount, 
                       IF(Department IS NULL OR Department = '', 'NONE', Department) AS Department, 
                       Position,  
                       IF(Complains IS NULL OR Complains = '', 'No complaints', Complains) AS Complains, 
                       IF(Temperature = 0, '', Temperature) AS Temperature, 
                       IF(BloodPressure = 0, '', BloodPressure) AS BloodPressure, 
                       IF(HeartRate = 0, '', HeartRate) AS HeartRate, 
                       IF(RespiratoryRate = 0, '', RespiratoryRate) AS RespiratoryRate,
                       IF(Height = 0, '', Height) AS Height,
                       IF(Weight = 0, '', Weight) AS Weight,
                       IF(AppointmentDate = '0000-00-00 00:00:00' OR AppointmentDate IS NULL, '', DATE_FORMAT(AppointmentDate, '%M %d, %Y %h:%i %p')) AS AppointmentDate
";
            $baseQuery = "SELECT $fields FROM faculty";
            $customLabels = [
                'Num' => '#',
                'IDNumber' => 'ID Number',
                'Rank' => 'Rank',
                'FirstName' => 'First Name',
                'LastName' => 'Last Name',
                'GmailAccount' => 'Gmail Account',
                'Department' => 'Department',
                'Position' => 'Position',
                'Complains' => 'Complaints',
                'Temperature' => 'Temperature',
                'BloodPressure' => 'BP',
                'HeartRate' => 'HR',
                'RespiratoryRate' => 'RR',
                'Height' => 'Height',
                'Weight' => 'Weight',
                'AppointmentDate' => 'Appointment Date'
            ];
            break;

        case 'medicine_inventory_report':
            $fields = "Num, MedName, SupplierName, StockQuantity";
            $baseQuery = "SELECT $fields FROM inventory";
            $customLabels = [
                'Num' => '#',
                'MedName' => 'Medicine Name',
                'SupplierName' => 'Supplier Name',
                'StockQuantity' => 'Stock Quantity'
            ];
            break;

        default:
            return [];
    }

    $sql = "$baseQuery $filterConditions ORDER BY Num ASC";
    $stmt = $conn->prepare($sql);

    // Dynamic parameter binding based on number of parameters
    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return ['data' => $data, 'labels' => $customLabels];
}

// Update how the report data is fetched
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'students_report';
$selectedYear = isset($_GET['years']) ? intval($_GET['years']) : $currentYear;
$selectedMonth = isset($_GET['months']) && !empty($_GET['months']) ? $_GET['months'] : null;

// Get the report data with modified parameters
$reportData = getReportData($reportType, $selectedYear, $selectedMonth);

// Check if report data is valid
if (isset($reportData['data']) && !empty($reportData['data'])) {
    // Use $reportData['data'] and $reportData['labels'] for rendering
} else {
    // Handle case when there is no data (e.g., show a message)
    echo "";
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>EHR Reports</title>
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

.main-content {
    background-color: rgba(173, 216, 230, 0.50);
    border-radius: 8px;
    padding: 15px; /* Reduced padding */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 11.6px; /* Reduced font size */
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
        .main-content {
    background-color: rgba(173, 216, 230, 0.50); /* Light blue background with 50% transparency */
    border-radius: 8px;
    padding: 0px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 11.6px;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    text-align: center;
    height: auto; /* Set a fixed height */
   
}
        h1 {
            color: white;
            font-size: 40px;
        }
        h3 {
            margin-top: -30px;
    margin-bottom: 20px;
    color: green; /* Keeping the green theme */
    font-size: 24px; /* Larger font for headings */
    border-bottom: 2px solid #4CAF50; /* Underline for emphasis */
    padding-bottom: 10px; /* Spacing below the heading */
}
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th, td {
    padding: 8px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    border: 0.1px solid #ddd;
    
}
        th {
            background-color: green;
            color: white;
        }
        tr:hover {
            background-color: lightgreen;
        }
        .error-message {
            color: red;
            margin-top: 15px;
            font-weight: bold;
        }

        .form-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
    align-items: center; /* Center align items */
}

label {
    font-weight: bold;
    color: #555;
}

.input-select {
    width: 25%; /* Width can be adjusted */
    padding: 10px;
    text-align: center;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
    transition: border-color 0.3s;
}

.input-select:focus {
    border-color: #007bff;
}

.btn {
    padding: 10px 15px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn:hover {
    background-color: #0056b3;
}

.form-group select {
    cursor: pointer;
}
/* Button Styling */
.btn1 {
    position: fixed; /* Fixes the position relative to the viewport */
    position: sticky;
        top: 0; /* Adjust to stick at the top */
        z-index: 10; /* Ensure it stays above the table */
        margin-bottom: -10px; /* Space between button and table */
        margin-top: -10px;
        margin-right: 1000px;
    padding: 10px 21px; /* Adds some padding for the button */
    font-size: 14px; /* Font size of the text inside the button */
    background-color: #4CAF50; /* Green background color */
    color: white; /* White text color */
    border: none; /* Removes the default border */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Changes the cursor to a pointer on hover */
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Adds a shadow for a floating effect */
}

.btn1:hover {
    background-color: #45a049; /* Darker green when hovered */
}
.menu-container {
        display: flex;
        justify-content: space-between; /* Adjust spacing between menus */
        align-items: center;
        gap: 10px; /* Optional: Adds spacing between the dropdowns */
    }

    .menu {
            position: relative;
            display: inline-block;
            text-align: center;
          margin-left: 10px;
          margin-right: -15px;
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
.toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #333;
            color: #fff;
            border: none;
            padding: 15px;
            cursor: pointer;
            font-size: 20px;
            z-index: 1000;
        }


        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100px;
            }
            .container {
                margin-left: 120px;
            }
            .sidebar a {
                padding: 10px 15px;
            }
            .container {
                padding: 15px;
            }

            h1 {
                font-size: 1.5rem;
            }

            .btn {
                font-size: 0.9rem;
            }
        }
        
    </style>
</head>
<body>
<button class="toggle-btn" id="toggleBtn"><i class="fas fa-bars"></i></button>

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
<div class="container">
    <div class="main-content">
        <header>
            <h1>REPORTS MODULE</h1>
        </header>

       <!-- Report Selection Form -->
<form method="GET" action="reports.php" class="form-group">
    <label for="report_type">Select Report Type:</label>
    <select id="report_type" name="report_type" class="input-select">
        <option value="students_report" <?= ($reportType == 'students_report') ? 'selected' : '' ?>>Students Report</option>
        <option value="personnels_report" <?= ($reportType == 'personnels_report') ? 'selected' : '' ?>>Personnels Report</option>
        <option value="consultations_report" <?= ($reportType == 'consultations_report') ? 'selected' : '' ?>>Consultations Report</option>
        <option value="treatments_report" <?= ($reportType == 'treatments_report') ? 'selected' : '' ?>>Treatments Report</option>
        <option value="medicine_inventory_report" <?= ($reportType == 'medicine_inventory_report') ? 'selected' : '' ?>>Medicines Inventory Report</option>
    </select>
    <button type="submit" class="btn">Generate Report</button>

    <!-- Year Selection -->
    <div class="menu-container">
        <div class="menu">
            <div class="menu-button" onclick="toggleDropdown()">Select Year</div>
            <div class="dropdown-content" id="yearDropdown">
                <?php for ($year = 2024; $year <= 2100; $year++): ?>
                    <a href="?years=<?php echo $year; ?>&months=<?php echo urlencode($selectedMonth); ?>&report_type=<?php echo urlencode($reportType); ?>" class="<?php echo ($year == $selectedYear) ? 'active' : ''; ?>">
                        <?php echo $year; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Month Selection -->
        <?php
        // Define months as strings (varchar)
        $months = [
            'January', 'February', 'March', 'April',
            'May', 'June', 'July', 'August',
            'September', 'October', 'November', 'December'
        ];

        // Get the selected month from the URL, or use null if not selected
        $selectedMonth = isset($_GET['months']) ? $_GET['months'] : null;
        ?>
        <div class="menu">
            <div class="menu-button1" onclick="toggleMonthDropdown()">Select Month</div>
            <div class="dropdown-content1" id="monthDropdown">
                <!-- Add an option for 'All Months' -->
                <a href="?years=<?php echo urlencode($selectedYear); ?>&report_type=<?php echo urlencode($reportType); ?>" class="<?php echo ($selectedMonth === null) ? 'active' : ''; ?>">All Months</a>
                
                <?php foreach ($months as $monthName): ?>
                    <a href="?months=<?php echo urlencode($monthName); ?>&years=<?php echo urlencode($selectedYear); ?>&report_type=<?php echo urlencode($reportType); ?>" class="<?php echo ($monthName == $selectedMonth) ? 'active' : ''; ?>">
                        <?php echo $monthName; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</form>


        <!-- Print Button -->
        <button onclick="printReport()" class="btn1">Print Report</button>

<h3>School Clinic Reports for the School Year <?php echo htmlspecialchars($selectedYear); ?> - <?php echo htmlspecialchars($selectedMonth ?: 'All Months'); ?></h3>
<div class="table-container">
    <table id="recordsTable">
        <thead>
            <tr>
                <?php if (!empty($reportData['data'])) { // Check if there is data
                    // Generate table headers from the custom labels
                    foreach ($reportData['labels'] as $label) {
                        echo "<th>" . htmlspecialchars($label) . "</th>";
                    }
                } ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reportData['data'])) { // Check if there's data to display
                foreach ($reportData['data'] as $row) {
                    echo "<tr>";
                    foreach ($row as $cell) {
                        echo "<td>" . htmlspecialchars($cell) . "</td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='100%'>No data available for the selected filters.</td></tr>";
            } ?>
        </tbody>
    </table>
</div>

<script>
    // Function to print the report
    function printReport() {
        // Get the content of the table to be printed
        var reportContent = document.getElementById('recordsTable').outerHTML;

        // Get the selected year, month, and report type from PHP variables
        var selectedYear = "<?php echo $selectedYear; ?>";
        var selectedMonth = "<?php echo $selectedMonth; ?>";
        var reportType = "<?php echo $reportType; ?>";

        // Define a dynamic report label based on the selected report type
        var reportLabel = getReportLabel(reportType);

        // Build the report header based on the selected filters
        var headerText = buildReportHeader(reportLabel, selectedYear, selectedMonth);

        // Create a new window for printing
        var printWindow = window.open('', '_blank', 'width=2500,height=1000');
        printWindow.document.write('<html><head><title>Print Report</title>');

        // Add CSS styles for the printed report
        printWindow.document.write(`
            <style>
                body {
                     font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
                    margin: 20px;
                   
                }
                h1, h2, h4 {
                    color: #333;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 3px;
                    text-align: center;
                    font-size: 14px;
                }
                th {
                    background-color: #f2f2f2;
                }
            </style>
        `);

        // Write the content of the report
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h1>REPORT MODULE</h1>');
        printWindow.document.write(headerText); // Dynamic report header
        printWindow.document.write(reportContent); // Table content
        printWindow.document.write('</body></html>');

        // Close the document and initiate the printing
        printWindow.document.close();
        printWindow.print();
        printWindow.close();
    }

    // Function to get the report label based on the report type
    function getReportLabel(reportType) {
        switch (reportType) {
            case "students_report":
                return "Students Report";
            case "treatments_report":
                return "Treatments Report";
            case "consultations_report":
                return "Consultations Report";
            case "personnels_report":
                return "Personnel Records Report";
            case "medicine_inventory_report":
                return "Medicines Inventory Report";
            case "medical_history_report":
                return "Medical History Report";
            case "accounts_report":
                return "Accounts Report";
            default:
                return "Report";
        }
    }

    // Function to build the report header with year and month information
    function buildReportHeader(reportLabel, selectedYear, selectedMonth) {
        var headerText = `<h2>${reportLabel}</h2>`; // Report name
        headerText += `<h4>For the School Year ${selectedYear}</h4>`;
        headerText += selectedMonth
            ? `<h4>For the Month of ${selectedMonth}</h4>`
            : `<h4>For All Months</h4>`;
        return headerText;
    }
</script>


<script>
    // Toggle the year dropdown visibility
    function toggleDropdown() {
        var dropdown = document.getElementById("yearDropdown");
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    }

    // Toggle the month dropdown visibility
    function toggleMonthDropdown() {
        var dropdown = document.getElementById("monthDropdown");
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    }
    
   
</script>

<!-- JavaScript for Print Function -->


<?php
// Close the database connection
mysqli_close($conn);
?>

<script>
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

