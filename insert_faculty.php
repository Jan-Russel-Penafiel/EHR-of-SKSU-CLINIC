<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Retrieve the IDNumber from the URL if available
$idNumber = isset($_GET['IDNumber']) ? mysqli_real_escape_string($conn, $_GET['IDNumber']) : '';

// Check if the IDNumber is provided and not empty
if (empty($idNumber)) {
    die("Error: IDNumber is not set or is empty.");
}

// Fetch additional details for hidden fields (if available)
$query = "SELECT Rank, FirstName, LastName, GmailAccount, Department, Position FROM faculty WHERE IDNumber = '$idNumber' LIMIT 1";
$result = mysqli_query($conn, $query);

$facultyDetails = [
    'Rank' => '',
    'FirstName' => '',
    'LastName' => '',
    'GmailAccount' => '',
    'Department' => '',
    'Position' => ''
];

if (mysqli_num_rows($result) > 0) {
    $facultyDetails = mysqli_fetch_assoc($result);
}

// Handle form submission for new records
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize each input field
    $rank = isset($_POST['Rank']) ? mysqli_real_escape_string($conn, $_POST['Rank']) : '';
    $firstName = isset($_POST['FirstName']) ? mysqli_real_escape_string($conn, $_POST['FirstName']) : '';
    $lastName = isset($_POST['LastName']) ? mysqli_real_escape_string($conn, $_POST['LastName']) : '';
    $gmailAccount = isset($_POST['GmailAccount']) ? mysqli_real_escape_string($conn, $_POST['GmailAccount']) : '';
    $department = isset($_POST['Department']) ? mysqli_real_escape_string($conn, $_POST['Department']) : '';
    $position = isset($_POST['Position']) ? mysqli_real_escape_string($conn, $_POST['Position']) : '';
    $complains = isset($_POST['Complains']) ? mysqli_real_escape_string($conn, $_POST['Complains']) : '';

    $temperature = isset($_POST['Temperature']) ? mysqli_real_escape_string($conn, $_POST['Temperature']) : '';
    $bloodPressure = isset($_POST['BloodPressure']) ? mysqli_real_escape_string($conn, $_POST['BloodPressure']) : '';
    $heartRate = isset($_POST['HeartRate']) ? mysqli_real_escape_string($conn, $_POST['HeartRate']) : '';
    $respiratoryRate = isset($_POST['RespiratoryRate']) ? mysqli_real_escape_string($conn, $_POST['RespiratoryRate']) : '';
    $height = isset($_POST['Height']) ? mysqli_real_escape_string($conn, $_POST['Height']) : '';
    $weight = isset($_POST['Weight']) ? mysqli_real_escape_string($conn, $_POST['Weight']) : '';
    $appointmentDate = isset($_POST['AppointmentDate']) ? mysqli_real_escape_string($conn, $_POST['AppointmentDate']) : '';

    // Insert a new record
    $sql = "INSERT INTO faculty (IDNumber, Rank, FirstName, LastName, GmailAccount, Department, Position, Complains, Temperature, BloodPressure, HeartRate, RespiratoryRate, Height, Weight, AppointmentDate) 
            VALUES ('$idNumber', '$rank', '$firstName', '$lastName', '$gmailAccount', '$department', '$position', '$complains', '$temperature', '$bloodPressure', '$heartRate', '$respiratoryRate', '$height', '$weight', '$appointmentDate')";

    if (mysqli_query($conn, $sql)) {
        // Redirect to display page after successful insertion
        header("Location: search.php");
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
    <title>Add Faculty Record</title>
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

        .form-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
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
        input[type="email"],
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
        input[type="email"]:focus,
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

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn:active {
            transform: translateY(0);
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
        }
    </style>
</head>
<body>
    <header>
        <h1><i class="fas fa-user-md"></i> Add Faculty </h1>
    </header>

    <div class="container">
        <div class="form-card">
            <form method="POST" action="insert_faculty.php?IDNumber=<?php echo htmlspecialchars($idNumber); ?>">
                <input type="hidden" name="IDNumber" value="<?php echo htmlspecialchars($idNumber); ?>">
                <input type="hidden" name="Rank" value="<?php echo htmlspecialchars($facultyDetails['Rank']); ?>">
                <input type="hidden" name="FirstName" value="<?php echo htmlspecialchars($facultyDetails['FirstName']); ?>">
                <input type="hidden" name="LastName" value="<?php echo htmlspecialchars($facultyDetails['LastName']); ?>">
                <input type="hidden" name="GmailAccount" value="<?php echo htmlspecialchars($facultyDetails['GmailAccount']); ?>">
                <input type="hidden" name="Department" value="<?php echo htmlspecialchars($facultyDetails['Department']); ?>">
                <input type="hidden" name="Position" value="<?php echo htmlspecialchars($facultyDetails['Position']); ?>">

                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h3>
                    <div class="form-group">
                        <label for="IDNumber">ID Number:</label>
                        <input type="text" name="IDNumber" value="<?php echo htmlspecialchars($idNumber); ?>" readonly>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="FirstName">First Name:</label>
                            <input type="text" name="FirstName" value="<?php echo htmlspecialchars($facultyDetails['FirstName']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="LastName">Last Name:</label>
                            <input type="text" name="LastName" value="<?php echo htmlspecialchars($facultyDetails['LastName']); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="Department">Department:</label>
                            <input type="text" name="Department" value="<?php echo htmlspecialchars($facultyDetails['Department']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="Position">Position:</label>
                            <input type="text" name="Position" value="<?php echo htmlspecialchars($facultyDetails['Position']); ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-notes-medical"></i> Medical Information</h3>
                    <div class="form-group">
                        <label for="Complains">Complaints/Symptoms:</label>
                        <textarea name="Complains" required placeholder="Enter complaints or symptoms"></textarea>
                    </div>

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

                    <div class="form-row">
                        <div class="form-group">
                            <label for="HeartRate">Heart Rate (bpm):</label>
                            <input type="number" name="HeartRate" required placeholder="Enter heart rate">
                        </div>

                        <div class="form-group">
                            <label for="RespiratoryRate">Respiratory Rate:</label>
                            <input type="number" name="RespiratoryRate" required placeholder="Enter respiratory rate">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="Height">Height (cm):</label>
                            <input type="number" name="Height" step="0.1" required placeholder="Enter height">
                        </div>

                        <div class="form-group">
                            <label for="Weight">Weight (kg):</label>
                            <input type="number" name="Weight" step="0.1" required placeholder="Enter weight">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="AppointmentDate">Appointment Date:</label>
                        <input type="datetime-local" name="AppointmentDate" required>
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
</body>
</html>
