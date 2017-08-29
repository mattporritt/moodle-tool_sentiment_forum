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

/**
 * Inject the competencies elements into all moodle module settings forms.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function tool_sentiment_forum_coursemodule_standard_elements($formwrapper, $mform) {
    $module = $formwrapper->get_current()->modulename;

    if ($module == 'forum') { // Only apply sentiment settings to forums.
        $mform->addElement('header', 'sentimentsection',
                get_string('sentimentsection', 'tool_sentiment_forum'));

        $mform->addElement('advcheckbox', 'sentimentenabled',
                get_string('sentimentenabled', 'tool_sentiment_forum'),
                get_string('sentimentenabled_label', 'tool_sentiment_forum'),
                false, array(0, 1));
    }

}
