<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $idNumber = mysqli_real_escape_string($conn, $_POST['IDNumber']);
    $height = mysqli_real_escape_string($conn, $_POST['Height']);
    $weight = mysqli_real_escape_string($conn, $_POST['Weight']);
    $heartRate = mysqli_real_escape_string($conn, $_POST['HeartRate']);
    $bloodPressure = mysqli_real_escape_string($conn, $_POST['BloodPressure']);
    $temperature = mysqli_real_escape_string($conn, $_POST['Temperature']);
    $year = date("Y");

    // Insert form data into the medical_history table
    $sql = "INSERT INTO medical_history (IDNumber, height, weight, heartrate, bloodpressure, temperature, years) 
            VALUES ('$idNumber', '$height', '$weight', '$heartRate', '$bloodPressure', '$temperature', '$year')";

    if (mysqli_query($conn, $sql)) {
        // Save the submitted data to session
        $_SESSION['medical_data'] = [
            'IDNumber' => $idNumber,
            'Height' => $height,
            'Weight' => $weight,
            'HeartRate' => $heartRate,
            'BloodPressure' => $bloodPressure,
            'Temperature' => $temperature,
            'Years' => $year,
        ];

        // Redirect to the display page after a successful insert
        header("Location: display_medical_information.php");
        exit();
    } else {
        // Error handling
        echo "Error inserting record: " . mysqli_error($conn);
    }
}

// Disable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0;");

// Get the selected year, defaulting to the current year
$currentYear = date("Y");
$selectedYear = isset($_GET['years']) ? intval($_GET['years']) : $currentYear;

// Fetch records for renumbering only for the selected year
$selectQuery = "SELECT Num FROM medical_history WHERE years = ? ORDER BY Num ASC";
$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$result = $stmt->get_result();

$new_num = 1; // Start renumbering from 1
while ($row = $result->fetch_assoc()) {
    $old_num = $row['Num'];

    // Update Num in medical_history for the selected year
    $updateMedicalHistory = "UPDATE medical_history SET Num = $new_num WHERE Num = $old_num AND years = $selectedYear";
    mysqli_query($conn, $updateMedicalHistory);

    $new_num++; // Increment for the next row
}

// Close the prepared statement
$stmt->close();

// Re-enable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1;");
$sql = "SELECT mh.Num, mh.height, mh.weight, mh.heartrate, mh.bloodpressure, mh.temperature, mh.years, 
               pi.FirstName, pi.LastName, pi.IDNumber, pi.Age, pi.Course, pi.Yr, pi.Section
        FROM medical_history mh
        INNER JOIN personal_info pi ON mh.Num = pi.Num
        WHERE mh.years = ?
        ORDER BY mh.Num ASC";



$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$result = $stmt->get_result();

// Close the statement and connection
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical History Records</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <style>

.no-records {
            text-align: center;
            margin-top: 20px;
            font-size: 18px; /* Larger font size for message */
            color: #dc3545; /* Red color for no records */
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
    font-size: 14px; /* Reduced font size */
    text-align: center;
}
        .resizer {
            width: 10px; /* Width of the resizer */
            cursor: ew-resize; /* Cursor style */
            position: fixed; /* Fixed position */
            top: 0;
            left: 200px; /* Initially set to match the sidebar width */
            height: 100vh; /* Full height */
            background-color: transparent; /* Invisible, can change for visibility */
            z-index: 1100; /* Above sidebar */
        }


.main-content {
    background-color: rgba(173, 216, 230, 0.50); /* Light blue background with 50% transparency */
    border-radius: 8px;
    padding: 0px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 12px;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    text-align: center;
    height: auto; /* Set a fixed height */
   
}



