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

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Adapter as UsersourceAdapter;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Usersource as UsersourceModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use deskpro_us_jwt\Usersource\Adapter\Jwt as JwtAdapter;
use Orb\Auth\Adapter\CallbackInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class UsersourceHandler.
 */
class UsersourceHandler extends AbstractEntityHandler
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * ThemeSetAssetHandler constructor.
     *
     * @param RouterInterface  $router
     * @param DeskproContainer $container
     */
    public function __construct(RouterInterface $router, DeskproContainer $container)
    {
        $this->router    = $router;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return Usersource::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Usersource $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new UsersourceModel($entity, $this->getDisplayType($entity), $this->getDisplayOptions($entity, $context));
    }

    /**
     * Display type.
     *
     * @param Usersource $entity
     *
     * @return string
     */
    public function getDisplayType(Usersource $entity)
    {
        switch ($entity->getSourceType()) {
            case UsersourceAdapter\GooglePlus::class:
            case UsersourceAdapter\Google::class:
            case UsersourceAdapter\Facebook::class:
            case UsersourceAdapter\Twitter::class:
                return 'social';
            case JwtAdapter::class:
            case UsersourceAdapter\Saml::class:
                return 'button';
            default:
                return 'none';
        }
    }

    /**
     * Display options.
     *
     * @param Usersource                   $entity
     * @param SideloadSerializationContext $context
     *
     * @return array
     */
    public function getDisplayOptions(Usersource $entity, SideloadSerializationContext $context)
    {
        $options = [];

        if ($entity->getOption('login_custom_text')) {
            $options['button_label'] = $entity->getOption('login_custom_text');
        } else {
            // social type fallback
            if (in_array($entity->getSourceType(), [UsersourceAdapter\GooglePlus::class, UsersourceAdapter\Google::class])) {
                $options['button_label'] = 'Log in with Google';
            } elseif ($entity->getSourceType() === UsersourceAdapter\Facebook::class) {
                $options['button_label'] = 'Log in with Facebook';
            } elseif ($entity->getSourceType() === UsersourceAdapter\Twitter::class) {
                $options['button_label'] = 'Log in with Twitter';
            }
        }

        $adapter = $this->container->getSystemService('usersource_auth_adapter_factory')->getAuthAdapter($entity);
        if ($adapter instanceof CallbackInterface) {
            $options['login_url'] = $this->router->generate(
                'deskpro_api_authentication_apitokens_usersourcelogin',
                [
                    'usersource' => $entity->getId(),
                    'format'     => $context->getRequest()->query->get('format', 'default'),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL)
            ;
        }

        return $options;
    }
}
