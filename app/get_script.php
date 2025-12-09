<?php

    $data = json_decode(file_get_contents("php://input"), true);
    $type = $data["type"];
    
    // Validate the input
    if (!isset($type) || empty($type)) {
        echo json_encode([
            "success" => false,
            "error" => "Missing integration type"
        ]);
        exit;
    }
    
    require '../lib/DCBProtect.php';
    $evina = new DCBProtect("hosted");
    
    $response = $evina->getScript();

    $ti = $response['ti'];
    $js = $response['script'];

    echo json_encode([
        "ti" => $ti,
        "js" => $js
    ]);

?>