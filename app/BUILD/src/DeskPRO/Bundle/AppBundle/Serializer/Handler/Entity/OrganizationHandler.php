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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\CustomDataOrganization;
use Application\DeskPRO\Entity\LabelOrganization;
use Application\DeskPRO\Entity\Organization as OrganizationEntity;
use Application\DeskPRO\Entity\OrganizationContactData;
use Application\DeskPRO\Entity\OrganizationEmailDomain;
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
    protected function createModel($entity, SideloadSerializationContext $context)
    {
        $this->organizationIds[] = $entity->getId();

        $model = new OrganizationModel($entity, $this->chatData->getChatsCountForOrganization($entity));
        $model->setCustomData(new CallbackDeferredProperty([$this, 'getCustomData'], [$entity]));
        $model->setContactData(new CallbackDeferredProperty([$this, 'getContactData'], [$entity]));
        $model->setLabels(new CallbackDeferredProperty([$this, 'getLabels'], [$entity]));
        $model->setEmailDomains(new CallbackDeferredProperty([$this, 'getEmailDomains'], [$entity]));

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
}
