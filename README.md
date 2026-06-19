# 🎓 Student Course Grades Report Plugin

<div align="center">
  <p><strong>A comprehensive grade reporting tool for Moodle 4.0+</strong></p>

  ![Version](https://img.shields.io/badge/version-1.1.2-blue.svg)
  ![Moodle](https://img.shields.io/badge/moodle-4.0%2B-orange.svg)
  ![License](https://img.shields.io/badge/license-GPLv3-green.svg)
</div>

---

## 📖 Description

The **Student Course Grades Report** plugin allows users to view and export their grade reports from **ALL courses** they are enrolled in as a single, comprehensive HTML document. Unlike traditional grade reports that show one course at a time, this plugin provides a holistic view of a student's academic performance across their entire enrollment history.

## ✨ Key Features

- 📑 **Single-file Export:** Export all course grades for a student as one combined HTML file.
- 🌳 **Hierarchical Structure:** Clear grade structure with categories and totals for each course.
- 🎨 **Deep Customization:** 18+ color settings manageable via an admin settings page.
- 🖼️ **Brand Integration:** Seamless site logo integration.
- 📝 **Word-Compatible:** HTML output is perfectly formatted for easy MS Word document processing.
- 🌍 **RTL Support:** Full support for Right-To-Left languages.
- 📊 **Overall Summary:** Displays global metrics like total courses enrolled.
- 🔒 **Privacy & Permissions:** Strict grade visibility and permission checking, including integration with `local_parentportal` for linked parent accounts.
- 🖨️ **Print-Ready:** Perfectly optimized print-friendly CSS styling.
- 🛡️ **Admin Capabilities:** Authorized roles can view any student's cross-course report.
- 🤖 **AI performance Analysis:** Dual-mode AI analysis (Instant on-screen modal & Asynchronous email reports).
- 📄 **Exportable AI Reports:** Instant print or PDF downloads of the on-screen AI feedback.
- ⏱️ **Rate Limiting Cooldown:** Built-in timers to prevent AI API spam and credit abuse.

## 🎯 Use Cases

This plugin is incredibly useful for various scenarios:

- 🎒 **Students** preparing academic portfolios or applying for scholarships (needing a comprehensive grade report).
- 👩‍🏫 **Academic Advisors** reviewing student progress across all courses.
- 👨‍👩‍👧 **Parents** viewing their child's complete academic record.
- 📅 **Administrators** needing end-of-semester comprehensive grade summaries.
- 🏫 **Transfer Students** needing complete transcripts for external institutions.

## 🤖 AI-Powered Performance Analysis

The plugin features a robust, dual-mode AI analysis system to evaluate student grades and provide actionable, constructive academic feedback:

### 1. Instant On-Screen Analysis (Moodle Core AI)
* **Real-time Feedback:** Uses Moodle 4.5+'s native AI subsystem (`\core_ai\manager`) to analyze grades.
* **Interactive Modal:** Renders the feedback instantly inside a Moodle modal, complete with custom loaders.
* **Export Controls:** Includes on-screen buttons to **Print** the response or **Download as PDF** dynamically.
* **Fallback Mode:** Safely displays a simulated analysis on older Moodle sites where Core AI is not available.

### 2. Email-Based Analysis (n8n Webhook Integration)
* **Asynchronous Execution:** Sends complete, structured student grade data (including activities, categories, grades, and totals in JSON format) to an external webhook.
* **Email Delivery:** The external system (e.g., n8n) parses the grade profile, runs the LLM, and emails the PDF/text report to the student's email.
* **Secure Auth:** Uses bearer token authentication and custom request headers (`Authorization` and `X-N8N-Chat-Token`) to verify webhook access.

### 3. Rate Limiting & Cooldown
* **API Protection:** Admins can define a custom cooldown period (in minutes) to limit how frequently a user can request AI analysis.
* **State Tracking:** Tracks the request timestamp per student using Moodle's user preferences (`report_studentgrades_last_ai_request`).

---

## ⚙️ Installation

1. Download the plugin ZIP file.
2. Extract the contents to `/path/to/moodle/report/studentgrades/`.
3. Log in as an admin and visit **Site Administration > Notifications** to complete the database installation.
4. Configure color settings at **Site Administration > Plugins > Reports > Student Course Grades**.
5. Set up **AI Analysis Settings** (enable buttons, enter webhook URL/token, customize the prompt, and set cooldown minutes) on the same page.

## 🔐 Permissions

The plugin utilizes the following capabilities to manage access:

| Capability | Purpose | Default Roles |
| :--- | :--- | :--- |
| `report/studentgrades:view` | View own course grades report | Students, Teachers, Editing Teachers, Managers |
| `report/studentgrades:viewall` | View any user's course grades report | Teachers, Editing Teachers, Managers |

## 🚀 Access Navigation

### For Students
Students can access their comprehensive report from:
- Their **User Profile Menu**
- Direct access via the URL: `/report/studentgrades/index.php`

### For Teachers & Admins
Users with the appropriate capabilities can access any student's report by simply navigating to their profile and selecting the report tab.

## 🏗️ Plugin Architecture

> [!NOTE]
> This is a **USER-LEVEL report plugin**, meaning it operates at the user context level rather than the course context. It actively complements existing course-level grade reports by extracting and combining a cross-course perspective.

### Comparison with Core Course Grade Reports

- **Course Grade Report:** One course → All students
- **Student Course Grades Report:** One student → All courses

*Both plugins can coexist peacefully and serve entirely different purposes.*

## 📋 Release Notes

### Version 1.1.2 *(June 2026)*
- **AI-Powered Performance Analysis:** Added support for student performance evaluation.
- **Moodle Core AI Subsystem:** Integrated with Moodle 4.5+ AI subsystem for instant on-screen analysis via modal.
- **External Webhook Integrations:** Support for sending structured grades data to external webhooks (e.g., n8n) for asynchronous email analysis.
- **Export Formats for AI Analysis:** Added Print and PDF Download options for on-screen AI reports.
- **Rate Limiting Cooldown:** Implemented customizable user cooldown (in minutes) to prevent AI token/API abuse.
- **Parent Portal Integration:** Support for parent-student linked relationships in `local_parentportal` to allow parents to view and analyze children's grades.

### Version 1.0.0 *(August 2025)*
- Initial release.
- Comprehensive color customization system with 18+ configurable colors.
- Admin settings page for color configuration.
- Privacy API implementation ensuring GDPR compliance.
- Robust error handling for logo fetching and course enrollments.
- Enhanced permission architecture for viewing reports safely.
- Proper CSS namespacing implemented to prevent global conflicts.
- "Export all enrolled courses" as a single HTML file functionality.
- Overall summary block displaying total courses.
- Standardized Word-compatible HTML output and full RTL language support.

## 🛡️ Privacy

> [!IMPORTANT]
> This plugin **does not store** any personal data. It strictly provides a read-only functionality to aggregate and export grade data already stored by Moodle's core grade system. All exported HTML files are generated dynamically on-demand and are never persistently stored or cached.

## 🤝 Support & Credits

For support, feature requests, and bug reports, please refer to the Moodle plugins directory or contact the plugin maintainer directly.

This plugin was inspired by the widespread need for comprehensive student grade exports across multiple courses, bridging the gap left by Moodle's built-in course-level reporting.

---
<div align="center">
  Licensed under the <a href="http://www.gnu.org/copyleft/gpl.html">GNU GPL v3 or later</a>.
</div>
