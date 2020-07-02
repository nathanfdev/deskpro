<?php

namespace DeskPRO\Bundle\AppBundle\Image;

use Imagine\Image\ImagineInterface;
use lsolesen\pel\PelDataWindow;
use lsolesen\pel\PelEntryAscii;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTiff;

/**
 * Class ImageCleaner.
 */
class ImageCleaner
{
    /**
     * @var ImagineInterface
     */
    private $imagine;

    /**
     * Constructor.
     *
     * @param ImagineInterface $imagine
     */
    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function stripImage($content)
    {
        try {
            $pelData = new PelDataWindow($content);

            if (PelJpeg::isValid($pelData)) {
                $jpeg = new PelJpeg($pelData);
                if ($jpeg->getExif()) {
                    $image = $this->imagine->load($content);
                    $image->strip();

                    $content = $image->get('jpeg');
                }
            } elseif (PelTiff::isValid($pelData)) {
                $img = new PelTiff();
                $img->load($pelData);

                $ifd = $img->getIfd();

                do {
                    foreach ($ifd->getEntries() as $num => $entry) {
                        if ($entry instanceof PelEntryAscii) {
                            $tag     = $entry->getBytes($pelData->getByteOrder());
                            $content = str_replace($tag, str_repeat(' ', strlen($tag)), $content);
                        }
                    }
                } while ($ifd = $ifd->getNextIfd());
            }
        } catch (\Exception $e) {
        }

        return $content;
    }
}
