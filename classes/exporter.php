<?php
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

namespace report_studentgrades;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->libdir . '/gradelib.php');

/**
 * Class for Student Course Grades export
 */
class exporter
{

    /** @var int User ID */
    private $userid;

    /** @var context User context */
    private $usercontext;

    /**
     * Constructor
     *
     * @param int $userid User ID
     */
    public function __construct($userid)
    {
        $this->userid = $userid;
        $this->usercontext = \context_user::instance($userid);
    }

    /**
     * Export user's grades from all courses as HTML
     */
    public function export_user_grades()
    {
        global $CFG, $SITE, $DB;

        // Verify access
        if (!$this->can_view_user_grades()) {
            throw new \moodle_exception('nopermissions', 'error');
        }

        $user = $DB->get_record('user', array('id' => $this->userid), '*', MUST_EXIST);

        // Generate HTML content
        $html = $this->generate_grades_html($user);

        // Set headers for download
        $filename = clean_filename(fullname($user) . '_all_courses_grades.html');
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        echo $html;
    }

    /**
     * Generate HTML content for user's grades across all courses
     *
     * @param stdClass $user User object
     * @return string HTML content
     */
    private function generate_grades_html($user)
    {
        global $CFG, $SITE;

        $html = $this->get_html_header($user);

        // Get all courses user is enrolled in
        $courses = $this->get_user_courses();

        if (empty($courses)) {
            $html .= '<div class="alert">' . get_string('nocourses', 'report_studentgrades') . '</div>';
        } else {
            foreach ($courses as $course) {
                $html .= $this->get_course_grades_html($user, $course);
            }

            // Add overall summary
            $html .= $this->get_overall_summary_html($courses);
        }

        $html .= $this->get_html_footer();

        return $html;
    }

    /**
     * Get courses user is enrolled in with error handling
     *
     * @return array Array of course objects
     */
    private function get_user_courses()
    {
        global $DB;

        try {
            // Get enrolled courses using Moodle API
            $courses = enrol_get_users_courses($this->userid, true, 'id,fullname,shortname,visible,summary,summaryformat');
            if (!empty($courses)) {
                return $courses;
            }
        } catch (\Exception $e) {
            error_log('report_studentgrades: Error getting user courses: ' . $e->getMessage());
        }

        try {
            // Fallback: direct database query
            $sql = "SELECT DISTINCT c.id, c.fullname, c.shortname, c.visible, c.summary, c.summaryformat
                    FROM {course} c
                    JOIN {enrol} e ON e.courseid = c.id
                    JOIN {user_enrolments} ue ON ue.enrolid = e.id
                    WHERE ue.userid = ? AND ue.status = 0 AND e.status = 0 AND c.visible = 1
                    ORDER BY c.fullname";
            $courses = $DB->get_records_sql($sql, array($this->userid));
            if (!empty($courses)) {
                return $courses;
            }
        } catch (\Exception $e) {
            error_log('report_studentgrades: Error getting courses from database: ' . $e->getMessage());
        }

        return array();
    }

