<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Clinic DBMS - Emergency Contacts</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <style>
        /* General reset and font setup */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            background-image: url('image.jpeg');
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            color: #333;
        }

        header {
            background-color: green;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 30px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 150px;
            border-radius: 10px;
            width: 100%;
            font-size: 23px;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 20px;
        }

        .main-content {
            width: 100%;
            max-width: 900px;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        #backButton {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: background-color 0.3s, transform 0.2s;
            font-size: 16px;
        }

        #backButton:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        #backButton:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .contact-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 10px 0;
            padding: 15px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .contact-card:hover {
            transform: scale(1.02);
        }

        .contact-icon {
            font-size: 2.5em;
            margin-right: 20px;
            color: #007bff;
        }

        .contact-info {
            flex: 1;
        }

        .contact-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .contact-number {
            font-size: 1.1em;
            color: #555;
            text-decoration: none;
        }

        .contact-number:hover {
            text-decoration: underline;
            color: #007bff;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            h1 {
                font-size: 2em;
            }

            .contact-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .contact-icon {
                margin-bottom: 10px;
            }
        }

    </style>
</head>
<body>

    <div class="container">
        <header>
            <h1>EMERGENCY CONTACTS</h1>
        </header>

        <div class="main-content">
            <div class="contact-card">
                <div class="contact-icon">🏥</div>
                <div class="contact-info">
                    <div class="contact-name">School Nurse</div>
                    <a href="tel:09773028345" class="contact-number">09773028345</a>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">🩹</div>
                <div class="contact-info">
                    <div class="contact-name">Red Cross SK</div>
                    <a href="tel:09453141978" class="contact-number">09453141978</a>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">🏥</div>
                <div class="contact-info">
                    <div class="contact-name">SKPH</div>
                    <a href="tel:09050432504" class="contact-number">09050432504</a>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">🚒</div>
                <div class="contact-info">
                    <div class="contact-name">BFP Station</div>
                    <a href="tel:09215066641" class="contact-number">09215066641</a>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">🚓</div>
                <div class="contact-info">
                    <div class="contact-name">Police Station</div>
                    <a href="tel:09531024582" class="contact-number">09531024582</a>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">👨‍⚕️</div>
                <div class="contact-info">
                    <div class="contact-name">Health Director</div>
                    <a href="tel:09177939655" class="contact-number">09177939655</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
