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

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\CustomDataPerson;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\BasePerson as BasePersonModel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person as PersonModel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\PersonProfile as PersonProfileModel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\WidgetPerson;
use DeskPRO\Bundle\AppBundle\Serializer\Model\ProfileAvatar;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class PersonHandler.
 */
class PersonHandler extends AbstractEntityHandler
{
    /**
     * @var AgentDataService
     */
    private $agentDataService;

    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $personIds = [];

    /**
     * @var array
     */
    private $lastSeen;

    /**
     * @var array
     */
    private $customData;

    /**
     * @var array
     */
    private $agentData;

    /**
     * Constructor.
     *
     * @param AvatarResolver   $avatarResolver
     * @param AgentDataService $agentDataService
     * @param EntityManager    $em
     */
    public function __construct(AvatarResolver $avatarResolver, AgentDataService $agentDataService, EntityManager $em)
    {
        $this->avatarResolver   = $avatarResolver;
        $this->agentDataService = $agentDataService;
        $this->em               = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return Person::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Person $entity
     */
    protected function createModel($entity, SideloadSerializationContext $context)
    {
        $serializerClass = $context->getMappedClass(Person::class);

        switch ($serializerClass) {
            case PersonProfileModel::class:
                return $this->createPersonProfile($entity);
            case WidgetPerson::class:
                return $this->createWidgetPerson($entity);
            case BasePersonModel::class:
                return $this->createBasePerson($entity);
            default:
                return $this->createPerson($entity);
        }
    }

    /**
     * @param Person $entity
     *
     * @return BasePersonModel
     */
    private function createBasePerson(Person $entity)
    {
        $model = new BasePersonModel($entity, $this->avatarResolver->getAvatarModel($entity));
        $model->setOnline($this->agentDataService->isAgentOnline($entity));

        $this->personIds[$entity->getId()] = true;
        $model->setLastSeen(new CallbackDeferredProperty([$this, 'getLastSeen'], [$entity]));
        $model->setAgentData(new CallbackDeferredProperty([$this, 'getAgentData'], [$entity]));

        return $model;
    }

    /**
     * @param Person $entity
     *
     * @return PersonModel
     */
    private function createPerson(Person $entity)
    {
        $model = new PersonModel($entity, $this->avatarResolver->getAvatarModel($entity));
        $model->setOnline($this->agentDataService->isAgentOnline($entity));

        $this->personIds[$entity->getId()] = true;
        $model->setLastSeen(new CallbackDeferredProperty([$this, 'getLastSeen'], [$entity]));
        $model->setCustomData(new CallbackDeferredProperty([$this, 'getCustomData'], [$entity]));
        $model->setAgentData(new CallbackDeferredProperty([$this, 'getAgentData'], [$entity]));

        return $model;
    }

    /**
     * @param Person $entity
     *
     * @return PersonProfileModel
     */
    private function createPersonProfile(Person $entity)
    {
        $avatar = null;
        if ($blob = $entity->getPictureBlob()) {
            $avatar = new ProfileAvatar(
                $blob->getAuthId(),
                $this->avatarResolver->getAvatarModel($entity)->getUrl(200)
            );
        }

        $model = new PersonProfileModel($entity, $avatar);

        return $model;
    }

    /**
     * @param Person $entity
     *
     * @return WidgetPerson
     */
    private function createWidgetPerson(Person $entity)
    {
        return new WidgetPerson($entity, $this->avatarResolver->getAvatarModel($entity));
    }

    /**
     * @param Person $entity
     *
     * @return string
     */
    public function getLastSeen(Person $entity)
    {
        if (null === $this->lastSeen) {
            /** @var \Application\DeskPRO\DBAL\Connection $connection */
            $connection     = $this->em->getConnection();
            $this->lastSeen = $connection->fetchAllKeyValue(
                'SELECT
                    `s`.`person_id`,
                    MAX(`s`.`date_last`)
                FROM
                  `sessions` AS `s`
                WHERE `s`.`person_id` IN (?)
                GROUP BY (`s`.`person_id`)
                ',
                [array_keys($this->personIds)], [Connection::PARAM_INT_ARRAY]
            );
        }

        if (isset($this->lastSeen[$entity->getId()])) {
            return new \DateTime($this->lastSeen[$entity->getId()]);
        }

        return;
    }

    /**
     * @param Person $entity
     *
     * @return array
     */
    public function getCustomData(Person $entity)
    {
        if (null === $this->customData) {
            $result = $this->em->getRepository(CustomDataPerson::class)->findBy([
                'person' => $this->personIds,
            ]);

            $this->customData = [];
            foreach ($result as $value) {
                $this->customData[$value->getPersonId()][] = $value;
            }
        }

        if (isset($this->customData[$entity->getId()])) {
            return new ArrayCollection($this->customData[$entity->getId()]);
        }

        return new ArrayCollection([]);
    }

    /**
     * @param Person $entity
     *
     * @return AgentData|null
     */
    public function getAgentData(Person $entity)
    {
        if (null === $this->agentData) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('a')
                ->from(AgentData::class, 'a')
                ->join(Person::class, 'p', Join::WITH, 'a.id = p.agentData')
                ->where('p.id IN (:people_ids)')
                ->setParameter('people_ids', $this->personIds)
            ;

            /** @var AgentData[] $result */
            $result = $qb->getQuery()->getResult();

            $this->agentData = [];
            foreach ($result as $value) {
                $this->agentData[$value->getPerson()->getId()] = $value;
            }
        }

        if (isset($this->agentData[$entity->getId()])) {
            return $this->agentData[$entity->getId()];
        }

        return;
    }
}
