<?php

namespace Application\DeskPRO\Attachments;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Doctrine\ORM\EntityManager;
use DpSys\LowError\SystemErrorHandler;
use Orb\Data\ContentTypes;
use Orb\Util\Arrays;
use Orb\Util\Env;
use Orb\Util\Numbers;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AcceptAttachment.
 */
class AcceptAttachment
{
    const ERR_SIZE    = 'size';
    const ERR_FAILED  = 'failed_upload';
    const ERR_NO_FILE = 'no_file';
    const ERR_SERVER  = 'server_error';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage
     */
    protected $blobstorage;

    /**
     * @var \Application\DeskPRO\Attachments\RestrictionSet[]
     */
    protected $restrictionSets = [];

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blobstorage
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $blobstorage)
    {
        $this->em          = $em;
        $this->blobstorage = $blobstorage;
    }

    /**
     * @param $id
     * @param \Application\DeskPRO\Attachments\RestrictionSet $set
     */
    public function addRestrictionSet($id, RestrictionSet $set)
    {
        $this->restrictionSets[$id] = $set;
    }

    /**
     * @param $id
     *
     * @throws \InvalidArgumentException
     *
     * @return \Application\DeskPRO\Attachments\RestrictionSet
     */
    public function getRestrictionSet($id)
    {
        if (!isset($this->restrictionSets[$id])) {
            // check if it's a restriction set of a custom file field
            // don't preload restrictions for all custom file fields on the service bootstrap
            // load it runtime on a restriction set request

            // e.g. custom_field.ticket.10.user
            if (preg_match('/^custom_field\.(\w+)\.(\d+)\.(\w+)/', $id, $matches)) {
                list(, $type, $fieldId, $context) = $matches;

                $customDefClass = 'Application\\DeskPRO\\Entity\\CustomDef'.ucfirst($type);
                if (!class_exists($customDefClass)) {
                    throw new \InvalidArgumentException("Unable to get upload restriction set for `$type` custom field");
                }
                if (!$fieldId) {
                    throw new \InvalidArgumentException('No custom field id provided');
                }

                /** @var CustomDefAbstract $def */
                $def = $this->em->getRepository($customDefClass)->find($fieldId);
                if (!$def || !$def->isFileType()) {
                    throw new \InvalidArgumentException('Unable to get custom file field');
                }

                $effectiveMaxUploadSize = Env::getEffectiveMaxUploadSize();

                $maxSize = $def->getOption($context.'_max_file_size');
                $maxSize = min($effectiveMaxUploadSize, $maxSize);

                $mustExtensions = $def->getOption($context.'_must_extensions', null);
                $notExtensions  = $def->getOption($context.'_not_extensions', null);

                if ($mustExtensions) {
                    array_walk($mustExtensions, 'trim');
                }
                if ($notExtensions) {
                    array_walk($notExtensions, 'trim');
                }

                $res = new RestrictionSet();
                $res
                    ->setMaxSize($maxSize)
                    ->setAllowedExts($mustExtensions)
                    ->setDisallowedExts($notExtensions)
                ;

                $this->addRestrictionSet($id, $res);

                return $res;
            }

            throw new \InvalidArgumentException("No set with id `$id`");
        }

        return $this->restrictionSets[$id];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param $restrictionSetId
     *
     * @return array|null
     */
    public function getError(UploadedFile $file = null, $restrictionSetId = null)
    {
        $restriction = null;
        if ($restrictionSetId) {
            $restriction = $this->getRestrictionSet($restrictionSetId);
        }

        $is_email = strpos($restrictionSetId, 'email') !== false;

        $max_size = min(\Orb\Util\Env::getEffectiveMaxUploadSize(), $restriction->getMaxSize());

        if ($file === null) {
            // This means the file is too big and PHP basically rejected the whole request data
            if (!$is_email && isset($_SERVER['CONTENT_LENGTH']) && empty($_POST) && empty($_FILES)) {
                return ['error_code' => self::ERR_SIZE, 'error_detail' => Numbers::filesizeDisplay($max_size)];
            }

            return ['error_code' => self::ERR_NO_FILE, 'error_detail' => 'null_file'];
        }

        $log_error = false;
        $error     = [
            'error_code'   => null,
            'error_detail' => null,
        ];

        if (!$file->isValid()) {
            switch ($file->getError()) {
                case \UPLOAD_ERR_INI_SIZE:
                    $error['error_code']   = self::ERR_SIZE;
                    $error['error_detail'] = Numbers::filesizeDisplay($max_size);
                    break;

                case \UPLOAD_ERR_PARTIAL:
                    $error['error_code']   = self::ERR_FAILED;
                    $error['error_detail'] = '';
                    break;

                case \UPLOAD_ERR_NO_FILE:
                    $error['error_code']   = self::ERR_NO_FILE;
                    $error['error_detail'] = '';
                    break;

                case \UPLOAD_ERR_NO_TMP_DIR:
                    $log_error             = true;
                    $error['error_code']   = self::ERR_SERVER;
                    $error['error_detail'] = 'bad_tmp_dir';
                    break;

                case \UPLOAD_ERR_CANT_WRITE:
                    $log_error             = true;
                    $error['error_code']   = self::ERR_SERVER;
                    $error['error_detail'] = 'failed_write';
                    break;

                case \UPLOAD_ERR_EXTENSION:
                    $log_error             = true;
                    $error['error_code']   = self::ERR_SERVER;
                    $error['error_detail'] = 'ext_stopped';
                    break;

                default:
                    $log_error             = true;
                    $error['error_code']   = self::ERR_SERVER;
                    $error['error_detail'] = $file->getError();
                    break;
            }
        }

        if (!$error['error_code']) {
            if (!is_uploaded_file($file->getRealPath()) || !file_exists($file->getRealPath())) {
                $error['error_code']   = self::ERR_NO_FILE;
                $error['error_detail'] = '';
            }
        }

        if (!$error['error_code']) {
            $error = null;
        }

        if (!$error && $restriction) {
            $error = $restriction->getError($file);
        }

        if (!$error || !$error['error_code']) {
            return;
        }

        if ($log_error) {
            $info = "Upload of {$file->getClientOriginalName()} failed because {$error['error_code']}\n";
            $info .= Arrays::implodeTemplate([
                'error_code'    => $error['error_code'],
                'error_detail'  => $error['error_detail'],
                'filename'      => $file->getClientOriginalName(),
                'type'          => $file->getClientMimeType(),
                'size'          => $file->getClientSize(),
                'file_err_code' => $file->getError(),
            ], "{KEY}: {VAL}\n");

            $e = new \Exception($info, 0);
            SystemErrorHandler::logException($e, false);
        }

        return $error;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param bool                                                $is_temp
     * @param array|null                                          $props   Props array passed to DeskproBlobStorage
     *
     * @return \Application\DeskPRO\Entity\Blob
     */
    public function accept(UploadedFile $file, $is_temp = false, $props = [])
    {
        try {
            $mime_type = $file->getMimeType();
        } catch (\Exception $e) {
            $mime_type = $file->getClientMimeType();
        }

        if (!$mime_type) {
            $mime_type = ContentTypes::getContentTypeFromFilename($file->getClientOriginalName());
        }

        if (!$mime_type) {
            $mime_type = 'application/octet-stream';
        }

        $filename = $file->getClientOriginalName();
        if (!$filename) {
            $filename = crc32(mt_rand(1111, 9999).mt_rand(1111, 9999).mt_rand(1111, 9999).mt_rand(1111, 9999));
            $ext      = ContentTypes::findExtensionForContentType($mime_type);
            if ($ext) {
                $filename .= '.'.$ext;
            }
        }

        $blob = $this->blobstorage->createBlobRecordFromFile(
            $file->getRealPath(),
            $filename,
            $mime_type,
            array_merge(['is_temp' => $is_temp], $props)
        );

        return $blob;
    }
}
