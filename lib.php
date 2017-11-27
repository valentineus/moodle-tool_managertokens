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

/**
 * It checks the ability to activate the token and produces it.
 *
 * @param  string $token
 * @return object
 */
function tool_managertokens_activate_token($token = "") {
    global $DB;

    $select_limited     = "limited = 0 OR scope < limited";
    $select_timelimited = "timelimited = 0 OR timelimited > " . time();
    $select = "enabled = 1 AND token = '$token' AND ($select_limited) AND ($select_timelimited)";

    if ($token = $DB->get_record_select("tool_managertokens_tokens", $select, null, "*", IGNORE_MISSING)) {
        $token->scope       = intval($token->scope) + 1;
        $token->timelastuse = time();
        $DB->update_record("tool_managertokens_tokens", $token, false);
    }

    return $token;
}

/* function tool_managertokens_create_backup() {} */

/**
 * Creates an entry in the database.
 *
 * @param  array $options
 * @return number
 */
function tool_managertokens_create_record($options = array()) {
    global $DB;

    if (!isset($options["targetid"])) {
        print_error("missingparam", "error", "", "targetid");
    }

    if (!isset($options["targettype"])) {
        print_error("missingparam", "error", "", "targettype");
    }

    if (!isset($options["token"])) {
        print_error("missingparam", "error", "", "token");
    }

    if ($DB->record_exists("tool_managertokens_tokens", array("token" => $options["token"]))) {
        print_error("duplicatefieldname", "error", "", "token");
    }

    $token = array();
    $token["enabled"]      = false;
    $token["timecreated"]  = time();
    $token["targetid"]     = intval($options["targetid"]);
    $token["targettype"]   = intval($options["targettype"]);
    $token["timemodified"] = $token["timecreated"];
    $token["token"]        = strval($options["token"]);

    if (isset($options["enabled"])) {
        $token["enabled"] = boolval($options["enabled"]);
    }

    if (isset($options["extendedaction"]) && isset($options["extendedoptions"])) {
        $token["extendedaction"] = strval($options["extendedaction"]);
        $token["extendedoptions"] = strval($options["extendedoptions"]);
    }

    if (isset($options["limited"])) {
        $token["limited"] = intval($options["limited"]);
    }

    if (isset($options["timelimited"])) {
        $token["timelimited"] = intval($options["timelimited"]);
    }

    $recordid = $DB->insert_record("tool_managertokens_tokens", $token, true, false);
    return $recordid;
}

/**
 * Removes all entries in the table.
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
    $select = "id = '$key' OR token = '$key'";
    $result = $DB->delete_records_select("tool_managertokens_tokens", $select, null);
    return boolval($result);
}

/**
 * Searches for an id or token.
 *
 * @param number|string $key
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

/* function tool_managertokens_restore_backup($backup = "") {} */

/**
 * Updates the entry in the database.
 *
 * @param  array $options
 * @return boolean
 */
function tool_managertokens_update_record($options = array()) {
    global $DB;

    $result = false;

    if (!isset($options["id"])) {
        print_error("missingparam", "error", "", "id");
    }

    if ($token = $DB->get_record("tool_managertokens_tokens", array("id" => $options["id"]), "*", IGNORE_MISSING)) {
        $token->timemodified = time();

        if (isset($options["enabled"])) {
            $token->enabled = boolval($options["enabled"]);
        }

        if (isset($options["extendedaction"]) && isset($options["extendedoptions"])) {
            $token->extendedaction  = strval($options["extendedaction"]);
            $token->extendedoptions = strval($options["extendedoptions"]);
        }

        if (isset($options["limited"])) {
            $token->limited = intval($options["limited"]);
        }

        if (isset($options["targetid"])) {
            $token->targetid = intval($options["targetid"]);
        }

        if (isset($options["targettype"])) {
            $token->targettype = strval($options["targettype"]);
        }

        if (isset($options["token"])) {
            $token->token = strval($options["token"]);
        }

        if (isset($options["timelimited"])) {
            $token->timelimited = intval($options["timelimited"]);
        }

        $result = $DB->update_record("tool_managertokens_tokens", $token, false);
    }

    return boolval($result);
}