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
 * Task to perform sentiment analysis on forum posts.
 *
 * @package     tool_sentiment_forum
 * @category    task
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_sentiment_forum\task;

defined('MOODLE_INTERNAL') || die();

use tool_sentiment_forum\analyze\analyze;

/**
 * Class to perform sentiment analysis on forum posts.
 *
 * @package     tool_sentiment_forum
 * @category    task
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class analyze_posts extends \core\task\scheduled_task {

    /**
     * {@inheritDoc}
     * @see \core\task\scheduled_task::get_name()
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('analyzeposts', 'tool_sentiment_forum');
    }

    /**
     *
     * {@inheritDoc}
     * @see \core\task\task_base::execute()
     */
    public function execute() {
        $analyzer = new analyze();

        mtrace('Getting Forums to perform sentinment analysis on...');
        $forums = $analyzer->get_enabled_forums();

        foreach ($forums as $forum) {
            $forumid = $forum->forumid;
            mtrace('Analyzing forum ID: ' . $forumid . '...');
            $analyzer->analyze_forum($forumid);

        }



    }
}
