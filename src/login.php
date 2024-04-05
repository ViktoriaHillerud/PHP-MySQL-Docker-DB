<?php
session_start();
include 'hpcrud.php';

if(isset($_SESSION["username"])) {
	header("Location: profile.php");
    exit();
} 

if (isset($_POST["login"])) {
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        echo "<center><h1>Please fill all the fields</h1></center>";
    } else {
        $user = login($_POST["username"], $_POST["password"]);
        if ($user) {
            $_SESSION["username"] = $user['username'];
            header("Location: hp.php");
            exit();
        } else {
            echo "<center><h1>Invalid username or password</h1></center>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login</title>
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
		<div>
		<label for="password">Password</label>
		<input type="text" name="password" id="password">
		</div>
	
		<button type="submit" name="login">Login</button>
	</form>
	</div>
	
</body>
</html>