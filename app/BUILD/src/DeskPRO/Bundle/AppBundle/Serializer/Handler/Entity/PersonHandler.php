<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person as SerializedPerson;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\PersonProfile;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\WidgetPerson;
use DeskPRO\Bundle\AppBundle\Serializer\Model\ProfileAvatar;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;

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
    private $lastSeen = [];

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
            case PersonProfile::class:
                return $this->createPersonProfile($entity);
            case WidgetPerson::class:
                return $this->createWidgetPerson($entity);
            default:
                return $this->createPerson($entity);
        }
    }

    /**
     * @param Person $entity
     *
     * @return SerializedPerson
     */
    private function createPerson(Person $entity)
    {
        $model = new SerializedPerson($entity);
        $model
            ->setAvatar($this->avatarResolver->getAvatarModel($entity))
            ->setOnline($this->agentDataService->isAgentOnline($entity))
        ;

        $this->personIds[$entity->getId()] = true; // (sic!) It's faster then doing array_unique about 12-13 times
        $model->setLastSeen(new CallbackDeferredProperty([$this, 'getLastSeen'], [$entity]));

        return $model;
    }

    /**
     * @param Person $entity
     *
     * @return string
     */
    public function getLastSeen(Person $entity)
    {
        if (!$this->lastSeen) {
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
                [implode(',', array_keys($this->personIds))]
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
     * @return \DeskPRO\Bundle\AppBundle\Serializer\Model\Person\PersonProfile
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

        $model = new PersonProfile($entity, $avatar);

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
}
