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
 * Configuration page.
 *
 * @package    auth_simplesaml
 * @copyright  2015 Jonathon Fowler <jf@jonof.id.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $OUTPUT;
?>
<table class="configtable">
    <tr><td colspan="3"><?php echo $OUTPUT->heading(get_string('configidp', 'auth_simplesaml')) ?></td></tr>
    <tr>
        <td><label for="idp_entityid"><?php echo get_string('idp_entityid', 'auth_simplesaml') ?></label></td>
        <td><input name="idp_entityid" id="idp_entityid" type="text" size="40" value="<?php echo s($config->idp_entityid) ?>"></td>
        <td class="desc"><?php echo get_string('idp_entityid_desc', 'auth_simplesaml') ?></td>
    </tr>
    <tr>
        <td><label for="idp_ssourl"><?php echo get_string('idp_ssourl', 'auth_simplesaml') ?></label></td>
        <td><input name="idp_ssourl" id="idp_ssourl" type="text" size="40" value="<?php echo s($config->idp_ssourl) ?>"></td>
        <td class="desc"><?php echo get_string('idp_ssourl_desc', 'auth_simplesaml') ?></td>
    </tr>
    <tr>
        <td><label for="idp_slourl"><?php echo get_string('idp_slourl', 'auth_simplesaml') ?></label></td>
        <td><input name="idp_slourl" id="idp_slourl" type="text" size="40" value="<?php echo s($config->idp_slourl) ?>"></td>
        <td class="desc"><?php echo get_string('idp_slourl_desc', 'auth_simplesaml') ?></td>
    </tr>
    <tr>
        <td><label for="idp_slobinding"><?php echo get_string('idp_slobinding', 'auth_simplesaml') ?></label></td>
        <td><?php
            $options = array(
                'redirect' => get_string('bindingredirect', 'auth_simplesaml'),
                'post' => get_string('bindingpost', 'auth_simplesaml'),
            );
            echo html_writer::select($options, 'idp_slobinding', $config->idp_slobinding, false, array('id' => 'idp_slobinding'));
        ?></td>
        <td class="desc"></td>
    </tr>
    <tr>
        <td><label for="idp_cert"><?php echo get_string('idp_cert', 'auth_simplesaml') ?></label></td>
        <td><textarea name="idp_cert" id="idp_cert" rows="3" cols="40"><?php echo s($config->idp_cert) ?></textarea></td>
        <td class="desc"><?php echo get_string('idp_cert_desc', 'auth_simplesaml') ?></td>
    </tr>
    <tr>
        <td><label for="idp_certfingerprint"><?php echo get_string('idp_certfingerprint', 'auth_simplesaml') ?></label></td>
        <td><input name="idp_certfingerprint" id="idp_certfingerprint" type="text" size="40" value="<?php echo s($config->idp_certfingerprint) ?>"></td>
        <td class="desc"><?php echo get_string('idp_certfingerprint_desc', 'auth_simplesaml') ?></td>
    </tr>
    <tr>
        <td><label for="idp_name"><?php echo get_string('idp_name', 'auth_simplesaml') ?></label></td>
        <td><input name="idp_name" id="idp_name" type="text" size="30" value="<?php echo s($config->idp_name) ?>"></td>
        <td class="desc"><?php echo get_string('idp_name_desc', 'auth_simplesaml') ?></td>
    </tr>

    <tr><td colspan="3"><?php echo $OUTPUT->heading(get_string('configencryption', 'auth_simplesaml')) ?></td></tr>
