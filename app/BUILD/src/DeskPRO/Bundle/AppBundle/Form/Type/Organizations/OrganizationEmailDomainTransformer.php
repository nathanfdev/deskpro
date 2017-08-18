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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\Organizations;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationEmailDomain;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class OrganizationEmailDomainTransformer.
 */
class OrganizationEmailDomainTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Organization
     */
    private $organization;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param Organization  $organization
     */
    public function __construct(EntityManager $em, Organization $organization)
    {
        $this->em           = $em;
        $this->organization = $organization;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        /** @var OrganizationEmailDomain[] $value */
        if (!is_array($value) && !$value instanceof \Traversable) {
            return [];
        }

        $result = [];
        foreach ($value as $org_domain) {
            $result[] = $org_domain->getDomain();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$this->organization) {
            throw new \InvalidArgumentException('Organization is not defined.');
        }

        $repository = $this->em->getRepository(OrganizationEmailDomain::class);
        $result     = [];

        foreach ($value as $email_domain) {
            $entity = $repository->findOneBy([
                'domain'       => $email_domain,
                'organization' => $this->organization->getId(),
            ]);

            if (!$entity) {
                $entity = new OrganizationEmailDomain();
                $entity
                    ->setDomain($email_domain)
                    ->setOrganization($this->organization)
                ;
            }

            $result[] = $entity;
        }

        return $result;
    }
}
