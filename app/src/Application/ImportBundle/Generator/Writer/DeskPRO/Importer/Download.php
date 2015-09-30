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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;

/**
 * DeskPRO download importer.
 *
 * Class Download
 */
final class Download extends AbstractImporter implements SkipDuplicateInterface
{
    /**
     * @var BlobAdapterInterface
     */
    private $blob_adapter;

    /**
     * Constructor.
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
     * todo add referred objects
     * 'total_rating'   => $dval->total_rating,
     * 'num_comments'   => $dval->num_comments,
     * 'num_ratings'    => $dval->num_ratings,
     */
    public function prepare(Entity\EntityInterface $entity, $entity_id = null)
    {
        if (!$entity instanceof Entity\Download) {
            Entity\UnexpectedException::throwUnexpectedEntityTypeException($entity);
        }

        $download = new DeskPROEntity\Download();
        $download
            ->setTitle($entity->getTitle())
            ->setContent($entity->getContent())
            ->setSlug($entity->getSlug())
            ->setPerson($this->getPersonMapper()->findOneByEmail($entity->getPersonEmail()))
            ->setLanguage($this->findLanguage($entity->getLanguage()))
            ->setBlob($this->blob_adapter->createByBlob($entity->getAttachment()))
            ->setCategory($this->findOrCreateDownloadCategory($entity->getCategory()))
            ->setDateCreated($entity->getDateCreated())
            ->setDatePublished($entity->getDatePublished())
            ->setViewsCount($entity->getViewCount())
            ->setNumDownloads($entity->getNumDownloads())
        ;

        $this->records->setPrimaryEntity($download);
    }

    /**
     * {@inheritdoc}
     *
     * @var Entity\Download
     */
    public function checkAlreadyExists(Entity\EntityInterface $entity)
    {
        if ($this->getDownloadMapper()->findOneByTitle($entity->getTitle(), false)) {
            throw new DuplicateException();
        }
    }

    /**
     * Returns an download category by title.
     * Creates a new article category if not found.
     *
     * @param string $title
     *
     * @throws \Exception
     *
     * @return DeskPROEntity\DownloadCategory|null
     */
    private function findOrCreateDownloadCategory($title)
    {
        $category = null;
        if ($title) {
            $category = $this->getDownloadCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logDebug(sprintf('Found existing download category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\DownloadCategory();
                $category->setRealTitle($title);

                $this->records->addRelatedEntity($category);
                $this->logInfo(sprintf('New download category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Returns the download category mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\DownloadCategory
     */
    private function getDownloadCategoryMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_DOWNLOAD_CATEGORY);
    }
}
