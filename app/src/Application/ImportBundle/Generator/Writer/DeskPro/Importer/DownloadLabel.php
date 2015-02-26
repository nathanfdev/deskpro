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

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DeskPro download labels importer
 *
 * Class DownloadLabel
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
final class DownloadLabel extends AbstractImporter
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_DOWNLOAD;
    }

    /**
     * {@inheritdoc}
     *
     * @var Entity\Download $entity
     */
    public function getDoctrineEntities(Entity\EntityInterface $entity)
    {
        $this->records = new ArrayCollection();

        $download = $this->getDownloadMapper()->findOneByTitle($entity->getTitle());
        $labels   = $this->getExistingLabelsNames($download->getId());

        foreach ($entity->getLabels() as $label) {
            if (in_array($label, $labels, true)) {
                $this->logWarning(sprintf(
                    'Found an existing label `%s` for download with oid `%d` (Skipping)',
                    $label, $download->getId()
                ));
            } else {
                $download->addLabel($this->createDownloadLabel($label));
                $this->logInfo(sprintf(
                    'Creating a new label `%s` for download with oid `%d`',
                    $label, $download->getId()
                ));
            }
        }

        return $this->records;
    }

    /**
     * Returns a new download label entity
     *
     * @param string $label
     * @return DeskPROEntity\LabelDownload
     */
    private function createDownloadLabel($label)
    {
        $entity = new DeskPROEntity\LabelDownload();
        $entity->setLabel($label);

        $this->records->add($entity);
        return $entity;
    }

    /**
     * Returns a collection of existing download label names
     *
     * @param int $id
     *
     * @return array
     * @throws Mapper\MapperException
     */
    private function getExistingLabelsNames($id)
    {
        $labels = $this->getDownloadLabelMapper()->findByDownloadId($id, false);
        $names  = array();

        foreach ($labels as $label) {
            $names[] = $label->getLabel();
        }

        return $names;
    }

    /**
     * Returns the download label mapper
     *
     * @return Mapper\DownloadLabel
     * @throws \Exception
     */
    private function getDownloadLabelMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_DOWNLOAD_LABEL);
    }
}
