<?php

$string['auth_simplesamldescription'] = 'Simplified SAML2 authentication plugin';
$string['configgeneral'] = 'General';
$string['configidp'] = 'Identity Provider (IdP)';
$string['defaultidpname'] = 'SAML';
$string['errorbadconfiguration'] = 'The plugin is not configured correctly.';
$string['errornotauthenticated'] = 'Authentication was not successful.';
$string['errornotconfigured'] = 'The plugin has not been fully configured.';
$string['idp_entityid'] = 'Entity ID';
$string['idp_entityid_desc'] = 'The entity ID of the SAML2 Identity Provider.';
$string['idp_name'] = 'Display name';
$string['idp_name_desc'] = 'A user-friendly name for the Identity Provider for display on the login page.';
$string['idp_ssourl'] = 'SSO URL';
$string['idp_ssourl_desc'] = 'The URL of the Single Sign On service.';
$string['idp_slourl'] = 'SLO URL';
$string['idp_slourl_desc'] = 'The URL of the Single Log Out service.';
$string['idp_cert'] = 'Certificate';
$string['idp_cert_desc'] = 'Base64-encoded X.509 certificate that signs the assertion (begins <code>MII...</code>). You can provide this, or just the \'Certificate fingerprint\'.';
$string['idp_certfingerprint'] = 'Certificate fingerprint';
$string['idp_certfingerprint_desc'] = 'The fingerprint of the certificate that signs the assertion (formatted <code>XX:XX:XX:...:XX</code>). This is a simpler method for validating the signature if you don\'t wish to use the full certificate.';
$string['nomcryptnotice'] = 'The PHP mcrypt module is not enabled so encryption, decryption, and signing functions are not available.';
$string['pluginname'] = 'Simplified SAML';
$string['prefersso'] = 'Prefer SSO';
$string['prefersso_desc'] = 'Whether to automatically send the user for sign on when the login page is accessed. When this is enabled, the redirection can be avoided with <code>{$a}</code>.';
$string['username_attribute'] = 'Username attribute';
$string['username_attribute_desc'] = 'The attribute that maps as the Moodle username.';
