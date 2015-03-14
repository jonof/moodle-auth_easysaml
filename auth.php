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
 * Simple SAML authentication plugin.
 *
 * @package    auth_simplesaml
 * @copyright  2015 Jonathon Fowler <jf@jonof.id.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once $CFG->libdir . '/authlib.php';

class auth_plugin_simplesaml extends auth_plugin_base {
    private static $auth = null;
    const CONFIGNAME = 'auth/simplesaml';

    public function __construct() {
        $this->authtype = 'simplesaml';
        $this->config = get_config(self::CONFIGNAME);
    }

    private function get_auth() {
        if (self::$auth === null) {
            $helper = new auth_simplesaml_helper();
            self::$auth = $helper->get_auth();
        }
        return self::$auth;
    }

    public function loginpage_hook() {
        global $frm, $SESSION;

        if (!isset($_GET['sso'])) {
            return;
        }

        $auth = $this->get_auth();
        if (!isset($SESSION->auth_simplesaml_userinfo) ||
                !is_array($SESSION->auth_simplesaml_userinfo)) {
            $url = new moodle_url(get_login_url(), array('sso' => 1));
            $auth->login((string)$url);

            throw new coding_exception("shouldn't have reached here");
        }

        $frm = new stdClass();
        $frm->username = $SESSION->auth_simplesaml_userinfo['username'];
        $frm->password = '';
    }

    public function user_login($username, $password) {
        global $SESSION;
        if (empty($SESSION->auth_simplesaml_userinfo)) {
            return false;
        }
        return true;
    }

    public function get_userinfo($username) {
        global $SESSION;
        if (empty($SESSION->auth_simplesaml_userinfo)) {
            debugging('auth_simplesaml get_userinfo called when not authed', DEBUG_DEVELOPER);
            return false;
        }
        if ($SESSION->auth_simplesaml_userinfo['username'] !== $username) {
            debugging('auth_simplesaml get_userinfo called for a different user', DEBUG_DEVELOPER);
            return false;
        }

        return $SESSION->auth_simplesaml_userinfo;
    }

    public function logoutpage_hook() {
        global $SESSION, $CFG;
        if (empty($this->config->idp_slourl) ||
                !isset($SESSION->auth_simplesaml_nameid) ||
                !isset($SESSION->auth_simplesaml_sessionindex)) {
            return;
        }

        $nameid = $SESSION->auth_simplesaml_nameid;
        $sessionindex = $SESSION->auth_simplesaml_sessionindex;

        // Because login/logout.php won't get an opportunity to call this.
        require_logout();

        $auth = $this->get_auth();
        $auth->logout($CFG->wwwroot . '/', array(), $nameid, $sessionindex);

        throw new coding_exception("shouldn't have reached here");
    }

    private function apply_config_defaults($config) {
        if (!isset($config->idp_entityid)) {
            $config->idp_entityid = '';
        }
        if (!isset($config->idp_ssourl)) {
            $config->idp_ssourl = '';
        }
        if (!isset($config->idp_slourl)) {
            $config->idp_slourl = '';
        }
        if (!isset($config->idp_cert)) {
            $config->idp_cert = '';
        }
        if (!isset($config->idp_certfingerprint)) {
            $config->idp_certfingerprint = '';
        }

        if (!isset($config->username_attribute)) {
            $config->username_attribute = '';
        }
    }

    public function config_form($config, $err, $user_fields) {
        $this->apply_config_defaults($config);
        include "config_form.php";
    }

    public function process_config($config) {
        $this->apply_config_defaults($config);

        set_config('idp_entityid', $config->idp_entityid, self::CONFIGNAME);
        set_config('idp_ssourl', $config->idp_ssourl, self::CONFIGNAME);
        set_config('idp_slourl', $config->idp_slourl, self::CONFIGNAME);
        set_config('idp_cert', $config->idp_cert, self::CONFIGNAME);
        set_config('idp_certfingerprint', $config->idp_certfingerprint, self::CONFIGNAME);
        set_config('username_attribute', $config->username_attribute, self::CONFIGNAME);

        // Field mappings/locks/etc are saved by the caller.

        return true;
    }

    public function loginpage_idp_list($wantsurl) {
        return array(
            array(
                'url' => new moodle_url(get_login_url(), array('sso' => 1)),
                'icon' => new pix_icon('i/guest', ''),
                'name' => get_string('defaultidpname', 'auth_simplesaml'),
            )
        );
    }

    public function is_internal() {
        return false;
    }

}
