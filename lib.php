<?php
defined('MOODLE_INTERNAL') || die();

// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
=======
 *
 * @package     report_studentgrades
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function report_studentgrades_extend_navigation_user($navigation, $user, $context, $course = null, $coursecontext = null) {
    global $CFG, $USER;

    // Make sure we have a valid user context.
    if (!empty($user) && !empty($user->id)) {
        $usercontext = context_user::instance($user->id);
    } else {
        $usercontext = context_user::instance($USER->id);
    }

    // Only show the report link if the user has the required capability.
    if (has_capability('report/studentgrades:view', $usercontext)) {
        $url = new moodle_url('/report/studentgrades/index.php', ['id' => $user->id ?? $USER->id]);
        $navigation->add(
            get_string('pluginname', 'report_studentgrades'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            null,
            new pix_icon('i/report', '')
        );
    }
}
