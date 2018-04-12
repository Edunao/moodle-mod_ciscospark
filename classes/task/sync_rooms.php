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
 * sync rooms task
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ciscospark\task;

class sync_rooms extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sync_rooms', 'mod_ciscospark');
    }

    /**
     * Run ciscospark cron.
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/ciscospark/lib.php');
        
        //sync all rooms
        $instances = $DB->get_records_sql('SELECT cm.* FROM {course_modules} cm, {modules} m WHERE cm.module = m.id AND m.name = ?', array('ciscospark'));
        
        foreach ($instances as $instance) {
            $cm = get_coursemodule_from_id('ciscospark', $instance->id);
            \mod_ciscospark\spark_controller::syncRooms($cm);
        }
    }

}
