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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Generator\Writer\Json\Destination;
use Application\ImportBundle\Entity;
use DateTime;

/**
 * Kb json file parser
 *
 * Class Kb
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
 */
final class Kb extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_KB;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getDirectoryFilesCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $kbs = $this->reader->getData($this->getConfig());

        foreach ($kbs as $num => $kb) {
            $this->advanceProgressBar();

            if ($this->hasRequiredKbColumns($kb) === false) {
                $this->logWarning(sprintf('Invalid kb record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\Kb();
                $entity
                    ->setOid($kb['oid'])
                    ->setPersonEmail($kb['person'])
                    ->setTitle($kb['title'])
                    ->setContent($kb['content'])
                    ->setLanguage($kb['language'])
                    ->setEndAction($kb['end_action'])
                    ->setSlug($kb['slug'])
                    ->setTotalRating($kb['total_rating'])
                    ->setNumComments($kb['num_comments'])
                    ->setNumRatings($kb['num_ratings'])
                    ->setStatus($kb['status'])
                    ->setDateCreated(new DateTime($kb['date_created']));

                if ($kb['date_published']) {
                    $entity->setDatePublished(new DateTime($kb['date_published']));
                }
                if ($kb['date_end']) {
                    $entity->setDateEnd(new DateTime($kb['date_end']));
                }
                foreach ($kb['categories'] as $category) {
                    $entity->addCategory($category);
                }
                foreach ($kb['labels'] as $label) {
                    $entity->addLabel($label);
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Json\JsonConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_KB_PATH);
    }

    /**
     * Check if kb has all required columns
     *
     * @param array $kb
     * @return bool
     */
    private function hasRequiredKbColumns(array $kb)
    {
        $columns = array(
            'oid',
            'person',
            'title',
            'content',
            'language',
            'end_action',
            'slug',
            'total_rating',
            'num_comments',
            'num_ratings',
            'status',
            'date_created',
            'date_published',
            'date_end',
            'categories',
            'labels',
        );

        return $this->hasRequiredColumns($kb, $columns)
            && is_array($kb['categories'])
            && is_array($kb['labels']);
    }
}
