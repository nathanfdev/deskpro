<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\CustomDataOrganization;
use Application\DeskPRO\Entity\LabelOrganization;
use Application\DeskPRO\Entity\Organization as OrganizationEntity;
use Application\DeskPRO\Entity\OrganizationContactData;
use Application\DeskPRO\Entity\OrganizationEmailDomain;
use Application\DeskPRO\Entity\OrganizationPhoneNumber;
use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatDataService;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Organization\Organization as OrganizationModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

/**
 * Class OrganizationHandler.
 */
class OrganizationHandler extends AbstractEntityHandler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ChatDataService
     */
    private $chatData;

    /**
     * @var array
     */
    private $organizationIds = [];

    /**
     * @var array
     */
    private $customData;

    /**
     * @var array
     */
    private $contactData;

    /**
     * @var array
     */
    private $labels;

    /**
     * @var array
     */
    private $emailDomains;

    /**
     * @var array
     */
    private $phoneNumbers;

    /**
     * Constructor.
     *
     * @param EntityManager   $em
     * @param ChatDataService $chatData
     */
    public function __construct(EntityManager $em, ChatDataService $chatData)
    {
        $this->em       = $em;
        $this->chatData = $chatData;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return OrganizationEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param OrganizationEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $this->organizationIds[] = $entity->getId();

        $model = new OrganizationModel($entity, $this->chatData->getChatsCountForOrganization($entity));
        $model->setCustomData(new CallbackDeferredProperty([$this, 'getCustomData'], [$entity]));
        $model->setContactData(new CallbackDeferredProperty([$this, 'getContactData'], [$entity]));
        $model->setLabels(new CallbackDeferredProperty([$this, 'getLabels'], [$entity]));
        $model->setEmailDomains(new CallbackDeferredProperty([$this, 'getEmailDomains'], [$entity]));
        $model->setPhoneNumbers(new CallbackDeferredProperty([$this, 'getPhoneNumbers'], [$entity]));

        return $model;
    }

    /**
     * @param OrganizationEntity $entity
     *
     * @return CustomDataOrganization[]
     */
    public function getCustomData(OrganizationEntity $entity)
    {
        if (null === $this->customData) {
            $result = $this->em->getRepository(CustomDataOrganization::class)->findBy([
                'organization' => $this->organizationIds,
            ]);

            $this->customData = [];
            foreach ($result as $value) {
                $this->customData[$value->getOrganizationId()][] = $value;
            }
        }

        if (isset($this->customData[$entity->getId()])) {
            return new ArrayCollection($this->customData[$entity->getId()]);
        }

        return new ArrayCollection([]);
    }

    /**
     * @param OrganizationEntity $entity
     *
     * @return OrganizationContactData[]
     */
    public function getContactData(OrganizationEntity $entity)
    {
        if (null === $this->contactData) {
            $result = $this->em->getRepository(OrganizationContactData::class)->findBy([
                'organization' => $this->organizationIds,
            ]);

            $this->contactData = [];
            foreach ($result as $value) {
                $this->contactData[$value->getOrganization()->getId()][] = $value;
            }
        }

        if (isset($this->contactData[$entity->getId()])) {
            return new ArrayCollection($this->contactData[$entity->getId()]);
        }

        return new ArrayCollection([]);
    }

    /**
     * @param OrganizationEntity $entity
     *
     * @return LabelOrganization[]
     */
    public function getLabels(OrganizationEntity $entity)
    {
        if (null === $this->labels) {
            $result = $this->em->getRepository(LabelOrganization::class)->findBy([
                'organization' => $this->organizationIds,
            ]);

            $this->labels = [];
            foreach ($result as $label) {
                $this->labels[$label->getOrganization()->getId()][] = $label;
            }
        }

        if (isset($this->labels[$entity->getId()])) {
            return $this->labels[$entity->getId()];
        }

        return [];
    }

    /**
     * @param OrganizationEntity $entity
     *
     * @return OrganizationEmailDomain[]
     */
    public function getEmailDomains(OrganizationEntity $entity)
    {
        if (null === $this->emailDomains) {
            $result = $this->em->getRepository(OrganizationEmailDomain::class)->findBy([
                'organization' => $this->organizationIds,
            ]);

            $this->emailDomains = [];
            foreach ($result as $emailDomain) {
                $this->emailDomains[$emailDomain->getOrganization()->getId()][] = $emailDomain;
            }
        }

        if (isset($this->emailDomains[$entity->getId()])) {
            return $this->emailDomains[$entity->getId()];
        }

        return [];
    }

    /**
     * @param OrganizationEntity $entity
     *
     * @return OrganizationPhoneNumber[]|ArrayCollection
     */
    public function getPhoneNumbers(OrganizationEntity $entity)
    {
        if (null === $this->phoneNumbers) {
            $result = $this->em->getRepository(OrganizationPhoneNumber::class)->findBy([
                'organization' => $this->organizationIds,
            ]);

            $this->phoneNumbers = [];
            foreach ($result as $value) {
                $this->phoneNumbers[$value->getOrganization()->getId()][] = $value;
            }
        }

        if (isset($this->phoneNumbers[$entity->getId()])) {
            return new ArrayCollection($this->phoneNumbers[$entity->getId()]);
        }

        return new ArrayCollection([]);
    }
}
