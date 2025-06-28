<?php
// Database connection
$host = 'localhost'; // Change if necessary
$dbname = 'ehrdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to update complaints and medication in faculty and inventory
function updateComplaintAndMedication($idNumber, $complains, $medication) {
    global $pdo;

    // Update the faculty table with the complaint and medication
    $sql = "UPDATE faculty SET Complains = :complains, MedName = :MedName WHERE IDNumber = :idNumber";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':complains', $complains, PDO::PARAM_STR);
    $stmt->bindParam(':MedName', $medication, PDO::PARAM_STR);
    $stmt->bindParam(':idNumber', $idNumber, PDO::PARAM_STR);
    $result = $stmt->execute();

    // If the update to faculty table is successful, update the inventory
    if ($result) {
        // Reduce the quantity of the medication in the inventory
        $sql_inventory = "UPDATE inventory SET StockQuantity = StockQuantity - 1 WHERE MedName = :MedName";
        $stmt_inventory = $pdo->prepare($sql_inventory);
        $stmt_inventory->bindParam(':MedName', $medication, PDO::PARAM_STR);

        return $stmt_inventory->execute();
    }

    return false;
}

// Function to retrieve faculty members
function getFacultyMembers($searchTerm = '') {  // Default value for $searchTerm
    global $pdo;
    
    // Modify the SQL query to handle the possibility of an empty searchTerm
    $sql = "SELECT Num ,IDNumber, FirstName, LastName , Complains , MedName FROM faculty WHERE IDNumber LIKE :searchTerm LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':searchTerm', "%" . $searchTerm . "%", PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idNumber = $_POST['IDNumber'];
    $complains = $_POST['Complains'];
    $medication = isset($_POST['MedName']) ? $_POST['MedName'] : ''; // Fallback if Medication is not set

    // Check if the user selected "Other" for Medication
    if ($medication == "Other" && !empty($_POST['OtherMedication'])) {
        $medication = $_POST['OtherMedication']; // Use the custom medication value
    }

    // Validate input
    if (!empty($idNumber) && !empty($complains) && !empty($medication)) {
        // Update complaint and medication in the database and update inventory
        if (updateComplaintAndMedication($idNumber, $complains, $medication)) {
            echo "<p>Complaint and medication recorded successfully. Inventory updated.</p>";
        } else {
            echo "<p>Failed to record the complaint and medication, or update the inventory.</p>";
        }
    } else {
        echo "<p>Please fill in all required fields.</p>";
    }
}

// Handle search request
if (isset($_GET['searchTerm'])) {
    $searchTerm = $_GET['searchTerm'];
    $facultyMembers = getFacultyMembers($searchTerm); // Pass search term to the function
    echo json_encode($facultyMembers);
    exit;
}

// Retrieve faculty records for displaying (no search term provided initially)
$facultyMembers = getFacultyMembers();  // Call function without searchTerm
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Complaints and Medication</title>
    <style>
  /* Global Styles */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f7f8fa;
    margin: 0;
    padding: 0;
    color: #333;
    line-height: 1.6;
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

h1 {
    margin-bottom: 5px;
    color: white; /* Keeping the green theme */
    font-size: 40px; /* Larger font for headings */
    border-bottom: 0px solid #4CAF50; /* Underline for emphasis */
    padding-bottom: 0px; /* Spacing below the heading */
}
h2, h3 {
    color: blue;
    text-align: center;
    margin: 20px 0;
    font-weight: 600;
    font-size: 28px;
}

h3 {
    font-size: 24px;
}

/* Form Styling */
form {
    width: 80%;
    max-width: 650px;
    margin: 40px auto;
    background-color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    font-size: 16px;
}

label {
    font-size: 16px;
    color: #333;
    margin-bottom: 8px;
    display: inline-block;
    font-weight: 500;
}

input, textarea, select {
    width: 100%;
    padding: 14px;
    margin-bottom: 18px;
    border-radius: 8px;
    border: 1px solid #ddd;
    font-size: 16px;
    color: #333;
    box-sizing: border-box;
    transition: border 0.3s ease;
}

input:focus, textarea:focus, select:focus {
    border-color: #2a3b8b;
    outline: none;
}

input[required], textarea[required], select[required] {
    border-color: #e74c3c;
}

/* Submit Button */
input[type="submit"] {
    background-color: #2a3b8b;
    color: #fff;
    border: none;
    padding: 15px;
    font-size: 18px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    width: 100%;
    font-weight: 600;
}

