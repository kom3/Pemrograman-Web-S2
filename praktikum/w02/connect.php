<?php

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'pemrograman_web';

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully", "<br />";

$sql = "CREATE TABLE MyGuests (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    firstname VARCHAR(30) NOT NULL,
    lastname VARCHAR(30) NOT NULL,
    email VARCHAR(50),
    reg_date TIMESTAMP
    )";
    
if ($conn->query($sql) === TRUE) {
    echo "Table MyGuests created successfully", "<br />";
} else {
    echo "Error creating table: " . $conn->error, "<br />";
}

$sql = "INSERT INTO MyGuests (firstname, lastname, email)
VALUES ('John', 'Doe', 'john@example.com')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully", "<br />";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error, "<br />";
}
    
$conn->close();