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
 * Download PDF action.
 *
 * @package     report_studentgrades
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->libdir . '/pdflib.php');
$userid = optional_param('userid', $USER->id, PARAM_INT);
// We use PARAM_RAW here to allow HTML, but we must immediately clean it before use
$html_content = optional_param('html_content', '', PARAM_RAW);
$html_content = clean_param($html_content, PARAM_CLEANHTML);
$action = optional_param('action', '', PARAM_ALPHA);

require_login();

// Basic security: Check if user is allowed to view this data
// In a real scenario, we should re-verify the content belongs to the user or re-generate it.
// Assuming the content is passed from the client for now as a "Save as PDF" feature of the modal view.
$context = context_user::instance($userid);
require_once(__DIR__ . '/lib.php');
if (!report_studentgrades_can_access_user($userid)) {
    throw new \moodle_exception('nopermissions', 'error');
}

if ($action === 'downloadpdf' && !empty($html_content)) {
    $pdf = new pdf();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    // Add some styling or title
    $html = '<h1>AI Analysis Result</h1>';
    $html .= '<p><strong>Student:</strong> ' . fullname($DB->get_record('user', ['id' => $userid])) . '</p>';
    $html .= '<p><strong>Date:</strong> ' . userdate(time()) . '</p>';
    $html .= '<hr>';
    $html .= $html_content;

    $pdf->writeHTML($html);
    $pdf->Output('AI_Analysis_' . $userid . '_' . date('YmdHis') . '.pdf', 'D');
    exit;
} else {
    echo get_string('nocontenttoexport', 'report_studentgrades');
}
