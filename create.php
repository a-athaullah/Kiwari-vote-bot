<?php 
$db_host = "ec2-54-235-159-101.compute-1.amazonaws.com";
$db_name = "d3poplfkcj6hve";
$db_user = "lbhprhtzuemwyg";
$db_pass = "531f614bf06282f8d96c6fab44a11876bdd060ec091c71af5fd6b124d534f6b0";
$db_port = "5432";

$db_conn_string = "host=".$db_host." port=".$db_port." dbname=".$db_name." user=".$db_user." password=".$db_pass;

$db = pg_connect($db_conn_string);

if (!$db) {
    echo "An DB error occurred.\n";
    exit;
}


$QISCUS_APP_ID = "kiwari-prod";
$QISCUS_SDK_SECRET = "kiwari-prod-123";
$bot_token = "Q0IQHdUvmPs5JKfSV9ml";

$url = 'https://api.qiscus.com/api/v2/mobile/post_comment';

$roomId = $_POST["roomid"];
$voteTitle = $_POST["title"];
$voteDesc = $_POST["desc"];

// print_r($_POST);
$voteQuery = "INSERT INTO vote (title,body,roomId) VALUES ('$voteTitle','$voteDesc','$roomId') RETURNING id;";
$voteQueryExec = pg_query($db,$voteQuery); 
$voteRow = pg_fetch_row($voteQueryExec); 
$voteId = $voteRow['0'];

error_log(print_r("new voteId: ".$voteId, TRUE));

$message = array();
$message['token'] = $bot_token;
$message['type'] = "card";
$message['topic_id'] = $roomId;
$data = array();
$data['text'] = "poll #$voteId";
$data['image'] = "http://blog.qsample.com/wp-content/uploads/2014/08/voting.jpg";
$data['title'] = $voteTitle;
$data['description'] = $voteDesc;
$data['url'] = "https://web.kiwari.id";
$data['buttons'] = array();

foreach ($_POST["option"] as $voteItem){
    $optQuery = "INSERT INTO vote_item (vote_id,value) VALUES ('$voteId','$voteItem') RETURNING id;";
    $optQueryExec = pg_query($db,$optQuery); 
    $optRow = pg_fetch_row($optQueryExec); 
    $optId = $optRow['0'];

    $button = array();
    $button['label'] = "VBV.".$optId." ".$voteItem;
    $button['type'] = "postback";
    $payload = array();
    $payload['url'] = "https://vote-bot-kiwari.herokuapp.com";
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

pg_close($db);

print("Poll created, please back to your room.")


?>