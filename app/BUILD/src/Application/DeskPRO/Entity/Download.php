<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Labels\Label;
use Application\DeskPRO\Entity\Labels\LabelsOwner;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkCustom;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Component\Util\RegexUtils;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @PortalLinkRoute("portal_downloads_view", route_param_map={"slug":"slug"})
 * @PortalLinkRoute("portal_downloads_view", route_param_map={"slug": "id"}, type="permalink")
 * @PortalLinkRoute("portal_downloads_files_toggle_subscription", route_param_map={"slug":"slug"}, type="toggle_subscription")
 * @PortalLinkRoute("portal_downloads_vote_up", route_param_map={"slug":"slug"}, type="vote_up")
 * @PortalLinkRoute("portal_downloads_vote_down", route_param_map={"slug":"slug"}, type="vote_down")
 * @PortalLinkRoute("portal_downloads_download", route_param_map={"slug":"slug"}, type="save")
 * @PortalLinkCustom(type="serve")
 */
class Download extends ContentAbstract implements HighlightableModelInterface, LabelsOwner
{
    const CONTENT_TYPE = 'download';

    /**
     * @var \Application\DeskPRO\Entity\DownloadCategory
     */
    protected $category;

    /**
     * Revisions of this download.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $revisions;

    /**
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $blob;

    /**
     * @var string
     */
    protected $fileurl;

    /**
     * @var string
     */
    protected $filesize;

    /**
     * @var string
     */
    protected $filename;

    /**
     * Total number of downloads.
     *
     * @var string
     */
    protected $num_downloads = 0;

    /**
     * String array of labels associated with this download.
     *
     * @Assert\Valid()
     * @AppAssert\UniqueCollection(property={"label"})
     *
     * @var \Doctrine\Common\Collections\ArrayCollection|LabelDownload[]
     */
    protected $labels;

    /**
     * @var \Application\DeskPRO\Labels\LabelManager
     */
    protected $_label_manager = null;

    /**
     * The search result highlights.
     *
     * @var array
     */
    protected $_search_highlights;

    public function incrementDownloadCount()
    {
        $new = (int) $this->num_downloads + 1;
        $this->setModelField('num_downloads', $new);
    }

    /**
     * @param Blob $blob
     *
     * @return $this
     */
    public function setBlob(Blob $blob = null)
    {
        if ($blob) {
            $this->setModelField('blob', $blob);
            $this->setModelField('fileurl', null);
            $this->setModelField('filesize', null);
            $this->setModelField('filename', null);
        } else {
            $this->setModelField('blob', null);
        }

        return $this;
    }

    /**
     * @param      $url
     * @param      $filesize
     * @param null $filename
     */
    public function setFileUrl($url, $filesize, $filename = null)
    {
        if (!$filename) {
            $last_bit = str_replace(['/', ':', '\\'], '/', $url);
            $last_bit = explode('/', $last_bit);
            $last_bit = array_pop($last_bit);

            if ($last_bit) {
                $filename = $last_bit;
            }
        }

        if (!$filename) {
            $filename = 'file';
        }

        if ($filesize && !is_numeric($filesize)) {
            $filesize = strtolower($filesize);
            $filesize = str_replace(
                ['bytes', 'kilobytes', 'megabytes', 'gigabytes'],
                ['b', 'kb', 'mb', 'gb'],
                $filesize
            );
            foreach (['k', 'm', 'g'] as $l) {
                $filesize = preg_replace("#\b$l\b#", "{$l}b", $filesize);
            }

            $num = preg_replace('#[^0-9\.]#', '', $filesize);
            if (strpos($filesize, 'tb') !== false) {
                $filesize = $num * 1099511627776;
            } elseif (strpos($filesize, 'gb') !== false) {
                $filesize = $num * 1073741824;
            } elseif (strpos($filesize, 'mb') !== false) {
                $filesize = $num * 1048576;
            } elseif (strpos($filesize, 'kb') !== false) {
                $filesize = $num * 1024;
            } else {
                $filesize = $num;
            }
        }

        $this->setModelField('blob', null);
        $this->setModelField('fileurl', $url);
        $this->setModelField('filesize', $filesize);
        $this->setModelField('filename', $filename);
    }

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->setModelField('filename', $filename);

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        if ($this->filename) {
            return $this->filename;
        }

        if (!$this->blob) {
            return '';
        }

