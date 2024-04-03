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
        // If an error occurs, roll back the transaction
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
            // Check if there is an existing relation with these two characters
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



?>