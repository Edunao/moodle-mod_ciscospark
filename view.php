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
 * Plugin main page
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once '../../config.php';
require_once $CFG->dirroot . '/mod/ciscospark/lib.php';

//check parameters
$cmid = required_param('id', PARAM_INT);

if (!$cm = get_coursemodule_from_id('ciscospark', $cmid)) {
    print_error('Course module not found');
}

if (!$course = get_course($cm->course)) {
    print_error('Course not found');
}

if (!$dbciscospark = $DB->get_record('ciscospark', array('id' => $cm->instance))) {
    print_error('Ciscospark not found: ' . $cm->instance);
}

if (!$ciscospark = mod_ciscospark\ciscospark::get_by_id($cm->instance)) {
    print_error('Ciscospark not found: ' . $cm->instance);
}

require_course_login($course, true, $cm);

$url           = new moodle_url('/mod/ciscospark/view.php', array('id' => $cmid));
$context       = context_module::instance($cmid);
$coursecontext = context_course::instance($course->id);

require_capability('mod/ciscospark:view', $context);

//Check if the bot is well configured
if (!ciscospark_get_bot_email() || !ciscospark_get_bot_access_token()) {
    print_error('You must configure a bot to create a room');
}

//check token
$spark = new \mod_ciscospark\spark($cmid);

mod_ciscospark\spark_controller::syncRooms($cm);

//GET CAPABILITIES
$capabilities = array(
    'accessallgroups' => has_capability('moodle/site:accessallgroups', $coursecontext),
    'viewhiddenrooms' => has_capability('mod/ciscospark:viewhiddenrooms', $context),
    'hiderooms'       => has_capability('mod/ciscospark:hiderooms', $context),
);

// Completion and trigger events.
ciscospark_view($dbciscospark, $course, $cm, $context);

//PAGE settings
$PAGE->set_course($course);
$PAGE->set_url($url);
$PAGE->set_title($ciscospark->name);
$PAGE->set_context($context);
$PAGE->requires->js_call_amd('mod_ciscospark/ciscospark', 'init');

$renderer = $PAGE->get_renderer('mod_ciscospark');

/************************** PAGE DISPLAY ***************************/

echo $OUTPUT->header();

echo $OUTPUT->heading($ciscospark->name);

//display module intro
if (!empty($ciscospark->intro)) {
    echo $OUTPUT->box(format_module_intro('ciscospark', $ciscospark, $cm->id), 'generalbox', 'intro');
}

echo '<div id="ciscospark-rooms">';

//display button(s)
switch ($ciscospark->usegroups) {

    //no groups
    case 0 :
	$room = $ciscospark->get_room(0);
        echo $renderer->display_room_link(0, $room, $capabilities);
        break;

    //separated groups
    case 1 :
        $groups = $capabilities['accessallgroups'] ? groups_get_all_groups($course->id) : ciscospark_get_user_groups($course->id);
        echo $renderer->display_groups_rooms($ciscospark, $groups, $capabilities);
        break;
}

echo '</div>';

echo $OUTPUT->footer();

