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
 * @package    auth_simplesaml
 * @copyright  2015 Jonathon Fowler <jf@jonof.id.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class auth_simplesaml_helper {
    const CONFIGNAME = 'auth/simplesaml';

    public function __construct() {
        // The essence of _toolkit_loader.php.
        $path = __DIR__ . '/../';
        require_once $path . '/extlib/xmlseclibs/xmlseclibs.php';
        foreach (glob($path . '/lib/Saml2/*.php') as $file) {
            require_once $file;
        }
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
        $config = get_config(self::CONFIGNAME);

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
            throw new moodle_exception('errornotconfigured', 'auth_simplesaml');
        }
        $config = get_config(self::CONFIGNAME);

        $settings = array(
            'strict' => true,
            'debug' => false,

            // Define ourselves.
            'sp' => array(
                'entityId' => $CFG->wwwroot . '/auth/simplesaml/metadata.php',
                'assertionConsumerService' => array(
                    'url' => $CFG->wwwroot . '/auth/simplesaml/acs.php',
                ),
                'singleLogoutService' => array(
                    'url' => $CFG->wwwroot . '/auth/simplesaml/sls.php',
                ),
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            ),

            // Define our identity provider.
            'idp' => array(
                'entityId' => $config->idp_entityid,
                'singleSignOnService' => array(
                    'url' => $config->idp_ssourl,
                ),
                'singleLogoutService' => array(
                    'url' => $config->idp_slourl,
                ),
            ),

            // Security settings.
            'security' => array(),
        );

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

            $settings['security']['signMetadata'] = !empty($config->signmetadata);
            $settings['security']['nameIdEncrypted'] = !empty($config->encryptnameid);
            $settings['security']['authnRequestsSigned'] = !empty($config->signauthrequests);
            $settings['security']['logoutRequestSigned'] = !empty($config->signlogoutrequests);
            $settings['security']['logoutResponseSigned'] = !empty($config->signlogoutresponses);
            $settings['security']['wantAssertionsEncrypted'] = !empty($config->wantencryptedasserts);
            $settings['security']['wantNameIdEncrypted'] = !empty($config->wantencryptednameid);
            $settings['security']['wantAssertionsSigned'] = !empty($config->wantsignedasserts);
            $settings['security']['wantMessagesSigned'] = !empty($config->wantsignedmessages);
        }

        if (!empty($config->idp_cert)) {
            $settings['idp']['x509cert'] = $config->idp_cert;
        }
        if (!empty($config->idp_certfingerprint)) {
            $settings['idp']['certFingerprint'] = $config->idp_certfingerprint;
        }

        return new OneLogin_Saml2_Auth($settings);
    }

    public function get_metadata() {
        $auth = $this->get_auth();
        $settings = $auth->getSettings();
        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);
        if (empty($errors)) {
            return $metadata;
        } else {
            debugging('auth_simplesaml sp metadata errors: ' . implode(', ', $errors), DEBUG_NORMAL);
            throw new moodle_exception('errorbadconfiguration', 'auth_simplesaml');
        }
    }

    public function handle_acs() {
        global $SESSION;

        $auth = $this->get_auth();
        $auth->processResponse();

        $errors = $auth->getErrors();
        if (!empty($errors)) {
            debugging('auth_simplesaml acs errors: ' . implode(', ', $errors) .
                ' (' . $auth->getLastErrorReason() . ')', DEBUG_NORMAL);
        }

        if (!$auth->isAuthenticated()) {
            unset($SESSION->auth_simplesaml_nameid);
            unset($SESSION->auth_simplesaml_sessionindex);
            unset($SESSION->auth_simplesaml_userinfo);
            return false;
        }

        $attrs = $auth->getAttributes();
        $userinfo = $this->process_attributes($attrs);
        //debugging('auth_simplesaml acs attributes: ' . var_export($attrs, true), DEBUG_DEVELOPER);
        if (!isset($userinfo['username']) || trim($userinfo['username']) === '') {
            debugging('auth_simplesaml acs: no username attribute found in response', DEBUG_NORMAL);
            throw new moodle_exception('errornotauthenticated', 'auth_simplesaml');
        }

        $SESSION->auth_simplesaml_nameid = $auth->getNameId();
        $SESSION->auth_simplesaml_sessionindex = $auth->getSessionIndex();
        $SESSION->auth_simplesaml_userinfo = $userinfo;

        return true;
    }

    public function handle_slo() {
        $auth = $this->get_auth();

        $auth->processSLO();
        $errors = $auth->getErrors();
        if (!empty($errors)) {
            debugging('auth_simplesaml slo errors: ' . implode(', ', $errors) .
                ' (' . $auth->getLastErrorReason() . ')', DEBUG_NORMAL);
        }
    }
}
