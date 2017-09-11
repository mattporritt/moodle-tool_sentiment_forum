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
     * @param int|bool $courseid If supplied limit to this course ID.
     * @return array $forums list of enabled forums
     */
    public function get_enabled_forums($courseid=false) {
        global $DB;

        if ($courseid) {
            $course = 'AND f.course = ?';
            $params = array('1', $courseid);
        } else {
            $course = '';
            $params = array('1');
        }

        $forums = $DB->get_records_sql(
                'SELECT sf.*, f.course, f.type, f.name
                FROM {sentiment_forum} sf
                LEFT JOIN {forum} f
                on sf.forumid = f.id
                WHERE sf.enabled = ? ' . $course,
                $params
                );

        return $forums;
    }

    /**
     * Given a forum ID return the overall forum
     * sentiment as a percentage.
     *
     * @param int $forumid Forum ID number.
     * @return number $sentiment Sentiment as a percentage.
     */
    public function get_forum_sentiment($forumid) {
        global $DB;

        $rawsentiment = $DB->get_field('sentiment_forum', 'sentiment', array('forumid' => $forumid));
        $sentiment = $rawsentiment * 100;

        return $sentiment;
    }

    /**
     * Given a forum ID return the overall forum
     * emtotions as a percentages.
     *
     * @param int $forumid Forum ID number.
     * @return array $emotions Emotions as percentages.
     */
    public function get_forum_emotions($forumid) {
        global $DB;

        $emotionsrecord = $DB->get_record('sentiment_forum', array('forumid' => $forumid));
        $emotions = array(
                'sadness' => ($emotionsrecord->sadness * 100),
                'joy' => ($emotionsrecord->joy * 100),
                'fear' => ($emotionsrecord->fear * 100),
                'disgust' => ($emotionsrecord->disgust * 100),
                'anger' => ($emotionsrecord->anger * 100),
        );

        return $emotions;
    }

    public function get_forum_emotion_trend($forumid) {
        global $DB;

        $emotionrecords = $DB->get_records_select(
                'sentiment_forum_posts',
                'sadness <> 0 AND joy <> 0 AND fear <> 0 AND disgust <> 0 AND anger <> 0 AND forumid = :forumid',
                array('forumid' => $forumid),
                'timeposted ASC'
                );

        return $emotionrecords;

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
                WHERE sfp.timeposted is null AND fd.forum = ?',
                array($forumid)
                );

        return $posts;
    }

    /**
     * Store individual forum post sentiment in Moodle database
     *
     * @param int $forumid The forum ID.
     * @param object $post The post ID.
     * @param int $sentiment The sentiment value
     * @param array $emotion Array of emotions.
     * @return boolean $result true on record insert
     */
    public function insert_sentiment_post($forumid, $post, $sentiment, $emotion) {
        global $DB;

        $record = new \stdClass();
        $record->forumid = $forumid;
        $record->postid = $post->id;
        $record->sentiment = $sentiment;
        $record->sadness = $emotion['sadness'];
        $record->joy = $emotion['joy'];
        $record->fear = $emotion['fear'];
        $record->anger = $emotion['anger'];
        $record->disgust = $emotion['disgust'];
        $record->timeposted = $post->created;

        $result = $DB->insert_record('sentiment_forum_posts', $record);

        return $result;
    }

    /**
     * Given a forum ID, update the running sentiment average
     * for that forum.
     *
     * @param int $forumid The ID of the forum to update
     * @return boolean $forum Success status of update.
     */
    public function update_sentiment_forum($forumid) {
        global $DB;

        $count = 0;
        $start = 0;
        $limit = 1000;
        $step = 1000;
        $forum = false;

        $totalsentiment = 0;
        $totalsadness = 0;
        $totaljoy = 0;
        $totalfear = 0;
        $totalanger = 0;
        $totaldisgust = 0;

        // Get 1000 rows of data from the log table order by oldest first.
        // Keep getting records 1000 at a time until we run out of records or max execution time is reached.
        while (true){
            $results = $DB->get_records('sentiment_forum_posts', array('forumid' => $forumid), '', '*', $start, $limit);

            if (empty($results)) {
                break; // Stop trying to get records when we run out;
            }

            // Increment record start position for next iteration.
            $start += $step;

            // Update running totals.
            foreach ($results as $result) {
                $count++;
                $totalsentiment += $result->sentiment;
                $totalsadness += $result->sadness;
                $totaljoy += $result->joy;
                $totalfear += $result->fear;
                $totalanger += $result->anger;
                $totaldisgust += $result->disgust;
            }
        }

        // If we have processed posts, update parent with averages.
        if ($count > 0) {
            $avgsenitment = $totalsentiment / $count;
            $avgsadness = $totalsadness / $count;
            $avgjoy = $totaljoy / $count;
            $avgfear = $totalfear / $count;
            $avganger = $totalanger / $count;
            $avgdisgust = $totaldisgust / $count;

            $tableid = $DB->get_field('sentiment_forum', 'id', array('forumid' => $forumid));
            $record = new \stdClass();
            $record->id = $tableid;
            $record->sentiment = $avgsenitment;
            $record->sadness = $avgsadness;
            $record->joy = $avgjoy;
            $record->fear = $avgfear;
            $record->anger = $avganger;
            $record->disgust = $avgdisgust;
            $record->timemodified = time();

            $forum = $DB->update_record('sentiment_forum', $record);
        }

        return $forum;
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
            list($sentiment, $emotion) = $watson->analyze_sentiment($analyzestring);

            // Update Database with post sentiment.
            $this->insert_sentiment_post($forumid, $post, $sentiment, $emotion);
        }

        // If new posts have been analyzed update forum sentiment.
        if (!empty($posts)) {
            $this->update_sentiment_forum($forumid);
        }

        return true;
    }
}