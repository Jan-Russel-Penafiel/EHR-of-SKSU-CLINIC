<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
require 'vendor/autoload.php'; // Adjust the path as necessary

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Secure credentials using environment variables
$emailUsername = getenv('EMAIL_USERNAME');
$emailPassword = getenv('EMAIL_PASSWORD');

// Function to send email using PHPMailer
function sendAlertEmail($email, $firstName, $lastName, $criticalStatuses, $facultyId, $conn) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sksuisulanschoolclinic@gmail.com';
        $mail->Password = 'ukti coep ddhn tzhh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('sksuisulanschoolclinic@gmail.com', 'Health Alert System');
        $mail->addAddress($email, $firstName . ' ' . $lastName);

        $mail->isHTML(true);
        $mail->Subject = "Health Alert: Critical Conditions Detected for $firstName $lastName";
        $mail->Body = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        color: #333;
                        line-height: 1.6;
                    }
                    h1 {
                        color: #d9534f;
                        font-size: 24px;
                    }
                    p {
                        font-size: 16px;
                    }
                    ul {
                        list-style-type: none;
                        padding: 0;
                    }
                    li {
                        background-color: #f8d7da;
                        margin: 5px 0;
                        padding: 10px;
                        border-left: 4px solid #d9534f;
                    }
                    .footer {
                        margin-top: 20px;
                        font-size: 14px;
                        color: #666;
                    }
                </style>
            </head>
            <body>
                <h1>Urgent Health Alert</h1>
                <p>Dear $firstName $lastName,</p>
                <p>Our monitoring system has detected the following critical health conditions:</p>
                <ul>
                    <li>" . implode('</li><li>', $criticalStatuses) . "</li>
                </ul>
                <p>Please take immediate precautions and consult a healthcare professional if necessary.</p>
                <p>Stay safe and take care of your health.</p>
                <div class='footer'>
                    <p>Regards,<br>School Clinic Personnels Monitoring System</p>
                </div>
            </body>
            </html>";

        $mail->send();

        // Increment alert count in the database and update alert_sent flag
        $updateQuery = "UPDATE faculty SET alert_sent = alert_sent + 1, alert_sent = TRUE WHERE Num = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $facultyId);
        $stmt->execute();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Define critical health thresholds
$criticalTemperature = 38.0;
$criticalBP = ['high' => 140, 'low' => 90];
$criticalHeartRate = 100;
$criticalRespiratoryRate = 20;  // Adjusted for critical respiratory rate threshold

// Function to determine critical statuses
function getCriticalStatus($temperature, $bloodPressure, $heartRate, $respiratoryRate) {
    global $criticalTemperature, $criticalBP, $criticalHeartRate, $criticalRespiratoryRate;

    $criticalStatuses = [];
    
    if ($temperature > $criticalTemperature) {
        $criticalStatuses[] = 'High Temperature';
    }
    
    // Parse blood pressure as a VARCHAR
    $bpParts = explode("/", $bloodPressure);
    $systolicBP = isset($bpParts[0]) ? (int)$bpParts[0] : 0;
    $diastolicBP = isset($bpParts[1]) ? (int)$bpParts[1] : 0;
    
    if (($systolicBP > $criticalBP['high']) || ($diastolicBP > $criticalBP['low'])) {
        $criticalStatuses[] = 'Abnormal Blood Pressure';
    }
    
    if ($heartRate > $criticalHeartRate) {
        $criticalStatuses[] = 'High Heart Rate';
    }
    
    if ($respiratoryRate > $criticalRespiratoryRate) {
        $criticalStatuses[] = 'High Respiratory Rate';
    }

    return $criticalStatuses;
}

// Handle faculty deletion
function deleteFaculty($facultyId, $conn) {
    $deleteQuery = "DELETE FROM faculty WHERE Num = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $facultyId);

    if ($stmt->execute()) {
        echo "Faculty member deleted successfully.";
    } else {
        echo "Error deleting faculty member: " . $conn->error;
    }
}

