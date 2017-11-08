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
