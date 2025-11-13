<?php
/**
 * Main interface for the Student Course Grades report
 *
 * @package     report_studentgrades
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__ . '/classes/exporter.php');

$userid = optional_param('userid', $USER->id, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

require_login();

// Verify access
$usercontext = context_user::instance($userid);
if ($userid != $USER->id && !has_capability('report/studentgrades:viewall', context_system::instance())) {
    throw new moodle_exception('nopermissions', 'error');
}

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
    
    echo html_writer::select($users, 'userid', $userid, array('' => get_string('choosedots')), 
                            array('id' => 'userid', 'class' => 'form-control'));
    echo html_writer::end_tag('div');
    
    echo html_writer::tag('button', get_string('view'), 
                         array('type' => 'submit', 'class' => 'btn btn-primary'));
    echo html_writer::end_tag('form');
    
    echo html_writer::tag('hr', '');
}

// Show export button for selected/current user
$user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

echo html_writer::tag('h2', fullname($user));

echo html_writer::start_tag('form', array('method' => 'post', 'action' => ''));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'userid', 'value' => $userid));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'export'));

echo html_writer::tag('p', get_string('exportmygrades', 'report_studentgrades'));

echo html_writer::tag('button', get_string('exporthtml', 'report_studentgrades'), 
                     array('type' => 'submit', 'class' => 'btn btn-primary btn-lg'));
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
