<?php

namespace DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias as ObjectAliasEntity;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\ObjectAlias as ObjectAliasDomain;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Exception\InvalidArgumentException;

/**
 * A service responsible for making sense of "Fields". Usually, special strings (see FormFields class), need to be
 * expanded into more information or fetched from the database.
 */
class CustomFieldManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param BrandStack    $brandStack
     */
    public function __construct(EntityManager $em, BrandStack $brandStack)
    {
        $this->em         = $em;
        $this->brandStack = $brandStack;
    }

    /**
     * @param string $customFieldType
     *
     * @throws \Exception
     *
     * @return FieldNameResolver
     */
    public function getFieldNameResolver($customFieldType)
    {
        try {
            $aliasFieldNameStrategy = null;
            $aliasType              = ObjectAliasEntity\Aliases::resolveAliasType($customFieldType, $this->em);
            if (empty($aliasType)) {
                throw new \DomainException('no alias type found');
            }

            /** @var ObjectAliasEntity\Repository $repository */
            $repository = $this->em->getRepository($aliasType);
            $strategies = [
                new ObjectAliasDomain\FieldIdResolvingStrategy(),
                new ObjectAliasDomain\DefaultIdResolvingStrategy($repository),
            ];

            return new FieldNameResolver($strategies);
        } catch (\Exception $e) {
            throw FieldNameResolverException::missingStrategy($customFieldType, $e);
        }
    }

    /**
     * @return CustomDefPerson[]
     */
    public function getAvailablePersonDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefPerson::class);
    }

    /**
     * @return CustomDefFeedback[]
     */
    public function getAvailableFeedbackDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefFeedback::class);
    }

    /**
     * @return CustomDefOrganization[]
     */
    public function getAvailableOrganizationDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefOrganization::class);
    }

    /**
     * @return CustomDefTicket[]
     */
    public function getAvailableTicketDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefTicket::class);
    }

    /**
     * @return CustomDefChat[]
     */
    public function getAvailableChatDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefChat::class);
    }

    /**
     * @param LayoutField $layoutField
     *
     * @return CustomDefAbstract|null
     */
    public function getCustomDefForLayoutField(LayoutField $layoutField)
    {
        $fieldId = $layoutField->getFieldId();

        switch ($layoutField->getFieldType()) {
            case FormFields::TICKET_FIELD:
                return $this->getCustomTicketFieldById($fieldId);
            case FormFields::USER_FIELD:
                return $this->getCustomTicketFieldById($fieldId);
            case FormFields::ORG_FIELD:
                return $this->getCustomTicketFieldById($fieldId);
            default:
                return false;
        }
    }

    /**
     * @param $id
     *
     * @return CustomDefTicket
     */
    public function getCustomTicketFieldById($id)
    {
        return $this->em->getRepository(CustomDefTicket::class)->find($id);
    }

    /**
     * @param $id
     *
     * @return CustomDefPerson
     */
    public function getCustomPersonFieldById($id)
    {
        return $this->em->getRepository(CustomDefPerson::class)->find($id);
    }

    /**
     * @param $id
     *
     * @return CustomDefOrganization
     */
    public function getCustomOrganizationFieldById($id)
    {
        return $this->em->getRepository(CustomDefOrganization::class)->find($id);
    }

    /**
     * per-user/per-organization special custom fields.
     *
     * @param $id
     *
     * @return \Application\DeskPRO\Entity\CustomFieldDefinition
     */
    public function getCustomPerFieldById($id)
    {
        return $this->em->getRepository(CustomFieldDefinition::class)->find($id);
    }

    /**
     * @param string $entityType
     *
     * @return CustomDefAbstract[]
     */
    private function getAvailableCustomDefs($entityType)
    {
        $qb = $this->em
            ->createQueryBuilder()
            ->select('f')
            ->from($entityType, 'f')
            ->where(
                'f.is_user_enabled = true',
                'f.is_enabled = true',
                'f.handler_class IS NOT NULL'
            )
            ->orderBy('f.display_order')
        ;

        if (in_array($entityType, [CustomDefFeedback::class])) {
            $currentBrand = $this->brandStack->getActive()->getBrand();
            if ($currentBrand && $currentBrand->getId()) {
                $qb->andWhere('f.brand = :brand');
                $qb->setParameter('brand', $currentBrand);
            }
        }

        $result = new ArrayCollection($qb->getQuery()->getResult());
        $result = $result->filter(function (CustomDefAbstract $def) {
            if ($def->isChoiceType() && !$def->hasChildren()) {
                return false;
            }

            return true;
        });

        return $result;
    }

    /**
     * @param $context
     *
     * @throws \Exception
     *
     * @return ArrayCollection
     */
    public function getAvailableContextualDefs($context)
    {
        if (!$context instanceof Person && !$context instanceof Organization) {
            throw new InvalidArgumentException('Context must be a type of Person or Organization');
        }

        $qb = $this->em
            ->createQueryBuilder()
            ->select('c')
            ->from(CustomFieldDefinition::class, 'c')
            ->where(
                'c.is_user_enabled = true',
                'c.is_enabled = true',
                'c.parent is null',
                'c.context_class = :class'
            )
            ->setParameter('class', ClassUtils::getClass($context))
            ->orderBy('c.display_order')
        ;
        $result = new ArrayCollection($qb->getQuery()->getResult());

        return $result;
    }

    /**
     * @param $context
     *
     * @throws \Exception
     *
     * @return ArrayCollection collection of {parent_id => children collection} for current context
     */
    public function getAvailableContextualDefsChildren($context)
    {
        if (!$context instanceof Person && !$context instanceof Organization) {
            throw new InvalidArgumentException('Context must be a type of Person or Organization');
        }

        // def children for current context
        $collection = new ArrayCollection();

        if (!$context->getId()) {
            return $collection;
        }

        $qb = $this->em
            ->createQueryBuilder()
            ->select('c, p')
            ->from(CustomFieldDefinition::class, 'c')
            ->join('c.parent', 'p')
            ->where(
                'c.is_user_enabled = true',
                'c.is_enabled = true',
                'c.context_class = :class',
                'c.context_id = :id'
            )
            ->setParameter('class', ClassUtils::getClass($context))
            ->setParameter('id', $context->getId())
            ->orderBy('c.display_order')
        ;

        $children = $qb->getQuery()->getResult();

        // build child tree
        foreach ($children as $child) {
            if (!$child->parent) {
                continue;
            }
            $pid = $child->parent['id'];
            if (!$sub = $collection->get($pid)) {
                $sub = new ArrayCollection();
                $collection->set($pid, $sub);
            }
            $sub->add($child);
        }

        return $collection;
    }
}
