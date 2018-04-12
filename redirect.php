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
 * Redirect to the good spark room
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once '../../config.php';
require_once $CFG->dirroot . '/mod/ciscospark/lib.php';
require_once $CFG->libdir . '/grouplib.php';

$roomid  = required_param('roomid', PARAM_INT);
$groupid = required_param('groupid', PARAM_INT);

// check data integrity

if (!$room = \mod_ciscospark\room::get_by_id($roomid)) {
    print_error('room not found');
}

if ($groupid != $room->groupid) {
    print_error('Invalid groupid');
}

if (!$ciscospark = $DB->get_record('ciscospark', array('id' => $room->ciscosparkid))) {
    print_error('Ciscospark not found');
}

if (!$cm = get_coursemodule_from_instance('ciscospark', $ciscospark->id)) {
    print_error('CM not found');
}

require_course_login($cm->course);

$context = context_module::instance($cm->id);

$canviewhiddenrooms = has_capability('mod/ciscospark:viewhiddenrooms', $context);

if (!$canviewhiddenrooms && !$room->visible) {
    print_error('Room not visible');
}

if (
        ($ciscospark->usegroups == 1) &&
        !$canviewhiddenrooms &&
        !groups_is_member($groupid)
) {
    print_error('Invalid group');
}
//decode spark url
$url = base64_decode($room->roomid);
$url = str_replace('ciscospark://us/ROOM/', 'https://web.ciscospark.com/rooms/', $url);

// go to spark
redirect($url);
