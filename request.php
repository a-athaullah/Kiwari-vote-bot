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

$content = file_get_contents("php://input");
$update = json_decode($content, true);
$message = $update["message"]["text"];
$sender = $update["from"]["qiscus_email"];
$room_id = $update["chat_room"]["qiscus_room_id"];

// variabel bot 

$QISCUS_APP_ID = "kiwari-prod";
$QISCUS_SDK_SECRET = "kiwari-prod-123";

$url = 'https://api.qiscus.com/api/v2/mobile/post_comment';

$dbErrorMessage = "DB ERROR occured";
echo $message;

$responseToMessage = false;

error_log(print_r($message, TRUE));
if ($message == "votebot create") {
    $data = '{
        "token": "'.$bot_token.'",
        "topic_id": "'.$room_id.'",
        "type": "buttons",
        "payload": {
          "text": "Press button to create poll",
          "buttons": [
              {
                  "label": "Create Poll",
                  "type": "link",
                  "payload": {
                      "url": "https://vote-bot-kiwari.herokuapp.com/create_vote.php?roomId='.$room_id.'"
                  }
              }
          ]
        }
    }';
    
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header'=>array("Content-type: application/json",
                            "QISCUS_SDK_SECRET: ".$QISCUS_SDK_SECRET,
                            "QISCUS_SDK_APP_ID: ".$QISCUS_APP_ID),
            'content' => $data)
            )
        ); 
    $response = file_get_contents($url, FALSE, $context);
    error_log(print_r($data, TRUE));
}else if (substr($message, 0, 4) == "VBV."){

    $comment = array();

    $messageSplit = explode(" ", $message); 
    $idSplit = explode(".", $messageSplit[0]);
    $optionId = $idSplit[1];

    error_log(print_r($messageSplit[0],TRUE));

    $optQuery = "SELECT * FROM vote_item where id = $optionId";
    $optResult = pg_query($db, $optQuery);
    $optRows = pg_num_rows($optResult);
    if (!$optResult) {
        error_log(print_r($dbErrorMessage,TRUE));
        exit;
    }
    if ($optRows == 0){
        error_log(print_r("Option not found",TRUE));
        exit;
    }

    $selectedOption = pg_fetch_assoc($optResult); 

    $voteQuery = "SELECT * FROM vote where id = $selectedOption[vote_id]";
    $voteResult = pg_query($db, $voteQuery);
    $optRows = pg_num_rows($optResult);
    if (!$voteResult) {
        error_log(print_r($dbErrorMessage,voteResult));
        exit;
    }
    if (!$voteResult) {
        error_log(print_r("Polling not found",voteResult));
        exit;
    }
    $vote = pg_fetch_assoc($voteResult); 

    $checkQuery = "SELECT * FROM vote_result WHERE user_id = '$sender' AND vote_id = $vote[id] ";
    $checkResult = pg_query($db, $checkQuery);
    $checkRows = pg_num_rows($checkResult);
    if (!$checkResult) {
        error_log(print_r($dbErrorMessage,TRUE));
        exit;
    }
    
    $updateQuery = "";
    
    if ($checkRows == 0){
        $updateQuery = "INSERT INTO vote_result (user_id,vote_id,vote_item_id) VALUES ('$sender',$vote[id],$optionId)";
        error_log(print_r("Belum vote",TRUE));
    }else{
        $updateQuery = "UPDATE vote_result SET vote_item_id = $optionId WHERE user_id = '$sender' AND vote_id = $vote[id]";
        error_log(print_r("Sudah vote",TRUE));
    }
    
    pg_query($db,$updateQuery);
    
    $comment['token'] = $bot_token;
    $comment['type'] = "card";
    $comment['topic_id'] = $vote['roomId'];
    $data = array();
    $data['text'] = "poll #$vote[id]";
    $data['image'] = "http://blog.qsample.com/wp-content/uploads/2014/08/voting.jpg";
    $data['title'] = $vote['title'];
    $data['description'] = $vote['body'];
    $data['url'] = "https://web.kiwari.id";
    $data['buttons'] = array();

    $optionQuery = "SELECT a.*,COUNT(b.vote_item_id) total FROM vote_item a LEFT JOIN vote_result b ON a.id = b.vote_item_id WHERE a.vote_id = $vote[id] GROUP BY a.id";
    
    $optionsRes = pg_query($db, $optionQuery);;


    while ($row = pg_fetch_assoc($optionsRes)) {
        $button = array();
        $button['label'] = "VBV.".$row['id']." ".$row['value']." (".$row['total'].")";
        $button['type'] = "postback";
        $payload = array();
        $payload['url'] = "https://vote-bot-kiwari.herokuapp.com";
        $payload['method'] = "POST";
        $payload['payload'] = null;
        $button['payload'] = $payload; 
        array_push($data[buttons],$button);
    }
    $comment['payload'] = $data;
    $json = json_encode($comment);

    error_log(print_r($json,TRUE));
    
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
}

pg_close($db);
     
?>