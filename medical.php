<?php
// Include database connection file
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Initialize variables for success/error messages
$message = '';
$error_message = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    $Num = $_POST['id']; // Use the correct identifier from the dropdown
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $temperature = $_POST['temperature'];
    $heartRate = $_POST['heartRate'];
    $bloodPressure = $_POST['bloodPressure'];
    $year = date("Y"); // Automatically get the current year

    // Validate numeric inputs
    if (!is_numeric($height) || !is_numeric($weight) || !is_numeric($temperature) || !is_numeric($heartRate)) {
        $error_message = "Please enter valid numeric values.";
    } else {
        // Check if the Num exists in personal_info
        $checkNumSql = "SELECT * FROM personal_info WHERE Num = ?";
        if ($checkNumStmt = $conn->prepare($checkNumSql)) {
            $checkNumStmt->bind_param("s", $Num);
            $checkNumStmt->execute();
            $checkNumStmt->store_result();

            if ($checkNumStmt->num_rows == 0) {
                // If Num does not exist
                $error_message = "The selected student does not exist.";
            } else {
                // Check for duplicate entry based on Num and year
                $checkSql = "SELECT * FROM medical_history WHERE Num = ? AND years = ?";
                if ($checkStmt = $conn->prepare($checkSql)) {
                    $checkStmt->bind_param("si", $Num, $year);
                    $checkStmt->execute();
                    $checkStmt->store_result();

                    if ($checkStmt->num_rows > 0) {
                        // Duplicate found
                        $error_message = "Medical data for this student already exists for the current year.";
                    } else {
                        // Insert into the Medical entity if no duplicate is found
                        $sql = "INSERT INTO medical_history (Num, height, weight, heartrate, bloodpressure, temperature, years) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";

                        if ($stmt = $conn->prepare($sql)) {
                            // Binding parameters: 'i' for integer and 's' for string
                            $stmt->bind_param("issssss", $Num, $height, $weight, $heartRate, $bloodPressure, $temperature, $year);
                            if ($stmt->execute()) {
                                $message = "Medical data successfully recorded!";
                                // Reset form fields after successful submission
                                $_POST = array();
                            } else {
                                $error_message = "Error: " . $stmt->error;
                            }
                            $stmt->close();
                        } else {
                            $error_message = "Error preparing statement: " . $conn->error;
                        }
                    }
                    $checkStmt->close();
                } else {
                    $error_message = "Error preparing duplicate check statement: " . $conn->error;
                }
            }
            $checkNumStmt->close();
        } else {
            $error_message = "Error preparing student check statement: " . $conn->error;
        }
    }
}

// Fetch personal info for the search
$personalInfoSql = "SELECT Num, FirstName, LastName, IDnumber, Age, Course, Yr, Section FROM personal_info";
$personalInfoResult = $conn->query($personalInfoSql);

// Fetch the student details if a student is selected
$selectedNum = '';
$studentDetails = [];