// If the form was submitted, process the alert for the specific faculty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['faculty_id'])) {
    $facultyId = $_POST['faculty_id'];

    $query = "SELECT * FROM faculty WHERE Num = ? AND alert_sent = FALSE";  // Only show unsent alerts
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $facultyId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $faculty = $result->fetch_assoc();

        $criticalStatuses = getCriticalStatus(
            $faculty['Temperature'], 
            $faculty['BloodPressure'], 
            $faculty['HeartRate'], 
            $faculty['RespiratoryRate']
        );

        if (!empty($criticalStatuses)) {
            sendAlertEmail($faculty['GmailAccount'], $faculty['FirstName'], $faculty['LastName'], $criticalStatuses, $facultyId, $conn);
        } else {
            echo 'No critical health conditions detected.';
        }
    } else {
        echo 'Faculty member not found or alert already sent.';
    }

    // Redirect to display_faculty.php
    header("Location: display_faculty.php");
    exit();
}

$queryFaculty = "SELECT * FROM faculty WHERE alert_sent = FALSE"; // Only fetch unsent alerts
$resultFaculty = $conn->query($queryFaculty);

if (!$resultFaculty) {
    echo "Error fetching faculty data: " . $conn->error;
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Alerts</title>
    <style>

        
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

        h1 {
            color: white;
            text-align: center;
            margin-bottom: 20px;
            font-size: 40px;
        }

        table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 3px;
}

th, td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    border: 0.1px solid #ddd;
    
}

th {
            background-color: red; /* Green header */
            color: white;
            font-weight: bold; /* Bold text for headers */
            cursor: pointer; /* Change cursor to pointer for clickable headers */
            
        }


tr:hover {
    background-color: lightgreen; /* Light green hover for rows */
}

        .critical {
            background-color: #ffe5e5;
        }

        .alert {
            color: #dc3545;
            font-weight: bold;
        }

        .alert-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 5px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            font-weight: 500;
        }

        .alert-btn:hover {
            background-color: darkred;
            transform: scale(1.05);
        }

        .button-container {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c82333;
            transform: scale(1.05);
        }

        #backButton {
            display: inline-block; /* Allows padding and margin */
            background-color: #007bff; /* Blue background */
            color: white; /* White text */
            padding: 10px 20px; /* Padding around the text */
            font-size: 16px; /* Font size */
            border-radius: 5px; /* Rounded corners */
            text-decoration: none; /* Remove underline */
            transition: background-color 0.3s, transform 0.2s; /* Transition effects */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
            margin: 10px; /* Margin for spacing */
            width: 2.5%;
            

        }

        #backButton:hover {
            background-color: red; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        #backButton:active {
            transform: translateY(0); /* Reset lift effect on click */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Reduce shadow on click */
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
            margin-bottom: 5px; /* Space below the search bar */
        }

        .main-content { 
    background-color: rgba(173, 216, 230, 0.50); /* Light blue background with 50% transparency */
    border-radius: 8px;
    padding: 0px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 14px;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    text-align: center;
    height: auto; /* Set a fixed height */
    width: 100%;
   
}

.container {
           
            padding: 0px;
            transition: margin-left 0.3s;
            width: 100%;
        }

        .button {
    background-color: red; /* Blue button for better visibility */
    color: white;
    border: none;
    padding: 10px 15px; /* Increased padding */
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s; /* Added transform for effect */
    margin-top: 10px;
}

.button:hover {
    background-color: darkred; /* Darker blue on hover */
    transform: scale(1.05); /* Slightly enlarges button */
}

