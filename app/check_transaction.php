<?php

    $data = json_decode(file_get_contents("php://input"), true);
    $ti = $data["ti"];
    $owner = $data["owner"];
    
    // Validate the input
    if (!isset($ti) || empty($ti)) {
        echo json_encode([
            "success" => false,
            "error" => "Missing transaction ID"
        ]);
        exit;
    }

    // Include DCBProtect class
    require '../lib/DCBProtect.php';
    
    $evina = new DCBProtect();
    
    // Calling CheckStatus function to get results
    $response = $evina->checkStatus($ti, $owner);
    
    $fraudCode = $response['fraud_code'];
    $message = $response['message'];
    
    echo json_encode([
        "fraud_code" => $fraudCode,
        "message" => $message
    ]);

?>