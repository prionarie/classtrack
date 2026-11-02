<?php
$con = mysqli_connect("localhost", "root", "", "classtrack");
if (!$con) {
    die("Connection Failed: " . mysqli_connect_error());
}
?>