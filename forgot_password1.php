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
        }
        input {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        #message {
            margin-top: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Forgot Password</h1>
        <form id="forgotPasswordForm">
            <input type="email" id="GmailAccount" name="GmailAccount" placeholder="Enter Gmail Account" required />
            <input type="password" id="newPassword" name="newPassword" placeholder="Enter New Password" required />
            <button type="submit">Reset Password</button>
        </form>
        <p id="message"></p>
    </div>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting traditionally

            const GmailAccount = document.getElementById('GmailAccount').value;
            const newPassword = document.getElementById('newPassword').value;
            const messageElement = document.getElementById('message');

            // Validate the Gmail account
            const gmailRegex = /^[a-zA-Z0-9._%+-]+@sksu\.edu\.ph$/; // Match @sksu.edu.ph domain
            if (!gmailRegex.test(GmailAccount)) {
                messageElement.textContent = 'Please enter a valid Gmail account (e.g., yourfullname@sksu.edu.ph).';
                messageElement.style.color = 'red';
                return;
            }

            // Validate password length
            if (newPassword.length < 8) {
                messageElement.textContent = 'Password must be at least 8 characters long.';
                messageElement.style.color = 'red';
                return;
            }

            // Send the reset password request to the server using Fetch API
            fetch('forgot_password_backend1.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    GmailAccount: GmailAccount,
                    newPassword: newPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageElement.textContent = 'Password successfully reset!';
                    messageElement.style.color = 'green';
                    setTimeout(() => {
                        window.location.href = 'login_faculty.html'; // Redirect to login page after reset
                    }, 2000);
                } else {
                    messageElement.textContent = 'Error: ' + data.message;
                    messageElement.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageElement.textContent = 'An error occurred. Please try again.';
                messageElement.style.color = 'red';
            });
        });
    </script>
</body>
</html>
