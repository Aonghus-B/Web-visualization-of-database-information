<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Information</title>
  <link rel="stylesheet" type="text/css" href="stylesheet.css" />
</head>

<body>
  <nav>
    <?php include "nav.php" ?>
  </nav>

  <div class="container">
    <h1 class="heading">
      Product Information
    </h1>
  </div>

  <div class="container">
    <div class="floatL" style="padding-left: 4%">
      <form method="post" action="index.php">

        <select name="sortOrder">
          <option value="">Sort by Quantity in Stock</option>
          <option value="ASC">Sort Accending</option>
          <option value="DESC">Sort Decending</option>
        </select>
        <input type="submit" name="submit" value="Sort">

      </form>
    </div>

    <div class="floatL" style="padding-left: 3%">
      <form method="post" action="index.php">
        <input type="text" name="stockFilter" placeholder="Filter by Max Stock">
        <input type="submit" name="submit" value="Filter">
      </form>
    </div>

    <div class="floatL" style="padding-left: 0%">

    </div>
    <form method="post" action="index.php">
      <label>Filter by Product Lines: </label>
      <input type="checkbox" name="plineFilter[]" value="Classic Cars"> Classic Cars
      <input type="checkbox" name="plineFilter[]" value="Motorcycles"> Motorcycles
      <input type="checkbox" name="plineFilter[]" value="Planes"> Planes
      <input type="checkbox" name="plineFilter[]" value="Ships"> Ships

      <input type="submit" name="submit" value="Filter">
    </form>
  </div>

  <div class="container" style="padding-bottom: 20px">
    <?php
    try {


      function myErrorHandler($errno, $errstr)
      # Custon error handling function 
      # Reference lecture 16 slides
      {
        echo "<br>An error occured<b/>: The error number is: [$errno] The error message is: [$errstr<br>";
        echo "Please try to reload the website, check database connection if unsuccessful.";
        die();
      }

      set_error_handler("myErrorHandler");
    

      function echoTable($SQL)
      # Function to return a table based on sql data using PDO to retrieve and display the data specified in the query.
      # Reference w3schools.com
      {
        require "config.php";

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo "<table class='styleTable'>";
        echo "<tr><th>Product Name</th><th>Product Code</th><th>Product Description</th><th>Quantity In Stock</th><th>MSRP (€)</th></tr>";

        class TableRows extends RecursiveIteratorIterator
        {
          function __construct($it)
          {
            parent::__construct($it, self::LEAVES_ONLY);
          }
          function current()
          {
            return "<td>" . parent::current() . "</td>";
          }
          function beginChildren()
          {
            echo "<tr>";
          }
          function endChildren()
          {
            echo "</tr>" . "\n";
          }
        }
        $stmt = $conn->prepare($SQL);
        $stmt->execute();
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        foreach (new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k => $v) {
          echo $v;
        }
        $conn = null;
        echo "</table>";
      }


      # if construct to recieve and interpret the users inputs and call the table generating functions with the correct SQL query. else caluse displays the default view.
      if (isset($_POST["submit"])) {
        $stockFilter = $_POST["stockFilter"] ?? "";
        $plineFilter = (isset($_POST["plineFilter"])) ? $_POST["plineFilter"] : array();
        $sortOrder = $_POST["sortOrder"] ?? "";

        if ($stockFilter != "" && ($sortOrder != "ASC" && $sortOrder != "DESC")) {
          $x = (int)$stockFilter;
            if ($x < 1) {
            trigger_error("The input must be an integer greater 0.");
          }

          else {
            $stmt = "SELECT productName, productCode, productDescription, quantityInStock, MSRP FROM products WHERE quantityInStock < $x";
            echoTable($stmt);  
          }
        } 
        
        elseif (count($plineFilter) > 0) {
          $x = implode("', '", $plineFilter);
          $stmt = "SELECT productName, productCode, productDescription, quantityInStock, MSRP FROM products WHERE productLine IN ('$x')";
          echoTable($stmt);
        } 
        
        elseif ($sortOrder == "ASC" || $sortOrder == "DESC") {
          $stmt = "SELECT productName, productCode, productDescription, quantityInStock, MSRP FROM products ORDER BY quantityInStock $sortOrder";
          echoTable($stmt);
        } 
        
        else {
          $stmt = "SELECT productName, productCode, productDescription, quantityInStock, MSRP FROM products";
          echoTable($stmt);
        }
      } 
      
      else {
        $stmt = "SELECT productName, productCode, productDescription, quantityInStock, MSRP FROM products";
        echoTable($stmt);
      }
    } 
    
    catch (PDOException $e) {
      // echo $sql . "<br>" . $e->getMessage();
      echo "Connection failed: " . $e->getMessage();
    }
    ?>
  </div>

  <footer>
    <?php
    include "footer.php"
    ?>
  </footer>

</body>
</html>