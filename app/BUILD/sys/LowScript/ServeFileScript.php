<?php

namespace DpSys\LowScript;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\Blob;
use DpSys\CodePlugin\DpPlugins;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Exception\RuntimeException;
use Imagine\Image\Box;
use Orb\Data\ContentTypes;
use Orb\Util\Colors;
use Orb\Util\Strings;
use Orb\Util\Util;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

if (!isset($DP_LOG_MESSAGES)) {
    $DP_LOG_MESSAGES = [];
}

/**
 * Database and filesystem-stored files are served through this file.
 *
 * Every blob has an "authcode" which doubely serves as a sort of password,
 * but it also embeds the blobs own ID and other required data.
 *
 * Database Files
 * --------------
 *
 * There is nothing very special about database-stored files. The blobs authcode is:
 * (id)(password)(0)
 *
 * That is the blob ID, a random string, and zero. The trailing zero tells this script
 * that it needs to fetch it from the database rather than the filesystem.
 *
 * Filesystem Files
 * ----------------
 *
 * Files in the filesystem are stored under the file root in folders counting up from 0. Each
 * folder has 1000 files in it.
 *
 * The authcode is:
 * (folder)(password)(id)(namehash)
 *
 * The "namehash" is a specil hash of the file filename. Since we don't connect to the database,
 * there's no way to know what the "real" filename of a file is. We output URLs with the correct filename in it
 * (aka it's a template-time thing), but we use the namehash to verify it is correct as an anti-spoofing mechanism.
 *
 * The hash is six characters, the three is part of a sha1 and the second three is part of an md5. While probably
 * possible to spoof the name still, using two different hashing functions should make it relatively hard.
 *
 * Since we can now trust the filename, we can use it to guess a mime-type based on extension, and send the correct headers,
 * all without connecting to the database.
 */
class ServeFileScript extends LowScriptAbstract
{
    /**
     * @var string
     */
    protected $errorMode = 'error';

    /** @var bool this setting is only overwritten by the apps v2 asset serving router */
    private $alwaysForceDownloadOfHtmlFiles = true;

    /**
     * @var bool
     */
    protected $localMode = false;

    public function runAction()
    {
        if (isset($_GET['debug'])) {
            $this->errorMode = 'exception';
        }

        try {
            $pathInfo = $this->getPathInfo();

            $this->addLogMessage('pathinfo: %s', $pathInfo);

            // local URLs just disable redirection action on remote URLs (e.g., S3)
            // Used to serve app assets where serving from a remote domain
            // could cause same-origin policy errors
            if (preg_match('#^/local/#', $pathInfo)) {
                $this->localMode = true;
                $pathInfo        = preg_replace('#^/local/#', '/', $pathInfo);
            }

            if (isset($_GET['local'])) {
                $this->localMode = true;
            }

            if (preg_match('#^/size/([0-9]+)/#', $pathInfo, $m)) {
                $_GET['s'] = $m[1];
                $pathInfo  = str_replace($m[0], '/', $pathInfo);
            }
            if (preg_match('#^/size-fit/#', $pathInfo, $m)) {
                $_GET['size-fit'] = 1;
                $pathInfo         = str_replace($m[0], '/', $pathInfo);
            }

            // Default avatar: /avatar/50/default
            if (preg_match('#^/avatar/([0-9]+)/default.jpg#', $pathInfo, $m)) {
                $this->defaultAvatarAction($m[1]);

            // Person avatar: /avatar/13
            } elseif (preg_match('#^/avatar/([0-9]+)#', $pathInfo, $m)) {
                $this->personAvatarAction($m[1]);

            // Default org avatar: /o-avatar/default
            } elseif (preg_match('#^/o-avatar/default#', $pathInfo)) {
                $this->defaultOrgAvatarAction();

            // A standard asset
            } elseif (preg_match('#^/dp-asset/([a-zA-Z0-9_\.\-\/]+)$#', $pathInfo, $m)) {
                $this->dpAsset($m[1]);

            // A standard asset
            } elseif (preg_match('#^/dp-asset/([a-zA-Z0-9_\.\-]+)$#', $pathInfo, $m)) {
                $this->dpAsset($m[1]);

            // Org avatar: /o-avatar/13
            } elseif (preg_match('#^/o-avatar/([0-9]+)#', $pathInfo, $m)) {
                $this->orgAvatarAction($m[1]);

            // sitemap.xml
            } elseif (preg_match('#^/sitemap.xml#', $pathInfo, $m)) {
                $this->sitemapXmlAction();

            // A filesystem blob like /123AJKJKHSD1244AXC/filename.zip
            // That is: /(batch)(authcode)(id)(namehash)/name.zip
            //0XNSNTQHTNR43DD567
            } elseif (preg_match('#^/([0-9]+)([A-Z]+)([0-9]+)([a-fA-F0-9]{6}T?)(?:/|\-)(.*?)$#', $pathInfo, $m)) {
                $this->addLogMessage('handleFilesystemBlobRequest: %s', implode(', ', $m));
                $this->handleFilesystemBlobRequest(
                    $m[1],
                    $m[2],
                    $m[3],
                    $m[4],
                    $m[5]
                );

            // A database-stored bloblike /123AHSDHJGSD0/filename.zip
            // That is (id)(authcode0)
            // The trailing 0 denotes it as a database storage authcode
            } elseif (preg_match('#^/([0-9]+)([A-Z]+0T?)(?:/|\-)(.*?)$#', $pathInfo, $m)) {
                $this->addLogMessage('handleDbBlobRequest: %s', implode(', ', $m));
                $this->handleDbBlobRequest($m[1], $m[2], $m[3]);
            } elseif (preg_match('#^/brand-[0-9]+/([0-9]+)([A-Z]+0T?)(?:/|\-)(.*?)$#', $pathInfo, $m)) {
                $this->addLogMessage('handleDbBlobRequest: %s (legacy brand route)', implode(', ', $m));
                $this->handleDbBlobRequest($m[1], $m[2], $m[3]);
            } elseif (preg_match('#^/gradient$#', $pathInfo)) {
                $this->handleGradientRequest();
            } elseif (preg_match('#^/apps/([a-zA-Z0-9_\-\.]+)/(app|js|css|html|res)/(.*?)$#', $pathInfo, $m)) {
                $this->handleAppsRequest($m[1], $m[2], $m[3]);
            } elseif (preg_match('#^/apps/([^/]+)/v[^/]+/files/(.+)$#', $pathInfo, $m)) {
                $this->handleAppsV2FileRequest($m[1], $m[2]);
            } else {
                if ($this->errorMode == 'exception') {
                    throw new \Exception('File not found. (bad_route)', 400);
                }
                header('HTTP/1.0 404 Not Found');
                echo 'File not found. (bad_route)';
            }
        } catch (\Exception $exception) {
            if ($this->dpEnv->isDebug()) {
                if (!empty($GLOBALS['DP_LOG_MESSAGES'])) {
                    echo "\n\n\n";
                    echo "LOG\n".str_repeat('=', 72)."\n";
                    foreach ($GLOBALS['DP_LOG_MESSAGES'] as $minfo) {
                        printf("[%s] %s\n", date('Y-m-d H:i:s', $minfo['time']), $minfo['message']);
                    }
                }

                echo "\n\n\nEXCEPTION\n".str_repeat('=', 72)."\n";
                echo "[{$exception->getCode()}] {$exception->getMessage()}\n\n";

                $backtrace = $exception->getTrace();
                $trace     = self::formatBacktrace($backtrace);
                echo $trace;
            }

            $this->handleException($exception);
        }
    }

