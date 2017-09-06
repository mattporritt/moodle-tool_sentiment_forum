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
 * Watson API interface for sentiment fourum tool.
 *
 * @package     tool_sentiment_forum
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_sentiment_forum\watson;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');

/**
* Watson API interface for sentiment fourum tool.
 *
 * @package     tool_sentiment_forum
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class watson_api {

    /**
     * Initialises the class.
     * Makes relevant configuration from config available and
     * creates Guzzle client.
     *
     * @param \stdClass $moduleinstance Activity instance.
     */
    public function __construct($handler = false) {
        $this->config = get_config('tool_sentiment_forum');
        $this->username = $this->config->username;
        $this->password = $this->config->password;
        $this->tokenendpoint= $this->config->tokenendpoint;
        $this->apiendpoint = $this->config->apiendpoint;
        $this->token = '';

        // Allow the caller to instansite the Guzzle client
        // with a custom handler.
        if ($handler) {
            $this->client = new \GuzzleHttp\Client(['handler' => $handler]);
        } else {
            $this->client = new \GuzzleHttp\Client();
        }

    }

    /**
     * Generates OAuth token from stored key and secret deatils.
     * Token is used to make API calls.
     *
     * @return string $token the API acesss token.
     */
    private function generate_token() {
        $url = $this->tokenendpoint;
        $authcreds = base64_encode ($this->username.':'.$this->password);
        $authstring = 'Basic '.$authcreds;
        $headers = ['Authorization' => $authstring];
        $params = ['headers' => $headers, 'query' => ['url' => $this->apiendpoint]];

        $response = $this->client->request('GET', $url, $params);
        $accesstoken = $response->getBody()->getContents();

        return $accesstoken;

    }

    /**
     * Calls the Watson API.
     *
     * @param string $url Watson service endpoint to call
     * @param bool $retry
     * @return object $responseobj The response recevied from the API
     */
    public function call_api($url, $packet) {

        // Sort out token to be used in analysis calls.
        if ($this->token == '') {
            $this->token = $this->generate_token();
        }

        $params = ['headers' => ['Content-Type' => 'application/json',
                'X-Watson-Authorization-Token' => $this->token ]];

        // Requests that receive a 4xx or 5xx response will throw a
        // Guzzle\Http\Exception\BadResponseException. We want to
        // handle this in a sane way and provide the caller with
        // a useful response. So we catch the error and return the
        // response.
        try {
            $response = $this->client->request('POST', $url, $params);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $response = $e->getResponse();
        }

        $responsecode = $response->getStatusCode();
        $responseobj = json_decode($response->getBody(), true);

        return $responseobj;
    }

    public function analyze_sentiment($text) {
        $url = $this->apiendpoint . '/v1/analyze?version=2017-02-27';


//         curl -X POST --header 'Content-Type: application/json' --header 'Accept: application/json' -d '{ \
//    "text": "you all suck alot.", \
//    "features": { \
//      "emotion": {}, \
//      "sentiment":{} \
//    } \
//  }' 'https://watson-api-explorer.mybluemix.net/natural-language-understanding/api/v1/analyze?version=2017-02-27'
    }

}