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

require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');

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

        $posts = $DB->get_records_sql('SELECT * FROM {table} WHERE foo = ?', array('bar'));

//      select f.* from mdl_forum_posts f
//         left join mdl_forum_discussions fd
//         on f.discussion = fd.id
//         left join mdl_sentiment_forum_posts sfp
//         on sfp.postid = f.id
//         where sfp.timemodified is null and fd.forum = 4;

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
        return false;
    }
}