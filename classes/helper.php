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
 * Moodle-to-OneLogin library interface.
 *
 * @package    auth_easysaml
 * @copyright  2015 Jonathon Fowler <jf@jonof.id.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class auth_easysaml_helper {
    const CONFIGNAME = 'auth_easysaml';

    private $config;

    public function __construct() {
        // The essence of _toolkit_loader.php.
        $path = __DIR__ . '/../';
        require_once $path . '/extlib/xmlseclibs/xmlseclibs.php';
        foreach (glob($path . '/lib/Saml2/*.php') as $file) {
            require_once $file;
        }
    }

    /**
     * Load the plugin configuration once.
     * @return object
     */
    private function get_config() {
        if (!isset($this->config)) {
            $this->config = get_config(self::CONFIGNAME);
        }
        return $this->config;
    }

    /**
     * Check whether all mandatory configuration options are set.
     * @return boolean
     */
    public static function is_configured() {
        $config = get_config(self::CONFIGNAME);
        if (!$config) {
            return false;
        }

        if (empty($config->idp_entityid) ||
            empty($config->idp_ssourl) ||
            empty($config->username_attribute)) {
            return false;
        }

        return true;
    }

    /**
     * Map attributes to Moodle user fields as configured.
     * @param array $attrs the raw attributes from SAML.
     * @return array the values mapped onto Moodle user fields.
     */
    private function process_attributes($attrs) {
        $userinfo = array();
        $config = $this->get_config();

        $fieldmap = array('username' => $config->username_attribute);
        foreach (get_object_vars($config) as $var => $value) {
            if (substr($var, 0, 10) === 'field_map_') {
                $fieldmap[ substr($var, 10) ] = $value;
            }
        }

        foreach ($attrs as $name => $values) {
            if (count($values) === 0) {
                continue;
            }
            $value = reset($values);

            $fields = array_keys($fieldmap, $name, true);
            if (empty($fields)) {
                continue;
            }

            foreach ($fields as $field) {
                $userinfo[$field] = $value;
            }
        }

        return $userinfo;
    }

    /**
     * Prepares a configured instance of the SAML Auth object.
     * @return OneLogin_Saml2_Auth
     */
    public function get_auth() {
        global $CFG;

        if (!self::is_configured()) {
            throw new moodle_exception('errornotconfigured', 'auth_easysaml');
        }
        $config = $this->get_config();

        $wwwroot = $CFG->httpswwwroot;
        if (!empty($CFG->loginhttps)) {
            $wwwroot = str_replace('http:', 'https:', $wwwroot);
        }

        $settings = array(
            'strict' => true,
            'debug' => debugging('', DEBUG_ALL),

            // Define ourselves.
            'sp' => array(
                'entityId' => $wwwroot . '/auth/easysaml/metadata.php',
                'assertionConsumerService' => array(
                    'url' => $wwwroot . '/auth/easysaml/acs.php',
                ),
                'singleLogoutService' => array(
                    'url' => $wwwroot . '/auth/easysaml/sls.php',
                    'binding' => $config->idp_slobinding === 'post' ?
                        OneLogin_Saml2_Constants::BINDING_HTTP_POST :
                        OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT
                ),
                'NameIDFormat' => OneLogin_Saml2_Constants::NAMEID_UNSPECIFIED,
            ),

            // Define our identity provider.
            'idp' => array(
                'entityId' => $config->idp_entityid,
                'singleSignOnService' => array(
                    'url' => $config->idp_ssourl,
                ),
            ),

            // Security settings.
            'security' => array(),
        );

        if (!empty($config->idp_slourl)) {
            $settings['idp']['singleLogoutService'] = array(
                'url' => $config->idp_slourl,
                'responseUrl' => $config->idp_sloresponseurl,
                'binding' => $config->idp_slobinding === 'post' ?
                    OneLogin_Saml2_Constants::BINDING_HTTP_POST :
                    OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT,
            );
        }

        if (!empty($CFG->supportname) && !empty($CFG->supportemail)) {
            $settings['contactPerson'] = array(
                'support' => array(
                    'givenName' => $CFG->supportname,
                    'emailAddress' => $CFG->supportemail,
                ),
            );
        }

        if (extension_loaded('mcrypt')) {
            if (!empty($config->sp_cert) && !empty($config->sp_privatekey)) {
                $settings['sp']['x509cert'] = $config->sp_cert;
                $settings['sp']['privateKey'] = $config->sp_privatekey;
            }

            $slopost = $config->idp_slobinding === 'post';
            $settings['security']['signMetadata'] = !empty($config->signmetadata);
            $settings['security']['nameIdEncrypted'] = !empty($config->encryptnameid);
            $settings['security']['authnRequestsSigned'] = !empty($config->signauthrequests);
            $settings['security']['logoutRequestSigned'] = !empty($config->signlogoutrequests) && !$slopost;
            $settings['security']['logoutResponseSigned'] = !empty($config->signlogoutresponses) && !$slopost;
            $settings['security']['wantAssertionsEncrypted'] = !empty($config->wantencryptedasserts);
            $settings['security']['wantNameIdEncrypted'] = !empty($config->wantencryptednameid);
        }
        $settings['security']['wantAssertionsSigned'] = !empty($config->wantsignedasserts);
        $settings['security']['wantMessagesSigned'] = !empty($config->wantsignedmessages);

        if (!empty($config->idp_cert)) {
            $settings['idp']['x509cert'] = $config->idp_cert;
        }
        if (!empty($config->idp_certfingerprint)) {
            $settings['idp']['certFingerprint'] = $config->idp_certfingerprint;
            $settings['idp']['certFingerprintAlgorithm'] = $config->idp_certfingerprintalgo;
        }

        $auth = new OneLogin_Saml2_Auth($settings);
        return $auth;
    }

    public function get_metadata() {
        $auth = $this->get_auth();
        $settings = $auth->getSettings();
        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);
        if (empty($errors)) {
            return $metadata;
        } else {
            debugging('auth_easysaml sp metadata errors: ' . implode(', ', $errors), DEBUG_NORMAL);
            throw new moodle_exception('errorbadconfiguration', 'auth_easysaml');
        }
    }

    public function handle_acs() {
        global $SESSION;

        $auth = $this->get_auth();
        $auth->processResponse();

        $errors = $auth->getErrors();
        if (!empty($errors)) {
            debugging('auth_easysaml acs errors: ' . implode(', ', $errors) .
                ' (' . $auth->getLastErrorReason() . ')', DEBUG_NORMAL);
        }

        if (!$auth->isAuthenticated()) {
            unset($SESSION->auth_easysaml_nameid);
            unset($SESSION->auth_easysaml_sessionindex);
            unset($SESSION->auth_easysaml_userinfo);
            return false;
        }

        $attrs = $auth->getAttributes();
        $userinfo = $this->process_attributes($attrs);
        //debugging('auth_easysaml acs attributes: ' . var_export($attrs, true), DEBUG_DEVELOPER);
        if (!isset($userinfo['username']) || trim($userinfo['username']) === '') {
            debugging('auth_easysaml acs: no username attribute found in response', DEBUG_NORMAL);
            throw new moodle_exception('errornotauthenticated', 'auth_easysaml');
        }

        $SESSION->auth_easysaml_nameid = $auth->getNameId();
        $SESSION->auth_easysaml_sessionindex = $auth->getSessionIndex();
        $SESSION->auth_easysaml_userinfo = $userinfo;

        return true;
    }

    public function handle_slo() {
        $auth = $this->get_auth();
        $config = $this->get_config();

        $retrieveParametersFromServer = $config->idp_slobinding === 'redirect';
        $result = $auth->processSLO(false, null, $retrieveParametersFromServer, array(__CLASS__, 'logout_callback'), true);
        if (is_string($result)) {
            redirect($result);
        } else if (is_array($result)) {
            $this->post_redirect($result['action'], $result['parameters']);
        }

        $errors = $auth->getErrors();
        if (!empty($errors)) {
            debugging('auth_easysaml slo errors: ' . implode(', ', $errors) .
                ' (' . $auth->getLastErrorReason() . ')', DEBUG_NORMAL);
            return false;
        }
        return true;
    }

    public static function logout_callback() {
        require_logout();
    }

    private function post_redirect($action, array $parameters) {
        global $PAGE, $OUTPUT;

        $PAGE->set_context(context_system::instance());
        $PAGE->set_title(get_string('logout'));
        $PAGE->set_cacheable(false);
        $PAGE->set_pagelayout('maintenance');
        $PAGE->requires->js_init_code('document.getElementById("saml-logout").submit();');

        echo $OUTPUT->header();

        $formattrs = array(
            'method' => 'POST',
            'action' => $action,
            'id' => 'saml-logout',
        );
        echo html_writer::start_tag('form', $formattrs);
        echo html_writer::start_tag('noscript');

        echo $OUTPUT->heading(get_string('logout'));

        echo $OUTPUT->box_start('generalbox', 'notice');
        echo html_writer::tag('p', get_string('logoutmessage', 'auth_easysaml'));
        echo html_writer::div(html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('continue'))), 'buttons');
        echo $OUTPUT->box_end();
        echo html_writer::end_tag('noscript');

        foreach ($parameters as $name => $value) {
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $name, 'value' => $value));
        }

        echo html_writer::end_tag('form');

        echo $OUTPUT->footer();
        exit;
    }
}
