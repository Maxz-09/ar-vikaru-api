<?php

// Don't disturb
require __DIR__ . "/../../../vendor/autoload.php";

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$apimsg = $_SERVER["HTTP_APIMSG"];

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Function
// Function
function getTempMail()
{
    $api_url = "https://api.maher-zubair.tech/misc/tempmail";
    $json_data = file_get_contents($api_url);
    $data = json_decode($json_data, true);
    return $data['result'];
}

// Make sure JSON data is not incomplete
if (!empty($data->query) && !empty($data->appPackageName) && !empty($data->messengerPackageName) && !empty($data->query->sender) && !empty($data->query->message)) {
    $appPackageName = $data->appPackageName;
    $messengerPackageName = $data->messengerPackageName;
    $sender = $data->query->sender;
    $message = $data->query->message;
    $isGroup = $data->query->isGroup;
    $groupParticipant = $data->query->groupParticipant;
    $ruleId = $data->query->ruleId;
    $isTestMessage = $data->query->isTestMessage;

    // Process messages here
    $response = getTempMail();
    
 $msg = $apimsg . "\n\nTempMail: " . $response[0] . "\nID: " . $response[1] . "\nTimezone: " . $response[3];
    
    $replies = ["replies" => [["message" => $msg]]];

    http_response_code(200);
    echo json_encode($replies);
    
} else {
    http_response_code(400);
    echo json_encode(["replies" => [["message" => "❌ Error!"], ["message" => "JSON data is incomplete. Was the request sent by AutoResponder?"]]]);
}
?>