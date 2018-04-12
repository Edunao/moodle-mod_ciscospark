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
 * Class ciscospark
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ciscospark;

/**
 * Class ciscospark
 *
 * @package mod_ciscospark
 */
class ciscospark {
    var $id;
    var $course;
    var $name;
    var $intro;
    var $introformat;
    var $timemodified;
    var $usegroups;

    var $visible;

    var $rooms = null;

    /**
     * Ciscospark constructor
     *
     * @param \stdClass $dbciscospark
     */
    public function __construct($dbciscospark) {
        foreach ($dbciscospark as $key => $value) {
            $this->{$key} = $value;
        }

        if ($cm = get_coursemodule_from_instance('ciscospark', $this->id)) {
            $this->visible = $cm->visible;
        }
    }

    /**
     * Get module instance by id
     *
     * @param int $id
     * @return \mod_ciscospark\ciscospark|boolean
     */
    public static function get_by_id($id) {
        global $DB;
        if (!$dbciscospark = $DB->get_record('ciscospark', array('id' => $id))) {
            return false;
        }
        return new ciscospark($dbciscospark);
    }

    /**
     * Called by module add_instance function
     *
     * @param \stdClass $data
     * @return \mod_ciscospark\ciscospark
     */
    public static function create_ciscospark($data) {
        global $DB;

        $data->timemodified = time();

        $data->id = $DB->insert_record('ciscospark', $data);

        return new ciscospark($data);
    }

    /**
     * Get all rooms
     *
     * @return room[]
     */
    public function get_rooms() {
        global $DB;

        if (is_array($this->rooms)) {
            return $this->rooms;
        }

        $this->rooms = array();
        $dbrooms     = $DB->get_records('ciscospark_rooms', array('ciscosparkid' => $this->id));
        foreach ($dbrooms as $dbroom) {
            $this->rooms[$dbroom->groupid] = new room($dbroom);
        }
        return $this->rooms;
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

    /**
     * Get a specific room for a given group (0 if no group)
     *
     * @param int $groupid
     * @return boolean|\mod_ciscospark\room
     */
    public function get_room($groupid) {
        $rooms = $this->get_rooms();

        if (!isset($rooms[$groupid])) {
            return false;
        }
        return new room($rooms[$groupid]);
    }

    /**
     * Delete instance of ciscospark
     *
     * @return bool true if the deletion is completed
     */
    public function delete() {
        global $DB;
        //delete all rooms
        $rooms = $this->get_rooms();
        foreach ($rooms as $room) {
            $room->delete();
        }
        $DB->delete_records('ciscospark', array('id' => $this->id));
        return true;
    }

    /**
     * Get the cisco spark course team
     *
     * @return team
     */
    public function get_team() {
        return team::get_by_course($this->course);
    }

    /**
     * Remove students of all rooms
     *
     * @return boolean
     */
    public function remove_all_students() {
        $rooms = $this->get_rooms();
        foreach ($rooms as $room) {
            $room->remove_all_students();
        }
        return true;
    }
}