<?php
    if (extension_loaded('mcrypt')) {
?>
    <tr>
        <td><label for="sp_cert"><?php echo get_string('sp_cert', 'auth_simplesaml') ?></label></td>
        <td><textarea name="sp_cert" id="sp_cert" rows="3" cols="40"><?php echo s($config->sp_cert) ?></textarea></td>
        <td class="desc"><?php echo get_string('sp_cert_desc', 'auth_simplesaml') ?></td>
    </tr>
    <tr>
        <td><label for="sp_privatekey"><?php echo get_string('sp_privatekey', 'auth_simplesaml') ?></label></td>
        <td><textarea name="sp_privatekey" id="sp_privatekey" rows="3" cols="40"><?php echo s($config->sp_privatekey) ?></textarea></td>
        <td class="desc"><?php echo get_string('sp_privatekey_desc', 'auth_simplesaml') ?></td>
    </tr>
    <tr>
        <td><label for="signmetadata"><?php echo get_string('signmetadata', 'auth_simplesaml') ?></label></td>
        <td><input name="signmetadata" id="signmetadata" type="checkbox" value="1" <?php echo $config->signmetadata ? 'checked' : '' ?>></td>
        <td class="desc" rowspan="9"><?php echo get_string('encryptionconfignote', 'auth_simplesaml') ?></td>
    </tr>
    <tr>
        <td><label for="signauthrequests"><?php echo get_string('signauthrequests', 'auth_simplesaml') ?></label></td>
        <td><input name="signauthrequests" id="signauthrequests" type="checkbox" value="1" <?php echo $config->signauthrequests ? 'checked' : '' ?>></td>
    </tr>
    <tr>
        <td><label for="signlogoutrequests"><?php echo get_string('signlogoutrequests', 'auth_simplesaml') ?></label></td>
        <td><input name="signlogoutrequests" id="signlogoutrequests" type="checkbox" value="1" <?php echo $config->signlogoutrequests ? 'checked' : '' ?>></td>
    </tr>
    <tr>
        <td><label for="signlogoutresponses"><?php echo get_string('signlogoutresponses', 'auth_simplesaml') ?></label></td>
        <td><input name="signlogoutresponses" id="signlogoutresponses" type="checkbox" value="1" <?php echo $config->signlogoutresponses ? 'checked' : '' ?>></td>
    </tr>
    <tr>
        <td><label for="encryptnameid"><?php echo get_string('encryptnameid', 'auth_simplesaml') ?></label></td>
        <td><input name="encryptnameid" id="encryptnameid" type="checkbox" value="1" <?php echo $config->encryptnameid ? 'checked' : '' ?>></td>
    </tr>
    <tr>
        <td><label for="wantencryptedasserts"><?php echo get_string('wantencryptedasserts', 'auth_simplesaml') ?></label></td>
        <td><input name="wantencryptedasserts" id="wantencryptedasserts" type="checkbox" value="1" <?php echo $config->wantencryptedasserts ? 'checked' : '' ?>></td>
    </tr>
    <tr>
        <td><label for="wantencryptednameid"><?php echo get_string('wantencryptednameid', 'auth_simplesaml') ?></label></td>
        <td><input name="wantencryptednameid" id="wantencryptednameid" type="checkbox" value="1" <?php echo $config->wantencryptednameid ? 'checked' : '' ?>></td>
    </tr>
<?php
    } else {
        echo '<tr><td colspan="3">';
        echo $OUTPUT->notification(get_string('nomcryptnotice', 'auth_simplesaml'));
        echo '</td></tr>';
    }
?>
    <tr>
        <td><label for="wantsignedasserts"><?php echo get_string('wantsignedasserts', 'auth_simplesaml') ?></label></td>
        <td><input name="wantsignedasserts" id="wantsignedasserts" type="checkbox" value="1" <?php echo $config->wantsignedasserts ? 'checked' : '' ?>></td>
    </tr>
    <tr>
        <td><label for="wantsignedmessages"><?php echo get_string('wantsignedmessages', 'auth_simplesaml') ?></label></td>
        <td><input name="wantsignedmessages" id="wantsignedmessages" type="checkbox" value="1" <?php echo $config->wantsignedmessages ? 'checked' : '' ?>></td>
    </tr>

    <tr><td colspan="3"><?php echo $OUTPUT->heading(get_string('configgeneral', 'auth_simplesaml')) ?></td></tr>
    <tr>
        <td><label for="username_attribute"><?php echo get_string('username_attribute', 'auth_simplesaml') ?></label></td>
        <td><input name="username_attribute" id="username_attribute" type="text" size="30" value="<?php echo s($config->username_attribute) ?>"></td>
        <td class="desc"><?php echo get_string('username_attribute_desc', 'auth_simplesaml') ?></td>
    </tr>
    <tr>
        <td><label for="prefersso"><?php echo get_string('prefersso', 'auth_simplesaml') ?></label></td>
        <td><input name="prefersso" id="prefersso" type="checkbox" value="1" <?php echo $config->prefersso ? 'checked' : '' ?>></td>
        <td class="desc"><?php echo get_string('prefersso_desc', 'auth_simplesaml', get_login_url() . '?nosso') ?></td>
    </tr>
    <tr>
        <td><label for="return_url"><?php echo get_string('return_url', 'auth_simplesaml') ?></label></td>
        <td><input name="return_url" id="return_url" type="text" size="40" value="<?php echo s($config->return_url) ?>"></td>
        <td class="desc"><?php echo get_string('return_url_desc', 'auth_simplesaml') ?></td>
    </tr>
    <tr>
        <td><label for="change_password_url"><?php echo get_string('change_password_url', 'auth_simplesaml') ?></label></td>
        <td><input name="change_password_url" id="change_password_url" type="text" size="40" value="<?php echo s($config->change_password_url) ?>"></td>
        <td class="desc"><?php echo get_string('change_password_url_desc', 'auth_simplesaml') ?></td>
    </tr>

    <?php print_auth_lock_options('simplesaml', $this->userfields, null, true, false); ?>
</table>
