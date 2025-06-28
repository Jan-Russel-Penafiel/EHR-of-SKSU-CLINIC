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
        header("Location: display_faculty.php");
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            margin: 0;
            padding: 15px;
            background-image: url('image.jpeg');
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
            background-color: rgba(173, 216, 230, 0.8);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 0px;
            max-height: 950px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-top: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 8px;
        }

        select,
        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        textarea,
        button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: white;
            font-size: 16px;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        #backButton {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
            margin: 0 0 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 35px;
            margin-bottom: -20px;
        }

        #backButton:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        #backButton:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

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
    <h2>ADD PERSONNEL RECORDS</h2>
    <form method="POST" action="insert_faculty.php?IDNumber=<?php echo htmlspecialchars($idNumber); ?>">
        <input type="hidden" name="IDNumber" value="<?php echo htmlspecialchars($idNumber); ?>">
        <input type="hidden" name="Rank" value="<?php echo htmlspecialchars($facultyDetails['Rank']); ?>">
        <input type="hidden" name="FirstName" value="<?php echo htmlspecialchars($facultyDetails['FirstName']); ?>">
        <input type="hidden" name="LastName" value="<?php echo htmlspecialchars($facultyDetails['LastName']); ?>">
        <input type="hidden" name="GmailAccount" value="<?php echo htmlspecialchars($facultyDetails['GmailAccount']); ?>">
        <input type="hidden" name="Department" value="<?php echo htmlspecialchars($facultyDetails['Department']); ?>">
        <input type="hidden" name="Position" value="<?php echo htmlspecialchars($facultyDetails['Position']); ?>">

        <label for="Complains">Complaints:</label>
        <textarea id="Complains" name="Complains" rows="4" placeholder="Enter any complaints or symptoms here..."></textarea>

        <label for="Temperature">Temperature (°C):</label>
        <input type="number" id="Temperature" name="Temperature" step="0.1" required placeholder="e.g., 36.5">

        <label for="BloodPressure">Blood Pressure (Systolic/Diastolic):</label>
        <input type="text" id="BloodPressure" name="BloodPressure" required placeholder="e.g., 120/80">

        <label for="HeartRate">Heart Rate (bpm):</label>
        <input type="number" id="HeartRate" name="HeartRate" required placeholder="e.g., 75">

        <label for="RespiratoryRate">Respiratory Rate (bpm):</label>
        <input type="number" id="RespiratoryRate" name="RespiratoryRate" required placeholder="e.g., 16">

        <label for="Height">Height (cm):</label>
        <input type="number" id="Height" name="Height" required placeholder="e.g., 175">

        <label for="Weight">Weight (kg):</label>
        <input type="number" id="Weight" name="Weight" required placeholder="e.g., 70">

        <label for="AppointmentDate">Appointment Date:</label>
        <input type="datetime-local" id="AppointmentDate" name="AppointmentDate" required>

        <button type="submit">Submit</button>
    </form>
</div>
</body>
</html>
