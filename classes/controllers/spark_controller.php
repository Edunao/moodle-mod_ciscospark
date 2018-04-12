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
 * Class spark_controller
 * Contains interactions between moodle and spark
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ciscospark;

/**
 * Class spark_controller
 *
 * @package mod_ciscospark
 */
class spark_controller {

    /**
     * Create a spark room
     *
     * @param \stdClass $room_infos
     * @param array $users
     * @return bool|\stdClass
     * @throws \moodle_exception
     */
    public static function create_spark_room($room_infos, $users = array()) {
        global $USER;

        $room = room::get_by_id($room_infos->dbroomid);

        //add cmid to redirect user
        $bot_token = ciscospark_get_bot_access_token();
        $spark_api = new spark($room_infos->cmid, $bot_token);

        //create spark room
        if ($room_infos->teamid == 0) {
            $created_room_infos = $spark_api->create_room($room_infos->title);
        } else {
            //create room team
            if (!$team = team::get_by_id($room_infos->teamid)) {
                print_error('Team does not exist');
            }
            $created_room_infos = $spark_api->create_team_room($team->teamid, $room_infos->title);
        }

        if (!is_object($created_room_infos)) {
            return false;
        }

        //add bot as moderator
        $bot_membership = $spark_api->add_bot_to_room($created_room_infos->id);
        $spark_api->set_moderator_permission($bot_membership->id, true);

        //enrol users
        foreach ($users as $user) {
            $membership_infos = $spark_api->add_user_to_room($user->email, $created_room_infos->id);
            if (!isset($membership_infos->errors)) {
                $room->add_member($user, $membership_infos->id);
            }
        }

        //remove teacher permission
        //$bot_token          = ciscospark_get_bot_access_token();
        $spark_api->set_token($bot_token);
        if ($teacher_membership = $spark_api->get_room_membership($created_room_infos->id, $USER->email)) {
            $spark_api->set_moderator_permission($teacher_membership->id, false);
            $room->add_member($USER, $teacher_membership->id);
        }

        return $created_room_infos;
    }

    /**
     * Synchronize rooms and users for a given course module
     *
     * @param \stdClass $cm
     */
    public static function syncRooms($cm) {
        global $CFG;
        require_once $CFG->libdir . '/grouplib.php';
        require_once $CFG->libdir . '/enrollib.php';

        //get activity instance
        $ciscospark = ciscospark::get_by_id($cm->instance);

        $course_infos = get_fast_modinfo($cm->course);
        $course_cm    = $course_infos->get_cm($cm->id);
        $availability = new \core_availability\info_module($course_cm);

        $user_availability = array();

        //get course team
        if (!$team = $ciscospark->get_team()) {
            $team = team::create_team($cm);
        }
        $teamid = $team->id;

        //add teachers to team
        $team->sync_teachers();

        //get activity rooms
        $existing_rooms = $ciscospark->get_rooms();

        //get course groups
        $course_groups = array();
        if ($ciscospark->usegroups == 0) {
            $emptygroup          = new \stdClass;
            $emptygroup->groupid = 0;
            $course_groups[]     = $emptygroup;
        } else {
            $course_groups = groups_get_all_groups($cm->course);
        }

        //delete rooms for deleted groups
        foreach ($existing_rooms as $existing_room) {
            if (!array_key_exists($existing_room->groupid, $course_groups)) {
                $existing_room->delete();
            }
        }

        //create rooms for new groups
        foreach ($course_groups as $groupid => $group) {
            //create room if needed
            if ($room = room::create_room($cm->instance, $groupid, $teamid)) {
                $existing_rooms[$groupid] = $room;
            }
            if (!isset($existing_rooms[$groupid])) {
                print_error('Inexisting room :' . $room->id);
            }
            $room = $existing_rooms[$groupid];

            $room_users   = $room->get_members();
            $course_users = ciscospark_get_group_users($cm->id, $groupid);

            if ($cm->visible == 0) {
                $room->remove_all_students();
            } else {
                //remove deleted users
                foreach ($room_users as $room_userid => $room_user) {
                    if (!isset($user_availability[$room_userid])) {
                        $user_availability[$room_userid] = $availability->is_user_visible($cm, $room_userid);
                    }

                    if (!array_key_exists($room_userid, $course_users) || !$user_availability[$room_userid]) {
                        $room->remove_member($room_userid);
                    }
                }

                //add new users
                if ($room->visible) {
                    foreach ($course_users as $userid => $course_user) {
                        if (!isset($user_availability[$userid])) {
                            $user_availability[$userid] = $availability->is_user_visible($cm, $userid);
                        }
                        if (!array_key_exists($userid, $room_users) && $user_availability[$userid]) {
                            $room->add_member($course_user);
                        }
                    }
                }
            }
        }
    }

}
