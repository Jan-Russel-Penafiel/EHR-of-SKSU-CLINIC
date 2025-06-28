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
            header("Location: search.php?temp=$temperature&systolic=$systolic&diastolic=$diastolic");
            exit();
        }

        // Renumber entries after insertion
        mysqli_query($conn, "SET @num = 0;");
        mysqli_query($conn, "UPDATE illmed SET Num = (@num := @num + 1) ORDER BY Num ASC;");

        // Redirect to display page
        header("Location: search.php?IDNumber=$idNumber");
        exit();
    } else {
        echo "Error inserting record: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medical Record</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --accent-color: #3498db;
            --text-color: #2c3e50;
            --text-light: #7f8c8d;
            --background-color: #f5f6fa;
            --error-color: #e74c3c;
            --border-radius: 8px;
            --box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 2rem;
            text-align: center;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
        }

        header h1 {
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .form-card {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .form-section {
            border-bottom: 1px solid #eee;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .section-title {
            color: var(--accent-color);
            font-size: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        select,
        textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="datetime-local"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background-color: var(--text-light);
            color: white;
        }

        .btn-accent {
            background-color: var(--accent-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-content {
            background-color: #fff;
            margin: 2% auto;
            padding: 2rem;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 800px;
            position: relative;
            animation: slideIn 0.3s ease-in-out;
        }

        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            color: var(--text-light);
            cursor: pointer;
            transition: var(--transition);
        }

        .close:hover {
            color: var(--error-color);
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .inventory-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        .inventory-table tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-low {
            background-color: #ffe0e0;
            color: #d63031;
        }

        .status-ok {
            background-color: #e0ffe0;
            color: #27ae60;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            header {
                border-radius: 0;
                margin-bottom: 1rem;
            }

            .container {
                padding: 0.5rem;
            }

            .form-card {
                padding: 1rem;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
                padding: 1rem;
            }

            .inventory-table {
                font-size: 0.875rem;
            }

            .inventory-table th,
            .inventory-table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1><i class="fas fa-file-medical"></i> Add Medical Record</h1>
    </header>

    <div class="container">
        <div class="form-card">
            <div class="button-group" style="margin-top: 0; margin-bottom: 1rem;">
                <button type="button" id="viewInventoryBtn" class="btn btn-accent">
                    <i class="fas fa-box-open"></i> View Medicine Inventory
                </button>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="Num" value="<?php echo isset($num) ? $num : ''; ?>">
                
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h3>
                    <div class="form-group">
                        <label for="IDNumber">ID Number:</label>
                        <input type="text" name="IDNumber" value="<?php echo htmlspecialchars($idNumber); ?>" readonly>
                    </div>

                    <div class="form-group">
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

                    <div class="form-group" id="OtherIllnessGroup" style="display:none;">
                        <label for="OtherIllness">Specify Illness:</label>
                        <textarea name="OtherIllness" id="OtherIllness" placeholder="Enter specific illness..."></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-pills"></i> Medication Details</h3>
                    <div class="form-row">
                        <div class="form-group">
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

                        <div class="form-group">
                            <label for="StockQuantity">Quantity:</label>
                            <input type="number" name="StockQuantity" id="StockQuantity" required min="1" placeholder="Enter quantity">
                        </div>
                    </div>

                    <div class="form-group" id="OtherMedicationGroup" style="display:none;">
                        <label for="OtherMedication">Specify Medication:</label>
                        <textarea name="OtherMedication" id="OtherMedication" placeholder="Enter specific medication..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="Prescription">Prescription Details:</label>
                        <textarea name="Prescription" required placeholder="Enter prescription details (e.g., dosage, frequency)"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-heartbeat"></i> Vital Signs</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="Temperature">Temperature (°C):</label>
                            <input type="number" name="Temperature" step="0.1" required placeholder="Enter temperature">
                        </div>

                        <div class="form-group">
                            <label for="BloodPressure">Blood Pressure:</label>
                            <input type="text" name="BloodPressure" required placeholder="e.g., 120/80">
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Record
                    </button>
                    <a href="search.php?IDNumber=<?php echo htmlspecialchars($idNumber); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Search
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Modal -->
    <div id="inventoryModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="section-title"><i class="fas fa-box-open"></i> Medicine Inventory</h2>
            <table class="inventory-table">
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
                    <?php
                    // Fetch inventory data
                    $query = "SELECT Num, MedName, SupplierName, StockQuantity FROM inventory ORDER BY Num";
                    $result = mysqli_query($conn, $query);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $status = ($row['StockQuantity'] <= 10) ? 'Low Stock' : 'In Stock';
                        $statusClass = ($row['StockQuantity'] <= 10) ? 'status-low' : 'status-ok';
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Num']); ?></td>
                            <td><?php echo htmlspecialchars($row['MedName']); ?></td>
                            <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                            <td><?php echo htmlspecialchars($row['StockQuantity']); ?></td>
                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById("inventoryModal");
        const btn = document.getElementById("viewInventoryBtn");
        const span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Show/hide other illness field
        function showOtherIllness() {
            const illnessSelect = document.getElementById("IllName");
            const otherIllnessGroup = document.getElementById("OtherIllnessGroup");
            otherIllnessGroup.style.display = illnessSelect.value === "Other" ? "block" : "none";
        }

        // Show/hide other medication field
        function showOtherMedication() {
            const medicationSelect = document.getElementById("MedName");
            const otherMedicationGroup = document.getElementById("OtherMedicationGroup");
            otherMedicationGroup.style.display = medicationSelect.value === "Other" ? "block" : "none";
        }
    </script>
</body>
</html>