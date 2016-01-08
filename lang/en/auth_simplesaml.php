<?php

$string['auth_simplesamldescription'] = 'Simplified SAML2 authentication plugin.

The IdP will need to be configured with these parameters:

 * SP entity ID: `{$a->metadataurl}`
 * Assertion Consumer Service URL: `{$a->acsurl}` (POST binding)
 * Single Logout Service request/response URL: `{$a->slsurl}`
';
$string['bindingpost'] = 'POST';
$string['bindingredirect'] = 'Redirect';
$string['change_password_url'] = 'Change password URL';
$string['change_password_url_desc'] = 'The location of the IdP\'s password change service. If unspecified, no \'Change password\' option will be shown on users\' preferences page.';
$string['configencryption'] = 'Encryption and Signing';
$string['configgeneral'] = 'General';
$string['configidp'] = 'Identity Provider (IdP)';
$string['defaultidpname'] = 'SAML';
$string['encryptionconfignote'] = 'These settings define the signing and encryption expectations of the Identity Provider and this site.
<ul>
<li><em>Sign</em> and <em>Require encrypted</em> options require the SP certificate and private key be configured.</li>
<li><em>Require signed</em> options require the IdP certificate or fingerprint be configured.</li>
<li><em>Encrypt the name ID</em> requires the IdP certificate be configured. (The fingerprint is not enough.)</li>
</ul>
';
$string['encryptnameid'] = 'Encrypt the name ID';
$string['errorbadconfiguration'] = 'The plugin is not configured correctly.';
$string['errornotauthenticated'] = 'Authentication was not successful.';
$string['errornotconfigured'] = 'The plugin has not been fully configured.';
$string['idp_entityid'] = 'Entity ID';
$string['idp_entityid_desc'] = 'The entity ID of the SAML2 Identity Provider.';
$string['idp_name'] = 'Display name';
$string['idp_name_desc'] = 'A user-friendly name for the Identity Provider for display on the login page.';
$string['idp_ssourl'] = 'SSO URL';
$string['idp_ssourl_desc'] = 'The URL of the Single Sign On service using "HTTP-Redirect" binding.';
$string['idp_slobinding'] = 'SLO Binding';
$string['idp_slourl'] = 'SLO Request URL';
$string['idp_slourl_desc'] = 'The URL of the Single Log Out service\'s request endpoint.';
$string['idp_sloresponseurl'] = 'SLO Response URL';
$string['idp_sloresponseurl_desc'] = 'The URL of the Single Log Out service\'s response endpoint.';
$string['idp_cert'] = 'Certificate';
$string['idp_cert_desc'] = 'The Base64-encoded X.509 certificate (DER or PEM format) that signs SAML IdP responses. You can provide this or the \'Certificate fingerprint\'.';
$string['idp_certfingerprint'] = 'Certificate fingerprint';
$string['idp_certfingerprint_desc'] = 'The <strong>SHA1</strong> fingerprint of the certificate that signs SAML IdP responses (formatted <code>XX:XX:XX:...:XX</code>). This is a simpler method for validating the signature if you don\'t wish to use the full certificate.';
$string['logoutmessage'] = 'Your browser should redirect automatically to complete the logout operation. If it does not, please press the "Continue" button below.';
$string['nomcryptnotice'] = 'The PHP mcrypt module is not enabled so many encryption and signing functions are not available.';
$string['pluginname'] = 'Simplified SAML';
$string['prefersso'] = 'Prefer SSO';
$string['prefersso_desc'] = 'Whether to automatically send the user for sign on when the login page is accessed. When this is enabled, the redirection can be avoided with <code>{$a}</code>.';
$string['return_url'] = 'Logout return URL';
$string['return_url_desc'] = 'URL for the IdP to redirect to after logout. If blank, return to Moodle.';
$string['sp_cert'] = 'SP certificate';
$string['sp_cert_desc'] = 'The Base64-encoded X.509 certificate (DER or PEM format) to use for encrypting this site\'s interaction with the IdP, if the IdP requires it. The private key must also be provided.';
$string['sp_privatekey'] = 'SP private key';
$string['sp_privatekey_desc'] = 'The Base64-encoded private key (DER or PEM format) that corresponds to the "SP certificate".';
$string['signauthrequests'] = 'Sign authentication requests';
$string['signlogoutrequests'] = 'Sign logout requests';
$string['signlogoutresponses'] = 'Sign logout responses';
$string['signmetadata'] = 'Sign metadata';
$string['username_attribute'] = 'Username attribute';
$string['username_attribute_desc'] = 'The attribute that maps to the Moodle username.';
$string['wantencryptedasserts'] = 'Require encrypted assertions';
$string['wantencryptednameid'] = 'Require encrypted name ID';
$string['wantsignedasserts'] = 'Require signed assertions';
$string['wantsignedmessages'] = 'Require signed messages';
