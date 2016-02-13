<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\PortalBundle\CustomField\Context;

use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Doctrine\ORM\EntityManager;

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

    public function __construct(EntityManager $em)
    {
        $this->save_queue = array();
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
        if (!$def = $this->em->getRepository('DeskPRO:CustomFieldDefinition')->find($definition_id)) {
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

    public function createNewOption(CustomFieldDefinition $def, $title, $context_id)
    {
        $child             = $def->spawnChild($title);
        $child->context_id = $context_id;

        return $child;
    }

    public function getOrCreateCustomPerFieldData(CustomFieldDefinition $def, CustomFieldContext $context)
    {
        if ($data = $this->getCustomPerFieldData($def, $context)) {
            return $data;
        }

        $owner      = $context->getOwner($def->getOwnerClass());
        $contextual = $context->getOwner($def->getOwnerClass());

        $data                  = new CustomFieldData();
        $data->definition      = $def;
        $data->root_definition = $this->findRootDefinition($def);
        $data->owner           = $owner;

        return $data;
    }

    public function saveDataToQueue(CustomFieldData $data)
    {
        $this->save_queue[] = $data;
    }

    public function flushDataQueue($flush = true)
    {
        $data = array();

        foreach ($this->save_queue as $queue_data) {
            $this->em->persist($queue_data);
            $data[] = $queue_data;
        }

        if ($flush) {
            $this->em->flush($data);
        }
    }

    public function getCustomPerFieldData(CustomFieldDefinition $def, CustomFieldContext $context)
    {
        $owner      = $context->getOwner($def->getOwnerClass());
        $contextual = $context->getOwner($def->getOwnerClass());

        if (!$owner || !$contextual) {
            return;
        }

        $result = $this->em->getRepository('DeskPRO:CustomFieldData')->getFieldData($def, $owner);

        if (is_array($result)) {
            return current($result);
        }

        return $result;
    }

    protected function findRootDefinition(CustomFieldDefinition $def)
    {
        if ($def->parent) {
            return $this->findRootDefinition($def->parent);
        }

        return $def;
    }

    public function getCustomPerFieldChoices(CustomFieldDefinition $def, CustomFieldContext $context)
    {
        $context_id = $context->getContext($def->getContextClass());

        $qb = $this->em->getRepository('DeskPRO:CustomFieldDefinition')->createQueryBuilder('def');
        $qb->where('def.parent = :parent')->setParameter('parent', $def);
        $qb->andWhere('def.context_id = :context_id')->setParameter('context_id', $context_id);
        $qb->andWhere('def.is_enabled = true');

        return $qb->getQuery()->getResult();
    }
}
