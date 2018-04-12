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
 * Spark API wrapper
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ciscospark;

class spark {

    var $client_id;
    var $client_secret;
    var $authorization_endpoint = 'https://api.ciscospark.com/v1/authorize';
    var $token_endpoint         = 'https://api.ciscospark.com/v1/access_token';
    var $redirect_uri;
    var $access_token;
    var $cmid;

    /**
     * 
     * @global \mod_ciscospark\type $CFG
     * @param int $cmid
     * @param string $access_token optional - default null if you want to use current user's token
     */
    public function __construct($cmid = null, $access_token = null) {
        global $CFG;
        $this->cmid         = $cmid;
        $this->redirect_uri = $CFG->wwwroot . '/mod/ciscospark/auth.php';
        
        $this->client_id = get_config('ciscospark', 'client_id');
        $this->client_secret = get_config('ciscospark', 'client_secret');
        
        if (empty($this->client_id) || empty($this->client_secret)) {
            print_error('Ciscospark settings not defined');
        }
        $this->access_token = $access_token ? $access_token : $this->get_user_token();
    }

    /**
     * Get token of current user
     * @global type $USER
     * @global type $DB
     * @global type $CFG
     * @return string access_token
     */
    protected function get_user_token() {
        global $USER, $DB, $CFG;
        
        if (!$user_token = $DB->get_record('ciscospark_users_tokens', array('userid' => $USER->id))) {
            require_once $CFG->dirroot . '/mod/ciscospark/lib/oauth/client.php';

            $client = new \OAuth2\Client($this->client_id, $this->client_secret);

            $code = optional_param('code', null, PARAM_RAW);

            if (empty($code)) {
                if (empty($this->cmid)) {
                    print_error('CM id not defined');
                }
                $extras   = array('state' => $this->cmid, 'scope' => 'spark:all');
                $auth_url = $client->getAuthenticationUrl($this->authorization_endpoint, $this->redirect_uri, $extras);
                header('Location: ' . $auth_url);
                die('Redirect');
            } else {
                $params   = array('code' => $code, 'redirect_uri' => $this->redirect_uri);
                $response = $client->getAccessToken($this->token_endpoint, 'authorization_code', $params);

                if ($response['code'] >= 300) {
                    
                    print_object($response);
                    print_error('ERROR in spark authentification');
                }

                $result = $response['result'];

                $user_token                           = new \stdClass();
                $user_token->userid                   = $USER->id;
                $user_token->access_token             = $result['access_token'];
                $user_token->refresh_token            = $result['refresh_token'];
                $user_token->expires_in               = $result['expires_in'];
                $user_token->refresh_token_expires_in = $result['refresh_token_expires_in'];
                $user_token->last_update              = time();
                $DB->insert_record('ciscospark_users_tokens', $user_token);
            }
        }
        return $user_token->access_token;
    }

    /**
     * Make a call to the Spark API
     * @param string $url
     * @param array $fields body parameters
     * @param string $custom_request GET|POST|PUT|DELETE defaut POST
     * @return stdClass REST response as json
     */
    protected function api_call($url, $fields, $custom_request = 'POST') {
        $curl = curl_init();
        
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $custom_request,
            CURLOPT_POSTFIELDS     => json_encode($fields),
            CURLOPT_HTTPHEADER     => array(
                "authorization: Bearer " . $this->access_token,
                "content-type: application/json"
            )
        );
        
        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        if ($response === false) {
            print_object('CURL Failed');
            print_object($err);
        }

