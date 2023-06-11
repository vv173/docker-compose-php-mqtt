<?php

require 'vendor/autoload.php'; // Assuming you have installed the php-mqtt/client library via Composer

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

$server = 'localhost'; // Replace with your MQTT broker's address
$mqtt_port = 1883; // Replace with the MQTT broker's port
$clean_session = false;
$qos = 2;
$topic = 'security';
$last_will_topic = 'security';

// MySQL database configuration
$host = '192.168.1.101';
$mysql_port = 3306; // Replace with your MySQL server's port
$dbName = 'mosquitto';
$username = 'root';
$password = "";

try {
    // Connect to the MySQL database
    $pdo = new PDO("mysql:host=$host;port=$mysql_port;dbname=$dbName", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit();
}

// Create the 'messages' table if it doesn't exist
$createTableQuery = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$pdo->exec($createTableQuery);

// MQTT broker login credentials
$username = 'test';
$password = 'test';

// Create connection settings with username and password
$settings = new ConnectionSettings();
$settings
    ->setUsername($username)
    ->setPassword($password)
    ->setKeepAliveInterval(60)
    ->setLastWillTopic($last_will_topic)
    ->setLastWillMessage('client disconnect')
    ->setLastWillQualityOfService($qos);


// Connect to the MQTT broker with connection settings
$client = new MqttClient($server, $mqtt_port, 'subscriber');
$client->connect($settings, $clean_session);

// Subscribe to the 'messages' topic
$client->subscribe($topic, function (string $received_topic, string $message) use ($pdo) {
    echo "Received message: $message\n";

     // Insert the received message into the database
     $insertQuery = "INSERT INTO messages (message) VALUES (:message)";
     $stmt = $pdo->prepare($insertQuery);
     $stmt->bindParam(':message', $message);
     $stmt->execute();
});

// Continuously listen for messages
while ($client->loop(true)) {
}

// Disconnect from the MQTT broker
$client->disconnect();
