<?php

namespace DeskPRO\Bundle\AppBundle\Renderer;

use DeskPRO\Bundle\AppBundle\Entity\SplashImageProperty;
use Exception;

class SplashImageRenderer
{
    public function getSplashUrl(SplashImageProperty $splashImage, $width, $orientation)
    {
        if (!in_array($orientation, ['landscape', 'portrait', 'squarish'])) {
            throw new Exception(sprintf('Invalid orientation %s', $orientation));
        }
        if ($splashImage->getUrnNs() === SplashImageProperty::$blobNs) {
            if ($splashImage->getBlob()) {
                return $splashImage->getBlob()->getDownloadUrl();
            }
        }
        if ($splashImage->getUrnNs() === SplashImageProperty::$unsplashNs) {
            $options = $splashImage->getOptions();

            return $options['url'].'&w='.$width;
        }
        throw new Exception(sprintf('No url for splash image %d', $splashImage->getId()));
    }

    /**
     * @param SplashImageProperty $splashImage
     * @param int                 $width
     * @param string              $orientation
     *
     * @throws Exception
     *
     * @return string
     */
    public function getSplashBgcss(SplashImageProperty $splashImage, $width = 200, $orientation = 'landscape')
    {
        $url = $this->getSplashUrl($splashImage, $width, $orientation);

        return '<div class="dp-po-splash-image" style="background-image: url('.$url.'); background-position: 0 0; background-size: cover; background-repeat: no-repeat"></div>';
    }
}
