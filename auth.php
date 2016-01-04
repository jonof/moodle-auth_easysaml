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

    /**
     * Returns the singleton instance of the SAML2 auth object
     * held by the helper.
     * @return OneLogin_Saml2_Auth
     */
    private function get_auth() {
        if (self::$auth === null) {
            $helper = new auth_simplesaml_helper();
            self::$auth = $helper->get_auth();
        }
        return self::$auth;
    }

    public function loginpage_hook() {
        global $frm, $SESSION;

        if (!auth_simplesaml_helper::is_configured()) {
            return;
        }

        if (isset($_GET['sso'])) {
            // Explicitly do want redirection.
            $wantsso = true;
            unset($SESSION->auth_simplesaml_nosso);
        } else if (isset($_GET['nosso'])) {
            // Explicitly don't want redirection.
            $wantsso = false;

            // Remember this so if the user fails on the login form we
            // don't try and redirect them on reloading.
            $SESSION->auth_simplesaml_nosso = true;
        } else {
            // Be guided by configuration preference and whether
            // we avoided redirection last time through.
            $wantsso = !empty($this->config->prefersso) &&
                empty($SESSION->auth_simplesaml_nosso);
        }
        if (!$wantsso) {
            return;
        }

        $auth = $this->get_auth();
        if (!isset($SESSION->auth_simplesaml_userinfo) ||
                !is_array($SESSION->auth_simplesaml_userinfo)) {
            $url = new moodle_url(get_login_url(), array('sso' => 1));
            $auth->login($url->out_as_local_url(false));

            throw new coding_exception("shouldn't have reached here");
        }

        $frm = new stdClass();
        $frm->username = $SESSION->auth_simplesaml_userinfo['username'];
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
        if (empty($SESSION->auth_simplesaml_userinfo)) {
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

        $auth = $this->get_auth();

        if ($this->config->idp_slobinding === 'post') {
            // POST binding requires the client perform a HTTP POST of a form to the
            // IdP, so we will emit the form, JavaScript required to trigger the
            // hand off, and exit.
            global $PAGE, $OUTPUT;

            $PAGE->set_title(get_string('logout'));
            $PAGE->set_cacheable(false);
            $PAGE->set_pagelayout('maintenance');
            $PAGE->requires->js_init_code('Y.one("#saml-logout").submit();');

            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('logout'));

            $formattrs = array(
                'method' => 'POST',
                'action' => $auth->getSLOurl(),
                'id' => 'saml-logout',
            );
            echo html_writer::start_tag('form', $formattrs);

            echo $OUTPUT->box_start('generalbox', 'notice');
            echo html_writer::tag('p', get_string('logoutmessage', 'auth_simplesaml'));

            echo html_writer::div(html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('continue'))), 'buttons');

            $logoutRequest = new OneLogin_Saml2_LogoutRequest($auth->getSettings(), null, $nameid, $sessionindex);
            $samlRequest = base64_encode($logoutRequest->getRawRequest());
            $relayState = $CFG->wwwroot . '/auth/simplesaml/sls.php';

            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'SAMLRequest', 'value' => $samlRequest));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'RelayState', 'value' => $relayState));
            echo $OUTPUT->box_end();

            echo html_writer::end_tag('form');

            echo $OUTPUT->footer();
            exit;
        }

        // Because login/logout.php won't get an opportunity to call this.
        require_logout();

        if (empty($this->config->return_url)) {
            $returnurl = $CFG->wwwroot . '/';
        } else {
            $returnurl = $this->config->return_url;
        }

        $auth->logout($returnurl, array(), $nameid, $sessionindex);

        throw new coding_exception("shouldn't have reached here");
    }

    private function apply_config_defaults($config) {
        if (!isset($config->idp_name) || $config->idp_name === '') {
            $config->idp_name = get_string('defaultidpname', 'auth_simplesaml');
        }
        if (!isset($config->idp_entityid)) {
            $config->idp_entityid = '';
        }
        if (!isset($config->idp_ssourl)) {
            $config->idp_ssourl = '';
        }
        if (!isset($config->idp_slourl)) {
            $config->idp_slourl = '';
        }
        if (!isset($config->idp_slobinding)) {
            $config->idp_slobinding = 'redirect';
        }
        if (!isset($config->idp_cert)) {
            $config->idp_cert = '';
        }
        if (!isset($config->idp_certfingerprint)) {
            $config->idp_certfingerprint = '';
        }
        if (!isset($config->prefersso)) {
            $config->prefersso = 0;
        }
        if (!isset($config->username_attribute)) {
            $config->username_attribute = '';
        }
        if (!isset($config->sp_cert)) {
            $config->sp_cert = '';
        }
        if (!isset($config->sp_privatekey)) {
            $config->sp_privatekey = '';
        }
        if (!isset($config->signmetadata)) {
            $config->signmetadata = 0;
        }
        if (!isset($config->encryptnameid)) {
            $config->encryptnameid = 0;
        }
        if (!isset($config->signauthrequests)) {
            $config->signauthrequests = 0;
        }
        if (!isset($config->signlogoutrequests)) {
            $config->signlogoutrequests = 0;
        }
        if (!isset($config->signlogoutresponses)) {
            $config->signlogoutresponses = 0;
        }
        if (!isset($config->wantencryptedasserts)) {
            $config->wantencryptedasserts = 0;
        }
        if (!isset($config->wantsignedasserts)) {
            $config->wantsignedasserts = 0;
        }
        if (!isset($config->wantencryptednameid)) {
            $config->wantencryptednameid = 0;
        }
        if (!isset($config->wantsignedmessages)) {
            $config->wantsignedmessages = 0;
        }
        if (!isset($config->return_url)) {
            $config->return_url = '';
        }
        if (!isset($config->change_password_url)) {
            $config->change_password_url = '';
        }
    }

    public function config_form($config, $err, $user_fields) {
        $this->apply_config_defaults($config);
        include "config_form.php";
    }

    public function get_description() {
        $a = new stdClass();
        $a->metadataurl = (string)new moodle_url('/auth/simplesaml/metadata.php');
        $a->acsurl = (string)new moodle_url('/auth/simplesaml/acs.php');
        $a->slsurl = (string)new moodle_url('/auth/simplesaml/sls.php');
        $authdescription = markdown_to_html(get_string("auth_simplesamldescription", "auth_simplesaml", $a));
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

    public function process_config($config) {
        $this->apply_config_defaults($config);

        set_config('idp_name', $config->idp_name, self::CONFIGNAME);
        set_config('idp_entityid', $config->idp_entityid, self::CONFIGNAME);
        set_config('idp_ssourl', $config->idp_ssourl, self::CONFIGNAME);
        set_config('idp_slourl', $config->idp_slourl, self::CONFIGNAME);
        set_config('idp_slobinding', $config->idp_slobinding, self::CONFIGNAME);
        set_config('idp_cert', $config->idp_cert, self::CONFIGNAME);
        set_config('idp_certfingerprint', $config->idp_certfingerprint, self::CONFIGNAME);
        set_config('username_attribute', $config->username_attribute, self::CONFIGNAME);
        set_config('prefersso', !empty($config->prefersso), self::CONFIGNAME);
        set_config('sp_cert', $config->sp_cert, self::CONFIGNAME);
        set_config('sp_privatekey', $config->sp_privatekey, self::CONFIGNAME);
        set_config('signmetadata', $config->signmetadata, self::CONFIGNAME);
        set_config('encryptnameid', $config->encryptnameid, self::CONFIGNAME);
        set_config('signauthrequests', $config->signauthrequests, self::CONFIGNAME);
        set_config('signlogoutrequests', $config->signlogoutrequests, self::CONFIGNAME);
        set_config('signlogoutresponses', $config->signlogoutresponses, self::CONFIGNAME);
        set_config('wantencryptedasserts', $config->wantencryptedasserts, self::CONFIGNAME);
        set_config('wantsignedasserts', $config->wantsignedasserts, self::CONFIGNAME);
        set_config('wantencryptednameid', $config->wantencryptednameid, self::CONFIGNAME);
        set_config('wantsignedmessages', $config->wantsignedmessages, self::CONFIGNAME);

        set_config('return_url', $config->return_url, self::CONFIGNAME);
        set_config('change_password_url', $config->change_password_url, self::CONFIGNAME);

        // Field mappings/locks/etc are saved by the caller.

        return true;
    }

    public function loginpage_idp_list($wantsurl) {
        if (!auth_simplesaml_helper::is_configured()) {
            return array();
        }

        return array(
            array(
                'url' => new moodle_url(get_login_url(), array('sso' => 1)),
                'icon' => new pix_icon('idp', '', 'auth_simplesaml'),
                'name' => $this->config->idp_name,
            )
        );
    }

    public function is_internal() {
        return false;
    }

}
