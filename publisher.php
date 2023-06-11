<?php

require 'vendor/autoload.php'; // Assuming you have installed the php-mqtt/client library via Composer

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

$server = 'localhost'; // Replace with your MQTT broker's address
$port = 1883; // Replace with the MQTT broker's port
$clean_session = false;

// MQTT broker login credentials
$username = 'test';
$password = 'test';

// Create connection settings with username and password
$settings = new ConnectionSettings();
$settings->setUsername($username);
$settings->setPassword($password);

// Connect to the MQTT broker with connection settings
$client = new MqttClient($server, $port, 'publisher');
$client->connect($settings, $clean_session);

// Continuously prompt for messages until "exit" is entered
while (true) {
    $message = readline('Enter a message ("exit" to quit): ');

    if ($message === 'exit') {
        break;
    }

    // Publish the message to a topic named 'messages'
    $client->publish('messages', $message);
}

// Disconnect from the MQTT broker
$client->disconnect();
