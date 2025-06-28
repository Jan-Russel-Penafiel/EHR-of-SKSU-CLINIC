<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Function to reduce stock quantity
function reduceStock($conn, $medName, $stockQuantity) {
    // Fetch current stock quantity for the medication
    $stockQuery = "SELECT StockQuantity FROM inventory WHERE MedName = ?";
    $stmt = mysqli_prepare($conn, $stockQuery);
    mysqli_stmt_bind_param($stmt, "s", $medName);
    mysqli_stmt_execute($stmt);
    $stockResult = mysqli_stmt_get_result($stmt);

    if ($stockResult && mysqli_num_rows($stockResult) > 0) {
        $stockRow = mysqli_fetch_assoc($stockResult);
        $currentStock = $stockRow['StockQuantity'];

        // Check if there's enough stock
        if ($currentStock >= $stockQuantity) {
            $newStock = $currentStock - $stockQuantity;
            // Update stock in inventory
            $updateStockQuery = "UPDATE inventory SET StockQuantity = ? WHERE MedName = ?";
            $updateStmt = mysqli_prepare($conn, $updateStockQuery);
            mysqli_stmt_bind_param($updateStmt, "is", $newStock, $medName);
            if (mysqli_stmt_execute($updateStmt)) {
                return true; // Stock successfully reduced
            } else {
                echo "Error updating stock: " . mysqli_error($conn);
                return false;
            }
        } else {
            // Not enough stock, redirect to display_inventory.php
            echo "Error: Not enough stock for $medName. Redirecting to inventory page...";
            header("Location: display_inventory.php");
            exit();
        }
    } else {
        echo "Error: Medication not found in inventory.";
        header("Location: insert_inventory.php");
        exit();
    }
}

// Function to add a new medication to inventory with default SupplierName and StockQuantity
function addNewMedicationToInventory($conn, $medName) {
    // Check if medication already exists in inventory
    $checkQuery = "SELECT * FROM inventory WHERE MedName = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "s", $medName);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        // Insert new medication with default stock quantity and SupplierName
        $supplierName = "SKSU MAIN CAMPUS";
        $defaultStockQuantity = 10; // Automatically set StockQuantity to 10
        $insertQuery = "INSERT INTO inventory (MedName, StockQuantity, SupplierName) VALUES (?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, "sis", $medName, $defaultStockQuantity, $supplierName);
        if (!mysqli_stmt_execute($insertStmt)) {
            echo "Error adding new medication to inventory: " . mysqli_error($conn);
            return false;
        }
    }
    return true;
}

