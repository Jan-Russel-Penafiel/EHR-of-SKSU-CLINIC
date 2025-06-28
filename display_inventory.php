<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Renumber the inventory
$renumberQuery = "SET @new_num = 0;
                  UPDATE inventory SET Num = (@new_num := @new_num + 1) ORDER BY Num ASC";
mysqli_multi_query($conn, $renumberQuery);

// Ensure the queries have executed before fetching the data
mysqli_next_result($conn);

// Fetch all items from the inventory
$query = "SELECT Num, MedName, SupplierName, StockQuantity FROM inventory ORDER BY Num";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo "Error fetching inventory: " . mysqli_error($conn);
    exit;
}

// Function to renumber the Num field
function renumberInventory($conn) {
    $query = "SELECT Num FROM inventory ORDER BY Num ASC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $num = 1; // Start numbering from 1
        while ($row = mysqli_fetch_assoc($result)) {
            // Use prepared statement for the update
            $updateQuery = "UPDATE inventory SET Num = ? WHERE Num = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "ii", $num, $row['Num']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $num++;
        }
    } else {
        echo "Error fetching inventory for renumbering: " . mysqli_error($conn);
    }
}

// Update inventory when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['operation'])) {
        // Update inventory
        $medicineId = $_POST['Num'];
        $operation = $_POST['operation']; // 'add' or 'remove'

        // Fetch current stock quantity
        $query = "SELECT StockQuantity FROM inventory WHERE Num = $medicineId";
        $fetchResult = mysqli_query($conn, $query);
        if ($fetchResult && mysqli_num_rows($fetchResult) > 0) {
            $row = mysqli_fetch_assoc($fetchResult);
            $currentQuantity = $row['StockQuantity'];

            // Update the stock quantity based on the operation
            if ($operation == 'add') {
                $newQuantity = $currentQuantity + 1;
            } elseif ($operation == 'remove') {
                $newQuantity = max(0, $currentQuantity - 1); // Ensure quantity doesn't go below 0
            }

            // Update quantity in the database
            $updateQuery = "UPDATE inventory SET StockQuantity = $newQuantity WHERE Num = $medicineId";
            if (!mysqli_query($conn, $updateQuery)) {
                echo "Error updating inventory: " . mysqli_error($conn);
            } else {
                // Redirect to avoid form resubmission issues
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            echo "Medicine not found.";
        }
    } elseif (isset($_POST['delete'])) {
        // Delete medicine
        $medicineId = $_POST['Num'];

        $deleteQuery = "DELETE FROM inventory WHERE Num = $medicineId";
        if (!mysqli_query($conn, $deleteQuery)) {
            echo "Error deleting medicine: " . mysqli_error($conn);
        } else {
            // Renumber the inventory after deletion
            renumberInventory($conn);

            // Redirect to avoid form resubmission issues
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Array to store names of low stock medicines
$lowStockMedicines = [];

// Check which medicines are low on stock
while ($row = mysqli_fetch_assoc($result)) {
    // Trigger alert only if quantity is less than 11 and below the low stock alert value
    if ($row['StockQuantity'] < 11) {
        $lowStockMedicines[] = $row['MedName'];
    }
}

// Close the previous result set before the next query
mysqli_data_seek($result, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <title>Medicine Inventory</title>
    <style>
        /* CSS styles */
         * {
    margin: 0;
    padding: 0;
    box-sizing: border-box; /* Ensures padding and border are included in element's total width and height */
}

/* Additional Improvements */
html, body {
    height: 100%; /* Ensures the body takes the full height of the viewport */
    font-size: 16px; /* Base font size for better scaling */
    line-height: 1.5; /* Improved line height for readability */
}

/* A smoother scroll experience */
html {
    scroll-behavior: smooth; /* Smooth scrolling for anchor links */
}

/* Prevent overflow on small screens */
body {
    overflow-x: hidden; /* Prevents horizontal overflow */
}

/* Ensure all elements have consistent transition properties */
*,
*:before,
*:after {
    transition: all 0.3s ease; /* Smooth transition for all properties */
}

/* Target specific elements for consistent styling */
h1, h2, h3, h4, h5, h6 {
    margin-bottom: 10px; /* Consistent margin for headings */
}

p {
    margin-bottom: 15px; /* Consistent margin for paragraphs */
}

a {
    text-decoration: none; /* Remove underline from links */
    color: inherit; /* Inherit color for links */
    transition: color 0.3s; /* Smooth color transition for links */
}

a:hover {
    color: #1abc9c; /* Change link color on hover */
}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */

    font-size: 18px; /* Adjust to suit the design */
    background-color: lightblue; /* Soft background color */
    color: black; /* Darker text for better readability */
  
   
}
body {
    background-image: url(image.jpeg);
    background-repeat: no-repeat;
    background-size: 100% 100%;
    background-attachment: fixed;
    
}

header {
    background-color: green; /* Green for a fresh look */
    color: white;
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    text-align: center;
    padding: 20px 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Slight shadow for depth */
    height: 100px; /* Define a fixed height for better vertical alignment */
    border-radius: 8px;
}


.sidebar {
    background-color: #2c3e50;
    width: 200px; /* Reduced width */
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding-top: 10px;
    overflow-y: auto;
    z-index: 1000;
    transition: width 0.3s;
}

.sidebar a {
    display: flex;
    align-items: center;
    padding: 8px; /* Reduced padding */
    text-decoration: none;
    color: white;
    font-size: 12px; /* Reduced font size */
}

.sidebar a:hover {
    background-color: green;
    border-radius: 5px;
}

.container {
    margin-left: 200px; /* Adjusted for new sidebar width */
    padding: 15px; /* Reduced padding */
    transition: margin-left 0.3s;
    
}

#backButton {
    background-color: blue; /* Blue button color */
            color: white; /* Text color */
            padding: 5px 5px; /* Padding */
            text-decoration: none; /* Remove underline */
            border-radius: 5px; /* Rounded corners */
            font-size: 14px; /* Larger font size */
            transition: background-color 0.3s; /* Transition for hover effect */
            cursor: pointer; /* Pointer cursor */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            margin: 1px;
        }

        #backButton:hover {
            background-color: green; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        #backButton:active {
            transform: translateY(0); /* Reset lift effect on click */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Reduce shadow on click */
        }
        .resizer {
            width: 10px; /* Width of the resizer */
            cursor: ew-resize; /* Cursor style */
            position: fixed; /* Fixed position */
            top: 0;
            left: 195px; /* Initially set to match the sidebar width */
            height: 100vh; /* Full height */
            background-color: transparent; /* Invisible, can change for visibility */
            z-index: 1100; /* Above sidebar */
        }

