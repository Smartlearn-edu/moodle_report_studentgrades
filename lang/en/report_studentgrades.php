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

$string['pluginname'] = 'Student Course Grades';
$string['studentgrades:view'] = 'View student course grades report';
$string['studentgrades:viewall'] = 'View all users\' course grades reports';
$string['selectuser'] = 'Select user';
$string['exporthtml'] = 'Export as HTML';
$string['nousers'] = 'No users found';
$string['nocourses'] = 'Student is not enrolled in any courses';
$string['user'] = 'Student';
$string['reportdate'] = 'Report date';
$string['coursename'] = 'Course';
$string['gradeitem'] = 'Grade item';
$string['grade'] = 'Grade';
$string['range'] = 'Range';
$string['percentage'] = 'Percentage';
$string['total'] = 'Total';
$string['coursetotal'] = 'Course Total';
$string['overallsummary'] = 'Overall Summary';
$string['totalcourses'] = 'Total Courses';
$string['viewmygrades'] = 'View My Grades';
$string['exportmygrades'] = 'Export My Grades';
$string['privacy:metadata'] = 'The Student Course Grades report plugin does not store any personal data.';

// Color Settings
$string['colorsettings'] = 'Color Settings';
$string['colorsettingsdesc'] = 'Customize the colors used in the HTML export grade reports. These settings allow you to match your institution\'s branding and improve visual accessibility.';

// Header Colors
$string['headerprimarycolor'] = 'Header Primary Color';
$string['headerprimarycolordesc'] = 'Primary color for the report header gradient background';
$string['headersecondarycolor'] = 'Header Secondary Color';
$string['headersecondarycolordesc'] = 'Secondary color for the report header gradient background';
$string['headertextcolor'] = 'Header Text Color';
$string['headertextcolordesc'] = 'Text color for the report header';

// Grade Performance Colors
$string['gradeexcellentcolor'] = 'Excellent Grade Color';
$string['gradeexcellentcolordesc'] = 'Color for excellent grade performance indicators';
$string['gradegoodcolor'] = 'Good Grade Color';
$string['gradegoodcolordesc'] = 'Color for good grade performance indicators';
$string['gradeaveragecolor'] = 'Average Grade Color';
$string['gradeaveragecolordesc'] = 'Color for average grade performance indicators';
$string['gradepoorcolor'] = 'Poor Grade Color';
$string['gradepoorcolordesc'] = 'Color for poor grade performance indicators';

// Table Colors
$string['tablebordercolor'] = 'Table Border Color';
$string['tablebordercolordesc'] = 'Color for table borders and cell separators';
$string['rowalternatecolor'] = 'Row Alternate Color';
$string['rowalternatecolordesc'] = 'Background color for alternating table rows';
$string['rowhovercolor'] = 'Row Hover Color';
$string['rowhovercolordesc'] = 'Background color when hovering over table rows';

// Category Colors
$string['categoryprimarycolor'] = 'Category Primary Color';
$string['categoryprimarycolordesc'] = 'Primary color for category row gradient background';
$string['categorysecondarycolor'] = 'Category Secondary Color';
$string['categorysecondarycolordesc'] = 'Secondary color for category row gradient background';

// Total Row Colors
$string['categorytotalprimarycolor'] = 'Category Total Primary Color';
$string['categorytotalprimarycolordesc'] = 'Primary color for category total row gradient background';
$string['categorytotalsecondarycolor'] = 'Category Total Secondary Color';
$string['categorytotalsecondarycolordesc'] = 'Secondary color for category total row gradient background';
$string['coursetotalprimarycolor'] = 'Course Total Primary Color';
$string['coursetotalprimarycolordesc'] = 'Primary color for course total row gradient background';
$string['coursetotalsecondarycolor'] = 'Course Total Secondary Color';
$string['coursetotalsecondarycolordesc'] = 'Secondary color for course total row gradient background';

// Grade Value Colors
$string['gradevaluecolor'] = 'Grade Value Text Color';
$string['gradevaluecolordesc'] = 'Text color for grade values';
$string['gradevaluebgcolor'] = 'Grade Value Background Color';
$string['gradevaluebgcolordesc'] = 'Background color for grade value cells';
$string['percentagecolor'] = 'Percentage Text Color';
$string['percentagecolordesc'] = 'Text color for percentage values';
$string['percentagebgcolor'] = 'Percentage Background Color';
$string['percentagebgcolordesc'] = 'Background color for percentage cells';

// AI Settings
$string['aisettings'] = 'AI Analysis Settings';
$string['aisettingsdesc'] = 'Configure the integration with external AI services via n8n.';
$string['enableemailanalysis'] = 'Enable Analysis via Email';
$string['enableemailanalysisdesc'] = 'Allow users to request an analysis report sent to their email.';
$string['enableinstantanalysis'] = 'Enable Instant Analysis';
$string['enableinstantanalysisdesc'] = 'Allow users to view an analysis report instantly in a modal window.';
$string['webhookurl'] = 'n8n Webhook URL';
$string['webhookurldesc'] = 'The URL of the n8n webhook that will process the student grade data.';
$string['token'] = 'n8n Webhook Token';
$string['tokendesc'] = 'Secure token for authentication with the n8n webhook (sent in headers).';
$string['aiprompt'] = 'AI Analysis Prompt';
$string['aipromptdesc'] = 'The prompt sent to the AI along with the student data. Customize this to change the tone or focus of the analysis.';
$string['aicooldown'] = 'Analysis Cooldown (Minutes)';
$string['aicooldowndesc'] = 'Minimum time in minutes between analysis requests for critical data saving. Set to 0 to disable.';

// Reset Information
$string['resetcolorsheading'] = 'Reset Colors';
$string['resetcolorsdesc'] = 'To reset all colors to their default values, clear each color field and save the settings. The plugin will automatically use the default color scheme.';