// Main code to handle form submission
$idNumber = isset($_GET['IDNumber']) ? mysqli_real_escape_string($conn, $_GET['IDNumber']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $num = mysqli_real_escape_string($conn, $_POST['Num']);
    $illName = mysqli_real_escape_string($conn, $_POST['IllName']);
    $medName = mysqli_real_escape_string($conn, $_POST['MedName']);
    $temperature = mysqli_real_escape_string($conn, $_POST['Temperature']);
    $bloodPressure = mysqli_real_escape_string($conn, $_POST['BloodPressure']);
    $prescription = mysqli_real_escape_string($conn, $_POST['Prescription']);
    $stockQuantity = mysqli_real_escape_string($conn, $_POST['StockQuantity']); // Get Stock Quantity

    // Custom illness name if "Other" is selected
    if ($illName == "Other" && !empty($_POST['OtherIllness'])) {
        $illName = mysqli_real_escape_string($conn, $_POST['OtherIllness']);
    }

    // Custom medication name if "Other" is selected
    if ($medName == "Other" && !empty($_POST['OtherMedication'])) {
        $medName = mysqli_real_escape_string($conn, $_POST['OtherMedication']);

        // Add the new medication to inventory
        if (!addNewMedicationToInventory($conn, $medName)) {
            exit(); // Stop execution if medication addition fails
        }
    }

    // Check if IDNumber exists in personal_info
    $result = mysqli_query($conn, "SELECT * FROM personal_info WHERE IDNumber='$idNumber'");
    if (mysqli_num_rows($result) == 0) {
        die("Error: IDNumber does not exist in personal_info table.");
    }

    // Check stock before inserting into illmed (only if stock deduction is applicable)
    if (!reduceStock($conn, $medName, $stockQuantity)) {
        exit(); // Stop execution if stock reduction fails
    }

    // Insert form data into the illmed table
    $sql = "INSERT INTO illmed (Num, IllName, MedName, Temperature, BloodPressure, Prescription, IDNumber) 
            VALUES ('$num', '$illName', '$medName', '$temperature', '$bloodPressure', '$prescription', '$idNumber')";
    
    if (mysqli_query($conn, $sql)) {
        // Redirect if necessary conditions are met
        list($systolic, $diastolic) = explode('/', $bloodPressure);
        if ($temperature > 38 || $systolic > 140 || $diastolic > 90) {
            header("Location: alert.php?temp=$temperature&systolic=$systolic&diastolic=$diastolic");
            exit();
        }

        // Renumber entries after insertion
        mysqli_query($conn, "SET @num = 0;");
        mysqli_query($conn, "UPDATE illmed SET Num = (@num := @num + 1) ORDER BY Num ASC;");

        // Redirect to display page
        header("Location: display_illmed.php?IDNumber=$idNumber");
        exit();
    } else {
        echo "Error inserting record: " . mysqli_error($conn);
    }

    mysqli_close(mysql: $conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Treatment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Style for the textarea */
textarea {
    width: 96%; /* Full width */
    padding: 10px; /* Padding inside the textarea */
    border: 1px solid #ccc; /* Light grey border */
    border-radius: 8px; /* Rounded corners */
    font-size: 16px; /* Font size */
    font-family: Arial, sans-serif; /* Font family */
    resize: vertical; /* Allow only vertical resizing */
    transition: border-color 0.3s ease, box-shadow 0.3s ease; /* Smooth transition */
    outline: none; /* Remove the default outline */
    height: 10.2vh;
}

/* Focus state for textarea */
textarea:focus {
    border-color: #007bff; /* Blue border on focus */
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.3); /* Light blue shadow */
}



/* Responsive adjustments */
@media (max-width: 600px) {
    textarea {
        font-size: 14px; /* Smaller font on mobile */
        padding: 8px; /* Adjust padding on small screens */
    }
}

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            font-size: 18px; /* Adjust to suit the design */
            margin: 0;
            padding: 15px;
            background-image: url(image.jpeg);
            background-repeat: no-repeat;
            background-size: cover; /* Adjust for full cover */
            background-attachment: fixed;
        }

        .container {
            max-width: 600px;
            margin: 0 auto; /* Center the container */
            padding: 15px;
            background-color: rgba(173, 216, 230, 0.8); /* Light blue background with transparency */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: -10px;
           max-height: auto;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-top: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 0px;
        }

        select,
        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        
        button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            background-color: white;
            font-size: 16px;
        }

     

        select:focus {
            outline: none; /* Remove default outline on focus */
            border-color: #28a745; /* Change border color on focus */
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.5); /* Add shadow effect on focus */
            background-color: white;
        }

        button {
            background-color: #28a745; /* Green */
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838; /* Darker green */
        }

        /* Responsive styling */
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }

            select, input[type="text"], input[type="number"], input[type="datetime-local"], button {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>ADD STUDENT TREATMENT RECORDS</h2>
    <div class="form-group"><button id="myBtn" class="btn2">View Medicine Inventory</button>
    <form method="POST" action="insert_illmed.php?IDNumber=<?php echo htmlspecialchars($idNumber); ?>"> <!-- Pass IDNumber in action URL -->
        <input type="hidden" name="Num" value="<?php echo isset($Num) ? htmlspecialchars($Num) : ''; ?>">
        <input type="hidden" name="IDNumber" value="<?php echo htmlspecialchars($idNumber); ?>"> <!-- Use the retrieved IDNumber -->

       
            <label for="IllName">Illness Name:</label>
            <select name="IllName" id="IllName" required onchange="showOtherIllness();">
                <option value="">Select Illness</option>
                <option value="Flu">1. Flu</option>
                <option value="Colds">2. Colds</option>
                <option value="Headache">3. Headache</option>
                <option value="Cough">4. Cough</option>
                <option value="Allergy">5. Allergy</option>
                <option value="Hyper Acidity">6. Hyper Acidity</option>
                <option value="Diarrhea">7. Diarrhea</option>
                <option value="Stomachache (Hypogastric)">8. Stomachache (Hypogastric)</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <!-- Text area for specific illness when 'Other' is selected -->
        <div class="form-group" id="OtherIllnessGroup" style="display:none;">
            <label for="OtherIllness">Specify Illness:</label>
            <textarea name="OtherIllness" id="OtherIllness" placeholder="Enter specific illness..." rows="3"></textarea>
        </div>

        <div class="form-group" style="display: flex; align-items: flex-start;">
    <!-- Medication Select on the left -->
    <div style="flex: 3; margin-right: 20px;">
        <label for="MedName">Medication Name:</label>
        <select name="MedName" id="MedName" required onchange="showOtherMedication();">
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

    <!-- New Stock Quantity Field on the right -->
    <div style="flex: 1;">
        <label for="StockQuantity">Quantity:</label>
        <input type="text" name="StockQuantity" id="StockQuantity" placeholder="Enter quantity" required />
    </div>
</div>


        <!-- Text area for specific medication when 'Other' is selected -->
        <div class="form-group" id="OtherMedicationGroup" style="display:none;">
            <label for="OtherMedication">Specify Medication:</label>
            <textarea name="OtherMedication" id="OtherMedication" placeholder="Enter specific medication..." rows="3"></textarea>
        </div>

        <div class="form-group">
            <label for="Prescription">Prescription:</label>
            <textarea name="Prescription" id="Prescription" rows="4" placeholder="Enter prescription (e.g., take 2 tablets after meals)"></textarea>
        </div>

        <div class="form-group">
            <label for="Temperature">Temperature:</label>
            <input type="number" name="Temperature" id="Temperature" step="0.1" required placeholder="Enter Temperature (°C)">
        </div>

        <div class="form-group">
            <label for="BloodPressure">Blood Pressure:</label>
            <input type="text" name="BloodPressure" id="BloodPressure" placeholder="Enter Blood Pressure (Systolic/Diastolic)">
        </div>

        <button type="submit" class="btn btn-primary">Submit Record</button>
    </form>
</div>

<?php



// Fetch all items from the inventory
$query = "SELECT Num, MedName, SupplierName, StockQuantity FROM inventory ORDER BY Num";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo "Error fetching inventory: " . mysqli_error($conn);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <title>Medicine Inventory - Modal</title>
    <style>
     /* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 9999; /* Ensure modal is on top of other elements */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6); /* Darker background for better focus */
    animation: fadeIn 0.3s ease-in-out; /* Smooth fade-in effect */
}
h2 {
    text-transform: uppercase; /* Make text in header uppercase */
}
/* Modal Content */
.modal-content {
    background-color: #fff;
    margin: 1% auto;
    padding: 30px;
    border-radius: 10px; /* Rounded corners */
    width: 90%;
    max-width: 600px; /* Limit the max width */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    animation: slideUp 0.3s ease-in-out; /* Smooth slide-up effect */
}

