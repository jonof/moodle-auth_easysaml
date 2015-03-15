<?php

$string['auth_simplesamldescription'] = 'Simplified SAML2 authentication plugin.';
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
$string['idp_slourl'] = 'SLO URL';
$string['idp_slourl_desc'] = 'The URL of the Single Log Out service using "HTTP-Redirect" binding.';
$string['idp_cert'] = 'Certificate';
$string['idp_cert_desc'] = 'The Base64-encoded X.509 certificate (DER or PEM format) that signs SAML IdP responses. You can provide this or the \'Certificate fingerprint\'.';
$string['idp_certfingerprint'] = 'Certificate fingerprint';
$string['idp_certfingerprint_desc'] = 'The <strong>SHA1</strong> fingerprint of the certificate that signs SAML IdP responses (formatted <code>XX:XX:XX:...:XX</code>). This is a simpler method for validating the signature if you don\'t wish to use the full certificate.';
$string['nomcryptnotice'] = 'The PHP mcrypt module is not enabled so encryption, decryption, and signing functions are not available.';
$string['pluginname'] = 'Simplified SAML';
$string['prefersso'] = 'Prefer SSO';
$string['prefersso_desc'] = 'Whether to automatically send the user for sign on when the login page is accessed. When this is enabled, the redirection can be avoided with <code>{$a}</code>.';
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
