<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

$idNumber = isset($_GET['IDNumber']) ? mysqli_real_escape_string($conn, $_GET['IDNumber']) : '';

// If the IDNumber is not set or is empty, show an error message
if (empty($idNumber)) {
    die("Error: IDNumber is not set or is empty.");
}

$faculty = [
    'IDNumber' => '',
    'Temperature' => '',
    'BloodPressure' => '',
    'HeartRate' => '',
    'RespiratoryRate' => '',
    'Height' => '',
    'Weight' => '',
    'AppointmentDate' => '',
    'Complains' => ''
];

// Check if record already exists
$query = "SELECT * FROM faculty WHERE IDNumber = '$idNumber'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $faculty = mysqli_fetch_assoc($result);
} else {
    echo "Faculty record not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize each input field
    $temperature = isset($_POST['Temperature']) && $_POST['Temperature'] !== '' ? mysqli_real_escape_string($conn, $_POST['Temperature']) : '';
    $bloodPressure = isset($_POST['BloodPressure']) && $_POST['BloodPressure'] !== '' ? mysqli_real_escape_string($conn, $_POST['BloodPressure']) : '';
    $heartRate = isset($_POST['HeartRate']) && $_POST['HeartRate'] !== '' ? mysqli_real_escape_string($conn, $_POST['HeartRate']) : '';
    $respiratoryRate = isset($_POST['RespiratoryRate']) && $_POST['RespiratoryRate'] !== '' ? mysqli_real_escape_string($conn, $_POST['RespiratoryRate']) : '';
    $height = isset($_POST['Height']) && $_POST['Height'] !== '' ? mysqli_real_escape_string($conn, $_POST['Height']) : '';
    $weight = isset($_POST['Weight']) && $_POST['Weight'] !== '' ? mysqli_real_escape_string($conn, $_POST['Weight']) : '';
    $appointmentDate = isset($_POST['AppointmentDate']) && $_POST['AppointmentDate'] !== '' ? mysqli_real_escape_string($conn, $_POST['AppointmentDate']) : '';
    $complains = isset($_POST['Complains']) && $_POST['Complains'] !== '' ? mysqli_real_escape_string($conn, $_POST['Complains']) : '';

    // If BloodPressure is blank, set both systolic and diastolic as blank
    list($systolic, $diastolic) = $bloodPressure !== '' ? explode('/', $bloodPressure) : ['', ''];

    // Update faculty record
    $sql = "UPDATE faculty SET 
            Temperature = '$temperature', 
            BloodPressure = '$bloodPressure', 
            HeartRate = '$heartRate', 
            RespiratoryRate = '$respiratoryRate', 
            Height = '$height', 
            Weight = '$weight', 
            AppointmentDate = '$appointmentDate', 
            Complains = '$complains'
            WHERE IDNumber = '$idNumber'";

    if (mysqli_query($conn, $sql)) {
        // Redirect with alerts if temperature or blood pressure exceeds limits
        if (($temperature > 38 || $systolic > 140 || $diastolic > 90) && $bloodPressure !== '') {
            header("Location: alert_faculty.php?temp=$temperature&systolic=$systolic&diastolic=$diastolic");
            exit();
        }
        header("Location: display_faculty.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Record Management</title>
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
        <h2>UPDATE PERSONNEL RECORDS</h2>
        <form method="POST" action="insert_faculty1.php?IDNumber=<?php echo htmlspecialchars($idNumber); ?>">
            <input type="hidden" name="IDNumber" value="<?php echo htmlspecialchars($faculty['IDNumber']); ?>">

            <label for="Complains">Complaints:</label>
            <textarea id="Complains" name="Complains" rows="4" placeholder="Enter any complaints or symptoms here..."><?php echo !empty($faculty['Complains']) ? htmlspecialchars($faculty['Complains']) : ''; ?></textarea>

            <label for="Temperature">Temperature (°C):</label>
            <input type="number" id="Temperature" name="Temperature" step="0.1" required placeholder="e.g., 36.5" value="<?php echo !empty($faculty['Temperature']) ? htmlspecialchars($faculty['Temperature']) : ''; ?>">

            <label for="BloodPressure">Blood Pressure (Systolic/Diastolic):</label>
            <input type="text" id="BloodPressure" name="BloodPressure" required placeholder="e.g., 120/80" value="<?php echo !empty($faculty['BloodPressure']) ? htmlspecialchars($faculty['BloodPressure']) : ''; ?>">

            <label for="HeartRate">Heart Rate (bpm):</label>
            <input type="number" id="HeartRate" name="HeartRate" required placeholder="e.g., 75" value="<?php echo !empty($faculty['HeartRate']) ? htmlspecialchars($faculty['HeartRate']) : ''; ?>">

            <label for="RespiratoryRate">Respiratory Rate (bpm):</label>
            <input type="number" id="RespiratoryRate" name="RespiratoryRate" required placeholder="e.g., 16" value="<?php echo !empty($faculty['RespiratoryRate']) ? htmlspecialchars($faculty['RespiratoryRate']) : ''; ?>">

            <label for="Height">Height (cm):</label>
            <input type="number" id="Height" name="Height" required placeholder="e.g., 175" value="<?php echo !empty($faculty['Height']) ? htmlspecialchars($faculty['Height']) : ''; ?>">

            <label for="Weight">Weight (kg):</label>
            <input type="number" id="Weight" name="Weight" required placeholder="e.g., 70" value="<?php echo !empty($faculty['Weight']) ? htmlspecialchars($faculty['Weight']) : ''; ?>">

            <label for="AppointmentDate">Appointment Date:</label>
<input type="datetime-local" id="AppointmentDate" name="AppointmentDate" 
    value="['AppointmentDate']" required>


            <button type="submit">Submit</button>
        </form>
      
    </div>
</body>
</html>
