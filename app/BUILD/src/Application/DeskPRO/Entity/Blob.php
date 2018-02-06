<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use DpSys\LowError\SystemErrorHandler;
use Orb\Data\ContentTypes;
use Orb\Util\DpStrings;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A blob is just a pointer to data.
 *
 * @property int       $id
 * @property int       $sys_name
 * @property Blob      $original_blob
 * @property string    $storage_loc
 * @property string    $storage_loc_pref
 * @property string    $storage_loc_specific
 * @property string    $save_path
 * @property string    $file_url
 * @property string    $filename
 * @property string    $filesize
 * @property string    $content_type
 * @property string    $authcode
 * @property string    $blob_hash
 * @property string    $is_media_upload
 * @property string    $title
 * @property int       $dim_w
 * @property int       $dim_h
 * @property \DateTime $date_created
 * @property bool      $is_temp
 */
class Blob extends \Application\DeskPRO\Domain\DomainObject
{
    const STORAGE_LOC_DATABASE   = 'db';
    const STORAGE_LOC_FILESYSTEM = 'fs';
    const STORAGE_LOC_S3         = 's3';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * A unique system name for the blob.
     *
     * @var string
     */
    protected $sys_name = null;

    /**
     * Sometimes we might have multiple versions of a file. For example, if a file has been
     * cropped then the cropped file is saved as its own blob, but the original
     * is linked here.
     *
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $original_blob;

    /**
     * The storage adapter that knows how to load this file.
     *
     * @var string
     */
    protected $storage_loc = 'db';

    /**
     * The preferred storage adapter. This is used to mark when we want to move
     * a file from one storage location to another. For example, if an upload
     * to S3 failed and we saved the file in the database instead,
     * then the $storage_loc would be 'db' but $storage_loc_pref would be 's3'.
     *
     * The cron jobs will look for when these two values don't match and will
     * attempt to move resources gradually.
     *
     * @var string
     */
    protected $storage_loc_pref = null;

    /**
     * A set storage adapter. This is used to 'set' a storage adapter that will
     * be used even if the system would normally use a different one.
     *
     * The main usage for this is to store things like logfiles in the database
     * instead of something like s3. DeskPRO saves lots of logfiles, so its
     * pretty inefficient to send off lots of small logfiles to s3.
     *
     * @var string
     */
    protected $storage_loc_specific = null;

    /**
     * The path to the file if it's not stored in the database.
     *
     * @var string
     */
    protected $save_path = null;

    /**
     * The HTTP link to download the file.
     *
     * @var string
     */
    protected $file_url = null;

    /**
     * The original filename.
     *
     * @var string
     */
    protected $filename = null;

    /**
     * The file size.
     *
     * @var int
     */
    protected $filesize = 0;

    /**
     * The files mimetype.
     *
     * @var string
     */
    protected $content_type = null;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $authcode;

    /**
     * @var string
     */
    protected $blob_hash;

    /**
     * Is this a media upload (appears in the media browser etc). These are files that were
     * uploaded and are attached to things.
     */
    protected $is_media_upload = false;

    /**
     * The title of this file used in interfaces if its a media upload.
     */
    protected $title = '';

    /**
     * If this type of file has dimentions, the width.
     *
     * @var int
     */
    protected $dim_w = 0;

    /**
     * If this type of file has dimentions, the height.
     *
     * @var int
     */
    protected $dim_h = 0;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var bool
     */
    protected $is_temp = false;

    /**
     * @Assert\Valid()
     * @AppAssert\UniqueCollection(property={"label"})
     *
     * @var ArrayCollection|LabelBlob[]
     */
    protected $labels;

    /**
     * @var null|\Application\DeskPRO\Labels\LabelManager
     */
    protected $_label_manager = null;

    public function __construct()
    {
        $this->date_created = new \DateTime();
        $this->authcode     = DpStrings::random(20, Strings::CHARS_KEY_ALPHA);
        $this->labels       = new ArrayCollection();
    }

