Student Course Grades Report Plugin
=====================================

Version: 1.0.0
Release Date: August 2025
Compatibility: Moodle 4.0+

DESCRIPTION
-----------
The Student Course Grades Report plugin allows users to view and export their grade reports from ALL courses they are enrolled in as a single comprehensive HTML document. Unlike traditional grade reports that show one course at a time, this plugin provides a holistic view of a student's academic performance across their entire enrollment.

FEATURES
--------
* Export all course grades for a student as one HTML file
* Hierarchical grade structure with categories and totals for each course
* Site logo integration from admin settings
* Customizable color scheme (18+ color settings)
* Word-compatible HTML output for easy document processing
* RTL language support
* Overall summary with total courses enrolled
* Grade visibility and permission checking
* Print-friendly CSS styling
* Admin capability to view any student's cross-course report

USE CASES
---------
* Students preparing academic portfolios
* Students applying for scholarships (need comprehensive grade report)
* Academic advisors reviewing student progress across all courses
* Parents viewing their child's complete academic record
* End-of-semester comprehensive grade summaries
* Transfer students needing complete transcripts

INSTALLATION
------------
1. Download the plugin ZIP file
2. Extract to /path/to/moodle/report/studentgrades/
3. Visit Site Administration > Notifications to complete installation
4. Configure color settings at Site Administration > Plugins > Reports > Student Course Grades

PERMISSIONS
-----------
* report/studentgrades:view - View own course grades report
  - Assigned to: students, teachers, editing teachers, managers
  
* report/studentgrades:viewall - View any user's course grades report
  - Assigned to: teachers, editing teachers, managers

ACCESS
------
Students can access this report from:
* Their user profile menu
* Direct link: /report/studentgrades/index.php

Teachers/Admins can access any student's report by selecting the user.

PLUGIN ARCHITECTURE
-------------------
This is a USER-LEVEL report plugin, meaning it operates at the user context level rather than course context. It complements the existing course-level grade reports by providing a cross-course perspective.

COMPARISON WITH COURSE GRADE REPORTS
------------------------------------
* Course Grade Report: One course → All students
* Student Course Grades Report: One student → All courses

Both plugins can coexist and serve different purposes.

RELEASE NOTES
-------------

Version 1.0.0 (August 2025)
----------------------------
* Initial release
* Comprehensive color customization system with 18+ configurable colors
* Admin settings page for color configuration
* Privacy API implementation for GDPR compliance
* Robust error handling for logo and course enrollment
* Enhanced permission handling for viewing reports
* Proper CSS namespacing to prevent conflicts
* Export grades from all enrolled courses as single HTML file
* Overall summary showing total courses
* Support for RTL languages
* Word-compatible HTML output

SUPPORT
-------
For support and bug reports, please use the Moodle plugins directory or contact the plugin maintainer.

PRIVACY
-------
This plugin does not store any personal data. It only provides functionality to export grade data that is already stored by Moodle's core grade system. Exported HTML files are generated on-demand and not persistently stored.

LICENSE
-------
GNU GPL v3 or later - http://www.gnu.org/copyleft/gpl.html

CREDITS
-------
This plugin was inspired by the need for comprehensive student grade reports across multiple courses, complementing Moodle's built-in course-level grade reporting.