    /**
     * @param string $message
     */
    private function addLogMessage($message)
    {
        global $DP_LOG_MESSAGES;

        $args = func_get_args();
        array_shift($args);

        if ($args) {
            $message = vsprintf($message, $args);
        }

        $DP_LOG_MESSAGES[] = [
            'time'    => time(),
            'message' => $message,
        ];
    }

    /**
     * Generates a gradient image on the fly.
     *
     * @throws \Exception
     */
    public function handleGradientRequest()
    {
        if (!function_exists('imagepng') || (!function_exists('imagecreatetruecolor') && !function_exists('imagecreate'))) {
            if ($this->errorMode == 'exception') {
                throw new \Exception('File not found. (no_image_manip)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'File not found (no_image_manip)';

            return;
        }

        require DP_ROOT.'/src/Orb/Util/Colors.php';
        require DP_ROOT.'/src/Orb/Images/Util.php';

        $startColor = isset($_REQUEST['start_color']) ? (string) $_REQUEST['start_color'] : '000000';
        $endColor   = isset($_REQUEST['end_color']) ? (string) $_REQUEST['end_color'] : '000000';

        $getRgb = function ($color) {
            // Not rgb(
            if (!strpos($color, '(') || !strpos($color, ')')) {
                $color = preg_replace('#[^a-fA-F0-9]#', '', $color);
                if (strlen($color) == 6 || strlen($color) == 3) {
                    $color = Colors::hex2rgb($color);
                    if ($color) {
                        $color = 'rgb('.implode(',', $color).')';
                    } else {
                        $color = 'rgb(0,0,0)';
                    }
                } else {
                    $color = 'rgb(0,0,0)';
                }
            }

            if (preg_match('#rgb\((.*?),(.*?),(.*?)\)#i', $color, $m)) {
                $rgb = [
                    'red'   => (int) trim($m[1]),
                    'green' => (int) trim($m[2]),
                    'blue'  => (int) trim($m[3]),
                ];

                return $rgb;
            } else {
                return ['red' => 0, 'green' => 0, 'blue' => 0];
            }
        };

        $startColor = $getRgb($startColor);
        $endColor   = $getRgb($endColor);

        $size = isset($_REQUEST['size']) ? (int) $_REQUEST['size'] : 20;
        if ($size < 1) {
            $size = 20;
        }
        if ($size > 1000) {
            $size = 1000;
        }

        $direction = isset($_REQUEST['direction']) ? $_REQUEST['direction'] : 'vertical';
        if ($direction != 'vertical' && $direction != 'horizontal') {
            $direction = 'vertical';
        }

        $im = \Orb\Images\Util::getGradientImage($size, $startColor, $endColor, $direction, 1, 1);

        $desc = implode('-', $startColor).'_'.implode('-', $endColor).'_'.$direction.'_'.$size.'.png';

        header('Last-Modified: '.date('D, d M Y H:i:s', 1366187634).' GMT');
        header('Expires: '.date('D, d M Y H:i:s', 1366187657).' GMT');
        header('Cache-Control: max-age=31556926,public');
        header('Content-Disposition: inline; filename='.$desc);
        header('Content-type: image/png');
        header('X-Content-Type-Options: nosniff');
        header('X-Robots-Tag: noindex, nofollow');

        imagepng($im);
        exit;
    }

    /**
     * Serve the sitemap.xml file.
     *
     * @throws \Exception
     */
    public function sitemapXmlAction()
    {
        $sth = $this->getPdoRead()->prepare("
            SELECT *
            FROM blobs
            WHERE sys_name = 'sitemap_xml'
        ");
        $sth->execute();
        $blob = $sth->fetch(\PDO::FETCH_ASSOC);

        if (!$blob) {
            if ($this->errorMode == 'exception') {
                throw new \Exception('File not found. (no_sitemap_blob)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'File not found. (no_sitemap_blob)';

            return;
        }

        $this->showBlob($blob);
    }

    /**
     * Render a persons avatar.
     *
     * @deprecated If you have a person record, then use picture_blob_id and directly link to the avatar
     *
     * @param $person_id
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function personAvatarAction($person_id)
    {
        $sth = $this->getPdoRead()->prepare('
            SELECT *
            FROM blobs
            LEFT JOIN people ON (blobs.id = people.picture_blob_id)
            WHERE people.id = :person_id
        ');
        $sth->execute(['person_id' => $person_id]);
        $blob = $sth->fetch(\PDO::FETCH_ASSOC);

        if (!$blob) {
            $this->defaultAvatarAction();

            return;
        }

        $size = null;
        if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] <= 600) {
            $size = $_GET['s'];
        }

        $this->showBlob($blob, $size);

        return;
    }

    /**
     * Render an org avatar.
     *
     * @deprecated If you have a org record, then use picture_blob_id and directly link to the avatar
     *
     * @param $org_id
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function orgAvatarAction($org_id)
    {
        $sth = $this->getPdoRead()->prepare('
            SELECT *
            FROM blobs
            LEFT JOIN organizations ON (blobs.id = organizations.picture_blob_id)
            WHERE organizations.id = :org_id
        ');
        $sth->execute(['org_id' => $org_id]);
        $blob = $sth->fetch(\PDO::FETCH_ASSOC);

        if (!$blob) {
            $this->defaultOrgAvatarAction();

            return;
        }

        $size = null;
        if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] <= 600) {
            $size = $_GET['s'];
        }

        $this->showBlob($blob, $size);

        return;
    }

    /**
     * Serve the default avatar.
     *
     * @param null $s
     *
     * @throws \Exception
     */
    public function defaultAvatarAction($s = null)
    {
        $name = 'picture-default';
        if (isset($_GET['is_agent'])) {
            $name = 'picture-default-agent';
        } elseif (isset($_GET['is_team'])) {
            $name = 'picture-default-team';
        } elseif (isset($_GET['is_dep'])) {
            $name = 'picture-default-dep';
        }

        $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE sys_name = :sys_name');
        $sth->execute(['sys_name' => $name]);
        $blob = $sth->fetch(\PDO::FETCH_ASSOC);

        // The default avatar blob hasnt been inserted yet, default it from the resources dir now
        if (!$blob) {
            $file = DP_ROOT.'/src/Application/DeskPRO/Resources/assets/'.$name.'.jpeg';
            $mime = 'image/jpeg';
            if (!file_exists($file)) {
                $file = DP_ROOT.'/src/Application/DeskPRO/Resources/assets/'.$name.'.png';
                $mime = 'image/png';
            }

            $container  = $this->bootFullSystem();
            $blobEntity = $container->getBlobStorage()->createBlobRecordFromFile(
                $file,
                pathinfo($file, PATHINFO_BASENAME),
                $mime,
                ['sys_name' => $name]
            );

            $blob = $blobEntity->toArray(DomainObject::TOARRAY_ONLY_PRIMATIVES);
        }

        $size = null;
        if ($s !== null) {
            $size = (int) $s;
        } elseif (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] <= 600) {
            $size = $_GET['s'];
        }

        $this->showBlob($blob, $size);
    }

    /**
     * @param string $asset_name
     *
     * @throws \Exception
     */
    public function dpAsset($asset_name)
    {
        $disposition = 'attachment';

        switch ($asset_name) {
            case 'Getting-Started-with-DeskPRO.pdf':
                $path     = DP_ROOT.'/src/Application/AgentBundle/Resources/assets/agent-quickstart/en_US.pdf';
                $filename = 'Getting Started with DeskPRO.pdf';
                $mimetype = 'application/pdf';
                break;

            case 'Admin-Bulk-Add-Agents-Spreadsheet.zip':
                $path     = DP_ROOT.'/src/Application/AdminInterfaceBundle/Resources/assets/Bulk-Add-Agents-Spreadsheet-Template.zip';
                $filename = 'Bulk-Add-Agents-Spreadsheet-Template.zip';
                $mimetype = 'application/zip';
                break;

            default:
                $path = DpPlugins::getManager()->getServeAssetFilePath($asset_name);
                if (!$path || !is_file($path)) {
                    if ($this->errorMode == 'exception') {
                        throw new \Exception('File not found. (300)', 400);
                    }
                    header('HTTP/1.0 404 Not Found');
                    echo 'File not found. (300)';

                    return;
                }

                $filename = basename($path);
                $mimetype = ContentTypes::getContentTypeFromFilename($filename);
                if (ContentTypes::isImageContentType($mimetype) || ContentTypes::isInlineContentType($mimetype, false)) {
                    $disposition = 'inline';
                }
        }

        $filesize = filesize($path);

        header('Content-Type: '.$mimetype.'; filename="'.addslashes($filename).'"');
        header('Content-Length: '.$filesize);
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: '.$disposition.'; filename="'.addslashes($filename).'"');
        header('X-Robots-Tag: noindex, nofollow');

        if ($this->dpEnv->getConfig('settings.filestorage_use_xsendfile')) {
            header("X-Sendfile: $path");
        } else {
            readfile($path);
        }
    }

    /**
     * Serve the default org avatar.
     *
     * @throws \Exception
     */
    public function defaultOrgAvatarAction()
    {
        $name = 'orgpicture-default';

        $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE sys_name = :sys_name');
        $sth->execute(['sys_name' => $name]);
        $blob = $sth->fetch(\PDO::FETCH_ASSOC);

        // The default avatar blob hasnt been inserted yet, default it from the resources dir now
        if (!$blob) {
            $container  = $this->bootFullSystem();
            $blobEntity = $container->getBlobStorage()->createBlobRecordFromFile(
                DP_ROOT.'/src/Application/DeskPRO/Resources/assets/'.$name.'.jpeg',
                $name.'.jpeg',
                'image/jpeg',
                ['sys_name' => $name]
            );

            $blob = $blobEntity->toArray(DomainObject::TOARRAY_ONLY_PRIMATIVES);
        }

        $size = null;
        if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] <= 600) {
            $size = $_GET['s'];
        }

        $this->showBlob($blob, $size);
    }

    /**
     * @param $batch
     * @param $authcode
     * @param int $blob_id
     * @param $namehash
     * @param $filename
     *
     * @throws \Exception
     */
    protected function handleFilesystemBlobRequest($batch, $authcode, $blob_id, $namehash, $filename)
    {
        //------------------------------
        // If its a simple file request we
        // can serve it without a db connection
        //------------------------------

        $basePath = $this->dpEnv->getUserFilesDir();

        $filepathPart = $batch.DIRECTORY_SEPARATOR.$batch.$authcode.$blob_id.$namehash;
        $filepath     = $basePath.DIRECTORY_SEPARATOR.$filepathPart;

        $filenameSafe = Strings::utf8_accents_to_ascii($filename);
        $filenameSafe = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $filenameSafe);
        $filenameSafe = preg_replace('#\-{2,}#', '-', $filenameSafe);

        $checkNamehash = strtoupper(substr(sha1($filenameSafe.$blob_id), 0, 3));
        $checkNamehash .= strtoupper(substr(md5($filenameSafe.$blob_id), 0, 3));

        $this->addLogMessage('Expecting file path: %s', $filepath);

        $size = null;
        if (isset($_GET['s']) && ((is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] <= 600) || preg_match('#^\d+x\d+$#', $_GET['s']))) {
            $size = $_GET['s'];
            $this->addLogMessage('With size: %s', $size);
        }

        // Invalid name hash
        // But we have to double-check before failing since the filename could
        // possibly be custom in the case of downloads
        if ($checkNamehash != $namehash) {
            $this->addLogMessage('Hash mismatch: %s !=', $checkNamehash, $namehash);

            $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE id = :id');
            $sth->execute(['id' => $blob_id]);
            $blob = $sth->fetch(\PDO::FETCH_ASSOC);

            if ($blob['filename']) {
                $blob['filename_safe'] = Strings::utf8_accents_to_ascii($blob['filename']);
                $blob['filename_safe'] = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $blob['filename_safe']);
                $blob['filename_safe'] = preg_replace('#\-{2,}#', '-', $blob['filename_safe']);
            }

            if (!$blob || ($blob['filename'] != $filename && $blob['filename_safe'] != $filename && $blob['filename_safe'] != $filenameSafe)) {
                if ($this->errorMode == 'exception') {
                    throw new \Exception('File not found. (2.1)', 400);
                }
                header('HTTP/1.0 404 Not Found');
                echo 'File not found. (2.1)';

                return;
            }
        }

