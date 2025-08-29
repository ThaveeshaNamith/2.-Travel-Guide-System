<?php
include 'db_connect.php';
$result = mysqli_query($conn, "SELECT * FROM hotels_cabs");
if ($result) {
    echo "Successfully connected to the hotels_cabs table!";
} else {
    echo "Error: " . mysqli_error($conn);
}
mysqli_close($conn);
?>