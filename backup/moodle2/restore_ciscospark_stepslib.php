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
 * Class restore_ciscospark_activity_structure_step
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class restore_ciscospark_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths   = array();
        $paths[] = new restore_path_element('ciscospark', '/activity/ciscospark');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_ciscospark($data) {
        global $DB;

        $data               = (object) $data;
        $oldid              = $data->id;
        $data->course       = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('ciscospark', $data);
        $this->apply_activity_instance($newitemid);

        $cmid = $this->task->get_moduleid();
        $cm = get_coursemodule_from_id('ciscospark', $cmid);
        mod_ciscospark\spark_controller::syncRooms($cm);
    }

}
