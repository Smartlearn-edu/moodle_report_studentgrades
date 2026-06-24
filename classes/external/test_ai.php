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
 * External API for AI Analysis.
 *
 * @package     report_studentgrades
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_studentgrades\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use Exception;
use Throwable;

class test_ai extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'The user ID'),
        ]);
    }

    public static function execute($userid) {
        global $CFG, $DB, $USER;
        require_once($CFG->libdir . '/filelib.php');

        $params = self::validate_parameters(self::execute_parameters(), ['userid' => $userid]);
        $userid = $params['userid'];
        $current_userid = (int)$USER->id;

        require_once(__DIR__ . '/../../lib.php');

        if (!report_studentgrades_can_access_user($userid, $current_userid)) {
            return [
                'success' => false,
                'message' => "Permission Denied. You are logged in as User ID: $current_userid but requested data for User ID: $userid. Appropriate capability or linked parent account required."
            ];
        }

        try {
            require_once(__DIR__ . '/../exporter.php');

            $cooldown_minutes = (int)get_config('report_studentgrades', 'aicooldown');
            if ($cooldown_minutes > 0) {
                $last_request_time = get_user_preferences('report_studentgrades_last_ai_request', 0, $userid);
                $current_time = time();
                $time_diff = $current_time - $last_request_time;
                $cooldown_seconds = $cooldown_minutes * 60;

                if ($time_diff < $cooldown_seconds) {
                    $minutes_remaining = ceil(($cooldown_seconds - $time_diff) / 60);
                    return [
                        'success' => false,
                        'message' => get_string('dailylimitreached', 'report_studentgrades', $minutes_remaining)
                    ];
                }
            }

            $exporter = new \report_studentgrades\exporter($userid);
            $user_obj = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
            $grade_data = $exporter->get_user_grades_data($user_obj);

            if ($cooldown_minutes > 0) {
                set_user_preference('report_studentgrades_last_ai_request', time(), $userid);
            }

            $data_json = json_encode($grade_data, JSON_PRETTY_PRINT);

            $default_prompt = "You are an educational AI assistant. Analyze the following student performance data. " .
                "The data includes course descriptions, activities, max grades, student grades, and activity descriptions. " .
                "Provide a constructive analysis of the student's strengths and areas for improvement based on this data.";

            $config_prompt = get_config('report_studentgrades', 'aiprompt');
            if (empty($config_prompt)) {
                $config_prompt = $default_prompt;
            }

            $prompt_text = $config_prompt . "\n\n" .
                "Student Data JSON:\n" . $data_json;

            if (class_exists('\core_ai\manager')) {
                $context = \context_system::instance();

                try {
                    $ctx_id = (int)$context->id;
                    $uid = (int)$userid;
                    $prompt = (string)$prompt_text;

                    $action = new \core_ai\aiactions\generate_text(
                        $ctx_id,
                        $uid,
                        $prompt
                    );

                    $manager = new \core_ai\manager($DB);
                    $result = $manager->process_action($action);

                    if ($result->get_success()) {
                        $data = $result->get_response_data();
                        if (!empty($data['generatedcontent'])) {
                            return ['success' => true, 'message' => $data['generatedcontent']];
                        } else {
                            return ['success' => true, 'message' => "AI Response success but no content. Raw data: " . print_r($data, true)];
                        }
                    } else {
                        return ['success' => false, 'message' => "AI Provider Error: " . $result->get_error_message()];
                    }
                } catch (Throwable $t) {
                    return ['success' => false, 'message' => 'Error initializing AI action: ' . $t->getMessage()];
                }
            } else {
                return ['success' => true, 'message' => "AI System Response: Hello! I received your message. (Moodle Core AI classes not detected, showing mock response)"];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success flag'),
            'message' => new external_value(PARAM_RAW, 'Result message or error')
        ]);
    }
}
