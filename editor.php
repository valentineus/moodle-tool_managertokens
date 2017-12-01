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
 * Page of the token editor.
 *
 * @package   tool_managertokens
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../../config.php");
require_once(__DIR__ . "/classes/standard_forms.php");
require_once(__DIR__ . "/lib.php");

require_once($CFG->libdir . "/adminlib.php");

/* Defines the basic parameters */
$tokenid = optional_param("tokenid", 0, PARAM_INT);

/* Defines the main links */
$urlparameters = array("tokenid" => $tokenid);
$baseurl       = new moodle_url("/admin/tool/managertokens/editor.php", $urlparameters);
$managertokens = new moodle_url("/admin/tool/managertokens/index.php");

/* Configure the context of the page */
admin_externalpage_setup("tool_managertokens", "", null, $baseurl, array());
$context = context_system::instance();

/* Declares the form */
$mform = new token_editor_form($PAGE->url);

/* Cancel processing */
if ($mform->is_cancelled()) {
    redirect($managertokens);
}

/* Loads existing data */
$tokenrecord = new stdClass();
if ($editing = !empty($tokenid)) {
    $tokenrecord = tool_managertokens_find_record($tokenid);
    $mform->set_data($tokenrecord);
}

/* Processing of received data */
if ($data = $mform->get_data()) {
    if ($editing) {
        $data->id = $tokenid;
        tool_managertokens_update_record($data);
    } else {
        tool_managertokens_create_record($data);
    }

    redirect($managertokens, new lang_string("changessaved", "moodle"));
}

/* Specifies the title of the page */
$titlepage = new lang_string("editsettings", "moodle");
$PAGE->navbar->add($titlepage);
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

/* Displays the form */
$mform->display();

/* Footer */
echo $OUTPUT->footer();