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
 * Ciscospark renderer
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class mod_ciscospark_renderer extends plugin_renderer_base {

    /**
     * Display room link
     *
     * @param stdClass|int $group or 0 to not display the group
     * @param room $room
     * @param array $capabilities
     * @return string
     */
    public function display_room_link($group, $room, $capabilities = array()) {
        global $CFG, $DB, $USER;
        if (!$token = $DB->get_record('ciscospark_users_tokens', array('userid' => $USER->id))) {
            print_error('User token not found');
        }
        $class  = !$room->visible ? 'hidden-room' : '';
        $output = '';
        $output .= '<div class="room ' . $class . '">';
        if ($group) {
            $output .= '<h4>' . $group->name . '</h4>';
        } else {
            $group     = new stdClass();
            $group->id = 0;
        }

        //add iframe
        $iframe_url = $CFG->wwwroot . '/mod/ciscospark/widget.php?token=' . $token->access_token . '&room=' . $room->roomid;
        $output     .= '<iframe src="' . $iframe_url . '"></iframe>';

        $url    = $CFG->wwwroot . '/mod/ciscospark/redirect.php?roomid=' . $room->id . '&groupid=' . $group->id;
        $output .= '<div class="room-buttons"><a class="enter-room-link" href="' . $url . '" target="_blank">' .
                   get_string('enterroom', 'ciscospark') . '</a>';

        //buttons 
        if ($group->id != 0 && isset($capabilities['hiderooms']) && $capabilities['hiderooms']) {
            $output .= '<div class="room-actions">';
            $output .= '<a href="" class="hide-button" data-room="' . $room->id . '" title="' .
                       get_string('showhide', 'ciscospark') . '"></a>';
            $output .= '</div>';
        }
        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Display a list a group rooms for a given ciscospark instance
     *
     * @param ciscospark $ciscospark
     * @param stdClass $groups
     * @param array $capabilities
     * @return string
     */
    public function display_groups_rooms($ciscospark, $groups, $capabilities = array()) {
        $output    = '';
        $displayed = 0;
        if (!empty($groups)) {
            foreach ($groups as $group) {
                if (!empty($group)) {
                    $room = $ciscospark->get_room($group->id);
                    if ((isset($capabilities['viewhiddenrooms']) && $capabilities['viewhiddenrooms']) || ($room->visible)) {
                        $output .= $this->display_room_link($group, $room, $capabilities);
                        $displayed++;
                    }
                }
            }
        }

        if ($displayed == 0) {
            $output .= get_string('noavailablerooms', 'ciscospark');
        }
        return $output;
    }

}