<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ImportBundle\Entity;

/**
 * Exporting entity interface
 *
 * Interface EntityInterface
 * @package Application\ImportBundle\Entity
 */
interface EntityInterface
{
    const TYPE_ARTICLE          = 'article';
    const TYPE_PERSON           = 'person';
    const TYPE_TICKET           = 'ticket';
    const TYPE_TICKET_MESSAGE   = 'ticket_message';
    const TYPE_ATTACHMENT       = 'attachment';
    const TYPE_CUSTOM_DEF_VALUE = 'custom_def_value';
    const TYPE_DOWNLOAD         = 'download';
    const TYPE_NEWS             = 'news';
    const TYPE_KB               = 'kb';
    const TYPE_FEEDBACK         = 'feedback';

    /**
     * Get entity type
     *
     * @return string
     */
    public function getType();

    /**
     * Get entity oid
     *
     * @return int
     */
    public function getOid();

    /**
     * Get entity destination
     *
     * @return string
     */
    public function getDestination();

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray();
}