input[type="submit"]:hover {
    background-color: #1a2b6b;
    transform: translateY(-2px);
}

/* Show/hide Other Medication text area */
#OtherMedicationGroup {
    display: none;
}

textarea#OtherMedication {
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px;
    font-size: 16px;
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
/* Responsive Design */
@media (max-width: 768px) {
    table {
        width: 100%;
        font-size: 14px;
    }

    form {
        width: 95%;
    }

    h2, h3 {
        font-size: 24px;
    }

    input, textarea, select {
        padding: 12px;
        font-size: 14px;
    }

    input[type="submit"] {
        font-size: 16px;
    }
}

/* Message Styling */
.message {
    margin: 20px 0;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    font-weight: 500;
    font-size: 16px;
}

.message.success {
    background-color: #28a745;
    color: white;
}

.message.error {
    background-color: #e74c3c;
    color: white;
}

/* Suggestions Box */
.suggestions {
    border: 1px solid #ccc;
    max-height: 150px;
    overflow-y: auto;
    position: absolute;
    background: white;
    width: 100%;
    z-index: 1000;
    border-radius: 6px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.suggestion-item {
    padding: 10px;
    cursor: pointer;
    font-size: 16px;
    color: #333;
}

.suggestion-item:hover {
    background-color: #f0f0f0;
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
            width: 3%;
            margin-bottom: -50px;
            margin-top: -30px;
            margin-left: -515px;
        }

        #backButton:hover {
            background-color: green; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        #backButton:active {
            transform: translateY(0); /* Reset lift effect on click */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Reduce shadow on click */
        }
        body {
    background-image: url(image.jpeg);
    background-repeat: no-repeat;
    background-size: 100% 100%;
    background-attachment: fixed;
    
}
.main-content { 
    background-color: rgba(173, 216, 230, 0.50); /* Light blue background with 50% transparency */
    border-radius: 8px;
    padding: 0px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 18px;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    text-align: center;
    height: auto; /* Set a fixed height */
   
}
.container {
    margin-left: 0; /* Adjusted for new sidebar width */
    padding: 15px; /* Reduced padding */
    transition: margin-left 0.3s;
}
    </style>
</head>
<body>
<div class="container">
    <div class="main-content">
<header>
        <h1>COMPLAINTS AND MEDICATION RECORDS</h1>
        </header>
           <a id="backButton" href="display_faculty.php">Back</a>
        <button id="openModalBtn" class="btn">Add/Edit Records</button>
       
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ID Number</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Complaints</th>
                <th>Medication</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($facultyMembers as $faculty) {
                echo "<tr>";
                echo "<td>{$faculty['Num']}</td>";
                echo "<td>{$faculty['IDNumber']}</td>";
                echo "<td>{$faculty['FirstName']}</td>";
                echo "<td>{$faculty['LastName']}</td>";
                echo "<td>{$faculty['Complains']}</td>";
                echo "<td>{$faculty['MedName']}</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
   </div>
   </div>
<!-- The Modal -->
<div id="complaintModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>

        <!-- Complaint and Medication form -->
        <form action="complains.php" method="POST" id="complaintForm">
            <label for="IDNumber">Personnel ID Number:</label>
            <input type="text" name="IDNumber" id="IDNumber" required placeholder="Enter ID Number" autocomplete="off" class="input-field"><br>
            <div id="suggestions-container"></div> <!-- Container for suggestions -->

            <label for="Complains">Complaint:</label><br>
            <textarea name="Complains" id="Complains" rows="3" cols="50" required placeholder="Describe the complaint..." class="input-field"></textarea><br>

            <div class="form-group">
                <label for="MedName">Medication Name:</label>
                <select name="MedName" id="MedName" required onchange="showOtherMedication();" class="input-field">
                    <option value="">Select Medication</option>
                    <option value="Paracetamol">1. Paracetamol</option>
                    <option value="Ibuprofen">2. Ibuprofen</option>
                    <option value="Mefenamic">3. Mefenamic</option>
                    <option value="Lagundi">4. Lagundi</option>
                    <option value="Ceterizine">5. Ceterizine</option>
                    <option value="Antacid">6. Antacid</option>
                    <option value="Loperamide">7. Loperamide</option>
                    <option value="Warm Compress">8. Warm Compress</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Text area for specific medication when 'Other' is selected -->
            <div class="form-group" id="OtherMedicationGroup" style="display:none;">
                <label for="OtherMedication">Specify Medication:</label>
                <textarea name="OtherMedication" id="OtherMedication" placeholder="Enter specific medication..." rows="2" class="input-field"></textarea>
            </div>

            <button type="submit" class="btn1">Submit</button>
        </form>
    </div>
</div>

<script>
// Get the modal and button elements
var modal = document.getElementById("complaintModal");
var btn = document.getElementById("openModalBtn");
var span = document.getElementsByClassName("close")[0];

// Open the modal when the button is clicked
btn.onclick = function() {
    modal.style.display = "block";
    document.body.style.overflow = "hidden";  // Prevent scrolling when modal is open
}

// Close the modal when the 'x' is clicked
span.onclick = function() {
    modal.style.display = "none";
    document.body.style.overflow = "auto";  // Restore scrolling when modal is closed
}

// Close the modal when clicking anywhere outside of it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
        document.body.style.overflow = "auto";
    }
}

