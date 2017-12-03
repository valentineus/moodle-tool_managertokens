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
 * Describes the plugin tables.
 *
 * @package   tool_managertokens
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once($CFG->libdir . "/tablelib.php");

/**
 * Describes the main table of the plugin.
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_managertokens_table extends table_sql {
    /**
     * Manager address.
     *
     * @var string $managertokens
     */
    protected static $managertokens = "/admin/tool/managertokens/index.php";

    /**
     * Editor's address.
     *
     * @var string $editor
     */
    protected static $editor = "/admin/tool/managertokens/editor.php";

    /**
     * Constructor.
     *
     * @param string $uniqueid The unique identifier of the table.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $this->define_table_columns();
        $this->define_table_configs();
    }

    /**
     * Defines the basic settings of the table.
     */
    public function define_table_configs() {
        $this->set_sql("*", "{tool_managertokens_tokens}", "1");
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->no_sorting("actions");
        $this->pageable(true);
    }

    /**
     * Defines the main columns and table headers.
     */
    public function define_table_columns() {
        $columns = array(
            "targetid",
            "token",
            "scope",
            "timelastuse",
            "timecreated",
            "actions"
        );

        $headers = array(
            new lang_string("role", "moodle"),
            new lang_string("password", "moodle"),
            new lang_string("used", "moodle"),
            new lang_string("lastmodified", "moodle"),
            new lang_string("create", "moodle"),
            new lang_string("actions", "moodle")
        );

        $this->define_columns($columns);
        $this->define_headers($headers);
    }

    /**
     * Specifies the display of a column with actions.
     *
     * @param  object $row Data from the database.
     * @return string      Displayed data.
     */
    public function col_actions($row) {
        global $OUTPUT;

        /* Sets the switch icon */
        $hideshowicon   = "t/lock";
        $hideshowstring = new lang_string("enable", "moodle");
        if (boolval($row->enabled)) {
            $hideshowicon   = "t/unlock";
            $hideshowstring = new lang_string("disable", "moodle");
        }

        /* Link to enable / disable the token */
        $hideshowlink = new moodle_url(self::$managertokens, array("hideshowid" => $row->id, "sesskey" => sesskey()));
        $hideshowitem = $OUTPUT->action_icon($hideshowlink, new pix_icon($hideshowicon, $hideshowstring));

        /* Link to edit the token */
        $editlink = new moodle_url(self::$editor, array("tokenid" => $row->id));
        $edititem = $OUTPUT->action_icon($editlink, new pix_icon("t/edit", new lang_string("edit", "moodle")));

        /* Link to delete the token */
        $deletelink = new moodle_url(self::$managertokens, array("deleteid" => $row->id, "sesskey" => sesskey()));
        $deleteitem = $OUTPUT->action_icon($deletelink, new pix_icon("t/delete", new lang_string("delete", "moodle")));

        $html = $hideshowitem . $edititem . $deleteitem;
        return $html;
    }

    /**
     * Specifies the display of the column with the role.
     *
     * @param  object $row Data from the database.
     * @return string      Displayed data.
     */
    public function col_targetid($row) {
        $html = $row->targetid;

        /* The role is absent */
        if ($row->targettype == "null") {
            $html = new lang_string("none", "moodle");
        }

        /* A role is a user */
        if ($row->targettype == "user") {
            /* Specifies the user name */
            $linktext = new lang_string("user", "moodle");
            if ($user = core_user::get_user($row->targetid)) {
                $linktext = "$user->firstname $user->lastname";
            }

            /* Creates a reference */
            $linkurl = new moodle_url("/user/profile.php", array("id" => $row->targetid));
            $html = html_writer::link($linkurl, $linktext);
        }

        return $html;
    }

    /**
     * Specifies the display of a column from the time of creation.
     *
     * @param  object $row Data from the database.
     * @return string      Displayed data.
     */
    public function col_timecreated($row) {
        $date = userdate($row->timecreated, new lang_string("strftimerecent", "langconfig"));
        return $date;
    }

    /**
     * Specifies the display of the last used column.
     *
     * @param  object $row Data from the database.
     * @return string      Displayed data.
     */
    public function col_timelastuse($row) {
        $date = $row->timelastuse;

        if (!empty($row->timelastuse)) {
            $date = userdate($row->timelastuse, new lang_string("strftimerecent", "langconfig"));
        }

        return $date;
    }

    /**
     * Specifies the display of a column with a token.
     *
     * @param  object $row Data from the database.
     * @return string      Displayed data.
     */
    public function col_token($row) {
        $linktext = $row->token;
        $linkurl  = new moodle_url(self::$editor, array("tokenid" => $row->id));

        $html = html_writer::link($linkurl, $linktext);
        return $html;
    }
}