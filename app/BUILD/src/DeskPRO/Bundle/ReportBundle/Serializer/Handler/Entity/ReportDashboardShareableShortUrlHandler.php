<?php

namespace DeskPRO\Bundle\ReportBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\ReportDashboardShareableShortUrl as ReportDashboardShareableShortUrlEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\ReportBundle\Serializer\Model\ReportDashboardShareableShortUrl as ReportDashboardShareableShortUrlModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ReportDashboardShareableShortUrlHandler.
 */
class ReportDashboardShareableShortUrlHandler extends AbstractEntityHandler
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
        return ReportDashboardShareableShortUrlEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param ReportDashboardShareableShortUrlEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new ReportDashboardShareableShortUrlModel($entity);
        $model->setRedirectUrl($this->router->generate('go_to_dashboard_short_url', ['authCode' => $entity->getAuthCode()], UrlGeneratorInterface::ABSOLUTE_URL));

        return $model;
    }
}
