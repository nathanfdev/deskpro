<?php

namespace DeskPRO\Bundle\ApiBundle\OAuth;

use Doctrine\ORM\EntityManager;
use OAuth2\Model\IOAuth2Client;
use OAuth2\OAuth2 as BaseOAuth2;

/**
 * {@inheritdoc}
 */
class OAuth2 extends BaseOAuth2
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Return AccessToken in the format of Deskpro API token.
     *
     * {@inheritdoc}
     */
    public function createAccessToken(IOAuth2Client $client, $data, $scope = null, $access_token_lifetime = null, $issue_refresh_token = true, $refresh_token_lifetime = null)
    {
        $token = parent::createAccessToken($client, $data, $scope, $access_token_lifetime, $issue_refresh_token, $refresh_token_lifetime);

        // length of api_token.token field
        $accessToken = substr($token['access_token'], 0, 25);

        $apiTokenEntity = $this->em->getRepository('DeskPRO:ApiToken')->findOneByToken($accessToken);
        if (!$apiTokenEntity) {
            throw new \Exception(sprintf('[OAuth2] Can\'t find ApiToken by token `%s`', $accessToken));
        }

        $token['access_token'] = sprintf('%s:%s', $apiTokenEntity->getId(), $accessToken);

        return $token;
    }
}
