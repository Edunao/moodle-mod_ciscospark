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
 * Class webhook
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ciscospark;

class webhook {
    var $id;
    var $name;
    var $targeturl;
    var $resource;
    var $event;
    var $filter;
    var $secret;
    var $webhookid;
    
    /**
     * Webhook constructor
     * @param type $dbwebhook
     */
    public function __construct($dbwebhook) {
        foreach ($dbwebhook as $key => $value) {
            $this->{$key} = $value;
        }
    }
    
    /**
     * Get a webhook by id
     * @global \mod_ciscospark\type $DB
     * @param int $id
     * @return boolean|\mod_ciscospark\webhook
     */
    public static function get_by_id($id) {
        global $DB;
        if (!$dbwebhook = $DB->get_record('ciscospark_webhooks', array('id' => $id))) {
            return false;
        }
        return new webhook($dbwebhook);
    }
    
    /**
     * Get a webhook by webhookid
     * @global \mod_ciscospark\type $DB
     * @param string $webhookid
     * @return boolean|\mod_ciscospark\webhook
     */
    public static function get_by_webhookid($webhookid) {
        global $DB;
        if (!$dbwebhook = $DB->get_record('ciscospark_webhooks', array('webhookid' => $webhookid))) {
            return false;
        }
        return new webhook($dbwebhook);
    }
    
    /**
     * Get a webhook by name and roomid
     * @global type $DB
     * @param string $name
     * @param int $roomid
     * @return boolean|\mod_ciscospark\webhook
     */
    public static function get_by_name($name, $roomid) {
        global $DB;
        if (!$dbwebhook = $DB->get_record('ciscospark_webhooks', array('name' => $name, 'roomid' => $roomid))) {
            return false;
        }
        return new webhook($dbwebhook);
    }
    
    /**
     * Create a webhook
     * @global \mod_ciscospark\type $DB
     * @param type $webhook_infos
     * @return boolean|\mod_ciscospark\webhook
     */
    public static function create_webhook($webhook_infos) {
        global $DB;
        if (self::get_by_name($webhook_infos->name, $webhook_infos->roomid)) {
            return false;
        }
        $spark_api = new spark();
        
        $webhook = $spark_api->create_webhook($webhook_infos);
        
        if (!isset($webhook->id)) {
            print_object($webhook_infos);
            print_object($webhook);
            exit;
        }
        
        $webhook_infos->webhookid = $webhook->id;
        $webhook_infos->targeturl = $webhook_infos->targetUrl;
        
        $webhook_infos->id = $DB->insert_record('ciscospark_webhooks', $webhook_infos);
        
        return new webhook($webhook_infos);
    }
    
    /**
     * Delete webhook
     * @global \mod_ciscospark\type $DB
     */
    public function delete() {
        global $DB;
        $spark_api = new spark();
        $spark_api->delete_webhook($this->webhookid);
        $DB->delete_records('ciscospark_webhooks', array('id' => $this->id));
    }
}
