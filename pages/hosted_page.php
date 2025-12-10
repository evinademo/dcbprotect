<?php

    //Server side request to Get script before DOMContentLoaded
    require '../lib/DCBProtect.php';
    $evina = new DCBProtect("hosted");
    
    $response = $evina->getScript();

    $uuid = $response['ti'];
    $js = $response['script'];
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Evina Hosted Page Demo</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inject Evina JS BEFORE DOMContentLoaded -->
    <script> <?php echo $js; ?> </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://demo.setllartech.com/evina/css/demopage.css">
</head>
<body>

    <div class="container">
        <h2>DCBprotect - Hosted Page Integration</h2>
        <p>Tip: Try with a VPN to simulate fraud scenario</p>
        <h3>Transaction ID: <?php echo $uuid; ?></h3>
        <button id="cta_btn" onclick="checkStatus()">Click to Continue</button>
        <div id="alertBox"></div>
    </div>
    
    <script>
    function checkStatus() {
        const ti = "<?php echo $uuid; ?>"; // transaction ID
        
        // Calling backend API to check the transaction status
        fetch("https://demo.setllartech.com/evina/app/check_transaction.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({ ti })
        })
        .then(res => res.json())
        .then(data => {
            const alertBox = document.getElementById("alertBox");
            alertBox.innerHTML = ""; // clear previous alert
    
            const div = document.createElement("div");
            div.classList.add("alert");
    
            if(data.fraud_code == "1000") {
                div.classList.add("success");
                div.textContent = "Secure User - " + data.message;
            } else {
                div.classList.add("error");
                div.textContent = "Fraud Code: "+ data.fraud_code + " - " + data.message;
            }
    
            alertBox.appendChild(div);
        })
        .catch(err => console.error(err));
    }
    </script>
</body>
</html>
