<!DOCTYPE html>
	<!--Adding the table with required input and style to the table -->

<html>
  <head>
    <style type="text/css">
    table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 90%;
    margin-left:50px;
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
<h2><center> PROFIT FROM PREVIOUS MONTH </center> </h2>
<table align="center" >
<tr>
<th> Revenue </th>
<th> Cost </th>
<th> Profit </th>
</tr>

<?php
 //connect to db

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if(mysqli_connect_errno()) // check connection
{
  echo "Failed to connect to MySQL: " .mysqli_connect_error();
}

//find today's date_time
//find last months date_time
date_default_timezone_set('America/New_York');
$now = date('Y/m/d H:i:s');
$a_month_ago = date("Y/m/d", strtotime("-1 months"));

//Query for Calculating the profit Since last month

$sql = "SELECT ROUND(SUM(final_price),2) AS revenue, ROUND(SUM(total_price/1.25),2) AS cost, ROUND(SUM(final_price - total_price/1.25),2) AS profit
FROM Receipt
JOIN Customer_order
USING(receipt_id)
WHERE date_time >= '$a_month_ago' AND date_time <='$now' ";

$result= $conn->query($sql);

if($result-> num_rows > 0){
	while($row= $result-> fetch_assoc()){
	echo "<tr><td>".$row["revenue"]."</td><td>".$row["cost"]."</td><td>".$row["profit"]."</td></tr>";
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
