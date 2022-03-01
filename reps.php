<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Information</title>
  <link rel="stylesheet" type="text/css" href="stylesheet.css" />
</head>

<body>
  <nav>
    <?php include "nav.php"; ?>
  </nav>

  <div class="container">
    <h1 class="heading">
      Staff Information and Client Sales Data
    </h1>
  </div>

  <div class="container">
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


      function connect($SQL)
      # Shared function used by the table generating functions to fatchAll sql data as arrays using PDO
      # Reference w3schools.com
      {
        require "config.php";
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare($SQL);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
      }


      function loopTablesEmployees($SQL)
      # Functoion to generate a table to display employee information on the reps page, includes button to swap to customers table.
      {
        $result = connect($SQL);
        echo "<table class='styleTable'>";
        echo "<tr><th>Name</th><th>Email</th><th>Office Address</th><th>Manager</th><th>Customer Info</th></tr>";
        foreach ($result as $row) {
          echo "<tr><td>" . $row["name"] . "</td><td>" . $row["email"] . "</td><td>" . $row["office address"] . "</td><td>" . $row["manager name"] . "</td><td><a href='reps.php?id="  . $row["employeeNumber"] . "'>Customer Info</a></td></tr>";
        }
        $conn = null;
        echo "</table>";
      }


      function loopTablesCustomers($SQL)
      # Functoion to generate a table to display customer information on the reps page, user uses back button on browser to return to employyes table.
      {
        $result = connect($SQL);
        echo "<table class='styleTable'>";
        echo "<tr><th>Name</th><th>Address</th><th>Credit Limit</th><th>Order Numbers</th><th>Total Payments Received</th></tr>";
        foreach ($result as $row) {
          echo "<tr><td>" . $row["customername"] . "</td><td>" . $row["address"] . "</td><td>" . "€ " . $row["creditLimit"] . "</td><td>" . $row["orders"] . "</td><td>" . "€ " . $row["payment"] . "</td></tr>";
        }
        $conn = null;
        echo "</table>";
      }


      # if construct to recieve and interpret the users inputs and call the table generating functions with the correct SQL query. else caluse displays the default view.
      if (isset($_GET["id"])) {
        $s = $_GET["id"];

        $SQL1 = " SELECT ROUND(SUM(DISTINCT P.amount), 2) AS 'payment' FROM customers C, payments P, Orders O WHERE salesRepEmployeeNumber = $s AND C.customerNumber = O.customerNumber AND C.customerNumber = P.customerNumber GROUP BY C.customerNumber";
        $x = (connect($SQL1));
        echo "<p>Total sales generated the Sales Rep: €" . $x[0]["payment"] . "</p>";

        $SQL2 = " SELECT customername,  CONCAT(addressLine1, ', ', city, ', ',country) AS 'address', creditLimit, GROUP_CONCAT(DISTINCT orderNumber SEPARATOR ', ') AS 'orders', ROUND(SUM(DISTINCT P.amount), 2) AS 'payment' FROM customers C, payments P, Orders O WHERE salesRepEmployeeNumber = $s AND C.customerNumber = O.customerNumber AND C.customerNumber = P.customerNumber GROUP BY C.customerNumber";
        loopTablesCustomers($SQL2);
      } else {
        $stmt = "SELECT E1.employeeNumber, CONCAT (E1.firstName, ' ', E1.lastName) AS 'name', E1.email, CONCAT(O.addressLine1, ', ', O.city, ', ',O.country) AS 'office address', CONCAT ( E2.firstName, ' ', E2.lastName) AS 'manager name' FROM employees E1, employees E2, offices O WHERE E1.jobTitle = 'Sales Rep' AND E1.reportsTo = E2.employeeNumber AND E1.officeCode = O.officeCode";
        loopTablesEmployees($stmt);
      }
    } catch (PDOException $e) {
      // echo $sql . "<br>" . $e->getMessage();
      echo "Failed to connect to server, check the server is running and that database configuration details are correct: " . $e->getMessage();
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