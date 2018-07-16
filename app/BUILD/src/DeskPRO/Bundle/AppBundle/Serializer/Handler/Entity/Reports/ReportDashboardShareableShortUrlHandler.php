<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Reports;

use Application\DeskPRO\Entity\ReportDashboardShareableShortUrl as ReportDashboardShareableShortUrlEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Reports\ReportDashboardShareableShortUrl as ReportDashboardShareableShortUrlModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
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
