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
	<title>Details</title>
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
		<img id="headerImage" src="images/header2.jpg" alt="Header Image" />
		<!-- The image is from shop.adidas.com.sg -->
	</header>
	<?php
		/*****************************************************************/
		/*  Get the product id of item which is clicked by the user and  */
		/*  print the detail of that item.								 */
		/*****************************************************************/
		// Get the product id of item chose by user
		$itemId = $_GET["p_id"];

		// Connect to the database and make a query request
		@ $db = new mysqli("csweb", "123105", "pcc1935");
		$db->select_db("cs368_123105");
		$query = "select * from products where P_id = ".$itemId;
		$product = $db->query($query);
		
		// Print all information about the product
		$item = $product->fetch_assoc();
		echo '<h2>'.$item["P_name"].'</h2>';
		echo '<div>';
		echo '<h5>Release: ' .$item["P_year"]. '</h5>';
		echo '<h3 class="price">';
		echo 'Price: $'.$item["P_price"];
		echo '</h3>';
		echo '<a href="cart.php?cart_action=Add&amp;p_id='.$itemId.'" '.
			 'id="addToCart">Add to Cart</a>';
		echo '</div>';
		echo '<img src="data:image/jpeg;base64,'.
			  base64_encode($item["P_picture"]).'" '.
			 'class="itemPicture" alt="Product Picture"/>';
		echo '<div class="desc">';
		echo '<h3>Description:</h3>';
		echo '<p>'.$item["P_description"].'</p>';
		echo '</div>';
		
		// Free and close the database
		$product->free();
		$db->close();
	?>
</body>
</html>