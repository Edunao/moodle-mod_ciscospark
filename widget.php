<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Widget html
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';

$token = required_param('token', PARAM_RAW); // user token
$room  = required_param('room', PARAM_RAW); // room id

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