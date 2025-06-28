<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



$renumberQuery = "SET @new_num = 0;
                  UPDATE accounts SET Num = (@new_num := @new_num + 1) ORDER BY Num ASC";

if ($conn->multi_query($renumberQuery)) {
    // Ensure the queries have executed before moving on
    while ($conn->next_result()) {
        if ($conn->more_results()) continue;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <title>Account Management</title>
  
    <style>


table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 15px; /* Reduced margin */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

th, td {
    border: 1px solid #ddd;
    padding: 10px; /* Reduced padding */
    text-align: center;
}

th {
    background-color: green;
    color: white;
    font-weight: bold;
}

tr:hover {
    background-color: lightgreen;
}






@media (max-width: 768px) {
    .sidebar {
        position: relative; 
        height: auto; 
        width: 100%; 
        padding: 10px; 
    }

    .container {
        margin-left: 0; 
        padding: 10px; 
        width: 100%; 
    }

    table {
        font-size: 12px; /* Reduced font size */
    }

    th, td {
        padding: 6px; /* Reduced padding */
    }
}

.student { background-color: #d0e8ff; }
.faculty { background-color: #ffd0d0; }

#searchInput {
    width: 28%;
    padding: 10px; /* Reduced padding */
    margin: 8px 0; /* Reduced margin */
    border: 1px solid #ccc; 
    border-radius: 5px; 
    font-size: 14px; /* Reduced font size */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
}
#searchInput:focus {
    border-color: #4CAF50; /* Green border when focused */
    outline: none; /* Remove the default focus outline */
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.5); /* Green shadow around the dropdown */
}
#searchContainer {
    margin-bottom: 0; 
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
    font-size: 20px;
  
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
    padding: 0px; /* Reduced padding */
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
/* Container to align input field and button */
#deleteYearContainer {
    display: flex;
    align-items: center; /* Align input and button vertically */
    gap: 8px; /* Spacing between input field and button */
    margin: 0 10px; /* Add margin for spacing from surrounding elements */
}

/* Input field styling */
#yearInput {
    width: 100px; /* Slightly adjusted for better readability */
    padding: 5px 10px; /* Consistent padding */
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box; /* Ensure padding doesn't affect width */
    height: 38px; /* Ensures height consistency with button */
}

/* Button styling */
#deleteYearContainer button {
    padding: 5px 10px; /* Matches input field padding */
    background-color: #dc3545;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    height: 38px; /* Matches input field height */
    box-sizing: border-box; /* Ensures padding doesn't affect size */
    display: flex;
    align-items: center; /* Center text vertically */
    justify-content: center; /* Center text horizontally */
    transition: background-color 0.3s ease;
}

/* Button hover effect */
#deleteYearContainer button:hover {
    background-color: #c82333;
}



/* Container for the select dropdown */
#tableSelect {
    font-family: 'Arial', sans-serif; /* Set the font family */
    font-size: 14px; /* Set the font size */
    padding: 1px; /* Add padding inside the dropdown */
    border: 2px solid #ddd; /* Light border around the dropdown */
    border-radius: 5px; /* Rounded corners */
    background-color: #fff; /* White background */
    color: #333; /* Dark text color */
    width: 200px; /* Set the width of the dropdown */
    transition: all 0.3s ease; /* Smooth transition for focus and hover effects */
}

/* Styling for the dropdown when it is focused */
#tableSelect:focus {
    border-color: #4CAF50; /* Green border when focused */
    outline: none; /* Remove the default focus outline */
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.5); /* Green shadow around the dropdown */
}

/* Style the options inside the dropdown */
#tableSelect option {
    padding: 10px; /* Add padding for options */
    background-color: #fff; /* White background for options */
    color: #333; /* Dark text color for options */
}

/* Hover effect for options */
#tableSelect option:hover {
    background-color: #f0f0f0; /* Light gray background on hover */
}

.btn1 {
            background-color: blue; /* Blue button for better visibility */
    color: white;
    border: none;
    padding: 3px 15px; /* Increased padding */
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s; /* Added transform for effect */
    margin-top: 1px;
        }

.btn1:hover {
    background-color: green; /* Darker green on hover */
    transform: scale(1.05); /* Slightly enlarges button */
}

/* Button styling */
.btn {
    background-color: red; /* Blue button for better visibility */
    color: white;
    border: none;
    padding: 5px 5px; /* Increased padding */
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s; /* Added transform for effect */
    margin-top: 1px;

}

.btn:hover {
    background-color: darkred; /* Darker blue on hover */
    transform: scale(1.05); /* Slightly enlarges button */
}

    </style>
    <script>



      // Function to search records based on the selected table
function searchTable() {
    let input = document.getElementById("searchInput").value.toUpperCase();
    let tableSelect = document.getElementById("tableSelect").value;
    let table, tr, td, i, j, txtValue;

    if (tableSelect === "personal_info") {
        table = document.querySelector(".table-wrapper:nth-child(1) table"); // Personal Info Table
    } else {
        table = document.querySelector(".table-wrapper:nth-child(2) table"); // Faculty Table
    }

    tr = table.getElementsByTagName("tr");

    // Loop through all table rows and hide those that don't match the search query
    for (i = 1; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td");
        let rowMatch = false;

        // Loop through all columns to check if any matches the search
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(input) > -1) {
                    rowMatch = true;
                    break;
                }
            }
        }

        // If no match is found, hide the row
        if (rowMatch) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
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


