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
 * Class mod_ciscospark_observer
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_ciscospark_observer {

    /**
     * Sync rooms on course module update
     * @param \core\event\course_module_updated $event
     */
    public static function course_module_updated(\core\event\course_module_updated $event) {
        global $CFG;
        
        require_once $CFG->dirroot . '/mod/ciscospark/lib.php';
        
        $cm = get_coursemodule_from_id('', $event->objectid);
        
        if ($cm->modname == 'ciscospark') {            
            if (!$ciscospark = mod_ciscospark\ciscospark::get_by_id($cm->instance)) {
                return;
            }
            mod_ciscospark\spark_controller::syncRooms($cm);
            
            $ciscospark->update_rooms_names();
        }
    }
    
    /**
     * Sync rooms when course name is updated
     * @global type $CFG
     * @param \core\event\course_updated $event
     */
    public static function course_updated(\core\event\course_updated $event) {
        global $CFG;
        
        require_once $CFG->dirroot . '/mod/ciscospark/lib.php';
        
        $course = get_course($event->objectid);
        
        if ($team = mod_ciscospark\team::get_by_course($course->id)) {
            $team->update_name();
            $team->update_rooms_names();
        }
    }
    
     /**
     * Sync rooms when group name is updated
     * @global type $CFG
     * @param \core\event\group_updated $event
     */
    public static function group_updated(\core\event\group_updated $event) {
        global $CFG;
        
        require_once $CFG->dirroot . '/mod/ciscospark/lib.php';
        require_once $CFG->libdir . '/grouplib.php';
        
        $group = groups_get_group($event->objectid);
        
        if ($team = mod_ciscospark\team::get_by_course($group->courseid)) {
            $team->update_rooms_names();
        }
    }
    
    
}
