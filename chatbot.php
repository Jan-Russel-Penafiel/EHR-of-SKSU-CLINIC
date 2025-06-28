
    <?php
    session_start(); // Start the session
    
    // Check if the user is logged in
    if (!isset($_SESSION['GmailAccount'])) {
        header('Location: login.html'); // Redirect to login page if not logged in
        exit();
    }
    
    // Database connection
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");
    
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Get the user's email from the session
    $GmailAccount = $_SESSION['GmailAccount'];
    
    // Query to retrieve user information from the personal_info table
    $queryPersonalInfo = "SELECT Num, FirstName, LastName FROM personal_info WHERE GmailAccount = '$GmailAccount'";
    $resultPersonalInfo = mysqli_query($conn, $queryPersonalInfo);
    
    if ($resultPersonalInfo && mysqli_num_rows($resultPersonalInfo) > 0) {
        // Fetch the user's personal information
        $userInfo = mysqli_fetch_assoc($resultPersonalInfo);
        $FirstName = $userInfo['FirstName'];
        $LastName = $userInfo['LastName'];
        $Num = $userInfo['Num'];
    } else {
        $FirstName = "User";
        $LastName = "";
        $Num = "N/A";
    }
    
    // Process chatbot messages
    if (isset($_POST['message'])) {
        $userMessage = strtolower(trim($_POST['message']));
        $botResponse = "";
    
        // Illnesses and corresponding treatments
        $illnesses = [
            'flu' => 'Take Paracetamol and get plenty of rest and stay hydrated. You may take antiviral medication if prescribed by a doctor. If symptoms persist, visit the clinic😊.',
            'colds' => 'Take Neuzep and drink plenty of fluids, get rest, and use decongestants or saline nasal sprays. If symptoms worsen, visit the clinic😊.',
            'headache' => 'Take Mefenamic or paracetamol for relief. Rest in a quiet room and avoid stress. If it persists, visit the clinic😊.',
            'any kind of stomachache' => 'Take Loperamide avoid solid food for a few hours, drink clear liquids, and try to rest. If the pain is severe or persistent, visit the clinic😊.',
            'allergy' => 'Take Ceterizine for relief. Avoid allergens if possible. If symptoms are severe, visit the clinic for further evaluation😊.',
            'hyper acidity' => 'Take Antacid and stay upright for 30 minutes then avoid spicy, fatty, and acidic foods and lastly drink water regularly. If it persists, visit the clinic😊.',
            'diarrhea' => 'Take Loperamide and stay hydrated by drinking clear fluids, and avoid fatty foods. If diarrhea persists for more than 48 hours, visit the clinic.😊',
            'cough' => 'Take lagundi and drink warm fluids like water or tea then avoid cold drinks and irritants and lastly Rest and stay hydrated.If it persists, visit the clinic😊.'
        ];
        
        // Logic to handle different user inputs
        if (strpos($userMessage, 'hello') !== false || strpos($userMessage, 'hi') !== false) {
            $botResponse = "Hello, $FirstName! What is your concern? Please type 'consultation' or 'checkup'";
        } elseif (strpos($userMessage, 'consultation') !== false || strpos($userMessage, 'checkup') !== false) {
            // Ask for the illness name
            $botResponse = "Please let me know what illness you're experiencing. Options: flu, colds, headache, any kind stomachache, allergy, hyper acidity, diarrhea, cough.";
        } elseif (array_key_exists($userMessage, $illnesses)) {
            // Provide treatment based on illness
            $botResponse = "This is the treatment for your illness " . $illnesses[$userMessage] . " Please type 'thank you' or 'okay' for further assistance.";
        } elseif (strpos($userMessage, 'thank you') !== false || strpos($userMessage, 'okay') !== false) {
            // Provide follow-up after the illness treatment and thank you message
            $botResponse = "You're welcome! Please visit me at our School clinic if you have class, and we will check your temperature and blood pressure there😊" . "\n
             Please type 'bye' if you like to end our conversation.";
        } elseif (strpos($userMessage, 'bye') !== false) {
            $botResponse = "Thank you for reaching out on me I hope my medication to your illness help to relieve your felling right now. Have a nice day SKSUans and take care for health!😊";
        } elseif (!array_key_exists($userMessage, $illnesses)) {
            // Illness not recognized message
            $botResponse = "I'm sorry, I don't have information on that illness. Please visit the clinic for proper consultation, or try again with one of the following options: flu, colds, headache, stomachache, allergy, chickenpox, diarrhea, sprain. 
            Or visit your physician or hospitals for your illness because we dont have enough treatment for that illness I'm Sorry😭";
        } else {
            $botResponse = "Sorry, I didn't understand that. Can you please rephrase?";
        }
        
        echo json_encode(['response' => $botResponse]);
        exit();
    }
    
    ?>
    

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation and Treatment Chatbot</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            background-color: lightblue;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }


