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
 * Defines the displayed forms.
 *
 * @package   tool_managertokens
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once($CFG->libdir . "/formslib.php");

/**
 * Defines the form of the token editor.
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class token_editor_form extends moodleform {
    /**
     * Constructor.
     *
     * @param string $baseurl
     */
    public function __construct($baseurl) {
        parent::__construct($baseurl);
    }

    /**
     * Defines the basic elements of the form.
     */
    protected function definition() {
        $mform =& $this->_form;

        /* Defines roles */
        $targettypetypes = array(
            "null" => new lang_string("none", "moodle"),
            "user" => new lang_string("user", "moodle")
        );

        /* Defines additional actions */
        $extendedactiontypes = array(
            "cohort"   => new lang_string("cohort", "cohort"),
            "course"   => new lang_string("course", "moodle"),
            "group"    => new lang_string("group", "moodle"),
            "null"     => new lang_string("none", "moodle"),
            "redirect" => new lang_string("redirect", "moodle")
        );

        /* The header of the main parameters */
        $mform->addElement("header", "general", new lang_string("general", "moodle"));

        /* Entry element of the token */
        $mform->addElement("text", "token", new lang_string("password", "moodle"));
        $mform->addRule("token", null, "required");
        $mform->setDefault("token", generate_password(12));
        $mform->setType("token", PARAM_RAW_TRIMMED);

        /* State switching element */
        $mform->addElement("advcheckbox", "enabled", new lang_string("enable", "moodle"));
        $mform->setAdvanced("enabled");
        $mform->setDefault("enabled", 1);
        $mform->setType("enabled", PARAM_BOOL);

        /* The role selection element */
        $mform->addElement("select", "targettype", new lang_string("role", "moodle"), $targettypetypes);
        $mform->setDefault("targettype", "user");
        $mform->setType("targettype", PARAM_TAG);

        /* The identifier element */
        $mform->addElement("text", "targetid", new lang_string("idnumbermod", "moodle"));
        $mform->setDefault("targetid", 1);
        $mform->setType("targetid", PARAM_INT);

        /* The header of constraints */
        $mform->addElement("header", "statsuserlogins", new lang_string("statsuserlogins", "moodle"));

        /* Element for restricting input attempts */
        $mform->addElement("text", "limited", new lang_string("statsuniquelogins", "moodle"));
        $mform->setDefault("limited", 0);
        $mform->setType("limited", PARAM_INT);

        /* Element for limiting time */
        $mform->addElement("duration", "timelimited", new lang_string("maxtimelimit", "admin"));
        $mform->setDefault("timelimited", 0);
        $mform->setType("timelimited", PARAM_INT);

        /* The header for additional actions */
        $mform->addElement("header", "advancedsettings", new lang_string("advancedsettings", "moodle"));

        /* Element for selecting an additional action */
        $mform->addElement("select", "extendedaction", new lang_string("action", "moodle"), $extendedactiontypes);
        $mform->setDefault("extendedaction", "null");
        $mform->setType("extendedaction", PARAM_TAG);

        /* Element of setting additional action */
        $mform->addElement("text", "extendedoptions", new lang_string("configuration", "moodle"));
        $mform->setDefault("extendedoptions", null);
        $mform->setType("extendedoptions", PARAM_RAW_TRIMMED);

        /* Control buttons */
        $this->add_action_buttons(true);
    }
}

/**
 * Defines the form of the recovery page.
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tokens_backup_form extends moodleform {
    /**
     * Constructor.
     *
     * @param string $baseurl
     */
    public function __construct($baseurl) {
        parent::__construct($baseurl);
    }

    /**
     * Defines the basic elements of the form.
     */
    protected function definition() {
        $mform =& $this->_form;

        /* The header of the page */
        $mform->addElement("header", "restore", new lang_string("restore", "moodle"));

        /* Element for download file */
        $mform->addElement("filepicker", "backupfile", new lang_string("file", "moodle"));
        $mform->addRule("backupfile", null, "required");

        /* Element for checking the action */
        $mform->addElement("hidden", "sesskey", sesskey());

        /* Control buttons */
        $this->add_action_buttons(true, new lang_string("restore", "moodle"));
    }
}