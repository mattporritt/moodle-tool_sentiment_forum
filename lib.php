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
 * Tool functions defined here.
 *
 * @package     tool_sentiment_forum
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_sentiment_forum\analyze\analyze;

/**
 * Inject the sentiment analysis elements into all moodle forum settings forms.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function tool_sentiment_forum_coursemodule_standard_elements($formwrapper, $mform) {
    global $DB;
    $module = $formwrapper->get_current()->modulename;

    if ($module == 'forum') { // Only apply sentiment settings to forums.
        // Get existing config.
        $forumid = $formwrapper->get_current()->id;

        if ($forumid != '') {
            $enabled = $DB->get_field('tool_sentiment_forum', 'enabled', array ('forumid' => $forumid));
        } else {
            $enabled = 0;
        }

        $mform->addElement('header', 'sentimentsection',
                get_string('sentimentsection', 'tool_sentiment_forum'));

        $mform->addElement('advcheckbox', 'sentimentenabled',
                get_string('sentimentenabled', 'tool_sentiment_forum'),
                get_string('sentimentenabled_label', 'tool_sentiment_forum'),
                false, array(0, 1));
        $mform->setDefault('sentimentenabled', $enabled);

    }

}

/**
 * Hook the add/edit of the course module.
 *
 * @param stdClass $data Data from the form submission.
 * @param stdClass $course The course.
 */
function tool_sentiment_forum_coursemodule_edit_post_actions($data, $course) {
    $module = $data->modulename;

    if ($module == 'forum') { // Only apply sentiment settings to forums.
        // save sentiment analysis in DB.
        $record = new \stdClass();
        $record->forumid = $data->instance;
        $record->enabled = isset($data->sentimentenabled) ? $data->sentimentenabled : 0;
        $record->timemodified = time();

        sentiment_forum_upsert($record);

    }

    return $data;
}

/**
 * Hook the delete action of the course module.
 *
 * @param stdClass $cm
 * @return void
 */
function tool_sentiment_forum_pre_course_module_delete(stdClass $cm) {
    // TODO: Handle module deletion.
}

/**
 * Adds a sentiment forum report link to the course admin menu.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the tool
 * @param context $context The context of the course
 * @return void|null return null if we don't want to display the node.
 */
function tool_sentiment_forum_extend_navigation_course($navigation, $course, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course || $PAGE->course->id == SITEID) {
        return null;
    }

    // Only show this report if there are sentiment enabled forums in this course.
    // TODO: this.

    $url = new moodle_url('/admin/tool/sentiment_forum/report.php',
            array('contextid' => $context->id, 'courseid' => $course->id)
            );
    $pluginname = get_string('pluginname', 'tool_sentiment_forum');

    // TODO: add capability check.
    // Add the report link.
    $navigation->add($pluginname, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
}

/**
 * Provides upsert (insert and/or update) functionality
 * for records into the sentiment forum table.
 *
 * @param object $record record to update or insert.
 * @return void
 */
function sentiment_forum_upsert($record) {
    global $DB;
    $insert = true;

    // Try insert.
    try {
        $DB->insert_record('tool_sentiment_forum', $record, false);
    } catch (Exception $e) {
        $insert = false;
    }

    // Insert failed try update.
    if (!$insert) {
        $id = $DB->get_field('tool_sentiment_forum', 'id', array ('forumid' => $record->forumid));
        $record->id = $id;
        $DB->update_record('tool_sentiment_forum', $record);
    }
}

/**
 * Given a Forum ID, construct a bar chart
 * to display overall forum sentiment.
 *
 * @param int $forumid Forum ID
 * @return \core\chart_bar $chart The constructed chart object.
 */
function get_chart_forum_sentiment($forumid) {
    $analyzer = new analyze();
    $sentiment = $analyzer->get_forum_sentiment($forumid);

    $chart = new \core\chart_bar(); // Get a bar chart instance.

    // Setup chart series and labels.
    $series = new core\chart_series(
            get_string('chart_forum_sentimentrating', 'tool_sentiment_forum'),
            [$sentiment]
            );
    $labels = ['sentiment'];

    // Customise Y axis.
    $yaxis = new \core\chart_axis();
    $yaxis->set_min(-100);
    $yaxis->set_max(100);
    $yaxis->set_label(get_string('chart_forum_sentimentrating', 'tool_sentiment_forum'));

    // Setup chart.
    $chart->add_series($series);
    $chart->set_labels($labels);
    $chart->set_title(get_string('chart_forum_sentiment_title', 'tool_sentiment_forum'));
    $chart->set_yaxis($yaxis);

    return $chart;

}

/**
 * Given a Forum ID, construct a bar chart
 * to display overall forum emotions.
 *
 * @param int $forumid Forum ID
 * @return \core\chart_bar $chart The constructed chart object.
 */
