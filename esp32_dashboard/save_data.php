<?php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['distance'])) {
        $distance = $_POST['distance'];
        
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO sensor_data (distance) VALUES (?)");
        $stmt->bind_param("d", $distance);

        if ($stmt->execute()) {
            echo "Data saved successfully.";
        } else {
            echo "Error: " . $stmt->error; // Debugging line
        }

        $stmt->close();
    } else {
        echo "No distance value received."; // Debugging line
    }
}
?>
