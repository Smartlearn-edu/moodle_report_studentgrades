<?php
require_once('../../config.php');
require_once($CFG->libdir . '/pdflib.php');

$userid = optional_param('userid', $USER->id, PARAM_INT);
$html_content = optional_param('html_content', '', PARAM_RAW); // RAW to allow HTML
$action = optional_param('action', '', PARAM_ALPHA);

require_login();

// Basic security: Check if user is allowed to view this data
// In a real scenario, we should re-verify the content belongs to the user or re-generate it.
// Assuming the content is passed from the client for now as a "Save as PDF" feature of the modal view.
$context = context_user::instance($userid);
if ($userid != $USER->id && !has_capability('report/studentgrades:viewall', context_system::instance())) {
    print_error('nopermissions');
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
    echo "No content to export.";
}
