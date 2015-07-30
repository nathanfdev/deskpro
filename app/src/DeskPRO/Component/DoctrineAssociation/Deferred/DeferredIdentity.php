<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Component\DoctrineAssociation\Deferred;


use DeskPRO\Component\DoctrineAssociation\DoctrineAssociationManager;

/**
 * This represents an array (or a single int) of foreign key IDs for the doctrine association that
 * will be queried for in the future.
 *
 * Often times (especially in our API) we know we want a set of IDs, but we might want LOTS of sets of
 * IDs. To avoid performing lots of query's in a loop, you can defer the resolving of the IDs if you don't
 * need them immediately by asking the DoctrineAssociationManager for an instance of this class.
 *
 * You don't normally construct this yourself. See DoctrineAssociationManager::deferAssociationIds().
 *
 * When you really do need the data, you can call resolve() and get the array (or single int). You should
 * avoid doing this until you have registered all of your DeferredIdentity with the manager, and it will try
 * to make the most optimized queries it can to deliver on all of these promises.
 */
class DeferredIdentity
{
    /**
     * @var DoctrineAssociationManager
     */
    private $assoc_manager;
    private $source_entity;
    private $property_name;
    private $resolved;
    private $result;

    public function __construct(DoctrineAssociationManager $assoc_manager, $source_entity, $property_name)
    {
        $this->assoc_manager = $assoc_manager;
        $this->source_entity = $source_entity;
        $this->property_name = $property_name;
        $this->resolved = false;
        $this->result = null;
    }

    /**
     * This will automatically trigger the association manager to resolve everything, so
     * be sure to push this call off as far into the future as possible. Ideally you want
     * to register all Deferred's with the association manager before resolving any of them.
     *
     * @return mixed the result
     */
    public function resolve()
    {
        if (!$this->resolved) {
            $this->assoc_manager->resolveDeferred();
        }

        return $this->getResult();
    }

    /**
     * @return boolean
     */
    public function isResolved()
    {
        return $this->resolved;
    }

    /**
     * @return mixed
     */
    public function getSourceEntity()
    {
        return $this->source_entity;
    }

    /**
     * @return mixed
     */
    public function getPropertyName()
    {
        return $this->property_name;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * The DoctrineAssociationManager will set a result to mark this as resolved.
     *
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
        $this->resolved = true;
    }
}