.critical-status {
    color: #dc3545; /* Red color to signify danger */
    font-weight: bold;
    font-size: 1.1em;
    background-color: #ffe5e5; /* Light red background for emphasis */
    padding: 0px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
}

        @media (max-width: 600px) {
            table, th, td {
                font-size: 14px;
            }

            .alert-btn {
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="main-content">
    <header>
        <h1>FACULTY MONITORING HEALTH STATUS</h1>
        <a id="backButton" href="display_faculty.php">Back</a>
    </header>

    <div id="searchContainer">
        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for records..." />
    </div>
    <button id="statusSummaryBtn">Show Critical Status Summary</button>

<!-- Modal Structure -->
<div id="statusSummaryModal" class="modal">
    <div class="modal-content">
        <!-- Close button for the modal -->
        <span class="close-btn" onclick="closeModal()">&times;</span>

        <!-- Modal Header -->
        <h3>Critical Health Status Summary</h3>

        <!-- Modal Description -->
        <p>This summary displays the count of patients with critical health statuses. Critical conditions include:</p>

        <!-- Critical Health Status List -->
        <ul>
            <li id="highTempCount">High Temperature (>37.5°C): <span>0</span></li>
            <li id="highBPCount">High Blood Pressure (Systolic > 140 mmHg): <span>0</span></li>
            <li id="highHRCount">High Heart Rate (>100 bpm): <span>0</span></li>
            <li id="highRespRateCount">High Respiratory Rate (>20 breaths/min): <span>0</span></li>
        </ul>

        <!-- Additional Information -->
        <p>Each item represents the number of patients with the corresponding critical health condition.</p>

        <!-- Patient List Container -->
        <div id="patientList">
            <h4>Patient List with Critical Conditions:</h4>
            <ul id="patientListContent">
                <!-- Patient names and conditions will be dynamically inserted here -->
            </ul>
        </div>
    </div>
</div>


<style>/* Modal Background */
/* Modal Background */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 120vh;
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.6); /* Darker background for better contrast */
    padding-top: 50px;
    transition: all 0.3s ease-in-out; /* Smooth transition */
    margin-top: -115px;
}

/* Modal Content */
.modal-content {
    background-color: whitesmoke;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
    border-radius: 10px;
    max-height: auto;
   
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
    transition: all 0.3s ease-in-out; /* Smooth transition for modal content */
}

/* Close Button */
.close-btn {
    color: #aaa;
    float: right;
    font-size: 30px;
    font-weight: bold;
    cursor: pointer;
}

.close-btn:hover,
.close-btn:focus {
    color: #ff3333; /* Red color for hover */
    text-decoration: none;
}

/* Status Summary Styling */
ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
}

li {
    font-size: 0.95em;
    padding: 8px;
    color: #555;
    border-bottom: 1px solid #ddd; /* Adding border for better separation */
}

li:first-child {
    color: #e74c3c; /* Red for high temperature */
}

li:nth-child(2) {
    color: #f39c12; /* Orange for high BP */
}

li:nth-child(3) {
    color: #f1c40f; /* Yellow for high heart rate */
}

li:nth-child(4) {
    color: #2ecc71; /* Green for high blood sugar */
}

/* Trigger Button Styling */
#statusSummaryBtn {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: -60px;
}

#statusSummaryBtn:hover {
    background-color: #2980b9; /* Darker blue on hover */
}

/* Patient List */
#patientList {
    margin-top: 20px;
}

.patient-item {
    font-size: 1em;
    margin: 8px 0;
    padding: 10px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow effect */
    transition: background-color 0.3s ease-in-out; /* Smooth hover effect */
}

.patient-item:hover {
    background-color: #f1f1f1; /* Light grey on hover */
}

/* Responsive Design for smaller screens */
@media (max-width: 768px) {
    .modal-content {
        width: 80%; /* Make the modal content take more width on smaller screens */
    }

    #statusSummaryBtn {
        width: 100%; /* Make the button full-width on small screens */
        padding: 14px;
        font-size: 1.2em;
    }
}



</style>
<script>// Get the modal
// Get the modal
const modal = document.getElementById("statusSummaryModal");

// Get the button that opens the modal
const btn = document.getElementById("statusSummaryBtn");

// Get the <span> element that closes the modal
const span = document.getElementsByClassName("close-btn")[0];

// When the user clicks the button, open the modal and update content
btn.onclick = function () {
    modal.style.display = "block";
    updateCriticalStatusSummary();
};

// When the user clicks on <span> (x), close the modal
span.onclick = function () {
    modal.style.display = "none";
};

// When the user clicks anywhere outside of the modal, close it
window.onclick = function (event) {
    if (event.target === modal) {
        modal.style.display = "none";
    }
};

