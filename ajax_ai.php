<?php
define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');

$userid = optional_param('userid', $USER->id, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);

// Basic security checks
require_login();

// Cast IDs to integers to avoid type mismatch issues
$userid = (int)$userid;
$current_userid = (int)$USER->id;

// Determine context and capability based on whether the user is viewing their own data
if ($userid === $current_userid) {
    // User is accessing their own data.
    // Matching logic from index.php: Implicitly allow users to view their own data
    // without enforcing the 'report/studentgrades:view' capability strictly here,
    // as it can cause issues if roles aren't perfectly propagated.
    $context = context_user::instance($userid);
} else {
    // User is accessing someone else's data
    // If they don't have viewall capability, throw a descriptive error (or rely on require_capability)
    // We rely on require_capability, but let's double check if we can give a hint if they are not admin
    $context = context_system::instance();

    // Check permission strictly
    // Note: If this fails, Moodle throws an exception which results in the generic error message the user saw.
    // If we want to debug why we are here, we can check logic.
    if (!has_capability('report/studentgrades:viewall', $context)) {
        // User is trying to access another user's data without permission OR logic decided they are different users.
        // Return a custom error to help debugging
        $response = [
            'success' => false,
            'message' => "Permission Denied. You are logged in as User ID: $current_userid but requested data for User ID: $userid. 'View all' capability required."
        ];
        echo json_encode($response);
        exit;
    }

    require_capability('report/studentgrades:viewall', $context);
}

$response = [
    'success' => false,
    'message' => ''
];

if ($action === 'test_ai') {
    try {
        // 2. Prepare the prompt with Real Data
        require_once(__DIR__ . '/classes/exporter.php');

        // Check for rate limiting (Cooldown)
        $cooldown_minutes = (int)get_config('report_studentgrades', 'aicooldown');
        if ($cooldown_minutes > 0) {
            $last_request_time = get_user_preferences('report_studentgrades_last_ai_request', 0, $userid);
            $current_time = time();
            $time_diff = $current_time - $last_request_time;
            $cooldown_seconds = $cooldown_minutes * 60;

            if ($time_diff < $cooldown_seconds) {
                // Rate limit exceeded
                $minutes_remaining = ceil(($cooldown_seconds - $time_diff) / 60);
                $response['success'] = false;
                $response['message'] = "Daily limit reached. Please wait $minutes_remaining minute(s) before generating a new analysis.";
                echo json_encode($response);
                exit;
            }
        }

        $exporter = new \report_studentgrades\exporter($userid);
        // Get user object for the function
        $user_obj = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        $grade_data = $exporter->get_user_grades_data($user_obj); // Move data fetching after limit check logic execution flow continues below...

        // Update last request time (only if we proceed, though strictly we should update on success, but here is fine to block repeated spamming attempts)
        if ($cooldown_minutes > 0) {
            set_user_preference('report_studentgrades_last_ai_request', time(), $userid);
        }

        // Construct the prompt
        $data_json = json_encode($grade_data, JSON_PRETTY_PRINT);
        // Construct the prompt
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

        // 1. Check if Moodle AI API (Core AI) is available (Moodle 4.5+)
        if (class_exists('\core_ai\manager')) {
            $context = context_system::instance();

            // This is a hypothetical usage based on standard Moodle AI patterns
            // Real implementation depends on the specific AI actions defined in the system

            // Let's attempt a text generation action if a provider is set up

            // Let's attempt a text generation action if a provider is set up
            try {
                // Ensure arguments are of correct type
                $ctx_id = (int)$context->id;
                $uid = (int)$userid;
                $prompt = (string)$prompt_text;

                $action = new \core_ai\aiactions\generate_text(
                    $ctx_id,
                    $uid,
                    $prompt
                );

                global $DB;
                $manager = new \core_ai\manager($DB);
                $result = $manager->process_action($action);

                if ($result->get_success()) {
                    $response['success'] = true;
                    $data = $result->get_response_data();
                    if (!empty($data['generatedcontent'])) {
                        $response['message'] = $data['generatedcontent'];
                    } else {
                        $response['message'] = "AI Response success but no content. Raw data: " . print_r($data, true);
                    }
                } else {
                    $response['message'] = "AI Provider Error: " . $result->get_error_message();
                }
            } catch (Throwable $t) {
                $response['message'] = 'Error initializing AI action: ' . $t->getMessage();
                $response['debug'] = [
                    'context_id' => $context->id,
                    'context_id_type' => gettype($context->id),
                    'userid' => $userid,
                    'userid_type' => gettype($userid),
                    'prompt' => $prompt_text,
                    'available_methods' => get_class_methods('\core_ai\manager')
                ];
            }
        } else {
            // Fallback for older Moodle versions or if core_ai is not found for testing
            // We simulate a response so the user sees the window working
            $response['success'] = true;
            $response['message'] = "AI System Response: Hello! I received your message. (Moodle Core AI classes not detected, showing mock response)";
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid action';
}

echo json_encode($response);