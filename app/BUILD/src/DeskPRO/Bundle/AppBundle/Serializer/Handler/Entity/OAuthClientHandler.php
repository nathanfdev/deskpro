<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\OAuthClient;
use DeskPRO\Bundle\AppBundle\Serializer\Model\OAuthClient as OAuthClientModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class OAuthClientHandler.
 */
class OAuthClientHandler extends AbstractEntityHandler
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return OAuthClient::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param OAuthClient $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $tokenEndpoint = $this->router->generate('api_oauth_get_token', [], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($entity->getContext() === OAuthClient::CONTEXT_USER) {
            $authEndpoint = $this->router->generate('api_oauth_login', [
                'context'    => 'user',
                'authClient' => $entity->getSysName() ?: $entity->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        } elseif ($entity->getContext() === OAuthClient::CONTEXT_AGENT) {
            $authEndpoint = $this->router->generate('api_oauth_login', [
                'context'    => 'agent',
                'authClient' => $entity->getSysName() ?: $entity->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $authEndpoint = '';
        }

        return new OAuthClientModel($entity, $authEndpoint, $tokenEndpoint);
    }
}
