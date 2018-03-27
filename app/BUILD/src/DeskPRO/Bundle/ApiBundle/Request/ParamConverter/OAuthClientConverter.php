<?php

namespace DeskPRO\Bundle\ApiBundle\Request\ParamConverter;

use DeskPRO\Bundle\AppBundle\Entity\OAuthClient;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OAuthClientConverter.
 */
class OAuthClientConverter implements ParamConverterInterface
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
        $id = $request->attributes->get($configuration->getName());
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('c')
            ->from(OAuthClient::class, 'c')
            ->orWhere(
                'c.id = :client',
                'c.sysName = :client'
            )
            ->setParameter('client', $id)
        ;

        $authClient = $qb->getQuery()->getOneOrNullResult();
        if (!$authClient) {
            // don't throw exception if it's oauth html login form
            // to render custom error in controller
            $options = $configuration->getOptions();
            if (!isset($options['html']) || !$options['html']) {
                throw new NotFoundHttpException("OAuthClient `$id` not found.");
            }
        }

        $request->attributes->set($configuration->getName(), $authClient);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'oauth_client';
    }
}