/* Close Button */
.close {
    color: #555;
    font-size: 32px;
    font-weight: bold;
    position: absolute; /* Position it at the top right */
    margin-left: 580px;
 

    cursor: pointer;
    transition: color 0.3s ease-in-out; /* Smooth transition for hover */
}

/* Close Button Hover */
.close:hover,
.close:focus {
    color: #ff0000; /* Red color for hover/focus */
    text-decoration: none;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

/* Table Header */
th, td {
    padding: 15px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

/* Table Header Styles */
th {
    background-color: #4CAF50; /* A more vibrant green */
    color: white;
    font-weight: bold;
   
}

/* Table Row Hover */
tr:hover {
    background-color: #f1f1f1; /* Light gray background on hover */
}

/* Responsive Design */
@media screen and (max-width: 600px) {
    .modal-content {
        width: 95%; /* Adjust modal width on small screens */
        padding: 15px; /* Reduce padding for smaller screens */
    }

    th, td {
        font-size: 14px; /* Smaller font size for smaller screens */
    }

    .close {
        font-size: 28px; /* Smaller close button on small screens */
    }
}

/* Animations */
@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

@keyframes slideUp {
    0% {
        transform: translateY(-20px);
    }
    100% {
        transform: translateY(0);
    }
}

    </style>
</head>
<body>

<!-- Button to open the modal -->


<!-- Modal Structure -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Medicine Inventory </h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Medication Name</th>
                    <th>Supplier</th>
                    <th>Stock Quantity</th>
                    <th>Status</th>
                   
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) {
                    $status = ($row['StockQuantity'] <= 10) ? 'Low Stock' : 'In Stock';  // Set low stock based on your requirement
                    $statusClass = ($row['StockQuantity'] <= 10) ? 'low-stock' : 'normal-stock';  // Adjust for low stock styling
                ?>
                <tr id="row-<?php echo $row['Num']; ?>" class="<?php echo $statusClass; ?>">
                    <td><?php echo $row['Num']; ?></td>
                    <td><?php echo $row['MedName']; ?></td>
                    <td><?php echo $row['SupplierName']; ?></td>
                    <td><?php echo $row['StockQuantity']; ?></td>
                    <td><?php echo $status; ?></td>
                    <td>
                      
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Your existing form content or other HTML elements can go here -->

<!-- JavaScript to handle modal functionality -->
<script>
    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the button that opens the modal
    var btn = document.getElementById("myBtn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Handle medicine selection
    var selectButtons = document.querySelectorAll(".select-btn");
    selectButtons.forEach(function(btn) {
        btn.onclick = function() {
            var medNum = this.getAttribute("data-num");
            var medName = this.getAttribute("data-name");

            // You can now pass these values to a hidden field or somewhere else in your form
            // Example: 
            // document.getElementById("medicine_id").value = medNum;
            // document.getElementById("medicine_name").value = medName;

            alert("Medicine selected: " + medName + " (ID: " + medNum + ")");
            modal.style.display = "none"; // Close the modal after selection
        }
    });
</script>

</body>
</html>

<?php
mysqli_close($conn);
?>


<script>
// Function to display the text area for "Other" illness
function showOtherIllness() {
    var illnessSelect = document.getElementById("IllName");
    var otherIllnessGroup = document.getElementById("OtherIllnessGroup");
    if (illnessSelect.value === "Other") {
        otherIllnessGroup.style.display = "block";
    } else {
        otherIllnessGroup.style.display = "none";
    }
}

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