
CREATE DATABASE IF NOT EXISTS hp;
USE hp;


CREATE TABLE IF NOT EXISTS Characters (
    charId int NOT NULL AUTO_INCREMENT, 
    fullName varchar(50) NOT NULL,
    wand varchar(50) DEFAULT NULL,
    pet varchar(50) DEFAULT NULL,
    birthdate varchar(50) DEFAULT NULL,
    species varchar(50) DEFAULT NULL,
    patronus varchar(50) DEFAULT NULL,
    quote varchar(255) DEFAULT NULL,
    PRIMARY KEY (charId)
);


CREATE TABLE IF NOT EXISTS users (
    userId int NOT NULL AUTO_INCREMENT, 
    username varchar(50) NOT NULL,
    email varchar(50) NOT NULL,
    pass varchar(255) NOT NULL, 
    PRIMARY KEY (userId)
);


CREATE TABLE IF NOT EXISTS relations (
    relationId int NOT NULL AUTO_INCREMENT,
    charId1 int NOT NULL,
    charId2 int NOT NULL,
    relationType varchar(50) NOT NULL,
    PRIMARY KEY (relationId),
    FOREIGN KEY (charId1) REFERENCES Characters(charId) ON DELETE CASCADE,
    FOREIGN KEY (charId2) REFERENCES Characters(charId) ON DELETE CASCADE,
    CONSTRAINT chk_different_characters CHECK (charId1 != charId2)
);

CREATE TABLE IF NOT EXISTS user_favorites (
    userId int NOT NULL,
    charId int NOT NULL,
    PRIMARY KEY (userId, charId),
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
    FOREIGN KEY (charId) REFERENCES Characters(charId) ON DELETE CASCADE
);
