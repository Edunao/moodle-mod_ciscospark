<?php
require_once '../../config.php';

$token = required_param('token', PARAM_RAW);
$room  = required_param('room', PARAM_RAW);

require_login();

?>

<html>
    <head>
        <meta charset="utf8">
        <title>Space widget</title>
        <script src="https://code.s4d.io/widget-space/production/bundle.js"></script>
        <link rel="stylesheet" href="https://code.s4d.io/widget-space/production/main.css">
    </head>
    <body>
        <div id="ciscospark-widget" style="width: 100%; height: 404px;"/>
        <script>
            var widgetEl = document.getElementById('ciscospark-widget');
            // Init a new widget
            ciscospark.widget(widgetEl).spaceWidget({
                accessToken: '<?php echo $token ?>',
                spaceId: '<?php echo $room ?>',
            });
        </script>
    </body>
</html>