.main-content { 
    background-color: rgba(173, 216, 230, 0.50); /* Light blue background with 50% transparency */
    border-radius: 8px;
    padding: 0px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 11.5px;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    text-align: center;
    height: auto; /* Set a fixed height */
   
}



h3 {
    margin-bottom: 20px;
    color: green; /* Keeping the green theme */
    font-size: 24px; /* Larger font for headings */
    border-bottom: 2px solid #4CAF50; /* Underline for emphasis */
    padding-bottom: 10px; /* Spacing below the heading */
}

h1 {
    margin-bottom: 5px;
    color: white; /* Keeping the green theme */
    font-size: 40px; /* Larger font for headings */
    border-bottom: 0px solid #4CAF50; /* Underline for emphasis */
    padding-bottom: 0px; /* Spacing below the heading */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    border: 0.1px solid white;
    
    
    
}

th {
            background-color: green; /* Green header */
            color: white;
            font-weight: bold; /* Bold text for headers */
            cursor: pointer; /* Change cursor to pointer for clickable headers */
            
        }


tr:hover {
    background-color: lightgreen; /* Light green hover for rows */
}

.btn {
    background-color: blue; /* Blue button for better visibility */
    color: white;
    border: none;
    padding: 10px 10px; /* Increased padding */
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s; /* Added transform for effect */
}

.btn:hover {
    background-color: darkblue; /* Darker blue on hover */
    transform: scale(1.05); /* Slightly enlarges button */
}

.button {
    background-color: red; /* Blue button for better visibility */
    color: white;
    border: none;
    padding: 10px 15px; /* Increased padding */
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s; /* Added transform for effect */
}

