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

    /**
     * Test call api functionality
     */
    public function test_call_api() {
        $container = [];
        $history = Middleware::history($container);

        // Create a mock response and stack.
        $mock = new MockHandler([
                new Response(200, ['Content-Type' => 'application/json'], '{"properties":"value"}')
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($history); // Add the history middleware to the handler stack.

        $client = new tool_sentiment_forum\watson\watson_api($stack);
        $client->token = '1234';

        $url = 'http://localhost:8080/foo?bar=blerg';
        $params = ['text' => 'the test text',
                'features' => [
                        'emotion' => new \stdClass(),
                        'sentiment' => new \stdClass()
                ]
        ];

        $response = $client->call_api($url, $params);
        $request = $container[0]['request'];
        $contentheader = $request->getHeader('content-type');

        // Check the results.
        $this->assertEquals($response['properties'], 'value');
        $this->assertEquals($request->getUri()->getScheme(), 'http');
        $this->assertEquals($request->getUri()->getHost(),  'localhost');
        $this->assertEquals($request->getUri()->getPort(),  '8080');
        $this->assertEquals($request->getUri()->getPath(), '/foo');
        $this->assertEquals($request->getUri()->getQuery(), 'bar=blerg');
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertEquals($contentheader, array('application/json'));

    }

    /**
     * Test analyze sentiment functionality, with an empty response.
     */
    public function test_analyze_sentiment_no_result() {
        // Mock out call api to return a predictable value.
        $builder = $this->getMockBuilder('tool_sentiment_forum\watson\watson_api');
        $builder->setMethods(array('call_api'));
        $stub = $builder->getMock();
        $stub->method('call_api')->willReturn(array());

        $response = $stub->analyze_sentiment('the test text');

        error_log(print_r($response, true));

    }
}
