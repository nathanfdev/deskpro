<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Adapter as UsersourceAdapter;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Usersource as UsersourceModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use deskpro_us_jwt\Usersource\Adapter\Jwt as JwtAdapter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
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
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $usersourceIds = [];

    /**
     * @var array
     */
    private $brandsMapping;

    /**
     * @var array
     */
    private $brands;

    /**
     * ThemeSetAssetHandler constructor.
     *
     * @param RouterInterface  $router
     * @param DeskproContainer $container
     * @param EntityManager    $em
     */
    public function __construct(RouterInterface $router, DeskproContainer $container, EntityManager $em)
    {
        $this->router    = $router;
        $this->container = $container;
        $this->em        = $em;
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
        $this->usersourceIds[] = $entity->getId();

        $model = new UsersourceModel($entity, $this->getDisplayType($entity), $this->getDisplayOptions($entity, $context));
        $model->setBrands(new CallbackDeferredProperty([$this, 'getBrands'], [$entity]));

        return $model;
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

    /**
     * @param Usersource $entity
     *
     * @return Brand[]|ArrayCollection
     */
    public function getBrands(Usersource $entity)
    {
        $this->loadBrands();

        if (isset($this->brandsMapping[$entity->getId()])) {
            $brands = [];
            foreach ($this->brandsMapping[$entity->getId()] as $brandId => $_) {
                if (isset($this->brands[$brandId])) {
                    $brands[] = $this->brands[$brandId];
                }
            }

            return new ArrayCollection($brands);
        }

        return new ArrayCollection([]);
    }

    private function loadBrands()
    {
        if (null === $this->brandsMapping) {
            $connection = $this->em->getConnection();
            $result     = $connection->executeQuery(
                'SELECT * FROM usersource_to_brand WHERE usersource_id IN (:usersource_ids)',
                ['usersource_ids' => $this->usersourceIds],
                ['usersource_ids' => Connection::PARAM_INT_ARRAY]
            )->fetchAll();

            $brandIds            = [];
            $this->brandsMapping = [];

            foreach ($result as $value) {
                $brandIds[$value['brand_id']]                                     = true;
                $this->brandsMapping[$value['usersource_id']][$value['brand_id']] = true;
            }

            $result = $this->em->getRepository(Brand::class)->findBy([
                'id' => array_keys($brandIds),
            ]);

            $this->brands = [];
            foreach ($result as $brand) {
                $this->brands[$brand->getId()] = $brand;
            }
        }
    }
}
