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

        $count = 0;
        foreach ($forums as $forum) {
            $count++;
        }

        $this->assertEquals(1, $count);
        $this->assertEquals($forum2->id, $forumresult->forumid);
        $forums->close();  // Close forums recordset.

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

        $count = 0;
        foreach ($forums as $forum) {
            $count++;
        }

        $this->assertEquals(1, $count);
        $this->assertEquals($forum1->id, $forumresult->forumid);
        $forums->close();  // Close forums recordset.

    }

    /**
     * Test get_unanalyzed_posts method.
     */
    public function test_get_unanalyzed_posts() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course(); // Create a course.
        $forum = $generator->create_module('forum', array(
            'course' => $course->id,
            'sentimentenabled' => 1)); // Create forum with sentiment analysis enabled.

        // Add a discussion.
        $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $generator->get_plugin_generator('mod_forum')->create_discussion($record);

        // Add a post.
        $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $generator->get_plugin_generator('mod_forum')->create_post($record);

        // Get enabled forums and check results.
        $analyzer = new analyze();
        $posts = $analyzer->get_unanalyzed_posts($forum->id);

        $count = 0;
        foreach ($posts as $post) {
            $count++;
        }

        $this->assertEquals(2, $count);
        $posts->close(); // Close recordset.

    }

    /**
     * Test insert keywords method.
     */
    public function test_insert_keywords() {
        global $DB;
        $this->resetAfterTest(true);

        $forumid = 1;
        $post = new \stdClass();
        $post->id = 2;
        $keywords = array(array('text' => 'Service', 'relevance' => 0.945 ));

        $analyzer = new analyze();
        $analyzer->insert_keywords($forumid, $post, $keywords);

        // Check keyword.
        $keywordresult = $DB->get_record('tool_sentiment_forum_keyword', array('keyword' => 'service'));
        $this->assertEquals(1, $keywordresult->count);

        // Check forum keyword.
        $forumresult = $DB->get_record('tool_sentiment_forum_k_forum', array('forumid' => $forumid));
        $this->assertEquals($keywordresult->id, $forumresult->keywordid);
        $this->assertEquals($forumid, $forumresult->forumid);
        $this->assertEquals(1, $forumresult->count);

        // Check post keyword.
        $postresult = $DB->get_record('tool_sentiment_forum_k_post', array('postid' => $post->id));
        $this->assertEquals($keywordresult->id, $forumresult->keywordid);
        $this->assertEquals($post->id, $postresult->postid);
        $this->assertEquals(1, $postresult->count);

    }

    /**
     * Test insert keywords method with multiple entries.
     */
    public function test_insert_keywords_multiple_entries() {
        global $DB;
        $this->resetAfterTest(true);

        $forumid = 1;
        $forumid2 = 2;
        $post = new \stdClass();
        $post->id = 2;
        $post2 = new \stdClass();
        $post2->id = 3;
        $keywords = array(array('text' => 'Service', 'relevance' => 0.945 ));

        $analyzer = new analyze();
        $analyzer->insert_keywords($forumid, $post, $keywords);
        $analyzer->insert_keywords($forumid2, $post2, $keywords);

        // Check keyword.
        $keywordresult = $DB->get_record('tool_sentiment_forum_keyword', array('keyword' => 'service'));
        $this->assertEquals(2, $keywordresult->count);

        // Check forum keyword.
        $forumresult = $DB->get_record('tool_sentiment_forum_k_forum', array('forumid' => $forumid));
        $this->assertEquals($keywordresult->id, $forumresult->keywordid);
        $this->assertEquals($forumid, $forumresult->forumid);
        $this->assertEquals(1, $forumresult->count);

        // Check post keyword.
        $postresult = $DB->get_record('tool_sentiment_forum_k_post', array('postid' => $post->id));
        $this->assertEquals($keywordresult->id, $forumresult->keywordid);
        $this->assertEquals($post->id, $postresult->postid);
        $this->assertEquals(1, $postresult->count);

    }

    /**
     * Test insert keywords method with multiple keywords
     */
    public function test_insert_keywords_multiple_keywords() {
        global $DB;
        $this->resetAfterTest(true);

        $forumid = 1;
        $post = new \stdClass();
        $post->id = 2;
        $keywords = array(
            array('text' => 'Service', 'relevance' => 0.945 ),
            array('text' => 'Insert', 'relevance' => 0.945 )
        );

        $analyzer = new analyze();
        $analyzer->insert_keywords($forumid, $post, $keywords);

        // Check keyword.
        $keywordresult = $DB->get_record('tool_sentiment_forum_keyword', array('keyword' => 'service'));
        $this->assertEquals(1, $keywordresult->count);

        // Check forum keyword.
        $forumresult = $DB->get_record('tool_sentiment_forum_k_forum', array('forumid' => $forumid, 'keywordid' => $keywordresult->id));
        $this->assertEquals($keywordresult->id, $forumresult->keywordid);
        $this->assertEquals($forumid, $forumresult->forumid);
        $this->assertEquals(1, $forumresult->count);

        // Check post keyword.
        $postresult = $DB->get_record('tool_sentiment_forum_k_post', array('postid' => $post->id, 'keywordid' => $keywordresult->id));
        $this->assertEquals($keywordresult->id, $forumresult->keywordid);
        $this->assertEquals($post->id, $postresult->postid);
        $this->assertEquals(1, $postresult->count);

    }

    /**
     * Test insert concepts method.
     */
    public function test_insert_concepts() {
        global $DB;
        $this->resetAfterTest(true);

        $forumid = 1;
        $post = new \stdClass();
        $post->id = 2;
        $concepts = array(array('text' => 'Service', 'relevance' => 0.945 ));

        $analyzer = new analyze();
        $analyzer->insert_concepts($forumid, $post, $concepts);

        // Check concept.
        $conceptresult = $DB->get_record('tool_sentiment_forum_concept', array('concept' => 'service'));
        $this->assertEquals(1, $conceptresult->count);

        // Check forum concept.
        $forumresult = $DB->get_record('tool_sentiment_forum_c_forum', array('forumid' => $forumid));
        $this->assertEquals($conceptresult->id, $forumresult->conceptid);
        $this->assertEquals($forumid, $forumresult->forumid);
        $this->assertEquals(1, $forumresult->count);

        // Check post concept.
        $postresult = $DB->get_record('tool_sentiment_forum_c_post', array('postid' => $post->id));
        $this->assertEquals($conceptresult->id, $forumresult->conceptid);
        $this->assertEquals($post->id, $postresult->postid);
        $this->assertEquals(1, $postresult->count);

    }

    /**
     * Test insert concepts method with multiple entries.
     */
    public function test_insert_concepts_multiple_entries() {
        global $DB;
        $this->resetAfterTest(true);

        $forumid = 1;
        $forumid2 = 2;
        $post = new \stdClass();
        $post->id = 2;
        $post2 = new \stdClass();
        $post2->id = 3;
        $concepts = array(array('text' => 'Service', 'relevance' => 0.945 ));

        $analyzer = new analyze();
        $analyzer->insert_concepts($forumid, $post, $concepts);
        $analyzer->insert_concepts($forumid2, $post2, $concepts);

        // Check concept.
        $conceptresult = $DB->get_record('tool_sentiment_forum_concept', array('concept' => 'service'));
        $this->assertEquals(2, $conceptresult->count);

        // Check forum concept.
        $forumresult = $DB->get_record('tool_sentiment_forum_c_forum', array('forumid' => $forumid));
        $this->assertEquals($conceptresult->id, $forumresult->conceptid);
        $this->assertEquals($forumid, $forumresult->forumid);
        $this->assertEquals(1, $forumresult->count);

        // Check post concept.
        $postresult = $DB->get_record('tool_sentiment_forum_c_post', array('postid' => $post->id));
        $this->assertEquals($conceptresult->id, $forumresult->conceptid);
        $this->assertEquals($post->id, $postresult->postid);
        $this->assertEquals(1, $postresult->count);

    }

    /**
     * Test insert concepts method with multiple concepts
     */
    public function test_insert_concepts_multiple_concepts() {
        global $DB;
        $this->resetAfterTest(true);

        $forumid = 1;
        $post = new \stdClass();
        $post->id = 2;
        $concepts = array(
            array('text' => 'Service', 'relevance' => 0.945 ),
            array('text' => 'Insert', 'relevance' => 0.945 )
        );

        $analyzer = new analyze();
        $analyzer->insert_concepts($forumid, $post, $concepts);

        // Check concept.
        $conceptresult = $DB->get_record('tool_sentiment_forum_concept', array('concept' => 'service'));
        $this->assertEquals(1, $conceptresult->count);

        // Check forum concept.
        $forumresult = $DB->get_record('tool_sentiment_forum_c_forum', array('forumid' => $forumid, 'conceptid' => $conceptresult->id));
        $this->assertEquals($conceptresult->id, $forumresult->conceptid);
        $this->assertEquals($forumid, $forumresult->forumid);
        $this->assertEquals(1, $forumresult->count);

        // Check post concept.
        $postresult = $DB->get_record('tool_sentiment_forum_c_post', array('postid' => $post->id, 'conceptid' => $conceptresult->id));
        $this->assertEquals($conceptresult->id, $forumresult->conceptid);
        $this->assertEquals($post->id, $postresult->postid);
        $this->assertEquals(1, $postresult->count);

    }
}
