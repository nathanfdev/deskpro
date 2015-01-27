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
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 */
namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * History of slugs
 */
class DownloadSlugHistory extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID
     *
     * @var int
     *
     */
    protected $id;

    /**
     * The Download
     *
     * @var string
     */
    protected $download;

    /**
     * The slug
     *
     * @var string
     */
    protected $slug;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @param Download $download the content
     * @param string $old_slug the slug to put in history
     */
    public function __construct(Download $download, $old_slug)
    {
        $this->setDownload($download);
        $this->setSlug($old_slug);
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDownload()
    {
        return $this->download;
    }

    /**
     * @param Download $download
     */
    public function setDownload(Download $download)
    {
        $this->setModelField('download', $download);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->setModelField('slug', $slug);
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ArticleSlugHistory';
        $metadata->setPrimaryTable(array( 'name' => 'downloads_slug_history'));
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            array(
                'fieldName' => 'date_created', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false,
                'columnName' => 'date_created',
            )
        );
        $metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true));
        $metadata->mapField(array( 'fieldName' => 'slug', 'type' => 'string', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'slug', 'unique' => true));
        $metadata->mapManyToOne(array(
            'fieldName' => 'download', 'targetEntity' => 'Application\DeskPRO\Entity\Download',
            'cascade' => array(0 => 'remove', 1 => 'persist', 3 => 'merge'), 'inversedBy' => 'slug_history'
        ));
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
