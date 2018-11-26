<?php

namespace DeskPRO\Bundle\ReportBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\ReportDashboardShareableLink as ReportDashboardShareableLinkEntity;
use Application\DeskPRO\Entity\ReportDashboardShareableShortUrl;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\ReportBundle\Serializer\Model\ReportDashboardShareableLink as ReportDashboardShareableLinkModel;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ReportDashboardShareableLinkHandler.
 */
class ReportDashboardShareableLinkHandler extends AbstractEntityHandler
{
    /**
     * @var array
     */
    private $shareableLinkIds = [];

    /**
     * @var array
     */
    private $shortUrls;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param EntityManager   $em
     * @param RouterInterface $router
     */
    public function __construct(EntityManager $em, RouterInterface $router)
    {
        $this->em     = $em;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return ReportDashboardShareableLinkEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param ReportDashboardShareableLinkEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $this->shareableLinkIds[] = $entity->getId();

        $model = new ReportDashboardShareableLinkModel($entity);
        $model->setShortUrl(new CallbackDeferredProperty([$this, 'getShortUrl'], [$entity]));
        $model->setPermalink($this->router->generate('go_to_dashboard_link', ['authCode' => $entity->getAuthCode()], UrlGeneratorInterface::ABSOLUTE_URL));

        return $model;
    }

    /**
     * @param ReportDashboardShareableLinkEntity $shareableLink
     *
     * @return array
     */
    public function getShortUrl(ReportDashboardShareableLinkEntity $shareableLink)
    {
        if (null === $this->shortUrls) {
            $this->shortUrls = $this->em->getRepository(ReportDashboardShareableShortUrl::class)->findBy([
                'shareableLink' => $this->shareableLinkIds,
            ]);
        }

        $activeShortUrl = null;
        foreach ($this->shortUrls as $shortUrl) {
            if ($shortUrl->getShareableLink() === $shareableLink && !$shortUrl->isExpired()) {
                $activeShortUrl = $shortUrl;
            }
        }

        return $activeShortUrl;
    }
}