function get_chart_forum_emotions($forumid) {
    $analyzer = new analyze();
    $emotions = $analyzer->get_forum_emotions($forumid);
    $seriesarray = array();
    $labelsarray = array();

    foreach ($emotions as $key => $value) {
        $seriesarray[] = $value;
        $labelsarray[] = $key;
    }

    $chart = new \core\chart_bar(); // Get a bar chart instance.

    // Setup chart series and labels.
    $series = new core\chart_series(
            get_string('chart_forum_emotionrating', 'tool_sentiment_forum'),
            $seriesarray
            );
    $labels = $labelsarray;

    // Customise Y axis.
    $yaxis = new \core\chart_axis();
    $yaxis->set_min(0);
    $yaxis->set_max(100);
    $yaxis->set_label(get_string('chart_forum_emotionrating', 'tool_sentiment_forum'));

    // Setup chart.
    $chart->add_series($series);
    $chart->set_labels($labels);
    $chart->set_title(get_string('chart_forum_emotion_title', 'tool_sentiment_forum'));
    $chart->set_yaxis($yaxis);

    return $chart;

}

/**
 * Given a Forum ID, construct a bar chart
 * to display overall forum sentiment.
 *
 * @param int $forumid Forum ID
 * @return \core\chart_bar $chart The constructed chart object.
 */
function get_chart_forum_emotion_trend($forumid) {
    global $CFG;

    $analyzer = new analyze();
    $emotionrecords = $analyzer->get_forum_emotion_trend($forumid);

    $chart = new \core\chart_line();
    $chart->set_smooth(true); // Calling set_smooth() passing true as parameter, will display smooth lines.

    // Split returned records up into weeks.
    $weektime = 0;
    $weekrecords = array();
    $avgarray = array();
    foreach ($emotionrecords as $record) {
        if ($record->timeposted > $weektime) {
            $weektime = $record->timeposted + 604800;
            $weekarray = array();
        }
        $weekarray[] = $record;
        $weekrecords[$weektime] = $weekarray;

    }

    // Average weekly record groups.
    $weekavg = array();
    foreach ($weekrecords as $time => $week) {

        $count = count($week);
        $sadnesstotal = 0;
        $joytotal = 0;
        $feartotal = 0;
        $disgusttotal = 0;
        $angertotal = 0;

        foreach ($week as $record) {
            $sadnesstotal += $record->sadness;
            $joytotal += $record->joy;
            $feartotal += $record->fear;
            $disgusttotal += $record->disgust;
            $angertotal += $record->anger;
        }

        $recordavg = new \stdClass();
        $recordavg->sadnessavg = ($sadnesstotal * 100) / $count;
        $recordavg->joyavg = ($joytotal * 100) / $count;
        $recordavg->fearavg = ($feartotal * 100) / $count;
        $recordavg->disgustavg = ($disgusttotal * 100) / $count;
        $recordavg->angeravg = ($angertotal * 100) / $count;

        $weekavg[$time] = $recordavg;
    }

    // Split out our averaged data in a chart format.
    $sadness = array();
    $joy = array();
    $fear = array();
    $disgust = array();
    $anger = array();
    $timeposted = array();

    foreach ($weekavg as $timestamp => $record) {
        $sadness[] = $record->sadnessavg;
        $joy[] = $record->joyavg;
        $fear[] = $record->fearavg;
        $disgust[] = $record->disgustavg;
        $anger[] = $record->angeravg;
        $timeposted[] = userdate($timestamp - DAYSECS, get_string('strftimedate'), $CFG->timezone);
    }

    // Setup chart series and labels.
    $sadnessseries = new core\chart_series(
            get_string('chart_forum_emotionsadness', 'tool_sentiment_forum'),
            $sadness
            );
    $joyseries = new core\chart_series(
            get_string('chart_forum_emotionjoy', 'tool_sentiment_forum'),
            $joy
            );
    $fearseries = new core\chart_series(
            get_string('chart_forum_emotionfear', 'tool_sentiment_forum'),
            $fear
            );
    $disgustseries = new core\chart_series(
            get_string('chart_forum_emotiondisgust', 'tool_sentiment_forum'),
            $disgust
            );
    $angerseries = new core\chart_series(
            get_string('chart_forum_emotionanger', 'tool_sentiment_forum'),
            $anger
            );

    $labels = $timeposted;

    // Customise Y axis.
    $yaxis = new \core\chart_axis();
    $yaxis->set_min(0);
    $yaxis->set_max(100);
    $yaxis->set_label(get_string('chart_forum_sentimentrating', 'tool_sentiment_forum'));

    // Setup chart.
    $chart->add_series($sadnessseries);
    $chart->add_series($joyseries);
    $chart->add_series($fearseries);
    $chart->add_series($disgustseries);
    $chart->add_series($angerseries);

    $chart->set_labels($labels);
    $chart->set_title(get_string('chart_forum_sentiment_title', 'tool_sentiment_forum'));
    $chart->set_yaxis($yaxis);

    return $chart;

}
