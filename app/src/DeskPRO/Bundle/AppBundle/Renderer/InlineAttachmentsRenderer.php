<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Renderer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Symfony\Component\Routing\RouterInterface;

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
     * @param RouterInterface $router
     * @param DeskproBlobStorage $blob_storage
     */
    public function __construct(RouterInterface $router, DeskproBlobStorage $blob_storage)
    {
        $this->router = $router;
        $this->blob_storage = $blob_storage;
    }

    /**
     * @param $string
     * @return string
     */
    public function render($string)
    {
        $changed = true;
        while ($changed) {
            $m = null;
            $changed = false;

            if (preg_match('#\[attach:([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\. ]+)\]#', $string, $m)) {
                $changed = true;
                $pos    = strpos($string, $m[0]);
                $before = substr($string, 0, $pos);
                $string = str_replace($m[0], $this->replaceTxt($m, $before), $string);
            }
        }

        return $string;
    }

    /**
     * @param array $m
     * @param string $before
     * @return string
     */
    private function replaceTxt($m, $before = '')
    {
        $download_url = $this->router->generate('serve_blob', array('blob_auth_id' => $m[2], 'filename' => $m[3]), RouterInterface::ABSOLUTE_URL);

        $extra = 'data-downloadurl="' . $download_url . '" data-blob-authid="' . $m[2] . '"';

        $marker_class_a = 'dp-embed-blob-a-' . $m[2];
        $marker_class_img = 'dp-embed-blob-img-' . $m[2];

        if ($m[1] == 'signature_image') {
            $url = $this->router->generate('serve_blob', array('blob_auth_id' => $m[2], 'filename' => $m[3]), RouterInterface::ABSOLUTE_URL);

            $replace = sprintf('<img src="%s" title="%s" />', $url, $m[3]);
        } elseif ($m[1] == 'image') {
            $url = $this->router->generate('serve_blob', array('blob_auth_id' => $m[2], 'filename' => $m[3], 's' => 350), RouterInterface::ABSOLUTE_URL);

            $do_link = true;

            // If we arent balanced, then it means the image is within an <a>, so
            // we shouldnt link the image ourselves
            if (substr_count($before, '<a') != substr_count($before, '</a>')) {
                $do_link = false;
            }

            if (!$do_link) {
                $replace = sprintf('<img src="%s" title="%s" class="dragout '.$marker_class_img.'" %s/>', $url, $m[3], $extra);
            } else {
                $replace = sprintf('<a href="%s" target="_blank" class="dp-is-image dragout '.$marker_class_a.'" %s><img src="%s" title="%s" class="'.$marker_class_img.'" /></a>', $download_url, $extra, $url, $m[3]);
            }
        } else {
            $replace = sprintf('<a href="%s" target="_blank" class="dp-is-image dragout '.$marker_class_a.'" %s>%s</a>', $download_url, $extra, $m[3]);
        }

        return $replace;
    }
}