.button:hover {
    background-color: darkred; /* Darker blue on hover */
    transform: scale(1.05); /* Slightly enlarges button */
}




        #searchInput {
            width: 50%;
            padding: 10px; /* Increased padding for comfort */
            margin: 10px 0; /* Space between elements */
            border: 1px solid #ccc; /* Border for input fields */
            border-radius: 5px; /* Rounded corners */
            font-size: 14px; /* Consistent font size */
        }

        #searchContainer {
            margin-bottom: 20px; /* Space below the search bar */
        }

        select {
    padding: 10px;
    border: 1px solid #ccc; /* Light gray border */
    border-radius: 4px; /* Slightly rounded corners */
    font-size: 14px;
    margin-right: 20px; /* Space between dropdown and search input */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    font-family: 'Fira Code', monospace;
}

input[type="text"] {
    padding: 10px;
    border: 1px solid #ccc; /* Light gray border */
    border-radius: 4px; /* Slightly rounded corners */
    font-size: 14px;
    width: 200px; /* Width for the search input */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    font-family: 'Fira Code', monospace;
}

input[type="text"]:focus, select:focus {
    border-color: #007bff; /* Change border color on focus */
    outline: none; /* Remove default outline */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Light blue shadow on focus */
}

.alert-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            margin-left: 10px;
            margin-top: 3px;
        }

        .alert-btn:hover {
            background-color: darkred; /* Darker shade on hover */
    transform: translateY(-2px); /* Lift the button slightly */
    border-color: #1e7e34; /* Change border color on hover */
        }

   

.button-container {
    display: flex;
    align-items: center;
    gap: 10px;
}
.icon-container {
    width: 30px; /* Smaller width */
    height: 21.3px; /* Smaller height */
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: lightblue;
    border-radius: 50%;
    margin-right: 3px; /* Smaller margin */
    font-size: 16px; /* Smaller font size */
}


