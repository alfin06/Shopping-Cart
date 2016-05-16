<!DOCTYPE HTML> 
<!--
	Name: Alfin Rahardja
	Class: CS 368
	Due-date: April 25, 2016
-->
<html>
<head>
	<meta name="author" content="Mr Sparks" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
	<link href="style06.css" rel="stylesheet" type="text/css" />
	<title>Shopping Cart</title>
	</head>
<body>
   <header>
		<img id="logo" src="images/adidas.gif" alt="Adidas Logo" />
		<nav>
			<a href="program6.php" id="productsButton">Products</a>
			<span id="separator"> | </span>
			<a href="cart.php"><img id="cartLogo" src="images/cart.png" alt="Shopping Cart logo" /></a>
			<!-- The image is from http://www.inmotionhosting.com -->
		</nav>
		<div id="headerImage"><img id="headerImage" src="images/header3.jpg" alt="Header Image" /></div>
		<!-- The image is from http://www.prodirectrunning.com -->	
	</header>
	<?php
		// Member Variables
		$username = $_SERVER ['AUTH_USER'];
	
		echo '<h2>Your Items:</h2>';
		// Connect to the database
		@ $db = new mysqli("csweb", "123105", "pcc1935");
		$db->select_db("cs368_123105");
	
		// Check if the ID from details page is not NULL
		if(isset($_GET["p_id"]))
		{
			// Get the item that is going to be added to the cart
			$itemId = $_GET["p_id"];
		
			// Make a query request and receive the item data that is going
			// to be added to the database
			$orderData = $db->query("select max(O_id) + 1 as newId from orders");
			$orderRecord = $orderData->fetch_assoc();
			$orderId = $orderRecord["newId"];
			$orderDate = date("m/j/Y");
			$status = 'P';
			
			$query = "insert into orders (O_id, O_status, O_username, O_date) values (?, ?, ?, ?)";
			$addOrder = $db->prepare($query);
			
			// Check if the O_id in orders table is not NULL
			if(!isset($orderId))
			{
				$GLOBALS["orderId"] = 1;
				$addOrder->bind_param("isss", $orderId, $status, $username, $orderDate);
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
				$addOrderLine->bind_param("iiii", $orderLineId, $orderId, $itemId, $orderLineQuantity);
				$addOrderLine->execute();
			}
			else
			{
				$addOrderLine->bind_param("iiii", $orderLineId, $orderId, $itemId, $orderLineQuantity);
				$addOrderLine->execute();
			}
			
			// Free the memory
			$orderLineData->free();
			
			// Print the items into the cart page
			printCart($db, $username);
		}
		else
		{
			if(strcmp($_GET["cart_action"], "update") == 0)
			{
				$quantity = $_GET["quantity"];
				$orderLineId = $_GET["ol_id"];
				
				$query = 	"update orderline".
							" set OL_quantity = ?".
							" where OL_id = ?";
							
				$update = $db->prepare($query);
				$update->bind_param("ii", $quantity, $orderLineId);
				$update->execute();
			}
			elseif(strcmp($_GET["cart_action"], "delete") == 0)
			{
				$userId = $_POST["userid"];

				$query = 	"delete from inclass where inclass_id = ?";

				$r = $db->prepare($query);
				$r->bind_param("i", $userId);
				$r->execute();
			}
			// Print the items into the cart page
			printCart($db, $username);
		}
		// Close the database
		$db->close();
	?>
		<a href="cart.php?cart_action=checkout&amp;o_id=1">Check out</a>
		
	<?php
		/*****************************************************************/
		/*  Print the item into the shopping cart page					 */
		/*****************************************************************/
		function printCart($db, $username)
		{
			$query = "select * from orderline join products using (P_id) where O_id in (select O_id from orders where O_status = 'P')";
			$showItem = $db->query($query);
			
			echo '<table id = cartTable>';
			
			while($item = $showItem->fetch_assoc())
			{
				echo '<tr>';
				echo '<td>';
				echo '<img src="data:image/jpeg;base64,'.base64_encode($item["P_picture"]).'" id="item'.$item["P_id"].'"/>';
				echo '</td>';
				echo '<td class="itemName">';
				echo $item["P_name"];
				echo '</td>';
				echo '<td class="priceCart">';
				echo '$'.$item["P_price"];
				echo '</td>';
				echo '<td>';
				echo '<form name="item1_cart_form" id="item1_cart_form" action="cart.php" method="get">';
				echo '<input type="number" name="quantity" value="'.$item["OL_quantity"].'"/>';
				echo '<br / >';
				echo '<input type="hidden" name="ol_id" value="' .$item[OL_id]. '"/>';
				echo '<input type="hidden" name="o_id" value="' .$item[O_id]. '"/>';
				echo '<button type="submit" name="cart_action" value="update">Update</button>';
				echo '<br />';
				echo '</form>';
				echo '<a href="cart.php?cart_action=delete&amp;o_id=' .$item[O_id]. '1&amp;ol_id=' .$item[OL_id]. '">Delete Item</a>';
				echo '</td>';
				echo '</tr>';
			}
			echo '<tr>';
			echo '<th></th>';
			echo '<th></th>';
			echo '<th>Total: ---</th>';
			echo '</table>';
		}
	?>
			
 
</body>
</html>

