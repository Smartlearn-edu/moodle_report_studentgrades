# 🎓 Student Course Grades Report Plugin

<div align="center">
  <p><strong>A comprehensive grade reporting tool for Moodle 4.0+</strong></p>

  ![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
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
- 🔒 **Privacy & Permissions:** Strict grade visibility and permission checking.
- 🖨️ **Print-Ready:** Perfectly optimized print-friendly CSS styling.
- 🛡️ **Admin Capabilities:** Authorized roles can view any student's cross-course report.

## 🎯 Use Cases

This plugin is incredibly useful for various scenarios:

- 🎒 **Students** preparing academic portfolios or applying for scholarships (needing a comprehensive grade report).
- 👩‍🏫 **Academic Advisors** reviewing student progress across all courses.
- 👨‍👩‍👧 **Parents** viewing their child's complete academic record.
- 📅 **Administrators** needing end-of-semester comprehensive grade summaries.
- 🏫 **Transfer Students** needing complete transcripts for external institutions.

## ⚙️ Installation

1. Download the plugin ZIP file.
2. Extract the contents to `/path/to/moodle/report/studentgrades/`.
3. Log in as an admin and visit **Site Administration > Notifications** to complete the database installation.
4. Configure color settings at **Site Administration > Plugins > Reports > Student Course Grades**.

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
