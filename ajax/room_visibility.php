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
 * Change a room visibility
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once $CFG->dirroot . '/mod/ciscospark/lib.php';

$roomid = required_param('room', PARAM_INT);

if (!$room = \mod_ciscospark\room::get_by_id($roomid)) {
    print_error('Room not found');
}

if (!$cm = get_coursemodule_from_instance('ciscospark', $room->ciscosparkid)) {
    print_error('CM not found');
}

$context = context_module::instance($cm->id);

require_capability('mod/ciscospark:hiderooms', $context);

//update room visibility
$room->change_visibility();

echo 1;