body {
    background-image: url(image.jpeg);
    background-repeat: no-repeat;
    background-size: 100% 100%;
    background-attachment: fixed;
    
}

        .chat-container {
            background-color: lightblue;
            width: 320px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            margin-top: -50px;
        }

        .chat-container h2 {
            text-align: center;
            color: #343a40;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .chat-box {
            border-radius: 10px;
            background-color: #f1f3f5;
            padding: 15px;
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 10px;
            max-height: 400px;
            border: 1px solid #dee2e6;
        }

        .chat-box .message {
            margin-bottom: 15px;
            font-family: 'Fira Code', monospace;
        }

        .chat-box .message.user {
            text-align: right;
            color: #007bff;
            font-weight: 500;
        }

        .chat-box .message.bot {
            text-align: left;
            color: #28a745;
            font-weight: 500;
        }

        .input-container {
            display: flex;
            align-items: lefts;
            gap: 10px;
        }

        .input-container input {
            flex-grow: 1;
            padding: 10px 15px;
            border-radius: 30px;
            border: 1px solid #ced4da;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .input-container input:focus {
            border-color: #007bff;
        }

        .input-container button {
            padding: 10px 15px;
            border-radius: 5px;
            border: none;
            background-color: #007bff;
            color: white;
            font-size: 12.5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .input-container button:hover {
            background-color: #0056b3;
        }

        /* Scrollbar styling */
        .chat-box::-webkit-scrollbar {
            width: 8px;
        }

        .chat-box::-webkit-scrollbar-thumb {
            background-color: #6c757d;
            border-radius: 10px;
        }

        .chat-box::-webkit-scrollbar-track {
            background-color: #f8f9fa;
        }
        #backButton {
            display: inline-block; /* Allows padding and margin */
            background-color: #007bff; /* Blue background */
            color: white; /* White text */
            padding: 10px 15px; /* Padding around the text */
            font-size: 12px; /* Font size */
            border-radius: 5px; /* Rounded corners */
            text-decoration: none; /* Remove underline */
            transition: background-color 0.3s, transform 0.2s; /* Transition effects */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
            margin: 10px; /* Margin for spacing */
            width: 21%;
            margin-top: -5px;
            margin-left: -0.2px;
        }

        #backButton:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        #backButton:active {
            transform: translateY(0); /* Reset lift effect on click */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Reduce shadow on click */
        }
    </style>
</head>
<body>

<div class="chat-container">

    <h2>CONSULATION CHATBOT🤖</h2>
    <div class="chat-box" id="chatBox">
        <p>Please interact with me😊 Please Type "hi" or "hello".</p>
    </div>
    <div class="input-container">
        <input type="text" id="userInput" placeholder="Type your message..." autofocus>
        
        <button onclick="sendMessage()">Send</button>
        
    </div>
</div>

<script>
function sendMessage() {
    const userInput = document.getElementById('userInput').value;

    if (userInput.trim() !== "") {
        // Display user message
        const chatBox = document.getElementById('chatBox');
        chatBox.innerHTML += `<div class="message user">${userInput}</div>`;

        // Send the message to the chatbot
        fetch('chatbot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `message=${encodeURIComponent(userInput)}`,
        })
        .then(response => response.json())
        .then(data => {
            // Display bot response
            chatBox.innerHTML += `<div class="message bot">${data.response}</div>`;
            chatBox.scrollTop = chatBox.scrollHeight; // Scroll to the bottom
        });

        // Clear input field
        document.getElementById('userInput').value = "";
    }
}
</script>

</body>
</html>
