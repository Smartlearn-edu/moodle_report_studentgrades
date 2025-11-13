<?php
/**
 * Student Course Grades report settings
 * @package     report_studentgrades
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    
    // Color Settings Section
    $settings->add(new admin_setting_heading('report_studentgrades/colorheading',
        get_string('colorsettings', 'report_studentgrades'),
        get_string('colorsettingsdesc', 'report_studentgrades')));
    
    // Header Colors
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/header_primary_color',
        get_string('headerprimarycolor', 'report_studentgrades'),
        get_string('headerprimarycolordesc', 'report_studentgrades'),
        '#6f42c1'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/header_secondary_color',
        get_string('headersecondarycolor', 'report_studentgrades'),
        get_string('headersecondarycolordesc', 'report_studentgrades'),
        '#8e44ad'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/header_text_color',
        get_string('headertextcolor', 'report_studentgrades'),
        get_string('headertextcolordesc', 'report_studentgrades'),
        '#ffffff'));
    
    // Grade Performance Colors
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/grade_excellent_color',
        get_string('gradeexcellentcolor', 'report_studentgrades'),
        get_string('gradeexcellentcolordesc', 'report_studentgrades'),
        '#28a745'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/grade_good_color',
        get_string('gradegoodcolor', 'report_studentgrades'),
        get_string('gradegoodcolordesc', 'report_studentgrades'),
        '#17a2b8'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/grade_average_color',
        get_string('gradeaveragecolor', 'report_studentgrades'),
        get_string('gradeaveragecolordesc', 'report_studentgrades'),
        '#ffc107'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/grade_poor_color',
        get_string('gradepoorcolor', 'report_studentgrades'),
        get_string('gradepoorcolordesc', 'report_studentgrades'),
        '#dc3545'));
    
    // Table and Row Colors
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/table_border_color',
        get_string('tablebordercolor', 'report_studentgrades'),
        get_string('tablebordercolordesc', 'report_studentgrades'),
        '#dee2e6'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/row_alternate_color',
        get_string('rowalternatecolor', 'report_studentgrades'),
        get_string('rowalternatecolordesc', 'report_studentgrades'),
        '#f8f9fa'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/row_hover_color',
        get_string('rowhovercolor', 'report_studentgrades'),
        get_string('rowhovercolordesc', 'report_studentgrades'),
        '#e8f4fd'));
    
    // Category Colors
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/category_primary_color',
        get_string('categoryprimarycolor', 'report_studentgrades'),
        get_string('categoryprimarycolordesc', 'report_studentgrades'),
        '#4a4a4a'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/category_secondary_color',
        get_string('categorysecondarycolor', 'report_studentgrades'),
        get_string('categorysecondarycolordesc', 'report_studentgrades'),
        '#2d2d2d'));
    
    // Total Row Colors
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/category_total_primary_color',
        get_string('categorytotalprimarycolor', 'report_studentgrades'),
        get_string('categorytotalprimarycolordesc', 'report_studentgrades'),
        '#17a2b8'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/category_total_secondary_color',
        get_string('categorytotalsecondarycolor', 'report_studentgrades'),
        get_string('categorytotalsecondarycolordesc', 'report_studentgrades'),
        '#138496'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/course_total_primary_color',
        get_string('coursetotalprimarycolor', 'report_studentgrades'),
        get_string('coursetotalprimarycolordesc', 'report_studentgrades'),
        '#28a745'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/course_total_secondary_color',
        get_string('coursetotalsecondarycolor', 'report_studentgrades'),
        get_string('coursetotalsecondarycolordesc', 'report_studentgrades'),
        '#1e7e34'));
    
    // Grade Value Display Colors
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/grade_value_color',
        get_string('gradevaluecolor', 'report_studentgrades'),
        get_string('gradevaluecolordesc', 'report_studentgrades'),
        '#28a745'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/grade_value_bg_color',
        get_string('gradevaluebgcolor', 'report_studentgrades'),
        get_string('gradevaluebgcolordesc', 'report_studentgrades'),
        '#f8fff9'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/percentage_color',
        get_string('percentagecolor', 'report_studentgrades'),
        get_string('percentagecolordesc', 'report_studentgrades'),
        '#007bff'));
    
    $settings->add(new admin_setting_configcolourpicker('report_studentgrades/percentage_bg_color',
        get_string('percentagebgcolor', 'report_studentgrades'),
        get_string('percentagebgcolordesc', 'report_studentgrades'),
        '#f8feff'));
    
    // Reset to defaults button (informational)
    $settings->add(new admin_setting_heading('report_studentgrades/resetheading',
        get_string('resetcolorsheading', 'report_studentgrades'),
        get_string('resetcolorsdesc', 'report_studentgrades')));
}
