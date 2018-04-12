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
 * Class team
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ciscospark;

class team {

    var $id;
    var $courseid;
    var $teamid; //spark team id
    var $timecreated;

    /**
     * Team constructor
     * @param stdClass $dbteam
     */
    public function __construct($dbteam) {
        foreach ($dbteam as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Get team rooms
     * @global \mod_ciscospark\type $DB
     * @return \mod_ciscospark\room
     */
    public function get_rooms() {
        global $DB;
        $dbrooms = $DB->get_records('ciscospark_rooms', array('teamid' => $this->id));
        $rooms   = array();
        foreach ($dbrooms as $dbroom) {
            $rooms[$dbroom->id] = new room($dbroom);
        }
        return $rooms;
    }

    /**
     * Get a team by id
     * @global \mod_ciscospark\type $DB
     * @param int $id
     * @return boolean|\mod_ciscospark\team
     */
    public static function get_by_id($id) {
        global $DB;
        if (!$team = $DB->get_record('ciscospark_teams', array('id' => $id))) {
            return false;
        }
        return new team($team);
    }

    /**
     * Get a course team
     * @global \mod_ciscospark\type $DB
     * @param int $courseid
     * @return boolean|\mod_ciscospark\team
     */
    public static function get_by_course($courseid) {
        global $DB;
        if (!$team = $DB->get_record('ciscospark_teams', array('courseid' => $courseid))) {
            return false;
        }
        return new team($team);
    }

    /**
     * 
     * @global type $DB
     * @param type $cm
     * @return boolean|\mod_ciscospark\team
     */
    public static function create_team($cm) {
        global $DB;

        if ($team = self::get_by_course($cm->course)) {
            return false;
        }
        $team               = new \stdClass;
        $team->courseid     = $cm->course;
        $team->timemodified = time();

        //create spark team
        $course       = get_course($cm->course);
        $spark_api    = new spark($cm->id);
        $created_team = $spark_api->create_team($course->fullname);
        
        //add bot to team
        $bot_email = ciscospark_get_bot_email();
        $bot_membership = $spark_api->add_user_to_team($bot_email, $created_team->id);
        $spark_api->set_team_moderator_permission($bot_membership->id, true);

        $team->teamid = $created_team->id;
        $team->id     = $DB->insert_record('ciscospark_teams', $team);

        return new team($team);
    }

    /**
     * Get team members
     * @global \mod_ciscospark\type $DB
     * @return stdClass[] users
     */
    public function get_members() {
        global $DB;
        return $DB->get_records_sql('
            SELECT u.*, tm.membershipid
            FROM 
                {user} u,
                {ciscospark_teams_members} tm
            WHERE
                teamid = ?
                AND
                tm.userid = u.id
        ', array($this->id));
    }

    /**
     * Add user to team
     * @global \mod_ciscospark\type $DB
     * @param stdClass $user
     * @return boolean
     */
    public function add_member($user) {
        global $DB;
        if ($member = $DB->get_record('ciscospark_teams_members', array('userid' => $user->id, 'teamid' => $this->id))) {
            return false;
        }
        $member = new \stdClass();
        $member->teamid = $this->id;
        $member->userid = $user->id;

        $bot_token = ciscospark_get_bot_access_token();
        
        $spark_api  = new spark(null, $bot_token);
        
        if (!$membership = $spark_api->add_user_to_team($user->email, $this->teamid)) {
            print_error('Cannot add user ' . $user->email . ' to team : ' . $this->teamid);
        }
        //user already in the spark team
        if (!isset($membership->id)) {
            $membership = $spark_api->get_team_membership($this->teamid, $user->email);
        }
        if (!isset($membership->id)) {
            print_error('Cannot add user ' . $user->email . ' to team : ' . $this->teamid);
        }

        $member->membershipid = $membership->id;
        $member->timemodified = time();

        return $DB->insert_record('ciscospark_teams_members', $member);
    }
    
    /**
     * Sync teachers to team
     */
    public function sync_teachers() {
        $context  = \context_course::instance($this->courseid);
        $teachers = get_users_by_capability($context, 'mod/ciscospark:addinstance');
        
        $members = $this->get_members();
        
        foreach ($teachers as $teacher) {
            if (is_enrolled($context, $teacher)) {
                $this->add_member($teacher);
            }
        }
        
        foreach ($members as $member) {
            if (!is_enrolled($context, $member) || !array_key_exists($member->id, $teachers)) {
                $this->remove_member($member);
            }
        }
    }

    /**
     * Remove user from the team
     * @global \mod_ciscospark\type $DB
     * @param stdClass $user
     * @return boolean
     */
    public function remove_member($user) {
        global $DB;
        if (!$member = $DB->get_record('ciscospark_teams_members', array('userid' => $user->id, 'teamid' => $this->id))) {
            return false;
        }
        $bot_token = ciscospark_get_bot_access_token();
        $spark_api = new spark(null, $bot_token);
        $spark_api->remove_user_from_team($member->membershipid);

        $DB->delete_records('ciscospark_teams_members', array('id' => $member->id));
        return true;
    }
    
    /**
     * Update team name
     * @param string $name
     * @return string errors
     */
    public function update_name($name = null) {
        if (empty($name)) {
            global $DB;
            $course = $DB->get_record('course', array('id' => $this->courseid));
            $name   = $course->fullname;
        }
        
        $bot_token = ciscospark_get_bot_access_token();
        if (empty($bot_token)) {
            print_error('Bot token not found');
        }
        
        $spark_api = new spark(null, $bot_token);
        return $spark_api->update_team($this->teamid, $name);
    }
    
    /**
     * Update team rooms names
     */
    public function update_rooms_names() {
        $rooms = $this->get_rooms();
        
        foreach ($rooms as $room) {
            $room->update_title();
        }
    }
}
