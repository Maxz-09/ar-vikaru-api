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
function getCariLaguResponse($query)
{
    $api_url = "https://api.nyxs.pw/tools/lirik?title=" . urlencode($query);
    $response = @file_get_contents($api_url);
    return $response ? json_decode($response, true)["result"] : null;
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
    $defaultMessage = "%response%";

    $messageReplies = isset($_SERVER["HTTP_REPLIES"]) ? $_SERVER["HTTP_REPLIES"] : $defaultMessage;

    if (isset($_SERVER["HTTP_EXPERIMENTAL"]) && $_SERVER["HTTP_EXPERIMENTAL"] === "true") {
        if (isset($_SERVER["HTTP_REGEX"])) {
            $regexPattern = $_SERVER["HTTP_REGEX"];
            if (preg_match($regexPattern, $message, $argument)) {
                $capturingGroup1 = isset($_SERVER["HTTP_ARG1"]) ? $_SERVER["HTTP_ARG1"] : 1;
                $argument1 = isset($argument[$capturingGroup1]) ? trim($argument[$capturingGroup1]) : '';
                $variable = ['%response%'];
                $replace = [getCariLaguResponse($argument1)];
                $msg = $apimsg . "\n\n" . "Title: " . $response["title"] . "\n Album: " . $response["album"] . "\nCover: " . $response["thumb"] . "\n\n> *_[Hasil Pencarian Lagu]_* \n\n" . $response["result"];
                $replies = ["replies" => [["message" => $msg]]];
            }
        }
    } else {
        $variable = ['%response%'];
        $replace = [getCariLaguResponse($message)];
        $response = str_replace($variable, $replace, $messageReplies);
        $replies = ["replies" => [["message" => $response]]];
    }

    http_response_code(200);
    echo json_encode($replies);
    		  
} else {
    http_response_code(400);
    echo json_encode(["replies" => [["message" => "❌ Error!"], ["message" => "JSON data is incomplete. Was the request sent by AutoResponder?"]]]);
}
?>