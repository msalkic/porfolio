<?php
header('Content-Type: text/html; charset=utf-8');
//placeholder

//$output=NULL;

if(isset($_POST['update_stock'])){

        //connect to db
        $con = mysqli_connect("localhost", "root", "", "bookstore");

      //we cannot clean these yet; they are arrays //we will sanitize as we enter the data into db
        $isbn = $_POST['isbn'];
        $title = $_POST['title'];
        $year = $_POST['year'];
        $purch_quantity = $_POST['purch_quantity'];
        $purch_price_per_book= $_POST['purch_price_per_book'];
        $pub_name = $_POST['pub_name'];
        $pub_phone = $_POST['pub_phone'];
        $pub_address = $_POST['pub_address'];
        $total_purchase_price = '';
        $purchase_prices_of_isbns = array();
        $purchase_id = '';
        date_default_timezone_set('America/New_York');
        $datetime_purch = date('Y/m/d H:i:s');
        $tax = 1.13;
        $purchase_detail_id = "";

      //add the the total prices for each isbn purchased inside an array
        foreach ($purch_quantity as $key => $value) {
              $purchase_prices_of_isbns[] =  $value * $purch_price_per_book[$key];
        }

        unset($key);

      //sum the individual prices to obtain $total_purchase_price
        $total_purchase_price = array_sum($purchase_prices_of_isbns) * $tax;

      //update the Purchase table with clean code
        if($stmt = $con -> prepare("INSERT INTO Purchase(purchase_id, date_time, total_purchase_price)
              VALUES(?, ?, ?)"))
        {

              $stmt -> bind_param("isd", $purchase_id, $datetime_purch, $total_purchase_price);
              $stmt -> execute();
              $stmt -> close();
            //  echo "A new purchase record added!".mysqli_error($con);
        }
        else
        {
              echo "Something bad happend!".mysqli_error($con);
        }


      $count=0; //initialize it;
      //check if first pub_phone exists in publisher if not add the publisher

        foreach ($pub_phone as $key => $value)
        {

            $sql_check_phone = "SELECT phone FROM Publisher WHERE phone =  '"  . $con->real_escape_string($pub_phone[$key]) . "' LIMIT 1";

            $rowcount = -1; //initialize rowcount


            if($result = mysqli_query($con, $sql_check_phone)) //check if pub_phone is in db possible
            {
              $rowcount = mysqli_num_rows($result); //we get the row count
            }
            if($rowcount == 0) //only add publisher if it doesnt exist
            {
                    if($stmt = $con -> prepare("INSERT INTO Publisher(phone, name, address)
                          VALUES(?, ?, ?)"))
                    {

                          $stmt -> bind_param("sss", $pub_phone[$key], $pub_name[$key], $pub_address[$key]);
                          $stmt -> execute();
                          $stmt -> close();
                        //  echo "A publisher record added!";
                        $count += 1;
                    }
                    else
                    {
                        echo "A new publisher was not added. Error!".mysqli_error($con);
                    }
            }
        }

        //echo $count. " new publishers were added to the database! ";
        unset($key);

        //update the stock table

        foreach ($isbn as $key => $value) {
            $sql_check_isbn = "SELECT isbn FROM Stock WHERE isbn = '"  . $con->real_escape_string($isbn[$key]) . "' LIMIT 1 ";

            $rowcount = -1; //initialize rowcount

            if($result = mysqli_query($con, $sql_check_isbn)) //check if isbn is in db
            {
              $rowcount = mysqli_num_rows($result); //we get the row count
            }

            if($rowcount == 0) //if the book is not in the bookstore
            {
            //  echo "row count is 0";
                  //  since the book doesnt exist in the bookstore the avg_cost = purchase_price_per_book
                    if($stmt = $con -> prepare("INSERT INTO Stock(isbn, num_of_copies, title, year, avg_cost, pub_phone)
                          VALUES(?, ?, ?, ?, ?, ?);"))
                    {

                          $stmt -> bind_param("sisids", $isbn[$key], $purch_quantity[$key], $title[$key], $year[$key], $purch_price_per_book[$key], $pub_phone[$key]);
                          $stmt -> execute();
                          $stmt -> close();
                        //  echo "A publisher record added!";
                        //$count += 1;
                    }
                    else
                    {
                        echo "The book was not added. Error!".mysqli_error($con);
                    }

            }
            else // if the book is in the bookstore
            {

                    $num_of_copies_db=-1;

                    if($stmt = $con -> prepare("SELECT num_of_copies FROM Stock WHERE isbn = ?;"))
                    {
                      $stmt -> bind_param("s", $isbn[$key]);
                      $stmt -> execute();
                      $stmt -> bind_result($num_of_copies_db);
                      $stmt->fetch();
                      $stmt -> close();
                    }

                    //now we have num_of_copies_db obtained from the //
                    $num_of_copies_db = $num_of_copies_db + $purch_quantity[$key]; //this updates the number of copies


                    //----------------------------------------------------
                      if($stmt = $con -> prepare("SELECT SUM(purchase_quantity) AS pq FROM Purchase_detail WHERE isbn = ? GROUP BY purchase_price;"))
                      {
                        $stmt -> bind_param("s", $isbn[$key]);
                        $stmt -> execute();
                        //$stmt -> bind_result($p_quant_db[], $p_price_db[]); //fetch purchace quantity and purchase price from db
                      //  $stmt->fetch();

                        $result_set = $stmt->get_result(); //grab a result set
                        $result = $result_set -> fetch_all(); //store it into an associative array
                        $stmt -> close();
                     }

                    //    extract($result);

                        $result_length= count($result);

                        $pq = array();

                        for ($x=0; $x < $result_length; $x++)
                        {
                                    foreach ($result[$x] as $value)
                                    {
                                                $pq[$x] = $value;
                                    }


                        }

                        //**********
                        array_push($pq, $con->real_escape_string($purch_quantity[$key])); //we add the current purchase quantity to the existing array

                        //*********

                    // PUT ALL THE PRICES INTO ONE ARRAY!
                        if($stmt = $con -> prepare("SELECT purchase_price AS pp FROM Purchase_detail WHERE isbn = ? GROUP BY purchase_price;"))
                        {
                          $stmt -> bind_param("s", $isbn[$key]);
                          $stmt -> execute();
                          $result_set = $stmt->get_result(); //grab a result set
                          $result = $result_set -> fetch_all(); //store it into an associative array
                          $stmt -> close();
                       }

                       $result_length= count($result);

                       $pp = array();

                       for ($x=0; $x < $result_length; $x++)
                       {
                                   foreach ($result[$x] as $value)
                                   {
                                               $pp[$x] = $value;
                                   }
                       }

                         array_push($pp, $con->real_escape_string($purch_price_per_book[$key])); //ALL PRICES IN ONE ARRAY -pp!!!

                      //   var_dump($pp);

                         $pq_length = count($pq); //this and pp length are the same
                         $pq_sum =  array_sum($pq); //this is the sum of purchase quantity updated;
                         $avg_cost = 0;
                         //CALCULATE NEW UPDATED AVG_COST!!! ***
                        for($x = 0; $x < $pq_length; $x++)
                        {
                              $avg_cost += $pq[$x]/$pq_sum *$pp[$x];
                        }


                      //  ***ADD THOSE BOOKS TO THE DATA BASE ****
                        if($stmt = $con -> prepare("UPDATE Stock SET num_of_copies = $num_of_copies_db, avg_cost = $avg_cost WHERE isbn = ?;"))
                        {

                              $stmt -> bind_param("s", $isbn[$key]);
                              $stmt -> execute();
                              $stmt -> close();
                            //  echo "A publisher record added!";
                            //$count += 1;
                        }
                        else
                        {
                            echo "The book was not added. Error!".mysqli_error($con);
                        }

                      //  -----UPDATE PURCHASE detail
            } //the stock brace


            //UPDATE the Purchase detail table;

            if($stmt = $con -> prepare("SELECT MAX(purchase_id) FROM Purchase;"))
            {
              //$stmt -> bind_param("s", $isbn[$key]);
              $stmt -> execute();
              $stmt -> bind_result($latest_purchase_id);
              $stmt->fetch();
              $stmt -> close();

                                          if($stmt = $con -> prepare("INSERT INTO Purchase_detail(purchase_detail_id, purchase_id, isbn, purchase_quantity, purchase_price)
                                                VALUES(?, ?, ?, ?, ?)"))
                                          {

                                                $stmt -> bind_param("iisid", $purchase_detail_id, $latest_purchase_id, $isbn[$key], $purch_quantity[$key], $purch_price_per_book[$key]);
                                                $stmt -> execute();
                                                $stmt -> close();
                                              //  echo "A new purchase detail added!".mysqli_error($con);
                                          }
                                          else
                                          {
                                                echo "Something bad happend!".mysqli_error($con);
                                          }
            }
          }


$copies_all = array();
$title_all = array();
$isbn_all = array();

 foreach ($isbn as $key => $value) {

   $stmt = $con->prepare( "SELECT num_of_copies, title FROM Stock WHERE isbn = ?;");
   $stmt -> bind_param("s", $isbn[$key]);
   $stmt -> execute();
   $stmt -> bind_result($copies, $title);
   $stmt-> fetch();
   $stmt -> close();

  array_push($copies_all, $copies);
  array_push($title_all, $title);
  array_push($isbn_all, $value);



 }
// echo "p" . $copies_all[0] . "of";

   $string = " ";
   for ($i=0; $i < count($isbn)-1; $i++) {

     $string = $string . "<b>$copies_all[$i]</b>" . " copies of ISBN: " . "<b>$isbn_all[$i]</b>". " (title: " . "<b>$title_all[$i])</b>, " ;
   }

   if(count($isbn)==1){

    $string = "<b>$copies_all[0]</b>" . " copies of ISBN: ". "<b>$isbn_all[0]</b>". " (title: " . "<b>$title_all[0]</b>" . ").";

   }
   else{
     $string = $string . "and " . $copies_all[count($isbn)-1] ." copies of ISBN: ". $isbn_all[count($isbn)-1] . " (title: " . $title_all[count($isbn)-1] . ")";

   }


  echo "After the new purchase we have: " . $string. " in stock.";

//." of ". $isbn_all[$i] ." (title: )". $title_all[$i] .", ";

 mysqli_close($con);
}

//------ ADD CUSTOMER-----
if(isset($_POST['add_cust']))
{

        //connect to db
        $con = mysqli_connect("localhost", "root", "", "bookstore");

        $fname = $_POST['firstname'];
        $mname = $_POST['midname'];
        $lname = $_POST['lastname'];
        $phone = $_POST['telphone'];
        $email = $_POST['email_address'];
        $customer_id = '';
        $credits = '';


        //check if customer exists in db based on email
        $sql_check_phone = "SELECT phone FROM Customer WHERE email =  '"  . $con->real_escape_string($email) . "' LIMIT 1";

        $rowcount = -1; //initialize rowcount
        if($result = mysqli_query($con, $sql_check_phone)) //check if pub_phone is in db possible
        {
          $rowcount = mysqli_num_rows($result); //we get the row count
        }

        if($rowcount == 0)
        {

                      //insert into Customer table
                      if($stmt = $con -> prepare("INSERT INTO Customer(customer_id, first_name, middle_name, last_name, phone, email, credits)
                            VALUES(?, ?, ?, ?, ?, ?, ?)"))
                      {

                            $stmt -> bind_param("isssssd", $customer_id, $fname, $mname, $lname, $phone, $email, $credits);
                            $stmt -> execute();
                            $stmt -> close();


                            //retrieve the customer id:

                            $stmt = $con -> prepare("SELECT MAX(customer_id) FROM Customer;");
                            //$stmt -> bind_param("s", $isbn[$key]);
                            $stmt -> execute();
                            $stmt -> bind_result($customer_id);
                            $stmt->fetch();
                            $stmt -> close();



                            echo "A new customer record for ". "<b>$fname $lname</b>" . " with customer id: <b>$customer_id</b> created. $fname $lname has <b>$0.00</b> credits.";
                      }
                      else
                      {
                            echo "Something bad happend!".mysqli_error($con);
                      }
        }
        else
        {
          echo "A customer with <b>$email</b> email already exists in the database!";


        }

        mysqli_close($con);
} //closes AddCustomer


//UPDATE CUSTOMER info
if(isset($_POST['update_cust'])){

              //connect to db
              $con = mysqli_connect("localhost", "root", "", "bookstore");

                $fname = $_POST['fname'];
                $mname = $_POST['mname'];
                $lname = $_POST['lname'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];
                $customer_id = $_POST['cust_id'];

                //check if customer exists in db based on customer_id
                $sql_check_customer_id = "SELECT * FROM Customer WHERE customer_id =  '"  . $con->real_escape_string($customer_id) . "' LIMIT 1";

                $rowcount = -1; //initialize rowcount
                if($result = mysqli_query($con, $sql_check_customer_id)) //check if customer id is in db
                {
                              $rowcount = mysqli_num_rows($result); //we get the row count
                }

                if($rowcount == 0)
                {
                              echo "Error - cannot update Customer Info with customer id: <b>$customer_id</b>. No existing customer with customer id: <b>$customer_id</b>.";
                }
                else //Update all info except the credits.
                {
                            if($stmt = $con -> prepare("UPDATE Customer SET first_name = ?, middle_name = ?, last_name = ?, phone = ?, email = ?  WHERE customer_id = ?;"))
                            {

                                  $stmt -> bind_param("sssssi", $fname, $mname, $lname, $phone, $email, $customer_id);
                                  $stmt -> execute();
                                  $stmt -> close();

                                  echo "Record belonging to <b>" . $fname . " " .$lname. "</b> with customer id: <b>" .$customer_id . " </b>successfully updated." ;

                            }
                            else
                            {
                                echo "Update failed. Error!".mysqli_error($con);
                            }
                }
        mysqli_close($con);
}

//NEW ORDER
if(isset($_POST['new_order']))
{
        //connect to db
        $con = mysqli_connect("localhost", "root", "", "bookstore");

        $cust_id = $_POST['customer_id']; //this is not an array
        $isbn = $_POST['isbn'];
        $quantity_ordered = $_POST['quantity'];
      //  $credits = $_POST['credits'];


      //first check if customer exists
      //check if customer exists in db based on customer_id
      $stmt = $con->prepare( "SELECT customer_id FROM Customer WHERE customer_id = ?;");
      $stmt -> bind_param("i", $cust_id);
      $stmt -> execute();
      $stmt -> bind_result($result);
      $stmt-> fetch();
      $stmt -> close();

      //var_dump($result, is_null($result));

      if(is_null($result))

      {
                    echo "Error - cannot make purchase. No existing customer with customer id: <b>$cust_id<b/>.";
      }

      else //customer is in db...let's check if the ordered ISBN's exist and if there is enough stock!
      {
                  $is_isbn_in_db = array();
                  foreach ($isbn as $key => $value)
                  {
                          $stmt= $con-> prepare("SELECT isbn FROM Stock WHERE isbn = ? AND num_of_copies >= ?;");
                          $stmt -> bind_param("si", $isbn[$key], $quantity_ordered[$key]);
                          $stmt -> execute();
                          $stmt -> bind_result($result);
                          $stmt-> fetch();
                          $stmt -> close();

                          if(!is_null($result))
                          array_push($is_isbn_in_db, $result); //append the rowcount to the array
                  }

                  //if any value in the array is NULL then either the bookstore doesnt have the isbn or there are not enough num_of_copies

                  if (count($is_isbn_in_db) !== count($isbn)) {

                              echo "One of the ISBN's is not in the bookstore or there is not enough stock!";

                  }

                  else //we have the books the cust is trying to order and enough copies of the same
                  {
                              $total_price = 0.00;
                              //calculate the total price
                              foreach ($isbn as $key => $value)
                              {
                                $stmt= $con-> prepare("SELECT avg_cost FROM Stock WHERE isbn = ?");
                                $stmt -> bind_param("d", $isbn[$key]);
                                $stmt -> execute();
                                $stmt -> bind_result($result);
                                $stmt-> fetch();
                                $stmt -> close();


                                                                                      // the price of one book is 1.25 * avg_cost of the isbn
                                $one_isbn_price = $result * 1.25 * $quantity_ordered[$key]; //agg price of one isbn; all quantity included
                                $total_price = $total_price + $one_isbn_price;

                              }




                              //calculate the discount if it applies

                              $discount = 0.00;
                              $credits = 0.00; //this are actual credits customer has from the Customer table

                              if($credits=="yes"){ //our algorithm is that we apply the minimum of 10% of the total price and total_credits the cust ezmlm_hash

                                $stmt= $con-> prepare("SELECT credits FROM Customer WHERE customer_id = ?");
                                $stmt -> bind_param("i", $cust_id);
                                $stmt -> execute();
                                $stmt -> bind_result($credits);
                                $stmt-> fetch();
                                $stmt -> close();

                                $discount = min($credits, 0.1 * $total_price);
                              }

                              //update the change in credits in the Customer tables
                              $credits = $credits - $discount + 0.05 * $total_price; //new updated credits of the customer we give him 5% of the current purchase
                              $stmt= $con-> prepare("UPDATE Customer SET credits = ? WHERE customer_id = ?");
                              $stmt -> bind_param("di", $credits, $cust_id);
                              $stmt -> execute();
                              $stmt -> close();


                              //calculate $tax

                              //$tax = 0.13 * $total_price;
                              $tax_rate=1.13;
                              $final_price = ($total_price - $discount)*$tax_rate;
                              $receipt_id = '';

                            //insert values into Receipt tables
                            if($stmt = $con -> prepare("INSERT INTO Receipt(receipt_id, final_price)
                                  VALUES(?, ?)"))
                            {

                                  $stmt -> bind_param("id", $receipt_id, $final_price);
                                  $stmt -> execute();
                                  $stmt -> close();
                                //  echo "A new receipt added! <br>".mysqli_error($con);
                            }
                            else
                            {
                                  echo "Something bad happend!".mysqli_error($con);
                            }


                            //obtain the latest receipt_id from the Receipt table we need it for thr Customer_order table (FK)

                              $stmt = $con -> prepare("SELECT MAX(receipt_id) FROM Receipt;");
                              $stmt -> execute();
                              $stmt -> bind_result($latest_receipt_id);
                              $stmt->fetch();
                              $stmt -> close();

                            //insert into Customer_order table
                            $order_id ='';
                            date_default_timezone_set('America/New_York');
                            $date_time = date('Y/m/d H:i:s');

                            if($stmt = $con -> prepare("INSERT INTO Customer_order(order_id, customer_id, date_time, total_price, discount, receipt_id)
                                  VALUES(?, ?, ?, ?, ?, ?)"))
                            {

                                  $stmt -> bind_param("iisddi", $order_id, $cust_id, $date_time, $total_price, $discount, $latest_receipt_id);
                                  $stmt -> execute();
                                  $stmt -> close();

                                  $credits_rounded = round($credits, 2);
                                  $final_price_rounded = round($final_price, 2);
                                  echo "A new customer order with receipt id: <b>$latest_receipt_id</b> added for customer id: <b>$cust_id</b>. The total price including taxes is <b>$$final_price_rounded</b>. The customer has <b>$$credits_rounded </b> credits left.";
                            }
                            else
                            {
                                  echo "Something bad happend!".mysqli_error($con);
                            }



                            //obtain latest order detail id because it is a FK for Order_detail table //

                            $stmt = $con -> prepare("SELECT MAX(order_id) FROM Customer_order;");
                            $stmt -> execute();
                            $stmt -> bind_result($latest_order_id);
                            $stmt->fetch();
                            $stmt -> close();

                              //update Order_detail table

                            $order_detail_id = '';
                            foreach ($isbn as $key => $value) {

                                        if($stmt = $con -> prepare("INSERT INTO Order_detail(order_detail_id, order_id, isbn, quantity_ordered)
                                              VALUES(?, ?, ?, ?)"))
                                        {

                                              $stmt -> bind_param("iisi", $order_detail_id, $latest_order_id, $isbn[$key], $quantity_ordered[$key]);
                                              $stmt -> execute();
                                              $stmt -> close();
                                              //echo "A new Order detail record added! <br>".mysqli_error($con);
                                        }
                                        else
                                        {
                                              echo "Something bad happend!".mysqli_error($con);
                                        }
                            }

                            //obtain the num_of_copies from stock so we can update it


                            foreach ($isbn as $key => $value) {

                                        $stmt = $con -> prepare("SELECT num_of_copies FROM Stock WHERE isbn = ?;");
                                        $stmt -> bind_param("s", $isbn[$key]);
                                        $stmt -> execute();
                                        $stmt -> bind_result($num_of_copies);
                                        $stmt->fetch();
                                        $stmt -> close();

                                        $num_of_copies = $num_of_copies - $quantity_ordered[$key];

                                        if($stmt = $con -> prepare("UPDATE Stock SET num_of_copies = $num_of_copies WHERE isbn = ?;"))
                                        {

                                              $stmt -> bind_param("s", $isbn[$key]);
                                              $stmt -> execute();
                                              $stmt -> close();
                                          //  echo "Stock table updated.";
                                        }
                                        else
                                        {
                                            echo "The book was not added. Error!".mysqli_error($con);
                                        }
                                }
                            }
         } mysqli_close($con);
}

?>
