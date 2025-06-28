<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if session variables are set
if (!isset($_SESSION['IDNumber']) || !isset($_SESSION['GmailAccount'])) {
    header("Location: signup_faculty.html"); // Redirect to signup if no session data is available
    exit();
}

// Set a success message if available
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']); // Clear the message after using it

$idNumber = $_SESSION['IDNumber'];
$gmailAccount = $_SESSION['GmailAccount'];

// Query the database for faculty information
$sql = "SELECT * FROM faculty WHERE IDNumber = ? AND GmailAccount = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $idNumber, $gmailAccount);
$stmt->execute();
$result = $stmt->get_result();

// Check if data was found
if ($result && $result->num_rows > 0) {
    $facultyData = $result->fetch_assoc();
} else {
    echo "No faculty data found for ID Number: $idNumber and Gmail Account: $gmailAccount";
    exit();
}

// Function to display the profile picture
function displayProfilePicture($picturePath) {
    if (!empty($picturePath)) {
        return '<img class="profile-picture" src="' . htmlspecialchars($picturePath, ENT_QUOTES) . '" alt="Profile Picture">';
    } else {
        return '<img class="profile-picture" src="default-profile.png" alt="Default Profile Picture">';
    }
}

// Function to display a field or "NONE" if empty
function displayField($field) {
    return !empty($field) ? htmlspecialchars($field, ENT_QUOTES) : "NONE";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Information</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        .success-message {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>PERSONNEL INFORMATION</h2>
    <hr>


    <?php echo displayProfilePicture($facultyData['ProfilePicture'] ?? ''); ?>
    <ul>
        <li><strong>ID Number:</strong> <?php echo displayField($facultyData['IDNumber'] ?? ''); ?></li>
        <li><strong>Rank:</strong> <?php echo displayField($facultyData['Rank'] ?? ''); ?></li>
        <li><strong>First Name:</strong> <?php echo displayField($facultyData['FirstName'] ?? ''); ?></li>
        <li><strong>Last Name:</strong> <?php echo displayField($facultyData['LastName'] ?? ''); ?></li>
        <li><strong>SKSU Account:</strong> <?php echo displayField($facultyData['GmailAccount'] ?? ''); ?></li>
        <li><strong>Department:</strong> <?php echo displayField($facultyData['Department'] ?? ''); ?></li>
        <li><strong>Position:</strong> <?php echo displayField($facultyData['Position'] ?? ''); ?></li>
    </ul>

    <a href="login_faculty.html">Login</a>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
