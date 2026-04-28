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

    // Only show the report link if the user has permission to access the grades.
    $targetuserid = $user->id ?? $USER->id;
    if (report_studentgrades_can_access_user($targetuserid)) {
        $url = new moodle_url('/report/studentgrades/index.php', ['userid' => $user->id ?? $USER->id]);
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

/**
 * Checks if the current user has access to view the grades of another user.
 * 
 * @param int $userid The user ID whose grades are being accessed.
 * @param int|null $current_userid The user ID attempting access (defaults to $USER->id).
 * @return bool True if access is allowed, false otherwise.
 */
function report_studentgrades_can_access_user($userid, $current_userid = null) {
    global $USER, $DB;
    
    if (empty($current_userid)) {
        $current_userid = $USER->id;
    }
    
    // 1. User can access their own data.
    if ($userid == $current_userid) {
        return true;
    }
    
    // 2. Admin/Manager with viewall capability.
    if (has_capability('report/studentgrades:viewall', context_system::instance())) {
        return true;
    }
    
    // 3. Check if the current user is a linked parent via local_parentportal.
    if ($DB->get_manager()->table_exists('local_parentportal_children')) {
        $isparent = $DB->record_exists('local_parentportal_children', [
            'parentid' => $current_userid,
            'childid' => $userid
        ]);
        if ($isparent) {
            return true;
        }
    }
    
    // 4. Fallback: Check if they have the view capability in the target user context.
    $usercontext = context_user::instance($userid);
    if (has_capability('report/studentgrades:view', $usercontext)) {
        return true;
    }
    
    return false;
}
