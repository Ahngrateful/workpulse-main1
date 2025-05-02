<?php
// Include Composer's autoloader
require 'vendor/autoload.php';

// Use the ZKTeco class
use Jmrashed\Zkteco\Lib\ZKTeco;

// Device configuration
define('DEVICE_IP', '192.168.1.100');
define('DEVICE_PORT', 4370); // Default ZKTeco UDP port

// Function to initialize and connect to the device
function connectToDevice() {
    $zk = new ZKTeco(DEVICE_IP, DEVICE_PORT);
    if (!$zk->connect()) {
        return false;
    }
    return $zk;
}

// Function to disconnect from the device
function disconnectFromDevice($zk) {
    $zk->disconnect();
}