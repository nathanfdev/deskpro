<?php

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
