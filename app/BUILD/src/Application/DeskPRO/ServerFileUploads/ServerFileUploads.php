<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ServerFileUploads;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;
use Orb\Util\Env;
use Orb\Util\Numbers;
use Orb\Util\OptionsArray;

class ServerFileUploads
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getPhpVars()
    {
        $phpVars = [];

        foreach (['file_uploads', 'upload_tmp_dir', 'upload_max_filesize', 'post_max_size'] as $var) {
            $phpVars[$var] = @ini_get($var);
        }

        $phpVars['upload_tmp_dir_real'] = Env::getUploadTempDir();
        $phpVars['memory_limit']        = Env::getMemoryLimit();
        $phpVars['memory_limit_real']   = DP_REAL_MEMSIZE;

        return $phpVars;
    }

    /**
     * @return string
     */
    public function getEffectiveMaxUploadSize()
    {
        $effective_max = Env::getEffectiveMaxUploadSize();

        $result = Numbers::getFilesizeDisplayParts($effective_max, 'cs');
        $result = ($result['number'] > 1 ?
                floor($result['number']) :
                $result['number'])
            .' '.$result['symbol'];

        return $result;
    }

    /**
     * @return string
     */
    public function getUrlToLearnPhpIni()
    {
        return App::get('deskpro.service_urls')->get('dp.kb.editing_php_ini');
    }

    /**
     * @return string
     */
    public function getPhpIniPath()
    {
        return Env::getPhpIniPath();
    }

    /**
     * @return array
     */
    public function getRestrictions()
    {
        return [
            'attach_user_maxsize'    => App::getSetting('core.attach_user_maxsize'),
            'attach_agent_maxsize'   => App::getSetting('core.attach_agent_maxsize'),
            'attach_user_not_exts'   => App::getSetting('core.attach_user_not_exts'),
            'attach_user_must_exts'  => App::getSetting('core.attach_user_must_exts'),
            'attach_agent_not_exts'  => App::getSetting('core.attach_agent_not_exts'),
            'attach_agent_must_exts' => App::getSetting('core.attach_agent_must_exts'),
        ];
    }

    /**
     * @return array
     */
    public function getMovingFiles()
    {
        $movingId = App::getContainer()->getSetting('core.filesystem_move_from_id');

        if ($movingId) {
            if ($movingId < 1) {
                $countDone = 0;
            } else {
                $countDone = App::getDb()->fetchColumn(
                    'SELECT COUNT(*) FROM blobs WHERE id < ?',
                    [$movingId]
                );
            }

            $countTodo = App::getDb()->fetchColumn('SELECT COUNT(*) FROM blobs', [$movingId]);

            if (!$countTodo) {
                $countTodo = 1;
            }

            $countLeft       = $countTodo - $countDone;
            $countPercentage = floor(($countDone / $countTodo) * 100);
        } else {
            $countDone = $countTodo = $countLeft = $countPercentage = 0;
            $countTodo = App::getDb()->fetchColumn('SELECT COUNT(*) FROM blobs');
        }

        $totalSize         = App::getDb()->fetchColumn('SELECT SUM(filesize) FROM blobs');
        $totalSizeReadable = Numbers::filesizeDisplay($totalSize);

        return [
            'id'               => $movingId,
            'count_done'       => $countDone,
            'count_left'       => $countLeft,
            'count_percentage' => $countPercentage,
            'count_todo'       => $countTodo,
            'total_size'       => $totalSizeReadable,
        ];
    }

    /**
     * @return bool
     */
    public function getStorageMethod()
    {
        return App::getContainer()->getSetting('core.filestorage_method');
    }

    /**
     * @return string
     */
    public function getFileStoragePath()
    {
        return App::getContainer()->getBlobDir();
    }

    /**
     * @return string
     */
    public function getFileUploaderUrl()
    {
        return App::getRouter()->generate('api_server_file_uploads');
    }

    /**
     * @param $file
     *
     * @return array
     */
    public function getUploadResults($file)
    {
        $uploadFailed = false;
        $attachUrl    = '';

        $accept = App::getContainer()->getAttachmentAccepter();
        $error  = $accept->getError($file, 'agent');

        if ($error) {
            $uploadFailed = App::getContainer()->getTranslator()->phrase(
                'agent.general.attach_error_'.$error['error_code'],
                $error
            );
        } else {
            $attachUrl = $accept->accept($file)->getDownloadUrl();
        }
        $uploadTempDir = Env::getUploadTempDir();

        return [
            'upload_failed'     => $uploadFailed,
            'uploaded_file_url' => $attachUrl,
            'tmp_dir'           => $uploadTempDir,
            'is_tmp_writable'   => $uploadTempDir && is_writable($uploadTempDir),
        ];
    }

    /**
     * @param array $options
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function switchStorage(array $options)
    {
        $options = new OptionsArray($options);

        $settings = App::$container->getSettingsHandler();
        $db       = App::$container->getDb();

        $method = $options->get('method', 'db');

        switch ($options->get('method', 'db')) {
            case 'db':
                $settings->setSetting('core.filestorage_method', 'db');
                $db->executeUpdate("UPDATE blobs SET storage_loc_pref = 'db' WHERE storage_loc != 'db' AND storage_loc_specific IS NULL");
                break;

            case 'fs':
                $settings->setSetting('core.filestorage_method', 'fs');
                $db->executeUpdate("UPDATE blobs SET storage_loc_pref = 'fs' WHERE storage_loc != 'fs' AND storage_loc_specific IS NULL");
                break;

            case 's3':
                $settings->setSetting('core.filestorage_method', 's3');
                $db->executeUpdate("UPDATE blobs SET storage_loc_pref = 's3' WHERE storage_loc != 's3' AND storage_loc_specific IS NULL");
                $settings->setSetting('core.filestorage_s3_bucket', $options->get('s3_bucket', null));
                $settings->setSetting('core.filestorage_s3_region', $options->get('s3_region', null));
                $settings->setSetting('core.filestorage_s3_endpoint', $options->get('s3_endpoint', null));
                $settings->setSetting('core.filestorage_s3_file_url_template', $options->get('s3_file_url_template', null));
                $credentialsSource = $options->get('s3_credentials_source', null);
                $settings->setSetting('core.filestorage_s3_credentials_source', $credentialsSource);
                if ($credentialsSource === 'ec2') {
                    $settings->setSetting('core.filestorage_s3_key', null);
                    $settings->setSetting('core.filestorage_s3_secret', null);
                } else {
                    $settings->setSetting('core.filestorage_s3_key', $options->get('s3_key', null));
                    $settings->setSetting('core.filestorage_s3_secret', $options->get('s3_secret', null));
                }

                break;
        }

        if ($method != 's3') {
            $settings->setSetting('core.filestorage_s3_key', null);
            $settings->setSetting('core.filestorage_s3_secret', null);
            $settings->setSetting('core.filestorage_s3_bucket', null);
            $settings->setSetting('core.filestorage_s3_region', null);
            $settings->setSetting('core.filestorage_s3_endpoint', null);
            $settings->setSetting('core.filestorage_s3_file_url_template', null);
            $settings->setSetting('core.filestorage_s3_credentials_source', null);
        }

        $settings->setSetting('core.filesystem_move_from_id', '-1');
    }

    /**
     * @return array
     */
    public function switchStorageStatus()
    {
        $transfer = $this->getMovingFiles();

        $toMethod = App::$container->getSettingsHandler()->get('core.filestorage_method');

        if (!empty($transfer['id'])) {
            $status = 'progress';

            switch ($toMethod) {
                case 'db':
                    $message = 'Currently transferring files to the database';
                    break;
                case 'fs':
                    $message = 'Currently transferring files to the filesystem';
                    break;
                case 's3':
                    $message = 'Currently transferring files AmazonS3';
                    break;
                default:
                    throw new \LogicException('Unknown storage for files is set');
            }

            $message = sprintf(
                '%s %d of %d (%d%%) files have been processed',
                $message,
                $transfer['count_done'],
                $transfer['count_todo'],
                $transfer['count_percentage']
            );
        } else {
            $status  = 'completed';
            $message = 'Transferring done!';
        }

        return [
            'status'  => $status,
            'message' => $message,
        ];
    }
}
