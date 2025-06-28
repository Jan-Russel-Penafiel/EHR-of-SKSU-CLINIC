<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the IDNumber from the URL parameter for auto-fill
$searchIDNumber = isset($_GET['IDNumber']) ? htmlspecialchars($_GET['IDNumber']) : '';

// Initialize variables
$userInfo = null;
$facultyRecords = []; // Updated to store multiple faculty records
$selectedYear = date('Y'); // Default to current year

// Get the IDNumber from the URL parameter if available, or use POST if form is submitted
if (isset($_GET['IDNumber']) && !empty($_GET['IDNumber'])) {
    $searchIDNumber = htmlspecialchars($_GET['IDNumber']);
} elseif (isset($_POST['IDNumber']) && !empty($_POST['IDNumber'])) {
    $searchIDNumber = htmlspecialchars($_POST['IDNumber']);
}

// Get selected year from POST (if available)
if (isset($_POST['selectedYear'])) {
    $selectedYear = mysqli_real_escape_string($conn, $_POST['selectedYear']);
}

// Query to retrieve user information from the personal_info table based on IDNumber and selected year
if ($searchIDNumber) {
    $queryPersonalInfo = "
        SELECT Num, FirstName, LastName, IDNumber, BirthDate, Age, Gender, Course, Yr, Section, years, ProfilePicture 
        FROM personal_info 
        WHERE IDNumber = '$searchIDNumber' 
        AND years = '$selectedYear'";

    $resultPersonalInfo = mysqli_query($conn, $queryPersonalInfo);

    if ($resultPersonalInfo && mysqli_num_rows($resultPersonalInfo) > 0) {
        $userInfo = mysqli_fetch_assoc($resultPersonalInfo);
    } else {
        $userInfo = false;
    }
}

// Fetch intervention records if user info is found
$resultIntv = null;
if ($userInfo && $userInfo['IDNumber'] != "N/A") {
    $idNumber = $userInfo['IDNumber'];
    $queryIntv = "
        SELECT IDNumber, what_you_do, what_is_your_existing_desease, have_you_a_family_history_desease, have_you_a_allergy, years 
        FROM intv 
        WHERE IDNumber = '$idNumber' 
        AND years = '$selectedYear'";

    $resultIntv = mysqli_query($conn, $queryIntv);
}

// Fetch illness medication records if user info is found
$resultIllmed = null;
if ($userInfo && $userInfo['IDNumber'] != "N/A") {
    $idNumber = $userInfo['IDNumber'];
    $queryIllmed = "
        SELECT IDNumber, IllName, MedName, Prescription, Temperature, BloodPressure, Appointment_Date, years 
        FROM illmed 
        WHERE IDNumber = '$idNumber' 
        AND years = '$selectedYear'";

    $resultIllmed = mysqli_query($conn, $queryIllmed);
}

// Fetch faculty data based on IDNumber and selected year (allow duplicates)
if ($searchIDNumber) {
    $queryFaculty = "
        SELECT Num, IDNumber, Rank, FirstName, LastName, GmailAccount, Department, Position, Complains, Temperature, BloodPressure, 
               HeartRate, RespiratoryRate, Height, Weight, AppointmentDate, ProfilePicture, years
        FROM faculty
        WHERE IDNumber = '$searchIDNumber'
          AND years = '$selectedYear'";

    $resultFaculty = mysqli_query($conn, $queryFaculty);

    if ($resultFaculty && mysqli_num_rows($resultFaculty) > 0) {
        // Fetch all rows as an associative array
        while ($row = mysqli_fetch_assoc($resultFaculty)) {
            $facultyRecords[] = $row; // Store each record in the array
        }
    } else {
        // Handle case where no faculty information is found
        $facultyRecords = []; // No data found for faculty
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Student</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
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
    border-radius: 10px;
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
            left: 220px; /* Initially set to match the sidebar width */
            height: 100vh; /* Full height */
            background-color: transparent; /* Invisible, can change for visibility */
            z-index: 1100; /* Above sidebar */
        }

        .main-content { 
    background-color: rgba(173, 216, 230, 0.50); /* Light blue background with 50% transparency */
    border-radius: 8px;
    padding: 0px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 11.5px;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    text-align: center;
    height: 300px; /* Fixed height */
    overflow-y: hidden; /* Enable vertical scrolling */
    overflow-x: hidden; /* Prevent horizontal scrolling */
}



h3 {
    margin-bottom: 20px;
    color: green; /* Keeping the green theme */
    font-size: 24px; /* Larger font for headings */
    border-bottom: 2px solid #4CAF50; /* Underline for emphasis */
    padding-bottom: 10px; /* Spacing below the heading */
    text-align: center;
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
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    
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
    padding: 10px 10px; /* Increased padding */
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
    padding: 10px 15px; /* Increased padding */
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

        #searchContainer {
            margin-bottom: 20px; /* Space below the search bar */
        }


.alert-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            margin-left: 10px;
            margin-top: 3px;
        }

        .alert-btn:hover {
            background-color: darkred; /* Darker shade on hover */
    transform: translateY(-2px); /* Lift the button slightly */
    border-color: #1e7e34; /* Change border color on hover */
        }

        .btn1 {
            background-color: blue;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            margin-left: 10px;
}

