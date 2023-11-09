<?php
// Check if the request is a GET request


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    return "yrdy";
    // Retrieve form data (use $_GET instead of $_POST)
    $name = $_GET["name"];
    $email = $_GET["email"];
    $address = $_GET["address"];
    $city = $_GET["city"];
    $state = $_GET["state"];
    $postalCode = $_GET["postal_code"];
    $country = $_GET["country"];


    // Create a database connection (modify with your database credentials)
    $conn = new mysqli("localhost", "user_info", "user_info", "user_info");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert data into a table (modify with your table name and column names)
    $sql = "INSERT INTO user_info (name, email, address, city, state, postal_code, country)
            VALUES ('$name', '$email', '$address', '$city', '$state', '$postalCode', '$country')";

    if ($conn->query($sql) === TRUE) {
        echo "Data inserted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the database connection
    $conn->close();
} else {
    // Handle non-GET requests or provide an error message
    echo "Invalid request method";
}
?>
