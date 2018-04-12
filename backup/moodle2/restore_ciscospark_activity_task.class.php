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
 * Class restore_ciscospark_activity_task
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/ciscospark/backup/moodle2/restore_ciscospark_stepslib.php'); // Because it exists (must)

class restore_ciscospark_activity_task extends restore_activity_task {

    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        $this->add_step(new restore_ciscospark_activity_structure_step('ciscospark_structure', 'ciscospark.xml'));
    }

    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('ciscospark', array('intro'), 'ciscospark');

        return $contents;
    }

    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('CISCOSPARKVIEWBYID', '/mod/ciscospark/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('CISCOSPARKINDEX', '/mod/ciscospark/index.php?id=$1', 'course');

        return $rules;
    }

    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('ciscospark', 'add', 'view.php?id={course_module}', '{ciscospark}');
        $rules[] = new restore_log_rule('ciscospark', 'update', 'view.php?id={course_module}', '{ciscospark}');
        $rules[] = new restore_log_rule('ciscospark', 'view', 'view.php?id={course_module}', '{ciscospark}');

        return $rules;
    }

    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('ciscospark', 'view all', 'index.php?id={course}', null);

        return $rules;
    }

}
