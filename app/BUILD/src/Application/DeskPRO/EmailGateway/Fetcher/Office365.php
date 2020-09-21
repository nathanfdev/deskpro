<?php

namespace Application\DeskPRO\EmailGateway\Fetcher;

use Application\DeskPRO\App;
use League\OAuth2\Client\Provider\GenericProvider;
use Symfony\Component\Routing\Generator\UrlGenerator;

class Office365
{
    /**
     * @param string $id
     * @param string $secret
     *
     * @return GenericProvider
     */
    public static function createOauthClient($id, $secret)
    {
        return new GenericProvider([
            'clientId'                => $id,
            'clientSecret'            => $secret,
            'redirectUri'             => App::get('router')->generate('office365_token', [], UrlGenerator::ABSOLUTE_URL),
            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                  => implode(' ', [
                'offline_access',
                'https://outlook.office.com/IMAP.AccessAsUser.All',
                'https://outlook.office.com/POP.AccessAsUser.All',
                'https://outlook.office.com/SMTP.Send',
                'https://outlook.office.com/EWS.AccessAsUser.All',
            ]),
        ]);
    }
}