.btn1:hover {
    background-color: darkblue; /* Darker shade on hover */
    transform: translateY(-2px); /* Lift the button slightly */
    border-color: #1e7e34; /* Change border color on hover */
}

.btn1:active {
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


        .dashboard-container {
            background-color: lightblue;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            transition: box-shadow 0.3s ease;
            margin-left: 500px;
            margin-top: 15px;
            
            

        }
        h1 {
            text-align: center;
            color: #007bff;
            font-size: 35px;
            margin-bottom: 10px;
        }
        .info-item {
            margin: 10px 0;
            font-size: 16px;
            line-height: 1.5;
        }
        .info-item strong {
            color: black;
        }
        .profile-picture {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid green;
            display: block;
            margin: 0 auto 20px;
        }
        .record-list {
            list-style: none;
            padding: 0;
            margin: 0px 0;
            
        }
        .record-list li {
            padding: 12px;
            background: lightblue;
            margin: 0px 0;
            border-radius: 5px;
            font-size: 16px;
            
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px 0;
        }

        input[type="text"] {
            width: 70%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #007bff;
            border-radius: 5px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            font-size: 18px;
            transition: border-color 0.3s ease;
            margin-top:5px;
        }

        input[type="text"]:focus {
            border-color: #0056b3;
            outline: none;
        }

        .action-button {
            padding: 10px 20px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
            margin-bottom: 10px;
        }

        .action-button:hover {
            background: linear-gradient(90deg, #0056b3, #004085);
        }

        .nav-buttons {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .nav-button {
          
            padding: 3px 10px;
            background: green;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13.8px;
            transition: background 0.3s ease;
            margin: 2px;
            margin-bottom: -20px;
        }

        .nav-button:hover {
            background: #218838;
        }
    
       
        label {
    font-size: 16px;
    font-weight: bold;
    margin-right: 10px;
    margin-top: 10px;
}

#selectedYear {
    width: 100px;
    margin-right: 97px;
    margin-top: 10px;
    height: 35px;
    font-size: 14px;
    padding: 5px;
    border: 2px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
    color: #333;
    cursor: pointer;
    transition: border-color 0.3s, box-shadow 0.3s;
}

/* Style for hover and focus */
#selectedYear:hover {
    border-color: #007bff;
}

#selectedYear:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Adjusting dropdown arrow */
#selectedYear::-ms-expand {
    display: none;
}



