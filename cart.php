<!DOCTYPE HTML> 
<!--
	Name: Alfin Rahardja
	Class: CS 368
	Due-date: April 25, 2016
-->
<html>
<head>
	<meta name="author" content="Alfin Rahardja" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
	<link href="style06.css" rel="stylesheet" type="text/css" />
	<title>Shopping Cart</title>
</head>
<body>
   <header>
		<a href="program6.php">
			<img id="logo" src="images/adidas.gif" alt="Adidas Logo" />
		</a>
		<nav>
			<a href="program6.php" id="productsButton">Products</a>
			<span id="separator"> | </span>
			<a href="cart.php">
				<img id="cartLogo" src="images/cart.png" alt="Shopping Cart logo" />
				<!-- The image is from http://www.inmotionhosting.com -->
			</a>
		</nav>
		<img id="headerImage" src="images/header3.jpg" alt="Header Image" />
		<!-- The image is from shop.adidas.com.sg -->
	</header>
	<?php
		// Constant Variables
		define("ADD", "Add");
		define("DELETE", "Delete");
		define("UPDATE", "Update");
		define("CHECKOUT", "Checkout");
		define("PENDING", "P");
		define("COMPLETED", "C");
	
		// Member Variables
		@$itemId = $_GET["p_id"];
		$username = $_SERVER ["AUTH_USER"];
		$breakUsername = explode("\\", $username);
		$queryUsername =  $breakUsername[0] . "\\\\" . $breakUsername[1];
		
		if(isset($_GET["cart_action"]))
		{
			// Check the action
			if(strcmp($_GET["cart_action"], ADD) == 0)
			{
				addItem($queryUsername, $username, $itemId);
				printCart($queryUsername);
			}
			elseif(strcmp($_GET["cart_action"], DELETE) == 0)
			{
				$orderId = $_GET["o_id"];
				deleteItem($orderId);
				printCart($queryUsername);
			}
			elseif(strcmp($_GET["cart_action"], UPDATE) == 0)
			{
				$quantity = $_GET["quantity"];
				$orderLineId = $_GET["ol_id"];
				updateItem($orderLineId, $quantity);
				printCart($queryUsername);
			}
			elseif(strcmp($_GET["cart_action"], CHECKOUT) == 0)
			{
				checkoutItems($queryUsername);
			}
		}
		else
		{
			printCart($queryUsername);
		}
	?>
		
	<?php
		/*****************************************************************/
		/*  Add item into the shopping cart								 */
		/*****************************************************************/
		function addItem($queryUsername, $username, $itemId)
		{
			// Create a connection to the database
			@ $db = new mysqli("csweb", "123105", "pcc1935");
			$db->select_db("cs368_123105");
			
			// Check at database for any order that is same with the item 
			// has been added
			$query = "select OL_id from orderline join products".
						" using (P_id)".
						" where P_id = ".$itemId." and O_id in (select O_id from orders".
						" where O_status = '".PENDING."'".
						" and O_username = '".$queryUsername."')";

			$orderLineData = $db->query($query);
			$orderLineRecord = $orderLineData->fetch_assoc();
			$orderLineId = $orderLineRecord["OL_id"];
			
			if(!isset($orderLineId))
			{
				// Make a query request and receive the item data that is going
				// to be added to the database
				$orderData = $db->query("select max(O_id) + 1 as newId from orders");
				$orderRecord = $orderData->fetch_assoc();
				$orderId = $orderRecord["newId"];
				$orderDate = date("m/j/Y");
				$status = PENDING;
				
				$query = "insert into orders (O_id, O_status, O_username, O_date)".
						 " values (?, ?, ?, ?)";
				$addOrder = $db->prepare($query);
				
				// Check if the Order id in orders table is not NULL
				if(!isset($orderId))
				{
					$GLOBALS["orderId"] = 1;
					$addOrder->bind_param("isss", $GLOBALS["orderId"], $status, $username, $orderDate);
					$addOrder->execute();
				}
				else
				{
					$addOrder->bind_param("isss", $orderId, $status, $username, $orderDate);
					$addOrder->execute();
				}
				
				// Free the memory
				$orderData->free();
				
				// Store all information to the linking table
				$orderLineData = $db->query("select max(OL_id) + 1 as newOLId from orderline");
				$orderLineRecord = $orderLineData->fetch_assoc();
				$orderLineId = $orderLineRecord["newOLId"];
				$orderLineQuantity = 1;
				$query = "insert into orderline (OL_id, O_id, P_id, OL_quantity) values (?, ?, ?, ?)";
				$addOrderLine = $db->prepare($query);
				
				// Check if the OL_id in orders table is not NULL
				if(!isset($orderLineId))
				{
					$GLOBALS["orderLineId"] = 1;
					$addOrderLine->bind_param("iiii", $GLOBALS["orderLineId"], $GLOBALS["orderId"], $itemId, $orderLineQuantity);
					$addOrderLine->execute();
				}
				else
				{
					$addOrderLine->bind_param("iiii", $orderLineId, $orderId, $itemId, $orderLineQuantity);
					$addOrderLine->execute();
				}
				
				// Free the memory
				$orderLineData->free();				
			}
			else
			{
				$orderLineData = $db->query("select OL_quantity + 1 as newQuantity from ".
											"orderline where OL_id = ".$orderLineId);
				$orderLineRecord = $orderLineData->fetch_assoc();
				$orderLineQuantity = $orderLineRecord["newQuantity"];
				$query = "update orderline set OL_quantity = ? where OL_id = ?";
				$addQuantity = $db->prepare($query);
				$addQuantity->bind_param("ii", $orderLineQuantity, $orderLineId);
				$addQuantity->execute();
				
				// Free the memory
				$orderLineData->free();
			}
			
			// Close the database
			$db->close();
		}
		
		/*****************************************************************/
		/*  Delete a specific item from the shopping cart				 */
		/*****************************************************************/
		function deleteItem($orderId)
		{
			// Create a connection to the database
			@ $db = new mysqli("csweb", "123105", "pcc1935");
			$db->select_db("cs368_123105");
			
			// Make a query request and delete the item from orderline table
			$query = "delete from orderline where O_id = ?";
			$deleteOrder = $db->prepare($query);
			$deleteOrder->bind_param("i", $orderId);
			$deleteOrder->execute();
			
			// Make a query request and delete the item from orders table
			$query = "delete from orders where O_id = ?";
			$deleteOrder = $db->prepare($query);
			$deleteOrder->bind_param("i", $orderId);
			$deleteOrder->execute();
			
			// Close the database
			$db->close();
		}
		
		/*****************************************************************/
		/*  Update a specific order 									 */
		/*****************************************************************/
		function updateItem($orderLineId, $quantity)
		{
			// Create a connection to the database
			@ $db = new mysqli("csweb", "123105", "pcc1935");
			$db->select_db("cs368_123105");
			
			// Make a query request and update the quantity of the item
			$query = 	"update orderline".
						" set OL_quantity = ?".
						" where OL_id = ?";				
			$update = $db->prepare($query);
			$update->bind_param("ii", $quantity, $orderLineId);
			$update->execute();
			
			// Close the database
			$db->close();
		}
		
		/*****************************************************************/
		/*  Checkout the orders											 */
		/*****************************************************************/
		function checkoutItems($queryUsername)
		{
			// Create a connection to the database
			@ $db = new mysqli("csweb", "123105", "pcc1935");
			$db->select_db("cs368_123105");
			
			// Make a query request and update the status of the order
			$query = 	"update orders".
						" set O_status = '".COMPLETED."'".
						" where O_username = '".$queryUsername."'";
			$checkout = $db->query($query);
			
			// Close the database
			$db->close();

			echo '<h1>Thank you for your orders!</h1>';
		}
		
		/*****************************************************************/
		/*  Print the items into the shopping cart page					 */
		/*****************************************************************/
		function printCart($queryUsername)
		{
			// Create a connection to the database
			@ $db = new mysqli("csweb", "123105", "pcc1935");
			$db->select_db("cs368_123105");
			
			// Make a query request and grab the Orderline Id
			$query = "select * from orderline join products".
					 " using (P_id) where O_id".
					 " in (select O_id from orders where O_status = '".PENDING."'".
					 " and O_username = '".$queryUsername."')".
					 " order by P_name";
			$showItem = $db->query($query);
			$item = $showItem->fetch_assoc();
			$orderList = $item["OL_id"];
			
			// Check if there is any saved order
			if(!isset($orderList))
			{
				echo '<h1>Your Shopping Cart is empty.</h1>';
			}
			else
			{
				echo '<div class="orderSummary">';
				echo '<h2>Your Items:</h2>';
				echo '<table id = cartTable>';
				
				// Make a query request and print all the items from orderline table
				$query = "select * from orderline join products".
					     " using (P_id) where O_id".
					     " in (select O_id from orders where O_status = '".PENDING."'".
					     " and O_username = '".$queryUsername."')".
					     " order by P_name";
				$showItem = $db->query($query);
				while($item = $showItem->fetch_assoc())
				{
						$counter = 1;
						echo '<tr>';
						echo '<td>';
						echo '<img src="data:image/jpeg;base64,'.
						      base64_encode($item["P_picture"]).'" '.
							 'id="item'.$item["P_id"].'" alt="Product Picture"/>';
						echo '</td>';
						echo '<td class="nameCart">';
						echo $item["P_name"];
						echo '</td>';
						echo '<td class="priceCart">';
						echo '$'.number_format(round($item["P_price"] * $item["OL_quantity"], 2),2);
						echo '</td>';
						echo '<td class="detailCart">';
						echo '<form name="item_cart_form'.$counter.'" '.
							 'id="item_cart_form'.$counter.'" action="cart.php" method="get">';
						echo '<input type="number" name="quantity" min = "1" value="'.$item["OL_quantity"].'"/>';
						echo '<br />';
						echo '<input type="hidden" name="ol_id" value="'.$item["OL_id"].'"/>';
						echo '<input type="hidden" name="o_id" value="'.$item["O_id"].'"/>';
						echo '<button type="submit" name="cart_action" value="Update">Update</button>';
						echo '<br />';
						echo '</form>';
						echo '<a href="cart.php?cart_action=Delete&amp;o_id=' 
							  .$item["O_id"]. '&amp;ol_id=' 
							  .$item["OL_id"]. '" id="delete">Delete</a>';
						echo '</td>';
						echo '</tr>';
						$counter++;
				}
				
				// Make a query request and calculate the total amount from the order
				$query = "select SUM(P_Price * OL_Quantity) as total from orderline join products".
						 " using (P_id) where O_id".
						 " in (select O_id from orders where O_status = '".PENDING."'".
						 " and O_username = '".$queryUsername."')";
				$calculation = $db->query($query);
				$getTotal = $calculation->fetch_assoc();
				$totalPrice = $getTotal["total"];
				echo '<tr>';
				echo '<th></th>';
				echo '<th></th>';
				echo '<th>Total: </th>';
				echo '<th>$'.number_format(round($totalPrice, 2), 2).'</th>';
				echo '</tr>';
				echo '<tr><th>&nbsp;</th><th></th><th></th><th></th></tr>';
				echo '<tr>';
				echo '<th></th>';
				echo '<th></th>';
				echo '<th colspan="2">';
				echo '<a href="cart.php?cart_action=Checkout&amp;o_id=1" id="checkOutButton">';
				echo 'Checkout';
				echo '</a></th>';
				echo '</tr>';
				echo '</table>';
				echo '</div>';
				
				// Free the memory and close the database
				$showItem->free();
				$db->close();
			}
		}
	?>
			
 
</body>
</html>