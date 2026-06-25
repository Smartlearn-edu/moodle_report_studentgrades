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
 *
 * @package     report_studentgrades
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_studentgrades\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;

/**
 * Privacy API implementation for the Student Course Grades report plugin
 *
 * This plugin does not store any personal data. It only provides functionality
 * to export grade data that is already stored by Moodle's core grade system.
 * The exported HTML files are generated on-demand and not persistently stored.
 */
class provider implements metadata_provider {
    /**
     * Returns metadata information about this plugin.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('n8n_webhook', [
            'userid' => 'privacy:metadata:userid',
            'grades' => 'privacy:metadata:grades',
        ], 'privacy:metadata:n8n_webhook_summary');

        return $collection;
    }
}
