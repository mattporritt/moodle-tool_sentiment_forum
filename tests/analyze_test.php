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
 * Unit tests.
 *
 * @package     tool_sentiment_forum
 * @copyright   2018 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_sentiment_forum\analyze\analyze;

/**
 * Unit tests.
 *
 * @package     tool_sentiment_forum
 * @copyright   2018 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_sentiment_forum_analyze_testcase extends advanced_testcase {

    /**
     * Test get_enabled_forums method.
     */
    public function test_get_enabled_forums() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        // Create a course.
        $course1 = $generator->create_course();

        // Create some forums and enable sentiment analysis in only one forum.
        $forum1 = $generator->create_module('forum', array('course' => $course1->id));
        $forum2 = $generator->create_module('forum', array('course' => $course1->id, 'sentimentenabled' => 1));

        // Get enabled forums and check results.
        $analyzer = new analyze();
        $forums = $analyzer->get_enabled_forums();
        $forumresult = $forums->current();

        $this->assertEquals(1, count($forums));
        $this->assertEquals($forum2->id, $forumresult->forumid);

    }

    /**
     * Test get_enabled_forums method with explicit course.
     */
    public function test_get_enabled_forums_explicit_course() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        // Create some courses.
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();

        // Create some forums and enable sentiment analysis.
        $forum1 = $generator->create_module('forum', array('course' => $course1->id, 'sentimentenabled' => 1));
        $forum2 = $generator->create_module('forum', array('course' => $course2->id, 'sentimentenabled' => 1));

        // Get enabled forums and check results.
        $analyzer = new analyze();
        $forums = $analyzer->get_enabled_forums($course1->id);
        $forumresult = $forums->current();

        $this->assertEquals(1, count($forums));
        $this->assertEquals($forum1->id, $forumresult->forumid);

    }
}
