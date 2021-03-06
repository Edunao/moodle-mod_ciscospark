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
 * Class backup_ciscospark_activity_task
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/ciscospark/backup/moodle2/backup_ciscospark_stepslib.php');

class backup_ciscospark_activity_task extends backup_activity_task {
    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(new backup_ciscospark_activity_structure_step('ciscospark_structure', 'ciscospark.xml'));
    }

    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot . '/mod/ciscospark', '#');

        //Access a list of all links in a course
        $pattern     = '#(' . $base . '/index\.php\?id=)([0-9]+)#';
        $replacement = '$@URLINDEX*$2@$';
        $content     = preg_replace($pattern, $replacement, $content);

        //Access the link supplying a course module id
        $pattern     = '#(' . $base . '/view\.php\?id=)([0-9]+)#';
        $replacement = '$@URLVIEWBYID*$2@$';
        $content     = preg_replace($pattern, $replacement, $content);

        //Access the link supplying an instance id
        $pattern     = '#(' . $base . '/view\.php\?u=)([0-9]+)#';
        $replacement = '$@URLVIEWBYU*$2@$';
        $content     = preg_replace($pattern, $replacement, $content);

        return $content;
    }
}
