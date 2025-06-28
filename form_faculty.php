<?php
// form_faculty.php

// Start the session (if you're using sessions) or retrieve parameters directly from the URL
$IDNumber = isset($_GET['IDNumber']) ? $_GET['IDNumber'] : '';
$GmailAccount = isset($_GET['GmailAccount']) ? $_GET['GmailAccount'] : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnels Registration Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            font-size: 18px; /* Adjust to suit the design */
            background-color: lightblue; /* Soft background color */
            color: black; /* Darker text for better readability */
        }
        body {
            background-image: url(image8.jpeg);
            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-attachment: fixed;
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        .form-container {
            max-width: 600px;
            margin: 15px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: lightblue;
            aspect-ratio: 16/9;
        }
        .label-group {
            margin-bottom: 15px;
            font-weight: 500;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 16px;
            
        }
        input[type="text"],
        input[type="email"],
        input[type="number"],
        select {
            width: 92%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14.5px;
            transition: border-color 0.3s;
        }
        select {
            width: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
            background-color: white;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
            background-color: white;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        #imagePreview {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #007bff;
            display: block;
            margin: 0 auto 20px;
        }

        .success-message {
            color: green;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        /* CSS for Reset Form button */
button[type="button1"] {
    background-color: #ff4d4d; /* Light red background */
    color: #fff; /* White text color */
    border: none; /* Remove border */
    border-radius: 5px; /* Rounded corners */
    padding: 5px 10px; /* Padding for size */
    font-size: 16px; /* Font size */
    cursor: pointer; /* Pointer cursor on hover */
    transition: background-color 0.3s ease; /* Smooth background color transition */
    width: 20%;
    margin-left: 0px;
    margin-bottom: 5px;
}

button[type="button1"]:hover {
    background-color: #cc0000; /* Darker red on hover */
}

    </style>
</head>
<body>
<div class="form-container">
    <h2>PERSONNEL INFORMATION</h2>
    <hr>
    <button type="button1" onclick="confirmAndResetForm()">Reset</button> <!-- Reset button with confirm -->

    


   

    <form id="studentForm" action="submit_faculty.php" method="post" enctype="multipart/form-data" onsubmit="showLoadingSpinner()">
        <div class="label-group">
            <label for="ProfilePicture">Dashboard Profile Picture:</label>
            <input type="file" id="ProfilePicture" name="ProfilePicture" accept="image/*" onchange="previewImage(event)" required>
            <img id="imagePreview">
        </div>
        <div class="label-group">
            <p>ID Number:</p>
            <input type="text" id="IDNumber" name="IDNumber" required placeholder="Enter your ID number" min="10000" max="99999" maxlength="5" oninput="validateIDNumber()" readonly>
        </div>
        <div class="label-group">
    <label for="Rank">Academic Rank:</label>
    <select id="Rank" name="Rank" required onchange="handleRankAndDepartmentChange()">
        <option value="">Select Rank</option>
        <option value="NONE">NONE</option>
        <option value="Instructor">Instructor</option>
        <option value="Assistant Professor">Assistant Professor</option>
        <option value="Associate Professor">Associate Professor</option>
        <option value="Professor">Professor</option>
      
    </select>
</div>

        <div id="loadingSpinner" class="loading-spinner" style="display: none;">
        <div class="spinner"></div> <!-- Spinner element -->
    </div>
        <div class="label-group">
            <label for="FirstName">First Name:</label>
            <input type="text" id="FirstName" name="FirstName" required placeholder="Enter your first name">
        </div>
        <div class="label-group">
            <label for="LastName">Last Name:</label>
            <input type="text" id="LastName" name="LastName" required placeholder="Enter your last name">
        </div>
        <div class="label-group">
            <p>Gmail Account:</p>
            <input type="email" id="GmailAccount" name="GmailAccount" required placeholder="Enter your SKSU account" oninput="validateEmail()" readonly>
            <small id="emailError" style="color: red; display: none;">Please enter a valid SKSU email (e.g., example@sksu.edu.ph).</small>
        </div>

      
        <div class="label-group">
            <label for="Department">Department:</label>
            <select id="Department" name="Department" required onchange="handleRankAndDepartmentChange()">
                <option value="">Select Department</option>
                <option value="NONE">NONE</option>
                <option value="CSS">CSS</option>
                <option value="ESO">ESO</option>
                <option value="NABA">NABA</option>
            </select>
        </div>
        
        <div class="label-group">
            <label for="Position">Position:</label>
            <select id="Position" name="Position" required>
                <option value="">Select Position</option>
                <option value="Faculty">Faculty</option>
                <option value="Staff">Staff</option>
            </select>
        </div>
        <button type="submit">Submit Information</button>
       
    </form>
</div>



<Style>
    /* Spinner styles */
.loading-spinner {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: none;
}

.spinner {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #000;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

</Style>

<script>
    // Function to show the loading spinner when the form is submitted
function showLoadingSpinner() {
    document.getElementById("loadingSpinner").style.display = "block"; // Show the spinner
}

// Optional: You can hide the spinner after the form is successfully submitted if needed
// Example: after successful submission, hide it
function hideLoadingSpinner() {
    document.getElementById("loadingSpinner").style.display = "none"; // Hide the spinner
}

function confirmAndResetForm() {
    if (confirm("Are you sure you want to reset the form?")) {
        const form = document.getElementById("studentForm");

        // Reset the form fields except IDNumber and GmailAccount
        const inputs = form.querySelectorAll("input, select");
        inputs.forEach(input => {
            if (input.id !== "IDNumber" && input.id !== "GmailAccount") {
                if (input.type === "file") {
                    input.value = ""; // Clear file input
                    document.getElementById("imagePreview").src = ""; // Clear image preview
                } else if (input.type === "checkbox" || input.type === "radio") {
                    input.checked = false; // Reset checkboxes and radio buttons
                } else {
                    input.value = ""; // Reset other inputs
                }
            }
        });

        // Reset selects to their default option
        const selects = form.querySelectorAll("select");
        selects.forEach(select => {
            if (select.id !== "IDNumber" && select.id !== "GmailAccount") {
                select.selectedIndex = 0;
            }
        });

        // Hide the success message if it's displayed
        document.getElementById("successMessage").style.display = "none";
    }
}

    function handleRankAndDepartmentChange() {
        const rankSelect = document.getElementById("Rank");
        const departmentSelect = document.getElementById("Department");
        const positionSelect = document.getElementById("Position");
        const restrictedPositions = ["Faculty", "Dean", "Program Chair"];

        // Handle Rank change - set Department to NONE if Rank is NONE
        if (rankSelect.value === "NONE") {
            departmentSelect.value = "NONE";
            departmentSelect.disabled = true;
        } else {
            departmentSelect.disabled = false;
        }

        // Handle Department change - disable certain positions if Department is NONE
        if (departmentSelect.value === "NONE") {
            restrictedPositions.forEach(positionValue => {
                const option = [...positionSelect.options].find(opt => opt.value === positionValue);
                if (option) option.disabled = true;
            });
        } else {
            restrictedPositions.forEach(positionValue => {
                const option = [...positionSelect.options].find(opt => opt.value === positionValue);
                if (option) option.disabled = false;
            });
        }
    }
</script>


    <script>


  // Display success message if the 'success' parameter is in the URL
  window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === 'true') {
                document.getElementById('successMessage').style.display = 'block';
            }
        };
         // Function to get URL parameters
         function getUrlParameter(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        // Automatically fill IDNumber and GmailAccount from URL parameters
        window.onload = function() {
            const IDNumber = getUrlParameter('IDNumber');
            const GmailAccount = getUrlParameter('GmailAccount');

            if (IDNumber) {
                document.getElementById('IDNumber').value = IDNumber;
            }

            if (GmailAccount) {
                document.getElementById('GmailAccount').value = GmailAccount;
            }
        };
        document.getElementById('ProfilePicture').addEventListener('change', function(event) {
            const imagePreview = document.getElementById('imagePreview');
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function() {
                imagePreview.src = reader.result; // Set the src of the image
                imagePreview.style.display = 'block'; // Show the image
            }

            if (file) {
                reader.readAsDataURL(file); // Read the file as a data URL
            }
        });

        // Show success message if the 'success' parameter is present in the URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            document.getElementById('successMessage').style.display = 'block';
        }

        function validateIDNumber() {
    var idInput = document.getElementById('IDNumber');
    
    // Ensure that only 5 digits are allowed
    if (idInput.value.length > 5) {
        idInput.value = idInput.value.slice(0, 5);
    }
}
 // Function to get URL parameters
 function getUrlParameter(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        // Automatically fill IDNumber and GmailAccount from URL parameters
        window.onload = function() {
            const IDNumber = getUrlParameter('IDNumber');
            const GmailAccount = getUrlParameter('GmailAccount');

            if (IDNumber) {
                document.getElementById('IDNumber').value = IDNumber;
            }

            if (GmailAccount) {
                document.getElementById('GmailAccount').value = GmailAccount;
            }
        };
function validateEmail() {
    var emailInput = document.getElementById('GmailAccount');
    var emailError = document.getElementById('emailError');
    var validDomain = "@sksu.edu.ph";

    // Check if the email ends with @sksu.edu.ph
    if (emailInput.value.endsWith(validDomain)) {
        emailError.style.display = "none"; // Hide error message
        emailInput.setCustomValidity("");  // Clear custom validity
    } else {
        emailError.style.display = "block"; // Show error message
        emailInput.setCustomValidity("Invalid email domain"); // Set custom validity
    }
}
    </script>
</body>
</html>
