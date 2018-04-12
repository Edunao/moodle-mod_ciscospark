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
 * Class room
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ciscospark;

class room {

    // ciscospark_rooms table id
    var $id;
    //associated group - default 0
    var $groupid;
    //course module instance id
    var $ciscosparkid;
    //spark room identifier
    var $roomid;
    var $visible;
    var $timemodified;
    // id from table ciscospark_teams or 0
    var $teamid;

    /**
     * Class constructor
     *
     * @param \stdClass $dbroom from ciscospark_rooms table
     */
    public function __construct($dbroom) {
        foreach ($dbroom as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Update room visibility
     */
    public function change_visibility() {
        global $DB;
        $this->visible = $this->visible == 1 ? 0 : 1;
        $DB->update_record('ciscospark_rooms', $this);

        if ($this->visible == 0) {
            $this->remove_all_students();
        } else {
            $this->add_all_students();
        }
    }

    /**
     * remove all students from the room
     *
     * @return boolean
     */
    public function remove_all_students() {
        if (!$cm = get_coursemodule_from_instance('ciscospark', $this->ciscosparkid)) {
            print_error('CM not found');
        }
        $members = ciscospark_get_group_users($cm->id, $this->groupid, false);
        foreach ($members as $member) {
            $this->remove_member($member->id);
        }
        return true;
    }

    /**
     * Add all students to the room
     *
     * @return boolean
     */
    public function add_all_students() {
        if (!$cm = get_coursemodule_from_instance('ciscospark', $this->ciscosparkid)) {
            print_error('CM not found');
        }
        $members = ciscospark_get_group_users($cm->id, $this->groupid, false);
        foreach ($members as $member) {
            $this->add_member($member);
        }
        return true;
    }

    /**
     * Get a room by id
     *
     * @param int $id
     * @return boolean|\mod_ciscospark\room
     */
    public static function get_by_id($id) {
        global $DB;
        if (!$dbroom = $DB->get_record('ciscospark_rooms', array('id' => $id))) {
            return false;
        }
        return new room($dbroom);
    }

    /**
     * Get a room by spark room id
     *
     * @param string $roomid
     * @return boolean|\mod_ciscospark\room
     */
    public static function get_by_roomid($roomid) {
        global $DB;
        if (!$dbroom = $DB->get_record('ciscospark_rooms', array('roomid' => $roomid))) {
            return false;
        }
        return new room($dbroom);
    }

    /**
     * Create a room
     *
     * @param int $ciscosparkid
     * @param int $groupid
     * @param int $teamid - optional default 0 for no team
     * @return bool|room
     */
    public static function create_room($ciscosparkid, $groupid, $teamid = 0) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        if ($DB->record_exists('ciscospark_rooms', array('ciscosparkid' => $ciscosparkid, 'groupid' => $groupid))) {
            return false;
        }

        if (!$cm = get_coursemodule_from_instance('ciscospark', $ciscosparkid)) {
            print_error('CM not found');
        }

        $room_infos               = new \stdClass;
        $room_infos->ciscosparkid = $ciscosparkid;
        $room_infos->groupid      = $groupid;
        $room_infos->teamid       = $teamid;

        $room_infos->title = '';

        $course            = get_course($cm->course);
        $room_infos->title .= $course->shortname . '_';

        $ciscospark        = $DB->get_record('ciscospark', array('id' => $ciscosparkid));
        $room_infos->title .= $ciscospark->name;

        if ($groupid != 0) {
            $group             = $DB->get_record('groups', array('id' => $groupid));
            $room_infos->title .= '_' . $group->name;
        }

        $room_infos->cmid = $cm->id;

        // create the new room in database
        $dbroom               = new \stdClass();
        $dbroom->ciscosparkid = $ciscosparkid;
        $dbroom->visible      = 1; //room visible by default
        $dbroom->groupid      = $groupid;
        $dbroom->timemodified = time();
        $dbroom->teamid       = $teamid;
        $dbroom->id = $DB->insert_record('ciscospark_rooms', $dbroom);

        $room_infos->dbroomid = $dbroom->id;

        $users = ciscospark_get_group_users($cm->id, $groupid);

        //create room in spark and add users
        if (!$room = spark_controller::create_spark_room($room_infos, $users)) {
            print_error('Cannot create spark room');
        }

        if (empty($room->id)) {
            $DB->delete_records('ciscospark_rooms', array('id' => $dbroom->id));
            print_object('Cannot create spark room, room id is empty');
        }

        $dbroom->roomid = $room->id;

        $DB->update_record('ciscospark_rooms', $dbroom);

        $newroom = new room($dbroom);

        return $newroom;
    }

    /**
     * Check that membership ids stored in database correspond to the spark membership ids
     */
    public function check_memberships_ids() {
        global $DB;
        $bot_token = ciscospark_get_bot_access_token();
        if (empty($bot_token)) {
            print_error('Bot token not found');
        }
        //delete room as the configureds bot
        $cm        = get_coursemodule_from_instance('ciscospark', $this->ciscosparkid);
        $spark_api = new spark($cm->id, $bot_token);

        $spark_memberships = $spark_api->get_room_memberships($this->roomid);

        foreach ($spark_memberships as $spark_membership) {
            if (!$room_membership = $DB->get_record('ciscospark_rooms_members', array('membershipid' => $spark_membership->id))) {
                if ($user = $DB->get_record('user', array('email' => $spark_membership->personEmail))) {
                    if ($room_membership = $DB->get_record('ciscospark_rooms_members',
                            array('roomid' => $this->id, 'userid' => $user->id))) {
                        $room_membership->membershipid = $spark_membership->id;
                        $DB->update_record('ciscospark_rooms_members', $room_membership);
                    } else {
                        $room_membership               = new \stdClass;
                        $room_membership->roomid       = $this->id;
                        $room_membership->userid       = $user->id;
                        $room_membership->membershipid = $spark_membership->id;
                        $room_membership->timemodified = time();
                        $DB->insert_record('ciscospark_rooms_members', $room_membership);
                    }
                }
            }
        }
    }

    /**
     * Delete the room
     *
     * @return boolean
     */
    public function delete() {
        global $DB;

        // delete room members
        $DB->delete_records('ciscospark_rooms_members', array('roomid' => $this->id));

        // delete the room
        $DB->delete_records('ciscospark_rooms', array('id' => $this->id));

        // delete the spark room
        $bot_token = ciscospark_get_bot_access_token();
        if (empty($bot_token)) {
            print_error('Bot token not found');
        }
        //delete room as the configureds bot
        $cm        = get_coursemodule_from_instance('ciscospark', $this->ciscosparkid);
        $spark_api = new spark($cm->id, $bot_token);

        $spark_api->delete_room($this->roomid);

        return true;
    }

    /**
     * Add a member to the room
     *
     * @param \stdClass $user
     * @param string $membershipid
     * @return mixed
     */
    public function add_member($user, $membershipid = null) {
        global $DB;

        //if user does not exists in the spark room
        if (!$membershipid) {
            $cm        = get_coursemodule_from_instance('ciscospark', $this->ciscosparkid);
            $bot_token = ciscospark_get_bot_access_token();
            if (empty($bot_token)) {
                print_error('Bot token not found');
            }
            $spark_api = new spark($cm->id, $bot_token);

            $membership_infos = $spark_api->add_user_to_room($user->email, $this->roomid);
            if (isset($membership_infos->errors)) {
                return false;
            }
            $membershipid = $membership_infos->id;
        }

        //add membership id to sparkroom member
        if ($room_membership = $DB->get_record('ciscospark_rooms_members', array('userid' => $user->id, 'roomid' => $this->id))) {
            $room_membership->membershipid = $membershipid;
            $DB->update_record('ciscospark_rooms_members', $room_membership);
            return;
        }
        //create sparkroom member
        $room_membership               = new \stdClass();
        $room_membership->groupid      = $this->groupid;
        $room_membership->roomid       = $this->id;
        $room_membership->userid       = $user->id;
        $room_membership->membershipid = $membershipid;
        $room_membership->timemodified = time();
        return $DB->insert_record('ciscospark_rooms_members', $room_membership);
    }

    /**
     * Get room members
     *
     * @return array users
     */
    public function get_members() {
        global $DB;
        return $DB->get_records_sql('
            SELECT
                u.id, u.firstname, u.lastname, rm. membershipid
            FROM
                {user} u,
                {ciscospark_rooms_members} rm
            WHERE
                roomid = ?
                AND
                u.id = rm.userid
        ', array($this->id));
    }

    /**
     * Remove a room member
     *
     * @param int $userid
     * @param string $message - optional default null
     * @return boolean
     */
    public function remove_member($userid, $message = null) {
        global $DB;

        //room member exists ?
        if (!$room_member = $DB->get_record('ciscospark_rooms_members', array('roomid' => $this->id, 'userid' => $userid))) {
            return false;
        }

        //user exists in moodle ?
        if (!$user = $DB->get_record('user', array('id' => $userid))) {
            return false;
        }

        //delete room member in database
        $DB->delete_records('ciscospark_rooms_members', array('roomid' => $this->id, 'userid' => $userid));

        if (!$cm = get_coursemodule_from_instance('ciscospark', $this->ciscosparkid)) {
            return false;
        }

        return $this->remove_spark_membership($room_member->membershipid, $userid, $message);
    }

    /**
     * Remove a user from the room
     *
     * @param string $membershipid
     * @param int $userid
     * @param string $message - optional default null
     * @return boolean
     */
    public function remove_spark_membership($membershipid, $userid, $message = null) {
        global $DB;
        $cm        = get_coursemodule_from_instance('ciscospark', $this->ciscosparkid);
        $bot_token = ciscospark_get_bot_access_token();
        $spark_api = new spark($cm->id, $bot_token);

        $spark_api->remove_user_from_room($membershipid);

        // send a message to the removed user
        if ($message) {
            $user = $DB->get_record('user', array('id' => $userid));
            $spark_api->send_message($user->email, $message);
        }
        return true;
    }

    /**
     * Check if user can join the room, remove the membership if necessary
     *
     * @param int $userid
     * @param string $membershipid
     */
    public function check_user($userid, $membershipid) {
        $message = get_string('remove_user_message', 'ciscospark');

        $members = $this->get_members();

        if (!array_key_exists($userid, $members)) {
            //kick user from the room
            if (!$this->remove_member($userid, $message)) {
                $this->remove_spark_membership($membershipid, $userid, $message);
            }
        }
    }

    /**
     * Update room title
     *
     * @param string $title
     * @return string errors
     */
    public function update_title($title = null) {
        if (empty($title)) {
            global $DB;

            if (!$cm = get_coursemodule_from_instance('ciscospark', $this->ciscosparkid)) {
                print_error('CM not found');
            }

            $course = get_course($cm->course);
            $title  = $course->shortname . '_';

            $ciscospark = $DB->get_record('ciscospark', array('id' => $this->ciscosparkid));
            $title      .= $ciscospark->name;

            if ($this->groupid != 0) {
                $group = $DB->get_record('groups', array('id' => $this->groupid));
                $title .= '_' . $group->name;
            }
        }

        $bot_token = ciscospark_get_bot_access_token();
        if (empty($bot_token)) {
            print_error('Bot token not found');
        }

        $spark_api = new spark(null, $bot_token);
        $errors    = $spark_api->update_room($this->roomid, $title);

        return $errors;
    }

}
