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
 * Admin settings and defaults.
 *
 * @package    auth_easysaml
 * @copyright  2015 Jonathon Fowler <jf@jonof.id.au>
 * @copyright  2017 The University of Southern Queensland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('auth_easysaml/configidp',
        new lang_string('configidp', 'auth_easysaml'), ''
    ));

    $settings->add(new admin_setting_configtext('auth_easysaml/idp_entityid',
        new lang_string('idp_entityid', 'auth_easysaml'),
        new lang_string('idp_entityid_desc', 'auth_easysaml'),
        '', PARAM_RAW_TRIMMED, 40
    ));
    $settings->add(new admin_setting_configtext('auth_easysaml/idp_ssourl',
        new lang_string('idp_ssourl', 'auth_easysaml'),
        new lang_string('idp_ssourl_desc', 'auth_easysaml'),
        '', PARAM_URL, 40
    ));
    $settings->add(new admin_setting_configtext('auth_easysaml/idp_slourl',
        new lang_string('idp_slourl', 'auth_easysaml'),
        new lang_string('idp_slourl_desc', 'auth_easysaml'),
        '', PARAM_URL, 40
    ));
    $settings->add(new admin_setting_configtext('auth_easysaml/idp_sloresponseurl',
        new lang_string('idp_sloresponseurl', 'auth_easysaml'),
        new lang_string('idp_sloresponseurl_desc', 'auth_easysaml'),
        '', PARAM_URL, 40
    ));
    $settings->add(new admin_setting_configselect('auth_easysaml/idp_slobinding',
        new lang_string('idp_slobinding', 'auth_easysaml'),
        new lang_string('idp_slobinding_desc', 'auth_easysaml'),
        'redirect',
        [
            'redirect' => new lang_string('bindingredirect', 'auth_easysaml'),
            'post' => new lang_string('bindingpost', 'auth_easysaml'),
        ]
    ));
    $settings->add(new admin_setting_configtextarea('auth_easysaml/idp_cert',
        new lang_string('idp_cert', 'auth_easysaml'),
        new lang_string('idp_cert_desc', 'auth_easysaml'),
        '', PARAM_RAW_TRIMMED, 40, 3
    ));
    $settings->add(new admin_setting_configtext('auth_easysaml/idp_certfingerprint',
        new lang_string('idp_certfingerprint', 'auth_easysaml'),
        new lang_string('idp_certfingerprint_desc', 'auth_easysaml'),
        '', PARAM_RAW_TRIMMED, 40
    ));
    $settings->add(new admin_setting_configtext('auth_easysaml/idp_name',
        new lang_string('idp_name', 'auth_easysaml'),
        new lang_string('idp_name_desc', 'auth_easysaml'),
        get_string('defaultidpname', 'auth_easysaml'), PARAM_RAW_TRIMMED, 30
    ));

    $settings->add(new admin_setting_heading('auth_easysaml/configencryption',
        new lang_string('configencryption', 'auth_easysaml'), ''
    ));

    if (extension_loaded('mcrypt')) {
        $settings->add(new admin_setting_configtextarea('auth_easysaml/sp_cert',
            new lang_string('sp_cert', 'auth_easysaml'),
            new lang_string('sp_cert_desc', 'auth_easysaml'),
            '', PARAM_RAW_TRIMMED, 40, 3
        ));
        $settings->add(new admin_setting_configtextarea('auth_easysaml/sp_privatekey',
            new lang_string('sp_privatekey', 'auth_easysaml'),
            new lang_string('sp_privatekey_desc', 'auth_easysaml'),
            '', PARAM_RAW_TRIMMED, 40, 3
        ));

        $settings->add(new admin_setting_heading('auth_easysaml/configencryptionnote',
            '', new lang_string('encryptionconfignote', 'auth_easysaml')
        ));

        $settings->add(new admin_setting_configcheckbox('auth_easysaml/signmetadata',
            new lang_string('signmetadata', 'auth_easysaml'), '',
            0
        ));
        $settings->add(new admin_setting_configcheckbox('auth_easysaml/signauthrequests',
            new lang_string('signauthrequests', 'auth_easysaml'), '',
            0
        ));
        $settings->add(new admin_setting_configcheckbox('auth_easysaml/signlogoutrequests',
            new lang_string('signlogoutrequests', 'auth_easysaml'), '',
            0
        ));
        $settings->add(new admin_setting_configcheckbox('auth_easysaml/signlogoutresponses',
            new lang_string('signlogoutresponses', 'auth_easysaml'), '',
            0
        ));
        $settings->add(new admin_setting_configcheckbox('auth_easysaml/encryptnameid',
            new lang_string('encryptnameid', 'auth_easysaml'), '',
            0
        ));
        $settings->add(new admin_setting_configcheckbox('auth_easysaml/wantencryptedasserts',
            new lang_string('wantencryptedasserts', 'auth_easysaml'), '',
            0
        ));
        $settings->add(new admin_setting_configcheckbox('auth_easysaml/wantencryptednameid',
            new lang_string('wantencryptednameid', 'auth_easysaml'), '',
            0
        ));
    } else {
        $settings->add(new admin_setting_heading('auth_easysaml/confignomcryptnotice',
            '', new lang_string('nomcryptnotice', 'auth_easysaml')
        ));
    }

    $settings->add(new admin_setting_configcheckbox('auth_easysaml/wantsignedasserts',
        new lang_string('wantsignedasserts', 'auth_easysaml'), '',
        0
    ));
    $settings->add(new admin_setting_configcheckbox('auth_easysaml/wantsignedmessages',
        new lang_string('wantsignedmessages', 'auth_easysaml'), '',
        0
    ));

    $settings->add(new admin_setting_heading('auth_easysaml/configgeneral',
        new lang_string('configgeneral', 'auth_easysaml'), ''
    ));

    $settings->add(new admin_setting_configtext('auth_easysaml/username_attribute',
        new lang_string('username_attribute', 'auth_easysaml'),
        new lang_string('username_attribute_desc', 'auth_easysaml'),
        '', PARAM_RAW_TRIMMED, 30
    ));
    $settings->add(new admin_setting_configcheckbox('auth_easysaml/prefersso',
        new lang_string('prefersso', 'auth_easysaml'),
        new lang_string('prefersso_desc', 'auth_easysaml', get_login_url() . '?nosso'),
        0
    ));
    $settings->add(new admin_setting_configtext('auth_easysaml/return_url',
        new lang_string('return_url', 'auth_easysaml'),
        new lang_string('return_url_desc', 'auth_easysaml'),
        '', PARAM_URL, 40
    ));
    $settings->add(new admin_setting_configtext('auth_easysaml/change_password_url',
        new lang_string('change_password_url', 'auth_easysaml'),
        new lang_string('change_password_url_desc', 'auth_easysaml'),
        '', PARAM_URL, 40
    ));

    $authplugin = get_auth_plugin('easysaml');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
            '', true, false, $authplugin->get_custom_user_profile_fields());

    // Adjust each field mapping from PARAM_ALPHANUMEXT type.
    foreach (get_object_vars($settings->settings) as $setting) {
        if (strpos($setting->name, 'field_map_') === 0) {
            $setting->paramtype = PARAM_RAW_TRIMMED;
        }
    }
}
