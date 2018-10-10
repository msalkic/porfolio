<?php
// this files will create all the tables of our database

$con = mysqli_connect("localhost", "root", "", "bookstore");

if(mysqli_connect_errno()) // check connection
{
  echo "Failed to connect to MySQL: " .mysqli_connect_error();
}

$sql = "CREATE TABLE Purchase
(
purchase_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(purchase_id),
date_time TIMESTAMP NOT NULL,
total_purchase_price DECIMAL(13, 2) NOT NULL
)ENGINE=INNODB;";

$sql .="CREATE TABLE Publisher
(
phone CHAR(15) NOT NULL,
PRIMARY KEY (phone),
name CHAR(30) NOT NULL,
address TEXT NOT NULL
)ENGINE=INNODB;";

$sql .="CREATE TABLE Stock
(
isbn VARCHAR(13) NOT NULL,
PRIMARY KEY(isbn),
num_of_copies INT NOT NULL,
title CHAR(30) NOT NULL,
year INT NOT NULL,
avg_cost DECIMAL(13, 2) NOT NULL,
pub_phone CHAR(30) NOT NULL,
FOREIGN KEY(pub_phone)
  REFERENCES Publisher(phone)
)ENGINE=INNODB;";

$sql .= "CREATE TABLE Purchase_detail
(
purchase_detail_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(purchase_detail_id),
purchase_id INT NOT NULL,
isbn VARCHAR(13) NOT NULL,
purchase_quantity INT NOT NULL,
purchase_price DECIMAL(13, 2) NOT NULL,
FOREIGN KEY(purchase_id)
    REFERENCES Purchase(purchase_id),
FOREIGN KEY(isbn)
    REFERENCES Stock(isbn)
)ENGINE=INNODB;"; //when we enter purchase detail first save stuff into Publisher, then into Stock then Purchase detail

$sql .= "CREATE TABLE Customer
(
customer_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY (customer_id),
first_name TEXT NOT NULL,
middle_name TEXT,
last_name TEXT NOT NULL,
phone TEXT NOT NULL,
email TEXT NOT NULL,
credits DECIMAL(13,2)
)ENGINE=INNODB;";

$sql .= "CREATE TABLE Receipt
(
receipt_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY (receipt_id),
final_price DECIMAL(13,2) NOT NULL
)ENGINE=INNODB;"; //we need to first persist to receipt before order table

$sql .= "CREATE TABLE Customer_order
(
order_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(order_id),
customer_id INT NOT NULL,
date_time TIMESTAMP NOT NULL,
total_price DECIMAL(13,2) NOT NULL,
discount DECIMAL(13,2),
receipt_id INT NOT NULL,
FOREIGN KEY(customer_id)
    REFERENCES Customer(customer_id),
FOREIGN KEY(receipt_id)
    REFERENCES Receipt(receipt_id)
)ENGINE=INNODB;";

$sql .= "CREATE TABLE Order_detail
(
order_detail_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(order_detail_id),
order_id INT NOT NULL,
isbn VARCHAR(13) NOT NULL,
quantity_ordered INT NOT NULL,
FOREIGN KEY(order_id)
    REFERENCES Customer_order(order_id),
FOREIGN KEY(isbn)
    REFERENCES Stock(isbn)
)ENGINE=INNODB;";

  if(mysqli_multi_query($con, $sql)) // make sure the database is created
  {

    echo "Tables created successfully!";
  }

  else
  {
    echo "Error creating Tables: " .mysqli_error($con);
  }

  mysqli_close($con); //closing connection




 ?>
