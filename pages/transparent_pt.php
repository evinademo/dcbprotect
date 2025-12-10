<?php
    // Get JSON POST data
    $ti     = $_GET['ti']     ?? "";
    $owner  = $_GET['owner']  ?? "";
    
    // Validate input  
    if ($ti === "") {
        die("Invalid request: TI not found");
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Evina Demo</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <!-- Inject Evina JS BEFORE DOMContentLoaded -->
        <script> <?php echo $js; ?> </script>
        
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://demo.setllartech.com/evina/css/demopage.css">
    </head>
    <body>
        
        <div class="container">
            <h2>DCBprotect Transparent- Partner Page</h2>
            <p>Tip: Try with a VPN to simulate fraud scenario</p>
            <h3>Owner Username: <?php echo $owner; ?></h3>
            <h3>Transaction ID: <?php echo $ti; ?></h3>
            <button id="cta_btn" onclick="checkStatus()">Click to Continue</button>
            <div id="alertBox"></div>
        </div>
        <script>
            
        // Partner checking the transaction status
        function checkStatus() {
            const ti= "<?php echo $ti; ?>";
            const owner= "<?php echo $owner; ?>";

            fetch("https://demo.setllartech.com/evina/app/check_transaction.php", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({ ti, owner })
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
