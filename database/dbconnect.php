<?php
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

//Kronos DB configuration, environment variables if local or hosted on linux server and fallbacks on hostinger domain
$servername_kronos = $_ENV['servername_kronos'] ?? "localhost";
$username_kronos = $_ENV['username_kronos'] ?? "u292602927_kronostest";
$password_kronos = $_ENV['password_kronos'] ?? "G+aFc2wzbe&a";
$dbname_kronos = $_ENV['dbname_kronos'] ?? "u292602927__kronostest";

// QAM DB configuration, environment variables if local or hosted on linux server and fallbacks on hostinger domain
$servername_qam = $_ENV['servername_qam'] ?? "localhost";
$username_qam = $_ENV['username_qam'] ?? "u292602927_testreact";
$password_qam = $_ENV['password_qam'] ?? "7lO#0stN^";
$dbname_qam = $_ENV['dbname_qam'] ?? "u292602927_testreact";


/* 
//Kronos DB configuration
$servername_kronos = "172.18.0.164";
$username_kronos = "sibssoftdev";
$password_kronos = "sibssoftdev";
$dbname_kronos = "kronos_testdb";

// QAM DB configuration  
$servername_qam = "172.18.0.164";
$username_qam = "sibssoftdev";
$password_qam = "sibssoftdev";
$dbname_qam = "qam_testdb";
*/

$kronosDB = null;
$qamDB = null;

try {
    // Create PDO connection for Kronos DB
    $kronosDB = new PDO(
        "mysql:host=$servername_kronos;dbname=$dbname_kronos;charset=utf8mb4",
        $username_kronos,
        $password_kronos,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Create PDO connection for QAM DB
    $qamDB = new PDO(
        "mysql:host=$servername_qam;dbname=$dbname_qam;charset=utf8mb4",
        $username_qam,
        $password_qam,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
} catch (PDOException $e) {
    // Log the error but don't kill the entire application
    error_log("Database connection failed: " . $e->getMessage());
    
    // Set to null so routes can handle missing connections gracefully
    $kronosDB = null;
    $qamDB = null;
}

// Make PDO connections available globally
$GLOBALS['kronosDB'] = $kronosDB;
$GLOBALS['qamDB'] = $qamDB;

// Functions to get database connections
function getKronosDB() {
    return $GLOBALS['kronosDB'];
}

function getQamDB() {
    return $GLOBALS['qamDB'];
}

// Function to check if databases are connected
function isDatabaseConnected() {
    return $GLOBALS['kronosDB'] !== null && $GLOBALS['qamDB'] !== null;
}
?>