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
 * The home page of the token manager.
 *
 * @package   tool_managertokens
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../../config.php");
require_once(__DIR__ . "/classes/managertokens_table.php");
require_once(__DIR__ . "/lib.php");

require_once($CFG->libdir . "/adminlib.php");

/* Defines the basic parameters */
$backupservices = optional_param("getbackup", 0, PARAM_BOOL);
$deleteall      = optional_param("deleteall", 0, PARAM_BOOL);
$deleteid       = optional_param("deleteid", 0, PARAM_INT);
$hideshowid     = optional_param("hideshowid", 0, PARAM_INT);

/* Defines the main links */
$editor        = "/admin/tool/managertokens/editor.php";
$managertokens = "/admin/tool/managertokens/index.php";
$restorebackup = "/admin/tool/managertokens/restorebackup.php";
$baseurl       = new moodle_url($managertokens);

/* Configure the context of the page */
admin_externalpage_setup("tool_managertokens", "", null, $baseurl, array());
$context = context_system::instance();

/* Creates a backup */
if (boolval($backupservices)) {
    $filecontent = tool_managertokens_create_backup();
    $filename    = "managertokens_" . date("U") . ".backup";
    send_file($filecontent, $filename, 0, 0, true, true);
}

/* Deletes all data */
if (boolval($deleteall) && confirm_sesskey()) {
    tool_managertokens_delete_all_records();
    redirect($baseurl, new lang_string("deleted", "moodle"));
}

/* Removes an existing token */
if (!empty($deleteid) && confirm_sesskey()) {
    tool_managertokens_delete_record($deleteid);
    redirect($baseurl, new lang_string("deleted", "moodle"));
}

/* Updates an existing token */
if (!empty($hideshowid) && confirm_sesskey()) {
    if ($record = tool_managertokens_find_record($hideshowid)) {
        $record->enabled = !boolval($record->enabled);
        tool_managertokens_update_record($record);
        redirect($baseurl, new lang_string("changessaved", "moodle"));
    }
}

/* Specifies the title of the page */
$titlepage = new lang_string("pluginname", "tool_managertokens");
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

/* Adds the add button */
$addtokenurl = new moodle_url($editor);
echo $OUTPUT->single_button($addtokenurl, new lang_string("add", "moodle"));

/* Adds a delete button */
$deleteallurl = new moodle_url($managertokens, array("deleteall" => true, "sesskey" => sesskey()));
echo $OUTPUT->single_button($deleteallurl, new lang_string("deleteall", "moodle"), "get");

/* Adds a backup button */
$backupurl = new moodle_url($managertokens, array("getbackup" => true));
echo $OUTPUT->single_button($backupurl, new lang_string("backup", "moodle"), "get");

/* Adds a restore button */
$restorebackupurl = new moodle_url($restorebackup);
echo $OUTPUT->single_button($restorebackupurl, new lang_string("restore", "moodle"));

/* Displays the table */
$table = new tool_managertokens_table("tool-managertokens-table");
$table->define_baseurl($baseurl);
$table->out(25, true);

/* Footer */
echo $OUTPUT->footer();