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

// Prepare data for template
$users_list = [];
if (has_capability('report/studentgrades:viewall', context_system::instance()) && $userid != $USER->id) {
    $all_users = $DB->get_records_menu('user', array('deleted' => 0), 'lastname, firstname', 'id, ' . $DB->sql_fullname() . ' AS fullname');
    foreach ($all_users as $uid => $uname) {
        $users_list[] = [
            'id' => $uid,
            'fullname' => $uname,
            'selected' => ($uid == $userid)
        ];
    }
}

$user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

$templatedata = [
    'showuserselection' => has_capability('report/studentgrades:viewall', context_system::instance()) && $userid != $USER->id,
    'userid' => $userid,
    'fullname' => fullname($user),
    'enableemailanalysis' => get_config('report_studentgrades', 'enableemailanalysis'),
    'enableinstantanalysis' => get_config('report_studentgrades', 'enableinstantanalysis'),
    'users' => $users_list
];

echo $OUTPUT->render_from_template('report_studentgrades/index', $templatedata);

if ($templatedata['enableinstantanalysis']) {
    $PAGE->requires->js_call_amd('report_studentgrades/ai_modal', 'init');
}


echo $OUTPUT->footer();
