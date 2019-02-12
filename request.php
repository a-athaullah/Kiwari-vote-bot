<?php 

function createVote($db,$room_id,$creator,$token){
    $statement = $db->prepare("INSERT INTO mytable (myvalue) VALUES(?)");
    $db->exec("INSERT INTO vote (creator) VALUES ('$creator');");
    $lastid = $db->lastInsertRowID();

    $message = "Vote Created \nVote id: ".$lastid."\nto set vote title, use command:\nvotebot#".$lastid."#settitle#[your vote title]\nremember not to use '#' inside the title";

    $data = array(
        'token' => $token,
        'topic_id' => $room_id,
        'comment' => $message
      );
    
    return $data;
}

$db = new SQLite3('vote.db');
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
                      "url": "https://7d9db3c2.ngrok.io/create_vote.php?roomId='.$room_id.'"
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
    $selectedOption = $db->querySingle($optQuery,TRUE); 

    $voteQuery = "SELECT * FROM vote where id = $selectedOption[vote_id]";
    $vote = $db->querySingle($voteQuery,TRUE);

    $checkQuery = "SELECT * FROM vote_result WHERE user_id = '$sender' AND vote_id = $vote[id] ";
    $checkData = $db->querySingle($checkQuery,TRUE);
    
    $voteQuery = "";
    
    if (empty($checkData)){
        $voteQuery = "INSERT INTO vote_result (user_id,vote_id,vote_item_id) VALUES ('$sender',$vote[id],$optionId)";
        error_log(print_r("Belum vote",TRUE));
    }else{
        $voteQuery = "UPDATE vote_result SET vote_item_id = $optionId WHERE user_id = '$sender' AND vote_id = $vote[id]";
        error_log(print_r("Sudah vote",TRUE));
    }
    
    $db->exec($voteQuery);
    
    $comment['token'] = $bot_token;
    $comment['type'] = "card";
    $comment['topic_id'] = $vote['roomId'];
    $data = array();
    $data['text'] = "poll #$vote[id]";
    $data['image'] = "http://blog.qsample.com/wp-content/uploads/2014/08/voting.jpg";
    $data['title'] = $vote['title'];
    $data['description'] = $vote['body'];
    $data['url'] = "https://qiscus.com";
    $data['buttons'] = array();

    $optionQuery = "SELECT a.*,COUNT(b.vote_item_id) total FROM vote_item a LEFT JOIN vote_result b ON a.id = b.vote_item_id WHERE a.vote_id = $vote[id] GROUP BY a.id";
    $optionsRes = $db->query($optionQuery);


    while ($row = $optionsRes->fetchArray()) {
        $button = array();
        $button['label'] = "VBV.".$row['id']." ".$row['value']." (".$row['total'].")";
        $button['type'] = "postback";
        $payload = array();
        $payload['url'] = "https://7d9db3c2.ngrok.io/";
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

$db = null;
 
// if ($responseToMessage) {
//     $response = file_get_contents($url, FALSE, $context);
//     error_log(print_r($response, TRUE)); 
//     $db = null;
// }


    
?>