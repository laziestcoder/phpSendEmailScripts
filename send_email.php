<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Function to parse the environments.txt file
function parseEnvironmentFile($filePath) {
    $env = [];
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }

        list($name, $value) = explode('=', $line, 2);
        $env[trim($name)] = trim($value);
    }

    return $env;
}

function sendEmailWithAttachments($env) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $env['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $env['smtp_username'];
        $mail->Password = $env['smtp_password'];
        $mail->SMTPSecure = $env['smtp_secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $env['smtp_port'];

        // Recipients
        $mail->setFrom($env['from_email'], $env['from_name']);
        $mail->addAddress($env['recipient_email']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $env['subject'];
        $mail->Body = $env['body'];
        $mail->AltBody = strip_tags($env['body']);

        // Attachments
        if (is_dir($env['directory'])) {
            $files = glob($env['directory'] . '/*sql*');
	    foreach ($files as $file) {
                if (is_file($file)) {
                    $mail->addAttachment($file);
                }
            }
        }

        // Send the email
        $mail->send();
        echo "Message has been sent.\n";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
    }
}

// Check if the script is being run from the command line
if (php_sapi_name() == 'cli') {
    if ($argc !== 2) {
        //echo "Usage: php send_email.php <environment_file>\n";
	//exit(1);
	$argv[1] = "environments.txt";
    }

    $envFilePath = $argv[1];
    if (!file_exists($envFilePath)) {
        echo "Environment file not found: $envFilePath\n";
	exit(1);
    }

    $env = parseEnvironmentFile($envFilePath);
    sendEmailWithAttachments($env);
} else {
    echo "This script must be run from the command line.";
}

