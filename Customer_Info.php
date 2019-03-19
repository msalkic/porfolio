<!DOCTYPE html>
	<!--Adding the table with required input and style to the table -->

<html>
  <head>
    <style type="text/css">
    table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 70%;
}

td, th {
    border: 3px solid #c2f7a0;
    text-align: left;
    padding: 8px;
}

tr:nth-child(even) {
    background-color: #c2f7a0;
}
    </style>
  </head>

<body>
<h2><center> CUSTOMER INFORMATION </center> </h2>
<table align="center" >
<tr>
<th> Customer ID </th>
<th> First Name </th>
<th> Middle Name </th>
<th> Last Name </th>
<th> Phone </th>
<th> Email </th>
<th> Credit </th>
</tr>

<?php
 //connect to db

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if(mysqli_connect_errno()) // check connection
{
  echo "Failed to connect to MySQL: " .mysqli_connect_error();
  
}

//Get the customer id from the html form
$custid = $_POST['customer_id'];

//Query for getting the customer info based on customer id in input

$sql = "SELECT * FROM customer WHERE customer_id= $custid ";
 
$result= $conn->query($sql);


if($result-> num_rows > 0){
	while($row= $result-> fetch_assoc()){
	echo "<tr><td>".$row["customer_id"]."</td><td>".$row["first_name"]."</td><td>".$row["middle_name"]."</td><td>".$row["last_name"]."</td><td>".$row["phone"]."</td><td>".$row["email"]."</td><td>".$row["credits"]."</td></tr>";
}
echo"</table>";
}
else{
	echo "0 Result";
	}

$conn->close();
?>	

</table>
</body>
</html>
