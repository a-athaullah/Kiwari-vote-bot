<?php
$db = new SQLite3('vote.db');

if(!$db) {
    echo $db->lastErrorMsg();
    return;
}

echo "Open database success...\n";

$db->exec(
    "CREATE TABLE IF NOT EXISTS vote (
        id INTEGER PRIMARY KEY AUTOINCREMENT, 
        title TEXT, 
        body TEXT,
        image TEXT,
        status TEXT,
        roomId TEXT)"
    );

$db->exec(
    "CREATE TABLE IF NOT EXISTS vote_item (
        id INTEGER PRIMARY KEY AUTOINCREMENT, 
        vote_id INTEGER,
        value TEXT)"
    );

$db->exec(
    "CREATE TABLE IF NOT EXISTS vote_result (
        id INTEGER PRIMARY KEY AUTOINCREMENT, 
        vote_item_id INTEGER,
        user_id TEXT,
        vote_id INTEGER)"
    );


echo realpath("vote.db");




?>