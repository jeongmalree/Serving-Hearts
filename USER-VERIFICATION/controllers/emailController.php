<?php

require_once 'vendor/autoload.php';
require_once 'config/constants.php';

// Create the Transport
$transport = (new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl'))
    ->setUsername(EMAIL)
    ->setPassword(PASSWORD)
    ->setStreamOptions([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);


// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);

$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(1, 1));
$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin(new Swift_Plugins_Loggers_ArrayLogger()));


function sendVerificationEmail($userEmail, $token) 
{
    global $mailer;
    $body = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Verify Email</title>
        </head>
        <body>
            <div class="wrapper">
                <p>
                    Thank you for registering and Welcome to Serving Hearts! <br><br>

                    We are thrilled to have you join our community of compassionate individuals dedicated to saving lives through blood donation. <br>
                    Your commitment to this vital cause helps ensure that patients in need receive the life-saving blood they require. <br>
                    Together, we can make a significant impact and bring hope to countless families. Thank you for choosing to be a part of Serving Hearts <br><br>
                    – your generosity and kindness truly make a difference!
                </p>
                <a href="http://localhost/Serving%20Hearts/USER-VERIFICATION/index.php?token=' . $token .'">
                    --> Verify your email address here <--
                </a>
            </div>
        </body>
        </html>';

    // Create a message
    $message = (new Swift_Message('Verify your Email Address'))
        ->setFrom(EMAIL)
        ->setTo($userEmail)
        ->setBody($body, 'text/html');

    // Send the message
    $result = $mailer->send($message);
}


function sendPasswordResetLink($userEmail, $token) {
    global $mailer, $conn;

    // Check if the user exists in the database
    $sql = "SELECT * FROM users WHERE email='$userEmail' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        // Update the user's reset token generated timestamp
        $update_sql = "UPDATE users SET reset_token_generated_at = NOW() WHERE email='$userEmail'";
        mysqli_query($conn, $update_sql);
    }

    $body = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Verify Email</title>
        </head>
        <body>
            <div class="wrapper">
                <p>
                    We received your concern about your password <br><br>

                    Please click on the link below to reset your password and to have access to your account again.
                </p>
                <a href="http://localhost/Serving%20Hearts/USER-VERIFICATION/index.php?password-token=' . $token .'">
                    Verify your email address here
                </a>
            </div>
        </body>
        </html>';

    // Create a message
    $message = (new Swift_Message('Reset your Password'))
        ->setFrom(EMAIL)
        ->setTo($userEmail)
        ->setBody($body, 'text/html');

    // Send the message
    $result = $mailer->send($message);
}
