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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Search
 */

namespace Application\DeskPRO\Search;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\ChatConversation;

/**
 * When something needs to be indexed, index it through this
 */
class SearchIndexer
{
    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    public function handle(array $updates, array $deletes)
    {
        #------------------------------
        # Elastic
        #------------------------------

        if ($this->container->getSetting('elastica.enabled')) {
            $updates_by_type = array();
            $deletes_by_type = array();

            $get_persister = function ($object) {
                switch (true) {
                    case $object instanceof Article:
                        return 'fos_elastica.object_persister.deskpro.article';
                    case $object instanceof News:
                        return 'fos_elastica.object_persister.deskpro.news';
                    case $object instanceof Download:
                        return 'fos_elastica.object_persister.deskpro.download';
                    case $object instanceof Feedback:
                        return 'fos_elastica.object_persister.deskpro.feedback';
                    case $object instanceof Ticket:
                        return 'fos_elastica.object_persister.deskpro.ticket';
                    case $object instanceof Person:
                        return 'fos_elastica.object_persister.deskpro.person';
                    case $object instanceof Organization:
                        return 'fos_elastica.object_persister.deskpro.organization';
                    case $object instanceof ChatConversation:
                        return 'fos_elastica.object_persister.deskpro.chat_conversation';
                }

                return null;
            };

            foreach ($updates as $object) {
                $persister_id = $get_persister($object);
                if ($persister_id) {
                    if (!isset($updates_by_type[$persister_id])) {
                        $updates_by_type[$persister_id] = array();
                    }
                    $updates_by_type[$persister_id][] = $object;
                }
            }
            foreach ($deletes as $object) {
                $persister_id = $get_persister($object);
                if ($persister_id) {
                    if (!isset($deletes_by_type[$persister_id])) {
                        $deletes_by_type[$persister_id] = array();
                    }
                    $deletes_by_type[$persister_id][] = $object;
                }
            }

            foreach ($updates_by_type as $persister_id => $objects) {
                $persister = $this->container->get($persister_id);
                $persister->replaceMany($objects);
            }
            foreach ($deletes_by_type as $persister_id => $objects) {
                $persister = $this->container->get($persister_id);
                $persister->deleteMany($objects);
            }

        #------------------------------
        # Default
        #------------------------------

        } else {
            foreach ($updates as $object) {
                switch (true) {
                    case $object instanceof Article:
                    case $object instanceof News:
                    case $object instanceof Download:
                    case $object instanceof Feedback:
                        App::getContainer()->getSearchAdapter()->updateObjectsInIndex(array($object));
                        break;
                }
            }
            foreach ($deletes as $object) {
                switch (true) {
                    case $object instanceof Article:
                    case $object instanceof News:
                    case $object instanceof Download:
                    case $object instanceof Feedback:
                        App::getContainer()->getSearchAdapter()->deleteObjectsFromIndex(array($object));
                        break;
                }
            }
        }
    }
}
