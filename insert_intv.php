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
        header("Location: insert_illmed.php?IDNumber=$idNumber"); // Include IDNumber in the redirect
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        input[type="text"]:focus,
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

        .error-message {
            color: var(--error-color);
            background-color: #fde8e8;
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            display: none;
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
        <h1><i class="fas fa-notes-medical"></i> Add Consultation Record</h1>
    </header>

    <div class="container">
        <div class="form-card">
            <form method="POST" action="insert_intv.php?IDNumber=<?php echo htmlspecialchars($idNumber); ?>">
                <input type="hidden" name="Num" value="<?php echo isset($num) ? $num : ''; ?>">
                
                <div class="form-group">
                    <label for="IDNumber">ID Number:</label>
                    <input type="text" name="IDNumber" value="<?php echo htmlspecialchars($idNumber); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="WhatYouDo">What do you do?</label>
                    <textarea name="WhatYouDo" required></textarea>
                </div>

                <div class="form-group">
                    <label for="ExistingDisease">Do you have any existing disease?</label>
                    <textarea name="ExistingDisease"></textarea>
                </div>

                <div class="form-group">
                    <label for="FamilyHistory">Do you have any family history of disease?</label>
                    <textarea name="FamilyHistory"></textarea>
                </div>

                <div class="form-group">
                    <label for="Allergies">Do you have any allergies?</label>
                    <textarea name="Allergies"></textarea>
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
