<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'hpcrud.php';

if (isset($_POST["register"])) {
    session_unset();
    
    if (empty($_POST["username"]) || empty($_POST["email"]) || empty($_POST["password"])) {
        $message = "<center><h1>Please fill all the fields</h1></center>";
    } else {
        $userId = register($_POST["username"], $_POST["email"], $_POST["password"]);
        if ($userId) {
            // Set the username and userId in the session
            $_SESSION["username"] = $_POST["username"];
            $_SESSION["userId"] = $userId;

            header("Location: hp.php");
            exit();
        } else {
            $message = "<center><h1>Registration failed.</h1></center>";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Register</title>
	<style>
		.wrapper {
			position: absolute;
			top: 20%;
			left: 35%;
			padding: 10px;
			border: 5px solid red;
			width: 300px;
			height: 250px;
			line-height: 40px;
			text-align: center;
			font-weight: bold;
		}
	</style>
</head>
<body>
	<div class="wrapper">
	<form method="POST" action="">
		<div>
		<label for="username">Username</label>
		<input type="text" name="username" id="username">
		</div>
		<div>
		<label for="email">email</label>
		<input type="text" name="email" id="email">
		</div>
		<div>
		<label for="password">Password</label>
		<input type="text" name="password" id="password">
		</div>
	
		<button type="submit" name="register">Login</button>
	</form>
	</div>
	
</body>
</html>