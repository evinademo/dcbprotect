<!DOCTYPE html>
<html>
    <head>
        <title>Evina Manual Script Demo</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://demo.setllartech.com/evina/css/demopage.css">
    </head>
    <body>
        <div class="container">
            <h2>DCBprotect - Manual Script integration (after DOMContentLoaded)</h2>
            <p>Tip: Try with a VPN to simulate fraud scenario</p>
            <h3 id="ti">Transaction ID: </h3>
            <button id="cta_btn" onclick="checkStatus()">Click to Continue</button>
            <div id="alertBox"></div>
        </div>
 
        <script>
            let ti = null;
            document.addEventListener("DOMContentLoaded", () => {
                getScript();
            });
            
            // Function to get script manually after DOMContentLoaded event
            function getScript() {
                const payload = {
                    type: "hosted"
                };
            
                fetch("https://demo.setllartech.com/evina/app/get_script.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    
                    ti = data.ti;
                    var dcbprotect = data.js;
                   
                    // Add the script to the page
                    var script = document.createElement("script");
                    script.type = "text/javascript";
                    script.text = dcbprotect; // string var that contains the script
                    document.body.appendChild(script);
                    // Launch it!
                    var ev = new Event("DCBProtectRun");
                    document.dispatchEvent(ev);
                    
                    // Adding transaction ID on front page
                    const heading = document.getElementById("ti");
                    // Append ti
                    heading.innerText += ti;
                    const alertBox = document.getElementById("alertBox");
                    alertBox.innerHTML = ""; // clear previous alert
            
                    const div = document.createElement("div");
                    div.classList.add("alert");
                    div.classList.add("success");
                    div.textContent = "Script loaded successfully ";
                    alertBox.appendChild(div);
                    
                    // Remove the alert after 3 seconds
                    setTimeout(() => {
                        div.remove();
                    }, 5000);
                })
                .catch(error => {
                    console.error("Error:", error);
                });
            }
        
            // Function to check the transaction status on button click
            function checkStatus() {
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