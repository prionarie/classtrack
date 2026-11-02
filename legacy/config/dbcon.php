<?php
$con = mysqli_connect("db", "root", "", "classtrack");
if (!$con) {
    die("Connection Failed: " . mysqli_connect_error());
}
?>