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
 * Plugin administration pages are defined here.
 *
 * @package     tool_sentiment_forum
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


global $PAGE;

if ($hassiteconfig) {
    $settings = new admin_settingpage('tool_sentiment_forum', get_string('pluginname', 'tool_sentiment_forum'));
    $ADMIN->add('tools', $settings);

    $settings->add(new admin_setting_heading(
            'tool_sentiment_forum_settings',
            '',
            get_string('pluginnamedesc', 'tool_sentiment_forum')
            ));

    if (! during_initial_install ()) {
        // General Settings.
        $settings->add(new admin_setting_configtext('tool_sentiment_forum/username',
                get_string('username', 'tool_sentiment_forum' ),
                get_string('username_desc', 'tool_sentiment_forum'),
                '', PARAM_TEXT));

        $settings->add(new admin_setting_configpasswordunmask('tool_sentiment_forum/password',
                get_string('password', 'tool_sentiment_forum' ),
                get_string('password_desc', 'tool_sentiment_forum'),
                ''));

        $settings->add(new admin_setting_configtext('tool_sentiment_forum/tokenendpoint',
                get_string('tokenendpoint',      'tool_sentiment_forum'),
                get_string('tokenendpoint_desc', 'tool_sentiment_forum'),
               'https://gateway.watsonplatform.net/authorization/api/v1/token', PARAM_URL));

        $settings->add(new admin_setting_configtext('tool_sentiment_forum/apiendpoint',
                get_string('apiendpoint',      'tool_sentiment_forum'),
                get_string('apiendpoint_desc', 'tool_sentiment_forum'),
                'https://gateway.watsonplatform.net/natural-language-understanding/api', PARAM_URL));

        $settings->add(new admin_setting_configtext('tool_sentiment_forum/maxkeywords',
                get_string('maxkeywords', 'search_elastic' ),
                get_string('maxkeywords_desc', 'search_elastic'),
                10, PARAM_INT, 2));

        $settings->add(new admin_setting_configtext('tool_sentiment_forum/maxconcepts',
                get_string('maxconcepts', 'tool_sentiment_forum' ),
                get_string('maxconcepts_desc', 'tool_sentiment_forum'),
                10, PARAM_INT, 2));

    }
}
