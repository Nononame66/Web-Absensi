<?php
session_start();

// Database configuration
$db_host = 'localhost';
$db_name = 'absensi_siswa';
$db_user = 'root';
$db_pass = '';

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);

    // Set error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Set timezone for database
    $conn->exec("SET time_zone = '+07:00'");
} catch (PDOException $e) {
    // Display connection error
    die("Database Connection Failed: " . $e->getMessage());
}

// Set timezone for PHP (WIB - Waktu Indonesia Barat)
// Yogyakarta, Jakarta, Bandung, Semarang menggunakan WIB (UTC+7)
date_default_timezone_set('Asia/Jakarta');

// Function to check if user is logged in as admin
function isAdminLoggedIn()
{
    return isset($_SESSION['admin_id']);
}

// Function to check if user is logged in as student
function isSiswaLoggedIn()
{
    return isset($_SESSION['siswa_id']);
}

// Helper function to sanitize input
function sanitize($data)
{
    return htmlspecialchars(trim($data));
}

// Helper function to get real client IP
function get_client_ip()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
        
    // Normalize localhost IPv6
    if ($ipaddress == '::1') {
        $ipaddress = '127.0.0.1';
    }
    
    return trim($ipaddress);
}

// Helper function to generate random string
function random_string($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $length > $i; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Function to log activity (simplified)
function log_activity($user_type, $user_id, $activity_type, $description, $conn)
{
    $sql = "INSERT INTO activity_log (user_type, user_id, activity_type, description) 
            VALUES (:user_type, :user_id, :activity_type, :description)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'user_type' => $user_type,
        'user_id' => $user_id,
        'activity_type' => $activity_type,
        'description' => $description
    ]);
}

// SECURITY: Include security helper functions
require_once __DIR__ . '/security_helpers.php';
