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
 * Plugin upgrade steps.
 *
 * @package    auth_easysaml
 * @copyright  2015 Jonathon Fowler <jf@jonof.id.au>
 * @copyright  2017 The University of Southern Queensland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade auth_easysaml.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_easysaml_upgrade($oldversion) {
    global $CFG, $DB;

    if ($oldversion < 2017092700) {
        // Convert info in config plugins from auth/easysaml to auth_easysaml.
        upgrade_fix_config_auth_plugin_names('easysaml');
        upgrade_fix_config_auth_plugin_defaults('easysaml');
        upgrade_plugin_savepoint(true, 2017092700, 'auth', 'easysaml');
    }

    if ($oldversion < 2018022100) {
        if (get_config('auth_easysaml', 'idp_certfingerprint') != '') {
            set_config('idp_certfingerprintalgo', 'sha1', 'auth_easysaml');
        }
        upgrade_plugin_savepoint(true, 2018022100, 'auth', 'easysaml');
    }

    return true;
}
