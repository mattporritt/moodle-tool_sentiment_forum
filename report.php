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

$contextid = required_param('contextid', PARAM_INT);
$context = context::instance_by_id($contextid, MUST_EXIST);

$PAGE->set_context($context);
$PAGE->set_url('/admin/tool/sentiment_forum/report.php', array('contextid' => $context->id));
$PAGE->set_title(get_string('pluginname', 'tool_sentiment_forum'));
$PAGE->set_heading(get_string('pluginname', 'tool_sentiment_forum'));

require_login();

$tabs = new \stdClass();
$tab1 = new \stdClass();
$tab1->name = 'tab1';
$tab1->displayname = 'Tab 1';
$tab1->active = 1;
$tab1->html = "content 1";

$tab2 = new \stdClass();
$tab2->name = 'tab2';
$tab2->displayname = 'Tab 2';
$tab2->active = 0;
$tab2->html = "content 2";

$tab3 = new \stdClass();
$tab3->name = 'tab3';
$tab3->displayname = 'Tab 3';
$tab3->active = 0;
$tab3->html = "content 3";


$tabs->tabs = array($tab1, $tab2, $tab3);


echo $OUTPUT->header();
echo $OUTPUT->render_from_template('tool_sentiment_forum/tabs', $tabs);
echo $OUTPUT->footer();
