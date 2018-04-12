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
 * Ciscospark strings
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['modulename']       = 'Cisco Spark';
$string['modulename_help']  = 'The Cisco Spark Activity Module provides collaborative communication Spaces in Cisco Spark.
These collaborative Spaces are accessible both via Moodle and via the Cisco Spark Mobile, PC and MAC apps, making it possible to continue to exchange, work and communicate everywhere, all the time, from any type of terminal.

This Moodle Activity can be configured to create either a single discussion space, including all participants in a class, or a set of separate spaces for different groups of class members.

Each Moodle class will be represented in a teacher\'s Cisco Spark interface as a TEAM containing all of the class\'s spaces. The teacher can enable or disable student access to different spaces at will.

The Cisco Spark activity for Moodle is an ideal solution for management of spaces for exchange and collaboration for team projects, guided personal projects and all sorts of other collaborative activities.
The direct integration of Cisco Spark access into Moodle facilitates tracking of which discussion spaces relate to which courses and projects.
Additional Cisco Spark features, such as video conferencing, accessible via class discussion spaces, expand the possibilities for teaching in the modern age';
$string['modulename_link']  = 'mod/ciscospark/view';
$string['modulenameplural'] = 'Cisco Spark';

$string['pluginadministration'] = 'Cisco Spark administration';
$string['pluginname']           = 'Cisco Spark';
$string['crontask']             = 'Refresh the Cisco Spark tokens';
$string['sync_rooms']           = 'Spaces synchronization';
$string['use_groups']           = 'Use course groups';
$string['remove_user_message']  = 'You don\'t have access to this discussion space';

//capabilities
$string['ciscospark:view']            = 'See activity';
$string['ciscospark:addinstance']     = 'Add an instance of Cisco Spark';
$string['ciscospark:viewhiddenrooms'] = 'See hidden spaces';
$string['ciscospark:hiderooms']       = 'Hide spaces';

//specific strings
$string['noavailablerooms'] = 'You don\'t have access to any discussion spaces';
$string['showhide']         = 'Show / Hide discussion space';
$string['enterroom']        = 'Go to Cisco Spark Space';

//settings
$string['setting_clientid']     = 'Cisco Spark integration Client ID';
$string['setting_clientsecret'] = 'Cisco Spark integration Client Secret';
$string['setting_bottoken']     = 'Bot access token';
$string['setting_botemail']     = 'Bot email';
