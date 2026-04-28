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
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/classes/exporter.php');

$userid = optional_param('userid', $USER->id, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

require_login();

// Verify access
require_once(__DIR__ . '/lib.php');
if (!report_studentgrades_can_access_user($userid)) {
    throw new moodle_exception('nopermissions', 'error');
}

$usercontext = context_user::instance($userid);

// Setup page FIRST (moved up before export)
$PAGE->set_context($usercontext);
$PAGE->set_url('/report/studentgrades/index.php', array('userid' => $userid));
$PAGE->set_title(get_string('pluginname', 'report_studentgrades'));
$PAGE->set_heading(get_string('pluginname', 'report_studentgrades'));
$PAGE->set_pagelayout('report');

// Handle export action (now after page setup)
if ($action === 'export') {
    $exporter = new \report_studentgrades\exporter($userid);
    $exporter->export_user_grades();
    exit;
} else if ($action === 'analyze') {
    $exporter = new \report_studentgrades\exporter($userid);
    $result = $exporter->trigger_ai_analysis();

    if ($result['success']) {
        \core\notification::success($result['message']);
    } else {
        \core\notification::error($result['message']);
    }
    // Continue to show page
}

echo $OUTPUT->header();

// Display user selection or export form
if (has_capability('report/studentgrades:viewall', context_system::instance()) && $userid != $USER->id) {
    // Admin view - show user selection
    echo html_writer::tag('h2', get_string('selectuser', 'report_studentgrades'));

    echo html_writer::start_tag('form', array('method' => 'get', 'action' => ''));
    echo html_writer::start_tag('div', array('class' => 'form-group'));
    echo html_writer::tag('label', get_string('selectuser', 'report_studentgrades'), array('for' => 'userid'));

    // Get all users - simplified for this example
    $users = $DB->get_records_menu('user', array('deleted' => 0), 'lastname, firstname', 'id, ' . $DB->sql_fullname() . ' AS fullname');

    echo html_writer::select(
        $users,
        'userid',
        $userid,
        array('' => get_string('choosedots')),
        array('id' => 'userid', 'class' => 'form-control')
    );
    echo html_writer::end_tag('div');

    echo html_writer::tag(
        'button',
        get_string('view'),
        array('type' => 'submit', 'class' => 'btn btn-primary')
    );
    echo html_writer::end_tag('form');

    echo html_writer::tag('hr', '');
}

// Show export button for selected/current user
$user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

echo html_writer::tag('h2', fullname($user));

echo html_writer::start_tag('form', array('method' => 'post', 'action' => '', 'style' => 'display: inline-block; margin-right: 10px;'));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'userid', 'value' => $userid));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'export'));
echo html_writer::tag(
    'button',
    get_string('exporthtml', 'report_studentgrades'),
    array('type' => 'submit', 'class' => 'btn btn-primary btn-lg')
);
echo html_writer::end_tag('form');

// Add Analysis Button
if (get_config('report_studentgrades', 'enableemailanalysis')) {
    echo html_writer::start_tag('form', array('method' => 'post', 'action' => '', 'style' => 'display: inline-block;'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'userid', 'value' => $userid));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'analyze'));
    // Use a different color/icon for AI analysis
    echo html_writer::tag(
        'button',
        'Analyze & Email Me',
        array('type' => 'submit', 'class' => 'btn btn-info btn-lg')
    );
    echo html_writer::end_tag('form');
}

// --- NEW MOODLE AI BUTTON & MODAL LOGIC ---

if (get_config('report_studentgrades', 'enableinstantanalysis')) {
    // Button
    echo html_writer::tag(
        'button',
        'View Analysis Now',
        array('id' => 'btn-moodle-ai-test', 'class' => 'btn btn-success btn-lg', 'style' => 'margin-left: 10px;')
    );

    // JavaScript for Modal
    $js = "
    require(['jquery', 'core/modal_factory', 'core/modal_events', 'core/str', 'core/ajax', 'core/notification'], 
    function($, ModalFactory, ModalEvents, Str, Ajax, Notification) {
        
        var triggerBtn = $('#btn-moodle-ai-test');
        
        ModalFactory.create({
            type: ModalFactory.types.DEFAULT,
            title: 'AI Analysis Result',
            body: '<div class=\"text-center\"><i class=\"fa fa-spinner fa-spin fa-3x\"></i><p>Talking to Moodle AI...</p></div>',
        }, triggerBtn).done(function(modal) {
            
            modal.getRoot().on(ModalEvents.shown, function() {
                // When modal shows, trigger the AJAX call
                // Reset body to loading state in case it was opened before
                modal.setBody('<div class=\"text-center\" style=\"padding:20px;\"><i class=\"fa fa-spinner fa-pulse fa-3x fa-fw\"></i><p>Generating Analysis...</p></div>');
                
                $.ajax({
                    url: 'ajax_ai.php',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'test_ai',
                        userid: " . $userid . "
                    },
                    success: function(response) {
                        if (response && response.success) {
                            var contentHtml = '<div id=\"ai-analysis-content\">' + (response.message || 'Success') + '</div>';
                            var controlsHtml = '<div style=\"margin-top:20px; text-align:right; border-top:1px solid #eee; padding-top:10px;\">' +
                                '<button class=\"btn btn-secondary\" id=\"btn-print-analysis\"><i class=\"fa fa-print\"></i> Print</button> ' +
                                '<button class=\"btn btn-primary\" id=\"btn-download-pdf\"><i class=\"fa fa-file-pdf-o\"></i> Download PDF</button>' +
                                '</div>';
                            
                            modal.setBody(contentHtml + controlsHtml);

                            // Bind events
                            setTimeout(function() {
                                $('#btn-print-analysis').on('click', function() {
                                    var printWindow = window.open('', '', 'height=600,width=800');
                                    printWindow.document.write('<html><head><title>AI Analysis</title>');
                                    printWindow.document.write('<style>body{font-family:sans-serif; padding:20px;}</style>');
                                    printWindow.document.write('</head><body>');
                                    printWindow.document.write($('#ai-analysis-content').html());
                                    printWindow.document.write('</body></html>');
                                    printWindow.document.close();
                                    printWindow.print();
                                });

                                $('#btn-download-pdf').on('click', function() {
                                    var form = $('<form action=\"download_pdf.php\" method=\"post\" target=\"_blank\">' +
                                        '<input type=\"hidden\" name=\"action\" value=\"downloadpdf\">' +
                                        '<input type=\"hidden\" name=\"userid\" value=\"' + " . $userid . " + '\">' +
                                        '<textarea name=\"html_content\" style=\"display:none;\">' + $('#ai-analysis-content').html() + '</textarea>' +
                                        '</form>');
                                    $('body').append(form);
                                    form.submit();
                                    form.remove();
                                });
                            }, 500);
                        } else {
                            var errMsg = (response && response.message) ? response.message : 'Unknown error occurred';
                            modal.setBody('<div class=\"alert alert-danger\">' + errMsg + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                         var errorDetails = status + ': ' + error;
                         try {
                            var resp = JSON.parse(xhr.responseText);
                            if(resp && resp.message) errorDetails = resp.message;
                         } catch(e) {}
                         modal.setBody('<div class=\"alert alert-danger\">Communication Error: ' + errorDetails + '</div>');
                    }
                });
            });
        });
    });
    ";

    $PAGE->requires->js_amd_inline($js);
}


echo $OUTPUT->footer();