if (isset($_POST['selectedNum'])) {
    $selectedNum = $_POST['selectedNum'];
    $studentDetailSql = "SELECT * FROM personal_info WHERE Num = ?";

    if ($stmt = $conn->prepare($studentDetailSql)) {
        $stmt->bind_param("s", $selectedNum);
        $stmt->execute();
        $result = $stmt->get_result();
        $studentDetails = $result->fetch_assoc();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Data Entry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        header {
            background-color: green; 
            color: white;
            display: flex;
            justify-content: center; 
            align-items: center; 
            text-align: center;
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
            height: 100px; 
            border-radius: 10px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            font-size: 18px;
            background-image: url(image.jpeg);
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto; /* Center the container */
            padding: 20px;
            background-color: rgba(173, 216, 230, 0.50);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 8px;
        }

        input[type="number"],
        input[type="text"],
        input[type="hidden"],
        select,
        button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: white;
        }

        /* Custom styling for select elements */
        select {
            appearance: none; /* Remove default styling in modern browsers */
            background-color: #f9f9f9; /* Light grey background for dropdown */
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            margin: 8px 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="black" d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center;
            background-size: 12px;
            font-size: 16px;
            color: #333;
            cursor: pointer;
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

        .student-list {
            max-height: 50px;
            overflow-y: auto;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            background-color: #ffffff;
            padding: 10px;
            display: none;
        }

        .student-item {
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .student-item:hover {
            background-color: #ecf0f1;
        }

        .message {
            text-align: center;
            color: green;
            margin: 10px 0;
        }

        .error-message {
            text-align: center;
            color: red;
            margin: 10px 0;
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
            margin-left: 2px;
            margin-top: -3px;
            width: 6%;
        }

        #backButton:hover {
            background-color: green; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        #backButton:active {
            transform: translateY(0); /* Reset lift effect on click */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Reduce shadow on click */
        }


        /* Responsive styling */
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }

            input[type="number"],
            input[type="text"],
            input[type="hidden"],
            select,
            button {
                padding: 8px;
            }
        }
    </style>
    <script>
        function filterStudents() {
            const input = document.getElementById('studentSearch').value.toLowerCase();
            const studentList = document.getElementById('studentList');
            const students = studentList.getElementsByClassName('student-item');

            let hasResults = false;

            for (let i = 0; i < students.length; i++) {
                const studentName = students[i].textContent.toLowerCase();
                if (studentName.includes(input)) {
                    students[i].style.display = ''; // Show matching student
                    hasResults = true;
                } else {
                    students[i].style.display = 'none'; // Hide non-matching student
                }
            }

            studentList.style.display = hasResults ? 'block' : 'none';
        }

        function selectStudent(num, firstName, lastName, age, course, yr, section) {
            document.getElementById('id').value = num;
            document.getElementById('studentSearch').value = firstName + ' ' + lastName;
            document.getElementById('studentAge').value = age;
            document.getElementById('studentCourse').value = course;
            document.getElementById('studentYear').value = yr;
            document.getElementById('studentSection').value = section;
            document.getElementById('studentList').style.display = 'none';
        }
    </script>
</head>
<body>
    <div class="container">
    <a id="backButton" href="display_medical.php">Back</a>
        <header>
            <h1>MEDICAL DATA ENTRY</h1>
        </header>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="POST" action="medical.php">
            <label for="studentSearch">Search Student:</label>
            <input type="text" id="studentSearch" onkeyup="filterStudents()" placeholder="Type a name...">
            <div id="studentList" class="student-list">
    <?php while ($row = $personalInfoResult->fetch_assoc()): ?>
        <div class="student-item" onclick="selectStudent('<?php echo $row['Num']; ?>', '<?php echo $row['FirstName']; ?>', '<?php echo $row['LastName']; ?>', '<?php echo $row['Age']; ?>', '<?php echo $row['Course']; ?>', '<?php echo $row['Yr']; ?>', '<?php echo $row['Section']; ?>')">
            <?php echo $row['FirstName'] . ' ' . $row['LastName'] . ' (ID Number: ' . $row['IDnumber'] . ')'; ?>
        </div>
    <?php endwhile; ?>
</div>

            <label for="id">Other Informations:</label>
            <input type="hidden" name="id" id="id" required>
            <input type="text" id="studentAge" placeholder="Age" disabled>
            <input type="text" id="studentCourse" placeholder="Course" disabled>
            <input type="text" id="studentYear" placeholder="Year" disabled>
            <input type="text" id="studentSection" placeholder="Section" disabled>

            <label for="height">Height (cm):</label>
<input type="number" name="height" placeholder="Enter height in cm" required min="120" max="250" title="Enter height between 120 cm and 250 cm">

<label for="weight">Weight (kg):</label>
<input type="number" name="weight" placeholder="Enter weight in kg" required min="1" max="300" title="Enter weight between 1 kg and 300 kg">

<label for="temperature">Temperature (°C):</label>
<input type="number" name="temperature" placeholder="Enter temperature in °C" required min="20" max="100" step="0.1" title="Enter temperature between 20°C and 100°C">

<label for="heartRate">Heart Rate (bpm):</label>
<input type="number" name="heartRate" placeholder="Enter heart rate in bpm" required min="40" max="200" title="Enter heart rate between 40 bpm and 200 bpm">

<label for="bloodPressure">Blood Pressure:</label>
<input type="text" name="bloodPressure" placeholder="e.g. 120/80" required pattern="^\d{2,3}/\d{2,3}$" title="Enter blood pressure in format '120/80' with numbers only">


            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
