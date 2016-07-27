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

namespace DeskPRO\Bundle\AppBundle\CustomField\Context;

use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Doctrine\ORM\EntityManager;

/**
 * Class CustomPerFieldManager.
 */
class CustomPerFieldManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Because the "owner" property of CustomFieldData requires that we have that entity flushed (so it
     * has an ID) before we can save this to the database, we have a method to add to the queue and a
     * method to flush the queue (once the owner (ticket for ex.) have been saved successfully).
     *
     * @var array
     */
    private $save_queue;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->save_queue = [];
        $this->em         = $em;
    }

    /**
     * @param $definition_id
     * @param CustomFieldContext $context
     *
     * @return \Application\DeskPRO\Entity\CustomFieldDefinition|null
     */
    public function getCustomPerFieldDefinition($definition_id, CustomFieldContext $context)
    {
        if (!$def = $this->em->getRepository(CustomFieldDefinition::class)->find($definition_id)) {
            return;
        }

        // make sure we know the owner from the context
        if (!$context->getOwner($def->getOwnerClass())) {
            return;
        }

        // make sure we know the context (person/org/etc) from the context
        if (!$context->getContext($def->getContextClass())) {
            return;
        }

        return $def;
    }

    /**
     * @param CustomFieldDefinition $def
     * @param $title
     * @param $context_id
     *
     * @return CustomFieldDefinition
     */
    public function createNewOption(CustomFieldDefinition $def, $title, $context_id)
    {
        $child             = $def->spawnChild($title);
        $child->context_id = $context_id;

        return $child;
    }

    /**
     * @param CustomFieldDefinition $def
     * @param CustomFieldContext    $context
     *
     * @return CustomFieldData
     */
    public function getOrCreateCustomPerFieldData(CustomFieldDefinition $def, CustomFieldContext $context)
    {
        if ($data = $this->getCustomPerFieldData($def, $context)) {
            return $data;
        }

        $owner = $context->getOwner($def->getOwnerClass());

        $data                  = new CustomFieldData();
        $data->definition      = $def;
        $data->root_definition = $this->findRootDefinition($def);
        $data->owner           = $owner;

        return $data;
    }

    /**
     * @param CustomFieldData $data
     */
    public function saveDataToQueue(CustomFieldData $data)
    {
        $this->save_queue[] = $data;
    }

    /**
     * @param bool $flush
     */
    public function flushDataQueue($flush = true)
    {
        $data = [];

        foreach ($this->save_queue as $queue_data) {
            $this->em->persist($queue_data);
            $data[] = $queue_data;
        }

        if ($flush) {
            $this->em->flush($data);
        }
    }

    /**
     * @param CustomFieldDefinition $def
     * @param CustomFieldContext    $context
     *
     * @return array|null
     */
    public function getCustomPerFieldData(CustomFieldDefinition $def, CustomFieldContext $context)
    {
        $owner      = $context->getOwner($def->getOwnerClass());
        $contextual = $context->getOwner($def->getOwnerClass());

        if (!$owner || !$contextual) {
            return;
        }

        /** @var \Application\DeskPRO\EntityRepository\CustomFieldData $repository */
        $repository = $this->em->getRepository(CustomFieldData::class);
        $result     = $repository->getFieldData($def, $owner);

        return is_array($result) ? current($result) : null;
    }

    /**
     * @param CustomFieldDefinition $def
     *
     * @return CustomFieldDefinition
     */
    protected function findRootDefinition(CustomFieldDefinition $def)
    {
        if ($def->parent) {
            return $this->findRootDefinition($def->parent);
        }

        return $def;
    }

    /**
     * @param CustomFieldDefinition $def
     * @param CustomFieldContext    $context
     *
     * @return array
     */
    public function getCustomPerFieldChoices(CustomFieldDefinition $def, CustomFieldContext $context)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('def')
            ->from(CustomFieldDefinition::class, 'def')
            ->where('def.parent = :parent')
            ->andWhere('def.context_id = :context_id')
            ->andWhere('def.is_enabled = true')
            ->setParameter('parent', $def)
            ->setParameter('context_id', $context->getContext($def->getContextClass()))
        ;

        return $qb->getQuery()->getResult();
    }
}
