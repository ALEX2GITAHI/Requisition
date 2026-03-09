<?php
function sendMessage($to, $text)
{
    $url = "https://api.infobip.com/sms/2/text/advanced";
    $apiKey = "6a07e3d405dd99d826800d011f336dc6-274ecbf9-34e1-4f9e-9477-4fc17c175a30";  // Paste your API key here
    $sender = "PCEA MUKINYI";          // Approved sender name

    $data = [
        "messages" => [
            [
                "from" => $sender,
                "destinations" => [["to" => $to]],
                "text" => $text
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: App $apiKey",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log("Infobip API Error: " . $err);
        return false;
    }

    $res = json_decode($response, true);
    return isset($res['messages'][0]['status']['groupId']) && $res['messages'][0]['status']['groupId'] == 1;
}