h3 {
    margin-top: -20px;
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
    padding: 10px;
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
    padding: 6px; /* Reduced padding inside the dropdown */
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
            margin-bottom: 5px; /* Space between menu and other content */
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
        .button-container {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 15px;
            margin-left: -35px;
        }

        .button {
            background-color: green; /* Green button color */
            color: white; /* Text color */
            padding: 12px 20px; /* Padding */
            text-decoration: none; /* Remove underline */
            border-radius: 5px; /* Rounded corners */
            margin: 0 5px; /* Margin between buttons */
            font-size: 16px; /* Larger font size */
            transition: background-color 0.3s, transform 0.3s; /* Transition for hover effect */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Box shadow */
        }

        .button:hover {
            background-color: blue; /* Darker shade on hover */
            transform: translateY(-2px); /* Slight lift on hover */
        }
        .btn1 {
            background-color: blue; /* Blue button for better visibility */
    color: white;
    border: none;
    padding: 10px 8px; /* Increased padding */
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s; /* Added transform for effect */
    margin-top: 1px;
        }

.btn1:hover {
    background-color: green; /* Darker green on hover */
    transform: scale(1.05); /* Slightly enlarges button */
}
/* Style for the Print button */
.btn {
   
   position: sticky;
       top: 0; /* Adjust to stick at the top */
       z-index: 10; /* Ensure it stays above the table */
       margin-bottom: -10px; /* Space between button and table */
       margin-top: -10px;
       margin-left: -1000px;
   padding: 10px 21px; /* Adds some padding for the button */
   font-size: 14px; /* Font size of the text inside the button */
   background-color: #4CAF50; /* Green background color */
   color: white; /* White text color */
   border: none; /* Removes the default border */
   border-radius: 5px; /* Rounded corners */
   cursor: pointer; /* Changes the cursor to a pointer on hover */
   box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Adds a shadow for a floating effect */
}

.btn:hover {
   background-color: #45a049; /* Darker green when hovered */
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
                <option value="2">Name</option>
                <option value="3">Age</option>
                <option value="4">Course</option>
                <option value="5">Year</option>
                <option value="6">Section</option>
                <option value="7">Height</option>
                <option value="8">Weight</option>
                <option value="9">Heart Rate </option>
                <option value="10">Blood Pressure </option>
                <option value="11">Temperature </option>
            </select>
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for records..." />
        </div>
         
    
    <div class="menu">
        <div class="menu-button" onclick="toggleDropdown()">Select Year</div>
        <div class="dropdown-content" id="yearDropdown">
            <?php for ($year = 2024; $year <= 2100; $year++): ?>
                <a href="?years=<?php echo $year; ?>" class="<?php echo ($year == $selectedYear) ? 'active' : ''; ?>">
                    <?php echo $year; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>

    <div class="button-container">
        <a href="medical.php" class="button">Add New Record</a>
    </div>
    <button onclick="printTable()" class="btn">Print Report</button>
    <h3>Student Medical History Records for Year <?php echo $selectedYear;?></h3>

<!-- Add Print Button -->


<?php if ($result->num_rows > 0): ?>
    <table id="recordsTable">
        <thead>
            <tr>
                <th data-order="asc">#</th>
                <th data-order="asc">ID Number</th>
                <th data-order="asc">Name</th>
                <th data-order="asc">Age</th>
                <th data-order="asc">Course</th>
                <th data-order="asc">Year</th>
                <th data-order="asc">Section</th>
                <th data-order="asc">Height (cm)</th>
                <th data-order="asc">Weight (kg)</th>
                <th data-order="asc">Heart Rate (bpm)</th>
                <th data-order="asc">Blood Pressure (mmHg)</th>
                <th data-order="asc">Temperature (°C)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['Num']; ?></td>
                    <td><?php echo $row['IDNumber']; ?></td> <!-- Use 'IDNumber' here -->
                    <td><?php echo $row['FirstName'] . ' ' . $row['LastName']; ?></td>
                    <td><?php echo $row['Age']; ?></td>
                    <td><?php echo $row['Course']; ?></td>
                    <td><?php echo $row['Yr']; ?></td>
                    <td><?php echo $row['Section']; ?></td>
                    <td><?php echo $row['height']; ?></td>
                    <td><?php echo $row['weight']; ?></td>
                    <td><?php echo $row['heartrate']; ?></td>
                    <td><?php echo $row['bloodpressure']; ?></td>
                    <td><?php echo $row['temperature']; ?></td>
                    <td>
                        <button class="btn1" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-records">No records found for the selected year.</p>
<?php endif; ?>


<!-- JavaScript to handle printing -->
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
    var headerText = `<h1>Students Medical History Records</h1>`;
    headerText += `<h2>Year: ${selectedYear}</h2>`;

    // Get the table content without the "Action" column
    var printContent = table.outerHTML;

    // Create a new window for printing
    var printWindow = window.open('', '', 'width=1000,height=1000');

    // Add CSS styles for the printed report
    printWindow.document.write(`
        <html>
        <head>
            <title>Medical History Records</title>
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
                    padding: 8px;
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


<!-- Edit Modal for Medical Record -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>EDIT MEDICAL</h2>
        <form id="editForm">
            <input type="hidden" name="Num" id="editNum">

            <div class="form-group">
                <label for="Height">Height (cm):</label>
                <input type="text" name="height" id="editHeight" required>
            </div>
            <div class="form-group">
                <label for="Weight">Weight (kg):</label>
                <input type="text" name="weight" id="editWeight" required>
            </div>
            <div class="form-group">
                <label for="HeartRate">Heart Rate (bpm):</label>
                <input type="text" name="heartrate" id="editHeartRate" required>
            </div>
            <div class="form-group">
                <label for="BloodPressure">Blood Pressure (mmHg):</label>
                <input type="text" name="bloodpressure" id="editBloodPressure" required>
            </div>
            <div class="form-group">
                <label for="Temperature">Temperature (°C):</label>
                <input type="text" name="temperature" id="editTemperature" required>
            </div>
            
            <button type="button" onclick="submitEditForm()">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openEditModal(rowData) {
    document.getElementById('editNum').value = rowData.Num;
    document.getElementById('editHeight').value = rowData.height;
    document.getElementById('editWeight').value = rowData.weight;
    document.getElementById('editHeartRate').value = rowData.heartrate;
    document.getElementById('editBloodPressure').value = rowData.bloodpressure;
    document.getElementById('editTemperature').value = rowData.temperature;

    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function submitEditForm() {
    const formData = new FormData(document.getElementById('editForm'));
    fetch('update_medical.php', {
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

<style>
/* Modal backdrop */


.btn1 {
    background-color: blue; /* Blue button for better visibility */
    color: white;
    border: none;
    padding: 8px 12px; /* Increased padding */
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s; /* Added transform for effect */
    margin: 0.1px 0;
}

.btn1:hover {
    background-color: green; /* Darker green on hover */
    transform: scale(1.05); /* Slightly enlarges button */
}

.modal-content h2 {
    width: 50%; /* Consistent width */
    margin: 0 auto;
    padding: 5px; /* Adjusted padding */
    text-align: center;
    background-color: #f2f2f2;
    color: #333;
    border-radius: 5px;
    font-size: 1.4em; /* Increased font size */
    font-weight: bold;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    margin-top: -5px;
    margin-bottom: 10px; /* Increased bottom margin */
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
}

/* Modal content */
.modal-content {
    background-color: lightblue; /* Keeping the light blue background */
    border-radius: 10px; /* Adjusted border radius */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
    margin: 7% auto; /* Increased margin for positioning */
    padding: 20px; /* Increased padding for comfort */
    width: 90%; /* Wider modal for larger screens */
    max-width: 500px; /* Increased max width */
    transition: transform 0.3s ease; /* Smooth opening animation */
    transform: translateY(-30px); /* Start position for animation */
}

/* Modal open animation */
.modal.show .modal-content {
    transform: translateY(0); /* End position for animation */
}

/* Close button styling */
.close {
    color: #888; /* Slightly darker for better visibility */
    float: right;
    font-size: 28px; /* Larger close button font size */
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
    margin-bottom: 15px; /* Space between form fields */
}

label {
    display: block; /* Labels on their own line */
    margin-top: 15px; /* Space below labels */
    font-size: 14.5px; /* Adjusted font size */
    font-weight: 600; 
}

/* Input field styling */
input[type="text"], input[type="datetime-local"], input[type="email"], input[type="number"], input[type="date"] {
    width: calc(100% - 20px); /* Full width minus padding */
    padding: 10px; /* Increased padding for input fields */
    border: 1px solid #ccc; /* Border for inputs */
    border-radius: 4px; /* Rounded corners for inputs */
}

/* Button styling */
button {
    background-color: #28a745; /* Green background for the button */
    color: white; /* White text */
    padding: 10px 15px; /* Increased padding around text */
    border: none; /* Remove border */
    border-radius: 4px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    margin-top: 15px; /* Increased margin on top */
}

button:hover {
    background-color: blue; /* Changed to blue on hover for visibility */
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
    </div>
    </div>

    <script>
        // Toggle dropdown menu visibility
        function toggleDropdown() {
            const dropdown = document.getElementById("yearDropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

        // Close dropdown if clicked outside
        window.onclick = function(event) {
            if (!event.target.matches('.menu-button')) {
                const dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    if (dropdowns[i].style.display === "block") {
                        dropdowns[i].style.display = "none";
                    }
                }
            }
        };

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


</body>
</html>
