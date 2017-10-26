<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppStoreBundle\Oauth1;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\Oauth1ClientCredentials;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\Oauth1ProviderDetails;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Server\User;
use League\OAuth1\Client\Signature\SignatureInterface;

class Client extends Server
{
    /** @var Oauth1ProviderDetails */
    private $providerDetails;

    /**
     * @param Oauth1ClientCredentials $credentials
     *
     * @return ClientCredentialsInterface
     */
    private static function convertClientCredentials(Oauth1ClientCredentials $credentials)
    {
        /** @var ClientCredentialsInterface $clientCredentials */
        $clientCredentials = null;
        $rsaPrivateKey     = $credentials->getRSAPrivateKey();

        if ($rsaPrivateKey) {
            $clientCredentials = new RsaClientCredentials();
            $clientCredentials->setRsaPrivateKey($rsaPrivateKey);
        } else {
            $clientCredentials = new ClientCredentials();
        }
        $clientCredentials->setCallbackUri($credentials->getUrlRedirect());
        $clientCredentials->setSecret($credentials->getClientSecret());
        $clientCredentials->setIdentifier($credentials->getClientId());

        return $clientCredentials;
    }

    /**
     * @param Oauth1ProviderDetails   $providerDetails
     * @param Oauth1ClientCredentials $credentials
     *
     * @return Client
     */
    public static function factory(
        Oauth1ProviderDetails $providerDetails,
        Oauth1ClientCredentials $credentials
    ) {
        /** @var ClientCredentialsInterface $clientCredentials */
        $clientCredentials = self::convertClientCredentials($credentials);

        return new self($providerDetails, $clientCredentials);
    }

    /**
     * Create a new server instance.
     *
     * @param Oauth1ProviderDetails            $providerDetails
     * @param ClientCredentialsInterface|array $clientCredentials
     * @param SignatureInterface               $signature
     */
    public function __construct(Oauth1ProviderDetails $providerDetails, $clientCredentials, SignatureInterface $signature = null)
    {
        parent::__construct($clientCredentials, $signature);
        $this->providerDetails = $providerDetails;
    }

    /**
     * Get the URL for retrieving temporary credentials.
     *
     * @return string
     */
    public function urlTemporaryCredentials()
    {
        return $this->providerDetails->getUrlTemporaryCredentials();
    }

    /**
     * Get the URL for redirecting the resource owner to authorize the client.
     *
     * @return string
     */
    public function urlAuthorization()
    {
        return $this->providerDetails->getUrlAuthorization();
    }

    /**
     * Get the URL retrieving token credentials.
     *
     * @return string
     */
    public function urlTokenCredentials()
    {
        return $this->providerDetails->getUrlTokenCredentials();
    }

    /**
     * Get the URL for retrieving user details.
     *
     * @return string
     */
    public function urlUserDetails()
    {
        return $this->providerDetails->getUrlUserDetails();
    }

    /**
     * Take the decoded data from the user details URL and convert
     * it to a User object.
     *
     * @param mixed            $data
     * @param TokenCredentials $tokenCredentials
     *
     * @return User
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        return $data;
    }

    /**
     * Take the decoded data from the user details URL and extract
     * the user's UID.
     *
     * @param mixed            $data
     * @param TokenCredentials $tokenCredentials
     *
     * @return string|int
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }

    /**
     * Take the decoded data from the user details URL and extract
     * the user's email.
     *
     * @param mixed            $data
     * @param TokenCredentials $tokenCredentials
     *
     * @return string
     */
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }

    /**
     * Take the decoded data from the user details URL and extract
     * the user's screen name.
     *
     * @param mixed            $data
     * @param TokenCredentials $tokenCredentials
     *
     * @return string
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }
}
