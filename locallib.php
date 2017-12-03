<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Functions and classes for the token manager.
 *
 * @package   tool_managertokens
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once($CFG->dirroot . "/cohort/lib.php");
require_once($CFG->dirroot . "/group/lib.php");

/**
 * Register the user in the global group.
 *
 * @param object $user
 * @param number $cohortid
 */
function tool_managertokens_enroll_user_for_cohort($user, $cohortid) {
    cohort_add_member($cohortid, $user->id);
}

/**
 * Enrollment of the user for the course.
 *
 * @param object $user
 * @param number $courseid
 */
function tool_managertokens_enroll_user_for_course($user, $courseid) {
    global $DB;

    $context = context_course::instance($courseid, IGNORE_MISSING);

    if (!is_enrolled($context, $user)) {
        $plugin = enrol_get_plugin("manual");

        if (empty($plugin)) {
            print_error("manualpluginnotinstalled", "enrol_manual");
        }

        $instance = null;
        $enrolinstances = enrol_get_instances($courseid, true);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "manual") {
                $instance = $courseenrolinstance;
                break;
            }
        }

        if (empty($instance) || !$plugin->allow_enrol($instance)) {
            $errorparams = new stdClass();
            $errorparams->courseid = $courseid;
            print_error("wsnoinstance", "enrol_manual", null, $errorparams);
        }

        if ($roleid = $DB->get_field("role", "id", array("shortname" => "student"), IGNORE_MISSING)) {
            $plugin->enrol_user($instance, $user->id, $roleid);
        }
    }
}

/**
 * Register the user in the group.
 *
 * @param object $user
 * @param number $groupid
 */
function tool_managertokens_enroll_user_for_group($user, $groupid) {
    $group = groups_get_group($groupid);
    tool_managertokens_enroll_user_for_course($user, $group->courseid);
    groups_add_member($group, $user);
}