// Function to update the critical status counts and patient details in the modal
function updateCriticalStatusSummary() {
    // Initialize counts
    let highTempCount = 0;
    let highBPCount = 0;
    let highHRCount = 0;
    let highRespiratoryRateCount = 0;

    // Initialize patient detail arrays
    const patientTempDetails = [];
    const patientBPDetails = [];
    const patientHRDetails = [];
    const patientRespiratoryRateDetails = [];

    // Get all rows from the table (assuming table ID is `recordsTable`)
    const tableRows = document.querySelectorAll("#recordsTable tbody tr");

    // Loop through the table rows and check for critical statuses
    tableRows.forEach((row) => {
        // Check if the row has a `critical` class
        if (row.classList.contains("critical")) {
            // Extract patient details from the respective columns
            const firstName = row.cells[2]?.textContent.trim(); // First Name
            const lastName = row.cells[3]?.textContent.trim(); // Last Name
            const patientName = `${firstName} ${lastName}`; // Full Name

            const tempCell = parseFloat(row.cells[4]?.textContent.trim()); // Temperature
            const bpCell = row.cells[5]?.textContent.trim(); // Blood Pressure
            const hrCell = parseInt(row.cells[6]?.textContent.trim(), 10); // Heart Rate
            const rrCell = parseInt(row.cells[7]?.textContent.trim(), 10); // Respiratory Rate

            // Evaluate critical conditions
            if (tempCell > 37.5) {
                highTempCount++;
                patientTempDetails.push(`${patientName} (Temp: ${tempCell}°C)`);
            }
            if (bpCell) {
                const [systolic] = bpCell.split("/").map(Number); // Extract systolic BP
                if (systolic > 140) {
                    highBPCount++;
                    patientBPDetails.push(`${patientName} (BP: ${bpCell})`);
                }
            }
            if (hrCell > 100) {
                highHRCount++;
                patientHRDetails.push(`${patientName} (HR: ${hrCell} bpm)`);
            }
            if (rrCell > 20) {
                highRespiratoryRateCount++;
                patientRespiratoryRateDetails.push(`${patientName} (Resp Rate: ${rrCell} bpm)`);
            }
        }
    });

    // Update the modal text with the counts
    document.getElementById("highTempCount").innerHTML = `High Temperature (>37.5°C): <span>${highTempCount}</span>`;
    document.getElementById("highBPCount").innerHTML = `High Blood Pressure (Systolic > 140 mmHg): <span>${highBPCount}</span>`;
    document.getElementById("highHRCount").innerHTML = `High Heart Rate (>100 bpm): <span>${highHRCount}</span>`;
    document.getElementById("highRespRateCount").innerHTML = `High Respiratory Rate (>20 breaths/min): <span>${highRespiratoryRateCount}</span>`;

    // Display detailed patient list
    const patientListDiv = document.getElementById("patientList");
    patientListDiv.innerHTML = ""; // Clear previous content

    // Append patient lists dynamically
    if (highTempCount > 0) {
        appendPatientDetails(patientListDiv, "Patients with High Temperature:", patientTempDetails);
    }
    if (highBPCount > 0) {
        appendPatientDetails(patientListDiv, "Patients with High Blood Pressure:", patientBPDetails);
    }
    if (highHRCount > 0) {
        appendPatientDetails(patientListDiv, "Patients with High Heart Rate:", patientHRDetails);
    }
    if (highRespiratoryRateCount > 0) {
        appendPatientDetails(patientListDiv, "Patients with High Respiratory Rate:", patientRespiratoryRateDetails);
    }
}

// Helper function to append patient details to the modal
function appendPatientDetails(container, title, details) {
    const sectionTitle = document.createElement("h4");
    sectionTitle.textContent = title;
    container.appendChild(sectionTitle);

    details.forEach((detail) => {
        const patientItem = document.createElement("div");
        patientItem.className = "patient-item";
        patientItem.textContent = detail;
        container.appendChild(patientItem);
    });
}

