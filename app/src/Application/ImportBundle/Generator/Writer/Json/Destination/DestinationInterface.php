<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\ImportBundle\Generator\Writer\Json\Destination;

/**
 * Entity destination interface
 *
 * Interface DestinationInterface
 * @package Application\ImportBundle\Generator\Writer\Json\Destination
 */
interface DestinationInterface
{
    const ENTITY_PERSON_PATH   = 'people/';
    const ENTITY_TICKET_PATH   = 'tickets/';
    const ENTITY_ARTICLE_PATH  = 'articles/';
    const ENTITY_DOWNLOAD_PATH = 'downloads';
    const ENTITY_FEEDBACK_PATH = 'feedback/';
    const ENTITY_KB_PATH       = 'kb/';
    const ENTITY_NEWS_PATH     = 'news/';

    /**
     * Referred entity type
     *
     * @return string
     */
    public function getEntityType();

    /**
     * Relative entity output path
     *
     * @return string
     */
    public function getEntityOutputPath();
}
