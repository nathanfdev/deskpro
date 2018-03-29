<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Entity\TwitterUser;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class TwitterListener.
 */
class TwitterListener
{
    /**
     * @param ContactDataAbstract $entity
     * @param LifecycleEventArgs  $args
     */
    public function postPersist(ContactDataAbstract $entity, LifecycleEventArgs $args)
    {
        if ($entity->getContactType() !== ContactDataAbstract::TYPE_TWITTER) {
            return;
        }

        $this->getConnection($args)->executeUpdate(
            'INSERT IGNORE INTO '.$this->getTableName($entity).
            ' ('.$this->getRefColumnName($entity).', screen_name, is_verified) VALUES (?, ?, 0)',

            array_values($this->getParams($entity))
        );

        if ($entity->getField3() === '') {
            /** @var \Application\DeskPRO\EntityRepository\TwitterUser $twitter_user_repo */
            $twitter_user_repo = $args->getEntityManager()->getRepository(TwitterUser::class);

            $user = $twitter_user_repo->getByScreenName($entity->getField1(), true);
            $entity->setField3($user ? $user->getId() : 0);
        }
    }

    /**
     * @param ContactDataAbstract $entity
     * @param PreUpdateEventArgs  $args
     */
    public function preUpdate(ContactDataAbstract $entity, PreUpdateEventArgs $args)
    {
        if ($entity->getContactType() !== ContactDataAbstract::TYPE_TWITTER) {
            return;
        }
        if (!$args->hasChangedField('field_1')) {
            return;
        }

        $this->getConnection($args)->delete(
            $this->getTableName($entity),
            $this->getParams($entity, $args->getOldValue('field_1'))
        );

        $this->postPersist($entity, $args);
    }

    /**
     * @param ContactDataAbstract $entity
     * @param LifecycleEventArgs  $args
     */
    public function preRemove(ContactDataAbstract $entity, LifecycleEventArgs $args)
    {
        if ($entity->getContactType() !== ContactDataAbstract::TYPE_TWITTER) {
            return;
        }

        $this->getConnection($args)->delete($this->getTableName($entity), $this->getParams($entity));
    }

    /**
     * @param ContactDataAbstract $entity
     *
     * @return string
     */
    protected function getTableName(ContactDataAbstract $entity)
    {
        return $entity instanceof PersonContactData ? 'people_twitter_users' : 'organizations_twitter_users';
    }

    /**
     * @param ContactDataAbstract $entity
     *
     * @return string
     */
    protected function getRefColumnName(ContactDataAbstract $entity)
    {
        return $entity instanceof PersonContactData ? 'person_id' : 'organization_id';
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnection(LifecycleEventArgs $args)
    {
        return $args->getEntityManager()->getConnection();
    }

    /**
     * @param ContactDataAbstract $entity
     * @param string|null         $screen_name
     *
     * @return array
     */
    protected function getParams(ContactDataAbstract $entity, $screen_name = null)
    {
        $ref_column = $this->getRefColumnName($entity);

        return [
            $ref_column   => $entity->getRef()->getId(),
            'screen_name' => $screen_name ?: $entity->getField1(),
        ];
    }
}
