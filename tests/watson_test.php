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
                        'sentiment' => new \stdClass(),
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
     * Test analyze sentiment functionality.
     */
    public function test_analyze_sentiment() {
        $callresponse = array (
                'usage' => array (
                        'text_units' => 1,
                        'text_characters' => 26,
                        'features' => 4
                ),
                'sentiment' => array (
                        'document' => array (
                                'score' => 0.900,
                                'label' => 'positive'
                        )
                ),
                'language' => 'en',
                'keywords' => array (
                        0 => array (
                                'text' => 'service',
                                'relevance' => 0.945
                        )
                ),
                'emotion' => array (
                        'document' => array (
                                'emotion' => array (
                                        'sadness' => 0.0195,
                                        'joy' => 0.785,
                                        'fear' => 0.000,
                                        'disgust' => 0.012,
                                        'anger' => 0.0164
                                )
                        )
                ),
                'concepts' => array (),
        );

        // Mock out call api to return a predictable value.
        $builder = $this->getMockBuilder('tool_sentiment_forum\watson\watson_api');
        $builder->setMethods(array('call_api'));
        $stub = $builder->getMock();
        $stub->method('call_api')->willReturn($callresponse);

        $response = $stub->analyze_sentiment('the test text');
        list($sentiment, $emotion, $keywords, $concepts) = $response;

        // Check the results.
        $this->assertEquals($sentiment, 0.9);
        $this->assertEquals($emotion['sadness'], 0.0195);
        $this->assertEquals($emotion['joy'], 0.785);
        $this->assertEquals($emotion['fear'], 0.000);
        $this->assertEquals($emotion['disgust'], 0.012);
        $this->assertEquals($emotion['anger'], 0.0164);
        $this->assertEquals($keywords[0]['text'], 'service');
        $this->assertEquals($concepts, array());

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
        list($sentiment, $emotion, $keywords, $concepts) = $response;

        // Check the results.
        $this->assertEquals($sentiment, 0);
        $this->assertEquals($emotion['sadness'], 0);
        $this->assertEquals($emotion['joy'], 0);
        $this->assertEquals($emotion['fear'], 0);
        $this->assertEquals($emotion['disgust'], 0);
        $this->assertEquals($emotion['anger'], 0);
        $this->assertEquals($keywords, array());
        $this->assertEquals($concepts, array());

    }

    /**
     * Test analyze sentiment request format.
     */
    public function test_analyze_sentiment_request() {
        $this->resetAfterTest(true);
        set_config('apiendpoint', 'https://localhost:8080', 'tool_sentiment_forum');
        set_config('maxkeywords', 1, 'tool_sentiment_forum');
        set_config('maxconcepts', 1, 'tool_sentiment_forum');

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

        $response = $client->analyze_sentiment('the test text');
        $request = $container[0]['request'];
        $requestbody = json_decode($request->getBody()->getContents());

        error_log(print_r($requestbody, true));

        // Check the results.
        $this->assertEquals($request->getUri()->getScheme(), 'https');
        $this->assertEquals($request->getUri()->getHost(),  'localhost');
        $this->assertEquals($request->getUri()->getPort(),  '8080');
        $this->assertTrue($request->hasHeader('content-type'));
    }
}