<div class="container">
    <div class="main-content">
        <header>
            <h1>ACCOUNT MANAGEMENT</h1>
        </header>

        <div id="searchContainer">
            
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for records..." />
           
        </div>
        <select id="tableSelect" onchange="searchTable()">
        <option value="">Select Records</option>
                <option value="personal_info">Student Accounts</option>
                <option value="faculty">Personnel Accounts</option>
            </select>
        <!-- Delete All for Specific Year -->
        <div id="deleteYearContainer">
            <input type="text" id="yearInput" placeholder="Enter year" />
            <button onclick="deleteAllByYear()">
                <span class="material-icons">delete</span>
            </button>
        </div>

        <?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query for personal_info with accounts
$queryPersonalInfo = "
    SELECT pi.Num, pi.IDNumber, pi.GmailAccount, pa.Password, pi.years
    FROM personal_info_accounts pa
    INNER JOIN personal_info pi ON pa.IDNumber = pi.IDNumber";
$resultPersonalInfo = mysqli_query($conn, $queryPersonalInfo);
if (!$resultPersonalInfo) {
    die("Error executing personal_info query: " . mysqli_error($conn));
}

// Query for faculty with accounts
$queryFaculty = "
    SELECT fa.Num, fa.IDNumber, fa.GmailAccount, fa.Password, fa.years,
           f.department, f.position
    FROM faculty_accounts fa
    INNER JOIN faculty f ON fa.IDNumber = f.IDNumber";
$resultFaculty = mysqli_query($conn, $queryFaculty);
if (!$resultFaculty) {
    die("Error executing faculty query: " . mysqli_error($conn));
}
?>

