<?php 
$db = new SQLite3('vote.db');

$QISCUS_APP_ID = "kiwari-prod";
$QISCUS_SDK_SECRET = "kiwari-prod-123";
$bot_token = "Q0IQHdUvmPs5JKfSV9ml";

$url = 'https://api.qiscus.com/api/v2/mobile/post_comment';

$roomId = $_POST["roomid"];
$voteTitle = $_POST["title"];
$voteDesc = $_POST["desc"];

// print_r($_POST);
$db->exec("INSERT INTO vote (title,body,roomId) VALUES ('$voteTitle','$voteDesc','$roomId');");
$voteId = $db->lastInsertRowID();


$message = array();
$message['token'] = $bot_token;
$message['type'] = "card";
$message['topic_id'] = $roomId;
$data = array();
$data['text'] = "poll #$voteId";
$data['image'] = "http://blog.qsample.com/wp-content/uploads/2014/08/voting.jpg";
$data['title'] = $voteTitle;
$data['description'] = $voteDesc;
$data['url'] = "https://qiscus.com";
$data['buttons'] = array();

foreach ($_POST["option"] as $voteItem){
    $db->exec("INSERT INTO vote_item (vote_id,value) VALUES ('$voteId','$voteItem');");
    $optId = $db->lastInsertRowID();
    $button = array();
    $button['label'] = "VBV.".$optId." ".$voteItem;
    $button['type'] = "postback";
    $payload = array();
    $payload['url'] = "https://7d9db3c2.ngrok.io/";
    $payload['method'] = "POST";
    $payload['payload'] = null;
    $button['payload'] = $payload; 
    array_push($data[buttons],$button);
}
$message['payload'] = $data;
$json = json_encode($message);

$context = stream_context_create(array(
    'http' => array(
        'method' => 'POST',
        'header'=>array("Content-type: application/json",
                        "QISCUS_SDK_SECRET: ".$QISCUS_SDK_SECRET,
                        "QISCUS_SDK_APP_ID: ".$QISCUS_APP_ID),
        'content' => $json)
        )
    ); 

$response = file_get_contents($url, FALSE, $context);
error_log(print_r($response, TRUE));

// var_dump($json);

print("Poll created, please back to your room.")



?>