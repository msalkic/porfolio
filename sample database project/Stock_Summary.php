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
<h2><center> STOCK INFORMATION </center> </h2>
<table align="center" >
<tr>
<th> ISBN </th>
<th> Title </th>
<th> Year </th>
<th> Stock </th>
</tr>



<?php

 //connect to db

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if(mysqli_connect_errno()) // check connection
{
  echo "Failed to connect to MySQL: " .mysqli_connect_error();
}
 //Query for retrieving the Stock information and ordering by number of copies left

$sql = "SELECT title, year,num_of_copies, isbn
FROM stock
ORDER BY num_of_copies";
 
$result= $conn->query($sql);

if($result-> num_rows > 0){
	while($row= $result-> fetch_assoc()){
	echo "<tr><td>".$row["isbn"]."</td><td>".$row["title"]."</td><td>".$row["year"]."</td><td>".$row["num_of_copies"]."</td></tr>";
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
