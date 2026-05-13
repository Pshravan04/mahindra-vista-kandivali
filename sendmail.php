<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name  = trim(strip_tags($_POST['name']  ?? ''));
    $email = trim(strip_tags($_POST['email'] ?? ''));
    $country_code = trim(strip_tags($_POST['country_code'] ?? ''));
    $phone_num    = trim(strip_tags($_POST['phone'] ?? ''));
    $phone = $country_code . ' ' . $phone_num;
    $config = trim(strip_tags($_POST['config'] ?? 'Not Specified'));

    $name  = substr($name,  0, 100);
    $email = substr($email, 0, 100);
    $phone = substr($phone, 0, 30);
    $config = substr($config, 0, 50);

    $phone_digits = preg_replace('/[^\d+]/', '', $phone);
    if (strlen($phone_digits) < 7 || strlen($phone_digits) > 15) {
        http_response_code(400);
        echo "Invalid phone number.";
        exit();
    }

    $spam_patterns = [
        '/https?:\/\//i',
        '/yandex\./i',
        '/t\.me\//i',
        '/bit\.ly\//i',
        '/wa\.me\//i',
        '/poll\//i',
        '/sex/i',
        '/dating/i',
        '/casino/i',
        '/loan.*whatsapp/i',
    ];
    foreach ($spam_patterns as $pattern) {
        if (preg_match($pattern, $name) || preg_match($pattern, $email)) {
            header("Location: thankyou.html");
            exit();
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Invalid email address.";
        exit();
    }

    if (!preg_match('/^[\p{L}\s.\-\']{2,100}$/u', $name)) {
        http_response_code(400);
        echo "Invalid name.";
        exit();
    }

    $safe_name  = htmlspecialchars($name,  ENT_QUOTES, 'UTF-8');
    $safe_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safe_phone = htmlspecialchars($phone_digits, ENT_QUOTES, 'UTF-8');
    $safe_config = htmlspecialchars($config, ENT_QUOTES, 'UTF-8');

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rock83694@gmail.com';
        $mail->Password   = 'eigvmkokcvihyboz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('rock83694@gmail.com', 'Website Lead');
        $mail->addAddress('tgmmayur@gmail.com');
        $mail->addAddress('thegrowthmonks@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'New Lead - Mahindra Vista Kandivali East';
        $mail->Body = "
            <h2>New Lead Submission</h2>
            <p><strong>Name:</strong> {$safe_name}</p>
            <p><strong>Email:</strong> {$safe_email}</p>
            <p><strong>Phone:</strong> {$safe_phone}</p>
            <p><strong>Interested Configuration:</strong> {$safe_config}</p>
            <p><strong>Project:</strong> Mahindra Vista Kandivali</p>
        ";

        if ($mail->send()) {
            $webhook_url = "https://script.google.com/macros/s/AKfycbzd74AhHTxvin9tj3UNcnnqhAdy86VSTxKeQkOkhXnlFa9UCXzaKaM6VGiQSFHS1O6XOg/exec?gid=0";

            $payload = json_encode([
                "name"         => $safe_name,
                "email"        => $safe_email,
                "phone"        => $safe_phone,
                "config"       => $safe_config,
                "source"       => "Mahindra Vista Kandivali Website",
                "submitted_at" => date("Y-m-d H:i:s")
            ]);

            $ch = curl_init($webhook_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);

            header("Location: thankyou.html");
            exit();
        }

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        header("Location: thankyou.html");
        exit();
    }

} else {
    http_response_code(405);
    header("Location: index.html");
    exit();
}
