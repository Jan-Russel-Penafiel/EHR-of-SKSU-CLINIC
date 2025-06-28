<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the session variables are set
if (!isset($_SESSION['IDNumber']) || !isset($_SESSION['GmailAccount'])) {
    header("Location: signup.html"); // Redirect to signup if no session data is available
    exit();
}

$idNumber = $_SESSION['IDNumber'];
$gmailAccount = $_SESSION['GmailAccount'];



// Query the database for the student's personal information
$sql = "SELECT * FROM personal_info WHERE IDNumber = ? AND GmailAccount = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $idNumber, $gmailAccount);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if the query was successful
if ($result && mysqli_num_rows($result) > 0) {
    $studentData = mysqli_fetch_assoc($result);
} else {
    echo "No student data found for ID Number: $idNumber and Gmail Account: $gmailAccount";
    exit();
}

// Calculate age if the birthdate is available
$age = 'Not specified'; // Default value if birthdate is not set
if (isset($studentData['Birthdate']) && !empty($studentData['Birthdate'])) {
    $birthdate = new DateTime($studentData['Birthdate']);
    // Calculate age
    $today = new DateTime();
    $age = $today->diff($birthdate)->y; // Get the age in years
}

// Function to display the profile picture
function displayProfilePicture($picturePath) {
    if (!empty($picturePath)) {
        return '<img class="profile-picture" src="' . htmlspecialchars($picturePath, ENT_QUOTES) . '" alt="Profile Picture">';
    } else {
        return '<img class="profile-picture" src="default-profile.png" alt="Default Profile Picture">'; // Path to a default picture
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            font-size: 16px;
            background-image: url('image.jpeg');
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            color: black;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: rgba(173, 216, 230, 0.9);
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
            text-align: center;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin: 10px 0;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        strong {
            color: #555;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background: darkblue;
        }
        .profile-picture {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #007bff;
            display: block;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>STUDENT INFORMATION</h2>
    <hr>
    <?php echo displayProfilePicture($studentData['ProfilePicture'] ?? ''); ?>
    <ul>
   
        <li><strong>First Name:</strong> <?php echo htmlspecialchars($studentData['FirstName'] ?? '', ENT_QUOTES); ?></li>
        <li><strong>Last Name:</strong> <?php echo htmlspecialchars($studentData['LastName'] ?? '', ENT_QUOTES); ?></li>
        <li><strong>ID Number:</strong> <?php echo htmlspecialchars($studentData['IDNumber'] ?? '', ENT_QUOTES); ?></li>
        <li><strong>SKSU Account:</strong> <?php echo htmlspecialchars($studentData['GmailAccount'] ?? '', ENT_QUOTES); ?></li>
        <li><strong>Age:</strong> <?php echo htmlspecialchars($age ?? 'Not specified', ENT_QUOTES); ?></li>
        <li><strong>Gender:</strong> <?php echo htmlspecialchars($studentData['Gender'] ?? '', ENT_QUOTES); ?></li>
        <li><strong>Course:</strong> <?php echo htmlspecialchars($studentData['Course'] ?? '', ENT_QUOTES); ?></li>
        <li><strong>Year:</strong> <?php echo htmlspecialchars($studentData['Yr'] ?? '', ENT_QUOTES); ?></li>
        <li><strong>Section:</strong> <?php echo htmlspecialchars($studentData['Section'] ?? '', ENT_QUOTES); ?></li>
    </ul>

    <a href="login.html">Login</a>
</div>

</body>
</html>