    public static function hasZipArchiveClass()
    {
        return class_exists('ZipArchive');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function setFilename($filename)
    {
        if ($filename[0] == '.') {
            $filename = '_.'.substr($filename, 1);
        }

        $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '_', $filename);

        // trim filename down to max length of 255 chars
        $pos = strrpos($filename, '.');
        if ($pos !== false) {
            $name      = substr($filename, 0, $pos);
            $extension = substr($filename, $pos + 1);

            $name = substr($name, 0, 255 - strlen($extension) - 1);

            $filename = $name.'.'.$extension;
        }

        $this->setModelField('filename', $filename);

        // Try to guess content type based off of filename exts
        if (!$this->content_type) {
            $ct = ContentTypes::getContentTypeFromFilename($this->filename);
            if ($ct) {
                $this->setContentType($ct);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtension()
    {
        $pos = strrpos($this->filename, '.');
        if ($pos === false) {
            return '';
        }

        return substr($this->filename, $pos + 1);
    }

    /**
     * Is the file an image?
     *
     * @return bool
     */
    public function isImage()
    {
        switch ($this->content_type) {
            case 'image/jpg':
            case 'image/jpeg':
                return true;
            case 'image/gif':
                return true;
            case 'image/png':
                return true;
        }

        return false;
    }

    /**
     * Get the type of image this is, or null if its not an image.
     *
     * @return string
     */
    public function getImageType()
    {
        switch ($this->content_type) {
            case 'image/jpg':
            case 'image/jpeg':
                return 'jpeg';
            case 'image/gif':
                return 'gif';
            case 'image/png':
                return 'png';
        }

        return;
    }

    /**
     * Is this file an archive (and supported by the Archive extraction API yet).
     *
     * @return bool
     */
    public function isArchive()
    {
        switch ($this->content_type) {
            case 'application/zip':
                return true;
        }

        return false;
    }

    /**
     * Get the filesize with B, KB, GB etc suffix.
     */
    public function getReadableFilesize()
    {
        return Numbers::filesizeDisplay($this->filesize);
    }

    /**
     * Get the id-auth combo typically used in urls.
     *
     * @return string
     */
    public function getAuthId()
    {
        // Note: The ID is part of the authcode
        // The format of the authcode is handled by the blobstorage system
        // See DeskproBlobStorage
        // (So this is why this isn't specifically including $this->id here)

        // Typically you just look up on the authcode which is unique in the table.
        return $this->authcode;
    }

    /**
     * @return string
     */
    public function getAuthcode()
    {
        return $this->authcode;
    }

    /**
     * @param string $authcode
     *
     * @return $this
     */
    public function setAuthCode($authcode)
    {
        $this->setModelField('authcode', $authcode);

        return $this;
    }

    /**
     * Get the standard download URL for this blob.
     *
     * @param bool $absolute
     *
     * @return string
     */
    public function getDownloadUrl($absolute = false, $use_file_url = true)
    {
        if ($use_file_url && $this->file_url) {
            return $this->file_url;
        }

        if (!$this->getAuthId()) {
            return;
        }

        $url = App::get('router')->generate('serve_blob', ['blob_auth_id' => $this->getAuthId(), 'filename' => $this->getFilenameSafe()], $absolute);

        // We are specifically requestinga local url,
        // make sure serve_file doesn't redirect.
        if ($this->file_url && !$use_file_url) {
            $url = str_replace('/file.php/', '/file.php/local/', $url);
        }

        return $url;
    }

    public function getEmbedCode($for_ticket = false, $type = 'image')
    {
        if ($for_ticket) {
            return '[attach:'.$type.':'.$this->getAuthId().':'.$this->getFilenameSafe().']';
        } else {
            return '[attach:'.$this->getAuthId().':'.$this->getFilenameSafe().']';
        }
    }

    /**
     * Get a thumbnail for this blob (if its an image).
     *
     * @param int        $size
     * @param int|string $absolute
     * @param bool       $sizeFit
     *
     * @return string
     */
    public function getThumbnailUrl($size = 50, $absolute = UrlGeneratorInterface::ABSOLUTE_PATH, $sizeFit = false)
    {
        if (!$this->isImage()) {
            return;
        }
        if (!$this->getAuthId()) {
            return;
        }

        $params = [
            'blob_auth_id' => $this->getAuthId(),
            'filename'     => $this->getFilenameSafe(),
        ];

        if ($size) {
            $params['s'] = $size;
        }
        if ($sizeFit) {
            $params['size-fit'] = 1;
        }

        return App::get('router')->generate('serve_blob', $params, $absolute);
    }

    /**
     * Get a safe version of a filename. That is the same filename with all "weird" characters removed.
     *
     * @return string
     */
    public function getFilenameSafe()
    {
        return Strings::getFilenameSafe($this->filename);
    }

    /**
     * The name hash is 6 chars long that represents the original filename.
     *
     * @return string
     */
    public function getNameHash()
    {
        $namehash = strtoupper(substr(sha1($this->getFilenameSafe().$this->id), 0, 3));
        $namehash .= strtoupper(substr(md5($this->getFilenameSafe().$this->id), 0, 3));

        return $namehash;
    }

    /**
     * @return string
     */
    public function getDisplayTitle()
    {
        if ($this->title) {
            return $this->title;
        }

        return $this->filename;
    }

    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * @param $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->setModelField('content_type', $contentType);

        return $this;
    }

    /**
     * @return Blob
     */
    public function getOriginalBlob()
    {
        return $this->original_blob;
    }

    /**
     * @param Blob $originalBlob
     *
     * @return $this
     */
    public function setOriginalBlob($originalBlob)
    {
        $this->setModelField('original_blob', $originalBlob);

        return $this;
    }

    /**
     * @return int
     */
    public function getDimW()
    {
        return $this->dim_w;
    }

    /**
     * @param int $dimW
     *
     * @return $this
     */
    public function setDimW($dimW)
    {
        $this->setModelField('dim_w', $dimW);

        return $this;
    }

    /**
     * @return int
     */
    public function getDimH()
    {
        return $this->dim_h;
    }

    /**
     * @param int $dimH
     *
     * @return $this
     */
    public function setDimH($dimH)
    {
        $this->setModelField('dim_h', $dimH);

        return $this;
    }

    public function setDimensions($dimensions)
    {
        return $this->setDimW($dimensions[0])->setDimH($dimensions[1]);
    }

    public function addLabel(LabelBlob $label)
    {
        $label['blob'] = $this;
        $this->labels->add($label);
        $this->_onPropertyChanged('labels', $this->labels, $this->labels);
    }

    /**
     * @param string $storage_loc
     */
    public function setStorageLocPref($storage_loc)
    {
        if (!$storage_loc) {
            $this->setModelField('storage_loc_pref', null);
        } else {
            $this->setModelField('storage_loc_pref', $storage_loc);
        }
    }

    /**
     * @return string
     */
    public function getStorageLocPref()
    {
        return $this->storage_loc_pref;
    }

    /**
     * @param string $storage_loc
     */
    public function setStorageLocSpecific($storage_loc)
    {
        if (!$storage_loc) {
            $this->setModelField('storage_loc_specific', null);
        } else {
            $this->setModelField('storage_loc_specific', $storage_loc);
        }
    }

    /**
     * @return string
     */
    public function getStorageLocSpecific()
    {
        return $this->storage_loc_specific;
    }

    /**
     * @return \Application\DeskPRO\Labels\LabelManager
     */
    public function getLabelManager()
    {
        if ($this->_label_manager === null) {
            $this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelBlob');
        }

        return $this->_label_manager;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        return $this->save_path;
    }

    /**
     * @param string $save_path
     *
     * @return $this
     */
    public function setSavePath($save_path)
    {
        $this->setModelField('save_path', $save_path);

        return $this;
    }

    /**
     * @return int
     */
    public function getSysName()
    {
        return $this->sys_name;
    }

    /**
     * @param int $sys_name
     *
     * @return $this
     */
    public function setSysName($sys_name)
    {
        $this->setModelField('sys_name', $sys_name);

        return $this;
    }

    /**
     * @return string
     */
    public function getFileUrl()
    {
        return $this->file_url;
    }

    /**
     * @param string $file_url
     *
     * @return $this
     */
    public function setFileUrl($file_url)
    {
        $this->setModelField('file_url', $file_url);

        return $this;
    }

    /**
     * @return string
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * @param string $filesize
     *
     * @return $this
     */
    public function setFilesize($filesize)
    {
        $this->setModelField('filesize', $filesize);

        return $this;
    }

    /**
     * @return string
     */
    public function getBlobHash()
    {
        return $this->blob_hash;
    }

    /**
     * @param string $blob_hash
     *
     * @return $this
     */
    public function setBlobHash($blob_hash)
    {
        $this->setModelField('blob_hash', $blob_hash);

        return $this;
    }

    /**
     * @return string
     */
    public function getStorageLoc()
    {
        return $this->storage_loc;
    }

    /**
     * @param string $storage_loc
     *
     * @return $this
     */
    public function setStorageLoc($storage_loc)
    {
        $this->setModelField('storage_loc', $storage_loc);

        return $this;
    }

    /**
     * @return bool
     */
    public function isTemp()
    {
        return $this->is_temp;
    }

    /**
     * @param bool $is_temp
     *
     * @return $this
     */
    public function setIsTemp($is_temp)
    {
        $this->setModelField('is_temp', $is_temp);

        return $this;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $is_image = $this->isImage();

        return [
            'id'                 => $this->id,
            'authcode'           => $this->authcode,
            'filename'           => $this->filename,
            'file_ext'           => $this->getExtension(),
            'filesize'           => $this->filesize,
            'filesize_display'   => $this->getReadableFilesize(),
            'date_created'       => $this->date_created->format('Y-m-d H:i:s'),
            'date_created_ts'    => $this->date_created->getTimestamp(),
            'date_created_ts_ms' => $this->date_created->getTimestamp() * 1000,
            'content_type'       => $this->content_type,
            'is_image'           => $is_image,
            'name_hash'          => $this->getNameHash(),
            'blob_hash'          => $this->blob_hash,
            'download_url'       => $this->getDownloadUrl(true),
            'relative_url'       => $this->getDownloadUrl(false),
            'thumbnail_url_80'   => $is_image ? $this->getThumbnailUrl(80, true) : null,
            'thumbnail_url_75'   => $is_image ? $this->getThumbnailUrl(75, true) : null,
            'thumbnail_url_50'   => $is_image ? $this->getThumbnailUrl(50, true) : null,
            'thumbnail_url_30'   => $is_image ? $this->getThumbnailUrl(30, true) : null,
            'thumbnail_url_20'   => $is_image ? $this->getThumbnailUrl(20, true) : null,
            'thumbnail_url_16'   => $is_image ? $this->getThumbnailUrl(16, true) : null,
        ];
    }

    /**
     * @return array
     */
    public function toDbArray()
    {
        return [
            'date_created'         => $this->date_created->format('Y-m-d H:i:s'),
            'authcode'             => $this->authcode,
            'sys_name'             => $this->sys_name,
            'original_blob_id'     => $this->original_blob ? $this->original_blob->id : null,
            'storage_loc'          => $this->storage_loc,
            'storage_loc_pref'     => $this->storage_loc_pref,
            'storage_loc_specific' => $this->storage_loc_specific,
            'save_path'            => $this->save_path,
            'file_url'             => $this->file_url,
            'filename'             => $this->filename,
            'filesize'             => $this->filesize,
            'content_type'         => $this->content_type,
            'blob_hash'            => $this->blob_hash,
            'is_media_upload'      => $this->is_media_upload,
            'title'                => $this->title,
            'dim_w'                => $this->dim_w,
            'dim_h'                => $this->dim_h,
            'is_temp'              => $this->is_temp ? 1 : 0,
        ];
    }

    /**
     * We can't set invalid data to the db so check properties before flush.
     */
    public function _onValidateProps()
    {
        // force set auth code
        if (!$this->authcode) {
            SystemErrorHandler::logException(new \InvalidArgumentException('Attempt to save a blob without authcode'), false, null, true);
            $this->setModelField('authcode', DpStrings::random(20, Strings::CHARS_KEY_ALPHA));
        }
    }

    /**
     * A list of all tables that reference blobs.
     *
     * @return array
     */
    public static function tablesWithBlobs()
    {
        return [
            ['table' => 'agent_teams', 'columns' => ['avatar_blob_id']],
            ['table' => 'app2_app_asset_blob', 'columns' => ['blob_id']],
            ['table' => 'app_assets', 'columns' => ['blob_id']],
            ['table' => 'article_attachments', 'columns' => ['blob_id']],
            ['table' => 'blobs', 'columns' => ['original_blob_id']],
            ['table' => 'brand_assets', 'columns' => ['blob_id']],
            ['table' => 'departments', 'columns' => ['avatar_blob_id']],
            ['table' => 'downloads', 'columns' => ['blob_id']],
            ['table' => 'download_revisions', 'columns' => ['blob_id']],
            ['table' => 'email_accounts', 'columns' => ['key_blob_id', 'cert_blob_id']],
            ['table' => 'email_sources', 'columns' => ['blob_id', 'log_blob_id']],
            ['table' => 'feedback_attachments', 'columns' => ['blob_id']],
            ['table' => 'labels_blobs', 'columns' => ['blob_id']],
            ['table' => 'organizations', 'columns' => ['picture_blob_id']],
            ['table' => 'organization_files', 'columns' => ['blob_id']],
            ['table' => 'people', 'columns' => ['picture_blob_id']],
            ['table' => 'people_files', 'columns' => ['blob_id']],
            ['table' => 'sendmail_sources', 'columns' => ['blob_id', 'log_blob_id']],
            ['table' => 'theme_set_assets', 'columns' => ['blob_id']],
            ['table' => 'tickets_attachments', 'columns' => ['blob_id']],
            ['table' => 'ticket_proc_log', 'columns' => ['blob_id']],
            ['table' => 'voice_assets', 'columns' => ['blob_id']],
            ['table' => 'voice_phone_calls', 'columns' => ['recording_id']],
        ];
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->addLifecycleCallback('_onValidateProps', 'prePersist');
        $metadata->addLifecycleCallback('_onValidateProps', 'preUpdate');
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Blob';
        $metadata->setPrimaryTable([
            'name'    => 'blobs',
            'indexes' => [
                'authcode_idx'     => ['columns' => ['authcode']],
                'storage_loc_idx'  => ['columns' => ['storage_loc', 'storage_loc_pref']],
                'sys_name_idx'     => ['columns' => ['sys_name']],
                'date_created_idx' => ['columns' => ['date_created', 'is_temp']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'sys_name',
            'type'       => 'string',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'sys_name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'storage_loc',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'storage_loc',
        ]);
        $metadata->mapField([
            'fieldName'  => 'storage_loc_pref',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'storage_loc_pref',
        ]);
        $metadata->mapField([
            'fieldName'  => 'storage_loc_specific',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'storage_loc_specific',
        ]);
        $metadata->mapField([
            'fieldName'  => 'save_path',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'save_path',
        ]);
        $metadata->mapField([
            'fieldName'  => 'file_url',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'file_url',
        ]);
        $metadata->mapField([
            'fieldName'  => 'filename',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'filename',
        ]);
        $metadata->mapField([
            'fieldName'  => 'filesize',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'filesize',
        ]);
        $metadata->mapField([
            'fieldName'  => 'content_type',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'content_type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'authcode',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'authcode',
        ]);
        $metadata->mapField([
            'fieldName'  => 'blob_hash',
            'type'       => 'string',
            'length'     => 40,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'blob_hash',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_media_upload',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_media_upload',
        ]);
        $metadata->mapField([
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'title',
        ]);
        $metadata->mapField([
            'fieldName'  => 'dim_w',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'dim_w',
        ]);
        $metadata->mapField([
            'fieldName'  => 'dim_h',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'dim_h',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_temp',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_temp',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'original_blob',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'original_blob_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapOneToMany([
            'fieldName'     => 'labels',
            'targetEntity'  => 'Application\\DeskPRO\\Entity\\LabelBlob',
            'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
            'mappedBy'      => 'blob',
            'orphanRemoval' => true,
        ]);
    }

    public function __getPropValue__($k)
    {
        return $this->$k;
    }

    public function __setPropValue__($k, $v)
    {
        $this->$k = $v;
    }

    public function __clone()
    {
        $this->id = null;

        return parent::__clone();
    }
}
