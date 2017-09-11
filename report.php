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
 * Sentiment analysis report page.
 *
 * @package     tool_sentiment_forum
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

defined('MOODLE_INTERNAL') || die();

use tool_sentiment_forum\analyze\analyze;

$contextid = required_param('contextid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$context = context::instance_by_id($contextid, MUST_EXIST);


$PAGE->set_context($context);
$PAGE->set_url('/admin/tool/sentiment_forum/report.php', array('contextid' => $context->id));
$PAGE->set_title(get_string('pluginname', 'tool_sentiment_forum'));
$PAGE->set_heading(get_string('pluginname', 'tool_sentiment_forum'));

require_login();

$analyzer = new analyze();
$forums = $analyzer->get_enabled_forums($courseid);
$tabs = new \stdClass();
$tabs->tabs = array();
$count = 1;

foreach ($forums as $forum) {
    $tab = new \stdClass();
    $tab->name = 'forum_tab_' . $count;
    $tab->displayname = $forum->name;
    if ($count == 1){
        $tab->active = 1;
    } else {
        $tab->active = 0;
    }
    $tab->html = "content 1";

    $tabs->tabs[] = $tab;
    $count++;
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('tool_sentiment_forum/tabs', $tabs);
echo $OUTPUT->footer();
