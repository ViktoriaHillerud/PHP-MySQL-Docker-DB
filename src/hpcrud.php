<?php
include 'hpdb.php';

function sanitize($input)
{
    return htmlspecialchars(strip_tags($input)); 
}

function characterExists($fullName) {
    $conn = connectDB();

    $fullName = sanitize($fullName);

    $query = "SELECT COUNT(*) FROM Characters WHERE fullName = :fullName";
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":fullName", $fullName, PDO::PARAM_STR);
        $stmt->execute();

        $count = $stmt->fetchColumn();

        return $count > 0;
    }
    catch (PDOException $error) {
        echo "Error: " . $error->getMessage();
        return false; 
    }
}

function userExists($email) {
    $conn = connectDB();

    $email = sanitize($email);

    $query = "SELECT COUNT(*) FROM users WHERE email = :email";
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        $count = $stmt->fetchColumn();

        return $count > 0;
    }
    catch (PDOException $error) {
        echo "Error: " . $error->getMessage();
        return false; 
    }
}


function createCharacter($fullName, $wand, $pet, $birthdate, $species, $patronus, $quote, $relatedCharId = null, $relationType = null)
{
    $conn = connectDB();

    if (characterExists($fullName)) {
        echo "Character with name " . htmlspecialchars($fullName) . " already exists.";
        return;
    }

    $fullName = sanitize($fullName);
    $wand = sanitize($wand);
    $pet = sanitize($pet);
    $birthdate = sanitize($birthdate);
    $species = sanitize($species);
    $patronus = sanitize($patronus);
    $quote = sanitize($quote);

    try {
        $conn->beginTransaction();

        $query =<<<SQL
        INSERT INTO Characters (fullName, wand, pet, birthdate, species, patronus, quote) 
        VALUES (:fullName, :wand, :pet, :birthdate, :species, :patronus, :quote)
        SQL;

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":fullName", $fullName);
        $stmt->bindParam(":wand", $wand);
        $stmt->bindParam(":pet", $pet);
        $stmt->bindParam(":birthdate", $birthdate);
        $stmt->bindParam(":species", $species);
        $stmt->bindParam(":patronus", $patronus);
        $stmt->bindParam(":quote", $quote);
        $stmt->execute();

        $newCharId = $conn->lastInsertId();
    
		if ($relatedCharId && $relationType) {
    		insertRelation($newCharId, $relatedCharId, $relationType, $conn);
		}

        $conn->commit();
    }
    catch (PDOException $error) {
        $conn->rollBack();
        echo "Error: " . $error->getMessage();
    }
}



function insertRelation($charId1, $charId2, $relationType, $conn)
{
   
    $query =<<<SQL
    INSERT INTO relations (charId1, charId2, relationType) VALUES (:charId1, :charId2, :relationType);
    SQL;

    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":charId1", $charId1, PDO::PARAM_INT);
        $stmt->bindParam(":charId2", $charId2, PDO::PARAM_INT);
        $stmt->bindParam(":relationType", $relationType, PDO::PARAM_STR);
        $stmt->execute();
    }
    catch (PDOException $error) {
        echo "Error: " . $error->getMessage();
    }
}

function getCharacter($fullName)
{
    $conn = connectDB();

    $query =<<<SQL
    SELECT * FROM Characters WHERE fullName=:fullName;
    SQL;

    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":fullName", $fullName, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null; 
    }
    catch (PDOException $error) {
        echo "Error: " . $error->getMessage();
        return null;
    }
}

function getAllCharacters()
{
    $conn = connectDB();

    $query =<<<SQL
    SELECT * FROM Characters;
    SQL;

    try {
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }
    catch (PDOException $error) {
        echo "Error: " . $error->getMessage();
    }
}

