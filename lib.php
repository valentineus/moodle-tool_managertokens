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
 * External plugin API.
 *
 * @package   tool_managertokens
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once(__DIR__ . "/locallib.php");

/**
 * It checks the ability to activate the token and produces it.
 *
 * @param  string $token
 * @return object
 */
function tool_managertokens_activate_token($token = "") {
    global $DB;

    $selectlimited     = "limited = 0 OR scope < limited";
    $selecttimelimited = "timelimited = 0 OR (timecreated + timelimited) > " . time();
    $select = "enabled = 1 AND token = '$token' AND ($selectlimited) AND ($selecttimelimited)";
    if ($token = $DB->get_record_select("tool_managertokens_tokens", $select, null, "*", IGNORE_MISSING)) {
        $token->scope       = intval($token->scope) + 1;
        $token->timelastuse = time();
        $DB->update_record("tool_managertokens_tokens", $token, false);
    }

    return $token;
}

/**
 * Toggles the status of an existing token.
 *
 * @param  number|string $key
 * @return boolean
 */
function tool_managertokens_change_status($key = "") {
    global $DB;

    $result = false;
    $select = "id = '$key' OR token = '$key'";
    if ($DB->record_exists_select("tool_managertokens_tokens", $select, null)) {
        $enabled = $DB->get_field_select("tool_managertokens_tokens", "enabled", $select, null, IGNORE_MISSING);
        $result = $DB->set_field_select("tool_managertokens_tokens", "enabled", !boolval($enabled), $select, null);
    }

    return boolval($result);
}

/**
 * Creates a backup copy of the tokens.
 *
 * @return string
 */
function tool_managertokens_create_backup() {
    $list = tool_managertokens_get_list(0, 0);
    $archive = base64_encode(gzcompress(serialize($list), 9));
    return $archive;
}

/**
 * Creates an entry in the database.
 *
 * @param  object $options
 * @return number
 */
function tool_managertokens_create_record($options) {
    global $DB;

    $token = new stdClass();
    $token->enabled      = !empty($options->enabled)     ? boolval($options->enabled)    : false;
    $token->limited      = !empty($options->limited)     ? intval($options->limited)     : 0;
    $token->targetid     = !empty($options->targetid)    ? intval($options->targetid)    : 0;
    $token->targettype   = !empty($options->targettype)  ? strval($options->targettype)  : "null";
    $token->timelimited  = !empty($options->timelimited) ? intval($options->timelimited) : 0;
    $token->token        = !empty($options->token)       ? strval($options->token)       : generate_password(12);
    $token->timecreated  = time();
    $token->timemodified = time();

    if (!empty($options->extendedaction) && !empty($options->extendedoptions)) {
        $token->extendedaction  = strval($options->extendedaction);
        $token->extendedoptions = strval($options->extendedoptions);
    }

    if ($DB->record_exists("tool_managertokens_tokens", array("token" => $token->token))) {
        print_error("duplicatefieldname", "error", null, "token");
    }

    $recordid = $DB->insert_record("tool_managertokens_tokens", $token, true, false);
    return $recordid;
}

/**
 * Specifies the user for authorization.
 *
 * @param object $token
 */
function tool_managertokens_definition_user($token) {
    $user = false;

    if ($token->targettype == "user") {
        $user = core_user::get_user($token->targetid);
    }

    return $user;
}

/**
 * Removes all entries in the table.
 * Attention! All current records are deleted!
 *
 * @return boolean
 */
function tool_managertokens_delete_all_records() {
    global $DB;

    $result = $DB->delete_records("tool_managertokens_tokens", null);
    return boolval($result);
}

/**
 * Deletes an entry by an ID or token.
 *
 * @param  number|string $key
 * @return boolean
 */
function tool_managertokens_delete_record($key = 0) {
    global $DB;

    $result = false;
    if ($token = tool_managertokens_find_record($key)) {
        $result = $DB->delete_records("tool_managertokens_tokens", array("id" => $token->id));
    }

    return boolval($result);
}

/**
 * Searches for an id or token.
 *
 * @param  number|string  $key
 * @return object|boolean
 */
function tool_managertokens_find_record($key = 0) {
    global $DB;

    $select = "id = '$key' OR token = '$key'";
    $token = $DB->get_record_select("tool_managertokens_tokens", $select, null, "*", IGNORE_MISSING);
    return $token;
}

/**
 * Get the entire list of tokens.
 *
 * @param  number $limitfrom
 * @param  number $limitnum
 * @return array
 */
function tool_managertokens_get_list($limitfrom = 0, $limitnum = 0) {
    global $DB;

    $result = $DB->get_records("tool_managertokens_tokens", null, "id", "*", $limitfrom, $limitnum);
    return $result;
}

/**
 * Performs additional actions for the user.
 *
 * @param object $token
 * @param object $user
 */
function tool_managertokens_perform_additional_action($token, $user) {
    global $DB;

    /* Redirect user */
    if ($token->extendedaction == "redirect") {
        $redirect = new moodle_url($token->extendedoptions);
        redirect($redirect);
    }

    /* Enroll in the local group */
    if ($token->extendedaction == "group") {
        $groupid = intval($token->extendedoptions);
        if ($DB->record_exists("groups", array("id" => $groupid))) {
            tool_managertokens_enroll_user_for_group($user, $groupid);
        }
    }

    /* Enroll in the global group */
    if ($token->extendedaction == "cohort") {
        $cohortid = intval($token->extendedoptions);
        if ($DB->record_exists("cohort", array("id" => $cohortid))) {
            tool_managertokens_enroll_user_for_cohort($user, $cohortid);
        }
    }

    /* Enroll in the global course */
    if ($token->extendedaction == "course") {
        $courseid = intval($token->extendedoptions);
        if ($DB->record_exists("course", array("id" => $courseid))) {
            tool_managertokens_enroll_user_for_course($user, $courseid);
        }
    }
}

/**
 * Restores data from a backup.
 * Attention! All current records are deleted!
 *
 * @param string $backup
 */
function tool_managertokens_restore_backup($backup = "") {
    global $DB;

    if ($list = unserialize(gzuncompress(base64_decode($backup)))) {
        tool_managertokens_delete_all_records();
        $DB->insert_records("tool_managertokens_tokens", $list);
    }
}

/**
 * Updates the entry in the database.
 *
 * @param  object  $options
 * @return boolean
 */
function tool_managertokens_update_record($options) {
    global $DB;

    $result = false;

    if (!isset($options->id)) {
        print_error("missingparam", "error", null, "id");
    }

    if ($token = $DB->get_record("tool_managertokens_tokens", array("id" => $options->id), "*", IGNORE_MISSING)) {
        $token->enabled      = !empty($options->enabled)     ? boolval($options->enabled)    : false;
        $token->limited      = !empty($options->limited)     ? intval($options->limited)     : $token->limited;
        $token->targetid     = !empty($options->targetid)    ? intval($options->targetid)    : $token->targetid;
        $token->targettype   = !empty($options->targettype)  ? strval($options->targettype)  : $token->targettype;
        $token->timelimited  = !empty($options->timelimited) ? intval($options->timelimited) : $token->timelimited;
        $token->token        = !empty($options->token)       ? strval($options->token)       : $token->token;
        $token->timemodified = time();

        if (!empty($options->extendedaction) && !empty($options->extendedoptions)) {
            $token->extendedaction  = strval($options->extendedaction);
            $token->extendedoptions = strval($options->extendedoptions);
        }

        $result = $DB->update_record("tool_managertokens_tokens", $token, false);
    }

    return boolval($result);
}