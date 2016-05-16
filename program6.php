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
	<title>Adidas Store</title>
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
		<img id="headerImage" src="images/header1.jpg" alt="Header Image" />
		<!-- The image is from shop.adidas.com.sg -->
	</header>
	
	<h2>Products</h2>
	<form name="myform" id="myform" 
          action="program6.php"
		  method="GET">
		<label for="sort">Sort by:</label>
		<select name="sort" id="sort"  onChange="document.location = this.value">
			<option>&nbsp;</option>
			<option value="program6.php?sort=Name1">Name (A-Z)</option>
			<option value="program6.php?sort=Name2">Name (Z-A)</option>
			<option value="program6.php?sort=Price1">Price (Low-High)</option>
			<option value="program6.php?sort=Price2">Price (High-Low)</option>
		</select>
	</form>
	
	<table>
		<?php
			/*****************************************************************/
			/*  Take data from products table in the database, then show it  */
			/*  to the web page.											 */
			/*****************************************************************/
			// Connect to the database and make a query request
			@ $db = new mysqli("csweb", "123105", "pcc1935");
			$db->select_db("cs368_123105");
			if(!isset($_GET["sort"]))
			{
				$order = "P_name";
			}
			else
			{
				if(strcmp($_GET["sort"], "Name1") == 0)
				{
					$order = "P_name";
				}
				elseif(strcmp($_GET["sort"], "Name2") == 0)
				{
					$order = "P_name desc";
				}
				elseif(strcmp($_GET["sort"], "Price1") == 0)
				{
					$order = "P_price";
				}
				else
				{
					$order = "P_price desc";
				}
			}
			$query = "select * from products order by ".$order;
			$product = $db->query($query);
			
			// Loop through the database, take the BLOB value and convert it
			// to JPEG
			echo '<tr>';
			while($item = $product->fetch_assoc())
			{
				echo '<td>';
				echo '<a href="details.php?p_id='.$item["P_id"].'">';
				echo '<img src="data:image/jpeg;base64,'.
					  base64_encode($item["P_picture"]).'" '.
					  'id="item'.$item["P_id"].'" alt="Product Picture"/>';
				echo '</a>';
				echo '</td>';
			}
			echo '</tr>';
			
			// Make a query request, then print all the product names
			$query = "select * from products order by ".$order;
			$product = $db->query($query);
			echo '<tr>';
			while($item = $product->fetch_assoc())
			{
				echo '<td class="itemName">';
				echo '<a href="details.php?p_id='.$item["P_id"].'">';
				echo $item["P_name"];
				echo '</a></td>';
			}
			echo '</tr>';
			
			// Make a query request, then print all the product prices
			$query = "select * from products order by ".$order;
			$product = $db->query($query);
			echo '<tr>';
			while($item = $product->fetch_assoc())
			{
				echo '<td class="priceTag">';
				echo '$'.$item["P_price"];
				echo '</td>';
			}
			
			// free and close the database
			$product->free();
			$db->close();
		?>
	</table> 
</body>
</html>