// Show the 'Other' medication field if selected
function showOtherMedication() {
    var medName = document.getElementById("MedName").value;
    var otherMedicationGroup = document.getElementById("OtherMedicationGroup");
    if (medName === "Other") {
        otherMedicationGroup.style.display = "block";
    } else {
        otherMedicationGroup.style.display = "none";
    }
}
</script>

<style>
/* Modal Styling */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
    padding-top: 5px;

   
}

.modal-content {
    background-color: rgba(173, 216, 230, 0.50); /* Light blue background with 50% transparency */
    margin: 0% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 60%;
    max-width: 400px;  /* Smaller width for compact size */
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    transform: translateY(-50px);
   
    margin-top: 60px;
}

/* Modal Open Animation */
@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(-50px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.close {
    color: #888;
    font-size: 24px;  /* Smaller size for close button */
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s;
    position: absolute; right: 10px; top: 10px;
}

.close:hover,
.close:focus {
    color: #333;
}

.input-field {
    width: 100%;
    padding: 8px;
    margin-bottom: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;  /* Smaller text */
    transition: border-color 0.3s ease;
}

.input-field:focus {
    border-color: grey;
    outline: none;
}

.btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 14px;  /* Adjusted padding for smaller size */
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;  /* Smaller button text */
    width: 20%;
    transition: background-color 0.3s ease;
    margin-top: 10px;
    margin-left: 450px;
}

.btn:hover {
    background-color: #45a049;
}
.btn1 {
    background-color: #4CAF50;
    color: white;
    padding: 10px 14px;  /* Adjusted padding for smaller size */
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;  /* Smaller button text */
    width: 30%;
    transition: background-color 0.3s ease;
    margin-top: 10px;

}

.btn1:hover {
    background-color: #45a049;
}


/* Accessibility Improvements */
input:focus,
textarea:focus,
button:focus {
    outline: 3px solid #66afe9;
}

/* Responsive Design */
@media screen and (max-width: 600px) {
    .modal-content {
        width: 90%;
        padding: 16px;
    }
    
    .btn {
        width: 100%;
    }
}
</style>



    <script>
        // Function to fetch suggestions based on ID Number input
        document.getElementById('IDNumber').addEventListener('input', function() {
            let searchTerm = this.value;

            if (searchTerm.length > 2) { // Start searching after 3 characters
                fetch('complains.php?searchTerm=' + searchTerm)
                    .then(response => response.json())
                    .then(data => {
                        let suggestionsContainer = document.getElementById('suggestions-container');
                        suggestionsContainer.innerHTML = ''; // Clear existing suggestions

                        if (data.length > 0) {
                            data.forEach(faculty => {
                                let div = document.createElement('div');
                                div.classList.add('suggestion-item');
                                div.textContent = faculty.IDNumber + ' - ' + faculty.FirstName + ' ' + faculty.LastName;
                                div.addEventListener('click', function() {
                                    document.getElementById('IDNumber').value = faculty.IDNumber; // Set the selected IDNumber
                                    suggestionsContainer.innerHTML = ''; // Clear suggestions after selection
                                });
                                suggestionsContainer.appendChild(div);
                            });
                        }
                    });
            } else {
                document.getElementById('suggestions-container').innerHTML = ''; // Clear suggestions if input is less than 3 chars
            }
        });

        // Function to display the text area for "Other" medication
        function showOtherMedication() {
            var medicationSelect = document.getElementById("MedName");
            var otherMedicationGroup = document.getElementById("OtherMedicationGroup");
            if (medicationSelect.value === "Other") {
                otherMedicationGroup.style.display = "block";
            } else {
                otherMedicationGroup.style.display = "none";
            }
        }
    </script>
</body>
</html>
