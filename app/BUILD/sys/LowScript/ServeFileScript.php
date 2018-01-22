<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpSys\LowScript;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Orb\Data\ContentTypes;
use Orb\Util\Strings;

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
    protected $error_mode = 'error';

    /** @var bool this setting is only overwritten by the apps v2 asset serving router */
    private $alwaysForceDownloadOfHtmlFiles = true;

    /**
     * @var bool
     */
    protected $local_mode = false;

    public function runAction()
    {
        if (isset($_GET['debug'])) {
            $this->error_mode = 'exception';
        }

        try {
            $pathinfo = $this->getPathInfo();

            $this->addLogMessage('pathinfo: %s', $pathinfo);

            // local URLs just disable redirection action on remote URLs (e.g., S3)
            // Used to serve app assets where serving from a remote domain
            // could cause same-origin policy errors
            if (preg_match('#^/local/#', $pathinfo)) {
                $this->local_mode = true;
                $pathinfo         = preg_replace('#^/local/#', '/', $pathinfo);
            }

            if (isset($_GET['local'])) {
                $this->local_mode = true;
            }

            if (preg_match('#^/size/([0-9]+)/#', $pathinfo, $m)) {
                $_GET['s'] = $m[1];
                $pathinfo  = str_replace($m[0], '/', $pathinfo);
            }
            if (preg_match('#^/size-fit/#', $pathinfo, $m)) {
                $_GET['size-fit'] = 1;
                $pathinfo         = str_replace($m[0], '/', $pathinfo);
            }

            // Default avatar: /avatar/50/default
            if (preg_match('#^/avatar/([0-9]+)/default.jpg#', $pathinfo, $m)) {
                $this->defaultAvatarAction($m[1]);

            // Person avatar: /avatar/13
            } elseif (preg_match('#^/avatar/([0-9]+)#', $pathinfo, $m)) {
                $this->personAvatarAction($m[1]);

            // Default org avatar: /o-avatar/default
            } elseif (preg_match('#^/o-avatar/default#', $pathinfo)) {
                $this->defaultOrgAvatarAction();

            // A standard asset
            } elseif (preg_match('#^/dp-asset/([a-zA-Z0-9_\.\-]+)$#', $pathinfo, $m)) {
                $this->dpAsset($m[1]);

            // A standard asset
            } elseif (preg_match('#^/dp-asset/([a-zA-Z0-9_\.\-]+)$#', $pathinfo, $m)) {
                $this->dpAsset($m[1]);

            // Org avatar: /o-avatar/13
            } elseif (preg_match('#^/o-avatar/([0-9]+)#', $pathinfo, $m)) {
                $this->orgAvatarAction($m[1]);

            // sitemap.xml
            } elseif (preg_match('#^/sitemap.xml#', $pathinfo, $m)) {
                $this->sitemapXmlAction();

            // A filesystem blob like /123AJKJKHSD1244AXC/filename.zip
            // That is: /(batch)(authcode)(id)(namehash)/name.zip
            //0XNSNTQHTNR43DD567
            } elseif (preg_match('#^/([0-9]+)([A-Z]+)([0-9]+)([A-Z0-9]{6})(?:/|\-)(.*?)$#', $pathinfo, $m)) {
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
            } elseif (preg_match('#^/([0-9]+)([A-Z]+0)(?:/|\-)(.*?)$#', $pathinfo, $m)) {
                $this->addLogMessage('handleDbBlobRequest: %s', implode(', ', $m));
                $this->handleDbBlobRequest($m[1], $m[2], $m[3]);
            } elseif (preg_match('#^/brand-[0-9]+/([0-9]+)([A-Z]+0)(?:/|\-)(.*?)$#', $pathinfo, $m)) {
                $this->addLogMessage('handleDbBlobRequest: %s (legacy brand route)', implode(', ', $m));
                $this->handleDbBlobRequest($m[1], $m[2], $m[3]);
            } elseif (preg_match('#^/gradient$#', $pathinfo)) {
                $this->handleGradientRequest();
            } elseif (preg_match('#^/apps/([a-zA-Z0-9_\-\.]+)/(app|js|css|html|res)/(.*?)$#', $pathinfo, $m)) {
                $this->handleAppsRequest($m[1], $m[2], $m[3]);
            } elseif (preg_match('#^/apps/([^/]+)/v[^/]+/files/(.+)$#', $pathinfo, $m)) {
                $this->handleAppsV2FileRequest($m[1], $m[2]);
            } else {
                if ($this->error_mode == 'exception') {
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
     */
    public function handleGradientRequest()
    {
        if (!function_exists('imagepng') || (!function_exists('imagecreatetruecolor') && !function_exists('imagecreate'))) {
            if ($this->error_mode == 'exception') {
                throw new \Exception('File not found. (no_image_manip)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'File not found (no_image_manip)';

            return;
        }

        require DP_ROOT.'/src/Orb/Util/Colors.php';
        require DP_ROOT.'/src/Orb/Images/Util.php';

        $start_color = isset($_REQUEST['start_color']) ? (string) $_REQUEST['start_color'] : '000000';
        $end_color   = isset($_REQUEST['end_color']) ? (string) $_REQUEST['end_color'] : '000000';

        $get_rgb = function ($color) {
            // Not rgb(
            if (!strpos($color, '(') || !strpos($color, ')')) {
                $color = preg_replace('#[^a-fA-F0-9]#', '', $color);
                if (strlen($color) == 6 || strlen($color) == 3) {
                    $color = \Orb\Util\Colors::hex2rgb($color);
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

        $start_color = $get_rgb($start_color);
        $end_color   = $get_rgb($end_color);

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

        $im = \Orb\Images\Util::getGradientImage($size, $start_color, $end_color, $direction, 1, 1);

        $desc = implode('-', $start_color).'_'.implode('-', $end_color).'_'.$direction.'_'.$size.'.png';

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
            if ($this->error_mode == 'exception') {
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
    }

    /**
     * Render an org avatar.
     *
     * @deprecated If you have a org record, then use picture_blob_id and directly link to the avatar
     *
     * @param $person_id
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
    }

    /**
     * Serve the default avatar.
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

            $container   = $this->bootFullSystem();
            $blob_entity = $container->getBlobStorage()->createBlobRecordFromFile(
                $file,
                pathinfo($file, PATHINFO_BASENAME),
                $mime,
                ['sys_name' => $name]
            );

            $blob = $blob_entity->toArray(DomainObject::TOARRAY_ONLY_PRIMATIVES);
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
                if ($this->error_mode == 'exception') {
                    throw new \Exception('File not found. (300)', 400);
                }
                header('HTTP/1.0 404 Not Found');
                echo 'File not found. (300)';

                return;
        }

        $filesize = filesize($path);

        header('Content-Type: '.$mimetype.'; filename="'.addslashes($filename).'"');
        header('Content-Length: '.$filesize);
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: attachment; filename="'.addslashes($filename).'"');
        header('X-Robots-Tag: noindex, nofollow');

        if ($this->dpEnv->getConfig('settings.filestorage_use_xsendfile')) {
            header("X-Sendfile: $path");
        } else {
            readfile($path);
        }
    }

    /**
     * Serve the default org avatar.
     */
    public function defaultOrgAvatarAction()
    {
        $name = 'orgpicture-default';

        $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE sys_name = :sys_name');
        $sth->execute(['sys_name' => $name]);
        $blob = $sth->fetch(\PDO::FETCH_ASSOC);

        // The default avatar blob hasnt been inserted yet, default it from the resources dir now
        if (!$blob) {
            $container   = $this->bootFullSystem();
            $blob_entity = $container->getBlobStorage()->createBlobRecordFromFile(
                DP_ROOT.'/src/Application/DeskPRO/Resources/assets/'.$name.'.jpeg',
                $name.'.jpeg',
                'image/jpeg',
                ['sys_name' => $name]
            );

            $blob = $blob_entity->toArray(DomainObject::TOARRAY_ONLY_PRIMATIVES);
        }

        $size = null;
        if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] <= 600) {
            $size = $_GET['s'];
        }

        $this->showBlob($blob, $size);
    }

    /**
     * @param int    $blob_id
     * @param string $blob_auth
     * @param string $blob_filename
     */
    protected function handleFilesystemBlobRequest($batch, $authcode, $blob_id, $namehash, $filename)
    {
        //------------------------------
        // If its a simple file request we
        // can serve it without a db connection
        //------------------------------

        $base_path = $this->dpEnv->getUserFilesDir();

        $filepath_part = $batch.DIRECTORY_SEPARATOR.$batch.$authcode.$blob_id.$namehash;
        $filepath      = $base_path.DIRECTORY_SEPARATOR.$filepath_part;

        $filename_safe = Strings::utf8_accents_to_ascii($filename);
        $filename_safe = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $filename_safe);
        $filename_safe = preg_replace('#\-{2,}#', '-', $filename_safe);

        $check_namehash = strtoupper(substr(sha1($filename_safe.$blob_id), 0, 3));
        $check_namehash .= strtoupper(substr(md5($filename_safe.$blob_id), 0, 3));

        $this->addLogMessage('Expecting file path: %s', $filepath);

        $size = null;
        if (isset($_GET['s']) && ((is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] <= 600) || preg_match('#^\d+x\d+$#', $_GET['s']))) {
            $size = $_GET['s'];
            $this->addLogMessage('With size: %s', $size);
        }

        // Invalid name hash
        // But we have to double-check before failing since the filename could
        // possibly be custom in the case of downloads
        if ($check_namehash != $namehash) {
            $this->addLogMessage('Hash mismatch: %s !=', $check_namehash, $namehash);

            $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE id = :id');
            $sth->execute(['id' => $blob_id]);
            $blob = $sth->fetch(\PDO::FETCH_ASSOC);

            if ($blob['filename']) {
                $blob['filename_safe'] = Strings::utf8_accents_to_ascii($blob['filename']);
                $blob['filename_safe'] = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $blob['filename_safe']);
                $blob['filename_safe'] = preg_replace('#\-{2,}#', '-', $blob['filename_safe']);
            }

            if (!$blob || ($blob['filename'] != $filename && $blob['filename_safe'] != $filename && $blob['filename_safe'] != $filename_safe)) {
                if ($this->error_mode == 'exception') {
                    throw new \Exception('File not found. (2.1)', 400);
                }
                header('HTTP/1.0 404 Not Found');
                echo 'File not found. (2.1)';

                return;
            }
        }

        // Check if we have a record of it being moved
        if (!file_exists($filepath)) {
            $moved_blob = $this->findMovedAuthBlob($authcode.$blob_id.$namehash);
            if ($moved_blob) {
                $this->showBlob($moved_blob, $size);

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

        $mimetype = \Orb\Data\ContentTypes::getContentTypeFromFilename($filename);
        if (!$mimetype) {
            $mimetype = 'application/octet-stream';
        }

        $content_disposition = 'attachment';
        if (!isset($_GET['dl']) && \Orb\Data\ContentTypes::isInlineContentType($mimetype, true, $filename)) {
            $content_disposition = 'inline';
        }

        header('Content-Type: '.$mimetype.'; filename="'.addslashes($filename).'"');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: '.filesize($filepath));
        header('Content-Disposition: '.$content_disposition.'; filename="'.addslashes($filename).'"');
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
     * @param string|null $blob_auth
     *
     * @throws \Exception
     */
    protected function showBlob($blob, $size = null, $blob_auth = null)
    {
        //------------------------------
        // Fetch the blob
        //------------------------------

        if (!is_array($blob)) {
            $blob_id = $blob;

            $this->addLogMessage('Loading blob %d', $blob_id);

            $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE id = :id');
            $sth->execute(['id' => $blob_id]);
            $blob = $sth->fetch(\PDO::FETCH_ASSOC);

            if (!$blob) {
                $this->addLogMessage('Could not load blob record');
            } elseif ($blob_auth && $blob['authcode'] != $blob_auth) {
                $this->addLogMessage('bad authcode: %s != %s', $blob['authcode'], $blob_auth);

                // check if it was moved
                $moved_blob = $this->findMovedAuthBlob($blob_auth);
                if ($moved_blob && $moved_blob['id'] == $blob['id']) {
                    $this->addLogMessage('blob was moved');
                    $blob_auth = $moved_blob['authcode'];
                }
            }

            if (!$blob || ($blob_auth && $blob['authcode'] != $blob_auth)) {
                // Check for a sign code that overrides the authcode check
                // (See TicketMessage::procInlineAttach)
                $okay = false;
                if (!empty($_GET['sc'])) {
                    $sth = $this->getPdoRead()->prepare("SELECT value FROM settings WHERE name = 'core.install_token'");
                    $sth->execute();
                    $install_token = $sth->fetchColumn(0);

                    if (\Orb\Util\Util::checkStaticSecurityToken($_GET['sc'], $install_token.$blob_auth)) {
                        $okay = true;
                    }
                }

                if (!$okay) {
                    if ($this->error_mode == 'exception') {
                        throw new \Exception('File not found. (3)', 400);
                    }
                    header('HTTP/1.0 404 Not Found');
                    echo 'File not found. (3)';

                    return;
                }
            }
        }

        if (!is_array($blob) || empty($blob)) {
            if ($this->error_mode == 'exception') {
                throw new \Exception('File not found. (3.1)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'File not found. (3.1)';

            return;
        }

        $blob_id = $blob['id'];

        //------------------------------
        // Serve the file
        //------------------------------

        if (!isset($blob['filename_safe'])) {
            $filename_safe         = Strings::utf8_accents_to_ascii($blob['filename']);
            $filename_safe         = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $filename_safe);
            $filename_safe         = preg_replace('#\-{2,}#', '-', $filename_safe);
            $blob['filename_safe'] = $filename_safe;
        }

        $is_image = false;
        switch ($blob['content_type']) {
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/gif':
            case 'image/png':
                $is_image = true;
                break;
        }

        if ($is_image && $size) {
            $this->addLogMessage('Showing resized: '.$size);

            $is_fit = false;

            if (isset($_GET['size-fit'])) {
                $is_fit = (bool) $_GET['size-fit'];
                $this->addLogMessage('Is fit: %d', $is_fit);
            }

            $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE original_blob_id = :original_blob_id AND sys_name = :sys_name');
            $sth->execute(['original_blob_id' => $blob_id, 'sys_name' => $this->getSizedBlobSysName($blob_id, $size, $is_fit)]);
            $sub_blob = $sth->fetch(\PDO::FETCH_ASSOC);

            // Already have the cached resized blob
            if ($sub_blob) {
                $sub_blob['filename_safe'] = $blob['filename_safe'];
                $blob                      = $sub_blob;

            // Generate the resized blob and save it now
            } else {
                // Check abuse for resize image cache
                // If we have more than max allowed resized image copies then don't create a new blob for resized cache, just proceed it in runtime
                $sth = $this->getPdoRead()->prepare('SELECT COUNT(*) FROM blobs WHERE original_blob_id = :original_blob_id AND sys_name LIKE :sys_name');
                $sth->execute(['original_blob_id' => $blob_id, 'sys_name' => $this->getSizedBlobSysName($blob_id, '%', false)]);
                $count = $sth->fetch(\PDO::FETCH_COLUMN);

                if ($count > 25) {
                    $file = $this->createSizedImage($blob, $size, $is_fit, false);

                    $this->sendHeaders($blob);
                    echo $file;

                    return;
                }

                $new_blob = $this->createSizedBlob($blob, $size, $is_fit, false);

                if ($new_blob) {
                    // Possible the resize failed, in which case we'd fall back on showing the orig
                    // So only reassign blob if we know $new_blob was actually made
                    $blob = $new_blob;
                }
            }
        }

        if (!empty($blob['file_url']) && $blob['file_url']) {
            // Need to send through this controller if its a download
            // request and the file is usually stored with an inline disposition
            if ($this->local_mode || $this->dpEnv->getConfig('settings.remote_blobs_proxy_local')) {
                if ($urlRewrites = $this->dpEnv->getConfig('settings.remote_blobs_local_urlrewrite')) {
                    foreach ($urlRewrites as $pattern => $replace) {
                        $blob['file_url'] = preg_replace($pattern, $replace, $blob['file_url']);
                    }
                }

                $context = stream_context_create([
                    'http' => ['timeout' => 10.0], // read timeout. we do it in chunks, so this is rather low
                ]);

                $time_start = time();
                $max_time   = 30;

                $buf  = '';
                $fail = false;

                $fp = @fopen($blob['file_url'], 'r', false, $context);
                while (!@feof($fp)) {
                    $buf .= @fread($fp, 1024);
                    if ((time() - $time_start) > $max_time) {
                        $fail = true;
                        break;
                    }
                }
                @fclose($fp);

                if (!$fail) {
                    $this->sendHeaders($blob);
                    echo $buf;
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
            $this->sendFromFilesystem($blob);
        } else {
            $this->sendFromDatabase($blob);
        }
    }

    /**
     * Send general file headers.
     *
     * Blobs never change so we can enable aggresive cache control on files served.
     *
     * @param $blob
     */
    protected function sendHeaders($blob)
    {
        header('Content-Type: '.$blob['content_type'].'; filename="'.addslashes($blob['filename']).'"');
        header('Content-Length: '.$blob['filesize']);

        $safeInlineContent = $this->alwaysForceDownloadOfHtmlFiles;
        if (!isset($_GET['dl']) && \Orb\Data\ContentTypes::isInlineContentType($blob['content_type'], $safeInlineContent, $blob['filename'])) {
            header('Content-Disposition: inline; filename="'.addslashes($blob['filename']).'"');
        } else {
            header('Content-Disposition: attachment; filename="'.addslashes($blob['filename_safe']).'"');
        }

        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $blob['date_created']);
        if (!$d) {
            $d = new \DateTime();
        }
        header('Last-Modified: '.$d->format('D, d M Y H:i:s').' GMT');
        header('Expires: '.date('D, d M Y H:i:s', strtotime('+1 year')).' GMT');
        header('Cache-Control: max-age=31556926,private');
        header('X-Robots-Tag: noindex, nofollow');
        header('X-Content-Type-Options: nosniff');
    }

    /**
     * Send a file that is stored in the filesystem.
     *
     * @param $blob
     */
    protected function sendFromFilesystem($blob)
    {
        $this->sendHeaders($blob);

        // folder we store blobs in
        $base_path = $this->dpEnv->getUserFilesDir();

        $filepath_part = $blob['save_path'];
        $filepath      = $base_path.DIRECTORY_SEPARATOR.$blob['save_path'];

        $this->addLogMessage('Expecting file path: %s', $filepath);

        if (!file_exists($filepath)) {
            if ($this->error_mode == 'exception') {
                throw new \Exception('File not found. (4)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'File not found. (4)';

            return;
        }

        if ($this->dpEnv->getConfig('settings.filestorage_use_xsendfile')) {
            header("X-Sendfile: $filepath");
        } else {
            readfile($filepath);
        }
    }

    /**
     * Send a file that is stored in the database.
     *
     * @param $blob
     */
    public function sendFromDatabase($blob)
    {
        $this->sendHeaders($blob);

        $sth = $this->getPdoRead()->prepare('SELECT data FROM blobs_storage WHERE blob_id = :blob_id ORDER BY id ASC');
        $sth->execute(['blob_id' => $blob['id']]);

        while (($seg = $sth->fetchColumn(0)) !== false) {
            echo $seg;
            flush();
        }

        $sth->closeCursor();
    }

    protected function getSizedBlobSysName($blob_id, $size, $is_fit)
    {
        $sys_name = 'blob-'.$blob_id.'-'.$size;

        if ($is_fit) {
            $sys_name .= '-fit';
        }

        $this->addLogMessage('Sized blob sys_name: %s', $sys_name);

        return $sys_name;
    }

    /**
     * @param array $blob_info
     * @param int   $size
     * @param bool  $is_fit
     * @param bool  $die_fail
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function createSizedImage(array $blob_info, $size, $is_fit, $die_fail = true)
    {
        $container = $this->bootFullSystem();
        $bs        = $container->getBlobStorage();

        $blob = $container->getEm()->find('DeskPRO:Blob', $blob_info['id']);
        $file = $bs->copyBlobRecordToString($blob);

        if (!$file) {
            $this->addLogMessage('Could not load blob file descriptor');

            if ($this->error_mode == 'exception') {
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
        } catch (\Imagine\Exception\InvalidArgumentException $e) {
            $this->addLogMessage('Failed to resize: %s', $e->getMessage());
            if ($die_fail) {
                header('HTTP/1.0 500 Internal Server Error');
                echo 'Invalid image file. (invalid_image_data)';
                exit;
            }

            return;
        }

        $width  = $image->getSize()->getWidth();
        $height = $image->getSize()->getHeight();

        $m = null;
        if (preg_match('#^(\d+)x(\d+)$#', $size, $m)) {
            $req_w = $m[1];
            $req_h = $m[2];
        } else {
            $req_w = $size;
            $req_h = $size;
        }

        $req_w = (int) $req_w;
        $req_h = (int) $req_h;

        if ($req_w < 1) {
            $req_w = 1;
        }
        if ($req_h < 1) {
            $req_h = 1;
        }

        if ($req_w > 1000) {
            $req_w = 1000;
        }
        if ($req_h > 1000) {
            $req_h = 1000;
        }

        if ($req_h == $req_h) {
            $no_fit = max($width, $height) > $size;
        } else {
            $no_fit = ($width > $req_w) || ($height > $req_h);
        }

        // Only shrink if it doesn't fit inside the box.

        // If the image has a w/h of 1, then scaling with
        // fit will result in a dim of 0 when Imagine tries to scale
        if ($width < 2) {
            $is_fit = false;
        }
        if ($height < 2) {
            $is_fit = false;
        }

        if ($no_fit || $is_fit) {
            $size_w = $req_w;
            $size_h = $req_h;

            if ($height > $width) {
                $size_w = round($size_w * ($width / $height));
            } elseif ($width > $height) {
                $size_h = round($size_h * ($height / $width));
            }

            if ($size_w == 0) {
                $size_w = 1;
            }

            if ($size_h == 0) {
                $size_h = 1;
            }

            $box = new \Imagine\Image\Box($size_w, $size_h);
            $image->resize($box);
        }

        try {
            $file = $image->get($blob->getImageType());

            // Workaround for potential bug in some Windows servers
            // where the GD handler tries to save a temp file and the default
            // temp dir is not writable.
        } catch (\Imagine\Exception\RuntimeException $e) {
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
     * @param bool  $is_fit
     * @param bool  $die_fail
     *
     * @return array
     */
    protected function createSizedBlob(array $blob_info, $size, $is_fit, $die_fail = true)
    {
        $container = $this->bootFullSystem();

        $file = $this->createSizedImage($blob_info, $size, $is_fit, $die_fail);
        $blob = $container->getEm()->find('DeskPRO:Blob', $blob_info['id']);
        $bs   = $container->getBlobStorage();

        $newBlob = $bs->createBlobRecordFromString($file, $blob->filename, $blob->content_type, [
            'sys_name'      => $this->getSizedBlobSysName($blob->id, $size, $is_fit),
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
            $this->local_mode = true;
            $this->showBlob($blobInfo['blob_id'], null, $blobInfo['blob_authcode']);
        } else {
            if ($this->error_mode == 'exception') {
                throw new \Exception('App file not found. (bad_asset_path)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'App file not found. (bad_asset_path)';

            return;
        }
    }

    /**
     * Serve static content from native 'apps'.
     */
    public function handleAppsRequest($app_name, $type, $filename)
    {
        if ($type == 'app' && ($filename == 'app.js' || $filename == 'module.js')) {
            $type_f = '';
        } else {
            $type_f = "{$type}/";
        }

        $path_info = $this->_getAppsPath($app_name, $type_f, $filename);
        if (!$path_info) {
            if ($this->error_mode == 'exception') {
                throw new \Exception('App file not found. (bad_path)', 400);
            }
            header('HTTP/1.0 404 Not Found');
            echo 'App file not found. (bad_path)';

            return;
        }

        $filepath     = $path_info['filepath'];
        $basepath     = $path_info['basepath'];
        $basepath_std = str_replace('\\', '/', $basepath);

        $mimetype = ContentTypes::getContentTypeFromFilename($filename);
        if (!$mimetype) {
            $mimetype = 'application/octet-stream';
        }

        $content = null;
        if ($type == 'html') {
            $content = file_get_contents($filepath);
            $content = preg_replace_callback('/<!\-\-#include\s+file="([a-zA-Z0-9_\-\.\/]+)"\s+\-\->/', function ($m) use ($basepath, $basepath_std) {
                $path = @realpath($basepath.$m[1]);
                $path_std = str_replace('\\', '/', $path);

                if (!$path || !is_file($path) || strpos($path_std, $basepath_std) !== 0) {
                    return '<!-- Invalid include file: '.$m[1].' -->';
                }

                $inc_content = @file_get_contents($path);

                return $inc_content;
            }, $content);

            $filesize = strlen($content);
        } else {
            $filesize = filesize($filepath);
            $content  = null;
        }

        header('Content-Type: '.$mimetype.'; filename="'.addslashes($filename).'"');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: '.$filesize);
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

    private function _getAppsPath($appname, $type_f, $filename)
    {
        static $paths = null;
        if (!$paths) {
            $paths            = $this->dpEnv->getConfig('paths.app_paths', []);
            $paths['default'] = DP_ROOT.'/apps';
        }

        $filename = str_replace('..', '', $filename);

        foreach ($paths as $prefix => $base_path) {
            if ($prefix === 'default' || strpos($appname, $prefix) === 0) {
                $path = $base_path.'/'.$appname.'/'.$type_f.$filename;
                if (file_exists($path)) {
                    return [
                        'filepath' => $path,
                        'basepath' => $base_path.'/'.$appname.'/'.$type_f,
                    ];
                }
            }
        }

        // Second path is doing dumb-check on every path
        foreach ($paths as $prefix => $base_path) {
            $path = $base_path.'/'.$appname.'/'.$type_f.$filename;
            if (file_exists($path)) {
                return [
                    'filepath' => $path,
                    'basepath' => $base_path.'/'.$appname.'/'.$type_f,
                ];
            }
        }

        return;
    }

    private function findMovedAuthBlob($old_authcode)
    {
        $moved = $this->findMovedBlobAuthInfo($old_authcode);
        if ($moved) {
            $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs WHERE authcode = :authcode');
            $sth->execute(['authcode' => $moved['new_authcode']]);
            $blob = $sth->fetch(\PDO::FETCH_ASSOC);

            return $blob;
        }

        return;
    }

    private function findMovedBlobAuthInfo($old_authcode)
    {
        $sth = $this->getPdoRead()->prepare('SELECT * FROM blobs_auth_moved WHERE old_authcode = :authcode');
        $sth->execute(['authcode' => $old_authcode]);
        $moved = $sth->fetch(\PDO::FETCH_ASSOC);

        if ($moved) {
            $moved_again = $this->findMovedBlobAuthInfo($moved['new_authcode']);
            if ($moved_again) {
                return $moved_again;
            }

            return $moved;
        }

        return;
    }
}
