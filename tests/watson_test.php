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
 * Unit tests for IBM Watson API.
 *
 * @package     tool_sentiment_forum
 * @copyright   2018 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

use tool_sentiment_forum\watson\watson_api;
use \GuzzleHttp\Handler\MockHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \GuzzleHttp\Psr7\Response;
use \GuzzleHttp\Psr7\Request;

/**
 * Unit tests for IBM Watson API.
 *
 * @package     tool_sentiment_forum
 * @copyright   2018 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_sentiment_forum_watson_testcase extends advanced_testcase {

    /**
     * Test that Guzzle proxy array is correctly constructed
     * from Moodle Proxy settings.
     */
    public function test_proxy_construct() {
        $this->resetAfterTest(true);
        set_config('proxyhost', 'localhost');
        set_config('proxyport', 3128);
        set_config('proxybypass', 'localhost, 127.0.0.1');

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('tool_sentiment_forum\watson\watson_api', 'proxyconstruct');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new tool_sentiment_forum\watson\watson_api); // Get result of invoked method.

        $expected = ['proxy' => ['http'  => 'tcp://localhost:3128',
                                 'https'  => 'tcp://localhost:3128',
                                 'no' => ['localhost', '127.0.0.1']]];

        $this->assertEquals($proxy, $expected, $canonicalize = true);
    }

    /**
     * Test that Guzzle proxy array is correctly constructed
     * from Moodle Proxy settings.
     * With proxy authentication.
     */
    public function test_proxy_construct_auth() {
        $this->resetAfterTest(true);
        set_config('proxyhost', 'localhost');
        set_config('proxyport', 3128);
        set_config('proxybypass', 'localhost, 127.0.0.1');
        set_config('proxyuser', 'user1');
        set_config('proxypassword', 'password');

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('tool_sentiment_forum\watson\watson_api', 'proxyconstruct');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new tool_sentiment_forum\watson\watson_api); // Get result of invoked method.

        $expected = ['proxy' => ['http'  => 'tcp://user1:password@localhost:3128',
                                 'https'  => 'tcp://user1:password@localhost:3128',
                                  'no' => ['localhost', '127.0.0.1']]];

        $this->assertEquals($proxy, $expected, $canonicalize = true);
    }

    /**
     * Test that Guzzle proxy array is correctly constructed
     * from Moodle Proxy settings.
     * With proxy authentication and no proxy bypass.
     */
    public function test_proxy_construct_no_bypass() {
        $this->resetAfterTest(true);
        set_config('proxyhost', 'localhost');
        set_config('proxyport', 3128);
        set_config('proxybypass', '');
        set_config('proxyuser', 'user1');
        set_config('proxypassword', 'password');

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('tool_sentiment_forum\watson\watson_api', 'proxyconstruct');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new tool_sentiment_forum\watson\watson_api); // Get result of invoked method.

        $expected = ['proxy' => ['http'  => 'tcp://user1:password@localhost:3128',
                                 'https'  => 'tcp://user1:password@localhost:3128']];

        $this->assertEquals($proxy, $expected, $canonicalize = true);
    }

    /**
     * Test that Guzzle proxy array is correctly constructed
     * from Moodle Proxy settings.
     * Using socks as the protocol.
     */
    public function test_proxy_construct_socks() {
        $this->resetAfterTest(true);
        set_config('proxyhost', 'localhost');
        set_config('proxyport', 3128);
        set_config('proxybypass', 'localhost, 127.0.0.1');
        set_config('proxytype', 'SOCKS5');

        // We're testing a private method, so we need to setup reflector magic.
        $method = new ReflectionMethod('tool_sentiment_forum\watson\watson_api', 'proxyconstruct');
        $method->setAccessible(true); // Allow accessing of private method.
        $proxy = $method->invoke(new tool_sentiment_forum\watson\watson_api); // Get result of invoked method.

        $expected = ['proxy' => ['http'  => 'socks5://localhost:3128',
                                 'https'  => 'socks5://localhost:3128',
                                 'no' => ['localhost', '127.0.0.1']]];

        $this->assertEquals($proxy, $expected, $canonicalize = true);
    }
}