function updateCharacter($charId, $wand, $pet, $birthdate, $species, $patronus, $quote, $relatedCharId = null, $relationType = null) {
    $conn = connectDB();

    try {
       
        $conn->beginTransaction();
        
        $charUpdateQuery = "UPDATE Characters SET wand = :wand, pet = :pet, birthdate = :birthdate, species = :species, patronus = :patronus, quote = :quote WHERE charId = :charId;";
        $charStmt = $conn->prepare($charUpdateQuery);
        $charStmt->bindParam(":charId", $charId);
        $charStmt->bindParam(":wand", $wand);
        $charStmt->bindParam(":pet", $pet);
        $charStmt->bindParam(":birthdate", $birthdate);
        $charStmt->bindParam(":species", $species);
        $charStmt->bindParam(":patronus", $patronus);
        $charStmt->bindParam(":quote", $quote);
        $charStmt->execute();

        if ($relatedCharId !== null && $relationType !== null) {
            $relationCheckQuery = "SELECT * FROM relations WHERE (charId1 = :charId AND charId2 = :relatedCharId) OR (charId1 = :relatedCharId AND charId2 = :charId);";
            $relationCheckStmt = $conn->prepare($relationCheckQuery);
            $relationCheckStmt->bindParam(":charId", $charId);
            $relationCheckStmt->bindParam(":relatedCharId", $relatedCharId);
            $relationCheckStmt->execute();
            $existingRelation = $relationCheckStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingRelation) {
                $relationUpdateQuery = "UPDATE relations SET relationType = :relationType WHERE relationId = :relationId;";
                $relationStmt = $conn->prepare($relationUpdateQuery);
                $relationStmt->bindParam(":relationType", $relationType);
                $relationStmt->bindParam(":relationId", $existingRelation['relationId']);
                $relationStmt->execute();
            } else {
                $relationInsertQuery = "INSERT INTO relations (charId1, charId2, relationType) VALUES (:charId, :relatedCharId, :relationType);";
                $relationStmt = $conn->prepare($relationInsertQuery);
                $relationStmt->bindParam(":charId", $charId);
                $relationStmt->bindParam(":relatedCharId", $relatedCharId);
                $relationStmt->bindParam(":relationType", $relationType);
                $relationStmt->execute();
            }
        }

        $conn->commit();
    } catch (PDOException $error) {
        $conn->rollBack();
        echo "Error: " . $error->getMessage();
    }
}

function deleteFavouriteChar($userId, $charId)
 {
	$conn = connectDB();

    $query = "DELETE FROM user_favorites WHERE userId = :userId AND charId = :charId";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':charId', $charId, PDO::PARAM_INT);
        $stmt->execute();

		if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
    catch (PDOException $error) {
        echo "Error: " . $error->getMessage();
        return null;
    }
 }

function getCharacterRelations($charId) {
    $conn = connectDB();

    $query = "SELECT * FROM relations WHERE charId1 = :charId OR charId2 = :charId";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":charId", $charId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $error) {
        echo "Error: " . $error->getMessage();
        return null;
    }
}

function register($username, $email, $pass) {
    $conn = connectDB();


	if (userExists($email)) {
        echo "User  " . htmlspecialchars($email) . " already exists.";
        return;
    }
    $username = sanitize($username);
    $email = sanitize($email);
    $pass = sanitize($pass);

    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

    try {
        
        $conn->beginTransaction();

        $query = "INSERT INTO users (username, email, pass) VALUES (:username, :email, :hashedPassword)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":hashedPassword", $hashedPassword); 
        $stmt->execute();

        $conn->commit();

		return true;

    } catch (PDOException $error) {
        $conn->rollBack();
        echo "Error: " . $error->getMessage();
    }
}


function login($username, $password) {
    $conn = connectDB();

    $username = sanitize($username);
    $password = sanitize($password);

    $query = "SELECT * FROM users WHERE username = :username";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['pass'])) {
			$_SESSION["username"] = $user['username'];
			$_SESSION["userId"] = $user['userId']; 
            return $user; 
        } else {
            return false;
        }
    }
    catch (PDOException $error) {
        echo "Error: " . $error->getMessage();
        return false;
    }
}


function getUserFavourites($userId) {
    $conn = connectDB();
    $favourites = array();

    try {
        $stmt = $conn->prepare("SELECT c.* FROM Characters c JOIN user_favorites uf ON c.charId = uf.charId WHERE uf.userId = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $favourites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 
    catch (PDOException $error){
        echo "Error: " . $error->getMessage();
    }
    return $favourites;
}

function createFavouriteChar($userId, $charId) {
	$conn = connectDB();
    try {
        $stmt = $conn->prepare("INSERT INTO user_favorites (userId, charId) VALUES (:userId, :charId)");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':charId', $charId, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    } 
    catch (PDOException $error){
        echo "Error: " . $error->getMessage();
        return false;
    }
 }
?>