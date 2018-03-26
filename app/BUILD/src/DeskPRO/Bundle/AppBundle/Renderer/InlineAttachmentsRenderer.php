<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Renderer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use DeskPRO\Component\Util\RegexUtils;
use Symfony\Component\Routing\RouterInterface;

/**
 * Inline attachments are like:.
 *
 *     [attach:type:blob_auth_id:filename]
 *
 * That is:
 *
 * - type: signature_image, image, url, link
 * - blob_auth_id: The blobs auth id
 * - filename: The filename
 */
class InlineAttachmentsRenderer
{
    /**
     * @var DeskproBlobStorage
     */
    private $blob_storage;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface    $router
     * @param DeskproBlobStorage $blob_storage
     */
    public function __construct(RouterInterface $router, DeskproBlobStorage $blob_storage)
    {
        $this->router       = $router;
        $this->blob_storage = $blob_storage;
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function render($string)
    {
        $changed = true;
        while ($changed) {
            $m       = null;
            $changed = false;

            if (RegexUtils::safePregMatch('#\[attach:([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\. ]+)\]#',
                $string, $m)) {
                $changed = true;
                $pos     = strpos($string, $m[0]);
                $before  = substr($string, 0, $pos);
                $string  = str_replace($m[0], $this->replaceTxt($m, $before), $string);
            }
        }

        return $string;
    }

    /**
     * @param array  $m
     * @param string $before
     *
     * @return string
     */
    private function replaceTxt($m, $before = '')
    {
        $attach_type  = $m[1];
        $blob_auth_id = $m[2];
        $filename     = $m[3];

        if (substr(strtolower($filename), -5) === '.tiff') {
            $attach_type = 'url';
        }

        $download_url = $this->router->generate(
            'serve_blob',
            ['blob_auth_id' => $blob_auth_id, 'filename' => $filename],
            RouterInterface::ABSOLUTE_URL
        );

        $extra            = 'data-downloadurl="'.$download_url.'" data-blob-authid="'.$blob_auth_id.'"';
        $marker_class_a   = 'dp-embed-blob-a-'.$blob_auth_id;
        $marker_class_img = 'dp-embed-blob-img-'.$blob_auth_id;

        switch ($attach_type) {
            case 'signature_image':
                $replace = sprintf('<img src="%s" title="%s" />', $download_url, $filename);
                break;

            case 'image':
                $thumb_url = $this->router->generate('serve_blob', ['blob_auth_id' => $blob_auth_id, 'filename' => $filename, 's' => 350], RouterInterface::ABSOLUTE_URL);

                $do_link = true;

                // If we arent balanced, then it means the image is within an <a>, so
                // we shouldnt link the image ourselves
                if (substr_count($before, '<a') != substr_count($before, '</a>')) {
                    $do_link = false;
                }

                if (!$do_link) {
                    $replace = sprintf('<img src="%s" title="%s" class="dragout '.$marker_class_img.'" %s/>', $download_url, $filename, $extra);
                } else {
                    $replace = sprintf('<a href="%s" target="_blank" class="dp-is-image dragout '.$marker_class_a.'" %s><img src="%s" title="%s" class="'.$marker_class_img.'" /></a>', $download_url, $extra, $thumb_url, $filename);
                }
                break;

            case 'url':
                $replace = sprintf('<a href="%s" target="_blank" class="dragout '.$marker_class_a.'" %s>%s</a>', $download_url, $extra, $filename);
                break;

            case 'link':
            default:
                $url = $this->router->generate(
                    'serve_blob',
                    ['blob_auth_id' => $blob_auth_id, 'filename' => $filename],
                    RouterInterface::ABSOLUTE_URL
                );

                $replace = sprintf('<a href="%s" target="_blank" class="dp-is-image dragout '.$marker_class_a.'" %s>%s</a>', $url, $extra, $filename);
                break;
        }

        return $replace;
    }
}
