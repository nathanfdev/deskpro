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
 * DeskPro download importer
 *
 * Class Download
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
final class Download extends AbstractImporter
{
    /**
     * @var BlobAdapterInterface
     */
    private $blob_adapter;

    /**
     * Constructor
     *
     * @param Mapper\Collection    $mappers
     * @param BlobAdapterInterface $blob_adapter
     */
    public function __construct(Mapper\Collection $mappers, BlobAdapterInterface $blob_adapter)
    {
        parent::__construct($mappers);
        $this->blob_adapter = $blob_adapter;
    }

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
     * @var Entity\Download $importing_entity
     *
     * todo add referred objects
     * 'total_rating'   => $dval->total_rating,
     * 'num_comments'   => $dval->num_comments,
     * 'num_ratings'    => $dval->num_ratings,
     * 'view_count'     => $dval->view_count,
     * 'num_downloads'  => $dval->num_downloads,
     */
    public function getDoctrineEntities(Entity\EntityInterface $importing_entity)
    {
        $this->records = new ArrayCollection();
        $exist_download = $this->getDownloadMapper()->findOneByTitle($importing_entity->getTitle(), false);
        if ($exist_download) {
            $this->logWarning(sprintf(
                'An Download with the title `%s` already exists (skipping)',
                $importing_entity->getTitle()
            ));
        } else {
            $download = new DeskPROEntity\Download();
            $download
                ->setTitle($importing_entity->getTitle())
                ->setContent($importing_entity->getContent())
                ->setSlug($importing_entity->getSlug())
                ->setPerson($this->getPersonMapper()->findOneByEmail($importing_entity->getPersonEmail()))
                ->setLanguage($this->findLanguage($importing_entity->getLanguage()))
                ->setBlob($this->blob_adapter->createByAttachment($importing_entity->getAttachment()))
                ->setCategory($this->findOrCreateDownloadCategory($importing_entity->getCategory()))
                ->setDateCreated($importing_entity->getDateCreated())
                ->setDatePublished($importing_entity->getDatePublished());

            foreach ($importing_entity->getLabels() as $label) {
                $download->addLabel($this->createDownloadLabel($label));
            }

            $this->records->add($download);
        }

        return $this->records;
    }

    /**
     * Returns an download category by title
     * Creates a new article category if not found
     *
     * @param string $title
     *
     * @return DeskPROEntity\DownloadCategory|null
     * @throws \Exception
     */
    private function findOrCreateDownloadCategory($title)
    {
        $category = null;
        if ($title) {
            $category = $this->getDownloadCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logInfo(sprintf('Found existing download category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\DownloadCategory();
                $category->setRealTitle($title);

                $this->records->add($category);
                $this->logWarning(sprintf('New download category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
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
     * Returns the download mapper
     *
     * @return Mapper\Download
     * @throws \Exception
     */
    private function getDownloadMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_DOWNLOAD);
    }

    /**
     * Returns the download category mapper
     *
     * @return Mapper\DownloadCategory
     * @throws \Exception
     */
    private function getDownloadCategoryMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_DOWNLOAD_CATEGORY);
    }
}
