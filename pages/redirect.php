<?php

    require '../lib/DCBProtect.php';
    $evina = new DCBProtect("redirect");
    
    $response = $evina->getScript();

    $uuid = $response['ti'];
    $js = $response['script'];
?>

<!DOCTYPE html>
<html>
    <head>
       <!-- Inject Evina JS before DOMContentLoaded -->
        <script>
            <?php echo $js; ?>
        </script>
    
    </head>
    <body>
    </body>
</html>