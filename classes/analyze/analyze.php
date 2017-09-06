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
 * Forum sentiment analyzer class.
 *
 * @package     tool_sentiment_forum
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_sentiment_forum\analyze;

defined('MOODLE_INTERNAL') || die();

use tool_sentiment_forum\watson\watson_api;

/**
 * Forum sentiment analyzer class.
 *
 * @package     tool_sentiment_forum
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class analyze {

    /**
     * Constructor for sentiment analyzer.
     * Makes relevant config available.
     *
     * @return void
     */
    public function __construct() {
        $this->config = get_config('tool_sentiment_forum');
    }

    /**
     * Get all forums that have sentiment analysis enabled.
     *
     * @return array $forums list of enabled forums
     */
    public function get_enabled_forums() {
        global $DB;

        $forums = $DB->get_records('sentiment_forum', array('enabled'=>'1'));

        return $forums;
    }

    /**
     * Given a forum ID get a list of the posts
     * that require sentiment analysis.
     *
     * @param int $forumid The id of the forum.
     * @return array $posts array of post ids.
     */
    public function get_unanalyzed_posts($forumid) {
        global $DB;

        $posts = $DB->get_records_sql(
                'SELECT f.* FROM {forum_posts} f
                LEFT JOIN {forum_discussions} fd
                ON f.discussion = fd.id
                LEFT JOIN mdl_sentiment_forum_posts sfp
                ON sfp.postid = f.id
                WHERE sfp.timemodified is null AND fd.forum = ?',
                array($forumid)
                );

        return $posts;
    }

    /**
     * Given a form id perform sentiment analysis
     * on all posts in that forum.
     *
     * @param int $forumid the forum to analyze.
     */
    public function analyze_forum($forumid){
        $posts = $this->get_unanalyzed_posts($forumid);
        $watson = new watson_api();

        foreach ($posts as $post) {
            // Get text from forum post.
            $subject = format_string($post->subject, true);
            $message = format_string($post->message, true);
            $analyzestring = $subject . ' ' . $message;

            // Analyze string.
            $sentiment = $watson->analyze_sentiment($analyzestring);
        }


        return false;
    }
}