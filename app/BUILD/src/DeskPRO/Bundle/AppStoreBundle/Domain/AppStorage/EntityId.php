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

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage;

class EntityId
{
    /**
     * @var string
     */
    private $entityType;

    /**
     * @var string
     */
    private $entityId;

    /**
     * @param string $idString
     * @return EntityId|null
     */
    public static function parse($idString)
    {
        $separator = ':';
        $pieces = explode($separator, trim($idString));

        if (count($pieces) != 2) {
            return null;
        }

        return new EntityId($pieces[0], $pieces[1]);
    }

    /**
     * @param EntityId $entityId
     * @return string
     */
    public static function convertToString(EntityId $entityId)
    {
        $separator = ':';
        $pieces = [
            $entityId->getEntityType(),
            $entityId->getEntityId(),
        ];
        return  implode($separator, $pieces);
    }

    /**
     * @param string $entityType
     * @param string $entityId
     */
    public function __construct($entityType, $entityId)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }

    /**
     * @return mixed
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

}
