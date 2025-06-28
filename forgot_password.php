<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-align: center;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }
        input {
            width: 90%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        #message {
            margin-top: 15px;
            font-size: 14px;
            color: red; /* Default color for error */
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Forgot Password</h1>
        <form id="forgotPasswordForm">
            <!-- Email Input Section -->
            <div id="emailSection">
                <input type="email" id="GmailAccount" name="GmailAccount" placeholder="Enter Gmail Account" required />
            </div>
            <!-- OTP Input Section -->
            <div id="otpSection" class="hidden">
                <input type="text" id="otp" name="otp" placeholder="Enter OTP" maxlength="6" required />
            </div>
            <!-- Password Input Section -->
            <div id="passwordSection" class="hidden">
                <input type="password" id="newPassword" name="newPassword" placeholder="Enter New Password (Min. 8 characters)" minlength="8" required />
            </div>
            <!-- Submit Button -->
            <button type="submit" id="submitButton">Send OTP</button>
        </form>
        <p id="message"></p>
    </div>

    <script>
        const form = document.getElementById('forgotPasswordForm');
        const messageElement = document.getElementById('message');
        const emailSection = document.getElementById('emailSection');
        const otpSection = document.getElementById('otpSection');
        const passwordSection = document.getElementById('passwordSection');
        const submitButton = document.getElementById('submitButton');

        let step = 1; // Tracks the current step in the process

        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const GmailAccount = document.getElementById('GmailAccount').value.trim();
            const otp = document.getElementById('otp')?.value.trim();
            const newPassword = document.getElementById('newPassword')?.value.trim();

            if (step === 1) {
                // Step 1: Send OTP
                if (!validateEmail(GmailAccount)) {
                    displayMessage('Invalid Gmail account format. Please use a valid @sksu.edu.ph email.', 'red');
                    return;
                }

                fetch('forgot_password_backend.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ GmailAccount })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayMessage('OTP sent to your email. Please enter it below.', 'green');
                            moveToStep(2);
                        } else {
                            displayMessage(data.message || 'Failed to send OTP. Please try again.', 'red');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        displayMessage('An error occurred. Please try again later.', 'red');
                    });
            } else if (step === 2) {
                // Step 2: Verify OTP and Reset Password
                if (!otp || !newPassword) {
                    displayMessage('Please provide both OTP and a new password.', 'red');
                    return;
                }

                if (newPassword.length < 8) {
                    displayMessage('Password must be at least 8 characters long.', 'red');
                    return;
                }

                fetch('forgot_password_backend.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ GmailAccount, otp, newPassword })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayMessage('Password successfully reset! Redirecting...', 'green');
                            setTimeout(() => {
                                window.location.href = 'login.html';
                            }, 2000);
                        } else {
                            displayMessage(data.message || 'Failed to reset password. Please try again.', 'red');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        displayMessage('An error occurred. Please try again later.', 'red');
                    });
            }
        });

        function validateEmail(email) {
            const gmailRegex = /^[a-zA-Z0-9._%+-]+@sksu\.edu\.ph$/;
            return gmailRegex.test(email);
        }

        function displayMessage(message, color) {
            messageElement.textContent = message;
            messageElement.style.color = color;
        }

        function moveToStep(nextStep) {
            step = nextStep;
            if (step === 2) {
                otpSection.classList.remove('hidden');
                passwordSection.classList.remove('hidden');
                submitButton.textContent = 'Reset Password';
            }
        }
    </script>
</body>
</html>
