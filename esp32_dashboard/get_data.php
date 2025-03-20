
<?php
include('config.php');

$query = "SELECT distance, created_at FROM sensor_data ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($query);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
echo json_encode($data);
$conn->close();
?>