</script>
<table id="recordsTable">
    <thead>
        <tr>
            <th>#</th>
            <th>ID Number</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Temperature</th>
            <th>Blood Pressure</th>
            <th>Heart Rate</th>
            <th>Respiratory Rate</th>
            <th>Critical Status</th>
            <th>Alerts Sent</th> <!-- Add a new column for the alert count -->
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
// Initialize counters for each type of critical status
$temperatureCount = 0;
$bpCount = 0;
$heartRateCount = 0;
$respiratoryRateCount = 0; // Corrected variable name

// Loop through faculty records and process critical statuses
while ($row = mysqli_fetch_assoc($resultFaculty)) {
    // Get critical statuses for the current faculty member
    $criticalStatuses = getCriticalStatus(
        $row['Temperature'], 
        $row['BloodPressure'], 
        $row['HeartRate'], 
        $row['RespiratoryRate']
    );

    // Increment counters for each type of critical status
    foreach ($criticalStatuses as $status) {
        switch ($status) {
            case 'High Temperature':
                $temperatureCount++;
                break;
            case 'Abnormal Blood Pressure':
                $bpCount++;
                break;
            case 'High Heart Rate':
                $heartRateCount++;
                break;
            case 'High Respiratory Rate':
                $respiratoryRateCount++;
                break;
        }
    }

    // Retrieve the alert count for the current faculty member
    $alertQuery = "SELECT alert_sent FROM faculty WHERE Num = ?";
    if ($stmt = $conn->prepare($alertQuery)) {
        $stmt->bind_param("i", $row['Num']);
        $stmt->execute();
        $stmt->bind_result($alertCount);
        $stmt->fetch();
        $stmt->close();
    } else {
        $alertCount = 0; // Default to 0 if query preparation fails
    }

    // Default the alert count to 0 if no value was retrieved
    $alertCount = $alertCount ?? 0;
?>
    <tr class="<?php echo !empty($criticalStatuses) ? 'critical' : ''; ?>">
        <td><?php echo htmlspecialchars($row['Num']); ?></td>
        <td><?php echo htmlspecialchars($row['IDNumber']); ?></td>
        <td><?php echo htmlspecialchars($row['FirstName']); ?></td>
        <td><?php echo htmlspecialchars($row['LastName']); ?></td>
        <td><?php echo htmlspecialchars($row['Temperature']); ?></td>
        <td><?php echo htmlspecialchars($row['BloodPressure']); ?></td>
        <td><?php echo htmlspecialchars($row['HeartRate']); ?></td>
        <td><?php echo htmlspecialchars($row['RespiratoryRate']); ?></td>
        <td>
            <?php if (!empty($criticalStatuses)): ?>
                <span class="critical-status">
                    <strong>Critical Status: </strong>
                    <?php echo implode(', ', $criticalStatuses); ?> detected!
                </span>
            <?php else: ?>
                <span>No Critical Status</span>
            <?php endif; ?>
        </td>
        <td>
            <!-- Display the alert count for this specific ID number -->
            <span> <?php echo $alertCount; ?></span>
        </td>
        <td>
            <div class="button-container">
                <form method="POST">
                    <button class="alert-btn" id="alertButton" type="submit" name="faculty_id" value="<?php echo htmlspecialchars($row['Num']); ?>">Send Alert</button>
                </form>
            </div>
        </td>
    </tr>
<?php } // End of while loop ?>
    </tbody>
</table>


   

            <script>
                function deleteRecord(id) {
                    if (confirm("Are you sure you want to delete this record?")) {
                        window.location.href = 'delete_faculty.php?id=' + id; // Redirect to delete script
                    }
                }

                function searchTable() {
                    const input = document.getElementById('searchInput');
                    const filter = input.value.toLowerCase();
                    const table = document.getElementById('facultyTable');
                    const tr = table.getElementsByTagName('tr');
                    
                    for (let i = 1; i < tr.length; i++) { // Skip the header row
                        let rowVisible = false;
                        const td = tr[i].getElementsByTagName('td');
                        
                        for (let j = 0; j < td.length; j++) {
                            if (td[j]) {
                                const textValue = td[j].textContent || td[j].innerText;
                                if (textValue.toLowerCase().indexOf(filter) > -1) {
                                    rowVisible = true;
                                    break;
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
        </div>
    </div>
</body>
</html>