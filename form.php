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
    <title>Student Information Form</title>
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



/* Styling for Birthdate dropdowns */
#Month, #Day, #Year {
    width: 32%;
    padding: 8px;
    margin-right: -0.1%;
    font-size: 0.70em;
    color: black;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: white;
    transition: border-color 0.3s ease;
  
}

#Month:focus, #Day:focus, #Year:focus {
    border-color: #007bff;
    outline: none;
    background-color: white;
}


/* Hover effect */
#Month:hover, #Day:hover, #Year:hover {
    border-color: #007bff;
    cursor: pointer;
}

/* Placeholder styling for empty dropdown options */
#Month option:first-child, #Day option:first-child, #Year option:first-child {
    color: #888;
}

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
    margin-bottom: 5px;
 
}

button[type="button1"]:hover {
    background-color: #cc0000; /* Darker red on hover */
}
    </style>
    <script>
        function previewImage(event) {
            const imagePreview = document.getElementById('imagePreview');
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function(){
                imagePreview.src = reader.result; // Set the src of the image
                imagePreview.style.display = 'block'; // Show the image
            }

            if (file) {
                reader.readAsDataURL(file); // Read the file as a data URL
            }
        }

        function calculateAge() {
            const month = parseInt(document.getElementById('Month').value, 10);
            const day = parseInt(document.getElementById('Day').value, 10);
            const year = parseInt(document.getElementById('Year').value, 10);

            if (month && day && year) {
                const birthDate = new Date(year, month - 1, day); // Month is 0-indexed
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--; // Adjust age if birthday hasn't occurred yet this year
                }
                document.getElementById('Age').value = age; // Set the age field
            }
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>STUDENT INFORMATION</h2>
        <hr>
        <button type="button1" onclick="confirmAndResetForm()">Reset</button> <!-- Reset button with confirm -->
        <form id="studentForm" action="submit_information.php" method="post" enctype="multipart/form-data">
            <!-- Profile Picture Upload -->
            <div class="label-group">
                <label for="ProfilePicture">Dashboard Profile Picture:</label>
                <input type="file" id="ProfilePicture" name="ProfilePicture" accept="image/*" onchange="previewImage(event)" required>
                <img id="imagePreview">
            </div>
            <div id="loadingSpinner" class="loading-spinner" style="display: none;">
        <div class="spinner"></div> <!-- Spinner element -->
    </div>
            <div class="label-group">
                <label for="FirstName">First Name:</label>
                <input type="text" name="FirstName" id="FirstName" required placeholder="Enter your first name">
            </div>

            <!-- Last Name -->
            <div class="label-group">
                <label for="LastName">Last Name:</label>
                <input type="text" name="LastName" id="LastName" required placeholder="Enter your last name">
            </div>

            <!-- ID Number -->
            <div class="label-group">
            <label for="IDNumber">ID Number:</label>
    <input type="number" id="IDNumber" name="IDNumber" required placeholder="Enter your ID number" 
           min="10000" max="99999" maxlength="5" oninput="validateIDNumber()" readonly>
</div>

<!-- Gmail Account -->
<div class="label-group">
<label for="GmailAccount">Gmail Account:</label>
    <input type="email" id="GmailAccount" name="GmailAccount" required placeholder="Enter your SKSU account" 
           oninput="validateEmail()" readonly>
    <small id="emailError" style="color: red; display: none;">Please enter a valid SKSU email (e.g., example@sksu.edu.ph).</small>
</div>

 <!-- Birthdate -->
 <div class="label-group">
                <label for="Birthdate">Birthdate:</label>
                <div style="display: flex; justify-content: space-between;">
                    <select id="Month" name="Month" required onchange="calculateAge()">
                        <option value="">Month</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                        <?php endfor; ?>
                    </select>
                    
                    <select id="Day" name="Day" required onchange="calculateAge()">
                        <option value="">Day</option>
                        <?php for ($d = 1; $d <= 31; $d++): ?>
                            <option value="<?php echo str_pad($d, 2, '0', STR_PAD_LEFT); ?>"><?php echo $d; ?></option>
                        <?php endfor; ?>
                    </select>
                    
                    <select id="Year" name="Year" required onchange="calculateAge()">
                        <option value="">Year</option>
                        <?php 
                        $currentYear = date('Y');
                        for ($y = $currentYear; $y >= 1900; $y--): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- Gender -->
            <div class="label-group">
                <label for="Gender">Gender:</label>
                <select id="Gender" name="Gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <!-- Course -->
            <div class="label-group">
                <label for="Course">Course:</label>
                <select id="Course" name="Course" required>
                    <option value="">Select Course</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Information Technology">Information Technology</option>
                    <option value="Information System">Information System</option>
                    <option value="Industrial Technology">Industrial Technology</option>
                    <option value="Civil Engineering">Civil Engineering</option>
                    <option value="Computer Engineering">Computer Engineering</option>
                    <option value="Electrical Engineering">Electrical Engineering</option>
                    <option value="Automotive Technology">Automotive Technology</option>
                    <option value="Technical Vocational Education">Technical Vocational Education</option>
                </select>
            </div>

            <!-- Year -->
            <div class="label-group">
                <label for="Yr">Year:</label>
                <select id="Yr" name="Yr" required>
                    <option value="">Select Year</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </div>

            <!-- Section -->
            <div class="label-group">
                <label for="Section">Section:</label>
                <select id="Section" name="Section">
                    <option value="">Select Section</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="E">E</option>
                    <option value="F">F</option>
                    <option value="G">G</option>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit">Submit Information</button>
        </form>
    </div>

   
</body>


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
            function validateIDNumber() {
    var idInput = document.getElementById('IDNumber');
    
    // Ensure that only 5 digits are allowed
    if (idInput.value.length > 5) {
        idInput.value = idInput.value.slice(0, 5);
    }
}

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
