<?php
/**
 * Library functions for report_studentgrades plugin
 *
 * @package    report_studentgrades
 * @copyright  2025 onwards, Moodle Community
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Moodle Community
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Adds link to Student Grades report in user profile navigation
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $user The user object
 * @param context_user $usercontext The user context
 * @param stdClass $course The course object
 * @param context_course $coursecontext The course context
 */
function report_studentgrades_extend_navigation_user($navigation, $user, $usercontext, $course, $coursecontext) {
    global $USER;
    
    // Check if user can view this report
    if (has_capability('report/studentgrades:view', $usercontext) && 
        ($USER->id == $user->id || has_capability('report/studentgrades:viewall', context_system::instance()))) {
        
        $url = new moodle_url('/report/studentgrades/index.php', array('userid' => $user->id));
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
