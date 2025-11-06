<?php
/**
 * Privacy API implementation for report_studentgrades plugin
 *
 * @package    report_studentgrades
 * @copyright  2025 onwards, Moodle Community
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Moodle Community
 */

namespace report_studentgrades\privacy;

use core_privacy\local\metadata\null_provider;

/**
 * Privacy API implementation for the Student Course Grades report plugin
 *
 * This plugin does not store any personal data. It only provides functionality
 * to export grade data that is already stored by Moodle's core grade system.
 * The exported HTML files are generated on-demand and not persistently stored.
 */
class provider implements null_provider {

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
