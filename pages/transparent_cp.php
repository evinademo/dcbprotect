<?php
    //Server side request to Get script before DOMContentLoaded
    require '../lib/DCBProtect.php';
    
    $evina = new DCBProtect("transparent");
    $response = $evina->getScript();

    $uuid = $response['ti'];
    $js = $response['script'];
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Evina Transparent Demo</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <!-- Inject Evina JS BEFORE DOMContentLoaded -->
        <script> <?php echo $js; ?> </script>
        
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://demo.setllartech.com/evina/css/demopage.css">
    </head>
    <body>
        <div class="container">
            <h2>DCBprotect Transparent- Content Provider Page</h2>
            <h3>Transaction ID: <?php echo $uuid; ?></h3>
            <button id="cta_btn" onclick="redirect()">Continue to Partner Page</button>
        </div>
        <script>
            // Redirect end user from Content Provider -> Partner page for transaction check
            function redirect() {
                const ti= "<?php echo $uuid; ?>"; // transaction ID
                const owner= "muhammad_so"; // Owner username

                window.location.href = `https://demo.setllartech.com/evina/pages/transparent_pt.php?ti=${encodeURIComponent(ti)}&owner=${encodeURIComponent(owner)}`;
            }
        </script>
    </body>
</html>
