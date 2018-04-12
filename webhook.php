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
 * File called by spark webhooks
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/ciscospark/lib.php';

$postData = file_get_contents("php://input");
$jsonPost = json_decode($postData, true);
$datas    = $jsonPost["data"];

$webhookid = $jsonPost['id'];

if (!$webhook = \mod_ciscospark\webhook::get_by_webhookid($webhookid)) {
    exit;
}

if ($webhook->name != $jsonPost['name']) {
    exit;
}

switch ($webhook->name) {
    // Remove a user who join another group room
    case 'joinroom' :        
        
        if (!$user = $DB->get_record('user', array('email' => $datas['personEmail']))) {
            exit;
        }
        
        if (!$room = \mod_ciscospark\room::get_by_roomid($datas['roomId'])) {
            exit;
        }
        
        $room->check_user($user->id, $datas['id']);
        
        break;
    default :
        break;
}


