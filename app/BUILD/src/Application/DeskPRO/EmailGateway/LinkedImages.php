<?php

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use DpSys\LowError\SystemErrorHandler;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Orb\Log\Logger;
use Orb\Util\Strings;

/**
 * Class LinkedImages.
 */
class LinkedImages
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $body
     *
     * @return string
     */
    public function importReplaceLinkedImages($body)
    {
        if (!App::getContainer()->getSetting('core.emails.download_hotlinked_images.enabled')) {
            $this->logger->log('Skipping `importReplaceLinkedImages` because settings '
                .'`core.emails.download_hotlinked_images.enabled` disabled', 'debug');

            return $body;
        }

        static $cache;
        $m      = null;
        $tmpDir = App::$container->get('deskpro.app_env')->getUserTmpDir();
        $client = new Client([
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::CONNECT_TIMEOUT => 4,
            RequestOptions::TIMEOUT         => 10,
        ]);
        $maxImageSize = (int) App::getContainer()->getSetting('core.emails.download_hotlinked_images.image_maxsize');
        $maxTotalSize = (int) App::getContainer()->getSetting('core.emails.download_hotlinked_images.total_maxsize');

        $totalImageSize = 0;
        if (preg_match_all('#(<|&lt;)img[^>]*/?(>|&gt;)((<|&lt;)/img(>|&gt;))?#iu', $body, $m, \PREG_SET_ORDER)) {
            foreach ($m as $match) {
                // Check if it is even an inline image
                $src = Strings::extractRegexMatch('#src=("|\')((https?:)?//.*?)(\1)#iu', $match[0], 2);
                if ($src) {
                    if (isset($cache[$src])) {
                        $body = str_replace($match[0], $cache[$src], $body);
                    } else {
                        $tmpFile  = $tmpDir.'/email-image-'.mt_rand(1000, 9999);
                        $resource = fopen($tmpFile, 'w');
                        $errors   = [];
                        try {
                            SystemErrorHandler::runWithoutErrorHandler(function () use ($client, $src, $resource) {
                                $client->request('GET', $src, ['sink' => $resource]);
                            }, $errors);
                            if ($errors) {
                                $e = $errors[0];
                                $this->logger->logError(sprintf('Download file failed: [%s:%s] %s', $e['Type'], $e['code'], substr($e['message'], 0, 1000)));
                                $tag  = "<a href=\"$src\" target=\"_blank\">$src</a>";
                                $body = str_replace($match[0], $tag, $body);

                                @fclose($resource);
                                continue;
                            }
                        } catch (\Exception $e) {
                            $this->logger->logError(sprintf('Download file failed: [%s:%s] %s', get_class($e), $e->getCode(), substr($e->getMessage(), 0, 1000)));
                            $tag  = "<a href=\"$src\" target=\"_blank\">$src</a>";
                            $body = str_replace($match[0], $tag, $body);
                            @fclose($resource);
                            continue;
                        }
                        @fclose($resource);
                        if (!file_exists($tmpFile)) {
                            continue;
                        }
                        $imageSize = filesize($tmpFile);
                        // We don't import images over 10 MB and more than 25MB of images in total
                        if ($imageSize > $maxImageSize || $totalImageSize + $imageSize > $maxTotalSize) {
                            unlink($tmpFile);
                            $tag  = "<a href=\"$src\" target=\"_blank\">$src</a>";
                            $body = str_replace($match[0], $tag, $body);
                            continue;
                        }
                        $totalImageSize += $imageSize;
                        if (function_exists('exif_imagetype')) {
                            if (!$type = @exif_imagetype($tmpFile)) {
                                // The downloaded file is not an image
                                unlink($tmpFile);
                                continue;
                            }
                        } else {
                            if (!$size = @getimagesize($tmpFile)) {
                                // The downloaded file is not an image
                                unlink($tmpFile);
                                continue;
                            }
                            $type = $size[2];
                        }

                        $name = $this->generateNameFromType($type);
                        $blob = App::getContainer()->getBlobStorage()->createBlobRecordFromFile(
                            $tmpFile,
                            $name[0],
                            $name[1]
                        );
                        unlink($tmpFile);
                        $tag         = '[attach:image:'.$blob->getAuthcode().':'.$name[0].']';
                        $body        = str_replace($match[0], $tag, $body);
                        $cache[$src] = $tag;
                    }
                }
            }
        }

        return $body;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    private function generateNameFromType($type)
    {
        $name = 'image'.mt_rand(1000, 9999);
        switch ($type) {
            case IMAGETYPE_GIF:
                return [$name.'.gif', 'image/gif'];
            case IMAGETYPE_JPEG:
                return [$name.'.jpg', 'image/jpeg'];
            case IMAGETYPE_PNG:
                return [$name.'.png', 'image/png'];
            case IMAGETYPE_BMP:
                return [$name.'.bmp', 'image/bmp'];
            case IMAGETYPE_TIFF_II:
            case IMAGETYPE_TIFF_MM:
                return [$name.'.tiff', 'image/tiff'];
        }
    }
}
