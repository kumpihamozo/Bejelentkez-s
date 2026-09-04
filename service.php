<?php
$servername = "localhost";
$dbusername = "root";   // change if needed
$dbpassword = "";       // change if needed
$nyaldki = "parkside_db";

$conn = new mysqli($servername, $dbusername, $dbpassword, $nyaldki);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_GET['a']) and isset($_GET['b'])){
    $email = $_GET['a'];
    $password = $_GET['b'];
    $sql = "SELECT * FROM felhasznalok WHERE email = '$email' AND password = '$password'";
    // Execute the query
    if(!($result = mysqli_query($conn, $sql))){
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
    }else{
        $resultCheck = mysqli_num_rows($result);
        if($resultCheck > 0){
            while ($row = mysqli_fetch_assoc($result)){
                echo $row['name'];
                //header('Location: http://localhost/bejelentkez-s/bejelentkezés.html?name='.$row['name']);
            }
        }else{
            echo "nincs találat";
            //header('Location: http://localhost/bejelentkezo/parkside.html');
        }
    }

    // Close the database connection
    mysqli_close($conn);
    
    

}
?>