        // Check if we have a record of it being moved
        if (!file_exists($filepath)) {
            $movedBlob = $this->findMovedAuthBlob($authcode.$blob_id.$namehash);
            if ($movedBlob) {
                $this->showBlob($movedBlob, $size);

                return;
            }
        }

        // The file doesnt exist on disk
        if (!file_exists($filepath)) {
            $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE id = :id');
            $sth->execute(['id' => $blob_id]);
            $blob = $sth->fetch(\PDO::FETCH_ASSOC);

            // Fallback on DB check, it may have been moved
            if ($blob && $blob['storage_loc'] !== 'fs') {
                $this->showBlob($blob_id, $size);

                return;
            }

            header('HTTP/1.0 404 Not Found');
            echo 'File not found. (2.2)';

            return;
        }

        //------------------------------
        // See if we need to resize
        //------------------------------

        if ($size) {
            $this->showBlob($blob_id, $size);

            return;
        }

        //------------------------------
        // Serve up
        //------------------------------

        $mimetype = ContentTypes::getContentTypeFromFilename($filename);
        if (!$mimetype) {
            $mimetype = 'application/octet-stream';
        }

        $contentDisposition = 'attachment';
        if (!isset($_GET['dl']) && ContentTypes::isInlineContentType($mimetype, true, $filename)) {
            $contentDisposition = 'inline';
        }