.logout { background-color: #ff4c4c; } /* Red */
.dashboard { background-color: #4caf50; } /* Green */
.students { background-color: #2196f3; } /* Blue */
.faculty { background-color: #9c27b0; } /* Purple */
.consultations { background-color: #ff9800; } /* Orange */
.treatments { background-color: #f44336; } /* Dark Red */
.medical { background-color: #009688; } /* Teal */
.inventory { background-color: #673ab7; } /* Deep Purple */
.online-appointment { background-color: #3f51b5; } /* Indigo */
.search { background-color: #00bcd4; } /* Cyan */
.alert { background-color: #ffeb3b; } /* Yellow */
.events { background-color: #ff5722; } /* Deep Orange */
.account { background-color: #607d8b; } /* Blue Grey */
.reports { background-color: #795548; } /* Brown */

.icon-container i {
    color: #fff; /* White icon color */
}

.logo-container {
    text-align: center; /* Center the logo */
    margin-bottom: 20px; /* Space below the logo */
    margin-left: 55px; /* Automatically adds space on the left */
    margin-right: 0; /* Optional: Ensure no right margin */
    width: 80px; /* Reduced width for a more compact look */
    height: 80px; /* Reduced height for a more compact look */
    border-radius: 50%; /* Makes the container circular */
    overflow: hidden; /* Hides overflow */
    display: flex; /* Center the image within the circle */
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    padding-left: 0; /* Remove left padding for perfect centering */
    background-color: #fff; /* Optional: background color for visibility */
    border: 2px solid green; /* Optional: border for better visibility */
}

.sksu-logo {
    width: 100%; /* Makes the logo responsive to container size */
    height: auto; /* Maintain aspect ratio */
}


        .low-stock {
            background-color:  #d4edda;
            color: #155724;
        }
        .normal-stock {
            background-color: #d4edda;
            color: #155724;
        }
        button {
            padding: 8px 12px;
            background-color: green;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #218838;
        }
        .delete-btn {
            background-color: red;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .operation-buttons {
            display: flex;
            justify-content: space-around;
        }

        .alert-banner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #f44336; /* Red color for alert */
            color: white;
            text-align: center;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Slight shadow for depth */
            z-index: 1001; /* Ensure it appears above all other elements */
            opacity: 0;
            transition: opacity 0.5s ease-in-out; /* Smooth fade-in/fade-out */
        }

        .alert-banner.visible {
            opacity: 1; /* Makes the alert visible */
        }

        .alert-banner.hidden {
            opacity: 0; /* Fades the alert out */
            transition: opacity 1.5s ease-in-out; /* Smooth fade-out */
        }

        .btn1 {
            background-color: green; /* Blue button color */
            color: white; /* Text color */
            padding: 5px 5px; /* Padding */
            text-decoration: none; /* Remove underline */
            border-radius: 5px; /* Rounded corners */
            font-size: 14px; /* Larger font size */
            transition: background-color 0.3s; /* Transition for hover effect */
            cursor: pointer; /* Pointer cursor */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            margin: 1px;
         
        }

.btn1:hover {
    background-color: blue; /* Darker green on hover */
}
    </style>
<script>
   function updateQuantity(medicineId, operation) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "update_inventory.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Parse the response
                    const responseData = xhr.responseText.split(',');
                    const updatedRow = `
                        <td>${responseData[0]}</td>
                        <td>${responseData[1]}</td>
                        <td>${responseData[2]}</td>
                        <td>${responseData[3]}</td>
                        <td>${responseData[3] <= responseData[4] ? 'Low Stock' : 'In Stock'}</td>
                        <td class="operation-buttons">
                            <button type="button" onclick="updateQuantity(${responseData[0]}, 'add')">Add</button>
                            <button type="button" onclick="updateQuantity(${responseData[0]}, 'remove')">Minus</button>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="Num" value="${responseData[0]}">
                                <button type="submit" name="delete" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    `;
                    document.getElementById(`row-${medicineId}`).innerHTML = updatedRow;

                    checkLowStock(); // Call the low stock check immediately after update
                } else {
                    console.error('Error updating inventory:', xhr.responseText);
                }
            }
        };
        xhr.send(`Num=${medicineId}&operation=${operation}`);
    }

    function checkLowStock() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "check_low_stock.php", true); // PHP file to return low stock medicines
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            const lowStockMedicines = JSON.parse(xhr.responseText);

            // Only display alert if low stock medicines exist
            if (Array.isArray(lowStockMedicines) && lowStockMedicines.length > 0) {
                const alertBanner = document.createElement('div');
                alertBanner.className = 'alert-banner visible'; // Initially visible
                alertBanner.innerHTML = "⚠️ Warning: The following medicines are low on stock: " + lowStockMedicines.join(", ");
                document.body.appendChild(alertBanner);

                // Automatically fade out after 10 seconds
                setTimeout(() => {
                    alertBanner.classList.remove('visible');
                    alertBanner.classList.add('hidden'); // Correctly hide the banner

                    // Remove the banner from the DOM after fading out
                    setTimeout(() => {
                        alertBanner.remove();

                        // Show the alert banner again after 5 seconds
                        setTimeout(checkLowStock, 5000); // Call checkLowStock again after 5 seconds
                    }, 2000); // Wait for fade-out to complete before removing
                }, 10000); // Show for 10 seconds
            } else {
                // If no low stock medicines, check again after 5 seconds
                setTimeout(checkLowStock, 5000);
            }
        }
    };
    xhr.send();
}

// Call the function on page load
window.onload = function() {
    checkLowStock(); // Check immediately when the page loads
    }

    function showLowStockBanner(message) {
        const alertBanner = document.createElement('div');
        alertBanner.className = 'alert-banner visible'; // Initially visible
        alertBanner.innerHTML = message;
        document.body.appendChild(alertBanner);

        // Automatically fade out after 10 seconds
        setTimeout(() => {
            alertBanner.classList.remove('visible');
            alertBanner.classList.add('hidden'); // Correctly hide the banner

            // Remove the banner from the DOM after fading out
            setTimeout(() => {
                alertBanner.remove();
                // Show the alert banner again after 5 seconds
                setTimeout(checkLowStock, 5000); // Call checkLowStock again after 5 seconds
            }, 2000); // Wait for fade-out to complete before removing
        }, 10000); // Show for 10 seconds
    }

    function hideLowStockBanner() {
        const existingBanner = document.querySelector('.alert-banner');
        if (existingBanner) {
            existingBanner.classList.remove('visible');
            existingBanner.classList.add('hidden'); // Hide the banner
            setTimeout(() => existingBanner.remove(), 2000); // Remove it after fade-out
        }
    }

    // Call the function on page load
    window.onload = function() {
        checkLowStock(); // Check immediately when the page loads
    };
</script>


</head>
<body>
<div class="sidebar">
<div class="logo-container">
        <img src="sksu-logo.png" alt="SKSU Logo" class="sksu-logo">
        </div> 
        <a href="logout_admin.php"><span class="icon-container logout"><i class="fas fa-sign-out-alt"></i></span> Logout</a>
<a href="dashboard.php"><span class="icon-container dashboard"><i class="fas fa-tachometer-alt"></i></span> Dashboard</a>
<a href="display_faculty.php"><span class="icon-container faculty"><i class="fas fa-chalkboard-teacher"></i></span> Personnel Records</a>
<a href="student_information.php"><span class="icon-container students"><i class="fas fa-user-graduate"></i></span> Students Records</a>
<a href="display_intv.php"><span class="icon-container consultations"><i class="fas fa-comments"></i></span> Consultations Records</a>
<a href="display_illmed.php"><span class="icon-container treatments"><i class="fas fa-stethoscope"></i></span> Treatments Records</a>
<a href="display_medical.php"><span class="icon-container medical"><i class="fas fa-file-medical"></i></span> Medical History</a>
<a href="display_inventory.php"><span class="icon-container inventory"><i class="fas fa-capsules"></i></span> Medicine Inventory</a>
<a href="display_online_appointment.php"><span class="icon-container online-appointment"><i class="fas fa-laptop"></i></span> Online Appointments</a>
<a href="search1.php"><span class="icon-container search"><i class="fas fa-search"></i></span> Search Engine</a>
<a href="events.php"><span class="icon-container events"><i class="fas fa-calendar-alt"></i></span> Create Events</a>
<a href="alert.php"><span class="icon-container alert"><i class="fas fa-exclamation-triangle"></i></span> View Alerts</a>
<a href="account.php"><span class="icon-container account"><i class="fas fa-user-circle"></i></span> View Accounts</a>
<a href="reports.php"><span class="icon-container reports"><i class="fas fa-file-alt"></i></span> View Reports</a>

</div>

<div class="resizer"></div>
<div class="container">

    <header>
    <h1>MEDICINE INVENTORY</h1>
    </header>
    <hr>
    
<a href="insert_inventory.php" class="btn1">Add New Medicine</a> 
<a id="backButton" href="search1.php">Back to Search</a>
    <table>
        <thead>
            <tr>
                <th>#</th> <!-- Add a header for the Num column -->
                <th>Medication Name</th>
                <th>Supplier</th>
                <th>Stock Quantity</th>
                <th>Status</th>
            <th>Change Quantity</th>
            <th>Delete</th>
            </tr>
        </thead>
        <tbody>
          
       
        <?php while ($row = mysqli_fetch_assoc($result)) { 
    $status = ($row['StockQuantity'] <= 10) ? 'Low Stock' : 'In Stock';  // Set low stock based on your requirement
    $statusClass = ($row['StockQuantity'] <= 10) ? 'low-stock' : 'normal-stock';  // Adjust for low stock styling
?>
    <tr id="row-<?php echo $row['Num']; ?>" class="<?php echo $statusClass; ?>">
        <td><?php echo $row['Num']; ?></td>
        <td><?php echo $row['MedName']; ?></td>
        <td><?php echo $row['SupplierName']; ?></td>
        <td><?php echo $row['StockQuantity']; ?></td>
        <td><?php echo $status; ?></td>
        <td class="operation-buttons">
            <button type="button" onclick="updateQuantity(<?php echo $row['Num']; ?>, 'add')">Add</button>
            <button type="button" onclick="updateQuantity(<?php echo $row['Num']; ?>, 'remove')">Minus</button>
        </td>
        <td>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="Num" value="<?php echo $row['Num']; ?>">
                <button type="submit" name="delete" class="delete-btn">Delete</button>
            </form>
        </td>
    </tr>
<?php } ?>
</tbody>

    </table>
</div>
<script>
    const resizer = document.querySelector('.resizer');
    const sidebar = document.querySelector('.sidebar');
    const container = document.querySelector('.container');

    let isResizing = false;

    resizer.addEventListener('mousedown', (event) => {
        isResizing = true;
    });

    window.addEventListener('mousemove', (event) => {
        if (!isResizing) return;
        const newWidth = event.clientX;
        sidebar.style.width = `${newWidth}px`;
        container.style.marginLeft = `${newWidth}px`;
        resizer.style.left = `${newWidth}px`; // Adjust the position of the resizer
    });

    window.addEventListener('mouseup', () => {
        isResizing = false;
    });
</script>
</body>
</html>

<?php
mysqli_close($conn);
?>