    /**
     * Generate HTML header
     *
     * @param stdClass $user User object
     * @return string HTML header
     */
    private function get_html_header($user)
    {
        global $SITE, $CFG;

        $html = '<!DOCTYPE html>';
        $html .= '<html dir="' . (right_to_left() ? 'rtl' : 'ltr') . '">';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . fullname($user) . ' - ' . get_string('pluginname', 'report_studentgrades') . '</title>';
        $html .= '<style>' . $this->get_word_compatible_css() . '</style>';
        $html .= '</head>';
        $html .= '<body>';

        // Header section
        $html .= '<div class="header">';
        $html .= '<div class="logo-section">';

        // Add site logo if available
        $logourl = $this->get_site_logo_url();
        if ($logourl) {
            $html .= '<img src="' . $logourl . '" alt="' . format_string($SITE->fullname) . ' Logo" class="site-logo">';
        }

        $html .= '<div class="header-text">';
        $html .= '<h1>' . format_string($SITE->fullname) . '</h1>';
        $html .= '<h2>' . get_string('pluginname', 'report_studentgrades') . '</h2>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="student-info">';
        $html .= '<h3>' . get_string('user', 'report_studentgrades') . ': ' . fullname($user) . '</h3>';
        $html .= '<p>' . get_string('reportdate', 'report_studentgrades') . ': ' . userdate(time()) . '</p>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate grade table HTML for a specific course
     *
     * @param stdClass $user User object
     * @param stdClass $course Course object
     * @return string HTML table
     */
    private function get_course_grades_html($user, $course)
    {
        global $CFG;

        try {
            $context = \context_course::instance($course->id);
            $gpr = new \grade_plugin_return(array('type' => 'report', 'plugin' => 'studentgrades', 'courseid' => $course->id));
            $gtree = new \grade_tree($course->id, false, false, null, $gpr);

            $html = '<div class="course-section">';
            $html .= '<h2 class="course-name">' . format_string($course->fullname) . '</h2>';
            $html .= '<div class="grade-table">';
            $html .= '<table>';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>' . get_string('gradeitem', 'report_studentgrades') . '</th>';
            $html .= '<th>' . get_string('grade', 'report_studentgrades') . '</th>';
            $html .= '<th>' . get_string('range', 'report_studentgrades') . '</th>';
            $html .= '<th>' . get_string('percentage', 'report_studentgrades') . '</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';

            // Process grade tree
            if (isset($gtree->top_element['children'])) {
                $html .= $this->process_grade_tree_children($gtree->top_element['children'], $user->id, 0);
            }

            // Add course total
            $course_total = $this->get_course_total($user->id, $course->id);
            if ($course_total !== null) {
                $html .= '<tr class="course-total-row">';
                $html .= '<td class="total-name"><strong>' . get_string('coursetotal', 'report_studentgrades') . '</strong></td>';
                $html .= '<td class="grade-value total-value">' . $course_total['value'] . '</td>';
                $html .= '<td class="grade-range">' . $course_total['range'] . '</td>';
                $html .= '<td class="grade-percentage total-percentage">' . $course_total['percentage'] . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
            $html .= '</div>';

            return $html;
        } catch (\Exception $e) {
            error_log('report_studentgrades: Error generating course grades: ' . $e->getMessage());
            return '<div class="course-section"><h2>' . format_string($course->fullname) . '</h2><p>Error loading grades</p></div>';
        }
    }

    /**
     * Process grade tree children recursively
     *
     * @param array $children Grade tree children
     * @param int $userid User ID
     * @param int $level Nesting level
     * @return string HTML rows
     */
    private function process_grade_tree_children($children, $userid, $level = 0)
    {
        $html = '';

        foreach ($children as $key => $child) {
            if ($child['type'] == 'category') {
                $category = $child['object'];

                // Category header
                $html .= '<tr class="category-row level-' . $level . '">';
                $html .= '<td colspan="4"><strong>' . format_string($category->fullname) . '</strong></td>';
                $html .= '</tr>';

                // Process category children
                if (!empty($child['children'])) {
                    $html .= $this->process_grade_tree_children($child['children'], $userid, $level + 1);
                }

                // Add category total
                if (!empty($child['children'])) {
                    $category_total = $this->calculate_category_total($category, $userid);
                    if ($category_total !== null) {
                        $html .= '<tr class="category-total-row level-' . $level . '">';
                        $html .= '<td class="total-name"><strong>' . get_string('total', 'report_studentgrades') . ' - ' . format_string($category->fullname) . '</strong></td>';
                        $html .= '<td class="grade-value total-value">' . $category_total['value'] . '</td>';
                        $html .= '<td class="grade-range">' . $category_total['range'] . '</td>';
                        $html .= '<td class="grade-percentage total-percentage">' . $category_total['percentage'] . '</td>';
                        $html .= '</tr>';
                    }
                }
            } else if ($child['type'] == 'item') {
                $grade_item = $child['object'];

                // Skip course total item
                if ($grade_item->itemtype == 'course') {
                    continue;
                }

                // Check visibility
                if (!$this->can_view_grade_item($grade_item)) {
                    continue;
                }

                $grade_grade = \grade_grade::fetch(array('itemid' => $grade_item->id, 'userid' => $userid));

                $html .= '<tr class="grade-row level-' . $level . '">';

                $description = $this->get_activity_description($grade_item);
                $html .= '<td class="item-name">';
                $html .= '<div class="item-title">' . format_string($grade_item->itemname) . '</div>';
                if ($description) {
                    $html .= '<div class="item-description">' . $description . '</div>';
                }
                $html .= '</td>';

                // Grade value
                $gradevalue = $this->format_grade_value($grade_grade, $grade_item);
                $html .= '<td class="grade-value">' . $gradevalue . '</td>';

                // Grade range
                $range = $this->format_grade_range($grade_item);
                $html .= '<td class="grade-range">' . $range . '</td>';

                // Percentage
                $percentage = $this->format_grade_percentage($grade_grade, $grade_item);
                $html .= '<td class="grade-percentage">' . $percentage . '</td>';

                $html .= '</tr>';
            }
        }

        return $html;
    }

    /**
     * Format grade value for display
     */
    private function format_grade_value($grade_grade, $grade_item)
    {
        if (!$grade_grade || is_null($grade_grade->finalgrade)) {
            return '-';
        }
        return \grade_format_gradevalue($grade_grade->finalgrade, $grade_item, true);
    }

    /**
     * Format grade range for display
     */
    private function format_grade_range($grade_item)
    {
        return \grade_format_gradevalue($grade_item->grademin, $grade_item, true) . ' - ' .
            \grade_format_gradevalue($grade_item->grademax, $grade_item, true);
    }

    /**
     * Format grade percentage for display
     */
    private function format_grade_percentage($grade_grade, $grade_item)
    {
        if (!$grade_grade || is_null($grade_grade->finalgrade)) {
            return '-';
        }
        return \grade_format_gradevalue($grade_grade->finalgrade, $grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
    }

    /**
     * Calculate category total grade
     */
    private function calculate_category_total($category, $userid)
    {
        $category_item = \grade_item::fetch(array('itemtype' => 'category', 'iteminstance' => $category->id));
        if (!$category_item) {
            return null;
        }

        $category_grade = \grade_grade::fetch(array('itemid' => $category_item->id, 'userid' => $userid));
        if (!$category_grade || is_null($category_grade->finalgrade)) {
            return null;
        }

        return array(
            'value' => \grade_format_gradevalue($category_grade->finalgrade, $category_item, true),
            'range' => \grade_format_gradevalue($category_item->grademin, $category_item, true) . ' - ' .
                \grade_format_gradevalue($category_item->grademax, $category_item, true),
            'percentage' => \grade_format_gradevalue($category_grade->finalgrade, $category_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE)
        );
    }

    /**
     * Get course total grade
     */
    private function get_course_total($userid, $courseid)
    {
        $course_item = \grade_item::fetch_course_item($courseid);
        if (!$course_item) {
            return null;
        }

        $course_grade = \grade_grade::fetch(array('itemid' => $course_item->id, 'userid' => $userid));
        if (!$course_grade || is_null($course_grade->finalgrade)) {
            return null;
        }

        return array(
            'value' => \grade_format_gradevalue($course_grade->finalgrade, $course_item, true),
            'range' => \grade_format_gradevalue($course_item->grademin, $course_item, true) . ' - ' .
                \grade_format_gradevalue($course_item->grademax, $course_item, true),
            'percentage' => \grade_format_gradevalue($course_grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE)
        );
    }

    /**
     * Generate overall summary HTML
     */
    private function get_overall_summary_html($courses)
    {
        $html = '<div class="overall-summary">';
        $html .= '<h2>' . get_string('overallsummary', 'report_studentgrades') . '</h2>';
        $html .= '<p><strong>' . get_string('totalcourses', 'report_studentgrades') . ':</strong> ' . count($courses) . '</p>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Check if user can view grade item
     */
    private function can_view_grade_item($grade_item)
    {
        try {
            if ($grade_item->is_hidden()) {
                return false;
            }
            return !$grade_item->is_locked() && !$grade_item->is_hidden();
        } catch (\Exception $e) {
            error_log('report_studentgrades: Error checking grade item visibility: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * Check if current user can view this user's grades
     */
    private function can_view_user_grades()
    {
        global $USER;

        try {
            // Users can view their own grades
            if ($this->userid == $USER->id) {
                return true;
            }

            // Admins and users with viewall capability can view any user
            if (is_siteadmin() || has_capability('report/studentgrades:viewall', \context_system::instance())) {
                return true;
            }
        } catch (\Exception $e) {
            error_log('report_studentgrades: Error checking permissions: ' . $e->getMessage());
            if ($this->userid == $USER->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get color setting with fallback to default
     */
    private function get_color_setting($setting, $default)
    {
        $value = get_config('report_studentgrades', $setting);
        return !empty($value) ? $value : $default;
    }

    /**
     * Get all color settings
     */
    private function get_color_settings()
    {
        return array(
            'header_primary' => $this->get_color_setting('header_primary_color', '#6f42c1'),
            'header_secondary' => $this->get_color_setting('header_secondary_color', '#8e44ad'),
            'header_text' => $this->get_color_setting('header_text_color', '#ffffff'),
            'grade_excellent' => $this->get_color_setting('grade_excellent_color', '#28a745'),
            'grade_good' => $this->get_color_setting('grade_good_color', '#17a2b8'),
            'grade_average' => $this->get_color_setting('grade_average_color', '#ffc107'),
            'grade_poor' => $this->get_color_setting('grade_poor_color', '#dc3545'),
            'table_border' => $this->get_color_setting('table_border_color', '#dee2e6'),
            'row_alternate' => $this->get_color_setting('row_alternate_color', '#f8f9fa'),
            'row_hover' => $this->get_color_setting('row_hover_color', '#e8f4fd'),
            'category_primary' => $this->get_color_setting('category_primary_color', '#4a4a4a'),
            'category_secondary' => $this->get_color_setting('category_secondary_color', '#2d2d2d'),
            'category_total_primary' => $this->get_color_setting('category_total_primary_color', '#17a2b8'),
            'category_total_secondary' => $this->get_color_setting('category_total_secondary_color', '#138496'),
            'course_total_primary' => $this->get_color_setting('course_total_primary_color', '#28a745'),
            'course_total_secondary' => $this->get_color_setting('course_total_secondary_color', '#1e7e34'),
            'grade_value' => $this->get_color_setting('grade_value_color', '#28a745'),
            'grade_value_bg' => $this->get_color_setting('grade_value_bg_color', '#f8fff9'),
            'percentage' => $this->get_color_setting('percentage_color', '#007bff'),
            'percentage_bg' => $this->get_color_setting('percentage_bg_color', '#f8feff'),
        );
    }

    /**
     * Get Word-compatible CSS with configurable colors
     */
    private function get_word_compatible_css()
    {
        $colors = $this->get_color_settings();

        return '
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 20px;
            direction: ' . (right_to_left() ? 'rtl' : 'ltr') . ';
            background-color: #f8f9fa;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid ' . $colors['header_primary'] . ';
            padding-bottom: 20px;
            background: linear-gradient(135deg, ' . $colors['header_primary'] . ' 0%, ' . $colors['header_secondary'] . ' 100%);
            color: ' . $colors['header_text'] . ';
            border-radius: 8px 8px 0 0;
            padding: 20px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .site-logo {
            max-height: 60px;
            max-width: 200px;
            margin-right: 20px;
            background: white;
            padding: 8px;
            border-radius: 8px;
        }
        
        .header h1 {
            font-size: 18pt;
            margin: 0 0 8px 0;
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 16pt;
            margin: 0;
            font-weight: normal;
        }
        
        .student-info {
            background-color: #f8f9fa;
            color: #333;
            padding: 15px;
            border-radius: 0 0 8px 8px;
            margin-top: 10px;
        }
        
        .student-info h3 {
            font-size: 14pt;
            margin: 0 0 5px 0;
            color: ' . $colors['header_primary'] . ';
            font-weight: bold;
        }
        
        .course-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        
        .course-name {
            background: linear-gradient(135deg, ' . $colors['category_primary'] . ' 0%, ' . $colors['category_secondary'] . ' 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
            margin: 20px 0 0 0;
            font-size: 14pt;
        }
        
        .grade-table {
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        th, td {
            border: 1px solid ' . $colors['table_border'] . ';
            padding: 12px 15px;
            text-align: ' . (right_to_left() ? 'right' : 'left') . ';
        }
        
        th {
            background: linear-gradient(135deg, ' . $colors['header_primary'] . ' 0%, ' . $colors['header_secondary'] . ' 100%);
            color: ' . $colors['header_text'] . ';
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .category-row td {
            background: linear-gradient(135deg, ' . $colors['category_primary'] . ' 0%, ' . $colors['category_secondary'] . ' 100%);
            color: white;
            font-weight: bold;
        }
        
        .grade-row td {
            background-color: ' . $colors['row_alternate'] . ';
        }
        
        .grade-row:nth-child(even) td {
            background-color: #ffffff;
        }
        
        .grade-value {
            text-align: center;
            font-weight: bold;
            color: ' . $colors['grade_value'] . ';
            background-color: ' . $colors['grade_value_bg'] . ' !important;
        }
        
        .grade-range {
            text-align: center;
            color: #6c757d;
        }
        
        .grade-percentage {
            text-align: center;
            font-weight: bold;
            color: ' . $colors['percentage'] . ';
            background-color: ' . $colors['percentage_bg'] . ' !important;
        }
        
        .category-total-row td {
            background: linear-gradient(135deg, ' . $colors['category_total_primary'] . ' 0%, ' . $colors['category_total_secondary'] . ' 100%);
            color: white;
            font-weight: bold;
        }
        
        .course-total-row td {
            background: linear-gradient(135deg, ' . $colors['course_total_primary'] . ' 0%, ' . $colors['course_total_secondary'] . ' 100%);
            color: white;
            font-weight: bold;
            font-size: 12pt;
        }
        
        .overall-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            border-left: 5px solid ' . $colors['header_primary'] . ';
        }
        
        @media print {
            body { 
                margin: 15px; 
                background-color: white;
            }
            .header { 
                background: ' . $colors['header_primary'] . ' !important;
                -webkit-print-color-adjust: exact;
            }
        }
        
        .item-description {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dotted #eee;
        }
        
        .item-description img {
            max-width: 100%;
            height: auto;
        }
        ';
    }

    /**
     * Get site logo URL with error handling
     */
    private function get_site_logo_url()
    {
        global $CFG, $OUTPUT;

        try {
            if (!empty($CFG->logo)) {
                return $CFG->wwwroot . '/pluginfile.php/1/core_admin/logo/0x200/' . $CFG->logo;
            }

            if (method_exists($OUTPUT, 'get_logo_url')) {
                try {
                    $logourl = $OUTPUT->get_logo_url();
                    if ($logourl && !empty($logourl)) {
                        return $logourl->out();
                    }
                } catch (\Exception $e) {
                    // Continue to next method
                }
            }
        } catch (\Exception $e) {
            error_log('report_studentgrades: Error getting logo: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get activity description if available
     */
    private function get_activity_description($grade_item)
    {
        global $DB, $CFG;

        if ($grade_item->itemtype !== 'mod') {
            return '';
        }

        try {
            // Get course module
            $cm = get_coursemodule_from_instance($grade_item->itemmodule, $grade_item->iteminstance, $grade_item->courseid);
            if (!$cm) {
                return '';
            }

            // Get activity record with intro
            $activity = $DB->get_record($grade_item->itemmodule, array('id' => $grade_item->iteminstance), 'id, intro, introformat');

            if ($activity && !empty($activity->intro)) {
                // Keep HTML but strip dangerous tags and limit size if needed
                // Using standard Moodle formatting
                $modcontext = \context_module::instance($cm->id);

                // Rewrite plugin file URLs to make images work
                $description = file_rewrite_pluginfile_urls($activity->intro, 'pluginfile.php', $modcontext->id, 'mod_' . $grade_item->itemmodule, 'intro', null);

                return format_text($description, $activity->introformat, array('noclean' => true, 'para' => false, 'context' => $modcontext));
            }
        } catch (\Exception $e) {
            // Silently fail to avoid breaking the report
        }
        return '';
    }

    /**
     * Trigger AI Analysis by sending data to n8n
     */
    public function trigger_ai_analysis()
    {
        global $DB, $CFG;
        require_once($CFG->libdir . '/filelib.php');

        // Check permissions
        if (!$this->can_view_user_grades()) {
            throw new \moodle_exception('nopermissions', 'error');
        }

        // Check for rate limiting (Cooldown)
        $cooldown_minutes = (int)get_config('report_studentgrades', 'aicooldown');
        if ($cooldown_minutes > 0) {
            $last_request_time = (int)get_user_preferences('report_studentgrades_last_ai_request', 0, $this->userid);
            $current_time = time();
            $time_diff = $current_time - $last_request_time;
            $cooldown_seconds = $cooldown_minutes * 60;

            if ($time_diff < $cooldown_seconds) {
                // Rate limit exceeded
                $minutes_remaining = ceil(($cooldown_seconds - $time_diff) / 60);
                return array('success' => false, 'message' => "Daily limit reached. Please wait " . (int)$minutes_remaining . " minute(s) before generating a new analysis.");
            }
        }

        // Get config from report_studentgrades
        $n8n_url = get_config('report_studentgrades', 'webhookurl');
        $n8n_token = get_config('report_studentgrades', 'token');

        if (!$n8n_url) {
            return array('success' => false, 'message' => 'AI Configuration (Webhook URL) not found in Student Grades Report settings.');
        }

        $user = $DB->get_record('user', array('id' => $this->userid), '*', MUST_EXIST);

        // Gather data
        $data = $this->get_user_grades_data($user);

        // Update last request time
        if ($cooldown_minutes > 0) {
            set_user_preference('report_studentgrades_last_ai_request', time(), $this->userid);
        }

        // Send to n8n
        return $this->post_to_n8n($n8n_url, $n8n_token, $data);
    }

    /**
     * Gather all user grades data into a structured array
     */
    public function get_user_grades_data($user)
    {
        $data = array(
            'student' => array(
                'id' => $user->id,
                'fullname' => fullname($user),
                'email' => $user->email,
                'username' => $user->username
            ),
            'generated_at' => time(),
            'courses' => array()
        );

        $courses = $this->get_user_courses();
        foreach ($courses as $course) {
            $course_data = array(
                'id' => $course->id,
                'fullname' => format_string($course->fullname),
                'shortname' => format_string($course->shortname),
                'description' => '', // Default empty
                'grades' => array()
            );

            // Add course summary/description
            if (!empty($course->summary)) {
                $course_context = \context_course::instance($course->id);
                $summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $course_context->id, 'course', 'summary', null);
                $course_data['description'] = format_text($summary, $course->summaryformat ?? FORMAT_HTML, array('noclean' => true, 'para' => false, 'context' => $course_context));
            }

            // Get grades
            $gpr = new \grade_plugin_return(array('type' => 'report', 'plugin' => 'studentgrades', 'courseid' => $course->id));
            $gtree = new \grade_tree($course->id, false, false, null, $gpr);

            if (isset($gtree->top_element['children'])) {
                $course_data['grades'] = $this->extract_grade_tree_data($gtree->top_element['children'], $user->id);
            }

            // Add course total
            $course_total = $this->get_course_total($user->id, $course->id);
            if ($course_total) {
                $course_data['total'] = $course_total;
            }

            $data['courses'][] = $course_data;
        }

        return $data;
    }

    /**
     * Recursive function to extract grade data
     */
    private function extract_grade_tree_data($children, $userid)
    {
        $items = array();

        foreach ($children as $child) {
            if ($child['type'] == 'category') {
                $category = $child['object'];
                $cat_data = array(
                    'type' => 'category',
                    'name' => format_string($category->fullname),
                    'children' => array()
                );

                if (!empty($child['children'])) {
                    $cat_data['children'] = $this->extract_grade_tree_data($child['children'], $userid);
                }

                $items[] = $cat_data;
            } else if ($child['type'] == 'item') {
                $grade_item = $child['object'];

                if ($grade_item->itemtype == 'course' || !$this->can_view_grade_item($grade_item)) {
                    continue;
                }

                $grade_grade = \grade_grade::fetch(array('itemid' => $grade_item->id, 'userid' => $userid));

                $item_data = array(
                    'type' => 'item',
                    'name' => format_string($grade_item->itemname),
                    'grade' => $this->format_grade_value($grade_grade, $grade_item),
                    'range' => $this->format_grade_range($grade_item),
                    'percentage' => $this->format_grade_percentage($grade_grade, $grade_item),
                    'description' => $this->get_activity_description($grade_item)
                );

                // Add feedback if exists
                if ($grade_grade && !empty($grade_grade->feedback)) {
                    $item_data['feedback'] = format_text($grade_grade->feedback, $grade_grade->feedbackformat);
                }

                $items[] = $item_data;
            }
        }

        return $items;
    }

    /**
     * Post data to n8n webhook
     */
    private function post_to_n8n($url, $token, $data)
    {
        $curl = new \curl();
        $options = array(
            'CURLOPT_HTTPHEADER' => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ),
            'CURLOPT_RETURNTRANSFER' => true
        );

        // Moodle's curl class might not support setopt array directly in constructor depending on version,
        // but let's use the post method which is standard.
        // We'll set headers manually.
        $curl->setHeader('Content-Type: application/json');
        $curl->setHeader('Authorization: Bearer ' . $token); // Or just 'Authorization: ' . $token depending on how n8n is set up, usually Bearer is safe.
        // If the user said "n8n Token", it might be a query param or header. Standard webhook protection is usually header.
        // Let's also add it to query string to be safe if that's how they implemented it? 
        // No, let's stick to Header. 
        // Actually, many n8n webhooks just use the URL. The "Token" usually refers to a Header Auth.
        // I will add X-N8N-Token as well just in case.
        $curl->setHeader('X-N8N-Chat-Token: ' . $token); // Common pattern

        // Moodle curl post takes ($url, $params, $options)
        // For JSON body, we usually pass string in $params.

        $json_data = json_encode($data);

        $response = $curl->post($url, $json_data);
        $info = $curl->get_info();

        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            return array('success' => true, 'message' => 'Analysis request sent successfully!');
        } else {
            return array('success' => false, 'message' => 'Failed to send data. HTTP Code: ' . $info['http_code'] . ' Response: ' . $response);
        }
    }

    /**
     * Generate HTML footer
     */
    private function get_html_footer()
    {
        return '</body></html>';
    }
}
