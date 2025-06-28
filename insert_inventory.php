<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medicineName = mysqli_real_escape_string($conn, $_POST['medicine_name']);
    $supplierName = mysqli_real_escape_string($conn, $_POST['supplier_name']);
    $initialQuantity = intval($_POST['initial_quantity']);

    // Check if the medicine already exists in the inventory
    $query = "SELECT * FROM inventory WHERE MedName = '$medicineName'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        // Medicine already exists
        echo "<script>alert('This medicine already exists in the inventory.');</script>";
    } else {
        // Insert new medicine record
        $insertQuery = "INSERT INTO inventory (MedName, SupplierName, StockQuantity) 
                        VALUES ('$medicineName', '$supplierName', $initialQuantity)";
        if (mysqli_query($conn, $insertQuery)) {
            // Redirect to display_inventory.php after successful insertion
            header("Location: display_inventory.php");
            exit();
        } else {
            echo "Error inserting record: " . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Medicine into Inventory</title>
    <style>

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
            margin: 5px; /* Margin for spacing */
            width: 6.3%;
        }

        #backButton:hover {
            background-color: green; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        #backButton:active {
            transform: translateY(0); /* Reset lift effect on click */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Reduce shadow on click */
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
            margin-top: 70px;
           max-height: 588px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-top: 10px;
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
   
    <a id="backButton" href="display_inventory.php">Back</a>
    <h2>ADD MEDICINE RECORDS</h2>
    <form method="POST" action="insert_inventory.php">
        <label for="medicine_name">Medicine Name:</label>
        <input type="text" name="medicine_name" id="medicine_name" required placeholder="Enter Medicine Name">

        <label for="supplier_name">Supplier Name:</label>
        <input type="text" name="supplier_name" id="supplier_name" required placeholder="Enter Supplier Name">

        <label for="initial_quantity">Initial Quantity:</label>
        <input type="number" name="initial_quantity" id="initial_quantity" required placeholder="Enter Initial Quantity">

        <button type="submit">Add Medicine</button>
    </form>
</div>
</body>
</html>
