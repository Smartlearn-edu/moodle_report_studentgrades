<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Extend user navigation for the Student Grades report.
 *
 * @param global_navigation $navigation
 * @param stdClass $user
 * @param context $context
 * @param stdClass|null $course
 * @param context|null $coursecontext
 * @return void
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