/* Optional: Add a container to center or align the dropdown */
.select-container {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.nav-button i {
    margin-right: 3px; /* Space between icon and text */
    font-size: 1.3em;  /* Size of the icon */
}

        @media (max-width: 600px) {
            input[type="text"], .action-button {
                width: 90%;
            }
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


<div class="dashboard-container">
    <h1>SEARCH STUDENT</h1>
    <hr>
    <form method="POST" action="search1.php">
        <input type="text" name="IDNumber" id="IDNumber" value="<?php echo $searchIDNumber; ?>" placeholder="Enter ID Number" required>
        <input type="submit" class="action-button" value="Search">
        <a href="qr_scanner1.php" class="action-button">QR Code Scanner</a>
        
        <div class="select-container">
            <label for="selectedYear">Select Year:</label>
            <select id="selectedYear" name="selectedYear">
                <?php for ($year = 2024; $year <= 2100; $year++): ?>
                    <option value="<?php echo $year; ?>" <?php echo ($selectedYear == $year) ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
    </form>
    <?php if ($userInfo && is_array($userInfo)): ?>
    <div>
    <h3>STUDENT INFORMATION</h3>
        <img src="<?php echo $userInfo['ProfilePicture'] != "N/A" ? htmlspecialchars($userInfo['ProfilePicture']) : 'default-profile.png'; ?>" alt="Profile Picture" class="profile-picture">
       
        <div class="info-item"><strong>First Name: </strong> <?php echo htmlspecialchars($userInfo['FirstName']); ?></div>
        <div class="info-item"><strong>Last Name: </strong> <?php echo htmlspecialchars($userInfo['LastName']); ?></div>
        <div class="info-item"><strong>ID Number: </strong> <?php echo htmlspecialchars($userInfo['IDNumber']); ?></div>
        <div class="info-item"><strong>Birth Date: </strong> 
            <?php echo ($userInfo['BirthDate'] != "N/A") ? date("F d, Y", strtotime($userInfo['BirthDate'])) : "N/A"; ?>
        </div>
        <div class="info-item"><strong>Age: </strong> <?php echo htmlspecialchars($userInfo['Age']); ?></div>
        <div class="info-item"><strong>Gender: </strong> <?php echo htmlspecialchars($userInfo['Gender']); ?></div>
        <div class="info-item"><strong>Course: </strong> <?php echo htmlspecialchars($userInfo['Course']); ?></div>
        <div class="info-item"><strong>Year: </strong> <?php echo htmlspecialchars($userInfo['Yr']); ?></div>
        <div class="info-item"><strong>Section: </strong> <?php echo htmlspecialchars($userInfo['Section']); ?></div>
    </div>
<?php else: ?>

<?php endif; ?>



        <?php if ($resultIntv && mysqli_num_rows($resultIntv) > 0): ?>
            <h3>Consultation Records</h3>
            <ul class="record-list">
                <?php while ($rowIntv = mysqli_fetch_assoc($resultIntv)): ?>
                    <li>Activities: <?php echo htmlspecialchars($rowIntv['what_you_do']); ?></li>
                    <li>Existing Disease: <?php echo htmlspecialchars($rowIntv['what_is_your_existing_desease']); ?></li>
                    <li>Family history: <?php echo htmlspecialchars($rowIntv['have_you_a_family_history_desease']); ?></li>
                    <li>Allergies: <?php echo htmlspecialchars($rowIntv['have_you_a_allergy']); ?></li>
                    <br>
                    <hr>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>

        <?php if ($resultIllmed && mysqli_num_rows($resultIllmed) > 0): ?>
            <h3>Treatment Records</h3>
            <ul class="record-list">
                <?php while ($rowIllmed = mysqli_fetch_assoc($resultIllmed)): ?>
                    <li>Illness Name: <?php echo htmlspecialchars($rowIllmed['IllName']); ?></li>
                    <li>Medication Name: <?php echo htmlspecialchars($rowIllmed['MedName']); ?></li>
                    <li>Prescription: <?php echo htmlspecialchars($rowIllmed['Prescription']); ?></li>
                    <li>Temperature: <?php echo htmlspecialchars($rowIllmed['Temperature']); ?> °C</li>
                    <li>Blood Pressure: <?php echo htmlspecialchars($rowIllmed['BloodPressure']); ?> mmHg</li>
                    <li>Appointment Date: <?php echo date("F d, Y g:i A", strtotime($rowIllmed['Appointment_Date'])); ?></li>
                    <br>
                    <hr>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
        
        <?php if (!empty($facultyRecords)): ?>
    <?php $firstRecord = true; // Flag to track the first record ?>
    <?php foreach ($facultyRecords as $facultyInfo): ?>
        <div class="faculty-info">
            <h3>PERSONNEL INFORMATION</h3>
            <?php if ($firstRecord): ?>
                <img src="<?php echo htmlspecialchars($facultyInfo['ProfilePicture']); ?>" alt="Faculty Profile Picture" class="profile-picture">
                <?php $firstRecord = false; // Set flag to false after displaying the first record ?>
            <?php endif; ?>
            <div class="info-item"><strong>Full Name: </strong> <?php echo htmlspecialchars($facultyInfo['FirstName']) . " " . htmlspecialchars($facultyInfo['LastName']); ?></div>
            <div class="info-item"><strong>ID Number: </strong> <?php echo htmlspecialchars($facultyInfo['IDNumber']); ?></div>

            <div class="info-item"><strong>Academic Rank: </strong> <?php echo htmlspecialchars($facultyInfo['Rank']); ?></div>
            <div class="info-item"><strong>Gmail Account: </strong> <?php echo htmlspecialchars($facultyInfo['GmailAccount']); ?></div>
            <div class="info-item"><strong>Department: </strong> <?php echo htmlspecialchars($facultyInfo['Department']); ?></div>
            <div class="info-item"><strong>Position: </strong> <?php echo htmlspecialchars($facultyInfo['Position']); ?></div>
            <div class="info-item"><strong>Complaints: </strong> <?php echo htmlspecialchars($facultyInfo['Complains']); ?></div>
            <div class="info-item"><strong>Temperature: </strong> <?php echo htmlspecialchars($facultyInfo['Temperature']); ?> °C</div>
            <div class="info-item"><strong>Blood Pressure: </strong> <?php echo htmlspecialchars($facultyInfo['BloodPressure']); ?> mmHg</div>
            <div class="info-item"><strong>Heart Rate: </strong> <?php echo htmlspecialchars($facultyInfo['HeartRate']); ?> bpm</div>
            <div class="info-item"><strong>Respiratory Rate: </strong> <?php echo htmlspecialchars($facultyInfo['RespiratoryRate']); ?> breaths/min</div>
            <div class="info-item"><strong>Height: </strong> <?php echo htmlspecialchars($facultyInfo['Height']); ?> cm</div>
            <div class="info-item"><strong>Weight: </strong> <?php echo htmlspecialchars($facultyInfo['Weight']); ?> kg</div>
            <div class="info-item"><strong>Appointment Date: </strong> 
                <?php echo date("F d, Y g:i A", strtotime($facultyInfo['AppointmentDate'])); ?>
            </div>
        </div>
        <hr> <!-- Divider between multiple records -->
    <?php endforeach; ?>
<?php else: ?>
    <div class="faculty-info">
        <p></p>
    </div>
<?php endif; ?>

        <div class="nav-buttons">
        <a href="insert_intv1.php?IDNumber=<?php echo htmlspecialchars($userInfo['IDNumber']); ?>" class="nav-button">
    <i class="fas fa-plus-circle"></i>Student Consultation
</a>

<a href="insert_illmed1.php?IDNumber=<?php echo htmlspecialchars($userInfo['IDNumber']); ?>" class="nav-button">
    <i class="fas fa-prescription-bottle-alt"></i>Student Treatment
</a>
<a href="insert_faculty2.php?IDNumber=<?php echo htmlspecialchars($facultyInfo['IDNumber']); ?>" class="nav-button">
    <i class="fas fa-edit"></i> Personnel Records
</a>

<a href="insert_faculty3.php?IDNumber=<?php echo htmlspecialchars($facultyInfo['IDNumber']); ?>" class="nav-button">
    <i class="fas fa-users"></i>Personnel Records
</a>


  
        <div class="back-to-top-container">
    <button id="backToTopBtn" class="back-to-top-btn" onclick="scrollToTop()">Back to Top</button>
</div>
    </div>



       

    
</div>


<script>
    // Function to scroll to the bottom of the page with smooth scrolling
    function scrollToBottom() {
        window.scrollTo({
            top: document.body.scrollHeight,
            behavior: 'smooth' // Enables smooth scrolling
        });
    }

    // Automatically scroll to the bottom when the page is loaded
    window.onload = function() {
        scrollToBottom();
    };

    // Scroll to the top when the button is clicked
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth' // Enables smooth scrolling
        });
    }

    // Show or hide the "back to top" button based on scroll position
    window.onscroll = function() {
        let backToTopBtn = document.getElementById("backToTopBtn");
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            backToTopBtn.style.display = "block"; // Show the button when scrolling down
        } else {
            backToTopBtn.style.display = "none"; // Hide the button when at the top
        }
    }
</script>


<style>
    /* Style the Back to Top button */
    .back-to-top-container {
        position: fixed;
        bottom: 20px;
        right: 210px;
        z-index: 100;
    }

    .back-to-top-btn {
        display: none;
        background-color: green;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .back-to-top-btn:hover {
        background-color: #0056b3;
    }
</style>



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
</script>
</body>
</html>