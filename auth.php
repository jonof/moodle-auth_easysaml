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
 * @package    auth_easysaml
 * @copyright  2015 Jonathon Fowler <jf@jonof.id.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once $CFG->libdir . '/authlib.php';

class auth_plugin_easysaml extends auth_plugin_base {
    private static $auth = null;
    const CONFIGNAME = 'auth_easysaml';

    public function __construct() {
        $this->authtype = 'easysaml';
        $this->config = get_config(self::CONFIGNAME);
    }

    /**
     * Returns the singleton instance of the SAML2 auth object
     * held by the helper.
     * @return OneLogin_Saml2_Auth
     */
    private function get_auth() {
        if (self::$auth === null) {
            $helper = new auth_easysaml_helper();
            self::$auth = $helper->get_auth();
        }
        return self::$auth;
    }

    public function loginpage_hook() {
        global $frm, $SESSION;

        if (!auth_easysaml_helper::is_configured()) {
            return;
        }

        if (isset($_GET['sso'])) {
            // Explicitly do want redirection.
            $wantsso = true;
            unset($SESSION->auth_easysaml_nosso);
        } else if (isset($_GET['nosso'])) {
            // Explicitly don't want redirection.
            $wantsso = false;

            // Remember this so if the user fails on the login form we
            // don't try and redirect them on reloading.
            $SESSION->auth_easysaml_nosso = true;
        } else {
            // Be guided by configuration preference and whether
            // we avoided redirection last time through.
            $wantsso = !empty($this->config->prefersso) &&
                empty($SESSION->auth_easysaml_nosso);
        }
        if (!$wantsso) {
            return;
        }

        $auth = $this->get_auth();
        if (!isset($SESSION->auth_easysaml_userinfo) ||
                !is_array($SESSION->auth_easysaml_userinfo)) {
            $url = new moodle_url(get_login_url(), array('sso' => 1));
            $auth->login($url->out_as_local_url(false));

            throw new coding_exception("shouldn't have reached here");
        }

        $frm = new stdClass();
        $frm->username = $SESSION->auth_easysaml_userinfo['username'];
        $frm->password = '';
    }

    /**
     * Handle a successful sign-on request from the SAML IdP.
     *
     * @param string $username The username (without system magic quotes)
     * @param string $password The password (without system magic quotes)
     *
     * @return bool Authentication success or failure.
     * @access public
     */
    public function user_login($username, $password) {
        global $SESSION;
        if (empty($SESSION->auth_easysaml_userinfo)) {
            return false;
        }
        return true;
    }

    /**
     * Read user information from the sources available to us.
     *
     * Called at login time by moodlelib.php:create_user_record
     * and moodlelib.php:update_user_record, both via
     * moodlelib.php:authenticate_user_login.
     *
     * @param string $username username
     *
     * @return mixed array with no magic quotes, or false on error
     * @access public
     */
    public function get_userinfo($username) {
        global $SESSION;
        if (empty($SESSION->auth_easysaml_userinfo)) {
            debugging('auth_easysaml get_userinfo called when not authed', DEBUG_DEVELOPER);
            return false;
        }
        if (strcasecmp($SESSION->auth_easysaml_userinfo['username'], $username) !== 0) {
            debugging('auth_easysaml get_userinfo called for a different user', DEBUG_DEVELOPER);
            return false;
        }

        return $SESSION->auth_easysaml_userinfo;
    }

    public function logoutpage_hook() {
        global $SESSION, $CFG;
        if (empty($this->config->idp_slourl) ||
                !isset($SESSION->auth_easysaml_nameid) ||
                !isset($SESSION->auth_easysaml_sessionindex)) {
            return;
        }

        $nameid = $SESSION->auth_easysaml_nameid;
        $sessionindex = $SESSION->auth_easysaml_sessionindex;

        $auth = $this->get_auth();

        if ($this->config->idp_slobinding === 'post') {
            // POST binding requires the client perform a HTTP POST of a form to the
            // IdP, so we will emit the form, JavaScript required to trigger the
            // hand off, and exit.

            $returnurl = $CFG->wwwroot . '/auth/easysaml/sls.php';
        } else {
            // Because login/logout.php won't get an opportunity to call this.
            require_logout();

            if (empty($this->config->return_url)) {
                $returnurl = $CFG->wwwroot . '/';
            } else {
                $returnurl = $this->config->return_url;
            }
        }

        $auth->logout($returnurl, array(), $nameid, $sessionindex);

        throw new coding_exception("shouldn't have reached here");
    }

    public function get_description() {
        global $CFG;

        $a = array(
            'metadataurl' => (string)new moodle_url('/auth/easysaml/metadata.php'),
            'acsurl' => (string)new moodle_url('/auth/easysaml/acs.php'),
            'slsurl' => (string)new moodle_url('/auth/easysaml/sls.php'),
        );
        if (!empty($CFG->loginhttps)) {
            $a = str_replace('http:', 'https:', $a);
        }

        $authdescription = markdown_to_html(get_string("auth_easysamldescription", "auth_easysaml", $a));
        return $authdescription;
    }

    /**
     * Determine whether to give the option to change password.
     * @return bool
     */
    public function can_change_password() {
        return !empty($this->config->change_password_url);
    }

    /**
     * Returns the URL for changing the user's password.
     * @return moodle_url url of the password change service, or null
     */
    public function change_password_url() {
        if (!empty($this->config->change_password_url)) {
            return new moodle_url($this->config->change_password_url);
        }
        return null;
    }

    public function loginpage_idp_list($wantsurl) {
        if (!auth_easysaml_helper::is_configured()) {
            return array();
        }

        return array(
            array(
                'url' => new moodle_url(get_login_url(), array('sso' => 1)),
                'icon' => new pix_icon('idp', '', 'auth_easysaml'),
                'name' => $this->config->idp_name,
            )
        );
    }

    public function is_internal() {
        return false;
    }

}
