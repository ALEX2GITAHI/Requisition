<?php
function sendMessage($phoneNumber, $message) {
    $url = "https://api.infobip.com/sms/1/text/single";
    $apiKey = "c4cda6284765f0750dbb9836d496a798-38728d04-de3c-4005-9cd3-9d7b2334a864"; // Replace with your actual API key

    $data = [
        "from" => "ALEX",
        "to" => $phoneNumber,
        "text" => $message
    ];

    $options = [
        "http" => [
            "header" => [
                "Content-Type: application/json",
                "Authorization: App $apiKey" // Try "Bearer" if "App" fails
            ],
            "method"  => "POST",
            "content" => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        // Display the response headers to help with debugging
        echo "Failed to send message to $phoneNumber.";
        $error = error_get_last();
        echo "\nError details: " . $error['message'];
    } else {
        echo "Message sent successfully!";
    }
}
?>

