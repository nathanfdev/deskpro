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
    protected function createModel($entity, SideloadSerializationContext $context)
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
