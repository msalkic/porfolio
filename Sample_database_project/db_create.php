<?php

// this file only creates the database
// it needs to be run only once


$con = mysqli_connect("localhost", "root", "");


if(mysqli_connect_errno()) // check connection
{
  echo "Failed to connect to MySQL: " .mysqli_connect_error();
}

$sql1 = "CREATE DATABASE bookstore COLLATE utf8mb4_general_ci"; // create database

if(mysqli_query($con, $sql1)) // make sure the database is created
{

  echo "Database bookstore created successfully!";
}

else
{
  echo "Error creating database: " .mysqli_error();
}

mysqli_close($con); //closing connection
 ?>
