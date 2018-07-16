<?php

namespace DeskPRO\Bundle\ApiBundle\Request\ParamConverter;

use Application\DeskPRO\Entity\ReportDashboardShareableLink;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ReportDashboardShareableLinkConverter.
 */
class ReportDashboardShareableLinkConverter implements ParamConverterInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $authCode = $request->attributes->get($configuration->getName());
        $link     = $this->em->getRepository(ReportDashboardShareableLink::class)->findOneBy([
            'authCode' => $authCode,
        ]);

        if (!$link) {
            throw new NotFoundHttpException('Shareable link not found.');
        }

        $request->attributes->set($configuration->getName(), $link);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'dashboard_shareable_link';
    }
}
