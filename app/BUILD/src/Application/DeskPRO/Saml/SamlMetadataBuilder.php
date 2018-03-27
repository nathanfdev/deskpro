<?php

namespace Application\DeskPRO\Saml;

use DateTime;

/**
 * This is taken from the onelogin/saml-php library, and changed so we can manipulate the metadata.
 *
 * We added the $custom_xml argument, which simply injects directly into the metadata XML
 */
class SamlMetadataBuilder extends \OneLogin_Saml2_Metadata
{
    const TIME_VALID  = 172800;  // 2 days
    const TIME_CACHED = 604800; // 1 week

    /**
     * Generates the metadata of the SP based on the settings.
     *
     * @param string        $sp            The SP data
     * @param bool|string   $authnsign     authnRequestsSigned attribute
     * @param bool|string   $wsign         wantAssertionsSigned attribute
     * @param DateTime|null $validUntil    Metadata's valid time
     * @param int|null      $cacheDuration Duration of the cache in seconds
     * @param array         $contacts      Contacts info
     * @param array         $organization  Organization ingo
     * @param array         $attributes
     * @param string        $custom_xml
     *
     * @return string SAML Metadata XML
     */
    public static function builder($sp, $authnsign = false, $wsign = false, $validUntil = null, $cacheDuration = null, $contacts = [], $organization = [], $attributes = [], $custom_xml = '')
    {
        if (!isset($validUntil)) {
            $validUntil = time() + self::TIME_VALID;
        }
        $validUntilTime = gmdate('Y-m-d\TH:i:s\Z', $validUntil);

        if (!isset($cacheDuration)) {
            $cacheDuration = self::TIME_CACHED;
        }

        $sls = '';

        if (isset($sp['singleLogoutService'])) {
            $sls = <<<SLS_TEMPLATE
        <md:SingleLogoutService Binding="{$sp['singleLogoutService']['binding']}"
                                Location="{$sp['singleLogoutService']['url']}" />

SLS_TEMPLATE;
        }

        if ($authnsign) {
            $strAuthnsign = 'true';
        } else {
            $strAuthnsign = 'false';
        }

        if ($wsign) {
            $strWsign = 'true';
        } else {
            $strWsign = 'false';
        }

        $strOrganization = '';
        if (!empty($organization)) {
            $organizationInfo = [];
            foreach ($organization as $lang => $info) {
                $organizationInfo[] = <<<ORGANIZATION

    <md:Organization>
       <md:OrganizationName xml:lang="{$lang}">{$info['name']}</md:OrganizationName>
       <md:OrganizationDisplayName xml:lang="{$lang}">{$info['displayname']}</md:OrganizationDisplayName>
       <md:OrganizationURL xml:lang="{$lang}">{$info['url']}</md:OrganizationURL>
    </md:Organization>
ORGANIZATION;
            }
            $strOrganization = implode("\n", $organizationInfo);
        }

        $strContacts = '';
        if (!empty($contacts)) {
            $contactsInfo = [];
            foreach ($contacts as $type => $info) {
                $contactsInfo[] = <<<CONTACT
    <md:ContactPerson contactType="{$type}">
        <md:GivenName>{$info['givenName']}</md:GivenName>
        <md:EmailAddress>{$info['emailAddress']}</md:EmailAddress>
    </md:ContactPerson>
CONTACT;
            }
            $strContacts = "\n".implode("\n", $contactsInfo);
        }

        $metadata = <<<METADATA_TEMPLATE
<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
                     validUntil="{$validUntilTime}"
                     cacheDuration="PT{$cacheDuration}S"
                     entityID="{$sp['entityId']}">
    <md:SPSSODescriptor AuthnRequestsSigned="{$strAuthnsign}" WantAssertionsSigned="{$strWsign}" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
{$sls}        <md:NameIDFormat>{$sp['NameIDFormat']}</md:NameIDFormat>
        <md:AssertionConsumerService Binding="{$sp['assertionConsumerService']['binding']}"
                                     Location="{$sp['assertionConsumerService']['url']}"
                                     index="1" />
        {$custom_xml}
    </md:SPSSODescriptor>{$strOrganization}{$strContacts}
</md:EntityDescriptor>
METADATA_TEMPLATE;

        return $metadata;
    }
}
