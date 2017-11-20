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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Manager tokens.
 *
 * @package   tool_managertokens
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../../config.php");
require_once($CFG->libdir . "/adminlib.php");

/* Link generation */
$managertokens = new moodle_url("/admin/tool/managertokens/managertokens.php");
$baseurl = new moodle_url($managertokens);

/* Configure the context of the page */
admin_externalpage_setup("tool_managertokens", "", null, $baseurl, array());
$context = context_system::instance();

/* The page title */
$titlepage = new lang_string("pluginname", "tool_managertokens");
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

/* Footer */
echo $OUTPUT->footer();