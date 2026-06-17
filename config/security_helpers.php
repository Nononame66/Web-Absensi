<?php
/**
 * Security Helper Functions
 * Contains IP validation and other security-related functions
 */

// Allowed IP addresses for attendance submission
define('ALLOWED_IPS', [
    '125.163.149.128'
]);

/**
 * Check if the current IP is allowed to submit attendance
 * @return bool True if IP is allowed, false otherwise
 */
function is_ip_allowed_for_attendance()
{
    $client_ip = get_client_ip();
    
    // Check if IP is in the allowed list
    return in_array($client_ip, ALLOWED_IPS);
}

/**
 * Validate IP for attendance submission
 * Throws exception if IP is not allowed
 * @throws Exception if IP is not allowed
 */
function validate_attendance_ip()
{
    if (!is_ip_allowed_for_attendance()) {
        $client_ip = get_client_ip();
        throw new Exception("Absensi hanya dapat dilakukan dari lokasi yang telah ditentukan. IP Anda: {$client_ip}");
    }
}

/**
 * Get formatted error message for IP restriction
 * @return string Error message
 */
function get_ip_restriction_message()
{
    $client_ip = get_client_ip();
    return "Maaf, absensi hanya dapat dilakukan dari lokasi sekolah. IP Anda saat ini: {$client_ip}";
}