        header('Content-Type: '.$mimetype.'; filename="'.addslashes($filename).'"');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: '.filesize($filepath));
        header('Content-Disposition: '.$contentDisposition.'; filename="'.addslashes($filename).'"');
        header('Last-Modified: '.date('D, d M Y H:i:s', strtotime('2010-01-01')).' GMT');
        header('Expires: '.date('D, d M Y H:i:s', strtotime('+1 year')).' GMT');
        header('Cache-Control: max-age=31556926,private');
        header('X-Robots-Tag: noindex, nofollow');

        if ($this->dpEnv->getConfig('settings.filestorage_use_xsendfile')) {
            header("X-Sendfile: $filepath");
        } else {
            readfile($filepath);
        }
    }

    /**
     * @param $blob_id
     * @param $authseg
     * @param $filename
     *
     * @throws \Exception
     */
    protected function handleDbBlobRequest($blob_id, $authseg, $filename)
    {
        $authcode = $blob_id.$authseg;

        $size = null;
        if (isset($_GET['s']) && ((is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] <= 600) || preg_match('#^\d+x\d+$#', $_GET['s']))) {
            $size = $_GET['s'];
        }

        $this->showBlob($blob_id, $size, $authcode);
    }

    /**
     * Renders a blob.
     *
     * @param int|array   $blob
     * @param int|null    $size
     * @param string|null $blobAuth
     *
     * @throws \Exception
     */
    protected function showBlob($blob, $size = null, $blobAuth = null)
    {
        if ($this->userDoesAcceptGzip() && isset($_GET['g'])) {
            if (preg_match('#^([0-9]+)([A-Z]+0)$#', $_GET['g'], $m)) {
                $blobAuth = $_GET['g'];
                $blob     = $m[1];
            }
        }

        //------------------------------
        // Fetch the blob
        //------------------------------

        if (!is_array($blob)) {
            $blobId = $blob;

            $this->addLogMessage('Loading blob %d', $blobId);

            $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE id = :id');
            $sth->execute(['id' => $blobId]);
            $blob = $sth->fetch(\PDO::FETCH_ASSOC);

            if (!$blob) {
                $this->addLogMessage('Could not load blob record');
            } elseif ($blobAuth && $blob['authcode'] != $blobAuth) {
                $this->addLogMessage('bad authcode: %s != %s', $blob['authcode'], $blobAuth);

                // check if it was moved
                $movedBlob = $this->findMovedAuthBlob($blobAuth);
                if ($movedBlob && $movedBlob['id'] == $blob['id']) {
                    $this->addLogMessage('blob was moved');
                    $blobAuth = $movedBlob['authcode'];
                }
            }

            if (!$blob || ($blobAuth && $blob['authcode'] != $blobAuth)) {
                // Check for a sign code that overrides the authcode check
                // (See TicketMessage::procInlineAttach)
                $okay = false;
                if (!empty($_GET['sc'])) {
                    $sth = $this->getPdoRead()->prepare("SELECT value FROM settings WHERE name = 'core.install_token'");
                    $sth->execute();
                    $installToken = $sth->fetchColumn(0);

                    if (Util::checkStaticSecurityToken($_GET['sc'], $installToken.$blobAuth)) {
                        $okay = true;
                    }
                }

                if (!$okay) {
                    if ($this->errorMode == 'exception') {
                        throw new \Exception('File not found. (3)', 400);
                    }
                    header('HTTP/1.0 404 Not Found');
                    echo 'File not found. (3)';

                    return;
                }
            }
        }

        if (!is_array($blob) || empty($blob)) {
            if ($this->errorMode == 'exception') {
                throw new \Exception('File not found. (3.1)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'File not found. (3.1)';

            return;
        }

        $blobId = $blob['id'];

        //------------------------------
        // Serve the file
        //------------------------------

        if (!isset($blob['filename_safe'])) {
            $filenameSafe          = Strings::utf8_accents_to_ascii($blob['filename']);
            $filenameSafe          = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $filenameSafe);
            $filenameSafe          = preg_replace('#\-{2,}#', '-', $filenameSafe);
            $blob['filename_safe'] = $filenameSafe;
        }

        $isImage = false;
        switch ($blob['content_type']) {
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/gif':
            case 'image/png':
                $isImage = true;
                break;
        }

        $isText = false;
        if (strpos($blob['content_type'], 'text/') === 0) {
            $isText = true;
        } else {
            switch ($blob['content_type']) {
                case 'application/json':
                case 'application/javascript':
                case 'application/xml':
                case 'application/xhtml+xml':
                    $isText = true;
                    break;
            }
        }

        if ($isImage && $size) {
            $this->addLogMessage('Showing resized: '.$size);

            $isFit = false;

            if (isset($_GET['size-fit'])) {
                $isFit = (bool) $_GET['size-fit'];
                $this->addLogMessage('Is fit: %d', $isFit);
            }

            $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE original_blob_id = :original_blob_id AND sys_name = :sys_name');
            $sth->execute(['original_blob_id' => $blobId, 'sys_name' => $this->getSizedBlobSysName($blobId, $size, $isFit)]);
            $subBlob = $sth->fetch(\PDO::FETCH_ASSOC);

            // Already have the cached resized blob
            if ($subBlob) {
                $subBlob['filename_safe'] = $blob['filename_safe'];
                $blob                     = $subBlob;

            // Generate the resized blob and save it now
            } else {
                // Check abuse for resize image cache
                // If we have more than max allowed resized image copies then don't create a new blob for resized cache, just proceed it in runtime
                $sth = $this->getPdoRead()->prepare('SELECT COUNT(*) FROM blobs WHERE original_blob_id = :original_blob_id AND sys_name LIKE :sys_name');
                $sth->execute(['original_blob_id' => $blobId, 'sys_name' => $this->getSizedBlobSysName($blobId, '%', false)]);
                $count = $sth->fetch(\PDO::FETCH_COLUMN);

                if ($count > 25) {
                    $file = $this->createSizedImage($blob, $size, $isFit, false);

                    $blob['filesize'] = strlen($file);
                    $headers          = $this->getHeaders($blob);
                    $response         = new Response($file, 200, $headers);

                    $response->send();

                    return;
                }

                $newBlob = $this->createSizedBlob($blob, $size, $isFit, false);

                if ($newBlob) {
                    // Possible the resize failed, in which case we'd fall back on showing the orig
                    // So only reassign blob if we know $new_blob was actually made
                    $blob = $newBlob;
                }
            }
        }

        if (!isset($_GET['g']) && !empty($blob['storage_loc']) && $blob['storage_loc'] === 'db') {
            if ($isText) {
                $gzBlob = $this->findGzipBlob($blob);
                if (!$gzBlob) {
                    return $this->compressBlob($blob);
                }
                $blob = $gzBlob;
            }
        }

        if (!empty($blob['file_url']) && $blob['file_url']) {
            // Need to send through this controller if its a download
            // request and the file is usually stored with an inline disposition
            if ($this->localMode || $this->dpEnv->getConfig('settings.remote_blobs_proxy_local')) {
                if ($urlRewrites = $this->dpEnv->getConfig('settings.remote_blobs_local_urlrewrite')) {
                    foreach ($urlRewrites as $pattern => $replace) {
                        $blob['file_url'] = preg_replace($pattern, $replace, $blob['file_url']);
                    }
                }

                $context = stream_context_create([
                    'http' => ['timeout' => 10.0], // read timeout. we do it in chunks, so this is rather low
                ]);

                $timeStart = time();
                $maxTime   = 30;

                $buf  = '';
                $fail = false;

                $fp = @fopen($blob['file_url'], 'r', false, $context);
                while (!@feof($fp)) {
                    $buf .= @fread($fp, 1024);
                    if ((time() - $timeStart) > $maxTime) {
                        $fail = true;
                        break;
                    }
                }
                @fclose($fp);

                if (!$fail) {
                    $headers  = $this->getHeaders($blob);
                    $response = new Response($buf, 200, $headers);

                    $response->send();
                    exit;
                }

                $buf = null;
            }

            if ($urlRewrites = $this->dpEnv->getConfig('settings.remote_blobs_redirect_urlrewrite')) {
                foreach ($urlRewrites as $pattern => $replace) {
                    $blob['file_url'] = preg_replace($pattern, $replace, $blob['file_url']);
                }
            }

            header('HTTP/1.1 301 Moved Permanently');
            header("Location: {$blob['file_url']}");
            exit;
        }

        if ($blob['storage_loc'] == 'fs') {
            $response = $this->sendFromFilesystem($blob);
        } else {
            $response = $this->sendFromDatabase($blob);
        }
        $response->send();
    }

    /**
     * Send general file headers.
     *
     * Blobs never change so we can enable aggresive cache control on files served.
     *
     * @param $blob
     *
     * @return array
     */
    protected function getHeaders($blob)
    {
        $headers                   = [];
        $headers['Content-Type']   = $blob['content_type'].'; filename="'.addslashes($blob['filename']).'"';
        $headers['Content-Length'] = $blob['filesize'];

        if (strpos($blob['sys_name'], '-gzip') === strlen($blob['sys_name']) - 5) {
            $headers['Content-Encoding'] = 'gzip';
        }
        if ($this->request->headers->has('range')) {
            // Only accept ranges on safe HTTP methods
            $headers['Accept-Ranges'] = $this->request->isMethodSafe(false) ? 'bytes' : 'none';
        }

        $safeInlineContent = $this->alwaysForceDownloadOfHtmlFiles;
        if (!isset($_GET['dl']) && ContentTypes::isInlineContentType($blob['content_type'], $safeInlineContent, $blob['filename'])) {
            $headers['Content-Disposition'] = 'inline; filename="'.addslashes($blob['filename']).'"';
        } else {
            $headers['Content-Disposition'] = 'attachment; filename="'.addslashes($blob['filename']).'"';
        }

        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $blob['date_created']);
        if (!$d) {
            $d = new \DateTime();
        }
        $headers['Last-Modified']          = $d->format('D, d M Y H:i:s').' GMT';
        $headers['Expires']                = date('D, d M Y H:i:s', strtotime('+1 year')).' GMT';
        $headers['Cache-Control']          = 'max-age=31556926,private';
        $headers['X-Robots-Tag']           = 'noindex, nofollow';
        $headers['X-Content-Type-Options'] = 'nosniff';

        return $headers;
    }

    /**
     * Send a file that is stored in the filesystem.
     *
     * @param $blob
     *
     * @throws \Exception
     *
     * @return Response
     */
    protected function sendFromFilesystem($blob)
    {
        $headers = $this->getHeaders($blob);

        // folder we store blobs in
        $basePath = $this->dpEnv->getUserFilesDir();

        $filepath = $basePath.DIRECTORY_SEPARATOR.$blob['save_path'];

        $this->addLogMessage('Expecting file path: %s', $filepath);

        if (!file_exists($filepath)) {
            if ($this->errorMode == 'exception') {
                throw new \Exception('File not found. (4)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'File not found. (4)';

            return;
        }

        if ($this->dpEnv->getConfig('settings.filestorage_use_xsendfile')) {
            $headers['X-Sendfile'] = $filepath;

            return new Response('', 200, $headers);
        } else {
            return new BinaryFileResponse($filepath, 200, $headers);
        }
    }

    /**
     * Send a file that is stored in the database.
     *
     * @param Blob $blob
     *
     * @return StreamedResponse
     */
    public function sendFromDatabase($blob)
    {
        $headers = $this->getHeaders($blob);

        $sth = $this->getPdoRead()->prepare('SELECT data FROM blobs_storage WHERE blob_id = :blob_id ORDER BY id ASC');
        $sth->execute(['blob_id' => $blob['id']]);

        $response = new StreamedResponse(
            function () use ($sth) {
                while (($seg = $sth->fetchColumn(0)) !== false) {
                    echo $seg;
                    flush();
                }
                $sth->closeCursor();
            },
            200,
            $headers
        );
        if ($this->request->headers->has('range')) {
            $this->setRangeHeaders($response, $blob);
        }

        return $response;
    }

    /**
     * @param Response $response
     * @param Blob     $blob
     *
     * @return Response
     */
    public function setRangeHeaders($response, $blob)
    {
        // Process the range headers.
        if (!$this->request->headers->has('If-Range')) {
            $range    = $this->request->headers->get('Range');
            $fileSize = $blob['filesize'];

            list($start, $end) = explode('-', substr($range, 6), 2) + [0];

            $end = ('' === $end) ? $fileSize - 1 : (int) $end;

            if ('' === $start) {
                $start = $fileSize - $end;
                $end   = $fileSize - 1;
            } else {
                $start = (int) $start;
            }

            if ($start <= $end) {
                if ($start < 0 || $end > $fileSize - 1) {
                    $response->setStatusCode(416);
                    $response->headers->set('Content-Range', sprintf('bytes */%s', $fileSize));
                } elseif (0 !== $start || $end !== $fileSize - 1) {
                    $response->setStatusCode(206);
                    $response->headers->set('Content-Range', sprintf('bytes %s-%s/%s', $start, $end, $fileSize));
                    $response->headers->set('Content-Length', $end - $start + 1);
                }
            }
        }

        return $response;
    }

    protected function getSizedBlobSysName($blob_id, $size, $is_fit)
    {
        $sysName = 'blob-'.$blob_id.'-'.$size;

        if ($is_fit) {
            $sysName .= '-fit';
        }

        $this->addLogMessage('Sized blob sys_name: %s', $sysName);

        return $sysName;
    }

    /**
     * @param array $blob_info
     * @param int   $size
     * @param bool  $isFit
     * @param bool  $die_fail
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function createSizedImage(array $blob_info, $size, $isFit, $die_fail = true)
    {
        $container = $this->bootFullSystem();
        $bs        = $container->getBlobStorage();

        $blob = $container->getEm()->find(Blob::class, $blob_info['id']);
        $file = $bs->copyBlobRecordToString($blob);

        if (!$file) {
            $this->addLogMessage('Could not load blob file descriptor');

            if ($this->errorMode == 'exception') {
                throw new \Exception('File not found. (no_exist)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'File not found. (no_exist)';
            exit;
        }

        try {
            // Imagine doesnt suppress normal errors, so in addition to exception we'll get errors logged,
            // So @ to get rid of those exceptions
            $image = @$container->getImagine()->load($file);
        } catch (InvalidArgumentException $e) {
            $this->addLogMessage('Failed to resize: %s', $e->getMessage());
            if ($die_fail) {
                header('HTTP/1.0 500 Internal Server Error');
                echo 'Invalid image file. (invalid_image_data)';
                exit;
            }

            return null;
        }

        $width  = $image->getSize()->getWidth();
        $height = $image->getSize()->getHeight();

        $m = null;
        if (preg_match('#^(\d+)x(\d+)$#', $size, $m)) {
            $reqW = $m[1];
            $reqH = $m[2];
        } else {
            $reqW = $size;
            $reqH = $size;
        }

        $reqW = (int) $reqW;
        $reqH = (int) $reqH;

        if ($reqW < 1) {
            $reqW = 1;
        }
        if ($reqH < 1) {
            $reqH = 1;
        }

        if ($reqW > 1000) {
            $reqW = 1000;
        }
        if ($reqH > 1000) {
            $reqH = 1000;
        }

        if ($reqH == $reqH) {
            $noFit = max($width, $height) > $size;
        } else {
            $noFit = ($width > $reqW) || ($height > $reqH);
        }

        // Only shrink if it doesn't fit inside the box.

        // If the image has a w/h of 1, then scaling with
        // fit will result in a dim of 0 when Imagine tries to scale
        if ($width < 2) {
            $isFit = false;
        }
        if ($height < 2) {
            $isFit = false;
        }

        if ($noFit || $isFit) {
            $sizeW = $reqW;
            $sizeH = $reqH;

            if ($height > $width) {
                $sizeW = round($sizeW * ($width / $height));
            } elseif ($width > $height) {
                $sizeH = round($sizeH * ($height / $width));
            }

            if ($sizeW == 0) {
                $sizeW = 1;
            }

            if ($sizeH == 0) {
                $sizeH = 1;
            }

            $box = new Box($sizeW, $sizeH);
            $image->resize($box);
        }

        try {
            $file = $image->get($blob->getImageType());

            // Workaround for potential bug in some Windows servers
            // where the GD handler tries to save a temp file and the default
            // temp dir is not writable.
        } catch (RuntimeException $e) {
            $tmp = $this->dpEnv->getUserTmpDir().DIRECTORY_SEPARATOR.uniqid('img', true).'.'.Strings::getExtension($blob->filename);
            $image->save($tmp);
            $file = file_get_contents($tmp);
            @unlink($tmp);
        }

        return $file;
    }

    /**
     * Resize a blob. This needs to load the entire environment.
     *
     * @param array $blob_info
     * @param int   $size
     * @param bool  $isFit
     * @param bool  $dieFail
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return array
     */
    protected function createSizedBlob(array $blob_info, $size, $isFit, $dieFail = true)
    {
        $container = $this->bootFullSystem();

        $file = $this->createSizedImage($blob_info, $size, $isFit, $dieFail);
        $blob = $container->getEm()->find(Blob::class, $blob_info['id']);
        $bs   = $container->getBlobStorage();

        $newBlob = $bs->createBlobRecordFromString($file, $blob->filename, $blob->content_type, [
            'sys_name'      => $this->getSizedBlobSysName($blob->id, $size, $isFit),
            'original_blob' => $blob,
        ]);

        $this->addLogMessage('Cached resize as blob %d', $newBlob->getId());

        $newBlobInfo                  = $newBlob->toArray(DomainObject::TOARRAY_ONLY_PRIMATIVES);
        $newBlobInfo['filename_safe'] = $blob->getFilenameSafe();

        return $newBlobInfo;
    }

    /**
     * Serves a v2 application asset.
     *
     * @param string $appId
     * @param string $assetPath
     *
     * @throws \Exception
     */
    public function handleAppsV2FileRequest($appId, $assetPath)
    {
        $statement    = 'SELECT blob_id, blob_authcode FROM app2_app_asset_blob WHERE app_id = :appId AND path =:assetPath LIMIT 1';
        $pdoStatement = $this->getPdoRead()->prepare($statement);
        $pdoStatement->execute(['appId' => $appId, 'assetPath' => $assetPath]);
        $this->addLogMessage("AppID: $appId, Path: $assetPath");
        $blobInfo = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
        if (!empty($blobInfo)) {
            $this->alwaysForceDownloadOfHtmlFiles = false;
            $this->localMode                      = true;
            $this->showBlob($blobInfo['blob_id'], null, $blobInfo['blob_authcode']);
        } else {
            if ($this->errorMode == 'exception') {
                throw new \Exception('App file not found. (bad_asset_path)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'App file not found. (bad_asset_path)';

            return;
        }
    }

    /**
     * Serve static content from native 'apps'.
     *
     * @throws \Exception
     */
    public function handleAppsRequest($app_name, $type, $filename)
    {
        if ($type == 'app' && ($filename == 'app.js' || $filename == 'module.js')) {
            $typeF = '';
        } else {
            $typeF = "{$type}/";
        }

        $pathInfo = $this->_getAppsPath($app_name, $typeF, $filename);
        if (!$pathInfo) {
            if ($this->errorMode == 'exception') {
                throw new \Exception('App file not found. (bad_path)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'App file not found. (bad_path)';

            return;
        }

        $filepath    = $pathInfo['filepath'];
        $basepath    = $pathInfo['basepath'];
        $basepathStd = str_replace('\\', '/', $basepath);

        $mimeType = ContentTypes::getContentTypeFromFilename($filename);
        if (!$mimeType) {
            $mimeType = 'application/octet-stream';
        }

        $content = null;
        if ($type == 'html') {
            $content = file_get_contents($filepath);
            $content = preg_replace_callback('/<!\-\-#include\s+file="([a-zA-Z0-9_\-\.\/]+)"\s+\-\->/', function ($m) use ($basepath, $basepathStd) {
                $path = @realpath($basepath.$m[1]);
                $pathStd = str_replace('\\', '/', $path);

                if (!$path || !is_file($path) || strpos($pathStd, $basepathStd) !== 0) {
                    return '<!-- Invalid include file: '.$m[1].' -->';
                }

                $incContent = @file_get_contents($path);

                return $incContent;
            }, $content);

            $fileSize = strlen($content);
        } else {
            $fileSize = filesize($filepath);
            $content  = null;
        }

        header('Content-Type: '.$mimeType.'; filename="'.addslashes($filename).'"');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: '.$fileSize);
        header('Last-Modified: '.date('D, d M Y H:i:s', time() - 3600).' GMT');
        header('Expires: '.date('D, d M Y H:i:s', time() - 3600).' GMT');
        header('Cache-Control: '.($this->dpEnv->isDebug() ? 'no-cache' : 'max-age=31556926,private'));
        header('X-Robots-Tag: noindex, nofollow');

        if ($content !== null) {
            echo $content;
        } else {
            readfile($filepath);
        }
    }

    private function _getAppsPath($appName, $typeF, $filename)
    {
        static $paths = null;
        if (!$paths) {
            $paths            = $this->dpEnv->getConfig('paths.app_paths', []);
            $paths['default'] = DP_ROOT.'/apps';
        }

        $filename = str_replace('..', '', $filename);

        foreach ($paths as $prefix => $basePath) {
            if ($prefix === 'default' || strpos($appName, $prefix) === 0) {
                $path = $basePath.'/'.$appName.'/'.$typeF.$filename;
                if (file_exists($path)) {
                    return [
                        'filepath' => $path,
                        'basepath' => $basePath.'/'.$appName.'/'.$typeF,
                    ];
                }
            }
        }

        // Second path is doing dumb-check on every path
        foreach ($paths as $prefix => $basePath) {
            $path = $basePath.'/'.$appName.'/'.$typeF.$filename;
            if (file_exists($path)) {
                return [
                    'filepath' => $path,
                    'basepath' => $basePath.'/'.$appName.'/'.$typeF,
                ];
            }
        }

        return null;
    }

    private function findMovedAuthBlob($oldAuthcode)
    {
        $moved = $this->findMovedBlobAuthInfo($oldAuthcode);
        if ($moved) {
            $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE authcode = :authcode');
            $sth->execute(['authcode' => $moved['new_authcode']]);
            $blob = $sth->fetch(\PDO::FETCH_ASSOC);

            return $blob;
        }

        return null;
    }

    private function findMovedBlobAuthInfo($oldAuthcode)
    {
        $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs_auth_moved WHERE old_authcode = :authcode');
        $sth->execute(['authcode' => $oldAuthcode]);
        $moved = $sth->fetch(\PDO::FETCH_ASSOC);

        if ($moved) {
            $movedAgain = $this->findMovedBlobAuthInfo($moved['new_authcode']);
            if ($movedAgain) {
                return $movedAgain;
            }

            return $moved;
        }

        return null;
    }

    private function userDoesAcceptGzip()
    {
        $supportsGzip = false;
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $supportsGzip = strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
        }

        return $supportsGzip && function_exists('gzencode');
    }

    private function findGzipBlob($blob)
    {
        $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE sys_name = :sys_name AND storage_loc = "db"');
        $sth->execute(['sys_name' => "blob-{$blob['id']}-gzip"]);

        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $blob
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    private function compressBlob($blob)
    {
        $container  = $this->bootFullSystem();
        $bs         = $container->getBlobStorage();
        $blobObject = $container->getEm()->find(Blob::class, $blob['id']);
        $file       = $bs->copyBlobRecordToString($blobObject);

        $bs->createBlobRecordFromString(gzencode($file), $blobObject->filename, $blobObject->content_type, [
            'sys_name'             => "blob-{$blob['id']}-gzip",
            'original_blob'        => $blobObject,
            'storage_loc_specific' => 'db',
        ]);

        $headers = $this->getHeaders($blob);

        $response = new Response($file, 200, $headers);
        $response->send();
    }
}
