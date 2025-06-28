<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Retrieve the IDNumber from the URL if available
$idNumber = isset($_GET['IDNumber']) ? mysqli_real_escape_string($conn, $_GET['IDNumber']) : '';

// Check if the IDNumber is provided and not empty
if (empty($idNumber)) {
    die("Error: IDNumber is not set or is empty.");
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $num = mysqli_real_escape_string($conn, $_POST['Num']); // Added Num field
    $whatYouDo = mysqli_real_escape_string($conn, $_POST['WhatYouDo']);
    $existingDisease = mysqli_real_escape_string($conn, $_POST['ExistingDisease']);
    $familyHistory = mysqli_real_escape_string($conn, $_POST['FamilyHistory']);
    $allergies = mysqli_real_escape_string($conn, $_POST['Allergies']);

    // Set "N/A" for empty fields
    $existingDisease = empty($existingDisease) ? 'N/A' : $existingDisease;
    $familyHistory = empty($familyHistory) ? 'N/A' : $familyHistory;
    $allergies = empty($allergies) ? 'N/A' : $allergies;

    // Check if the IDNumber exists in personal_info table
    $result = mysqli_query($conn, "SELECT * FROM personal_info WHERE IDNumber='$idNumber'");
    if (mysqli_num_rows($result) == 0) {
        die("Error: IDNumber does not exist in personal_info table.");
    }

    // Insert form data into the database
    $sql = "INSERT INTO intv (Num, IDNumber, what_you_do, what_is_your_existing_desease, have_you_a_family_history_desease, have_you_a_allergy) 
            VALUES ('$num', '$idNumber', '$whatYouDo', '$existingDisease', '$familyHistory', '$allergies')";

    if (mysqli_query($conn, $sql)) {
        // Renumber entries after insertion (if necessary)
        if (!mysqli_query($conn, "SET @num = 0;")) {
            echo "Error resetting num: " . mysqli_error($conn);
        }
        if (!mysqli_query($conn, "UPDATE intv SET Num = (@num := @num + 1) ORDER BY Num ASC;")) {
            echo "Error renumbering entries: " . mysqli_error($conn);
        }

        // Redirect to display page
        header("Location: insert_illmed1.php?IDNumber=$idNumber"); // Include IDNumber in the redirect
        exit();
    } else {
        echo "Error inserting record: " . mysqli_error($conn);
    }

    mysqli_close($conn); // Close the database connection
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Consultation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
            margin-top: 43px;
           max-height: 588px;
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
    <h2>ADD STUDENT CONSULTATION RECORDS</h2>
    <form method="POST" action="insert_intv.php?IDNumber=<?php echo htmlspecialchars($idNumber); ?>"> <!-- Pass IDNumber in action URL -->
        <input type="hidden" name="Num" value="<?php echo isset($Num) ? htmlspecialchars($Num) : ''; ?>">
        <input type="hidden" name="IDNumber" value="<?php echo htmlspecialchars($idNumber); ?>"> <!-- Use the retrieved IDNumber -->

        <div class="form-group">
            <label for="WhatYouDo">Activity:</label>
            <input type="text" name="WhatYouDo" id="WhatYouDo" placeholder="e.g., Walking, Exercise, Eating" required>
        </div>

        <div class="form-group">
            <label for="ExistingDisease">Existing Disease:</label>
            <input type="text" name="ExistingDisease" id="ExistingDisease" placeholder="e.g., Diabetes, Hypertension">
        </div>

        <div class="form-group">
            <label for="FamilyHistory">Family History of Disease:</label>
            <input type="text" name="FamilyHistory" id="FamilyHistory" placeholder="e.g., Heart Disease, Cancer">
        </div>

        <div class="form-group">
            <label for="Allergies">Allergies:</label>
            <input type="text" name="Allergies" id="Allergies" placeholder="e.g., Penicillin, Nuts">
        </div>

        <button type="submit" class="btn">Submit Record</button>
    </form>
</div>
</body>
</html>