<div class="table-container">
    <!-- Personal Info Table -->
    <div class="table-wrapper">
        <h3>STUDENT ACCOUNTS</h3>
        <table border="1">
            <tr>
                <th>#</th>
                <th>ID Number</th>
                <th>Gmail Account</th>
                <th>Password</th>
                <th>Years</th>
                <th>Action</th>
            </tr>
            <?php
            $renumberedIndex = 1;
            if (mysqli_num_rows($resultPersonalInfo) > 0) {
                while ($row = mysqli_fetch_assoc($resultPersonalInfo)) {
                    echo "<tr>";
                    echo "<td>" . $renumberedIndex . "</td>";
                    echo "<td>" . $row['IDNumber'] . "</td>";
                    echo "<td>" . $row['GmailAccount'] . "</td>";
                    echo "<td>" . $row['Password'] . "</td>";
                    echo "<td>" . $row['years'] . "</td>";
                    echo "<td>
                                                <button class='btn1' onclick='openEditModal(\"" . $row['IDNumber'] . "\", \"" . $row['GmailAccount'] . "\", \"" . $row['Password'] . "\", \"" . $row['years'] . "\", \"personal_info_accounts\")'>Edit</button>

                            <button class='btn' onclick='deleteAccount(\"" . $row['GmailAccount'] . "\", \"personal_info_accounts\")'>Delete</button>
                          </td>";
                    echo "</tr>";
                    $renumberedIndex++;
                }
            } else {
                echo "<tr><td colspan='6' style='color: red;'>No student accounts found.</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Faculty Table -->
    <div class="table-wrapper">
        <h3>PERSONNEL ACCOUNTS</h3>
        <table border="1">
            <tr>
                <th>#</th>
                <th>ID Number</th>
                <th>Gmail Account</th>
                <th>Password</th>
                <th>Years</th>
                <th>Action</th>
            </tr>
            <?php
            $renumberedIndex = 1;
            if (mysqli_num_rows($resultFaculty) > 0) {
                while ($row = mysqli_fetch_assoc($resultFaculty)) {
                    echo "<tr>";
                    echo "<td>" . $renumberedIndex . "</td>";
                    echo "<td>" . $row['IDNumber'] . "</td>";
                    echo "<td>" . $row['GmailAccount'] . "</td>";
                    echo "<td>" . $row['Password'] . "</td>";
                    echo "<td>" . $row['years'] . "</td>";
                    echo "<td>
                            <button class='btn1' onclick='openEditModal(\"" . $row['IDNumber'] . "\", \"" . $row['GmailAccount'] . "\", \"" . $row['Password'] . "\", \"" . $row['years'] . "\", \"faculty_accounts\")'>Edit</button>
                                                        <button class='btn' onclick='deleteAccount(\"" . $row['GmailAccount'] . "\", \"faculty_accounts\")'>Delete</button>

                          </td>";
                    echo "</tr>";
                    $renumberedIndex++;
                }
            } else {
                echo "<tr><td colspan='6' style='color: red;'>No personnel accounts found.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>EDIT ACCOUNT</h2>
        <form id="editForm" method="POST" action="account.php"> <!-- This can be dynamically updated as well -->
            <label for="editIDNumber">ID Number:</label>
            <input type="text" id="editIDNumber" name="IDNumber" required>
            <br>
            <label for="editGmailAccount">Gmail Account:</label>
            <input type="text" id="editGmailAccount" name="GmailAccount" required readonly>
            <br>
            <label for="editPassword">Password:</label>
            <input type="text" id="editPassword" name="Password" required>
            <br>
            <input type="hidden" id="editYears" name="Years" required readonly> 
            <br><br>
            <button type="button1" onclick="submitEditForm()">Save Changes</button>
        </form>
    </div>
</div>


<script>
// Open Edit Modal and Populate the Fields
function openEditModal(idNumber, gmail, password, years, table) {
    document.getElementById("editModal").style.display = "block";
    document.getElementById("editIDNumber").value = idNumber;  // Populating IDNumber (editable)
    document.getElementById("editGmailAccount").value = gmail;
    document.getElementById("editPassword").value = password;
    document.getElementById("editYears").value = years;
    document.getElementById("editForm").action = table === "personal_info_accounts" ? "update_personal_info.php" : "update_facultys.php";
}

// Close Edit Modal
function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

// Modal Styles
var modal = document.getElementById("editModal");
window.onclick = function(event) {
    if (event.target == modal) {
        closeEditModal();
    }
}

</script>


<!-- Modal Styles -->
<style>
input[type="text"]:focus, select:focus {
    border-color: #007bff; /* Change border color on focus */
    outline: none; /* Remove default outline */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Light blue shadow on focus */
}
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
    background-color: lightblue; 
    border-radius: 8px; /* Added border radius for rounded corners */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
    margin: 13% auto;
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
    margin-bottom: 15px; /* Space between form fields */
}

label {
    display: block; /* Labels on their own line */
    margin-top: 5px; /* Space below labels */
    font-size: 14.5px;
    font-weight: 600; 
}

/* Input field styling */
input[type="text"] {
    width: calc(100% - 20px); /* Full width minus padding */
    padding: 10px; /* Padding for input fields */
    border: 1px solid #ccc; /* Border for inputs */
    border-radius: 4px; /* Rounded corners for inputs */
}

/* Button Style */
button[type="button1"] {
    background-color: #4CAF50; /* Green background */
    color: white; /* White text */
    border: none; /* No border */
    padding: 10px 20px; /* Padding around the text */
    text-align: center; /* Center text inside the button */
    text-decoration: none; /* No underline */
    display: inline-block; /* Make the button inline */
    font-size: 16px; /* Font size */
    margin: 10px 0; /* Margin around the button */
    cursor: pointer; /* Cursor as pointer to show it's clickable */
    border-radius: 5px; /* Rounded corners */
    margin-top: -40px;
}

/* Hover Effect */
button[type="button1"]:hover {
    background-color: blue; /* Darker green when hovering */
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


<script>
    // JavaScript function to delete account
    function deleteAccount(gmailAccount, tableName) {
        if (confirm('Are you sure you want to delete this account?')) {
            // Sending AJAX request to delete the account
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_account.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Account deleted successfully');
                        location.reload(); // Reload page after deletion
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            };
            xhr.send('GmailAccount=' + encodeURIComponent(gmailAccount) + '&table=' + encodeURIComponent(tableName));
        }
    }
</script>

<?php
// Close the database connection
mysqli_close($conn);
?>

                </table>
            </div>
        </div>
    </div>
</div>


<style>

h3 {
    text-align: center;
    margin-bottom: 2px; /* More spacing for a cleaner look */
    color: green; /* Darker text for better contrast */
    font-size: 1.5em; /* Slightly larger for prominence */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
}
    .table-container {
        display: flex;
        gap: 5px; /* Space between tables */
        justify-content: space-between;
    }

    .table-wrapper {
        width: 60%; /* Adjust to fit two tables side by side */
    }

    table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 15px; /* Reduced margin */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

th, td {
    border: 1px solid #ddd;
    padding: 10px; /* Reduced padding */
    text-align: center;
}

th {
    background-color: green;
    color: white;
    font-weight: bold;
}

tr:hover {
    background-color: lightgreen;
}


  
</style>

<script>
    // JavaScript function to delete all accounts for a specified year
    function deleteAllByYear() {
        const year = document.getElementById('yearInput').value;

        if (year === "") {
            alert("Please enter a year.");
            return;
        }

        if (confirm(`Are you sure you want to delete all accounts for the year ${year}?`)) {
            // Send AJAX request to delete_by_year.php
            fetch('delete_by_year.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `year=${encodeURIComponent(year)}`
            })
            .then(response => response.text())
            .then(data => {
                alert(data); // Display response message
                location.reload(); // Reload the page to update the table
            })
            .catch(error => console.error('Error:', error));
        }
    }
</script>

<style>
   button {
       display: flex;
       align-items: center;
       padding: 12px 12px;
       font-size: 16px;
       background-color: #d9534f;
       color: white;
       border: none;
       border-radius: 5px;
       cursor: pointer;
   }

   button i, button .material-icons {
       margin-right: 0px;
   }

   button:hover {
       background-color: #c9302c;
   }
</style>


</body>
</html>