        return $this->blob['filename'];
    }

    /**
     * @return string
     */
    public function getFilenameSafe()
    {
        $filename_safe = Strings::utf8_accents_to_ascii($this->getFileName());
        $filename_safe = RegexUtils::safePregReplace('#[^a-zA-Z0-9\-_\.]#', '-', $filename_safe);
        $filename_safe = RegexUtils::safePregReplace('#\-{2,}#', '-', $filename_safe);

        return $filename_safe;
    }

    /**
     * @return int|string
     */
    public function getFileSize()
    {
        if ($this->filesize) {
            return $this->filesize;
        }

        if (!$this->blob) {
            return 0;
        }

        return $this->blob['filesize'];
    }

    /**
     * @return string
     */
    public function getReadableFileSize()
    {
        if ($this->filesize) {
            return Numbers::filesizeDisplay($this->filesize);
        }

        if (!$this->blob) {
            return '0 B';
        }

        return $this->blob->getReadableFilesize();
    }

    /**
     * @return array
     */
    public function getCategoryPath()
    {
        $path = [];

        $cat    = $this->category;
        $path[] = $cat;
        while ($cat['parent']) {
            $cat    = $cat['parent'];
            $path[] = $cat;
        }

        return $path;
    }

    /**
     * @param DownloadCategory $category
     *
     * @return $this
     */
    public function setCategory(DownloadCategory $category = null)
    {
        $this->setModelField('category', $category);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearLabels()
    {
        foreach ($this->labels as $data) {
            $this->labels->removeElement($data);
        }

        $this->_onPropertyChanged('labels', null, $this->labels);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel(Label $label)
    {
        $label['download'] = $this;
        $this->labels->add($label);
    }

    /**
     * {@inheritdoc}
     */
    public function removeLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
            $this->_onPropertyChanged('labels', $this->labels, $this->labels);
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\LabelDownload[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Set downloads count.
     *
     * @param int $num_downloads
     *
     * @return $this
     */
    public function setNumDownloads($num_downloads)
    {
        $this->setModelField('num_downloads', $num_downloads);

        return $this;
    }

    /**
     * @return string
     */
    public function getNumDownloads()
    {
        return $this->num_downloads;
    }

    /**
     * @return string
     */
    public function getContentDesc()
    {
        $content = $this->content;
        $content = Strings::html2Text($content);
        $content = str_replace("\n", ' ', $content);
        $content = preg_replace('# {2,}#', ' ', $content);

        if (strlen($content) > 120) {
            $content = substr($content, 0, 120).'...';
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);
        if ($deep) {
            $data['labels'] = [];
            foreach ($this->labels as $label) {
                $data['labels'][] = $label['label'];
            }
        }

        $data['filename'] = $this->getFileName();
        $data['filesize'] = $this->getFileSize();
        if ($this->blob) {
            $data['downloadurl'] = App::getRouter()->generate(
                'serve_blob',
                ['blob_auth_id' => $this->blob->auth_id, 'filename' => $this->getFilenameSafe()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $data;
    }

    /**
     * Set ElasticSearch highlight data.
     *
     * @param array $highlights array of highlight strings
     */
    public function setElasticHighlights(array $highlights)
    {
        if (!empty($highlights)) {
            $this->_search_highlights = $highlights;
        }
    }

    /**
     * Get Elasticsearch highlight data.
     *
     * @param null $field
     *
     * @return array|null
     */
    public function getElasticHighlights($field = null)
    {
        if (is_null($field)) {
            return $this->_search_highlights;
        } else {
            if (isset($this->_search_highlights[$field])) {
                return $this->_search_highlights[$field];
            } else {
                return;
            }
        }
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getCategoryId()
    {
        return $this->category['id'];
    }

    /**
     * @return string
     */
    public function getFileurl()
    {
        return $this->fileurl;
    }

    protected function addSlugHistory($oldSlug)
    {
        $history = new DownloadSlugHistory($this, $oldSlug);
        $this->slug_history->add($history);

        return $history;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Download';
        $metadata->setPrimaryTable(
            [
                'name'    => 'downloads',
                'indexes' => [
                    'date_published_idx'    => ['columns' => [0 => 'date_published']],
                    'date_updated_idx'      => ['columns' => ['date_updated']],
                    'date_last_comment_idx' => ['columns' => ['date_last_comment']],
                    'status_idx'            => ['columns' => ['status']],
                ],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'num_downloads',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'num_downloads',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'slug',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'slug',
                'unique'     => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'fileurl',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'fileurl',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'filename',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'filename',
            ]
        );
        $metadata->mapField(
            ['fieldName' => 'filesize', 'type' => 'integer', 'nullable' => true, 'columnName' => 'filesize']
        );
        $metadata->mapField(
            [
                'fieldName'  => 'content',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'content',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'content_input',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'content_input',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'content_input_type',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'content_input_type',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'view_count',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'view_count',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'total_rating',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'total_rating',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'num_comments',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'num_comments',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'num_ratings',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'num_ratings',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'status',
                'type'       => 'string',
                'length'     => 15,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'status',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'hidden_status',
                'type'       => 'string',
                'length'     => 15,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'hidden_status',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_updated',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_updated',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_published',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_published',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_last_comment',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_last_comment',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'category',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\DownloadCategory',
                'mappedBy'     => null,
                'inversedBy'   => 'downloads',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'category_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'revisions',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\DownloadRevision',
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'download',
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'blob',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'blob_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => null,
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'labels',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\LabelDownload',
                'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'      => 'download',
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'language',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Language',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'language_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'comments',
                'targetEntity' => DownloadComment::class,
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'download',
                'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'slug_history',
                'targetEntity' => 'Application\DeskPRO\Entity\DownloadSlugHistory',
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'download',
            ]
        );

        $metadata->addLifecycleCallback('_preUpdate', 'preUpdate');
    }

    /**
     * @return Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    protected function getUpdateFields()
    {
        $fields   = parent::getUpdateFields();
        $fields[] = 'blob';
        $fields[] = 'fileurl';
        $fields[] = 'filename';
        $fields[] = 'filesize';

        return $fields;
    }
}
