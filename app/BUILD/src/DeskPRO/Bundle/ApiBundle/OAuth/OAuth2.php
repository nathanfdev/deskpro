<?php

namespace DeskPRO\Bundle\ApiBundle\OAuth;

use Application\DeskPRO\Entity\ApiToken;
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
     * {@inheritdoc}
     */
    protected function genAccessToken()
    {
        $token = new ApiToken();

        return $token->getToken();
    }

    /**
     * Return AccessToken in the format of Deskpro API token id:tokenstring.
     *
     * {@inheritdoc}
     */
    public function createAccessToken(IOAuth2Client $client, $data, $scope = null, $access_token_lifetime = null, $issue_refresh_token = true, $refresh_token_lifetime = null)
    {
        $tokenData   = parent::createAccessToken($client, $data, $scope, $access_token_lifetime, $issue_refresh_token, $refresh_token_lifetime);
        $accessToken = $tokenData['access_token'];

        $apiTokenEntity = $this->em->getRepository('DeskPRO:ApiToken')->findOneByToken($accessToken);
        if (!$apiTokenEntity) {
            throw new \Exception(sprintf('[OAuth2] Can\'t find ApiToken by token `%s`', $accessToken));
        }

        $tokenData['access_token'] = sprintf('%s:%s', $apiTokenEntity->getId(), $accessToken);

        return $tokenData;
    }
}
