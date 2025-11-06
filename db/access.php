<?php
/**
 * Capability definitions for report_studentgrades plugin
 *
 * @package    report_studentgrades
 * @copyright  2025 onwards, Moodle Community
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Moodle Community
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'report/studentgrades:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ),
    ),
    'report/studentgrades:viewall' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ),
    ),
);