        return json_decode($response);
    }

    /**
     * Create a spark room and add the current user token as moderator
     * @param string $room_title
     * @return stdClass room infos
     */
    public function create_room($room_title) {
        $url    = "https://api.ciscospark.com/v1/rooms";
        $fields = array('title' => $room_title);
        return $this->api_call($url, $fields);
    }

    /**
     * Create a team
     * @param string $team_name
     * @return stdClass team infos
     */
    public function create_team($team_name) {
        $url    = "https://api.ciscospark.com/v1/teams";
        $fields = array('name' => $team_name);
        return $this->api_call($url, $fields);
    }

    /**
     * Create a room for a given team
     * @param string $team_id
     * @param string $room_title
     * @return stdClass room details
     */
    public function create_team_room($team_id, $room_title) {        
        $url    = "https://api.ciscospark.com/v1/rooms";
        $fields = array('title' => $room_title, 'teamId' => $team_id);
        return $this->api_call($url, $fields);
    }

    /**
     * Delete a room
     * @param string $room_id
     * @return string errors
     */
    public function delete_room($room_id) {
        $url            = "https://api.ciscospark.com/v1/rooms/" . $room_id;
        $custom_request = 'DELETE';
        return $this->api_call($url, array(), $custom_request);
    }
    
    /**
     * Update a room name
     * @param string $room_id
     * @param string $room_title
     * @return string errors
     */
    public function update_room($room_id, $room_title) {
        $url            = "https://api.ciscospark.com/v1/rooms/" . $room_id;
        $fields         = array('title' => $room_title);
        $custom_request = 'PUT';
        return $this->api_call($url, $fields, $custom_request);
    }

    /**
     * Delete a team
     * @param string $team_id
     * @return string errors
     */
    public function delete_team($team_id) {
        $url            = "https://api.ciscospark.com/v1/teams/" . $team_id;
        $custom_request = 'DELETE';
        return $this->api_call($url, array(), $custom_request);
    }
    
    /**
     * Update a team name
     * @param string $team_id
     * @param string $team_name
     * @return string errors
     */
    public function update_team($team_id, $team_name) {
        $url            = "https://api.ciscospark.com/v1/teams/" . $team_id;
        $fields         = array('name' => $team_name);
        $custom_request = 'PUT';
        return $this->api_call($url, $fields, $custom_request);
    }

    /**
     * Add a user to a team
     * @param string $user_email
     * @param string $team_id
     * @param bool $is_moderator optional - default false
     * @return stdClass team membership details
     */
    public function add_user_to_team($user_email, $team_id, $is_moderator = false) {
        $url    = "https://api.ciscospark.com/v1/team/memberships";
        $fields = array('teamId' => $team_id, 'personEmail' => $user_email, 'isModerator' => $is_moderator);
        return $this->api_call($url, $fields);
    }

    /**
     * Add a user to a room
     * @param string $user_email
     * @param string $room_id
     * @param bool $is_moderator optional - default false
     * @return stdClass membership details
     */
    public function add_user_to_room($user_email, $room_id, $is_moderator = false) {
        $url    = "https://api.ciscospark.com/v1/memberships";
        $fields = array('roomId' => $room_id, 'personEmail' => $user_email, 'isModerator' => $is_moderator);
        return $this->api_call($url, $fields);
    }

    /**
     * Remove a user from a team
     * @param string $membership_id
     * @return string errors
     */
    public function remove_user_from_team($membership_id) {
        $url            = "https://api.ciscospark.com/v1/team/memberships/" . $membership_id;
        $custom_request = 'DELETE';
        return $this->api_call($url, array(), $custom_request);
    }

    /**
     * Remove a user from a room
     * @param string $membership_id
     * @return string errors
     */
    public function remove_user_from_room($membership_id) {
        $url            = "https://api.ciscospark.com/v1/memberships/" . $membership_id;
        $custom_request = 'DELETE';
        return $this->api_call($url, array(), $custom_request);
    }
    
    /**
     * Send a message to a user
     * @param string $user_email
     * @param string $message
     * @return type
     */
    public function send_message($user_email, $message) {
        $url    = "https://api.ciscospark.com/v1/messages";
        $fields = array('toPersonEmail' => $user_email, 'text' => $message);
        return $this->api_call($url, $fields);
    }

    /**
     * Add a bot into a room
     * @param string $room_id
     * @return stdClass bot membership details
     */
    public function add_bot_to_room($room_id) {
        if (!$bot_email = ciscospark_get_bot_email()) {
            print_error('Bot email not configured');
        }
        return $this->add_user_to_room($bot_email, $room_id, true);
    }

    /**
     * Update current token
     * @param string $token
     */
    public function set_token($token) {
        $this->token = $token;
    }

    /**
     * Define if a user is moderator in a room
     * @param string $membership_id
     * @param bool $is_moderator
     * @return stdClass membership infos
     */
    public function set_moderator_permission($membership_id, $is_moderator) {
        $url            = "https://api.ciscospark.com/v1/memberships/" . $membership_id;
        $fields         = array('isModerator' => $is_moderator);
        $custom_request = "PUT";
        return $this->api_call($url, $fields, $custom_request);
    }
    
    /**
     * 
     * @param int $team_membership_id
     * @param int $is_moderator
     * @return type
     */
    public function set_team_moderator_permission($team_membership_id, $is_moderator) {
        $url            = "https://api.ciscospark.com/v1/team/memberships/" . $team_membership_id;
        $fields         = array('isModerator' => $is_moderator);
        $custom_request = "PUT";
        return $this->api_call($url, $fields, $custom_request);
    }

    /**
     * Get user membership for a given room
     * @param string $room_id
     * @param string $user_email
     * @return stdClass membership details
     */
    public function get_room_membership($room_id, $user_email) {
        $url            = "https://api.ciscospark.com/v1/memberships?roomId=".$room_id."&personEmail=".$user_email;
        $fields         = array();
        $custom_request = 'GET';
        $response = $this->api_call($url, $fields, $custom_request);
        return $response->items[0];
    }
    
    /**
     * List memberships for a given rooms
     * @param string $room_id
     * @return array
     */
    public function get_room_memberships($room_id) {
        $url            = "https://api.ciscospark.com/v1/memberships";
        $fields         = array('roomId' => $room_id);
        $custom_request = 'GET';
        $response = $this->api_call($url, $fields, $custom_request);
        return $response->items;
    }
    
    /**
     * Get a team membership
     * @param string $team_id
     * @param string $user_email
     * @return type
     */
    public function get_team_membership($team_id, $user_email) {
        $url            = "https://api.ciscospark.com/v1/team/memberships?teamId=".$team_id."&personEmail=".$user_email;
        $fields         = array();
        $custom_request = 'GET';
        $response = $this->api_call($url, $fields, $custom_request);
        return $response->items[0];
    }
    
    /**
     * Create a webhook
     * @param array $fields
     * @return type
     */
    public function create_webhook($fields) {
        $url = 'https://api.ciscospark.com/v1/webhooks/';
        return $this->api_call($url, $fields);
    }
    
    /**
     * Delete a webhook
     * @param string $webhook_id
     * @return type
     */
    public function delete_webhook($webhook_id) {
        $url            = "https://api.ciscospark.com/v1/webhooks/" . $webhook_id;
        $custom_request = 'DELETE';
        return $this->api_call($url, array(), $custom_request);
    }

}
