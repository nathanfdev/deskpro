<?php

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
