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
 * Ciscospark tasks
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();


$tasks = array(
        //run cron every days at 4am
        array(
                'classname' => 'mod_ciscospark\task\cron_task',
                'blocking'  => 0,
                'minute'    => '*',
                'hour'      => '4',
                'day'       => '*',
                'month'     => '*',
                'dayofweek' => '*'
        ),
        //run cron every hours
        array(
                'classname' => 'mod_ciscospark\task\sync_rooms',
                'blocking'  => 0,
                'minute'    => '*',
                'hour'      => '*/1', //every hours
                'day'       => '*',
                'month'     => '*',
                'dayofweek' => '*'
        )
);
