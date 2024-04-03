<?php
    include 'hpcrud.php';
?>


<html lang="en">

<body>
	<h1>Welcome to the HP-database!</h1>

	<form method="GET" action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<label>Search HP-characters:</label>
	<input type="text" name="searchQuery">
	<button type="submit">Search</button>
	</form>

	
	
	<?php
	if(isset($_GET["searchQuery"])) {
		$characterName = sanitize($_GET["searchQuery"]); 
		$character = getCharacter($characterName);
		if ($character) {
			echo "Name: " . htmlspecialchars($character['fullName']);
			echo("<br>");
			echo "Wand: " . htmlspecialchars($character['wand']);
			echo("<br>");
			echo "Birthdate: " . htmlspecialchars($character['birthdate']);
			echo("<br>");
			echo "Pet: " .  htmlspecialchars($character['pet']);
			echo("<br>");
			echo "Patronus: " . htmlspecialchars($character['patronus']);
			echo("<br>");
			echo "Species: " . htmlspecialchars($character['species']);
			echo("<br>");
			echo "Signum Quote: " . htmlspecialchars($character['quote']);
			
		} 
	}
	?>

<h4>Add a new character to the HP-collection</h4>

<form method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<label for="fullName">Name:</label><br>
	<input type="text" name="fullName" id="fullName"><br>
	<label for="wand">Wand:</label><br>
	<input type="text" name="wand" id="wand"><br>
	<label for="pet">Pet:</label><br>
	<input type="text" name="pet" id="pet"><br>
	<label for="birthdate">Birthdate:</label><br>
	<input type="text" name="birthdate" id="birthdate"><br>
	<label for="species">Species:</label><br>
	<input type="text" name="species" id="species"><br>
	<label for="patronus">Patronus:</label><br>
	<input type="text" name="patronus" id="patronus"><br>
	<label for="quote">Quote:</label><br>
	<input type="text" name="quote" id="quote"><br>
	<label for="relatedCharacter">Related Character:</label><br>
<select name="relatedCharacter" id="relatedCharacter" onchange="showRelationTypeInput()">
    <option value="">Select a character...</option>
    <?php
        foreach (getAllCharacters() as $relCharacter) {
            echo "<option value=\"" . htmlspecialchars($relCharacter['charId']) . "\">" . htmlspecialchars($relCharacter['fullName']) . "</option>";
        }
    ?>
</select><br>
<span id="relationTypeInput"></span>

	<input type="submit" value="Add Character">
</form>

<?php

if(isset($_POST["fullName"])){
    createCharacter(
        $_POST["fullName"], 
        $_POST["wand"], 
        $_POST["pet"], 
        $_POST["birthdate"],  
        $_POST["species"],  
        $_POST["patronus"],  
        $_POST["quote"],
        $_POST["relatedCharacter"],
        $_POST["relationType"]
    );
}

echo("<ul>");
    foreach(getAllCharacters() as $character)
    {
        print("<li>");
        print("<a href='" . $_SERVER["PHP_SELF"] . "?fullName=" . $character["fullName"] . "'>");
        print($character['fullName']); 
		print("<button>Edit</button>");
        print("</a>");
        print("</li>");
    }
    echo("</ul>");

	if(isset($_GET["fullName"]))
	{
		$character = getCharacter($_GET["fullName"]);
		if($character !== null) {
		
			$relations = getCharacterRelations($character['charId']);
			$currentRelation = $relations[0] ?? null; 

			?>
			<form method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
			<label for="updateName">Name:</label><br>
	<input type="text" name="updateName" id="updateName" value="<? print($character["fullName"]) ?> "><br>
	<label for="updateWand">Wand:</label><br>
	<input type="text" name="updateWand" id="wand" value="<? print($character["wand"]) ?> "><br>
	<label for="updatePet">Pet:</label><br>
	<input type="text" name="updatePet" id="updatePet" value="<? print($character["pet"]) ?> "><br>
	<label for="updateBirthdate">Birthdate:</label><br>
	<input type="text" name="updateBirthdate" id="updateBirthdate" value="<? print($character["birthdate"]) ?> "><br>
	<label for="updateSpecies">Species:</label><br>
	<input type="text" name="updateSpecies" id="updateSpecies" value="<? print($character["species"]) ?> "><br>
	<label for="updatePatronus">Patronus:</label><br>
	<input type="text" name="updatePatronus" id="updatePatronus"value="<? print($character["patronus"]) ?> "><br>
	<label for="updateQuote">Quote:</label><br>
	<input type="text" name="updateQuote" id="updateQuote" value="<? print($character["quote"]) ?> "><br>
	<input type="hidden" name="updateChar" value="<?php echo htmlspecialchars($character['charId']); ?>">
	<label for="updateRelatedCharacter">Related Character:</label><br>
        <select name="updateRelatedCharacter" id="updateRelatedCharacter">
            <option value="">Select a character...</option>
            <?php
                $allCharacters = getAllCharacters();
                foreach ($allCharacters as $relCharacter) {
                    if ($currentRelation && ($currentRelation['charId1'] == $relCharacter['charId'] || $currentRelation['charId2'] == $relCharacter['charId'])) {
                        $selected = 'selected';
                    } else {
                        $selected = '';
                    }
                    echo "<option value=\"" . htmlspecialchars($relCharacter['charId']) . "\" $selected>" . htmlspecialchars($relCharacter['fullName']) . "</option>";
                }
            ?>
        </select><br>
        <?php
        
        if ($currentRelation) {
            echo '<label for="relationType">Relation Type:</label><br>';
            echo '<input type="text" name="relationType" id="relationType" value="' . htmlspecialchars($currentRelation['relationType']) . '"><br>';
        }
?>
	<input type="submit" value="Update Character">
			</form>

			<?php
		} else {
			print("Character not found.");
		}
	}


	if (isset($_POST['updateChar'])) {
		$charId = isset($_POST["updateChar"]) ? $_POST["updateChar"] : null;
	
		$relatedCharId = isset($_POST["updateRelatedCharacter"]) ? $_POST["updateRelatedCharacter"] : null;
		$relationType = isset($_POST["relationType"]) ? $_POST["relationType"] : null;
	
		updateCharacter(
			$charId,
			$_POST["updateWand"],
			$_POST["updatePet"],
			$_POST["updateBirthdate"],
			$_POST["updateSpecies"],
			$_POST["updatePatronus"],
			$_POST["updateQuote"],
			$relatedCharId,
			$relationType
		);
	}
	
	
?>



<script>
function showRelationTypeInput() {
    var selectedCharacter = document.getElementById('relatedCharacter').value;
    var inputContainer = document.getElementById('relationTypeInput');
    
    if (selectedCharacter) {
        inputContainer.innerHTML = `<label for="relationType">Relation Type:</label><br>
                                    <input type="text" name="relationType" id="relationType"><br>`;
    } else {
       
        inputContainer.innerHTML = '';
    }
}
</script>

</body>

</html>