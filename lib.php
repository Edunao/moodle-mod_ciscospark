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
 * Ciscospark required functions
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once $CFG->dirroot . '/mod/ciscospark/classes/models/spark.php';
require_once $CFG->dirroot . '/mod/ciscospark/classes/models/team.php';
require_once $CFG->dirroot . '/mod/ciscospark/classes/models/room.php';
require_once $CFG->dirroot . '/mod/ciscospark/classes/models/ciscospark.php';
require_once $CFG->dirroot . '/mod/ciscospark/classes/controllers/spark_controller.php';

/**
 * Check if the plugin supports a feature
 *
 * @param string $feature
 * @return boolean
 */
function ciscospark_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

/**
 * Trigger view event on the activity
 *
 * @param stdClass $ciscospark
 * @param stdClass $course
 * @param stdClass $cm
 * @param context_course $context
 */
function ciscospark_view($ciscospark, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
            'context'  => $context,
            'objectid' => $ciscospark->id
    );

    $event = \mod_ciscospark\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('ciscospark', $ciscospark);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Add an instance of ciscospark
 *
 * @param stdClass $data
 * @param mod_ciscospark_mod_form $mform
 * @return boolean
 */
function ciscospark_add_instance($data, $mform) {

    if ($ciscospark = mod_ciscospark\ciscospark::create_ciscospark($data)) {
        return $ciscospark->id;
    }

    return false;
}

/**
 * Update an instance of ciscospark
 *
 * @param stdClass $data
 * @param mod_ciscospark_mod_form $mform
 * @return boolean
 */
function ciscospark_update_instance($data, $mform) {
    global $DB;
    $data->timemodified = time();
    $data->id           = $data->instance;

    $DB->update_record('ciscospark', $data);
    return true;
}

/**
 * Delete an instance of ciscospark and all associated rooms
 *
 * @param int $id
 * @return boolean
 */
function ciscospark_delete_instance($id) {
    if (!$ciscospark = mod_ciscospark\ciscospark::get_by_id($id)) {
        return false;
    }

    return $ciscospark->delete();
}

/* * *******SPECIFIC FUNCTIONS ********* */

/**
 * Get groups of a user
 *
 * @param int $courseid
 * @param int $userid - optional default 0 for current user
 * @return stdClass[] groups from groups table
 */
function ciscospark_get_user_groups($courseid, $userid = 0) {
    global $DB;
    if ($userid == 0) {
        global $USER;
        $userid = $USER->id;
    }

    $sql
            = "SELECT g.*
        FROM 
            {groups} g,
            {groups_members} gm
        WHERE
            gm.userid = :userid
            AND
            gm.groupid = g.id
            AND
            g.courseid = :courseid";
    $params = array('userid' => $userid, 'courseid' => $courseid);

    return $DB->get_records_sql($sql, $params);
}

/**
 * Get bot access token from settings
 *
 * @return string
 */
function ciscospark_get_bot_access_token() {
    return get_config('ciscospark', 'bot_access_token');
}

/**
 * Get bot email from settings
 *
 * @return string
 */
function ciscospark_get_bot_email() {
    return get_config('ciscospark', 'bot_email');
}

/**
 * Get group users who can view a course module
 * @param int $cmid
 * @param int $groupid
 * @param bool $includeteacher - optional default true
 * @return stdClass[] users
 */
function ciscospark_get_group_users($cmid, $groupid, $includeteacher = true) {

    $context      = context_module::instance($cmid);
    $course_users = get_enrolled_users($context, '', $groupid);

    $teachers = get_users_by_capability($context, 'mod/ciscospark:addinstance');

    if (!$includeteacher) {
        foreach ($teachers as $teacher) {
            unset($course_users[$teacher->id]);
        }
        $teachers = array();
    }

    $all_users = $course_users + $teachers;

    // get users who can view the activity
    foreach ($all_users as $user) {
        $is_visible = \core_availability\info_module::is_user_visible($cmid, $user->id);
        if (!$is_visible || !is_enrolled($context, $user->id)) {
            unset($all_users[$user->id]);
        }
    }

    return $all_users;
}

/**
 * Get all students for a given cmid
 *
 * @param int $cmid
 * @return array
 */
function ciscospark_get_students($cmid) {
    $context      = context_module::instance($cmid);

    // get all users
    $course_users = get_enrolled_users($context);

    // remove teachers
    $teachers = ciscospark_get_teachers($cmid);
    foreach ($teachers as $teacher) {
        unset($course_users[$teacher->id]);
    }

    return $course_users;
}

/**
 * Get cm teachers
 *
 * @param int $cmid
 * @return array
 */
function ciscospark_get_teachers($cmid) {
    $context = context_module::instance($cmid);
    return get_users_by_capability($context, 'mod/ciscospark:addinstance');
}

/**
 * Refresh all users tokens using refresh tokens- Called by the cron task.
 */
function ciscospark_refresh_all_tokens() {
    global $DB, $CFG;

    require_once $CFG->dirroot . '/mod/ciscospark/lib/oauth/client.php';

    mtrace('Processing mod_ciscospark CRON ...');

    $client_id     = get_config('ciscospark', 'client_id');
    $client_secret = get_config('ciscospark', 'client_secret');

    if (!ciscospark_plugin_is_configured($client_id, $client_secret)) {
        mtrace('Spark client not configured');
        return;
    }

    // get all existings users tokens
    $users_tokens = $DB->get_records('ciscospark_users_tokens');

    $errors = array();

    $client = new \OAuth2\Client($client_id, $client_secret);

    $fields = array(
            'grant_type'    => 'refresh_token',
            'client_id'     => $client_id,
            'client_secret' => $client_secret
    );

    // for each user token => refresh it using the refresh token
    foreach ($users_tokens as $user_token) {

        $fields['refresh_token'] = $user_token->refresh_token;

        $response = $client->getAccessToken('https://api.ciscospark.com/v1/access_token', 'refresh_token', $fields);

        if ($response['code'] < 300) {
            $result                               = $response['result'];
            $user_token->access_token             = $result['access_token'];
            $user_token->refresh_token            = $result['refresh_token'];
            $user_token->expires_in               = $result['expires_in'];
            $user_token->refresh_token_expires_in = $result['refresh_token_expires_in'];
            $user_token->last_update              = time();
            $DB->update_record('ciscospark_users_tokens', $user_token);
            mtrace('Update user token for userid = ' . $user_token->userid);
        } else {
            $errors[] = 'Cannot refresh user token for userid = ' . $user_token->userid;
            $DB->delete_records('ciscospark_users_tokens', array('id' => $user_token->id));
            mtrace('Cannot refresh user token for userid = ' . $user_token->userid);
        }
    }
    return $errors;
}

/**
 * Check if the plugin is well configured
 *
 * @param string $clientid - optional default '' to get the parameter value from the config
 * @param string $clientsecret - optional default '' to get the parameter value from the config
 * @return bool
 * @throws dml_exception
 */
function ciscospark_plugin_is_configured($clientid = '', $clientsecret = '') {
    if (empty($clientid)) {
        $clientid = get_config('ciscospark', 'client_id');
    }

    if (empty($clientsecret)) {
        $clientsecret = get_config('ciscospark', 'client_secret');
    }

    return (!empty($clientid) && !empty($clientsecret));
}