<?php
session_start();
include 'hpcrud.php';


$userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : null;

if (isset($_POST['deleteChar'])) {
    $charIdToDelete = (int)$_POST['deleteChar']; 
    deleteFavouriteChar($userId, $charIdToDelete);
    header("Location: profile.php");
    exit();
}

$favourites = $userId ? getUserFavourites($userId) : [];

if (isset($_GET["favouriteCharacter"]) && $userId) {
    createFavouriteChar($userId, $_GET["favouriteCharacter"]);
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Profile</title>
	<style>
		.listStyle {
			display: flex; 
		}
	</style>
</head>
<body>
	<h1>Profile</h1>

	<h4>My favourite characters</h4>
	<ul>
		<?php foreach ($favourites as $favourite): ?>
			<div class="listStyle">
			<li><?= htmlspecialchars($favourite['fullName']); ?></li>
			<form method="POST" action="<?= $_SERVER["PHP_SELF"] ?>">
			<button type="submit" name="deleteChar" value="<?= htmlspecialchars($favourite['charId']); ?>">Delete from favourites</button>
		</form>
			</div>
		<?php endforeach; ?>
	</ul>

	<form method="GET" action="<?= $_SERVER["PHP_SELF"] ?>">
		<label for="favouriteCharacter">Add a favourite character:</label><br>
		<select name="favouriteCharacter" id="favouriteCharacter" onchange="showRelationTypeInput()">
			<option value="">Select a character...</option>
			<?php foreach (getAllCharacters() as $relCharacter): ?>
				<option value="<?= htmlspecialchars($relCharacter['charId']); ?>">
					<?= htmlspecialchars($relCharacter['fullName']); ?>
				</option>
			<?php endforeach; ?>
		</select><br>
		<input type="submit" value="Add Character">
	</form>

	<a href="hp.php">Tillbaka</a>
</body>
</html>
